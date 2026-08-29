<?php

namespace App\Services\Hr;

use CodeIgniter\Database\BaseConnection;

/**
 * Loads configurable HR master data (types, sources, statuses, departments, etc.).
 */
class HrMasterDataService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function tableExists(string $table): bool
    {
        return $this->db->tableExists($table);
    }

    /** @return list<array<string, mixed>> */
    public function employeeTypes(?int $companyId = null): array
    {
        return $this->lookupRows('hr_employee_types', $companyId);
    }

    /** @return list<array<string, mixed>> */
    public function employmentSources(?int $companyId = null): array
    {
        return $this->lookupRows('hr_employment_sources', $companyId);
    }

    /** @return list<array<string, mixed>> */
    public function employeeStatuses(?int $companyId = null): array
    {
        return $this->lookupRows('hr_employee_statuses', $companyId);
    }

    /** @return list<array<string, mixed>> */
    public function departments(?int $companyId = null): array
    {
        return $this->lookupRows('hr_departments', $companyId);
    }

    /** @return list<array<string, mixed>> */
    public function designations(?int $companyId = null): array
    {
        return $this->lookupRows('hr_designations', $companyId);
    }

    /** @return list<array<string, mixed>> */
    public function grades(?int $companyId = null): array
    {
        return $this->lookupRows('hr_grades', $companyId);
    }

    /** @return list<array<string, mixed>> */
    public function operatingCompanies(?int $companyId = null): array
    {
        if (! $this->db->tableExists('finance_branches')) {
            return [];
        }
        $q = $this->db->table('finance_branches')
            ->where('is_active', 1)
            ->orderBy('name', 'ASC');
        if ($companyId) {
            $q->where('company_id', $companyId);
        }

        return $q->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function costCenters(?int $companyId = null): array
    {
        if (! $this->db->tableExists('finance_cost_centers')) {
            return [];
        }
        $q = $this->db->table('finance_cost_centers')->orderBy('name', 'ASC');
        if ($companyId && $this->db->fieldExists('company_id', 'finance_cost_centers')) {
            $q->where('company_id', $companyId);
        }

        return $q->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function companies(): array
    {
        if (! $this->db->tableExists('companies')) {
            return [];
        }

        return $this->db->table('companies')
            ->where('status', 'active')
            ->orderBy('name', 'ASC')
            ->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function facilities(): array
    {
        if (! $this->db->tableExists('facilities')) {
            return [];
        }

        return $this->db->table('facilities')
            ->where('status', 'active')
            ->orderBy('name', 'ASC')
            ->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function managerCandidates(?int $companyId = null): array
    {
        $q = $this->db->table('employees e')
            ->select('e.id, u.name, e.emp_code, e.designation')
            ->join('users u', 'u.id = e.user_id', 'left')
            ->whereIn('e.status', ['active', 'on_leave'])
            ->orderBy('u.name', 'ASC');
        if ($companyId) {
            $q->where('e.company_id', $companyId);
        }

        return $q->get()->getResultArray();
    }

    /** @return array<string, list<array<string, mixed>>> */
    public function formOptions(?int $companyId = null): array
    {
        return [
            'employeeTypes'      => $this->employeeTypes($companyId),
            'employmentSources'  => $this->employmentSources($companyId),
            'employeeStatuses'   => $this->employeeStatuses($companyId),
            'departments'        => $this->departments($companyId),
            'designations'       => $this->designations($companyId),
            'grades'             => $this->grades($companyId),
            'operatingCompanies' => $this->operatingCompanies($companyId),
            'costCenters'        => $this->costCenters($companyId),
            'companies'          => $this->companies(),
            'facilities'         => $this->facilities(),
            'managers'           => $this->managerCandidates($companyId),
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     */
    public function saveLookupRow(string $table, array $row, ?int $id = null): int
    {
        if (! $this->db->tableExists($table)) {
            throw new \RuntimeException("Table {$table} does not exist. Run HR migrations first.");
        }
        if ($id) {
            $this->db->table($table)->where('id', $id)->update($row);

            return $id;
        }
        $this->db->table($table)->insert($row);

        return (int) $this->db->insertID();
    }

    /** @return list<array<string, mixed>> */
    private function lookupRows(string $table, ?int $companyId): array
    {
        if (! $this->db->tableExists($table)) {
            return [];
        }

        $q = $this->db->table($table);
        if ($this->db->fieldExists('is_active', $table)) {
            $q->where('is_active', 1);
        }
        if ($this->db->fieldExists('sort_order', $table)) {
            $q->orderBy('sort_order', 'ASC');
        }
        $q->orderBy('name', 'ASC');

        if ($companyId !== null && $this->db->fieldExists('company_id', $table)) {
            $q->groupStart()
                ->where('company_id', $companyId)
                ->orWhere('company_id', null)
                ->groupEnd();
        }

        return $q->get()->getResultArray();
    }
}
