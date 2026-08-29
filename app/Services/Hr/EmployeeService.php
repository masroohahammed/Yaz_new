<?php

namespace App\Services\Hr;

use App\Models\Hr\EmployeeModel;
use App\Models\UserModel;
use CodeIgniter\Database\BaseConnection;

class EmployeeService
{
    private BaseConnection $db;
    private EmployeeModel $model;
    private HrMasterDataService $masters;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db      = $db ?? \Config\Database::connect();
        $this->model   = new EmployeeModel($this->db);
        $this->masters = new HrMasterDataService($this->db);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{rows: list<array<string, mixed>>, total: int}
     */
    public function list(array $filters, int $limit, int $offset, ?callable $scopeFacilities = null): array
    {
        $q = $this->db->table('employees e')
            ->select('e.*, u.name, u.email, u.phone, f.name AS facility_name,
                et.name AS employee_type_name, es.name AS employment_source_name,
                hs.name AS status_name, d.name AS department_master_name, dg.name AS designation_master_name,
                c.name AS company_name, fb.name AS operating_company_name')
            ->join('users u', 'u.id = e.user_id', 'left')
            ->join('facilities f', 'f.id = e.facility_id', 'left')
            ->join('companies c', 'c.id = e.company_id', 'left')
            ->join('finance_branches fb', 'fb.id = e.operating_company_id', 'left')
            ->join('hr_employee_types et', 'et.id = e.employee_type_id', 'left')
            ->join('hr_employment_sources es', 'es.id = e.employment_source_id', 'left')
            ->join('hr_employee_statuses hs', 'hs.id = e.status_id', 'left')
            ->join('hr_departments d', 'd.id = e.department_id', 'left')
            ->join('hr_designations dg', 'dg.id = e.designation_id', 'left');

        if ($scopeFacilities) {
            $scopeFacilities($q, 'e.facility_id');
        }

        if (! empty($filters['company_id'])) {
            $q->where('e.company_id', (int) $filters['company_id']);
        }
        if (! empty($filters['status_id'])) {
            $q->where('e.status_id', (int) $filters['status_id']);
        } elseif (! empty($filters['status'])) {
            $q->where('e.status', $filters['status']);
        }
        if (! empty($filters['employee_type_id'])) {
            $q->where('e.employee_type_id', (int) $filters['employee_type_id']);
        }
        if (! empty($filters['employment_source_id'])) {
            $q->where('e.employment_source_id', (int) $filters['employment_source_id']);
        }
        if (! empty($filters['department_id'])) {
            $q->where('e.department_id', (int) $filters['department_id']);
        }
        if (! empty($filters['facility_id'])) {
            $q->where('e.facility_id', (int) $filters['facility_id']);
        }
        if (! empty($filters['search'])) {
            $term = trim((string) $filters['search']);
            $q->groupStart()
                ->like('e.emp_code', $term)
                ->orLike('u.name', $term)
                ->orLike('e.name_ar', $term)
                ->orLike('e.first_name', $term)
                ->orLike('e.last_name', $term)
                ->orLike('e.qid_number', $term)
                ->orLike('e.passport_number', $term)
                ->orLike('e.personal_mobile', $term)
                ->groupEnd();
        }

        $total = (clone $q)->countAllResults(false);
        $rows  = $q->orderBy('u.name', 'ASC')->limit($limit, $offset)->get()->getResultArray();

        return ['rows' => $rows, 'total' => $total];
    }

    public function find(int $id): ?array
    {
        $row = $this->db->table('employees e')
            ->select('e.*, u.name, u.email, u.phone,
                f.name AS facility_name, c.name AS company_name,
                fb.name AS operating_company_name,
                et.name AS employee_type_name, es.name AS employment_source_name,
                hs.name AS status_name, hs.code AS status_code,
                d.name AS department_master_name, dg.name AS designation_master_name,
                g.name AS grade_name,
                rm_u.name AS reporting_manager_name,
                cc.name AS cost_center_name')
            ->join('users u', 'u.id = e.user_id', 'left')
            ->join('facilities f', 'f.id = e.facility_id', 'left')
            ->join('companies c', 'c.id = e.company_id', 'left')
            ->join('finance_branches fb', 'fb.id = e.operating_company_id', 'left')
            ->join('hr_employee_types et', 'et.id = e.employee_type_id', 'left')
            ->join('hr_employment_sources es', 'es.id = e.employment_source_id', 'left')
            ->join('hr_employee_statuses hs', 'hs.id = e.status_id', 'left')
            ->join('hr_departments d', 'd.id = e.department_id', 'left')
            ->join('hr_designations dg', 'dg.id = e.designation_id', 'left')
            ->join('hr_grades g', 'g.id = e.grade_id', 'left')
            ->join('employees rm', 'rm.id = e.reporting_manager_id', 'left')
            ->join('users rm_u', 'rm_u.id = rm.user_id', 'left')
            ->join('finance_cost_centers cc', 'cc.id = e.cost_center_id', 'left')
            ->where('e.id', $id)
            ->get()->getRowArray();

        return $row ?: null;
    }

    public function displayName(array $emp): string
    {
        $parts = array_filter([
            trim((string) ($emp['first_name'] ?? '')),
            trim((string) ($emp['last_name'] ?? '')),
        ]);
        if ($parts !== []) {
            return implode(' ', $parts);
        }

        return (string) ($emp['name'] ?? $emp['emp_code'] ?? 'Employee');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, int $userId): int
    {
        helper('fm');
        $data['emp_code']     = $data['emp_code'] ?? $this->nextEmpCode($data['company_id'] ?? null);
        $data['created_by']   = $userId;
        $data['updated_by']   = $userId;
        $data['joining_date'] = $data['joining_date'] ?? $data['hire_date'] ?? null;

        if (empty($data['status_id']) && $this->db->tableExists('hr_employee_statuses')) {
            $active = $this->db->table('hr_employee_statuses')->where('code', 'active')->where('company_id', null)->get()->getRowArray();
            if ($active) {
                $data['status_id'] = (int) $active['id'];
            }
        }
        if (empty($data['status'])) {
            $data['status'] = 'active';
        }

        $this->resolveDepartmentDesignationText($data);

        if (function_exists('fm_insert_row_id')) {
            $id = fm_insert_row_id($this->db, 'employees', $data);
            $this->createEmploymentPeriod($id, $data);

            return $id;
        }

        $this->db->table('employees')->insert($data);
        $id = (int) $this->db->insertID();
        $this->createEmploymentPeriod($id, $data);

        return $id;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(int $id, array $data, int $userId): bool
    {
        $data['updated_by'] = $userId;
        $this->resolveDepartmentDesignationText($data);
        $this->syncLegacyStatus($data);

        return $this->db->table('employees')->where('id', $id)->update($data);
    }

    public function deactivate(int $id, int $userId): bool
    {
        $patch = ['status' => 'inactive', 'updated_by' => $userId];
        if ($this->db->tableExists('hr_employee_statuses')) {
            $row = $this->db->table('hr_employee_statuses')->where('code', 'inactive')->where('company_id', null)->get()->getRowArray();
            if ($row) {
                $patch['status_id'] = (int) $row['id'];
            }
        }

        return $this->db->table('employees')->where('id', $id)->update($patch);
    }

    /** @return list<array<string, mixed>> */
    public function linkableUsers(?int $companyId = null): array
    {
        $userModel = new UserModel($this->db);

        return array_merge(
            $userModel->getUsersByRole('technician', $companyId),
            $userModel->getUsersByRole('supervisor', $companyId),
            $userModel->getUsersByRole('facility_manager', $companyId),
        );
    }

    public function masters(): HrMasterDataService
    {
        return $this->masters;
    }

    public function hrTablesReady(): bool
    {
        return (new HrSchemaService($this->db))->employeeMasterReady();
    }

    public function mask(string $value, bool $canView): string
    {
        if ($canView || $value === '') {
            return $value;
        }
        $len = strlen($value);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }

        return str_repeat('*', $len - 4) . substr($value, -4);
    }

    private function nextEmpCode(?int $companyId): string
    {
        $prefix = 'EMP-';
        $q      = $this->db->table('employees')->select('emp_code')->like('emp_code', $prefix, 'after')->orderBy('id', 'DESC')->limit(1);
        if ($companyId) {
            $q->where('company_id', $companyId);
        }
        $last = $q->get()->getRowArray();
        $seq  = 1;
        if ($last && preg_match('/(\d+)$/', (string) $last['emp_code'], $m)) {
            $seq = (int) $m[1] + 1;
        }

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /** @param array<string, mixed> $data */
    private function resolveDepartmentDesignationText(array &$data): void
    {
        if (! empty($data['department_id']) && $this->db->tableExists('hr_departments')) {
            $d = $this->db->table('hr_departments')->where('id', (int) $data['department_id'])->get()->getRowArray();
            if ($d) {
                $data['department'] = $d['name'];
            }
        }
        if (! empty($data['designation_id']) && $this->db->tableExists('hr_designations')) {
            $d = $this->db->table('hr_designations')->where('id', (int) $data['designation_id'])->get()->getRowArray();
            if ($d) {
                $data['designation'] = $d['name'];
            }
        }
    }

    /** @param array<string, mixed> $data */
    private function syncLegacyStatus(array &$data): void
    {
        if (! empty($data['status_id']) && $this->db->tableExists('hr_employee_statuses')) {
            $s = $this->db->table('hr_employee_statuses')->where('id', (int) $data['status_id'])->get()->getRowArray();
            if ($s && ! empty($s['legacy_status'])) {
                $data['status'] = $s['legacy_status'];
            }
        } elseif (! empty($data['status']) && empty($data['status_id']) && $this->db->tableExists('hr_employee_statuses')) {
            $s = $this->db->table('hr_employee_statuses')
                ->where('legacy_status', $data['status'])
                ->where('company_id', null)
                ->get()->getRowArray();
            if ($s) {
                $data['status_id'] = (int) $s['id'];
            }
        }
    }

    /** @param array<string, mixed> $data */
    private function createEmploymentPeriod(int $employeeId, array $data): void
    {
        if (! $this->db->tableExists('hr_employment_periods')) {
            return;
        }
        $this->db->table('hr_employment_periods')->insert([
            'employee_id'          => $employeeId,
            'company_id'           => $data['company_id'] ?? null,
            'operating_company_id' => $data['operating_company_id'] ?? null,
            'period_number'        => 1,
            'joining_date'         => $data['joining_date'] ?? null,
            'status_id'            => $data['status_id'] ?? null,
            'is_current'           => 1,
        ]);
        $periodId = (int) $this->db->insertID();
        if ($periodId > 0) {
            $this->db->table('employees')->where('id', $employeeId)->update([
                'current_employment_period_id' => $periodId,
            ]);
        }
    }
}
