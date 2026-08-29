<?php
/**
 * Upload modal for entity pages — render outside .tab-content (Bootstrap requirement).
 */
$module        = $module ?? '';
$refId         = (int) ($refId ?? 0);
$docTypes      = $docTypes ?? fm_document_types();
$facilityId    = (int) ($facilityId ?? 0);
$unitId        = (int) ($unitId ?? 0);
$tenantId      = (int) ($tenantId ?? 0);
$contractId    = (int) ($contractId ?? 0);
$inspectionId  = (int) ($inspectionId ?? 0);
$uploadModalId = 'dmsUpload' . preg_replace('/[^a-z0-9]/', '', strtolower($module)) . $refId;

if ($module === '' || $refId <= 0) {
    return;
}

echo view('documents/_upload_modal', [
    'hub'          => false,
    'modalId'      => $uploadModalId,
    'module'       => $module,
    'refId'        => $refId,
    'docTypes'     => $docTypes,
    'linkOptions'  => [],
    'facilityId'   => $facilityId,
    'unitId'       => $unitId,
    'tenantId'     => $tenantId,
    'contractId'   => $contractId,
    'inspectionId' => $inspectionId,
]);
