<?php

namespace App\Controllers\Hr;

use App\Controllers\BaseController;
use App\Controllers\Traits\HrRbacTrait;
use App\Services\Hr\EmployeeService;
use App\Services\Hr\HrAdvanceLoanService;

class Compensation extends BaseController
{
    use HrRbacTrait;

    protected ?string $workspaceRequired = 'fm';

    private HrAdvanceLoanService $comp;

    public function __construct()
    {
        $this->comp = new HrAdvanceLoanService($this->db);
    }

    public function advances()
    {
        $this->requireHrPermission('employee.salary.view');

        $companyId  = (int) ($this->currentUser()['company_id'] ?? 0) ?: null;
        $employeeId = (int) ($this->request->getGet('employee_id') ?: 0) ?: null;

        return view('hr/compensation/advances', $this->viewData([
            'title'             => 'Salary Advances',
            'pending'           => $this->comp->pendingAdvances($companyId),
            'records'           => $employeeId ? $this->comp->advancesForEmployee($employeeId) : [],
            'selectedEmp'       => $employeeId,
            'canApprove'        => $this->hrCan('advance.approve'),
            'canApply'          => $this->hrCan('employee.salary.view'),
            'migrationRequired' => ! $this->comp->advancesReady(),
        ]));
    }

    public function loans()
    {
        $this->requireHrPermission('employee.salary.view');

        $companyId  = (int) ($this->currentUser()['company_id'] ?? 0) ?: null;
        $employeeId = (int) ($this->request->getGet('employee_id') ?: 0) ?: null;

        return view('hr/compensation/loans', $this->viewData([
            'title'             => 'Employee Loans',
            'pending'           => $this->comp->pendingLoans($companyId),
            'records'           => $employeeId ? $this->comp->loansForEmployee($employeeId) : [],
            'selectedEmp'       => $employeeId,
            'canApprove'        => $this->hrCan('loan.approve'),
            'migrationRequired' => ! $this->comp->loansReady(),
        ]));
    }

    public function storeAdvance()
    {
        $this->requireHrPermission('employee.salary.view');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        try {
            $this->comp->submitAdvance([
                'employee_id'         => (int) $this->request->getPost('employee_id'),
                'amount'              => (float) $this->request->getPost('amount'),
                'installments'        => (int) ($this->request->getPost('installments') ?: 1),
                'recovery_start_date' => $this->request->getPost('recovery_start_date') ?: null,
                'reason'              => esc(trim((string) $this->request->getPost('reason'))),
            ], (int) session()->get('user_id'));
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to(base_url('hr/compensation/advances'))->with('success', 'Advance request submitted.');
    }

    public function storeLoan()
    {
        $this->requireHrPermission('employee.salary.view');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        try {
            $this->comp->submitLoan([
                'employee_id'   => (int) $this->request->getPost('employee_id'),
                'principal'     => (float) $this->request->getPost('principal'),
                'tenure_months' => (int) ($this->request->getPost('tenure_months') ?: 12),
                'interest_rate' => (float) ($this->request->getPost('interest_rate') ?: 0),
                'start_date'    => $this->request->getPost('start_date') ?: null,
                'reason'        => esc(trim((string) $this->request->getPost('reason'))),
            ], (int) session()->get('user_id'));
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to(base_url('hr/compensation/loans'))->with('success', 'Loan request submitted.');
    }

    public function approveAdvance(int $id)
    {
        $this->requireHrPermission('advance.approve');
        if (! $this->comp->approveAdvance($id, (int) session()->get('user_id'))) {
            return redirect()->back()->with('error', 'Could not approve advance.');
        }

        return redirect()->to(base_url('hr/compensation/advances'))->with('success', 'Advance approved.');
    }

    public function rejectAdvance(int $id)
    {
        $this->requireHrPermission('advance.approve');
        $this->comp->rejectAdvance($id, (int) session()->get('user_id'));

        return redirect()->to(base_url('hr/compensation/advances'))->with('success', 'Advance rejected.');
    }

    public function approveLoan(int $id)
    {
        $this->requireHrPermission('loan.approve');
        if (! $this->comp->approveLoan($id, (int) session()->get('user_id'))) {
            return redirect()->back()->with('error', 'Could not approve loan.');
        }

        return redirect()->to(base_url('hr/compensation/loans'))->with('success', 'Loan approved and schedule generated.');
    }

    public function rejectLoan(int $id)
    {
        $this->requireHrPermission('loan.approve');
        $this->comp->rejectLoan($id, (int) session()->get('user_id'));

        return redirect()->to(base_url('hr/compensation/loans'))->with('success', 'Loan rejected.');
    }
}
