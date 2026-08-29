<?php

namespace App\Services\Hr;

use CodeIgniter\Database\BaseConnection;

class HrShiftService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function tablesReady(): bool
    {
        return $this->db->tableExists('hr_shifts');
    }

    /** @return list<array<string, mixed>> */
    public function list(?int $companyId = null, bool $activeOnly = true): array
    {
        if (! $this->tablesReady()) {
            return [];
        }

        $q = $this->db->table('hr_shifts')->orderBy('name', 'ASC');
        if ($activeOnly) {
            $q->where('is_active', 1);
        }
        if ($companyId) {
            $q->groupStart()
                ->where('company_id', $companyId)
                ->orWhere('company_id', null)
                ->groupEnd();
        }

        return $q->get()->getResultArray();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): int
    {
        if (! $this->tablesReady()) {
            throw new \RuntimeException('Shift tables missing.');
        }

        helper('fm');
        if (function_exists('fm_insert_row_id')) {
            return fm_insert_row_id($this->db, 'hr_shifts', $data);
        }

        $this->db->table('hr_shifts')->insert($data);

        return (int) $this->db->insertID();
    }

    /** @return list<array<string, mixed>> */
    public function assignmentsForEmployee(int $employeeId): array
    {
        if (! $this->db->tableExists('hr_shift_assignments')) {
            return [];
        }

        return $this->db->table('hr_shift_assignments sa')
            ->select('sa.*, s.name AS shift_name, s.start_time, s.end_time, f.name AS facility_name')
            ->join('hr_shifts s', 's.id = sa.shift_id', 'left')
            ->join('facilities f', 'f.id = sa.facility_id', 'left')
            ->where('sa.employee_id', $employeeId)
            ->orderBy('sa.effective_from', 'DESC')
            ->get()->getResultArray();
    }

    public function currentAssignment(int $employeeId): ?array
    {
        if (! $this->db->tableExists('hr_shift_assignments')) {
            return null;
        }

        $today = date('Y-m-d');
        $row   = $this->db->table('hr_shift_assignments sa')
            ->select('sa.*, s.name AS shift_name, s.start_time, s.end_time, s.grace_in_minutes, s.grace_out_minutes')
            ->join('hr_shifts s', 's.id = sa.shift_id', 'left')
            ->where('sa.employee_id', $employeeId)
            ->where('sa.is_current', 1)
            ->groupStart()
                ->where('sa.effective_from IS NULL')
                ->orWhere('sa.effective_from <=', $today)
            ->groupEnd()
            ->groupStart()
                ->where('sa.effective_to IS NULL')
                ->orWhere('sa.effective_to >=', $today)
            ->groupEnd()
            ->orderBy('sa.id', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        return $row ?: null;
    }

    /** @param array<string, mixed> $data */
    public function assignToEmployee(array $data, int $userId): int
    {
        if (! $this->db->tableExists('hr_shift_assignments')) {
            throw new \RuntimeException('Shift assignment tables missing.');
        }

        $this->db->table('hr_shift_assignments')
            ->where('employee_id', (int) $data['employee_id'])
            ->where('is_current', 1)
            ->update(['is_current' => 0, 'updated_at' => date('Y-m-d H:i:s')]);

        $data['created_by'] = $userId;
        $data['is_current'] = 1;

        helper('fm');
        if (function_exists('fm_insert_row_id')) {
            return fm_insert_row_id($this->db, 'hr_shift_assignments', $data);
        }

        $this->db->table('hr_shift_assignments')->insert($data);

        return (int) $this->db->insertID();
    }
}
