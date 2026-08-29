<?php

namespace App\Controllers;

use App\Controllers\Traits\HrRbacTrait;
use App\Services\Hr\EmployeeService;
use App\Services\RbacService;

class Employees extends BaseController
{
    use HrRbacTrait;

    protected ?string $workspaceRequired = 'fm';

    private EmployeeService $employees;

    public function __construct()
    {
        $this->employees = new EmployeeService();
    }

    public function index()
    {
        $this->requireHrPermission('employees');
        $filters = [
            'search'               => trim((string) $this->request->getGet('q')),
            'status_id'            => (int) ($this->request->getGet('status_id') ?: 0) ?: null,
            'employee_type_id'     => (int) ($this->request->getGet('employee_type_id') ?: 0) ?: null,
            'employment_source_id' => (int) ($this->request->getGet('employment_source_id') ?: 0) ?: null,
            'department_id'        => (int) ($this->request->getGet('department_id') ?: 0) ?: null,
            'facility_id'          => (int) ($this->request->getGet('facility_id') ?: 0) ?: null,
        ];
        $companyId = $this->resolveCompanyId();
        if ($companyId) {
            $filters['company_id'] = $companyId;
        }

        $pg   = $this->paginate(25);
        $result = $this->employees->list(
            $filters,
            $pg['perPage'],
            $pg['offset'],
            fn ($q, $col) => $this->scopeFacilities($q, $col)
        );

        $masters = $this->employees->masters()->formOptions($companyId);

        return view('employees/index', $this->viewData([
            'title'       => 'Employees',
            'hrNavActive' => 'employees',
            'employees'   => $result['rows'],
            'totalCount'  => $result['total'],
            'perPage'     => $pg['perPage'],
            'currentPage' => $pg['page'],
            'filters'     => $filters,
            'masters'     => $masters,
            'hrReady'     => $this->employees->hrTablesReady(),
            'perms'       => $this->hrPermissionFlags(['employee.create', 'employee.edit', 'employee.delete', 'hr.settings']),
        ]));
    }

    public function create()
    {
        $this->requireHrPermission('employee.create');
        $companyId = $this->resolveCompanyId();
        $options   = $this->employees->masters()->formOptions($companyId);

        return view('employees/create', $this->viewData([
            'title'       => 'Add Employee',
            'hrNavActive' => 'employees',
            'users'   => $this->employees->linkableUsers($companyId),
            'options' => $options,
            'hrReady' => $this->employees->hrTablesReady(),
        ]));
    }

