<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$editId = (int) ($this->request->getGet('edit') ?? 0);
$editing = null;
foreach ($templates as $t) {
    if ((int) $t['id'] === $editId) {
        $editing = $t;
        break;
    }
}
?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-file-earmark-richtext me-2"></i>Contract Templates</h1>
    <div class="small text-muted">Bilingual EN/AR lease templates with merge placeholders.</div>
  </div>
  <a href="<?= base_url('settings') ?>" class="btn btn-fm-outline btn-sm">← Settings</a>
</div>

<div class="row g-3">
  <div class="col-lg-5">
    <div class="fm-card">
      <div class="fm-card-body">
        <h6 class="mb-3"><?= $editing ? 'Edit template' : 'New template' ?></h6>
        <?= form_open(base_url('settings/contract-templates/save')) ?>
        <input type="hidden" name="id" value="<?= $editing ? (int) $editing['id'] : 0 ?>">
        <div class="mb-3">
          <label class="form-label">Template name</label>
          <input type="text" name="name" class="form-control" required value="<?= esc($editing['name'] ?? old('name') ?? '') ?>">
        </div>
        <div class="mb-3 form-check">
          <input type="checkbox" name="is_active" value="1" class="form-check-input" id="tplActive" <?= ($editing['is_active'] ?? 1) ? 'checked' : '' ?>>
          <label class="form-check-label" for="tplActive">Active</label>
        </div>
        <div class="mb-3">
          <label class="form-label">Content (English)</label>
          <textarea name="content_en" class="form-control font-monospace" rows="8"><?= esc($editing['content_en'] ?? old('content_en') ?? '') ?></textarea>
          <div class="form-text">Placeholders: {{property_name}}, {{unit_number}}, {{rent_amount}}, {{currency}}, {{start_date}}, {{end_date}}, {{payment_frequency}}</div>
        </div>
        <div class="mb-3">
          <label class="form-label">Content (Arabic)</label>
          <textarea name="content_ar" class="form-control font-monospace" dir="rtl" rows="8"><?= esc($editing['content_ar'] ?? old('content_ar') ?? '') ?></textarea>
        </div>
        <button type="submit" class="btn btn-fm-primary w-100">
          <i class="bi bi-check-lg me-1"></i><?= $editing ? 'Update template' : 'Create template' ?>
        </button>
        <?php if ($editing): ?>
        <a href="<?= base_url('settings/contract-templates') ?>" class="btn btn-fm-outline w-100 mt-2">Cancel edit</a>
        <?php endif; ?>
        <?= form_close() ?>
      </div>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="fm-card">
      <div class="fm-card-body p-0" style="overflow-x:auto">
        <table class="fm-table table-sm">
          <thead>
            <tr>
              <th>Name</th>
              <th>Status</th>
              <th>Updated</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($templates)): ?>
            <tr><td colspan="4" class="text-muted text-center py-4">No templates yet.</td></tr>
          <?php else: ?>
            <?php foreach ($templates as $tpl): ?>
            <tr>
              <td class="fw-semibold"><?= esc($tpl['name']) ?></td>
              <td>
                <?php if (! empty($tpl['is_active'])): ?>
                <span class="badge bg-success-subtle text-success">Active</span>
                <?php else: ?>
                <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                <?php endif; ?>
              </td>
              <td class="small text-muted"><?= esc($tpl['updated_at'] ?? $tpl['created_at'] ?? '—') ?></td>
              <td class="text-end">
                <a href="<?= base_url('settings/contract-templates?edit=' . (int) $tpl['id']) ?>" class="btn btn-sm btn-fm-outline">Edit</a>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
