<?php if (empty($embed)): ?><?= $this->extend('layouts/main') ?><?= $this->section('content') ?><?php endif; ?>
<?php
$hrDocs = $hrDocs ?? null;
$canUpload = $canUpload ?? true;
$canDelete = $canDelete ?? true;
$categories = $categories ?? [];
$facilityId = $facilityId ?? null;
?>
<?php if (!empty($migrationRequired)): ?>
<div class="alert alert-warning mb-0">Documents table not ready. Run PM ERP migration.</div>
<?php else: ?>
<div class="form-card <?= !empty($embed) ? 'mb-0' : '' ?>">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="mb-0 text-muted text-uppercase small">Documents<?php if ($module && $refId): ?> — <?= esc($module) ?> #<?= (int)$refId ?><?php endif; ?></h6>
  </div>
  <?php if ($module && $refId && $canUpload): ?>
  <form method="post" action="<?= base_url('documents/store') ?>" enctype="multipart/form-data" class="mb-3 border rounded p-3 bg-light"><?= csrf_field() ?>
    <input type="hidden" name="module" value="<?= esc($module) ?>">
    <input type="hidden" name="ref_id" value="<?= (int)$refId ?>">
    <?php if ($facilityId): ?><input type="hidden" name="facility_id" value="<?= (int)$facilityId ?>"><?php endif; ?>
    <div class="row g-2">
      <div class="col-md-4">
        <label class="form-label small mb-0">Title *</label>
        <input name="title" class="form-control form-control-sm" required>
      </div>
      <?php if (!empty($categories)): ?>
      <div class="col-md-3">
        <label class="form-label small mb-0">Category</label>
        <select name="category_id" class="form-select form-select-sm">
          <option value="">— General —</option>
          <?php foreach ($categories as $cat): ?>
          <option value="<?= (int)$cat['id'] ?>"><?= esc($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php else: ?>
      <div class="col-md-3">
        <label class="form-label small mb-0">Type</label>
        <input name="doc_type" class="form-control form-control-sm" placeholder="e.g. passport">
      </div>
      <?php endif; ?>
      <div class="col-md-3">
        <label class="form-label small mb-0">Document Number</label>
        <input name="doc_number" class="form-control form-control-sm">
      </div>
      <div class="col-md-2">
        <label class="form-label small mb-0">Issue Date</label>
        <input type="date" name="issue_date" class="form-control form-control-sm">
      </div>
      <div class="col-md-2">
        <label class="form-label small mb-0">Expiry Date</label>
        <input type="date" name="expiry_date" class="form-control form-control-sm">
      </div>
      <div class="col-md-4">
        <label class="form-label small mb-0">File *</label>
        <input type="file" name="document" class="form-control form-control-sm" required>
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button class="btn btn-fm-primary btn-sm w-100">Upload</button>
      </div>
    </div>
  </form>
  <?php elseif ($module && $refId && !$canUpload): ?>
  <div class="alert alert-light border small mb-3"><i class="bi bi-lock me-1"></i>You do not have permission to upload documents.</div>
  <?php endif; ?>

  <div class="table-responsive">
    <table class="table table-registry table-sm mb-0">
      <thead><tr><th>Title</th><th>Type</th><th>Number</th><th>Expiry</th><th>Uploaded</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($documents as $doc): ?>
      <?php
        $expiryClass = $hrDocs ? $hrDocs->expiryBadgeClass($doc['expiry_date'] ?? null) : 'bg-secondary-subtle';
        $expiryLabel = $doc['expiry_date'] ? date('d M Y', strtotime($doc['expiry_date'])) : '—';
      ?>
      <tr>
        <td><?= esc($doc['title']) ?></td>
        <td class="small"><?= esc($doc['category_name'] ?? $doc['doc_type'] ?? 'general') ?></td>
        <td class="small"><?= esc($doc['doc_number'] ?? '—') ?></td>
        <td><span class="badge <?= $expiryClass ?>"><?= $expiryLabel ?></span></td>
        <td class="small text-muted"><?= esc(substr($doc['created_at'] ?? '', 0, 10)) ?></td>
        <td class="text-end">
          <?php if (!empty($doc['file_path'])): ?>
          <a href="<?= base_url('file/'.ltrim(str_replace('uploads/', '', $doc['file_path']), '/')) ?>" target="_blank" class="btn btn-sm btn-fm-outline">Open</a>
          <?php endif; ?>
          <?php if ($canDelete): ?>
          <?= form_open(base_url('documents/delete/'.$doc['id']), ['class' => 'd-inline']) ?>
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this document?')"><i class="bi bi-trash"></i></button>
          <?= form_close() ?>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($documents)): ?><tr><td colspan="6" class="text-muted">No documents.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
<?php if (empty($embed)): ?><?= $this->endSection() ?><?php endif; ?>
