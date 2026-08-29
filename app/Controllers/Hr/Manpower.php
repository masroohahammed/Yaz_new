<?php

namespace App\Controllers\Hr;

use App\Controllers\BaseController;
use App\Controllers\Traits\HrRbacTrait;
use App\Services\Hr\HrMasterDataService;
use App\Services\Hr\ManpowerPlanningService;

class Manpower extends BaseController
{
    use HrRbacTrait;

    protected ?string $workspaceRequired = 'fm';

    private ManpowerPlanningService $manpower;

    public function __construct()
    {
        $this->manpower = new ManpowerPlanningService();
    }

    public function index()
    {
        $this->requireHrPermission('manpower.view');

        if (! $this->manpower->tablesReady()) {
            return view('hr/manpower/index', $this->viewData([
                'title'             => 'Manpower Planning',
                'migrationRequired' => true,
            ]));
        }

        $filters = [
            'company_id'  => (int) ($this->request->getGet('company_id') ?: $this->currentUser()['company_id'] ?? 0) ?: null,
            'facility_id' => (int) ($this->request->getGet('facility_id') ?: 0) ?: null,
        ];

        $masters    = (new HrMasterDataService())->formOptions($filters['company_id']);
        $facilitiesQ = $this->db->table('facilities')->where('status', 'active')->orderBy('name');
        if ($filters['company_id'] && $this->db->fieldExists('company_id', 'facilities')) {
            $facilitiesQ->where('company_id', $filters['company_id']);
        }
        $this->scopeFacilities($facilitiesQ, 'facilities.id');
        $facilities = $facilitiesQ->get()->getResultArray();

        return view('hr/manpower/index', $this->viewData([
            'title'       => 'Manpower Planning',
            'rows'        => $this->manpower->dashboard($filters),
            'filters'     => $filters,
            'masters'     => $masters,
            'facilities'  => $facilities,
            'canManage'   => $this->hrCan('manpower.manage'),
        ]));
    }

    public function storeRequirement()
    {
        $this->requireHrPermission('manpower.manage');
        if (! $this->request->is('post') || ! $this->manpower->tablesReady()) {
            return redirect()->back()->with('error', 'Invalid request.');
        }

        $facilityId = (int) $this->request->getPost('facility_id');
        if ($facilityId) {
            $this->assertFacilityAccess($facilityId);
        }

        $data = [
            'company_id'         => (int) ($this->request->getPost('company_id') ?: $this->currentUser()['company_id'] ?? 0) ?: null,
            'facility_id'        => $facilityId,
            'department_id'      => (int) ($this->request->getPost('department_id') ?: 0) ?: null,
            'designation_id'     => (int) ($this->request->getPost('designation_id') ?: 0) ?: null,
            'required_headcount' => max(1, (int) ($this->request->getPost('required_headcount') ?: 1)),
            'start_date'         => $this->request->getPost('start_date') ?: null,
            'end_date'           => $this->request->getPost('end_date') ?: null,
            'status'             => $this->request->getPost('status') ?: 'active',
            'remarks'            => esc(trim((string) $this->request->getPost('remarks'))),
        ];

        try {
            $this->manpower->createRequirement($data, (int) session()->get('user_id'));
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', 'Could not save requirement.');
        }

        return redirect()->to(base_url('hr/manpower'))->with('success', 'Manpower requirement added.');
    }
}
