<?php

namespace App\Controllers\Hr;

class HrEmployees extends HrBaseController
{
    protected ?string $workspaceRequired = null;

    public function index()
    {
        $this->requireHrAccess();

        $rows = [];
        if ($this->db->tableExists('employee_profiles')) {
            $q = $this->db->table('employee_profiles ep')
                ->select('ep.*, u.name AS user_name, u.email AS user_email, hd.name AS department_name, hdes.title AS designation_title')
                ->join('users u', 'u.id = ep.user_id', 'left')
                ->join('hr_departments hd', 'hd.id = ep.department_id', 'left')
                ->join('hr_designations hdes', 'hdes.id = ep.designation_id', 'left')
                ->where('ep.deleted_at', null)
                ->orderBy('ep.employee_code', 'ASC');
            $this->scopeEmployeeCompany($q);
            $rows = $q->limit(200)->get()->getResultArray();
        }

        return view('hr/employees/index', $this->viewData([
            'title'     => 'Employees',
            'hrActive'  => 'employees',
            'employees' => $rows,
            'migration' => ! $this->db->tableExists('employee_profiles'),
        ]));
    }

    public function create()
    {
        $this->requireHrAccess();

        $users = $this->usersByRole('technician');
        $managers = array_merge(
            $this->usersByRole('facility_manager'),
            $this->usersByRole('super_admin'),
            $this->usersByRole('supervisor')
        );
        $departments  = $this->db->tableExists('hr_departments') ? $this->db->table('hr_departments')->where('status', 'active')->get()->getResultArray() : [];
        $designations = $this->db->tableExists('hr_designations') ? $this->db->table('hr_designations')->where('status', 'active')->get()->getResultArray() : [];

        return view('hr/employees/form', $this->viewData([
            'title'        => 'Add Employee',
            'hrActive'     => 'employees',
            'employee'     => [],
            'users'        => $users,
            'managers'     => $managers,
            'departments'  => $departments,
            'designations' => $designations,
            'formAction'   => base_url('hr/employees/store'),
        ]));
    }

    public function store()
    {
        $this->requireHrAccess();

        if (! $this->db->tableExists('employee_profiles')) {
            return redirect()->back()->with('error', 'Run database/pm_dms_hrms_patch.sql first.');
        }

        $userId = (int) $this->request->getPost('user_id');
        if ($userId <= 0) {
            return redirect()->back()->withInput()->with('error', 'Select a user account.');
        }

        $exists = $this->db->table('employee_profiles')->where('user_id', $userId)->countAllResults();
        if ($exists) {
            return redirect()->back()->withInput()->with('error', 'This user already has an employee profile.');
        }

        $code = trim((string) $this->request->getPost('employee_code'));
        if ($code === '') {
            $max = (int) $this->db->table('employee_profiles')->selectMax('id', 'm')->get()->getRowArray()['m'];
            $code = 'EMP-' . str_pad((string) ($max + 1), 4, '0', STR_PAD_LEFT);
        }

        $payload = $this->profilePayload($code);
        $payload['user_id'] = $userId;
        $this->db->table('employee_profiles')->insert($payload);

        $id = (int) $this->db->insertID();
        $this->logActivity('create', 'employee_profiles', $id, 'Employee profile created: ' . $code);

        return redirect()->to(base_url('hr/employees/view/' . $id))->with('success', 'Employee profile created.');
    }

    public function view(int $id)
    {
        $this->requireHrAccess();

        $emp = $this->findProfile($id);
        if (! $emp) {
            return redirect()->to(base_url('hr/employees'))->with('error', 'Employee not found.');
        }

        $documents = [];
        if ($this->db->tableExists('documents')) {
            $dms = new \App\Services\DocumentManagementService($this->db);
            $documents = $dms->listForEntity('employee', (int) $emp['user_id']);
        }

        return view('hr/employees/view', $this->viewData([
            'title'     => $emp['employee_code'],
            'hrActive'  => 'employees',
            'employee'  => $emp,
            'documents' => $documents,
        ]));
    }

    public function edit(int $id)
    {
        $this->requireHrAccess();

        $emp = $this->findProfile($id);
        if (! $emp) {
            return redirect()->to(base_url('hr/employees'))->with('error', 'Employee not found.');
        }

        $users = $this->usersByRole('technician');
        if (! empty($emp['user_id'])) {
            $linked = $this->db->table('users')->select('id, name, email')->where('id', (int) $emp['user_id'])->get()->getRowArray();
            if ($linked && ! in_array((int) $linked['id'], array_map(static fn ($u) => (int) $u['id'], $users), true)) {
                array_unshift($users, $linked);
            }
        }

        $managers = array_merge(
            $this->usersByRole('facility_manager'),
            $this->usersByRole('super_admin'),
            $this->usersByRole('supervisor')
        );
        $departments  = $this->db->tableExists('hr_departments') ? $this->db->table('hr_departments')->where('status', 'active')->get()->getResultArray() : [];
        $designations = $this->db->tableExists('hr_designations') ? $this->db->table('hr_designations')->where('status', 'active')->get()->getResultArray() : [];

        return view('hr/employees/form', $this->viewData([
            'title'        => 'Edit ' . ($emp['employee_code'] ?? 'Employee'),
            'hrActive'     => 'employees',
            'employee'     => $emp,
            'users'        => $users,
            'managers'     => $managers,
            'departments'  => $departments,
            'designations' => $designations,
            'formAction'   => base_url('hr/employees/' . $id . '/update'),
        ]));
    }

