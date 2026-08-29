<?php

namespace App\Controllers\Hr;

use App\Controllers\BaseController;
use App\Controllers\Traits\HrRbacTrait;
use App\Services\Hr\HrAttendanceService;
use App\Services\Hr\HrShiftService;

class Attendance extends BaseController
{
    use HrRbacTrait;

    protected ?string $workspaceRequired = 'fm';

    private HrAttendanceService $attendance;

    public function __construct()
    {
        $this->attendance = new HrAttendanceService($this->db);
    }

    public function index()
    {
        $this->requireHrPermission('attendance.view');

        $filters = [
            'month'       => $this->request->getGet('month') ?: date('Y-m'),
            'employee_id' => (int) ($this->request->getGet('emp_id') ?: 0) ?: null,
            'facility_id' => (int) ($this->request->getGet('facility_id') ?: 0) ?: null,
            'company_id'  => (int) ($this->request->getGet('company_id') ?: $this->currentUser()['company_id'] ?? 0) ?: null,
        ];

        $employeesQ = $this->db->table('employees e')
            ->select('e.id, u.name')
            ->join('users u', 'u.id = e.user_id', 'left')
            ->where('e.status', 'active')
            ->orderBy('u.name');
        if ($filters['company_id'] && $this->db->fieldExists('company_id', 'employees')) {
            $employeesQ->where('e.company_id', $filters['company_id']);
        }
        $this->scopeFacilities($employeesQ, 'e.facility_id');
        $employees = $employeesQ->get()->getResultArray();

        $recordsQ = $this->attendance->listRecords($filters);
        if ($filters['facility_id']) {
            // already filtered in service
        }

        return view('hr/attendance/index', $this->viewData([
            'title'       => 'Attendance',
            'records'     => $recordsQ,
            'employees'   => $employees,
            'filters'     => $filters,
            'canAdjust'   => $this->hrCan('attendance.adjust'),
            'canApprove'  => $this->hrCan('attendance.approve'),
            'migrationRequired' => ! $this->attendance->tablesReady(),
        ]));
    }

    public function regularizations()
    {
        $this->requireHrPermission('attendance.view');

        $companyId = (int) ($this->currentUser()['company_id'] ?? 0) ?: null;

        return view('hr/attendance/regularizations', $this->viewData([
            'title'      => 'Attendance Regularizations',
            'pending'    => $this->attendance->pendingRegularizations($companyId),
            'canApprove' => $this->hrCan('attendance.approve'),
            'canAdjust'  => $this->hrCan('attendance.adjust'),
            'migrationRequired' => ! $this->attendance->regularizationsReady(),
        ]));
    }

    public function submitRegularization()
    {
        $this->requireHrPermission('attendance.adjust');
        if (! $this->request->is('post') || ! $this->attendance->regularizationsReady()) {
            return redirect()->back()->with('error', 'Invalid request.');
        }

        $employeeId = (int) $this->request->getPost('employee_id');
        $date       = $this->request->getPost('attendance_date') ?: date('Y-m-d');

        try {
            $this->attendance->submitRegularization([
                'employee_id'         => $employeeId,
                'attendance_date'     => $date,
                'requested_check_in'  => $this->request->getPost('requested_check_in') ?: null,
                'requested_check_out' => $this->request->getPost('requested_check_out') ?: null,
                'requested_status'    => $this->request->getPost('requested_status') ?: 'present',
                'reason'              => esc(trim((string) $this->request->getPost('reason'))),
            ], (int) session()->get('user_id'));
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Could not submit regularization.');
        }

        return redirect()->to(base_url('hr/attendance/regularizations'))->with('success', 'Regularization submitted for approval.');
    }

    public function approveRegularization(int $id)
    {
        $this->requireHrPermission('attendance.approve');
        $notes = trim((string) $this->request->getPost('review_notes'));
        if (! $this->attendance->approveRegularization($id, (int) session()->get('user_id'), $notes ?: null)) {
            return redirect()->back()->with('error', 'Could not approve request.');
        }

        return redirect()->to(base_url('hr/attendance/regularizations'))->with('success', 'Regularization approved and attendance updated.');
    }

    public function rejectRegularization(int $id)
    {
        $this->requireHrPermission('attendance.approve');
        $notes = trim((string) $this->request->getPost('review_notes'));
        if (! $this->attendance->rejectRegularization($id, (int) session()->get('user_id'), $notes ?: null)) {
            return redirect()->back()->with('error', 'Could not reject request.');
        }

        return redirect()->to(base_url('hr/attendance/regularizations'))->with('success', 'Regularization rejected.');
    }

    public function adjust(int $id)
    {
        $this->requireHrPermission('attendance.adjust');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        $ok = $this->attendance->manualAdjust($id, [
            'check_in'  => $this->request->getPost('check_in') ?: null,
            'check_out' => $this->request->getPost('check_out') ?: null,
            'status'    => $this->request->getPost('status') ?: null,
            'notes'     => esc(trim((string) $this->request->getPost('notes'))),
        ], (int) session()->get('user_id'));

        if (! $ok) {
            return redirect()->back()->with('error', 'Attendance record not found.');
        }

        return redirect()->to(base_url('hr/attendance'))->with('success', 'Attendance adjusted.');
    }
}
