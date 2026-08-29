<?php

namespace App\Controllers\Hr;

use App\Controllers\BaseController;
use App\Controllers\Traits\HrRbacTrait;
use App\Services\Hr\EmployeeService;
use App\Services\Hr\HrLeaveService;
use App\Services\Hr\HrSchemaService;

class Leave extends BaseController
{
    use HrRbacTrait;

    protected ?string $workspaceRequired = 'fm';

    private HrLeaveService $leave;

    public function __construct()
    {
        $this->leave = new HrLeaveService($this->db);
    }

    public function index()
    {
        $this->requireHrPermission('leave.view');

        try {
            (new HrSchemaService($this->db))->ensureLeaveOperationsSchema();
        } catch (\Throwable $e) {
            log_message('warning', 'Leave schema align: ' . $e->getMessage());
        }

        if (! $this->leave->tablesReady()) {
            return view('hr/leave/index', $this->viewData([
                'title'             => 'Leave',
                'hrNavActive'       => 'leave',
                'migrationRequired' => true,
            ]));
        }

        $companyId  = (int) ($this->currentUser()['company_id'] ?? 0) ?: null;
        $employeeId = (int) ($this->request->getGet('employee_id') ?: 0) ?: null;
        $myEmployee = $this->resolveMyEmployeeId();

        if (! $employeeId && ! $this->hrCan('leave.approve') && $myEmployee) {
            $employeeId = $myEmployee;
        }

        $employeesQ = $this->db->table('employees e')
            ->select('e.id, e.emp_code, u.name, e.leave_applicable')
            ->join('users u', 'u.id = e.user_id', 'left')
            ->where('e.status', 'active')
            ->orderBy('u.name');
        if ($companyId) {
            $employeesQ->where('e.company_id', $companyId);
        }
        $this->scopeFacilities($employeesQ, 'e.facility_id');

        return view('hr/leave/index', $this->viewData([
            'title'        => 'Leave Management',
            'hrNavActive'  => 'leave',
            'requests'     => $this->leave->listRequests(array_filter([
                'employee_id' => $employeeId,
                'company_id'  => $companyId,
            ])),
            'balances'     => $employeeId ? $this->leave->balancesForEmployee($employeeId) : [],
            'leaveTypes'   => $this->leave->leaveTypes($companyId),
            'employees'    => $employeesQ->get()->getResultArray(),
            'selectedEmp'  => $employeeId,
            'myEmployeeId' => $myEmployee,
            'canApply'     => $this->hrCan('leave.apply'),
            'canApprove'   => $this->hrCan('leave.approve'),
            'statuses'     => HrLeaveService::STATUSES,
        ]));
    }

    public function apply()
    {
        $this->requireHrPermission('leave.apply');
        if (! $this->request->is('post') || ! $this->leave->tablesReady()) {
            return redirect()->back()->with('error', 'Invalid request.');
        }

        $employeeId = (int) ($this->request->getPost('employee_id') ?: 0);
        $myEmployee = $this->resolveMyEmployeeId();
        if (! $this->hrCan('leave.approve') && $myEmployee && $employeeId !== $myEmployee) {
            $employeeId = $myEmployee;
        }

        if ($employeeId < 1) {
            return redirect()->back()->with('error', 'Employee not linked to your account.');
        }

        $this->leave->initializeBalances($employeeId, (int) ($this->currentUser()['company_id'] ?? 0) ?: null);

        try {
            $this->leave->submitRequest([
                'employee_id'    => $employeeId,
                'leave_type_id'  => (int) $this->request->getPost('leave_type_id'),
                'start_date'     => $this->request->getPost('start_date'),
                'end_date'       => $this->request->getPost('end_date') ?: $this->request->getPost('start_date'),
                'half_day'       => $this->request->getPost('half_day') ?: 'none',
                'reason'         => esc(trim((string) $this->request->getPost('reason'))),
            ], (int) session()->get('user_id'));
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to(base_url('hr/leave?employee_id=' . $employeeId))->with('success', 'Leave request submitted.');
    }

    public function approvals()
    {
        $this->requireHrPermission('leave.approve');

        try {
            (new HrSchemaService($this->db))->ensureLeaveOperationsSchema();
        } catch (\Throwable $e) {
            log_message('warning', 'Leave schema align: ' . $e->getMessage());
        }

        $companyId = (int) ($this->currentUser()['company_id'] ?? 0) ?: null;

        return view('hr/leave/approvals', $this->viewData([
            'title'             => 'Leave Approvals',
            'hrNavActive'       => 'leave',
            'pending'           => $this->leave->pendingApprovals($companyId),
            'migrationRequired' => ! $this->leave->tablesReady(),
            'statuses'          => HrLeaveService::STATUSES,
        ]));
    }

    public function approve(int $id)
    {
        $this->requireHrPermission('leave.approve');
        $notes = trim((string) $this->request->getPost('review_notes'));
        if (! $this->leave->approveRequest($id, (int) session()->get('user_id'), $notes ?: null)) {
            return redirect()->back()->with('error', 'Could not approve leave request.');
        }

        return redirect()->to(base_url('hr/leave/approvals'))->with('success', 'Leave approved and attendance updated.');
    }

    public function reject(int $id)
    {
        $this->requireHrPermission('leave.approve');
        $notes = trim((string) $this->request->getPost('review_notes'));
        if (! $this->leave->rejectRequest($id, (int) session()->get('user_id'), $notes ?: null)) {
            return redirect()->back()->with('error', 'Could not reject leave request.');
        }

        return redirect()->to(base_url('hr/leave/approvals'))->with('success', 'Leave request rejected.');
    }

    public function initBalances(int $employeeId)
    {
        $this->requireHrPermission('leave.approve');
        $emp = (new EmployeeService($this->db))->find($employeeId);
        if (! $emp) {
            return redirect()->back()->with('error', 'Employee not found.');
        }

        $count = $this->leave->initializeBalances($employeeId, $emp['company_id'] ?? null);

        return redirect()->to(base_url('hr/leave?employee_id=' . $employeeId))->with('success', "Initialized {$count} leave balance(s).");
    }

    private function resolveMyEmployeeId(): ?int
    {
        helper('fm');
        $uid = function_exists('fm_session_user_id') ? fm_session_user_id() : (int) session()->get('user_id');
        if ($uid < 1) {
            return null;
        }

        $emp = $this->db->table('employees')->select('id')->where('user_id', $uid)->get()->getRowArray();

        return $emp ? (int) $emp['id'] : null;
    }
}
