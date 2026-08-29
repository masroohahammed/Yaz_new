<?php

namespace App\Controllers;

/**
 * Public entity-scoped maintenance and inspection pages (QR scan CTAs).
 */
class PublicEntity extends BaseController
{
    public function maintenance()
    {
        $scope = $this->resolveScope();
        if ($scope === null) {
            return redirect()->to(base_url('request'))->with('error', 'Property, unit, or asset not specified.');
        }

        $isLoggedIn = (bool) session()->get('user_id');
        $records    = $this->loadMaintenanceRecords($scope);
        $entityLabel = $this->entityLabel($scope);

        return view('public/maintenance', [
            'title'       => 'Maintenance — ' . $entityLabel,
            'scope'       => $scope,
            'entityLabel' => $entityLabel,
            'records'     => $records,
            'isLoggedIn'  => $isLoggedIn,
            'settings'    => $this->settings,
            'backUrl'     => $scope['scan_url'] ?? base_url('request'),
        ]);
    }

    public function maintenanceSubmit()
    {
        $scope = $this->resolveScope();
        if ($scope === null) {
            return redirect()->back()->with('error', 'Invalid scope.');
        }

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

        $qs = $this->scopeQueryString($scope);

        return redirect()->to(base_url('public/maintenance?' . $qs))
            ->with('success', 'Maintenance request submitted. Ticket: ' . $ticket);
    }

    public function inspections()
    {
        if (! session()->get('user_id')) {
            $return = current_url() . '?' . ($_SERVER['QUERY_STRING'] ?? '');

            return redirect()->to(base_url('login'))->with('info', 'Please log in to access inspections.')->with('redirect_after_login', $return);
        }

        $scope = $this->resolveScope();
        if ($scope === null) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Property, unit, or asset not specified.');
        }

        $entityLabel = $this->entityLabel($scope);
        $reports     = $this->loadInspectionReports($scope);
        $units       = [];

        if (($scope['type'] ?? '') === 'property' && ! empty($scope['facility_id'])) {
            $units = $this->db->table('units u')
                ->select('u.id, u.unit_number, u.floor, u.status, u.tenant_name')
                ->where('u.facility_id', (int) $scope['facility_id'])
                ->where('u.deleted_at', null)
                ->orderBy('u.unit_number')
                ->get()->getResultArray();
        }

