<?php

namespace App\Controllers\Hr;

use App\Controllers\BaseController;
use App\Controllers\Traits\HrRbacTrait;
use App\Services\Hr\EmployeeService;
use App\Services\Hr\HrShiftService;

class Shifts extends BaseController
{
    use HrRbacTrait;

    protected ?string $workspaceRequired = 'fm';

    private HrShiftService $shifts;

    public function __construct()
    {
        $this->shifts = new HrShiftService($this->db);
    }

    public function index()
    {
        $this->requireHrPermission('attendance.adjust');

        $companyId = $this->resolveCompanyId();

        if (! $this->shifts->tablesReady()) {
            return view('hr/shifts/index', $this->viewData([
                'title'             => 'Shift Master',
                'migrationRequired' => true,
            ]));
        }

        $employeesQ = $this->db->table('employees e')
            ->select('e.id, e.emp_code, u.name')
            ->join('users u', 'u.id = e.user_id', 'left')
            ->where('e.status', 'active')
            ->orderBy('u.name');
        if ($companyId) {
            $employeesQ->where('e.company_id', $companyId);
        }
        $this->scopeFacilities($employeesQ, 'e.facility_id');

        $facilitiesQ = $this->db->table('facilities')->where('status', 'active')->orderBy('name');
        $this->scopeFacilities($facilitiesQ, 'facilities.id');

        return view('hr/shifts/index', $this->viewData([
            'title'      => 'Shift Master',
            'shifts'     => $this->shifts->list($companyId),
            'employees'  => $employeesQ->get()->getResultArray(),
            'facilities' => $facilitiesQ->get()->getResultArray(),
            'companyId'  => $companyId,
        ]));
    }

    public function store()
    {
        $this->requireHrPermission('attendance.adjust');
        if (! $this->request->is('post') || ! $this->shifts->tablesReady()) {
            return redirect()->back()->with('error', 'Invalid request.');
        }

        $companyId = $this->resolveCompanyId();
        $code      = strtoupper(preg_replace('/[^A-Z0-9_]/', '', (string) $this->request->getPost('code')) ?: 'SHIFT');

        try {
            $this->shifts->create([
                'company_id'         => $companyId,
                'code'               => $code,
                'name'               => esc(trim((string) $this->request->getPost('name'))),
                'start_time'         => $this->request->getPost('start_time') ?: '08:00:00',
                'end_time'           => $this->request->getPost('end_time') ?: '17:00:00',
                'break_minutes'      => (int) ($this->request->getPost('break_minutes') ?: 0),
                'grace_in_minutes'   => (int) ($this->request->getPost('grace_in_minutes') ?: 15),
                'grace_out_minutes'  => (int) ($this->request->getPost('grace_out_minutes') ?: 15),
                'is_overnight'       => $this->request->getPost('is_overnight') ? 1 : 0,
                'is_active'          => 1,
            ]);
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Could not save shift.');
        }

        return redirect()->to(base_url('hr/shifts'))->with('success', 'Shift created.');
    }

    public function assign()
    {
        $this->requireHrPermission('attendance.adjust');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        $employeeId = (int) $this->request->getPost('employee_id');
        $emp        = (new EmployeeService($this->db))->find($employeeId);
        if (! $emp) {
            return redirect()->back()->with('error', 'Employee not found.');
        }

        $facilityId = (int) ($this->request->getPost('facility_id') ?: 0) ?: null;
        if ($facilityId) {
            $this->assertFacilityAccess($facilityId);
        }

        try {
            $this->shifts->assignToEmployee([
                'employee_id'    => $employeeId,
                'shift_id'       => (int) $this->request->getPost('shift_id'),
                'facility_id'    => $facilityId,
                'effective_from' => $this->request->getPost('effective_from') ?: date('Y-m-d'),
                'effective_to'   => $this->request->getPost('effective_to') ?: null,
            ], (int) session()->get('user_id'));
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Could not assign shift.');
        }

        return redirect()->to(base_url('hr/shifts'))->with('success', 'Shift assigned to employee.');
    }

    private function resolveCompanyId(): ?int
    {
        $user = $this->currentUser();
        if (! empty($user['company_id'])) {
            return (int) $user['company_id'];
        }

        return null;
    }
}
