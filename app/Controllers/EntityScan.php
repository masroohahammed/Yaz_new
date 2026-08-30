<?php

namespace App\Controllers;

use App\Services\EntityQrService;
use App\Services\InspectionAreaService;
use App\Services\MaintenanceScopeQuery;
use App\Services\QrScanLogService;

/**
 * Public QR deep links for properties and units.
 */
class EntityScan extends BaseController
{
    public function propertyByToken(string $token)
    {
        $qr = new EntityQrService($this->db);
        $facility = $qr->findPropertyByToken($token);
        if (! $facility) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Property not found.');
        }

        return $this->renderPropertyScan($facility, 'qr');
    }

    public function propertyById(int $id)
    {
        $qr = new EntityQrService($this->db);
        $facility = $qr->findPropertyById($id);
        if (! $facility) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $qr->ensureToken('property', $id);
        $facility = $qr->findPropertyById($id) ?? $facility;

        return $this->renderPropertyScan($facility, 'direct');
    }

    public function unitByToken(string $token)
    {
        $qr = new EntityQrService($this->db);
        $unit = $qr->findUnitByToken($token);
        if (! $unit) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Unit not found.');
        }

        return $this->renderUnitScan($unit, 'qr');
    }

    public function unitById(int $id)
    {
        $qr = new EntityQrService($this->db);
        $unit = $qr->findUnitById($id);
        if (! $unit) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $qr->ensureToken('unit', $id);
        $unit = $qr->findUnitById($id) ?? $unit;

        return $this->renderUnitScan($unit, 'direct');
    }

    public function propertyComplaint(string $token)
    {
        $qr = new EntityQrService($this->db);
        $facility = $qr->findPropertyByToken($token);
        if (! $facility) {
            return redirect()->back()->with('error', 'Property not found.');
        }

        return $this->submitMaintenanceRequest([
            'facility_id' => (int) $facility['id'],
            'unit_id'     => (int) ($this->request->getPost('unit_id') ?: 0) ?: null,
        ], 'property', (int) $facility['id'], base_url('scan/property/' . $token));
    }

    public function unitComplaint(string $token)
    {
        $qr = new EntityQrService($this->db);
        $unit = $qr->findUnitByToken($token);
        if (! $unit) {
            return redirect()->back()->with('error', 'Unit not found.');
        }

        return $this->submitMaintenanceRequest([
            'facility_id' => (int) ($unit['facility_id'] ?? 0),
            'unit_id'     => (int) $unit['id'],
        ], 'unit', (int) $unit['id'], base_url('scan/unit/' . $token));
    }

    /**
     * @param array<string, mixed> $facility
     */
    private function renderPropertyScan(array $facility, string $source): string
    {
        $facilityId = (int) $facility['id'];
        $userId     = session()->get('user_id') ? (int) session()->get('user_id') : null;
        $isLoggedIn = (bool) $userId;

        (new QrScanLogService($this->db))->log(
            'property',
            $facilityId,
            $userId,
            $source,
            $isLoggedIn ? 'authenticated_view' : 'public_view',
            $this->request->getIPAddress(),
            $this->request->getUserAgent()?->getAgentString()
        );

        $qr      = new EntityQrService($this->db);
        $scanUrl = $qr->scanUrl('property', $facility);

        $units = $this->db->table('units')
            ->select('id, unit_number')
            ->where('facility_id', $facilityId)
            ->where('deleted_at', null)
            ->orderBy('unit_number')
            ->get()->getResultArray();

        $openMaintenance = $this->loadOpenMaintenance($facilityId, null, null);
        $maintenanceHistory = $this->loadMaintenanceHistory($facilityId, null, null);
        $inspectionCount = $this->countInspectionsForFacility($facilityId);

        $scopeQs = 'facility_id=' . $facilityId;

        return view('entity_scan/landing', [
            'entityType'         => 'property',
            'entity'             => $facility,
            'title'              => $facility['name'],
            'subtitle'           => ($facility['code'] ?? '') . ' · ' . ($facility['city'] ?? ''),
            'scanUrl'            => $scanUrl,
            'qrImageUrl'         => $qr->qrImageUrl($scanUrl, 180),
            'isLoggedIn'         => $isLoggedIn,
            'units'              => $units,
            'openMaintenance'    => $openMaintenance,
            'maintenanceHistory' => $maintenanceHistory,
            'inspectionCount'    => $inspectionCount,
            'inspectionsUrl'     => InspectionAreaService::createUrl(['facility_id' => $facilityId]),
            'maintenanceUrl'     => base_url('maintenance?' . $scopeQs),
            'workOrdersUrl'      => base_url('workorders?facility_id=' . $facilityId),
            'workOrderCreateUrl' => base_url('workorders/create?facility_id=' . $facilityId),
            'detailsUrl'         => base_url('properties/view/' . $facilityId),
            'settings'           => $this->settings,
            'currency'           => $this->settings['currency'] ?? 'QAR',
        ]);
    }

    /**
     * @param array<string, mixed> $unit
     */
    private function renderUnitScan(array $unit, string $source): string
    {
        $unitId     = (int) $unit['id'];
        $userId     = session()->get('user_id') ? (int) session()->get('user_id') : null;
        $isLoggedIn = (bool) $userId;

        (new QrScanLogService($this->db))->log(
            'unit',
            $unitId,
            $userId,
            $source,
            $isLoggedIn ? 'authenticated_view' : 'public_view',
            $this->request->getIPAddress(),
            $this->request->getUserAgent()?->getAgentString()
        );

        $qr      = new EntityQrService($this->db);
        $scanUrl = $qr->scanUrl('unit', $unit);

        $openMaintenance = $this->loadOpenMaintenance((int) ($unit['facility_id'] ?? 0), $unitId, null);
        $maintenanceHistory = $this->loadMaintenanceHistory((int) ($unit['facility_id'] ?? 0), $unitId, null);
        $inspectionCount = $this->countInspectionsForUnit($unitId);

        $scopeQs = 'unit_id=' . $unitId;

        return view('entity_scan/landing', [
            'entityType'         => 'unit',
            'entity'             => $unit,
            'title'              => 'Unit ' . ($unit['unit_number'] ?? $unitId),
            'subtitle'           => ($unit['facility_name'] ?? '') . ' · ' . ucfirst((string) ($unit['status'] ?? '')),
            'scanUrl'            => $scanUrl,
            'qrImageUrl'         => $qr->qrImageUrl($scanUrl, 180),
            'isLoggedIn'         => $isLoggedIn,
            'units'              => [],
            'openMaintenance'    => $openMaintenance,
            'maintenanceHistory' => $maintenanceHistory,
            'inspectionCount'    => $inspectionCount,
            'inspectionsUrl'     => InspectionAreaService::createUrl(['facility_id' => (int) ($unit['facility_id'] ?? 0), 'unit_id' => $unitId]),
            'maintenanceUrl'     => base_url('maintenance?' . $scopeQs),
            'workOrdersUrl'      => base_url('workorders?facility_id=' . (int) ($unit['facility_id'] ?? 0) . '&unit_id=' . $unitId),
            'workOrderCreateUrl' => base_url('workorders/create?facility_id=' . (int) ($unit['facility_id'] ?? 0) . '&unit_id=' . $unitId),
            'detailsUrl'         => base_url('units/view/' . $unitId),
            'settings'           => $this->settings,
            'currency'           => $this->settings['currency'] ?? 'QAR',
        ]);
    }

    /**
     * @param array{facility_id?: int, unit_id?: int|null, asset_id?: int|null} $scope
     */
    private function submitMaintenanceRequest(array $scope, string $entityType, int $entityId, string $redirectUrl)
    {
        $rules = [
            'requester_name' => 'required|min_length[2]',
            'description'    => 'required|min_length[10]',
            'priority'       => 'required|in_list[critical,high,medium,low]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        if (! $this->db->tableExists('maintenance_requests')) {
            return redirect()->back()->with('error', 'Maintenance module not available.');
        }

        $ticket = 'TKT-' . date('Y') . '-' . strtoupper(substr(uniqid(), -6));
        $data = [
            'ticket_number'   => $ticket,
            'facility_id'     => $scope['facility_id'] ?? null,
            'unit_id'         => $scope['unit_id'] ?? null,
            'requester_name'  => esc($this->request->getPost('requester_name')),
            'requester_email' => esc($this->request->getPost('requester_email') ?? ''),
            'requester_phone' => esc($this->request->getPost('requester_phone') ?? ''),
            'category'        => esc($this->request->getPost('category') ?: 'general'),
            'description'     => esc($this->request->getPost('description')),
            'priority'        => $this->request->getPost('priority'),
            'status'          => 'pending',
            'approval_status' => 'pending',
        ];

        if ($this->db->fieldExists('asset_id', 'maintenance_requests') && ! empty($scope['asset_id'])) {
            $data['asset_id'] = (int) $scope['asset_id'];
        }
        if ($this->db->fieldExists('scan_source', 'maintenance_requests')) {
            $data['scan_source'] = 'qr_scan';
        }

        $this->db->table('maintenance_requests')->insert($data);

        (new QrScanLogService($this->db))->log(
            $entityType,
            $entityId,
            session()->get('user_id') ? (int) session()->get('user_id') : null,
            'qr',
            'complaint_submitted',
            $this->request->getIPAddress(),
            $this->request->getUserAgent()?->getAgentString()
        );

        return redirect()->to($redirectUrl)->with('success', 'Maintenance request submitted. Ticket: ' . $ticket);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function loadOpenMaintenance(?int $facilityId, ?int $unitId, ?int $assetId): array
    {
        $scope = array_filter([
            'facility_id' => $facilityId ?: null,
            'unit_id'     => $unitId ?: null,
            'asset_id'    => $assetId ?: null,
        ]);

        return MaintenanceScopeQuery::listRecords(
            $this->db,
            $scope,
            'mr.id, mr.ticket_number, mr.category, mr.priority, mr.status, mr.created_at',
            10,
            ['pending', 'verified', 'in_progress', 'open', 'assigned']
        );
    }

    private function loadMaintenanceHistory(?int $facilityId, ?int $unitId, ?int $assetId): array
    {
        $scope = array_filter([
            'facility_id' => $facilityId ?: null,
            'unit_id'     => $unitId ?: null,
            'asset_id'    => $assetId ?: null,
        ]);

        return MaintenanceScopeQuery::listRecords(
            $this->db,
            $scope,
            'mr.id, mr.ticket_number, mr.category, mr.status, mr.created_at',
            8
        );
    }

    private function countInspectionsForFacility(int $facilityId): int
    {
        if (! $this->db->tableExists('unit_checklists')) {
            return 0;
        }

        return (int) $this->db->table('unit_checklists uc')
            ->join('units u', 'u.id = uc.unit_id', 'left')
            ->where('COALESCE(uc.facility_id, u.facility_id)', $facilityId, false)
            ->countAllResults();
    }

    private function countInspectionsForUnit(int $unitId): int
    {
        if (! $this->db->tableExists('unit_checklists')) {
            return 0;
        }

        return (int) $this->db->table('unit_checklists')->where('unit_id', $unitId)->countAllResults();
    }
}
