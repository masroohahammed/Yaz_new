<?php

namespace App\Controllers\Hr;

use App\Controllers\BaseController;
use App\Controllers\Traits\HrRbacTrait;
use App\Services\Hr\EmployeeTransferService;
use App\Services\Hr\HrMasterDataService;

class Transfers extends BaseController
{
    use HrRbacTrait;

    protected ?string $workspaceRequired = 'fm';

    private EmployeeTransferService $transfers;

    public function __construct()
    {
        $this->transfers = new EmployeeTransferService($this->db);
    }

    public function index()
    {
        $this->requireHrPermission('employee.edit');

        $companyId = (int) ($this->currentUser()['company_id'] ?? 0) ?: null;
        $masters   = new HrMasterDataService($this->db);

        return view('hr/transfers/index', $this->viewData([
            'title'             => 'Employee Transfers',
            'pending'           => $this->transfers->pending($companyId),
            'departments'       => $masters->departments($companyId),
            'facilities'        => $masters->facilities($companyId),
            'branches'          => $masters->operatingCompanies($companyId),
            'canApprove'        => $this->hrCan('transfer.approve'),
            'migrationRequired' => ! $this->transfers->tablesReady(),
        ]));
    }

    public function store()
    {
        $this->requireHrPermission('employee.edit');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        try {
            $this->transfers->submit([
                'employee_id'             => (int) $this->request->getPost('employee_id'),
                'to_department_id'        => (int) ($this->request->getPost('to_department_id') ?: 0) ?: null,
                'to_facility_id'          => (int) ($this->request->getPost('to_facility_id') ?: 0) ?: null,
                'to_operating_company_id' => (int) ($this->request->getPost('to_operating_company_id') ?: 0) ?: null,
                'to_reporting_manager_id' => (int) ($this->request->getPost('to_reporting_manager_id') ?: 0) ?: null,
                'effective_date'          => $this->request->getPost('effective_date') ?: date('Y-m-d'),
                'reason'                  => esc(trim((string) $this->request->getPost('reason'))),
            ], (int) session()->get('user_id'));
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->to(base_url('hr/transfers'))->with('success', 'Transfer request submitted.');
    }

    public function approve(int $id)
    {
        $this->requireHrPermission('transfer.approve');
        if (! $this->transfers->approve($id, (int) session()->get('user_id'), esc(trim((string) $this->request->getPost('notes'))))) {
            return redirect()->back()->with('error', 'Could not approve transfer.');
        }

        return redirect()->to(base_url('hr/transfers'))->with('success', 'Transfer processed.');
    }

    public function reject(int $id)
    {
        $this->requireHrPermission('transfer.approve');
        $this->transfers->reject($id, (int) session()->get('user_id'), esc(trim((string) $this->request->getPost('notes'))));

        return redirect()->to(base_url('hr/transfers'))->with('success', 'Transfer rejected.');
    }
}
