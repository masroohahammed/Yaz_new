<?php

namespace App\Controllers;

use App\Services\MaintenanceScopeQuery;

/**
 * Public entity-scoped inspection pages (QR scan CTAs).
 * Maintenance form lives in PublicMaintenance (standalone, no BaseController).
 */
class PublicEntity extends BaseController
{
    public function inspections()
    {
        if (! session()->get('user_id')) {
            $return = current_url() . '?' . ($_SERVER['QUERY_STRING'] ?? '');

            return redirect()->to(base_url('login'))->with('info', 'Please log in to access inspections.')->with('redirect_after_login', $return);
        }

        $scope = $this->resolveScope();
        if ($scope === null) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Property, unit, or asset not found or QR code does not match a registered entity.');
        }

        $entityLabel = $this->entityLabel($scope);
        $reports     = $this->loadInspectionReports($scope);
        $units       = [];

        if (($scope['type'] ?? '') === 'property' && ! empty($scope['facility_id'])) {
            $units = MaintenanceScopeQuery::unitsForFacility($this->db, (int) $scope['facility_id']);
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
            $unit = MaintenanceScopeQuery::resolveUnit($this->db, $unitId);
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
            $asset = MaintenanceScopeQuery::resolveAsset($this->db, $assetId);
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
            $facility = MaintenanceScopeQuery::resolveFacility($this->db, $facilityId);
            if (! $facility) {
                return null;
            }

            return [
                'type'        => 'property',
                'facility_id' => $facilityId,
                'label'       => $facility['name'] ?? ('Property #' . $facilityId),
                'subtitle'    => trim(($facility['code'] ?? '') . ' · ' . ($facility['city'] ?? ''), ' ·'),
                'scan_url'    => ! empty($facility['qr_token'])
                    ? base_url('scan/property/' . $facility['qr_token'])
                    : base_url('scan/property/id/' . $facilityId),
            ];
        }

        return null;
    }

    /** @param array<string, mixed> $scope */
    private function entityLabel(array $scope): string
    {
        return (string) ($scope['label'] ?? 'Entity');
    }

    /** @param array<string, mixed> $scope
     * @return list<array<string, mixed>>
     */
    private function loadInspectionReports(array $scope): array
    {
        $unitId = (int) ($scope['unit_id'] ?? 0);
        $facilityId = (int) ($scope['facility_id'] ?? 0);
        $assetId = (int) ($scope['asset_id'] ?? 0);

        $sql = 'SELECT uc.*, u.unit_number, u.id AS unit_id, fa.name AS asset_name, usr.name AS created_by_name
            FROM unit_checklists uc
            LEFT JOIN units u ON u.id = uc.unit_id
            LEFT JOIN assets fa ON fa.id = uc.asset_id
            LEFT JOIN users usr ON usr.id = uc.created_by
            WHERE 1=1';
        $params = [];

        if (($scope['type'] ?? '') === 'asset' && $assetId > 0) {
            $sql .= ' AND uc.asset_id = ?';
            $params[] = $assetId;
        } elseif (($scope['type'] ?? '') === 'unit' && $unitId > 0) {
            $sql .= ' AND uc.unit_id = ?';
            $params[] = $unitId;
        } elseif ($facilityId > 0) {
            $sql .= ' AND COALESCE(uc.facility_id, u.facility_id) = ?';
            $params[] = $facilityId;
        }

        $sql .= ' ORDER BY uc.created_at DESC LIMIT 50';

        try {
            return $this->db->query($sql, $params)->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'PublicEntity loadInspectionReports: ' . $e->getMessage());

            return [];
        }
    }
}
