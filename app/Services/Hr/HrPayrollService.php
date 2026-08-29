<?php

namespace App\Services\Hr;

use App\Services\Finance\GlPostingService;
use CodeIgniter\Database\BaseConnection;

class HrPayrollService
{
    public const STATUSES = [
        'draft'      => 'Draft',
        'calculated' => 'Calculated',
        'approved'   => 'Approved',
        'locked'     => 'Locked',
        'posted'     => 'Posted to GL',
        'cancelled'  => 'Cancelled',
    ];

    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function tablesReady(): bool
    {
        return $this->db->tableExists('hr_payroll_runs')
            && $this->db->tableExists('hr_payroll_lines');
    }

    /** @return list<array<string, mixed>> */
    public function listRuns(?int $companyId = null, ?int $limit = 50): array
    {
        if (! $this->tablesReady()) {
            return [];
        }

        $q = $this->db->table('hr_payroll_runs r')
            ->select('r.*, fb.name AS branch_name')
            ->join('finance_branches fb', 'fb.id = r.branch_id', 'left')
            ->orderBy('r.period_start', 'DESC');

        if ($companyId) {
            $q->where('r.company_id', $companyId);
        }

        if ($limit) {
            $q->limit($limit);
        }

        return $q->get()->getResultArray();
    }

    public function findRun(int $id): ?array
    {
        if (! $this->tablesReady()) {
            return null;
        }

        $row = $this->db->table('hr_payroll_runs r')
            ->select('r.*, fb.name AS branch_name')
            ->join('finance_branches fb', 'fb.id = r.branch_id', 'left')
            ->where('r.id', $id)
            ->get()->getRowArray();

        return $row ?: null;
    }

