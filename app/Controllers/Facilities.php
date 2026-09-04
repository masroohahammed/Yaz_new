<?php

namespace App\Controllers;

use App\Models\FacilityModel;
use App\Models\CompanyModel;
use App\Models\UserModel;
use App\Services\EntityQrService;
use App\Services\PropertyAssignmentService;

/**
 * Facilities — manages facility CRUD.
 * Now includes company selection on create/edit.
 */
class Facilities extends BaseController
{
    private FacilityModel $model;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->model = new FacilityModel();
    }

    // ----------------------------------------------------------
    // Index
    // ----------------------------------------------------------

    public function index()
    {
        $filters = $this->request->getGet();

        $q = $this->db->table('facilities f')
            ->select('f.*, c.name AS company_name, u.name AS manager_name')
            ->join('companies c', 'c.id = f.company_id', 'left')
            ->join('users u', 'u.id = f.manager_id', 'left')
            ->where('f.deleted_at', null);

        if ($this->db->fieldExists('company_id', 'facilities')) {
            $this->scopeCompany($q, 'f.company_id');
        }
        $this->scopeFacilities($q, 'f.id');

        if (! empty($filters['status'])) {
            $q->where('f.status', $filters['status']);
        }
        if (! empty($filters['company_id'])) {
            $q->where('f.company_id', $filters['company_id']);
        }
        if (! empty($filters['search'])) {
            $q->groupStart()
                ->like('f.name', $filters['search'])
                ->orLike('f.code', $filters['search'])
                ->groupEnd();
        }

        $facilities = $q->orderBy('f.name', 'ASC')->get()->getResultArray();

        $companyModel = new CompanyModel();
        $companies    = $companyModel->where('status', 'active')->findAll();

        return view('facilities/index', $this->viewData([
            'title'      => 'Properties',
            'pageTitle'  => 'Properties',
            'facilities' => $facilities,
            'companies'  => $companies,
            'filters'    => $filters,
        ]));
    }

    // ----------------------------------------------------------
    // Create
    // ----------------------------------------------------------

    public function create()
    {
        $this->requirePermission('facilities.create');

        $companyModel = new CompanyModel();
        $userModel    = new UserModel();

        $companies = $companyModel->where('status', 'active')->findAll();
        $managers  = $userModel->getUsersByRole('facility_manager');
        $propertyManagers = $userModel->getUsersByRoles(['property_manager', 'manager', 'real_estate_manager']);
        $landlords = $this->db->tableExists('landlords')
            ? $this->db->table('landlords')->where('status', 'active')->where('deleted_at', null)->orderBy('full_name')->get()->getResultArray()
            : [];

        return view('facilities/create', [
            'pageTitle' => 'Add Facility',
            'companies' => $companies,
            'managers'  => $managers,
            'propertyManagers' => $propertyManagers,
            'assignedManagerIds' => [],
            'landlords' => $landlords,
            'facility'  => [],
        ]);
    }

    public function store()
    {
        $this->requirePermission('facilities.create');

        $rules = [
            'name'       => 'required|min_length[2]|max_length[200]',
            'code'       => 'required|max_length[20]|is_unique[facilities.code]',
            'city'       => 'required|max_length[100]',
            'country'    => 'required|max_length[100]',
            'company_id' => 'required|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $post = $this->request->getPost();

        $data = [
            'name'                    => $post['name'],
            'code'                    => strtoupper($post['code']),
            'address'                 => $post['address'] ?? null,
            'city'                    => $post['city'],
            'country'                 => $post['country'],
            'company_id'              => $post['company_id'],
            'manager_id'              => $post['manager_id'] ?: null,
            'area_sqm'                => $post['area_sqm'] ?: null,
            'floors'                  => $post['floors'] ?: 1,
            'status'                  => $post['status'] ?? 'active',
            'category'                => $post['category'] ?: null,
            'property_type'           => $post['property_type'] ?: null,
            'listing_status'          => $post['listing_status'] ?: null,
            'for_sale'                => !empty($post['for_sale']) ? 1 : 0,
            'sale_price'              => $post['sale_price'] ?: null,
            'landlord_id'             => $post['landlord_id'] ?: null,
            'expected_monthly_income' => $post['expected_monthly_income'] ?: null,
            'landlord_share_pct'      => $post['landlord_share_pct'] ?: null,
            'management_fee_pct'      => $post['management_fee_pct'] ?: null,
            'finance_notes'           => $post['finance_notes'] ?: null,
        ];

        $id = $this->model->insert($data);
        (new EntityQrService($this->db))->ensureToken('property', (int) $id);
        $this->syncPropertyManagers((int) $id, $post);
        $this->logActivity('create', 'facilities', $id, 'Facility created: ' . $data['name']);

        return redirect()->to('/facilities/' . $id)->with('success', 'Facility created successfully.');
    }

    // ----------------------------------------------------------
    // Show
    // ----------------------------------------------------------

    public function show(int $id)
    {
        return $this->viewFacility($id);
    }

    /** Rich facility dashboard with units, contracts, assets, and work orders. */
    public function viewFacility(int $id)
    {
        $this->assertFacilityAccess($id);

        $facility = $this->db->table('facilities f')
            ->select('f.*, c.name AS company_name, u.name AS manager_name')
            ->join('companies c', 'c.id = f.company_id', 'left')
            ->join('users u',     'u.id = f.manager_id', 'left')
            ->where('f.id', $id)
            ->where('f.deleted_at', null)
            ->get()->getRowArray();

        if (! $facility) {
            return redirect()->to(base_url('facilities'))->with('error', 'Facility not found.');
        }

        $qrSvc = new EntityQrService($this->db);
        $qrSvc->ensureToken('property', $id);
        $facility = $this->db->table('facilities f')
            ->select('f.*, c.name AS company_name, u.name AS manager_name')
            ->join('companies c', 'c.id = f.company_id', 'left')
            ->join('users u',     'u.id = f.manager_id', 'left')
            ->where('f.id', $id)
            ->where('f.deleted_at', null)
            ->get()->getRowArray() ?? $facility;

        $scanUrl    = $qrSvc->scanUrl('property', $facility);
        $qrImageUrl = $qrSvc->qrImageUrl($scanUrl, 200);

        $facilityUnits = $this->db->table('units')
            ->where('facility_id', $id)
            ->where('deleted_at', null)
            ->orderBy('unit_number', 'ASC')
            ->get()->getResultArray();

        $units = $facilityUnits;
        $occupied = count(array_filter($facilityUnits, static fn ($u) => ($u['status'] ?? '') === 'occupied'));

        $kpi = [
            'total_units'    => count($facilityUnits),
            'occupied_units' => $occupied,
        ];

        $assets = $this->db->table('assets')
            ->where('facility_id', $id)
            ->where('deleted_at', null)
            ->get()->getResultArray();

        $contracts = $this->db->table('contracts')
            ->where('facility_id', $id)
            ->orderBy('end_date', 'DESC')
            ->get()->getResultArray();

        $openWO = $this->db->table('work_orders w')
            ->select('w.*, u.name AS assigned_name, un.unit_number')
            ->join('users u', 'u.id = w.assigned_to', 'left')
            ->join('units un', 'un.id = w.unit_id', 'left')
            ->where('w.facility_id', $id)
            ->whereIn('w.status', ['new', 'assigned', 'in_progress', 'on_hold'])
            ->where('w.deleted_at', null)
            ->orderBy('w.created_at', 'DESC')
            ->limit(50)
            ->get()->getResultArray();

        $leaseContracts = [];
        if ($this->db->tableExists('lease_contracts')) {
            $leaseContracts = $this->db->table('lease_contracts lc')
                ->select('lc.*, t.full_name AS tenant_name, u.unit_number')
                ->join('tenants t', 't.id = lc.tenant_id', 'left')
                ->join('units u', 'u.id = lc.unit_id', 'left')
                ->where('lc.facility_id', $id)
                ->where('lc.deleted_at', null)
                ->orderBy('lc.end_date', 'DESC')
                ->get()->getResultArray();
        }

        $maintenanceHistory = $this->db->table('maintenance_requests mr')
            ->select('mr.id, mr.ticket_number, mr.category, mr.priority, mr.status, mr.created_at, u.unit_number')
            ->join('units u', 'u.id = mr.unit_id', 'left')
            ->where('mr.facility_id', $id)
            ->orderBy('mr.created_at', 'DESC')
            ->limit(20)
            ->get()->getResultArray();

        $workspace = $this->currentWorkspace();
        $hasParkingUnits = (bool) array_filter(
            $facilityUnits,
            static fn ($u) => strtolower((string) ($u['unit_type'] ?? '')) === 'parking'
        );

        $propertyDocuments = [];
        if ($this->db->tableExists('documents') && $this->db->fieldExists('module', 'documents')) {
            $propertyDocuments = $this->db->table('documents')
                ->where('module', 'facility')
                ->where('ref_id', $id)
                ->orderBy('created_at', 'DESC')
                ->get()->getResultArray();
        }

        $inspectionReports = [];
        if ($this->db->tableExists('unit_checklists')) {
            $inspectionReports = $this->db->table('unit_checklists uc')
                ->select('uc.*, u.unit_number, u.id AS unit_id, usr.name AS created_by_name')
                ->join('units u', 'u.id = uc.unit_id', 'inner')
                ->join('users usr', 'usr.id = uc.created_by', 'left')
                ->where('u.facility_id', $id)
                ->where('u.deleted_at', null)
                ->orderBy('uc.created_at', 'DESC')
                ->get()->getResultArray();
        }

        return view('facilities/view', $this->viewData([
            'title'         => $facility['name'],
            'facility'      => $facility,
            'facilityUnits' => $facilityUnits,
            'units'         => $units,
            'kpi'           => $kpi,
            'assets'        => $assets,
            'contracts'     => $contracts,
            'leaseContracts'=> $leaseContracts,
            'maintenanceHistory' => $maintenanceHistory,
            'workspace'     => $workspace,
            'propertyDocuments' => $propertyDocuments,
            'openWO'        => $openWO,
            'hasParkingUnits' => $hasParkingUnits,
            'inspectionReports' => $inspectionReports,
            'scanUrl'           => $scanUrl,
            'qrImageUrl'        => $qrImageUrl,
        ]));
    }

    public function qrcode(int $id)
    {
        $facility = $this->db->table('facilities')->where('id', $id)->where('deleted_at', null)->get()->getRowArray();
        if (! $facility) {
            return redirect()->to(base_url('properties'))->with('error', 'Property not found.');
        }

        $qrSvc = new EntityQrService($this->db);
        $qrSvc->ensureToken('property', $id);
        $facility = $this->db->table('facilities')->where('id', $id)->get()->getRowArray() ?? $facility;
        $scanUrl  = $qrSvc->scanUrl('property', $facility);

        return view('facilities/qrcode', $this->viewData([
            'title'      => 'QR Code — ' . $facility['name'],
            'facility'   => $facility,
            'scanUrl'    => $scanUrl,
            'qrImageUrl' => $qrSvc->qrImageUrl($scanUrl, 280),
        ]));
    }

    // ----------------------------------------------------------
    // Edit / Update
    // ----------------------------------------------------------

    public function edit(int $id)
    {
        $this->requirePermission('facilities.edit');
        $this->assertFacilityAccess($id);

        $facility     = $this->model->find($id);
        if (! $facility) return redirect()->to('/facilities')->with('error', 'Facility not found.');

        $companyModel = new CompanyModel();
        $userModel    = new UserModel();
        $landlords    = $this->db->tableExists('landlords')
            ? $this->db->table('landlords')->where('status', 'active')->where('deleted_at', null)->orderBy('full_name')->get()->getResultArray()
            : [];

        $propertyManagers = $userModel->getUsersByRoles(['property_manager', 'manager', 'real_estate_manager']);
        $assignedManagerIds = $this->assignedManagerIds($id);

        return view('facilities/create', [
            'pageTitle' => 'Edit Facility — ' . $facility['name'],
            'facility'  => $facility,
            'companies' => $companyModel->where('status', 'active')->findAll(),
            'managers'  => $userModel->getUsersByRole('facility_manager'),
            'propertyManagers' => $propertyManagers,
            'assignedManagerIds' => $assignedManagerIds,
            'landlords' => $landlords,
        ]);
    }

    public function update(int $id)
    {
        $this->requirePermission('facilities.edit');
        $this->assertFacilityAccess($id);

        $facility = $this->model->find($id);
        if (! $facility) return redirect()->to('/facilities')->with('error', 'Facility not found.');

        $rules = [
            'name'       => 'required|min_length[2]|max_length[200]',
            'code'       => "required|max_length[20]|is_unique[facilities.code,id,{$id}]",
            'city'       => 'required|max_length[100]',
            'country'    => 'required|max_length[100]',
            'company_id' => 'required|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $post = $this->request->getPost();

        $this->model->update($id, [
            'name'                    => $post['name'],
            'code'                    => strtoupper($post['code']),
            'address'                 => $post['address'] ?? null,
            'city'                    => $post['city'],
            'country'                 => $post['country'],
            'company_id'              => $post['company_id'],
            'manager_id'              => $post['manager_id'] ?: null,
            'area_sqm'                => $post['area_sqm'] ?: null,
            'floors'                  => $post['floors'] ?: 1,
            'status'                  => $post['status'] ?? 'active',
            'category'                => $post['category'] ?: null,
            'property_type'           => $post['property_type'] ?: null,
            'listing_status'          => $post['listing_status'] ?: null,
            'for_sale'                => !empty($post['for_sale']) ? 1 : 0,
            'sale_price'              => $post['sale_price'] ?: null,
            'landlord_id'             => $post['landlord_id'] ?: null,
            'expected_monthly_income' => $post['expected_monthly_income'] ?: null,
            'landlord_share_pct'      => $post['landlord_share_pct'] ?: null,
            'management_fee_pct'      => $post['management_fee_pct'] ?: null,
            'finance_notes'           => $post['finance_notes'] ?: null,
        ]);

        $this->syncPropertyManagers($id, $post);
        $this->logActivity('update', 'facilities', $id, 'Facility updated');
        return redirect()->to('/facilities/' . $id)->with('success', 'Facility updated.');
    }

    /** @return list<int> */
    private function assignedManagerIds(int $facilityId): array
    {
        if (! $this->db->tableExists('user_property_assignments')) {
            $facility = $this->model->find($facilityId);

            return ! empty($facility['manager_id']) ? [(int) $facility['manager_id']] : [];
        }

        return array_map(
            static fn ($r) => (int) $r['user_id'],
            $this->db->table('user_property_assignments')
                ->select('user_id')
                ->where('facility_id', $facilityId)
                ->where('role_type', 'manager')
                ->get()
                ->getResultArray()
        );
    }

    /** @param array<string, mixed> $post */
    private function syncPropertyManagers(int $facilityId, array $post): void
    {
        $managerIds = array_values(array_filter(array_map('intval', (array) ($post['manager_ids'] ?? []))));
        if ($managerIds === [] && ! empty($post['manager_id'])) {
            $managerIds = [(int) $post['manager_id']];
        }

        (new PropertyAssignmentService($this->db))->syncAssignments(
            $facilityId,
            $managerIds,
            [],
            (int) session()->get('user_id')
        );
    }

    // ----------------------------------------------------------
    // Delete (soft)
    // ----------------------------------------------------------

    public function delete(int $id)
    {
        $this->requireRole(['super_admin']);

        $this->model->delete($id);
        $this->logActivity('delete', 'facilities', $id, 'Facility soft-deleted');

        return redirect()->to('/facilities')->with('success', 'Facility removed.');
    }
}
