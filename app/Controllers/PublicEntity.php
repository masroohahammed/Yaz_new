<?php

namespace App\Controllers;

use App\Controllers\Traits\QrInspectionRedirectTrait;
use App\Services\InspectionAreaService;
use App\Services\MaintenanceScopeQuery;

/**
 * Public entity-scoped inspection pages (QR scan CTAs).
 * Maintenance form lives in PublicMaintenance (standalone, no BaseController).
 */
class PublicEntity extends BaseController
{
    use QrInspectionRedirectTrait;

    public function inspections()
    {
        $scope = $this->resolveScope();
        if ($scope === null) {
            return redirect()->to(base_url('dashboard'))->with('error', 'Property, unit, or asset not found or QR code does not match a registered entity.');
        }

        $createUrl = $this->inspectionCreateUrlForScope($scope);
        $extra = [];
        $inspectionType = $this->inspectionTypeFromScan();
        if ($inspectionType !== null) {
            $extra['inspection_type'] = $inspectionType;
        }

        return $this->redirectToInspectionUrl($this->appendQuery($createUrl, $extra));
    }

    /** @param array<string, mixed> $scope */
    private function inspectionCreateUrlForScope(array $scope): string
    {
        $facilityId = (int) ($scope['facility_id'] ?? 0);
        $unitId     = (int) ($scope['unit_id'] ?? 0);
        $assetId    = (int) ($scope['asset_id'] ?? 0);

        if (($scope['type'] ?? '') === 'asset' && $assetId > 0) {
            return InspectionAreaService::createUrl(['asset_id' => $assetId]);
        }

        if (($scope['type'] ?? '') === 'unit' && $unitId > 0) {
            return InspectionAreaService::createUrl([
                'facility_id' => $facilityId,
                'unit_id'     => $unitId,
            ]);
        }

        return InspectionAreaService::createUrl(['facility_id' => $facilityId]);
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
}