    /** @return list<array<string, mixed>> */
    public function linesForRun(int $runId): array
    {
        if (! $this->db->tableExists('hr_payroll_lines')) {
            return [];
        }

        return $this->db->table('hr_payroll_lines')
            ->where('payroll_run_id', $runId)
            ->orderBy('employee_name')
            ->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function componentsForLine(int $lineId): array
    {
        if (! $this->db->tableExists('hr_payroll_line_components')) {
            return [];
        }

        return $this->db->table('hr_payroll_line_components')
            ->where('payroll_line_id', $lineId)
            ->orderBy('sort_order')
            ->get()->getResultArray();
    }

    /** @param array<string, mixed> $data */
    public function createRun(array $data, int $userId): int
    {
        if (! $this->tablesReady()) {
            throw new \RuntimeException('Payroll tables missing.');
        }

        $periodStart = $data['period_start'] ?? date('Y-m-01');
        $periodEnd   = $data['period_end'] ?? date('Y-m-t', strtotime($periodStart));

        $payload = [
            'run_number'       => $this->nextRunNumber(),
            'company_id'       => $data['company_id'] ?? null,
            'branch_id'        => $data['branch_id'] ?? null,
            'payroll_group_id' => $data['payroll_group_id'] ?? null,
            'period_start'     => $periodStart,
            'period_end'       => $periodEnd,
            'pay_date'         => $data['pay_date'] ?? $periodEnd,
            'currency'         => $data['currency'] ?? 'QAR',
            'status'           => 'draft',
            'created_by'       => $userId,
        ];

        helper('fm');
        if (function_exists('fm_insert_row_id')) {
            return fm_insert_row_id($this->db, 'hr_payroll_runs', $payload);
        }

        $this->db->table('hr_payroll_runs')->insert($payload);

        return (int) $this->db->insertID();
    }

    public function calculateRun(int $runId): array
    {
        $run = $this->findRun($runId);
        if (! $run || in_array($run['status'], ['locked', 'posted', 'cancelled'], true)) {
            throw new \RuntimeException('Payroll run cannot be calculated in current status.');
        }

        $this->clearRunLines($runId);

        $salaryService = new HrSalaryService($this->db);
        $loanService   = new HrAdvanceLoanService($this->db);

        $employees = $this->eligibleEmployees(
            (int) ($run['company_id'] ?? 0) ?: null,
            (int) ($run['branch_id'] ?? 0) ?: null
        );

        $errors   = [];
        $warnings = [];
        $totGross = 0.0;
        $totDed   = 0.0;
        $totNet   = 0.0;
        $count    = 0;

        foreach ($employees as $emp) {
            $result = $this->calculateEmployeeLine($run, $emp, $salaryService, $loanService);
            if ($result['status'] === 'error') {
                $errors[] = $result['message'];
            } elseif ($result['status'] === 'warning') {
                $warnings[] = $result['message'];
            }

            if ($result['line_id']) {
                $count++;
                $totGross += (float) $result['gross'];
                $totDed   += (float) $result['deductions'];
                $totNet   += (float) $result['net'];
            }
        }

        $summary = json_encode([
            'errors'   => $errors,
            'warnings' => $warnings,
            'included' => $count,
        ]);

        $this->db->table('hr_payroll_runs')->where('id', $runId)->update([
            'employee_count'     => $count,
            'total_gross'        => round($totGross, 2),
            'total_deductions'   => round($totDed, 2),
            'total_net'          => round($totNet, 2),
            'status'             => 'calculated',
            'validation_summary' => $summary,
            'updated_at'         => date('Y-m-d H:i:s'),
        ]);

        return [
            'employee_count' => $count,
            'total_gross'    => round($totGross, 2),
            'total_deductions' => round($totDed, 2),
            'total_net'      => round($totNet, 2),
            'errors'         => $errors,
            'warnings'       => $warnings,
        ];
    }

    public function approveRun(int $runId, int $userId): bool
    {
        $run = $this->findRun($runId);
        if (! $run || $run['status'] !== 'calculated') {
            return false;
        }

        return $this->db->table('hr_payroll_runs')->where('id', $runId)->update([
            'status'      => 'approved',
            'approved_by' => $userId,
            'approved_at' => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function lockRun(int $runId, int $userId, ?string $reason = null): bool
    {
        $run = $this->findRun($runId);
        if (! $run || $run['status'] !== 'approved') {
            return false;
        }

        $this->db->table('hr_payroll_runs')->where('id', $runId)->update([
            'status'     => 'locked',
            'locked_at'  => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->recordLockAction($runId, 'lock', $userId, $reason);

        return true;
    }

    public function unlockRun(int $runId, int $userId, string $reason): bool
    {
        $run = $this->findRun($runId);
        if (! $run || ! in_array($run['status'], ['locked', 'posted'], true)) {
            return false;
        }

        $this->db->table('hr_payroll_runs')->where('id', $runId)->update([
            'status'            => 'approved',
            'locked_at'         => null,
            'posted_at'         => null,
            'journal_entry_id'  => null,
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);

        $this->recordLockAction($runId, 'unlock', $userId, $reason);

        return true;
    }

    public function postToGl(int $runId, int $userId): ?int
    {
        $run = $this->findRun($runId);
        if (! $run || $run['status'] !== 'locked') {
            return null;
        }

        $net = (float) $run['total_net'];
        if ($net <= 0) {
            return null;
        }

        $gl = new GlPostingService($this->db);
        $jid = $gl->postPayrollFinalized($runId, $net, $userId, (int) ($run['company_id'] ?? 0) ?: null);
        if (! $jid) {
            return null;
        }

        $this->db->table('hr_payroll_runs')->where('id', $runId)->update([
            'status'           => 'posted',
            'journal_entry_id' => $jid,
            'posted_at'        => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);

        return $jid;
    }

    public function cancelRun(int $runId): bool
    {
        $run = $this->findRun($runId);
        if (! $run || in_array($run['status'], ['locked', 'posted'], true)) {
            return false;
        }

        return $this->db->table('hr_payroll_runs')->where('id', $runId)->update([
            'status'     => 'cancelled',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /** @return list<array<string, mixed>> */
    private function eligibleEmployees(?int $companyId, ?int $branchId): array
    {
        $q = $this->db->table('employees e')
            ->select('e.*, u.name AS employee_name')
            ->join('users u', 'u.id = e.user_id', 'left')
            ->where('e.status', 'active')
            ->where('e.payroll_applicable', 1)
            ->where('e.payroll_responsibility', 'our_company');

        if ($companyId) {
            $q->where('e.company_id', $companyId);
        }
        if ($branchId) {
            $q->where('e.operating_company_id', $branchId);
        }

        return $q->orderBy('u.name')->get()->getResultArray();
    }

    /** @param array<string, mixed> $run */
    /** @param array<string, mixed> $emp */
    /** @return array{line_id: int|null, status: string, message: string, gross: float, deductions: float, net: float} */
    private function calculateEmployeeLine(array $run, array $emp, HrSalaryService $salaryService, HrAdvanceLoanService $loanService): array
    {
        $structure = $salaryService->currentStructure((int) $emp['id']);
        if (! $structure || empty($structure['lines'])) {
            return $this->insertErrorLine($run, $emp, 'No active salary structure.');
        }

        $components = [];
        $earnings   = 0.0;
        $deductions = 0.0;
        $sort       = 1;

        foreach ($structure['lines'] as $line) {
            $amount = (float) $line['amount'];
            $type   = ($line['component_type'] ?? '') === 'deduction' ? 'deduction' : 'earning';
            if ($type === 'deduction') {
                $deductions += $amount;
            } else {
                $earnings += $amount;
            }
            $components[] = [
                'component_id'   => (int) $line['component_id'],
                'component_code' => $line['code'] ?? null,
                'component_name' => $line['name'] ?? null,
                'component_type' => $type,
                'amount'         => $amount,
                'source_type'    => 'salary_structure',
                'source_id'      => (int) $structure['id'],
                'sort_order'     => $sort++,
            ];
        }

        $advDed = $this->advanceDeduction((int) $emp['id'], $run['period_end']);
        if ($advDed > 0) {
            $deductions += $advDed;
            $components[] = [
                'component_id'   => null,
                'component_code' => 'ADV_DED',
                'component_name' => 'Advance Deduction',
                'component_type' => 'deduction',
                'amount'         => $advDed,
                'source_type'    => 'advance',
                'source_id'      => null,
                'sort_order'     => $sort++,
            ];
        }

        $loanDed = $this->loanDeduction((int) $emp['id'], $run['period_end']);
        if ($loanDed > 0) {
            $deductions += $loanDed;
            $components[] = [
                'component_id'   => null,
                'component_code' => 'LOAN_DED',
                'component_name' => 'Loan Deduction',
                'component_type' => 'deduction',
                'amount'         => $loanDed,
                'source_type'    => 'loan',
                'source_id'      => null,
                'sort_order'     => $sort++,
            ];
        }

        $gross = round($earnings, 2);
        $ded   = round($deductions, 2);
        $net   = round(max(0, $gross - $ded), 2);

        $linePayload = [
            'payroll_run_id'   => (int) $run['id'],
            'employee_id'      => (int) $emp['id'],
            'structure_id'     => (int) $structure['id'],
            'emp_code'         => $emp['emp_code'] ?? null,
            'employee_name'    => $emp['employee_name'] ?? $emp['name'] ?? null,
            'iban'             => $emp['iban'] ?? null,
            'wps_applicable'   => ! empty($emp['wps_applicable']) ? 1 : 0,
            'working_days'     => $this->workingDaysInPeriod($run['period_start'], $run['period_end']),
            'paid_days'        => $this->workingDaysInPeriod($run['period_start'], $run['period_end']),
            'gross_salary'     => $gross,
            'total_earnings'   => $gross,
            'total_deductions' => $ded,
            'net_salary'       => $net,
            'status'           => 'included',
        ];

        helper('fm');
        if (function_exists('fm_insert_row_id')) {
            $lineId = fm_insert_row_id($this->db, 'hr_payroll_lines', $linePayload);
        } else {
            $this->db->table('hr_payroll_lines')->insert($linePayload);
            $lineId = (int) $this->db->insertID();
        }

        foreach ($components as $c) {
            $c['payroll_line_id'] = $lineId;
            $this->db->table('hr_payroll_line_components')->insert($c);
        }

        $this->allocateLineCost($lineId, $emp, $net);

        $status  = 'ok';
        $message = '';
        if (empty($emp['iban']) && ! empty($emp['wps_applicable'])) {
            $status  = 'warning';
            $message = ($emp['emp_code'] ?? $emp['id']) . ': WPS applicable but IBAN missing.';
        }

        return [
            'line_id'    => $lineId,
            'status'     => $status,
            'message'    => $message,
            'gross'      => $gross,
            'deductions' => $ded,
            'net'        => $net,
        ];
    }

    /** @param array<string, mixed> $emp */
    /** @return array{line_id: int|null, status: string, message: string, gross: float, deductions: float, net: float} */
    private function insertErrorLine(array $run, array $emp, string $msg): array
    {
        $payload = [
            'payroll_run_id' => (int) $run['id'],
            'employee_id'    => (int) $emp['id'],
            'emp_code'       => $emp['emp_code'] ?? null,
            'employee_name'  => $emp['employee_name'] ?? null,
            'status'         => 'error',
            'error_message'  => $msg,
        ];
        $this->db->table('hr_payroll_lines')->insert($payload);

        return [
            'line_id'    => null,
            'status'     => 'error',
            'message'    => ($emp['emp_code'] ?? $emp['id']) . ': ' . $msg,
            'gross'      => 0,
            'deductions' => 0,
            'net'        => 0,
        ];
    }

    private function advanceDeduction(int $employeeId, string $periodEnd): float
    {
        if (! $this->db->tableExists('hr_salary_advances')) {
            return 0.0;
        }

        $rows = $this->db->table('hr_salary_advances')
            ->where('employee_id', $employeeId)
            ->where('status', 'active')
            ->where('balance >', 0)
            ->get()->getResultArray();

        $total = 0.0;
        foreach ($rows as $row) {
            if (! empty($row['recovery_start_date']) && $row['recovery_start_date'] > $periodEnd) {
                continue;
            }
            $total += min((float) ($row['installment_amount'] ?? 0), (float) $row['balance']);
        }

        return round($total, 2);
    }

    private function loanDeduction(int $employeeId, string $periodEnd): float
    {
        if (! $this->db->tableExists('hr_loan_installments') || ! $this->db->tableExists('hr_employee_loans')) {
            return 0.0;
        }

        $monthStart = date('Y-m-01', strtotime($periodEnd));
        $monthEnd   = date('Y-m-t', strtotime($periodEnd));

        $row = $this->db->table('hr_loan_installments i')
            ->select('i.total_amount')
            ->join('hr_employee_loans l', 'l.id = i.loan_id')
            ->where('l.employee_id', $employeeId)
            ->where('l.status', 'active')
            ->where('i.status', 'pending')
            ->where('i.due_date >=', $monthStart)
            ->where('i.due_date <=', $monthEnd)
            ->orderBy('i.due_date', 'ASC')
            ->limit(1)
            ->get()->getRowArray();

        return $row ? round((float) $row['total_amount'], 2) : 0.0;
    }

    private function workingDaysInPeriod(string $start, string $end): float
    {
        $days = 0;
        $cur  = strtotime($start);
        $endTs = strtotime($end);
        while ($cur <= $endTs) {
            $dow = (int) date('N', $cur);
            if ($dow < 6) {
                $days++;
            }
            $cur = strtotime('+1 day', $cur);
        }

        return (float) $days;
    }

    /** @param array<string, mixed> $emp */
    private function allocateLineCost(int $lineId, array $emp, float $net): void
    {
        if (! $this->db->tableExists('hr_payroll_allocations')) {
            return;
        }

        $costCenterId = ! empty($emp['cost_center_id']) ? (int) $emp['cost_center_id'] : null;
        $facilityId   = ! empty($emp['facility_id']) ? (int) $emp['facility_id'] : null;
        if (! $costCenterId && ! $facilityId) {
            return;
        }

        $this->db->table('hr_payroll_allocations')->insert([
            'payroll_line_id'  => $lineId,
            'cost_center_id'   => $costCenterId,
            'facility_id'      => $facilityId,
            'allocation_pct'   => 100,
            'amount'           => $net,
        ]);
    }

    private function clearRunLines(int $runId): void
    {
        $lineIds = array_column(
            $this->db->table('hr_payroll_lines')->select('id')->where('payroll_run_id', $runId)->get()->getResultArray(),
            'id'
        );

        if ($lineIds && $this->db->tableExists('hr_payroll_line_components')) {
            $this->db->table('hr_payroll_line_components')->whereIn('payroll_line_id', $lineIds)->delete();
        }
        if ($lineIds && $this->db->tableExists('hr_payroll_allocations')) {
            $this->db->table('hr_payroll_allocations')->whereIn('payroll_line_id', $lineIds)->delete();
        }

        $this->db->table('hr_payroll_lines')->where('payroll_run_id', $runId)->delete();
    }

    private function recordLockAction(int $runId, string $action, int $userId, ?string $reason): void
    {
        if (! $this->db->tableExists('hr_payroll_locks')) {
            return;
        }

        $this->db->table('hr_payroll_locks')->insert([
            'payroll_run_id' => $runId,
            'action'         => $action,
            'reason'         => $reason,
            'performed_by'   => $userId,
        ]);
    }

    private function nextRunNumber(): string
    {
        $n = (int) $this->db->table('hr_payroll_runs')->countAllResults() + 1;

        return 'PR-' . date('Ym') . '-' . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
    }

    public function glAccountCode(string $key, ?int $companyId = null): string
    {
        $defaults = [
            'gl_payroll_expense'  => '5300',
            'gl_salary_payable'   => '2310',
            'gl_employee_advance' => '1310',
            'gl_employee_loan'    => '1315',
            'gl_bank'             => '1200',
        ];

        if (! $this->db->tableExists('hr_settings')) {
            return $defaults[$key] ?? '5300';
        }

        $q = $this->db->table('hr_settings')->where('setting_key', $key);
        if ($companyId) {
            $q->groupStart()->where('company_id', $companyId)->orWhere('company_id', null)->groupEnd();
        }
        $row = $q->orderBy('company_id', 'DESC')->limit(1)->get()->getRowArray();

        return $row['setting_value'] ?? ($defaults[$key] ?? '5300');
    }
}
