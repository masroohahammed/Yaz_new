<?php

namespace App\Services\Hr;

use CodeIgniter\Database\BaseConnection;

class HrWpsService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function tablesReady(): bool
    {
        return $this->db->tableExists('hr_wps_batches')
            && $this->db->tableExists('hr_wps_records');
    }

    /** @return list<array<string, mixed>> */
    public function listBatches(?int $companyId = null, int $limit = 30): array
    {
        if (! $this->tablesReady()) {
            return [];
        }

        $q = $this->db->table('hr_wps_batches b')
            ->select('b.*, r.run_number, fb.name AS branch_name')
            ->join('hr_payroll_runs r', 'r.id = b.payroll_run_id', 'left')
            ->join('finance_branches fb', 'fb.id = b.branch_id', 'left')
            ->orderBy('b.created_at', 'DESC')
            ->limit($limit);

        if ($companyId) {
            $q->where('r.company_id', $companyId);
        }

        return $q->get()->getResultArray();
    }

    public function findBatch(int $id): ?array
    {
        if (! $this->tablesReady()) {
            return null;
        }

        $row = $this->db->table('hr_wps_batches b')
            ->select('b.*, r.run_number, r.status AS payroll_status')
            ->join('hr_payroll_runs r', 'r.id = b.payroll_run_id', 'left')
            ->where('b.id', $id)
            ->get()->getRowArray();

        return $row ?: null;
    }

    /** @return list<array<string, mixed>> */
    public function recordsForBatch(int $batchId): array
    {
        if (! $this->db->tableExists('hr_wps_records')) {
            return [];
        }

        return $this->db->table('hr_wps_records')
            ->where('batch_id', $batchId)
            ->orderBy('employee_name')
            ->get()->getResultArray();
    }

    public function settingsForBranch(int $branchId): ?array
    {
        if (! $this->db->tableExists('hr_wps_settings')) {
            return null;
        }

        $row = $this->db->table('hr_wps_settings')->where('branch_id', $branchId)->where('is_active', 1)->get()->getRowArray();

        return $row ?: null;
    }

    /** @return array{valid: bool, errors: list<string>} */
    public function validateRunForWps(int $runId): array
    {
        $payroll = new HrPayrollService($this->db);
        $run     = $payroll->findRun($runId);
        $errors  = [];

        if (! $run) {
            return ['valid' => false, 'errors' => ['Payroll run not found.']];
        }

        if (! in_array($run['status'], ['locked', 'posted'], true)) {
            $errors[] = 'Payroll must be locked before WPS generation.';
        }

        $branchId = (int) ($run['branch_id'] ?? 0);
        if ($branchId < 1) {
            $errors[] = 'Payroll run has no operating branch.';
        } elseif ($this->db->tableExists('finance_branches')) {
            $branch = $this->db->table('finance_branches')->where('id', $branchId)->get()->getRowArray();
            if (empty($branch['wps_enabled'])) {
                $errors[] = 'WPS is not enabled for this branch.';
            }
            if (empty($branch['establishment_id'])) {
                $errors[] = 'Branch establishment ID is missing.';
            }
        }

        $settings = $branchId ? $this->settingsForBranch($branchId) : null;
        if (! $settings) {
            $errors[] = 'WPS settings not configured for branch.';
        }

        return ['valid' => $errors === [], 'errors' => $errors];
    }

    public function generateBatch(int $runId, int $userId): int
    {
        $validation = $this->validateRunForWps($runId);
        if (! $validation['valid']) {
            throw new \RuntimeException(implode(' ', $validation['errors']));
        }

        $payroll = new HrPayrollService($this->db);
        $run     = $payroll->findRun($runId);
        if (! $run) {
            throw new \RuntimeException('Payroll run not found.');
        }

        $branchId = (int) $run['branch_id'];
        $lines    = $this->db->table('hr_payroll_lines pl')
            ->select('pl.*, e.qid_number')
            ->join('employees e', 'e.id = pl.employee_id', 'left')
            ->where('pl.payroll_run_id', $runId)
            ->where('pl.status', 'included')
            ->where('pl.wps_applicable', 1)
            ->where('pl.net_salary >', 0)
            ->get()->getResultArray();

        $batchNo = 'WPS-' . date('Ym') . '-' . str_pad((string) ((int) $this->db->table('hr_wps_batches')->countAllResults() + 1), 4, '0', STR_PAD_LEFT);

        $payload = [
            'batch_number'  => $batchNo,
            'payroll_run_id'=> $runId,
            'branch_id'     => $branchId,
            'period_start'  => $run['period_start'],
            'period_end'    => $run['period_end'],
            'status'        => 'draft',
            'generated_by'  => $userId,
        ];

        helper('fm');
        if (function_exists('fm_insert_row_id')) {
            $batchId = fm_insert_row_id($this->db, 'hr_wps_batches', $payload);
        } else {
            $this->db->table('hr_wps_batches')->insert($payload);
            $batchId = (int) $this->db->insertID();
        }

        $total   = 0.0;
        $count   = 0;
        $records = [];

        foreach ($lines as $line) {
            $valid = ! empty($line['iban']);
            $msg   = $valid ? null : 'Missing IBAN';

            $this->db->table('hr_wps_records')->insert([
                'batch_id'           => $batchId,
                'payroll_line_id'    => (int) $line['id'],
                'employee_id'        => (int) $line['employee_id'],
                'emp_code'           => $line['emp_code'],
                'employee_name'      => $line['employee_name'],
                'iban'               => $line['iban'],
                'qid_number'         => $line['qid_number'] ?? null,
                'amount'             => (float) $line['net_salary'],
                'status'             => $valid ? 'valid' : 'invalid',
                'validation_message' => $msg,
            ]);

            if ($valid) {
                $total += (float) $line['net_salary'];
                $count++;
                $records[] = $line;
            }
        }

        $fileContent = $this->buildFileContent($branchId, $run, $records);
        $fileName    = $batchNo . '.csv';

        $this->db->table('hr_wps_batches')->where('id', $batchId)->update([
            'record_count'  => $count,
            'total_amount'  => round($total, 2),
            'status'        => $count > 0 ? 'generated' : 'failed',
            'file_name'     => $fileName,
            'file_content'  => $fileContent,
            'generated_at'  => date('Y-m-d H:i:s'),
        ]);

        return $batchId;
    }

    /** @param list<array<string, mixed>> $lines */
    private function buildFileContent(int $branchId, array $run, array $lines): string
    {
        $settings = $this->settingsForBranch($branchId) ?? [];
        $branch   = $this->db->tableExists('finance_branches')
            ? $this->db->table('finance_branches')->where('id', $branchId)->get()->getRowArray()
            : [];

        $header = [
            'EmployerEID',
            'PayerEID',
            'PayerIBAN',
            'SalaryMonth',
            'EmployeeQID',
            'EmployeeIBAN',
            'NetSalary',
            'EmployeeName',
        ];

        $rows = [implode(',', $header)];
        $salaryMonth = date('mY', strtotime($run['period_end']));

        foreach ($lines as $line) {
            $rows[] = implode(',', [
                $this->csv($branch['establishment_id'] ?? $settings['employer_eid'] ?? ''),
                $this->csv($settings['payer_eid'] ?? $branch['establishment_id'] ?? ''),
                $this->csv($settings['payer_iban'] ?? ''),
                $this->csv($salaryMonth),
                $this->csv($line['qid_number'] ?? ''),
                $this->csv($line['iban'] ?? ''),
                number_format((float) $line['net_salary'], 2, '.', ''),
                $this->csv($line['employee_name'] ?? ''),
            ]);
        }

        return implode("\n", $rows);
    }

    private function csv(?string $value): string
    {
        $value = (string) ($value ?? '');

        return str_contains($value, ',') ? '"' . str_replace('"', '""', $value) . '"' : $value;
    }
}