    public function store()
    {
        $this->requireHrPermission('employee.create');
        $rules = [
            'designation' => 'required|max_length[100]',
            'department'  => 'permit_empty|max_length[100]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $companyId = $this->resolveCompanyId();
        $data      = $this->collectEmployeePayload($companyId);
        if (empty($data['department']) && empty($data['department_id'])) {
            return redirect()->back()->withInput()->with('error', 'Department is required.');
        }
        if (empty($data['designation']) && empty($data['designation_id'])) {
            return redirect()->back()->withInput()->with('error', 'Designation is required.');
        }

        $userId = (int) session()->get('user_id');
        $this->employees->create($data, $userId);

        return redirect()->to(base_url('employees'))->with('success', 'Employee added.');
    }

    public function view(int $id)
    {
        $this->requireHrPermission('employees');
        $emp = $this->employees->find($id);
        if (! $emp) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $this->assertEmployeeFacilityAccess($emp);

        $attendance = $this->db->table('attendance')->where('employee_id', $id)->orderBy('date', 'DESC')->limit(30)->get()->getResultArray();
        $assignedWO = $this->db->table('work_orders w')
            ->select('w.*, f.name as facility_name')
            ->join('facilities f', 'f.id = w.facility_id', 'left')
            ->where('w.assigned_to', $emp['user_id'])
            ->whereIn('w.status', ['assigned', 'in_progress', 'on_hold'])
            ->get()->getResultArray();

        $rbac = new RbacService($this->db);
        $role = (string) session()->get('user_role');
        $canViewSensitive = $rbac->can($role, 'employee.identification.view') || $rbac->can($role, '*');
        $canViewSalary    = $rbac->can($role, 'employee.salary.view') || $rbac->can($role, '*');
        $canViewDocuments = $rbac->can($role, 'employee.documents.view') || $rbac->can($role, '*');

        $employeeDocuments = [];
        $docCategories     = [];
        $hrDocService      = new \App\Services\Hr\HrDocumentService($this->db);
        if ($canViewDocuments && $hrDocService->tablesReady()) {
            $employeeDocuments = $hrDocService->forEmployee($id);
            $docCategories     = $hrDocService->categories($emp['company_id'] ?? null);
        }

        $canViewContracts  = $this->hrCan('employee.contract.view');
        $contractService   = new \App\Services\Hr\EmploymentContractService($this->db);
        $contracts         = [];
        $suppliers         = [];
        if ($canViewContracts && $contractService->tablesReady()) {
            $contracts = $contractService->forEmployee($id);
            $suppliers = $this->db->table('vendors')->where('status', 'active')->orderBy('name')->get()->getResultArray();
        }

        $canViewAssignments = $this->hrCan('employee.assignment.view');
        $assignmentService  = new \App\Services\Hr\EmployeeAssignmentService($this->db);
        $assignments        = [];
        $facilities         = [];
        $units              = [];
        if ($canViewAssignments && $assignmentService->tablesReady()) {
            $assignments = $assignmentService->forEmployee($id);
            $facilitiesQ = $this->db->table('facilities')->where('status', 'active')->orderBy('name');
            $this->scopeFacilities($facilitiesQ, 'facilities.id');
            $facilities  = $facilitiesQ->get()->getResultArray();
            if ($this->db->tableExists('units')) {
                $units = $this->db->table('units')->select('id, unit_number, facility_id')->orderBy('unit_number')->limit(500)->get()->getResultArray();
            }
        }

        $canViewLeave   = $this->hrCan('leave.view') && ! empty($emp['leave_applicable']);
        $leaveService   = new \App\Services\Hr\HrLeaveService($this->db);
        $leaveRequests  = [];
        $leaveBalances  = [];
        if ($canViewLeave && $leaveService->tablesReady()) {
            $leaveRequests = $leaveService->forEmployee($id);
            $leaveBalances = $leaveService->balancesForEmployee($id);
            if (empty($leaveBalances)) {
                $leaveService->initializeBalances($id, $emp['company_id'] ?? null);
                $leaveBalances = $leaveService->balancesForEmployee($id);
            }
        }

        $canViewSalaryTab = $canViewSalary && ! empty($emp['payroll_applicable']);
        $salaryStructure  = null;
        $salaryRevisions  = [];
        $advances         = [];
        $loans            = [];
        if ($canViewSalaryTab) {
            $salaryService = new \App\Services\Hr\HrSalaryService($this->db);
            if ($salaryService->tablesReady()) {
                $salaryStructure = $salaryService->currentStructure($id);
                $salaryRevisions = $salaryService->revisions($id);
            }
            $compService = new \App\Services\Hr\HrAdvanceLoanService($this->db);
            if ($compService->advancesReady()) {
                $advances = $compService->advancesForEmployee($id);
            }
            if ($compService->loansReady()) {
                $loans = $compService->loansForEmployee($id);
            }
        }

        $timelineService = new \App\Services\Hr\EmployeeTimelineService($this->db);
        $timelineEvents  = $timelineService->tablesReady() ? $timelineService->forEmployee($id) : [];

        return view('employees/view', $this->viewData([
            'title'            => $this->employees->displayName($emp),
            'hrNavActive'      => 'employees',
            'emp'              => $emp,
            'displayName'      => $this->employees->displayName($emp),
            'attendance'       => $attendance,
            'assignedWO'       => $assignedWO,
            'canViewSensitive' => $canViewSensitive,
            'canViewSalary'    => $canViewSalary,
            'canViewDocuments' => $canViewDocuments,
            'canUploadDocuments' => $rbac->can($role, 'employee.documents.upload') || $rbac->can($role, '*'),
            'employeeDocuments' => $employeeDocuments,
            'docCategories'    => $docCategories,
            'hrDocService'     => $hrDocService,
            'canViewAssignments' => $canViewAssignments,
            'assignments'        => $assignments,
            'facilities'         => $facilities,
            'units'              => $units,
            'assignmentTypes'    => \App\Services\Hr\EmployeeAssignmentService::TYPES,
            'assignmentStatuses' => \App\Services\Hr\EmployeeAssignmentService::STATUSES,
            'canViewLeave'     => $canViewLeave,
            'leaveRequests'    => $leaveRequests,
            'leaveBalances'    => $leaveBalances,
            'canViewSalaryTab' => $canViewSalaryTab,
            'salaryStructure'  => $salaryStructure,
            'salaryRevisions'  => $salaryRevisions,
            'advances'         => $advances,
            'loans'            => $loans,
            'timelineEvents'   => $timelineEvents,
            'canViewContracts' => $canViewContracts,
            'contracts'        => $contracts,
            'suppliers'        => $suppliers,
            'contractStatuses' => \App\Services\Hr\EmploymentContractService::STATUSES,
            'perms'            => $this->hrPermissionFlags([
                'employee.contract.view', 'employee.contract.view_rate', 'employee.contract.edit', 'employee.contract.edit_rate',
                'employee.assignment.view', 'employee.assignment.edit',
                'leave.view', 'leave.apply',
                'employee.salary.view', 'employee.salary.edit',
                'employee.edit',
            ]),
            'activeTab'        => $this->request->getGet('tab') ?: 'overview',
        ]));
    }

    public function edit(int $id)
    {
        $this->requireHrPermission('employee.edit');
        $emp = $this->employees->find($id);
        if (! $emp) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $this->assertEmployeeFacilityAccess($emp);

        $companyId = $emp['company_id'] ? (int) $emp['company_id'] : $this->resolveCompanyId();

        return view('employees/edit', $this->viewData([
            'title'       => 'Edit Employee',
            'hrNavActive' => 'employees',
            'emp'     => $emp,
            'options' => $this->employees->masters()->formOptions($companyId),
            'hrReady' => $this->employees->hrTablesReady(),
        ]));
    }

    public function update(int $id)
    {
        $this->requireHrPermission('employee.edit');
        $emp = $this->employees->find($id);
        if (! $emp) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $this->assertEmployeeFacilityAccess($emp);

        $companyId = $emp['company_id'] ? (int) $emp['company_id'] : $this->resolveCompanyId();
        $data      = $this->collectEmployeePayload($companyId, includeSensitive: true);
        $this->employees->update($id, $data, (int) session()->get('user_id'));

        return redirect()->to(base_url('employees/view/' . $id))->with('success', 'Employee updated.');
    }

    public function delete(int $id)
    {
        $this->requireHrPermission('employee.delete');
        $emp = $this->employees->find($id);
        if (! $emp) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $this->assertEmployeeFacilityAccess($emp);
        $this->employees->deactivate($id, (int) session()->get('user_id'));

        return redirect()->to(base_url('employees'))->with('success', 'Employee deactivated.');
    }

    public function checkin()
    {
        $this->requireHrPermission('attendance');
        helper('fm');
        $uid = fm_session_user_id();
        $emp = $this->db->table('employees')->where('user_id', $uid)->get()->getRowArray();
        if (! $emp) {
            return $this->response->setJSON(['status' => false, 'message' => 'Employee not found']);
        }

        $svc    = new \App\Services\Hr\HrAttendanceService($this->db);
        $result = $svc->recordPunch((int) $emp['id'], 'check_in', [
            'latitude'  => (float) $this->request->getPost('latitude'),
            'longitude' => (float) $this->request->getPost('longitude'),
            'source'    => 'web',
        ]);

        return $this->response->setJSON([
            'status'  => $result['status'],
            'message' => $result['message'],
        ]);
    }

    public function checkout()
    {
        $this->requireHrPermission('attendance');
        helper('fm');
        $uid = fm_session_user_id();
        $emp = $this->db->table('employees')->where('user_id', $uid)->get()->getRowArray();
        if (! $emp) {
            return $this->response->setJSON(['status' => false, 'message' => 'Employee not found']);
        }

        $svc    = new \App\Services\Hr\HrAttendanceService($this->db);
        $result = $svc->recordPunch((int) $emp['id'], 'check_out', [
            'reason' => $this->request->getPost('reason') ?? '',
            'source' => 'web',
        ]);

        return $this->response->setJSON([
            'status'   => $result['status'],
            'message'  => $result['message'],
            'overtime' => $result['overtime'] ?? 0,
        ]);
    }

    public function startBreak()
    {
        $this->requireHrPermission('attendance');
        helper('fm');
        $uid = fm_session_user_id();
        $emp = $this->db->table('employees')->where('user_id', $uid)->get()->getRowArray();
        if (! $emp) {
            return $this->response->setJSON(['status' => false, 'message' => 'Employee not found']);
        }

        $this->db->table('employee_breaks')->insert([
            'employee_id' => $emp['id'],
            'break_start' => date('Y-m-d H:i:s'),
            'date'        => date('Y-m-d'),
        ]);

        return $this->response->setJSON(['status' => true, 'message' => 'Break started']);
    }

    public function endBreak()
    {
        $this->requireHrPermission('attendance');
        helper('fm');
        $uid = fm_session_user_id();
        $emp = $this->db->table('employees')->where('user_id', $uid)->get()->getRowArray();
        if (! $emp) {
            return $this->response->setJSON(['status' => false, 'message' => 'Employee not found']);
        }

        $break = $this->db->table('employee_breaks')
            ->where('employee_id', $emp['id'])
            ->where('date', date('Y-m-d'))
            ->where('break_end', null)
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        if ($break) {
            $mins = (int) round((time() - strtotime($break['break_start'])) / 60);
            $this->db->table('employee_breaks')->where('id', $break['id'])->update([
                'break_end'     => date('Y-m-d H:i:s'),
                'duration_mins' => $mins,
            ]);
        }

        return $this->response->setJSON(['status' => true, 'message' => 'Break ended']);
    }

    public function attendance()
    {
        if ($this->hrCan('attendance.view')) {
            return redirect()->to(base_url('hr/attendance?' . http_build_query([
                'month'  => $this->request->getGet('month') ?: date('Y-m'),
                'emp_id' => $this->request->getGet('emp_id'),
            ])));
        }

        $this->requireHrPermission('employees');
        $empId = $this->request->getGet('emp_id');
        $month = $this->request->getGet('month') ?? date('Y-m');

        $q = $this->db->table('attendance a')
            ->select('a.*, e.emp_code, u.name')
            ->join('employees e', 'e.id = a.employee_id', 'left')
            ->join('users u', 'u.id = e.user_id', 'left');

        if ($empId) {
            $q->where('a.employee_id', $empId);
        }
        $q->like('a.date', $month, 'after');
        $records = $q->orderBy('a.date', 'DESC')->get()->getResultArray();

        $employees = $this->db->table('employees e')
            ->select('e.id, u.name')
            ->join('users u', 'u.id = e.user_id', 'left')
            ->where('e.status', 'active')
            ->get()->getResultArray();

        return view('employees/attendance', $this->viewData([
            'title'       => 'Attendance',
            'hrNavActive' => 'employees',
            'records'     => $records,
            'employees'   => $employees,
            'month'       => $month,
            'selectedEmp' => $empId,
        ]));
    }

    public function show(int $id)
    {
        return $this->view($id);
    }

    /** @return array<string, mixed> */
    private function collectEmployeePayload(?int $companyId, bool $includeSensitive = false): array
    {
        $post = $this->request->getPost();
        $deptId = (int) ($post['department_id'] ?? 0) ?: null;
        $desigId = (int) ($post['designation_id'] ?? 0) ?: null;

        $data = [
            'company_id'            => $companyId,
            'operating_company_id'  => (int) ($post['operating_company_id'] ?? 0) ?: null,
            'user_id'               => (int) ($post['user_id'] ?? 0) ?: null,
            'facility_id'           => (int) ($post['facility_id'] ?? 0) ?: null,
            'cost_center_id'        => (int) ($post['cost_center_id'] ?? 0) ?: null,
            'department_id'         => $deptId,
            'designation_id'        => $desigId,
            'department'            => esc(trim((string) ($post['department'] ?? ''))),
            'designation'           => esc(trim((string) ($post['designation'] ?? ''))),
            'grade_id'              => (int) ($post['grade_id'] ?? 0) ?: null,
            'employee_type_id'      => (int) ($post['employee_type_id'] ?? 0) ?: null,
            'employment_source_id'  => (int) ($post['employment_source_id'] ?? 0) ?: null,
            'status_id'             => (int) ($post['status_id'] ?? 0) ?: null,
            'reporting_manager_id'  => (int) ($post['reporting_manager_id'] ?? 0) ?: null,
            'shift_start'           => $post['shift_start'] ?: '08:00',
            'shift_end'             => $post['shift_end'] ?: '17:00',
            'hourly_rate'           => (float) ($post['hourly_rate'] ?? 0),
            'hire_date'             => $post['hire_date'] ?: null,
            'joining_date'          => $post['joining_date'] ?: ($post['hire_date'] ?: null),
            'confirmation_date'     => $post['confirmation_date'] ?: null,
            'probation_end_date'    => $post['probation_end_date'] ?: null,
            'wps_applicable'        => $post['wps_applicable'] ? 1 : 0,
            'payroll_applicable'    => $post['payroll_applicable'] ? 1 : 0,
            'leave_applicable'      => $post['leave_applicable'] ? 1 : 0,
            'attendance_applicable' => $post['attendance_applicable'] ? 1 : 0,
            'overtime_applicable'   => $post['overtime_applicable'] ? 1 : 0,
            'payroll_responsibility'=> $post['payroll_responsibility'] ?: 'our_company',
            'first_name'            => esc(trim((string) ($post['first_name'] ?? ''))),
            'middle_name'           => esc(trim((string) ($post['middle_name'] ?? ''))),
            'last_name'             => esc(trim((string) ($post['last_name'] ?? ''))),
            'name_ar'               => esc(trim((string) ($post['name_ar'] ?? ''))),
            'gender'                => $post['gender'] ?: null,
            'date_of_birth'         => $post['date_of_birth'] ?: null,
            'nationality'           => esc(trim((string) ($post['nationality'] ?? ''))),
            'marital_status'        => esc(trim((string) ($post['marital_status'] ?? ''))),
            'personal_mobile'       => esc(trim((string) ($post['personal_mobile'] ?? ''))),
            'personal_email'        => esc(trim((string) ($post['personal_email'] ?? ''))),
            'current_address'       => esc(trim((string) ($post['current_address'] ?? ''))),
            'permanent_address'     => esc(trim((string) ($post['permanent_address'] ?? ''))),
            'emergency_contact_name' => esc(trim((string) ($post['emergency_contact_name'] ?? ''))),
            'emergency_contact_relationship' => esc(trim((string) ($post['emergency_contact_relationship'] ?? ''))),
            'emergency_contact_phone' => esc(trim((string) ($post['emergency_contact_phone'] ?? ''))),
        ];

        if (! empty($post['status'])) {
            $data['status'] = $post['status'];
        }

        if ($includeSensitive) {
            $rbac = new RbacService($this->db);
            $role = (string) session()->get('user_role');
            if ($rbac->can($role, 'employee.identification.view') || $rbac->can($role, '*')) {
                $data['qid_number'] = esc(trim((string) ($post['qid_number'] ?? '')));
                $data['qid_issue_date'] = $post['qid_issue_date'] ?: null;
                $data['qid_expiry'] = $post['qid_expiry'] ?: null;
                $data['passport_number'] = esc(trim((string) ($post['passport_number'] ?? '')));
                $data['passport_issue_date'] = $post['passport_issue_date'] ?: null;
                $data['passport_expiry'] = $post['passport_expiry'] ?: null;
                $data['passport_country'] = esc(trim((string) ($post['passport_country'] ?? '')));
                $data['visa_number'] = esc(trim((string) ($post['visa_number'] ?? '')));
                $data['visa_type'] = esc(trim((string) ($post['visa_type'] ?? '')));
                $data['visa_issue_date'] = $post['visa_issue_date'] ?: null;
                $data['visa_expiry'] = $post['visa_expiry'] ?: null;
            }
            if ($rbac->can($role, 'employee.salary.view') || $rbac->can($role, '*')) {
                $data['bank_name'] = esc(trim((string) ($post['bank_name'] ?? '')));
                $data['bank_account_number'] = esc(trim((string) ($post['bank_account_number'] ?? '')));
                $data['iban'] = esc(trim((string) ($post['iban'] ?? '')));
            }
        }

        return $data;
    }

    private function resolveCompanyId(): ?int
    {
        $user = $this->currentUser();
        if (! empty($user['company_id'])) {
            return (int) $user['company_id'];
        }
        $posted = (int) ($this->request->getPost('company_id') ?? $this->request->getGet('company_id') ?? 0);

        return $posted > 0 ? $posted : null;
    }

    /** @param array<string, mixed> $emp */
    private function assertEmployeeFacilityAccess(array $emp): void
    {
        if (! empty($emp['facility_id'])) {
            $this->assertFacilityAccess((int) $emp['facility_id']);
        }
    }
}
