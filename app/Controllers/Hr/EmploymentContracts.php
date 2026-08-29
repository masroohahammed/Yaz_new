<?php

namespace App\Controllers\Hr;

use App\Controllers\BaseController;
use App\Controllers\Traits\HrRbacTrait;
use App\Services\Hr\EmploymentContractService;
use App\Services\Hr\HrMasterDataService;

class EmploymentContracts extends BaseController
{
    use HrRbacTrait;

    protected ?string $workspaceRequired = 'fm';

    private EmploymentContractService $contracts;

    public function __construct()
    {
        $this->contracts = new EmploymentContractService();
    }

    public function store()
    {
        $this->requireHrPermission('employee.contract.edit');
        if (! $this->request->is('post') || ! $this->contracts->tablesReady()) {
            return redirect()->back()->with('error', 'Invalid request or run HR migration first.');
        }

        $employeeId = (int) $this->request->getPost('employee_id');
        $emp        = (new \App\Services\Hr\EmployeeService())->find($employeeId);
        if (! $emp) {
            return redirect()->back()->with('error', 'Employee not found.');
        }

        $data = $this->collectPayload($emp);
        try {
            $this->contracts->create($data, (int) session()->get('user_id'));
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Could not save contract.');
        }

        return redirect()->to(base_url('employees/view/' . $employeeId . '?tab=contract'))->with('success', 'Contract saved.');
    }

    public function update(int $id)
    {
        $this->requireHrPermission('employee.contract.edit');
        $contract = $this->contracts->find($id);
        if (! $contract) {
            return redirect()->back()->with('error', 'Contract not found.');
        }
        $emp = (new \App\Services\Hr\EmployeeService())->find((int) $contract['employee_id']);
        if (! $emp) {
            return redirect()->back()->with('error', 'Employee not found.');
        }

        $data = $this->collectPayload($emp, $contract);
        $this->contracts->update($id, $data, (int) session()->get('user_id'));

        return redirect()->to(base_url('employees/view/' . $contract['employee_id'] . '?tab=contract'))->with('success', 'Contract updated.');
    }

    public function renew(int $id)
    {
        $this->requireHrPermission('employee.contract.edit');
        $contract = $this->contracts->find($id);
        if (! $contract) {
            return redirect()->back()->with('error', 'Contract not found.');
        }

        $newData = [
            'contract_number'      => $this->request->getPost('contract_number'),
            'contract_start_date'  => $this->request->getPost('contract_start_date'),
            'contract_end_date'    => $this->request->getPost('contract_end_date'),
            'contract_type'        => $this->request->getPost('contract_type') ?: $contract['contract_type'],
            'supplier_id'          => (int) ($this->request->getPost('supplier_id') ?: $contract['supplier_id'] ?: 0) ?: null,
            'operating_company_id' => (int) ($this->request->getPost('operating_company_id') ?: $contract['operating_company_id'] ?: 0) ?: null,
            'payroll_responsibility' => $this->request->getPost('payroll_responsibility') ?: $contract['payroll_responsibility'],
        ];
        if ($this->hrCan('employee.contract.edit_rate')) {
            $newData['cost_rate'] = (float) ($this->request->getPost('cost_rate') ?: 0) ?: null;
            $newData['billing_rate'] = (float) ($this->request->getPost('billing_rate') ?: 0) ?: null;
            $newData['client_billing_rate'] = (float) ($this->request->getPost('client_billing_rate') ?: 0) ?: null;
        }

        try {
            $newId = $this->contracts->renew($id, $newData, (int) session()->get('user_id'));
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Renewal failed.');
        }

        return redirect()->to(base_url('employees/view/' . $contract['employee_id'] . '?tab=contract'))->with('success', 'Contract renewed (new ID #' . $newId . ').');
    }

    /** @param array<string, mixed> $emp */
    /** @param array<string, mixed>|null $existing */
    private function collectPayload(array $emp, ?array $existing = null): array
    {
        $data = [
            'employee_id'            => (int) $emp['id'],
            'company_id'             => $emp['company_id'] ?? null,
            'operating_company_id'   => (int) ($this->request->getPost('operating_company_id') ?: 0) ?: null,
            'contract_type'          => esc($this->request->getPost('contract_type') ?: 'fixed_term'),
            'contract_number'        => esc(trim((string) $this->request->getPost('contract_number'))),
            'contract_start_date'    => $this->request->getPost('contract_start_date') ?: null,
            'contract_end_date'      => $this->request->getPost('contract_end_date') ?: null,
            'contract_duration_months' => (int) ($this->request->getPost('contract_duration_months') ?: 0) ?: null,
            'expected_release_date'  => $this->request->getPost('expected_release_date') ?: null,
            'notice_period_days'     => (int) ($this->request->getPost('notice_period_days') ?: 0) ?: null,
            'contract_status'          => $this->request->getPost('contract_status') ?: 'draft',
            'renewal_status'         => esc($this->request->getPost('renewal_status') ?: ''),
            'payroll_responsibility' => $this->request->getPost('payroll_responsibility') ?: 'our_company',
            'supplier_id'            => (int) ($this->request->getPost('supplier_id') ?: 0) ?: null,
            'supplier_employee_ref'  => esc(trim((string) $this->request->getPost('supplier_employee_ref'))),
            'supplier_contract_ref'  => esc(trim((string) $this->request->getPost('supplier_contract_ref'))),
            'billing_type'           => $this->request->getPost('billing_type') ?: null,
            'remarks'                => esc(trim((string) $this->request->getPost('remarks'))),
            'is_current'             => $this->request->getPost('is_current') ? 1 : 1,
        ];

        if ($this->hrCan('employee.contract.edit_rate')) {
            $data['cost_rate'] = (float) ($this->request->getPost('cost_rate') ?: 0) ?: null;
            $data['billing_rate'] = (float) ($this->request->getPost('billing_rate') ?: 0) ?: null;
            $data['client_billing_rate'] = (float) ($this->request->getPost('client_billing_rate') ?: 0) ?: null;
        } elseif ($existing) {
            $data['cost_rate'] = $existing['cost_rate'];
            $data['billing_rate'] = $existing['billing_rate'];
            $data['client_billing_rate'] = $existing['client_billing_rate'];
        }

        return $data;
    }
}
