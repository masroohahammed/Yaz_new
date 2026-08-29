<?php

namespace App\Controllers\Hr;

use App\Controllers\BaseController;
use App\Controllers\Traits\HrRbacTrait;
use App\Services\Hr\EmployeeAssignmentService;
use App\Services\Hr\EmployeeService;

class EmployeeAssignments extends BaseController
{
    use HrRbacTrait;

    protected ?string $workspaceRequired = 'fm';

    private EmployeeAssignmentService $assignments;

    public function __construct()
    {
        $this->assignments = new EmployeeAssignmentService();
    }

    public function store()
    {
        $this->requireHrPermission('employee.assignment.edit');
        if (! $this->request->is('post') || ! $this->assignments->tablesReady()) {
            return redirect()->back()->with('error', 'Invalid request or run HR migration first.');
        }

        $employeeId = (int) $this->request->getPost('employee_id');
        $emp        = (new EmployeeService())->find($employeeId);
        if (! $emp) {
            return redirect()->back()->with('error', 'Employee not found.');
        }
        if (! empty($emp['facility_id'])) {
            $this->assertFacilityAccess((int) $emp['facility_id']);
        }

        try {
            $this->assignments->create($this->collectPayload($emp), (int) session()->get('user_id'));
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Could not save assignment.');
        }

        return redirect()->to(base_url('employees/view/' . $employeeId . '?tab=assignment'))->with('success', 'Assignment saved.');
    }

    public function update(int $id)
    {
        $this->requireHrPermission('employee.assignment.edit');
        $row = $this->assignments->find($id);
        if (! $row) {
            return redirect()->back()->with('error', 'Assignment not found.');
        }

        $emp = (new EmployeeService())->find((int) $row['employee_id']);
        if (! $emp) {
            return redirect()->back()->with('error', 'Employee not found.');
        }
        if (! empty($emp['facility_id'])) {
            $this->assertFacilityAccess((int) $emp['facility_id']);
        }

        $this->assignments->update($id, $this->collectPayload($emp, $row), (int) session()->get('user_id'));

        return redirect()->to(base_url('employees/view/' . $row['employee_id'] . '?tab=assignment'))->with('success', 'Assignment updated.');
    }

    public function end(int $id)
    {
        $this->requireHrPermission('employee.assignment.edit');
        $row = $this->assignments->find($id);
        if (! $row) {
            return redirect()->back()->with('error', 'Assignment not found.');
        }

        $endDate = $this->request->getPost('end_date') ?: date('Y-m-d');
        $this->assignments->endAssignment($id, $endDate, (int) session()->get('user_id'));

        return redirect()->to(base_url('employees/view/' . $row['employee_id'] . '?tab=assignment'))->with('success', 'Assignment ended.');
    }

    public function transfer(int $id)
    {
        $this->requireHrPermission('employee.assignment.edit');
        $row = $this->assignments->find($id);
        if (! $row) {
            return redirect()->back()->with('error', 'Assignment not found.');
        }

        $emp = (new EmployeeService())->find((int) $row['employee_id']);
        if (! $emp) {
            return redirect()->back()->with('error', 'Employee not found.');
        }

        $newData = [
            'facility_id'       => (int) ($this->request->getPost('facility_id') ?: 0) ?: null,
            'unit_id'           => (int) ($this->request->getPost('unit_id') ?: 0) ?: null,
            'assignment_type'   => $this->request->getPost('assignment_type') ?: 'primary',
            'start_date'        => $this->request->getPost('start_date') ?: date('Y-m-d'),
            'end_date'          => $this->request->getPost('end_date') ?: null,
            'allocation_pct'    => (float) ($this->request->getPost('allocation_pct') ?: 100),
            'role_on_site'      => esc(trim((string) $this->request->getPost('role_on_site'))),
            'remarks'           => esc(trim((string) $this->request->getPost('remarks'))),
            'company_id'        => $emp['company_id'] ?? null,
        ];

        try {
            $newId = $this->assignments->transfer($id, $newData, (int) session()->get('user_id'));
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Transfer failed.');
        }

        return redirect()->to(base_url('employees/view/' . $row['employee_id'] . '?tab=assignment'))->with('success', 'Employee transferred (assignment #' . $newId . ').');
    }

    /** @param array<string, mixed> $emp */
    /** @param array<string, mixed>|null $existing */
    private function collectPayload(array $emp, ?array $existing = null): array
    {
        $facilityId = (int) ($this->request->getPost('facility_id') ?: 0) ?: null;
        if ($facilityId) {
            $this->assertFacilityAccess($facilityId);
        }

        return [
            'employee_id'        => (int) $emp['id'],
            'company_id'         => $emp['company_id'] ?? null,
            'facility_id'        => $facilityId,
            'unit_id'            => (int) ($this->request->getPost('unit_id') ?: 0) ?: null,
            'contract_id'        => (int) ($this->request->getPost('contract_id') ?: 0) ?: null,
            'assignment_type'    => $this->request->getPost('assignment_type') ?: 'primary',
            'assignment_status'  => $this->request->getPost('assignment_status') ?: 'active',
            'start_date'         => $this->request->getPost('start_date') ?: null,
            'end_date'           => $this->request->getPost('end_date') ?: null,
            'allocation_pct'     => (float) ($this->request->getPost('allocation_pct') ?: 100),
            'role_on_site'       => esc(trim((string) $this->request->getPost('role_on_site'))),
            'client_name'        => esc(trim((string) $this->request->getPost('client_name'))),
            'remarks'            => esc(trim((string) $this->request->getPost('remarks'))),
            'is_current'         => $this->request->getPost('is_current') !== null ? (int) (bool) $this->request->getPost('is_current') : 1,
        ];
    }
}