    public function update(int $id)
    {
        $this->requireHrAccess();
        if (! $this->request->is('post')) {
            return redirect()->to(base_url('hr/employees/' . $id . '/edit'));
        }

        $emp = $this->findProfile($id);
        if (! $emp) {
            return redirect()->to(base_url('hr/employees'))->with('error', 'Employee not found.');
        }

        $code = trim((string) $this->request->getPost('employee_code'));
        if ($code === '') {
            $code = (string) $emp['employee_code'];
        }

        $status = (string) $this->request->getPost('status');
        if (! in_array($status, ['active', 'inactive', 'terminated'], true)) {
            $status = (string) ($emp['status'] ?? 'active');
        }

        $payload = $this->profilePayload($code);
        $payload['status']     = $status;
        $payload['updated_at'] = date('Y-m-d H:i:s');
        unset($payload['user_id'], $payload['created_at']);

        $this->db->transStart();
        try {
            $this->db->table('employee_profiles')->where('id', $id)->update($payload);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return redirect()->back()->withInput()->with('error', 'Could not update employee.');
            }
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'HR employee update failed: ' . $e->getMessage());

            return redirect()->back()->withInput()->with('error', 'Could not update employee. Please try again.');
        }

        $this->logActivity('update', 'employee_profiles', $id, 'Employee profile updated: ' . $code);

        return redirect()->to(base_url('hr/employees/view/' . $id))->with('success', 'Employee profile updated.');
    }

    public function status(int $id)
    {
        $this->requireHrAccess();
        if (! $this->request->is('post')) {
            return redirect()->to(base_url('hr/employees/view/' . $id));
        }

        $emp = $this->findProfile($id);
        if (! $emp) {
            return redirect()->to(base_url('hr/employees'))->with('error', 'Employee not found.');
        }

        $status = (string) $this->request->getPost('status');
        if (! in_array($status, ['active', 'inactive', 'terminated'], true)) {
            return redirect()->back()->with('error', 'Invalid employee status.');
        }

        $this->db->table('employee_profiles')->where('id', $id)->update([
            'status'     => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->logActivity('update', 'employee_profiles', $id, 'Employee status set to ' . $status);

        return redirect()->to(base_url('hr/employees/view/' . $id))->with('success', 'Employee status updated.');
    }

    /** @return array<string, mixed>|null */
    private function findProfile(int $id): ?array
    {
        if (! $this->db->tableExists('employee_profiles') || $id < 1) {
            return null;
        }

        $q = $this->db->table('employee_profiles ep')
            ->select('ep.*, u.name AS user_name, u.email AS user_email')
            ->join('users u', 'u.id = ep.user_id', 'left')
            ->where('ep.id', $id)
            ->where('ep.deleted_at', null);
        $this->scopeEmployeeCompany($q);

        return $q->get()->getRowArray() ?: null;
    }

    private function scopeEmployeeCompany(object $builder): void
    {
        if ($this->db->fieldExists('company_id', 'employee_profiles')) {
            $this->scopeCompany($builder, 'ep.company_id');
        }
    }

    /** @return array<string, mixed> */
    private function profilePayload(string $code): array
    {
        return [
            'user_id'         => (int) $this->request->getPost('user_id'),
            'employee_code'   => $code,
            'company_id'      => session()->get('company_id') ?: null,
            'workspace'       => $this->request->getPost('workspace') ?: 'fm',
            'department_id'   => $this->request->getPost('department_id') ?: null,
            'designation_id'  => $this->request->getPost('designation_id') ?: null,
            'manager_user_id' => $this->request->getPost('manager_user_id') ?: null,
            'status'          => 'active',
            'hire_date'       => $this->request->getPost('hire_date') ?: null,
            'employment_type' => $this->request->getPost('employment_type') ?: 'full_time',
            'phone'           => esc($this->request->getPost('phone')),
            'national_id'     => esc($this->request->getPost('national_id')),
            'passport_no'     => esc($this->request->getPost('passport_no')),
            'passport_expiry' => $this->request->getPost('passport_expiry') ?: null,
            'visa_expiry'     => $this->request->getPost('visa_expiry') ?: null,
            'basic_salary'    => (float) $this->request->getPost('basic_salary'),
            'allowances'      => (float) $this->request->getPost('allowances'),
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];
    }
}
