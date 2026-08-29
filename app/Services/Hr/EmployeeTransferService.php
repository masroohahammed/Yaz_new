<?php

namespace App\Services\Hr;

use CodeIgniter\Database\BaseConnection;

class EmployeeTransferService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function tablesReady(): bool
    {
        return $this->db->tableExists('hr_employee_transfers');
    }

    /** @param array<string, mixed> $data */
    public function submit(array $data, int $userId): int
    {
        if (! $this->tablesReady()) {
            throw new \RuntimeException('Transfer tables missing.');
        }

        $emp = (new EmployeeService($this->db))->find((int) $data['employee_id']);
        if (! $emp) {
            throw new \RuntimeException('Employee not found.');
        }

        $payload = [
            'request_number'            => 'TR-' . date('Y') . '-' . random_int(1000, 9999),
            'employee_id'               => (int) $data['employee_id'],
            'company_id'                => $emp['company_id'] ?? null,
            'from_department_id'        => $emp['department_id'] ?? null,
            'to_department_id'          => (int) ($data['to_department_id'] ?? 0) ?: null,
            'from_facility_id'          => $emp['facility_id'] ?? null,
            'to_facility_id'            => (int) ($data['to_facility_id'] ?? 0) ?: null,
            'from_operating_company_id' => $emp['operating_company_id'] ?? null,
            'to_operating_company_id'   => (int) ($data['to_operating_company_id'] ?? 0) ?: null,
            'from_reporting_manager_id' => $emp['reporting_manager_id'] ?? null,
            'to_reporting_manager_id'   => (int) ($data['to_reporting_manager_id'] ?? 0) ?: null,
            'effective_date'            => $data['effective_date'] ?? date('Y-m-d'),
            'reason'                    => $data['reason'] ?? null,
            'status'                    => 'pending',
            'requested_by'              => $userId,
        ];

        helper('fm');
        if (function_exists('fm_insert_row_id')) {
            $transferId = fm_insert_row_id($this->db, 'hr_employee_transfers', $payload);
        } else {
            $this->db->table('hr_employee_transfers')->insert($payload);
            $transferId = (int) $this->db->insertID();
        }

        $approval = new ApprovalWorkflowService($this->db);
        if ($approval->tablesReady()) {
            $approvalId = $approval->submitRequest([
                'employee_id'  => (int) $data['employee_id'],
                'company_id'   => $emp['company_id'] ?? null,
                'module'       => 'transfer',
                'request_type' => 'org_transfer',
                'source_table' => 'hr_employee_transfers',
                'source_id'    => $transferId,
                'title'        => 'Transfer request ' . $payload['request_number'],
                'description'  => $payload['reason'],
            ], $userId);

            $this->db->table('hr_employee_transfers')->where('id', $transferId)->update(['approval_request_id' => $approvalId]);
        }

        return $transferId;
    }

    /** @return list<array<string, mixed>> */
    public function pending(?int $companyId = null): array
    {
        if (! $this->tablesReady()) {
            return [];
        }

        $q = $this->db->table('hr_employee_transfers t')
            ->select('t.*, u.name AS employee_name, e.emp_code')
            ->join('employees e', 'e.id = t.employee_id', 'left')
            ->join('users u', 'u.id = e.user_id', 'left')
            ->where('t.status', 'pending');

        if ($companyId) {
            $q->where('t.company_id', $companyId);
        }

        return $q->orderBy('t.created_at', 'ASC')->get()->getResultArray();
    }

    public function approve(int $transferId, int $userId, ?string $notes = null): bool
    {
        $row = $this->find($transferId);
        if (! $row || $row['status'] !== 'pending') {
            return false;
        }

        if (! empty($row['approval_request_id'])) {
            $approval = new ApprovalWorkflowService($this->db);
            if (! $approval->approveStep((int) $row['approval_request_id'], $userId, $notes)) {
                return false;
            }
            $updated = $approval->findApprovalRequest((int) $row['approval_request_id']);
            if (($updated['status'] ?? '') !== 'approved') {
                return true;
            }
        }

        return $this->execute($transferId, $userId, $notes);
    }

    public function reject(int $transferId, int $userId, ?string $notes = null): bool
    {
        $row = $this->find($transferId);
        if (! $row || $row['status'] !== 'pending') {
            return false;
        }

        if (! empty($row['approval_request_id'])) {
            (new ApprovalWorkflowService($this->db))->rejectRequest((int) $row['approval_request_id'], $userId, $notes);
        }

        return $this->db->table('hr_employee_transfers')->where('id', $transferId)->update([
            'status'       => 'rejected',
            'reviewed_by'  => $userId,
            'reviewed_at'  => date('Y-m-d H:i:s'),
            'review_notes' => $notes,
        ]);
    }

    public function execute(int $transferId, int $userId, ?string $notes = null): bool
    {
        $row = $this->find($transferId);
        if (! $row) {
            return false;
        }

        $patch = ['updated_by' => $userId];
        if ($row['to_department_id']) {
            $patch['department_id'] = $row['to_department_id'];
        }
        if ($row['to_facility_id']) {
            $patch['facility_id'] = $row['to_facility_id'];
        }
        if ($row['to_operating_company_id']) {
            $patch['operating_company_id'] = $row['to_operating_company_id'];
        }
        if ($row['to_reporting_manager_id']) {
            $patch['reporting_manager_id'] = $row['to_reporting_manager_id'];
        }

        (new EmployeeService($this->db))->update((int) $row['employee_id'], $patch, $userId);

        $this->db->table('hr_employee_transfers')->where('id', $transferId)->update([
            'status'       => 'completed',
            'reviewed_by'  => $userId,
            'reviewed_at'  => date('Y-m-d H:i:s'),
            'review_notes' => $notes,
        ]);

        (new EmployeeTimelineService($this->db))->record(
            (int) $row['employee_id'],
            'transfer',
            'Organizational transfer completed',
            'completed',
            $row['reason'] ?? null,
            'transfer',
            $transferId,
            null,
            $userId
        );

        return true;
    }

    public function find(int $id): ?array
    {
        $row = $this->db->table('hr_employee_transfers')->where('id', $id)->get()->getRowArray();

        return $row ?: null;
    }
}
