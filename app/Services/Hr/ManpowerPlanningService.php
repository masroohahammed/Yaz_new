<?php

namespace App\Services\Hr;

use CodeIgniter\Database\BaseConnection;

class ManpowerPlanningService
{
    private BaseConnection $db;

    private EmployeeAssignmentService $assignments;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db          = $db ?? \Config\Database::connect();
        $this->assignments = new EmployeeAssignmentService($this->db);
    }

    public function tablesReady(): bool
    {
        return $this->db->tableExists('hr_manpower_requirements');
    }

    /** @param array<string, mixed> $filters */
    public function dashboard(array $filters = []): array
    {
        if (! $this->tablesReady()) {
            return [];
        }

        $q = $this->db->table('hr_manpower_requirements r')
            ->select('r.*, f.name AS facility_name, d.name AS designation_name, dept.name AS department_name')
            ->join('facilities f', 'f.id = r.facility_id', 'left')
            ->join('hr_designations d', 'd.id = r.designation_id', 'left')
            ->join('hr_departments dept', 'dept.id = r.department_id', 'left')
            ->whereIn('r.status', ['draft', 'active']);

        if (! empty($filters['company_id'])) {
            $q->where('r.company_id', (int) $filters['company_id']);
        }
        if (! empty($filters['facility_id'])) {
            $q->where('r.facility_id', (int) $filters['facility_id']);
        }

        $rows = $q->orderBy('f.name', 'ASC')->get()->getResultArray();
        foreach ($rows as &$row) {
            $assigned = $this->assignments->countActiveAtFacility(
                (int) $row['facility_id'],
                ! empty($row['designation_id']) ? (int) $row['designation_id'] : null
            );
            $row['assigned_headcount'] = $assigned;
            $row['gap']                = (int) $row['required_headcount'] - $assigned;
        }
        unset($row);

        return $rows;
    }

    /** @param array<string, mixed> $data */
    public function createRequirement(array $data, int $userId): int
    {
        if (! $this->tablesReady()) {
            throw new \RuntimeException('Manpower tables missing.');
        }

        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;

        helper('fm');
        if (function_exists('fm_insert_row_id')) {
            return fm_insert_row_id($this->db, 'hr_manpower_requirements', $data);
        }

        $this->db->table('hr_manpower_requirements')->insert($data);

        return (int) $this->db->insertID();
    }

    /** @param array<string, mixed> $data */
    public function updateRequirement(int $id, array $data, int $userId): bool
    {
        if (! $this->tablesReady()) {
            return false;
        }

        $data['updated_by'] = $userId;

        return $this->db->table('hr_manpower_requirements')->where('id', $id)->update($data);
    }

    public function findRequirement(int $id): ?array
    {
        if (! $this->tablesReady()) {
            return null;
        }

        $row = $this->db->table('hr_manpower_requirements r')
            ->select('r.*, f.name AS facility_name')
            ->join('facilities f', 'f.id = r.facility_id', 'left')
            ->where('r.id', $id)
            ->get()->getRowArray();

        return $row ?: null;
    }
}
