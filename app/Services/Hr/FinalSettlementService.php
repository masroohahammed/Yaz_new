<?php

namespace App\Services\Hr;

use CodeIgniter\Database\BaseConnection;

class FinalSettlementService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function tablesReady(): bool
    {
        return $this->db->tableExists('hr_final_settlements');
    }

    public function find(int $id): ?array
    {
        if (! $this->tablesReady()) {
            return null;
        }

        $row = $this->db->table('hr_final_settlements')->where('id', $id)->get()->getRowArray();

        return $row ?: null;
    }

    public function forEmployee(int $employeeId): ?array
    {
        if (! $this->tablesReady()) {
            return null;
        }

        $row = $this->db->table('hr_final_settlements')->where('employee_id', $employeeId)->orderBy('id', 'DESC')->limit(1)->get()->getRowArray();

        return $row ?: null;
    }

    /** @return list<array<string, mixed>> */
    public function lines(int $settlementId): array
    {
        if (! $this->db->tableExists('hr_final_settlement_lines')) {
            return [];
        }

        return $this->db->table('hr_final_settlement_lines')->where('settlement_id', $settlementId)->orderBy('sort_order')->get()->getResultArray();
    }

    public function calculate(int $employeeId, string $lastWorkingDate, int $userId): int
    {
        if (! $this->tablesReady()) {
            throw new \RuntimeException('Settlement tables missing.');
        }

        $emp = (new EmployeeService($this->db))->find($employeeId);
        if (! $emp) {
            throw new \RuntimeException('Employee not found.');
        }

        $salaryService = new HrSalaryService($this->db);
        $structure     = $salaryService->currentStructure($employeeId);
        $earnings      = 0.0;
        $deductions    = 0.0;
        $lines         = [];

        if ($structure) {
            $proRata = $this->proRataFactor($lastWorkingDate);
            foreach ($structure['lines'] ?? [] as $i => $line) {
                $amount = round((float) $line['amount'] * $proRata, 2);
                $type   = ($line['component_type'] ?? '') === 'deduction' ? 'deduction' : 'earning';
                if ($type === 'deduction') {
                    $deductions += $amount;
                } else {
                    $earnings += $amount;
                }
                $lines[] = [
                    'component_code' => $line['code'] ?? null,
                    'component_name' => $line['name'] ?? 'Component',
                    'component_type' => $type,
                    'amount'         => $amount,
                    'source_type'    => 'salary_structure',
                    'source_id'      => (int) $structure['id'],
                    'sort_order'     => $i + 1,
                ];
            }
        }

        $loanService = new HrAdvanceLoanService($this->db);
        if ($loanService->advancesReady()) {
            foreach ($loanService->advancesForEmployee($employeeId) as $adv) {
                if ($adv['status'] === 'active' && (float) $adv['balance'] > 0) {
                    $amt = (float) $adv['balance'];
                    $deductions += $amt;
                    $lines[] = [
                        'component_code' => 'ADV_BAL',
                        'component_name' => 'Outstanding Advance',
                        'component_type' => 'deduction',
                        'amount'         => $amt,
                        'source_type'    => 'advance',
                        'source_id'      => (int) $adv['id'],
                        'sort_order'     => 100,
                    ];
                }
            }
        }

        $net = round(max(0, $earnings - $deductions), 2);

        helper('fm');
        $payload = [
            'settlement_number'  => 'FS-' . date('Y') . '-' . random_int(1000, 9999),
            'employee_id'        => $employeeId,
            'employment_period_id' => $emp['current_employment_period_id'] ?? null,
            'company_id'         => $emp['company_id'] ?? null,
            'last_working_date'  => $lastWorkingDate,
            'status'             => 'calculated',
            'total_earnings'     => round($earnings, 2),
            'total_deductions'   => round($deductions, 2),
            'net_payable'        => $net,
            'calculated_by'      => $userId,
        ];

        if (function_exists('fm_insert_row_id')) {
            $settlementId = fm_insert_row_id($this->db, 'hr_final_settlements', $payload);
        } else {
            $this->db->table('hr_final_settlements')->insert($payload);
            $settlementId = (int) $this->db->insertID();
        }

        foreach ($lines as $line) {
            $line['settlement_id'] = $settlementId;
            $this->db->table('hr_final_settlement_lines')->insert($line);
        }

        if ((new ApprovalWorkflowService($this->db))->tablesReady()) {
            $approvalId = (new ApprovalWorkflowService($this->db))->submitRequest([
                'employee_id'  => $employeeId,
                'company_id'   => $emp['company_id'] ?? null,
                'module'       => 'settlement',
                'request_type' => 'final',
                'source_table' => 'hr_final_settlements',
                'source_id'    => $settlementId,
                'title'        => 'Final settlement ' . $payload['settlement_number'],
                'amount'       => $net,
            ], $userId);

            $this->db->table('hr_final_settlements')->where('id', $settlementId)->update([
                'approval_request_id' => $approvalId,
                'status'              => 'pending_approval',
            ]);
        }

        (new EmployeeTimelineService($this->db))->record(
            $employeeId,
            'settlement',
            'Final settlement calculated',
            'calculated',
            'Net payable: ' . number_format($net, 2),
            'settlement',
            $settlementId,
            null,
            $userId
        );

        return $settlementId;
    }

    public function approve(int $settlementId, int $userId): bool
    {
        $row = $this->find($settlementId);
        if (! $row || ! in_array($row['status'], ['calculated', 'pending_approval'], true)) {
            return false;
        }

        if (! empty($row['approval_request_id'])) {
            (new ApprovalWorkflowService($this->db))->approveStep((int) $row['approval_request_id'], $userId);
        }

        return $this->db->table('hr_final_settlements')->where('id', $settlementId)->update([
            'status'      => 'approved',
            'approved_by' => $userId,
            'approved_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function proRataFactor(string $lastWorkingDate): float
    {
        $day   = (int) date('j', strtotime($lastWorkingDate));
        $total = (int) date('t', strtotime($lastWorkingDate));

        return $total > 0 ? min(1, $day / $total) : 1;
    }
}
