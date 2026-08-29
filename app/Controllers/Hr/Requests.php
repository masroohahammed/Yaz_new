<?php

namespace App\Controllers\Hr;

use App\Controllers\BaseController;
use App\Controllers\Traits\HrRbacTrait;
use App\Services\Hr\ApprovalWorkflowService;
use App\Services\Hr\EmployeeTransferService;

class Requests extends BaseController
{
    use HrRbacTrait;

    protected ?string $workspaceRequired = 'fm';

    private ApprovalWorkflowService $approvals;

    public function __construct()
    {
        $this->approvals = new ApprovalWorkflowService($this->db);
    }

    public function index()
    {
        $this->requireHrPermission('employees');

        $companyId = (int) ($this->currentUser()['company_id'] ?? 0) ?: null;

        return view('hr/requests/index', $this->viewData([
            'title'             => 'HR Requests',
            'requests'          => $this->approvals->tablesReady() ? $this->approvals->pendingRequests($companyId) : [],
            'migrationRequired' => ! $this->approvals->tablesReady(),
        ]));
    }

    public function approvals()
    {
        if (! $this->hrCan('transfer.approve') && ! $this->hrCan('settlement.approve') && ! $this->hrCan('leave.approve')) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Access denied.');
        }

        $companyId = (int) ($this->currentUser()['company_id'] ?? 0) ?: null;
        $transfers = (new EmployeeTransferService($this->db))->pending($companyId);

        return view('hr/requests/approvals', $this->viewData([
            'title'             => 'HR Approvals',
            'requests'          => $this->approvals->tablesReady() ? $this->approvals->pendingRequests($companyId) : [],
            'transfers'         => $transfers,
            'canTransfer'       => $this->hrCan('transfer.approve'),
            'canSettlement'     => $this->hrCan('settlement.approve'),
            'migrationRequired' => ! $this->approvals->tablesReady(),
        ]));
    }
}
