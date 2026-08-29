<?php

namespace App\Controllers\Hr;

use App\Controllers\BaseController;
use App\Controllers\Traits\HrRbacTrait;
use App\Services\Hr\EmploymentContractService;
use App\Services\Hr\HrMasterDataService;

class ContractExpiry extends BaseController
{
    use HrRbacTrait;

    protected ?string $workspaceRequired = 'fm';

    private EmploymentContractService $contracts;

    public function __construct()
    {
        $this->contracts = new EmploymentContractService();
    }

    public function index()
    {
        $this->requireHrPermission('hr.contract_expiry');

        if (! $this->contracts->tablesReady()) {
            return view('hr/contracts/expiry', $this->viewData([
                'title'             => 'Contract Expiry',
                'migrationRequired' => true,
            ]));
        }

        $filters = [
            'company_id'  => (int) ($this->request->getGet('company_id') ?: $this->currentUser()['company_id'] ?? 0) ?: null,
            'facility_id' => (int) ($this->request->getGet('facility_id') ?: 0) ?: null,
            'supplier_id' => (int) ($this->request->getGet('supplier_id') ?: 0) ?: null,
            'search'      => trim((string) $this->request->getGet('q')),
        ];
        $bucket = (string) ($this->request->getGet('bucket') ?: 'expired');
        if (! isset(EmploymentContractService::EXPIRY_BUCKETS[$bucket])) {
            $bucket = 'expired';
        }

        $masters = (new HrMasterDataService())->formOptions($filters['company_id']);
        $suppliers = $this->db->table('vendors')->where('status', 'active')->orderBy('name')->get()->getResultArray();

        return view('hr/contracts/expiry', $this->viewData([
            'title'        => 'Contract Expiry',
            'buckets'      => EmploymentContractService::EXPIRY_BUCKETS,
            'bucketData'   => $this->contracts->expiryDashboard($filters),
            'activeBucket' => $bucket,
            'activeRows'   => $this->contracts->bucketRows($bucket, $filters),
            'filters'      => $filters,
            'masters'      => $masters,
            'suppliers'    => $suppliers,
            'canViewRates' => $this->hrCan('employee.contract.view_rate'),
        ]));
    }
}
