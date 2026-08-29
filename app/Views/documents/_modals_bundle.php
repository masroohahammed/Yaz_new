<?php
/**
 * Document upload + type modals bundle (render at page bottom, outside tab panes).
 *
 * @var string $uploadModalId
 * @var string|null $typeModalId
 * @var bool $showLinkPicker
 * @var bool $showTypeModal
 */
$uploadModalId  = $uploadModalId ?? 'dmsUploadModal';
$typeModalId    = $typeModalId ?? null;
$showLinkPicker = ! empty($showLinkPicker);
$showTypeModal  = ! empty($showTypeModal);
$docTypes       = $docTypes ?? (function_exists('fm_document_types') ? fm_document_types() : []);
$linkOptions    = $linkOptions ?? [];
$module         = $module ?? '';
$refId          = (int) ($refId ?? 0);
$facilityId     = (int) ($facilityId ?? 0);
$unitId         = (int) ($unitId ?? 0);
$tenantId       = (int) ($tenantId ?? 0);
$contractId     = (int) ($contractId ?? 0);
$inspectionId   = (int) ($inspectionId ?? 0);
$redirect       = $redirect ?? current_url();
?>
<div class="dms-modal-root" data-dms-link-modal="<?= esc($showLinkPicker ? $uploadModalId : '') ?>">
  <?= view('documents/_upload_modal', [
    'modalId'        => $uploadModalId,
    'showLinkPicker' => $showLinkPicker,
    'module'         => $module,
    'refId'          => $refId,
    'docTypes'       => $docTypes,
    'linkOptions'    => $linkOptions,
    'facilityId'     => $facilityId,
    'unitId'         => $unitId,
    'tenantId'       => $tenantId,
    'contractId'     => $contractId,
    'inspectionId'   => $inspectionId,
  ]) ?>
  <?php if ($showTypeModal && $typeModalId): ?>
  <?= view('documents/_type_modal', [
    'typeModalId' => $typeModalId,
    'docTypes'    => $docTypes,
    'redirect'    => $redirect,
  ]) ?>
  <?php endif; ?>
</div>
