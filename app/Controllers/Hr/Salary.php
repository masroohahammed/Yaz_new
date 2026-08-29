<?php

namespace App\Controllers\Hr;

use App\Controllers\BaseController;
use App\Controllers\Traits\HrRbacTrait;
use App\Services\Hr\EmployeeService;
use App\Services\Hr\HrSalaryService;

class Salary extends BaseController
{
    use HrRbacTrait;

    protected ?string $workspaceRequired = 'fm';

    private HrSalaryService $salary;

    public function __construct()
    {
        $this->salary = new HrSalaryService($this->db);
    }

    public function index()
    {
        $this->requireHrPermission('employee.salary.view');

        $employeeId = (int) ($this->request->getGet('employee_id') ?: 0) ?: null;
        $companyId  = (int) ($this->currentUser()['company_id'] ?? 0) ?: null;

        if (! $this->salary->tablesReady()) {
            return view('hr/salary/index', $this->viewData([
                'title'             => 'Salary Structures',
                'migrationRequired' => true,
            ]));
        }

        $employeesQ = $this->db->table('employees e')
            ->select('e.id, e.emp_code, u.name, e.payroll_applicable')
            ->join('users u', 'u.id = e.user_id', 'left')
            ->where('e.status', 'active')
            ->orderBy('u.name');
        if ($companyId) {
            $employeesQ->where('e.company_id', $companyId);
        }
        $this->scopeFacilities($employeesQ, 'e.facility_id');

        $emp = $employeeId ? (new EmployeeService($this->db))->find($employeeId) : null;

        return view('hr/salary/index', $this->viewData([
            'title'       => 'Salary Structures',
            'employees'   => $employeesQ->get()->getResultArray(),
            'components'  => $this->salary->components($companyId),
            'selectedEmp' => $employeeId,
            'emp'         => $emp,
            'structure'   => $employeeId ? $this->salary->currentStructure($employeeId) : null,
            'history'     => $employeeId ? $this->salary->structureHistory($employeeId) : [],
            'revisions'   => $employeeId ? $this->salary->revisions($employeeId) : [],
            'canEdit'     => $this->hrCan('employee.salary.edit'),
        ]));
    }

    public function store()
    {
        $this->requireHrPermission('employee.salary.edit');
        if (! $this->request->is('post') || ! $this->salary->tablesReady()) {
            return redirect()->back()->with('error', 'Invalid request.');
        }

        $employeeId = (int) $this->request->getPost('employee_id');
        $emp        = (new EmployeeService($this->db))->find($employeeId);
        if (! $emp || empty($emp['payroll_applicable'])) {
            return redirect()->back()->with('error', 'Employee not eligible for payroll.');
        }

        $componentIds = (array) $this->request->getPost('component_id');
        $amounts      = (array) $this->request->getPost('amount');
        $lines        = [];
        foreach ($componentIds as $i => $compId) {
            if ((int) $compId < 1) {
                continue;
            }
            $lines[] = [
                'component_id' => (int) $compId,
                'amount'       => (float) ($amounts[$i] ?? 0),
            ];
        }

        try {
            $this->salary->saveStructure($employeeId, [
                'company_id'     => $emp['company_id'] ?? null,
                'effective_from' => $this->request->getPost('effective_from') ?: date('Y-m-d'),
                'currency'       => $this->request->getPost('currency') ?: 'QAR',
                'remarks'        => esc(trim((string) $this->request->getPost('remarks'))),
            ], $lines, (int) session()->get('user_id'));
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Could not save salary structure.');
        }

        return redirect()->to(base_url('hr/salary?employee_id=' . $employeeId))->with('success', 'Salary structure saved (previous version archived).');
    }
}
