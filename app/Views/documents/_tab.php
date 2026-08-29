<?php
/**
 * Documents list + upload (embed on entity pages or full hub).
 */
helper('fm');

$module            = $module ?? '';
$refId             = (int) ($refId ?? 0);
$embed             = ! empty($embed);
$migrationRequired = ! empty($migrationRequired);
$documents         = $documents ?? ($docs ?? []);
$filters           = $filters ?? [];
$docTypes          = $docTypes ?? fm_document_types();
$linkOptions       = $linkOptions ?? [];
$facilityId        = (int) ($facilityId ?? 0);
$unitId            = (int) ($unitId ?? 0);
$tenantId          = (int) ($tenantId ?? 0);
$contractId        = (int) ($contractId ?? 0);
$inspectionId      = (int) ($inspectionId ?? 0);

$hub         = ! $embed;
$canUpload   = $hub || ($module && $refId);
$uploadModal = $hub ? 'dmsUploadModal' : 'dmsUpload' . preg_replace('/[^a-z0-9]/', '', strtolower($module)) . $refId;

$filterBase = $embed && $module && $refId
    ? current_url() . '#tab-documents'
    : base_url('documents?' . http_build_query(array_filter(['module' => $module ?: null, 'ref_id' => $refId ?: null])));
?>
<link rel="stylesheet" href="<?= base_url('assets/css/dms.css') ?>">

<?php if ($migrationRequired): ?>
<div class="alert alert-warning">Documents table not ready. Run <code>database/pm_dms_hrms_patch.sql</code>.</div>
<?php else: ?>

<div class="dms-panel <?= $embed ? 'dms-panel-embed' : '' ?>">
  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <h6 class="dms-title mb-0"><i class="bi bi-folder2-open me-2"></i>Documents</h6>
    <div class="d-flex gap-2">
      <?php if ($canUpload): ?>
      <button type="button" class="btn btn-sm btn-fm-primary" data-bs-toggle="modal" data-bs-target="#<?= esc($uploadModal) ?>">
        <i class="bi bi-cloud-arrow-up me-1"></i>Upload document
      </button>
      <?php endif; ?>
      <?php if ($hub): ?>
      <button type="button" class="btn btn-sm btn-fm-outline" data-bs-toggle="modal" data-bs-target="#dmsTypeModal">
        <i class="bi bi-tags me-1"></i>Document types
      </button>
      <?php else: ?>
      <a href="<?= base_url('documents') ?>" class="btn btn-sm btn-fm-outline" target="_blank">Open DMS</a>
      <?php endif; ?>
    </div>
  </div>

  <form method="get" action="<?= esc($filterBase) ?>" class="dms-filter-box mb-3">
    <?php if ($module): ?><input type="hidden" name="module" value="<?= esc($module) ?>"><?php endif; ?>
    <?php if ($refId): ?><input type="hidden" name="ref_id" value="<?= $refId ?>"><?php endif; ?>
    <div class="row g-2 align-items-end">
      <div class="col-md-2">
        <label class="form-label small">Type</label>
        <select name="doc_type" class="form-select form-select-sm">
          <option value="">All</option>
          <?php foreach ($docTypes as $k => $label): ?>
          <option value="<?= esc($k) ?>" <?= ($filters['doc_type'] ?? '') === $k ? 'selected' : '' ?>><?= esc($label) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2"><label class="form-label small">Month</label><input type="month" name="month" class="form-control form-control-sm" value="<?= esc($filters['month'] ?? '') ?>"></div>
      <div class="col-md-2"><label class="form-label small">From</label><input type="date" name="date_from" class="form-control form-control-sm" value="<?= esc($filters['date_from'] ?? '') ?>"></div>
      <div class="col-md-2"><label class="form-label small">To</label><input type="date" name="date_to" class="form-control form-control-sm" value="<?= esc($filters['date_to'] ?? '') ?>"></div>
      <div class="col-md-2">
        <label class="form-label small">Expiry</label>
        <select name="expiry_status" class="form-select form-select-sm">
          <option value="">Any</option>
          <option value="valid" <?= ($filters['expiry_status'] ?? '') === 'valid' ? 'selected' : '' ?>>Valid</option>
          <option value="expiring" <?= ($filters['expiry_status'] ?? '') === 'expiring' ? 'selected' : '' ?>>Expiring</option>
          <option value="expired" <?= ($filters['expiry_status'] ?? '') === 'expired' ? 'selected' : '' ?>>Expired</option>
        </select>
      </div>
      <div class="col-md-2"><label class="form-label small">Search</label><input type="search" name="search" class="form-control form-control-sm" value="<?= esc($filters['search'] ?? '') ?>"></div>
      <div class="col-md-2"><button type="submit" class="btn btn-fm-primary btn-sm w-100">Filter</button></div>
    </div>
  </form>

  <div class="table-responsive">
    <table class="table table-sm table-hover dms-table mb-0">
      <thead><tr><th>Name</th><th>Type</th><th>Issue</th><th>Expiry</th><th>Status</th><th>Uploaded</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($documents as $doc):
        $exp = $doc['expiry_date'] ?? '';
        $st  = $exp ? ((strtotime($exp) < strtotime('today')) ? 'expired' : ((strtotime($exp) <= strtotime('+30 days')) ? 'expiring' : 'valid')) : ($doc['status'] ?? 'valid');
      ?>
      <tr>
        <td><?php if (! empty($doc['is_confidential'])): ?><i class="bi bi-shield-lock text-warning me-1"></i><?php endif; ?><?= esc($doc['title']) ?></td>
        <td class="small"><?= esc($docTypes[$doc['doc_type'] ?? 'general'] ?? $doc['doc_type'] ?? 'general') ?></td>
        <td class="small"><?= esc($doc['issue_date'] ?? '—') ?></td>
        <td class="small"><?= esc($doc['expiry_date'] ?? '—') ?></td>
        <td><span class="badge dms-badge-<?= esc($st) ?>"><?= ucfirst($st) ?></span></td>
        <td class="small text-muted"><?= esc($doc['created_at'] ?? '') ?></td>
        <td class="text-end">
          <?php if (! empty($doc['file_path'])): ?>
          <a href="<?= base_url('file/' . ltrim(str_replace('uploads/', '', $doc['file_path']), '/')) ?>" target="_blank" class="btn btn-sm btn-fm-outline">Open</a>
          <?php endif; ?>
          <?= form_open(base_url('documents/delete/' . (int) $doc['id']), ['class' => 'd-inline', 'onsubmit' => 'return confirm("Delete?")']) ?>
            <?= csrf_field() ?><button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
          <?= form_close() ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($documents)): ?><tr><td colspan="7" class="text-muted text-center py-4">No documents yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if ($hub && $canUpload): ?>
<?php if (! empty($linkOptions)): ?>
<script type="application/json" id="dmsLinkData"><?= json_encode($linkOptions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?></script>
<?php endif; ?>
<?= view('documents/_upload_modal', [
  'hub'          => true,
  'modalId'      => $uploadModal,
  'module'       => $module,
  'refId'        => $refId,
  'docTypes'     => $docTypes,
  'linkOptions'  => $linkOptions,
  'facilityId'   => $facilityId,
  'unitId'       => $unitId,
  'tenantId'     => $tenantId,
  'contractId'   => $contractId,
  'inspectionId' => $inspectionId,
]) ?>
<?php endif; ?>

<?php if ($hub): ?>
<?= view('documents/_type_modal', ['docTypes' => $docTypes, 'redirect' => current_url()]) ?>
<?php endif; ?>

<?php endif; ?>
