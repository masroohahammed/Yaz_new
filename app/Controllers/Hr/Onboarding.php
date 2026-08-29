<?php

namespace App\Controllers\Hr;

use App\Controllers\BaseController;
use App\Controllers\Traits\HrRbacTrait;
use App\Services\Hr\EmployeeService;
use App\Services\Hr\OnboardingService;

class Onboarding extends BaseController
{
    use HrRbacTrait;

    protected ?string $workspaceRequired = 'fm';

    private OnboardingService $onboarding;

    public function __construct()
    {
        $this->onboarding = new OnboardingService($this->db);
    }

    public function index()
    {
        $this->requireHrPermission('employee.edit');

        $employeeId = (int) ($this->request->getGet('employee_id') ?: 0) ?: null;
        $companyId  = (int) ($this->currentUser()['company_id'] ?? 0) ?: null;

        $employeesQ = $this->db->table('employees e')
            ->select('e.id, e.emp_code, u.name, hs.name AS status_name')
            ->join('users u', 'u.id = e.user_id', 'left')
            ->join('hr_employee_statuses hs', 'hs.id = e.status_id', 'left')
            ->orderBy('u.name');
        if ($companyId) {
            $employeesQ->where('e.company_id', $companyId);
        }

        $instance = $employeeId ? $this->onboarding->instanceForEmployee($employeeId) : null;

        return view('hr/onboarding/index', $this->viewData([
            'title'             => 'Employee Onboarding',
            'employees'         => $employeesQ->get()->getResultArray(),
            'selectedEmp'       => $employeeId,
            'instance'          => $instance,
            'tasks'             => $instance ? $this->onboarding->tasksForInstance((int) $instance['id']) : [],
            'checklists'        => $this->onboarding->checklists($companyId),
            'migrationRequired' => ! $this->onboarding->tablesReady(),
        ]));
    }

    public function start(int $employeeId)
    {
        $this->requireHrPermission('employee.edit');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        try {
            $this->onboarding->startOnboarding($employeeId, (int) session()->get('user_id'));
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to(base_url('hr/onboarding?employee_id=' . $employeeId))->with('success', 'Onboarding started.');
    }

    public function completeTask(int $taskStatusId)
    {
        $this->requireHrPermission('employee.edit');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        $this->onboarding->completeTask($taskStatusId, (int) session()->get('user_id'), esc(trim((string) $this->request->getPost('notes'))));

        return redirect()->back()->with('success', 'Task marked complete.');
    }
}
