<?php

namespace App\Controllers\Hr;

use App\Controllers\BaseController;
use App\Controllers\Traits\HrRbacTrait;
use App\Services\Hr\HrMasterDataService;
use App\Services\Hr\HrSchemaService;

class HrSettings extends BaseController
{
    use HrRbacTrait;
    protected ?string $workspaceRequired = 'fm';

    private HrMasterDataService $masters;
    private HrSchemaService $schema;

    public function __construct()
    {
        $this->masters = new HrMasterDataService();
        $this->schema  = new HrSchemaService();
    }

    public function index()
    {
        $this->requireHrPermission('hr.settings');
        $companyId = $this->resolveCompanyId();

        if (! $this->schema->m0Ready() || ! $this->schema->masterTablesReady()) {
        return view('hr/settings/index', $this->viewData([
            'title'             => 'HR Settings',
            'hrNavActive'       => 'settings',
            'migrationRequired' => true,
            ]));
        }

        return view('hr/settings/index', $this->viewData([
            'title'           => 'HR Settings',
            'hrNavActive'     => 'settings',
            'companyId'       => $companyId,
            'employeeTypes'   => $this->masters->employeeTypes($companyId),
            'employmentSources' => $this->masters->employmentSources($companyId),
            'employeeStatuses' => $this->masters->employeeStatuses($companyId),
            'departments'     => $this->masters->departments($companyId),
            'designations'    => $this->masters->designations($companyId),
            'grades'          => $this->masters->grades($companyId),
        ]));
    }

    public function storeLookup()
    {
        $this->requireHrPermission('hr.settings');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        $table = (string) $this->request->getPost('table');
        $allowed = [
            'hr_employee_types', 'hr_employment_sources', 'hr_employee_statuses',
            'hr_departments', 'hr_designations', 'hr_grades',
        ];
        if (! in_array($table, $allowed, true)) {
            return redirect()->back()->with('error', 'Invalid settings table.');
        }

        $companyId = $this->resolveCompanyId();
        $code      = strtolower(preg_replace('/[^a-z0-9_]+/i', '_', trim((string) $this->request->getPost('code'))));
        $name      = trim((string) $this->request->getPost('name'));
        if ($code === '' || $name === '') {
            return redirect()->back()->withInput()->with('error', 'Code and name are required.');
        }

        $row = [
            'company_id' => $companyId,
            'code'       => $code,
            'name'       => esc($name),
            'is_active'  => 1,
        ];
        if ($table === 'hr_employment_sources') {
            $row['payroll_responsibility'] = $this->request->getPost('payroll_responsibility') ?: 'our_company';
        }

        try {
            $this->masters->saveLookupRow($table, $row);
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Could not save. Run HR database patches first.');
        }

        return redirect()->to(base_url('hr/settings'))->with('success', 'Record added.');
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