        return view('public/inspections', [
            'title'       => 'Inspections — ' . $entityLabel,
            'scope'       => $scope,
            'entityLabel' => $entityLabel,
            'reports'     => $reports,
            'units'       => $units,
            'settings'    => $this->settings,
            'backUrl'     => $scope['scan_url'] ?? base_url('dashboard'),
        ]);
    }

    /** @return array<string, mixed>|null */
    private function resolveScope(): ?array
    {
        $facilityId = (int) ($this->request->getGet('facility_id') ?? $this->request->getPost('facility_id') ?? 0);
        $unitId     = (int) ($this->request->getGet('unit_id') ?? $this->request->getPost('unit_id') ?? 0);
        $assetId    = (int) ($this->request->getGet('asset_id') ?? $this->request->getPost('asset_id') ?? 0);

        if ($unitId > 0) {
            $unit = $this->db->table('units u')
                ->select('u.*, f.name AS facility_name')
                ->join('facilities f', 'f.id = u.facility_id', 'left')
                ->where('u.id', $unitId)
                ->where('u.deleted_at', null)
                ->get()->getRowArray();
            if (! $unit) {
                return null;
            }

            return [
                'type'        => 'unit',
                'unit_id'     => $unitId,
                'facility_id' => (int) ($unit['facility_id'] ?? 0),
                'label'       => 'Unit ' . ($unit['unit_number'] ?? $unitId),
                'subtitle'    => $unit['facility_name'] ?? '',
                'scan_url'    => ! empty($unit['qr_token']) ? base_url('scan/unit/' . $unit['qr_token']) : base_url('scan/unit/id/' . $unitId),
            ];
        }

        if ($assetId > 0) {
            $asset = $this->db->table('assets a')
                ->select('a.*, f.name AS facility_name')
                ->join('facilities f', 'f.id = a.facility_id', 'left')
                ->where('a.id', $assetId)
                ->where('a.deleted_at', null)
                ->get()->getRowArray();
            if (! $asset) {
                return null;
            }

            return [
                'type'        => 'asset',
                'asset_id'    => $assetId,
                'facility_id' => (int) ($asset['facility_id'] ?? 0),
                'label'       => $asset['name'] ?? ('Asset #' . $assetId),
                'subtitle'    => ($asset['asset_code'] ?? '') . ' · ' . ($asset['facility_name'] ?? ''),
                'scan_url'    => ! empty($asset['qr_token']) ? base_url('scan/asset/' . $asset['qr_token']) : base_url('scan/asset/id/' . $assetId),
            ];
        }

        if ($facilityId > 0) {
            $facility = $this->db->table('facilities')
                ->where('id', $facilityId)
                ->where('deleted_at', null)
                ->get()->getRowArray();
            if (! $facility) {
                return null;
            }

            return [
                'type'        => 'property',
                'facility_id' => $facilityId,
                'label'       => $facility['name'] ?? ('Property #' . $facilityId),
                'subtitle'    => ($facility['code'] ?? '') . ' · ' . ($facility['city'] ?? ''),
                'scan_url'    => ! empty($facility['qr_token']) ? base_url('scan/property/' . $facility['qr_token']) : base_url('scan/property/id/' . $facilityId),
            ];
        }

        return null;
    }

    /** @param array<string, mixed> $scope */
    private function entityLabel(array $scope): string
    {
        return (string) ($scope['label'] ?? 'Entity');
    }

    /** @param array<string, mixed> $scope */
    private function scopeQueryString(array $scope): string
    {
        $parts = [];
        if (! empty($scope['facility_id']) && ($scope['type'] ?? '') === 'property') {
            $parts['facility_id'] = (int) $scope['facility_id'];
        }
        if (! empty($scope['unit_id'])) {
            $parts['unit_id'] = (int) $scope['unit_id'];
        }
        if (! empty($scope['asset_id'])) {
            $parts['asset_id'] = (int) $scope['asset_id'];
        }
        if (($scope['type'] ?? '') === 'property' && ! empty($scope['facility_id'])) {
            $parts['facility_id'] = (int) $scope['facility_id'];
        }

        return http_build_query($parts);
    }

    /** @param array<string, mixed> $scope
     * @return list<array<string, mixed>>
     */
    private function loadMaintenanceRecords(array $scope): array
    {
        if (! $this->db->tableExists('maintenance_requests')) {
            return [];
        }

        $q = $this->db->table('maintenance_requests mr')
            ->select('mr.id, mr.ticket_number, mr.category, mr.priority, mr.status, mr.created_at, mr.requester_name')
            ->orderBy('mr.created_at', 'DESC')
            ->limit(25);

        $this->applyMaintenanceScope($q, $scope, 'mr');

        return $q->get()->getResultArray();
    }

    /** @param array<string, mixed> $scope
     * @return list<array<string, mixed>>
     */
    private function loadInspectionReports(array $scope): array
    {
        if (! $this->db->tableExists('unit_checklists')) {
            return [];
        }

        if (($scope['type'] ?? '') === 'asset') {
            return [];
        }

        $q = $this->db->table('unit_checklists uc')
            ->select('uc.*, u.unit_number, u.id AS unit_id, usr.name AS created_by_name')
            ->join('units u', 'u.id = uc.unit_id', 'left')
            ->join('users usr', 'usr.id = uc.created_by', 'left')
            ->orderBy('uc.created_at', 'DESC')
            ->limit(50);

        if (($scope['type'] ?? '') === 'unit' && ! empty($scope['unit_id'])) {
            $q->where('uc.unit_id', (int) $scope['unit_id']);
        } elseif (! empty($scope['facility_id'])) {
            $q->where('u.facility_id', (int) $scope['facility_id']);
        }

        return $q->get()->getResultArray();
    }

    /** @param array<string, mixed> $scope */
    private function applyMaintenanceScope($q, array $scope, string $prefix = ''): void
    {
        $p = $prefix !== '' ? $prefix . '.' : '';

        if (! empty($scope['unit_id'])) {
            $q->where($p . 'unit_id', (int) $scope['unit_id']);
        } elseif (! empty($scope['asset_id']) && $this->db->fieldExists('asset_id', 'maintenance_requests')) {
            $q->where($p . 'asset_id', (int) $scope['asset_id']);
        } elseif (! empty($scope['facility_id'])) {
            $q->where($p . 'facility_id', (int) $scope['facility_id']);
        }
    }
}
