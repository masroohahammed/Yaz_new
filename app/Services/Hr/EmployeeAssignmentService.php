<?php

namespace App\Services\Hr;

use CodeIgniter\Database\BaseConnection;

class EmployeeAssignmentService
{
    public const TYPES = [
        'primary'     => 'Primary Site',
        'secondary'   => 'Secondary Site',
        'temporary'   => 'Temporary',
        'project'     => 'Project',
        'replacement' => 'Replacement',
    ];

    public const STATUSES = [
        'draft'       => 'Draft',
        'active'      => 'Active',
        'completed'   => 'Completed',
        'cancelled'   => 'Cancelled',
        'transferred' => 'Transferred',
    ];

    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function tablesReady(): bool
    {
        return $this->db->tableExists('hr_employee_assignments');
    }

    /** @return list<array<string, mixed>> */
    public function forEmployee(int $employeeId, bool $includeHistory = true): array
    {
        if (! $this->tablesReady()) {
            return [];
        }

        $q = $this->db->table('hr_employee_assignments a')
            ->select('a.*, f.name AS facility_name, u.name AS unit_name, c.contract_number')
            ->join('facilities f', 'f.id = a.facility_id', 'left')
            ->join('units u', 'u.id = a.unit_id', 'left')
            ->join('hr_employment_contracts c', 'c.id = a.contract_id', 'left')
            ->where('a.employee_id', $employeeId)
            ->orderBy('a.start_date', 'DESC');

        if (! $includeHistory) {
            $q->where('a.is_current', 1);
        }

        return $q->get()->getResultArray();
    }

    public function find(int $id): ?array
    {
        if (! $this->tablesReady()) {
            return null;
        }

        $row = $this->db->table('hr_employee_assignments a')
            ->select('a.*, f.name AS facility_name, e.emp_code, eu.name AS employee_name')
            ->join('facilities f', 'f.id = a.facility_id', 'left')
            ->join('employees e', 'e.id = a.employee_id', 'left')
            ->join('users eu', 'eu.id = e.user_id', 'left')
            ->where('a.id', $id)
            ->get()->getRowArray();

        return $row ?: null;
    }

    /** @param array<string, mixed> $data */
    public function create(array $data, int $userId): int
    {
        if (! $this->tablesReady()) {
            throw new \RuntimeException('Assignment tables missing.');
        }

        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;
        $data['is_current'] = $data['is_current'] ?? 1;
        if (empty($data['assignment_status'])) {
            $data['assignment_status'] = 'active';
        }

        if (! empty($data['is_current'])) {
            $this->clearCurrentFlag((int) $data['employee_id'], (string) ($data['assignment_type'] ?? 'primary'));
        }

        helper('fm');
        if (function_exists('fm_insert_row_id')) {
            $id = fm_insert_row_id($this->db, 'hr_employee_assignments', $data);
        } else {
            $this->db->table('hr_employee_assignments')->insert($data);
            $id = (int) $this->db->insertID();
        }

        $this->writeHistory($id, (int) $data['employee_id'], 'created', $userId, $data);
        $this->syncEmployeePrimaryFacility((int) $data['employee_id']);

        return $id;
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data, int $userId): bool
    {
        $existing = $this->find($id);
        if (! $existing) {
            return false;
        }

        $this->writeHistory($id, (int) $existing['employee_id'], 'updated', $userId, $existing);
        $data['updated_by'] = $userId;

        if (! empty($data['is_current']) && empty($existing['is_current'])) {
            $this->clearCurrentFlag((int) $existing['employee_id'], (string) ($data['assignment_type'] ?? $existing['assignment_type']));
        }

        $ok = $this->db->table('hr_employee_assignments')->where('id', $id)->update($data);
        $this->syncEmployeePrimaryFacility((int) $existing['employee_id']);

        return $ok;
    }

    public function endAssignment(int $id, ?string $endDate, int $userId): bool
    {
        $existing = $this->find($id);
        if (! $existing) {
            return false;
        }

        $payload = [
            'assignment_status' => 'completed',
            'is_current'        => 0,
            'end_date'          => $endDate ?: date('Y-m-d'),
            'updated_by'        => $userId,
            'updated_at'        => date('Y-m-d H:i:s'),
        ];

        $this->writeHistory($id, (int) $existing['employee_id'], 'completed', $userId, $existing);
        $ok = $this->db->table('hr_employee_assignments')->where('id', $id)->update($payload);
        $this->syncEmployeePrimaryFacility((int) $existing['employee_id']);

        return $ok;
    }

