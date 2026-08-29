<?php

namespace App\Controllers\Hr;

use App\Controllers\BaseController;
use App\Controllers\Traits\HrRbacTrait;
use App\Services\Hr\HrDocumentService;

class DocumentExpiry extends BaseController
{
    use HrRbacTrait;
    protected ?string $workspaceRequired = 'fm';

    private HrDocumentService $docs;

    public function __construct()
    {
        $this->docs = new HrDocumentService();
    }

    public function index()
    {
        $this->requireHrPermission('hr.document_expiry');
        if (! $this->docs->tablesReady()) {
            return view('hr/documents/expiry', $this->viewData([
                'title'             => 'HR Document Expiry',
                'migrationRequired' => true,
            ]));
        }

        $filters = [
            'company_id'    => (int) ($this->request->getGet('company_id') ?: $this->currentUser()['company_id'] ?? 0) ?: null,
            'facility_id'   => (int) ($this->request->getGet('facility_id') ?: 0) ?: null,
            'department_id' => (int) ($this->request->getGet('department_id') ?: 0) ?: null,
            'search'        => trim((string) $this->request->getGet('q')),
            'active_only'   => true,
        ];
        $bucket = (string) ($this->request->getGet('bucket') ?: 'expired');
        if (! isset(HrDocumentService::EXPIRY_BUCKETS[$bucket])) {
            $bucket = 'expired';
        }

        $masters = (new \App\Services\Hr\HrMasterDataService())->formOptions($filters['company_id']);

        $hrDocService = new \App\Services\Hr\HrDocumentService();

        return view('hr/documents/expiry', $this->viewData([
            'title'         => 'HR Document Expiry',
            'buckets'       => HrDocumentService::EXPIRY_BUCKETS,
            'bucketData'    => $this->docs->expiryDashboard($filters),
            'activeBucket'  => $bucket,
            'activeRows'    => $this->docs->bucketRows($bucket, $filters),
            'counts'        => $this->docs->expiryCounts($filters),
            'filters'       => $filters,
            'masters'       => $masters,
            'hrDocService'  => $hrDocService,
        ]));
    }
}
