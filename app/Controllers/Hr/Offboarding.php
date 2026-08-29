<?php

namespace App\Controllers\Hr;

use App\Controllers\BaseController;
use App\Controllers\Traits\HrRbacTrait;
use App\Services\Hr\EmployeeLifecycleService;
use App\Services\Hr\FinalSettlementService;
use App\Services\Hr\OffboardingService;

class Offboarding extends BaseController
{
    use HrRbacTrait;

    protected ?string $workspaceRequired = 'fm';

    private OffboardingService $offboarding;
    private FinalSettlementService $settlement;

    public function __construct()
    {
        $this->offboarding  = new OffboardingService($this->db);
        $this->settlement   = new FinalSettlementService($this->db);
    }

    public function show(int $employeeId)
    {
        $this->requireHrPermission('employees');

        $emp = (new \App\Services\Hr\EmployeeService($this->db))->find($employeeId);
        if (! $emp) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $instance   = $this->offboarding->instanceForEmployee($employeeId);
        $settlement = $this->settlement->forEmployee($employeeId);

        return view('hr/offboarding/show', $this->viewData([
            'title'             => 'Offboarding — ' . ($emp['name'] ?? $emp['emp_code']),
            'emp'               => $emp,
            'instance'          => $instance,
            'clearanceItems'    => $instance ? $this->offboarding->itemsForInstance((int) $instance['id']) : [],
            'settlement'        => $settlement,
            'settlementLines'   => $settlement ? $this->settlement->lines((int) $settlement['id']) : [],
            'lifecycle'         => new EmployeeLifecycleService($this->db),
            'migrationRequired' => ! $this->offboarding->tablesReady(),
            'canApprove'        => $this->hrCan('settlement.approve'),
        ]));
    }

    public function startClearance(int $employeeId)
    {
        $this->requireHrPermission('employee.edit');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        try {
            $this->offboarding->startClearance(
                $employeeId,
                (int) session()->get('user_id'),
                $this->request->getPost('separation_type') ?: 'resignation'
            );
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to(base_url('hr/offboarding/' . $employeeId))->with('success', 'Exit clearance started.');
    }

    public function clearItem(int $itemStatusId)
    {
        $this->requireHrPermission('employee.edit');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        $this->offboarding->clearItem($itemStatusId, (int) session()->get('user_id'));

        return redirect()->back()->with('success', 'Clearance item updated.');
    }

    public function calculateSettlement(int $employeeId)
    {
        $this->requireHrPermission('employee.salary.view');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        $lwd = $this->request->getPost('last_working_date') ?: date('Y-m-d');

        try {
            $this->settlement->calculate($employeeId, $lwd, (int) session()->get('user_id'));
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to(base_url('hr/offboarding/' . $employeeId))->with('success', 'Settlement calculated.');
    }

    public function approveSettlement(int $id)
    {
        $this->requireHrPermission('settlement.approve');
        if (! $this->settlement->approve($id, (int) session()->get('user_id'))) {
            return redirect()->back()->with('error', 'Could not approve settlement.');
        }

        $row = $this->settlement->find($id);
        if ($row) {
            (new EmployeeLifecycleService($this->db))->completeInactive((int) $row['employee_id'], (int) session()->get('user_id'), 'Settlement approved');
        }

        return redirect()->back()->with('success', 'Settlement approved.');
    }
}