    /** @param array<string, mixed> $newData */
    public function transfer(int $oldAssignmentId, array $newData, int $userId): int
    {
        $old = $this->find($oldAssignmentId);
        if (! $old) {
            throw new \RuntimeException('Assignment not found.');
        }

        $this->db->table('hr_employee_assignments')->where('id', $oldAssignmentId)->update([
            'assignment_status' => 'transferred',
            'is_current'        => 0,
            'end_date'          => $newData['start_date'] ?? date('Y-m-d'),
            'updated_by'        => $userId,
            'updated_at'        => date('Y-m-d H:i:s'),
        ]);
        $this->writeHistory($oldAssignmentId, (int) $old['employee_id'], 'transferred', $userId, $old);

        $newData['employee_id'] = $old['employee_id'];
        $newData['company_id']  = $newData['company_id'] ?? $old['company_id'];
        $newData['is_current']  = 1;
        $newData['assignment_status'] = $newData['assignment_status'] ?? 'active';

        return $this->create($newData, $userId);
    }

    /** @param array<string, mixed> $filters */
    public function activeAtFacility(int $facilityId, array $filters = []): array
    {
        if (! $this->tablesReady()) {
            return [];
        }

        $q = $this->db->table('hr_employee_assignments a')
            ->select('a.*, e.emp_code, u.name AS employee_name, d.name AS designation_name')
            ->join('employees e', 'e.id = a.employee_id', 'left')
            ->join('users u', 'u.id = e.user_id', 'left')
            ->join('hr_designations d', 'd.id = e.designation_id', 'left')
            ->where('a.facility_id', $facilityId)
            ->where('a.is_current', 1)
            ->where('a.assignment_status', 'active');

        if (! empty($filters['designation_id'])) {
            $q->where('e.designation_id', (int) $filters['designation_id']);
        }

        return $q->orderBy('u.name', 'ASC')->get()->getResultArray();
    }

    /** @param array<string, mixed> $filters */
    public function countActiveAtFacility(int $facilityId, ?int $designationId = null): int
    {
        if (! $this->tablesReady()) {
            return 0;
        }

        $q = $this->db->table('hr_employee_assignments a')
            ->join('employees e', 'e.id = a.employee_id', 'left')
            ->where('a.facility_id', $facilityId)
            ->where('a.is_current', 1)
            ->where('a.assignment_status', 'active');

        if ($designationId) {
            $q->where('e.designation_id', $designationId);
        }

        return $q->countAllResults();
    }

    private function clearCurrentFlag(int $employeeId, string $assignmentType): void
    {
        $q = $this->db->table('hr_employee_assignments')
            ->where('employee_id', $employeeId)
            ->where('is_current', 1);

        if ($assignmentType === 'primary') {
            $q->where('assignment_type', 'primary');
        }

        $q->update(['is_current' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    private function syncEmployeePrimaryFacility(int $employeeId): void
    {
        if (! $this->db->tableExists('employees')) {
            return;
        }

        $primary = $this->db->table('hr_employee_assignments')
            ->where('employee_id', $employeeId)
            ->where('assignment_type', 'primary')
            ->where('is_current', 1)
            ->where('assignment_status', 'active')
            ->orderBy('start_date', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        $facilityId = $primary['facility_id'] ?? null;
        $this->db->table('employees')->where('id', $employeeId)->update([
            'facility_id' => $facilityId,
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    /** @param array<string, mixed> $snapshot */
    private function writeHistory(int $assignmentId, int $employeeId, string $action, int $userId, array $snapshot): void
    {
        if (! $this->db->tableExists('hr_employee_assignment_history')) {
            return;
        }

        $this->db->table('hr_employee_assignment_history')->insert([
            'assignment_id' => $assignmentId,
            'employee_id'   => $employeeId,
            'action'        => $action,
            'snapshot'      => json_encode($snapshot),
            'changed_by'    => $userId,
            'changed_at'    => date('Y-m-d H:i:s'),
        ]);
    }
}
