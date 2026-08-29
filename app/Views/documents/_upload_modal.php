<?php
/**
 * Document upload modal — single clean popup.
 *
 * @var bool $hub           Central /documents hub (entity picker + type manager)
 * @var string $modalId     Modal element id
 * @var string $module
 * @var int $refId
 * @var array $docTypes
 * @var array $linkOptions
 */
$hub           = ! empty($hub);
$modalId       = $modalId ?? ($hub ? 'dmsUploadModal' : 'dmsUploadEmbed');
$module        = $module ?? '';
$refId         = (int) ($refId ?? 0);
$docTypes      = $docTypes ?? [];
$linkOptions   = $linkOptions ?? [];
$facilityId    = (int) ($facilityId ?? 0);
$unitId        = (int) ($unitId ?? 0);
$tenantId      = (int) ($tenantId ?? 0);
$contractId    = (int) ($contractId ?? 0);
$inspectionId  = (int) ($inspectionId ?? 0);
?>
<div class="modal fade" id="<?= esc($modalId) ?>" tabindex="-1" aria-labelledby="<?= esc($modalId) ?>Label" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="<?= base_url('documents/store') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <?php if (! $hub): ?>
        <input type="hidden" name="module" value="<?= esc($module) ?>">
        <input type="hidden" name="ref_id" value="<?= $refId ?>">
        <?php if ($facilityId): ?><input type="hidden" name="facility_id" value="<?= $facilityId ?>"><?php endif; ?>
        <?php if ($unitId): ?><input type="hidden" name="unit_id" value="<?= $unitId ?>"><?php endif; ?>
        <?php if ($tenantId): ?><input type="hidden" name="tenant_id" value="<?= $tenantId ?>"><?php endif; ?>
        <?php if ($contractId): ?><input type="hidden" name="contract_id" value="<?= $contractId ?>"><?php endif; ?>
        <?php if ($inspectionId): ?><input type="hidden" name="inspection_id" value="<?= $inspectionId ?>"><?php endif; ?>
        <?php endif; ?>

        <div class="modal-header">
          <h5 class="modal-title" id="<?= esc($modalId) ?>Label"><i class="bi bi-cloud-arrow-up me-2"></i>Upload document</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <div class="modal-body">
          <?php if ($hub): ?>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">Link to</label>
              <select name="link_entity_type" class="form-select" id="dmsLinkType" required>
                <?php foreach ($linkOptions as $key => $opt): ?>
                <option value="<?= esc($key) ?>"><?= esc($opt['label'] ?? $key) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Record</label>
              <select name="link_entity_id" class="form-select" id="dmsLinkEntity">
                <option value="">— Select —</option>
              </select>
            </div>
          </div>
          <?php endif; ?>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Document type</label>
              <select name="doc_type" class="form-select" required>
                <?php foreach ($docTypes as $k => $label): ?>
                <option value="<?= esc($k) ?>"><?= esc($label) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Name</label>
              <input type="text" name="title" class="form-control" required placeholder="Document name">
            </div>
            <div class="col-md-4">
              <label class="form-label">Issue date</label>
              <input type="date" name="issue_date" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Expiry date</label>
              <input type="date" name="expiry_date" class="form-control">
            </div>
            <div class="col-md-4 d-flex align-items-end">
              <div class="form-check form-switch mb-2">
                <input type="checkbox" class="form-check-input" name="is_confidential" value="1" id="<?= esc($modalId) ?>Confidential">
                <label class="form-check-label" for="<?= esc($modalId) ?>Confidential">Confidential</label>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label">Files</label>
              <div class="dms-dropzone" data-dms-dropzone>
                <input type="file" name="documents[]" multiple class="dms-file-input" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.webp">
                <div class="dms-dropzone-inner">
                  <i class="bi bi-cloud-arrow-up fs-4 text-muted"></i>
                  <p class="mb-0 small">Drag & drop files here or click to browse</p>
                  <p class="text-muted x-small mb-0">Multiple files · PDF, Office, images</p>
                </div>
                <div class="dms-file-list small mt-2"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-fm-outline" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-fm-primary"><i class="bi bi-upload me-1"></i>Upload</button>
        </div>
      </form>
    </div>
  </div>
</div>
