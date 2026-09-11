<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$editId = (int) ($editId ?? 0);
$editing = null;
foreach ($templates as $t) {
    if ((int) $t['id'] === $editId) {
        $editing = $t;
        break;
    }
}
$editTypeId = (int) ($editTypeId ?? 0);
$editingType = null;
foreach ($contractTypes ?? [] as $ct) {
    if ((int) $ct['id'] === $editTypeId) {
        $editingType = $ct;
        break;
    }
}
$placeholders = \App\Services\ContractTemplateService::PLACEHOLDERS;
?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-file-earmark-richtext me-2"></i>Contract Setup</h1>
    <div class="small text-muted">Manage contract types and bilingual print templates (TinyMCE) with dynamic placeholders.</div>
  </div>
  <a href="<?= base_url('settings') ?>" class="btn btn-fm-outline btn-sm">← Settings</a>
</div>

<div class="row g-3">
  <div class="col-lg-4">
    <div class="fm-card mb-3">
      <div class="fm-card-body">
        <h6 class="mb-3"><?= $editingType ? 'Edit contract type' : 'Add contract type' ?></h6>
        <?= form_open(base_url('settings/contract-types/save')) ?>
        <input type="hidden" name="id" value="<?= $editingType ? (int) $editingType['id'] : 0 ?>">
        <div class="mb-2"><label class="form-label small">Slug</label><input type="text" name="slug" class="form-control form-control-sm" <?= ! empty($editingType['is_system']) ? 'readonly' : '' ?> value="<?= esc($editingType['slug'] ?? old('slug') ?? '') ?>" placeholder="e.g. retail"></div>
        <div class="mb-2"><label class="form-label small">Name (EN)</label><input type="text" name="name_en" class="form-control form-control-sm" required value="<?= esc($editingType['name_en'] ?? old('name_en') ?? '') ?>"></div>
        <div class="mb-2"><label class="form-label small">Name (AR)</label><input type="text" name="name_ar" class="form-control form-control-sm" dir="rtl" value="<?= esc($editingType['name_ar'] ?? old('name_ar') ?? '') ?>"></div>
        <div class="mb-2"><label class="form-label small">Sort order</label><input type="number" name="sort_order" class="form-control form-control-sm" value="<?= esc($editingType['sort_order'] ?? old('sort_order') ?? 99) ?>"></div>
        <div class="mb-3 form-check"><input type="checkbox" name="is_active" value="1" class="form-check-input" id="typeActive" <?= ($editingType['is_active'] ?? 1) ? 'checked' : '' ?>><label class="form-check-label" for="typeActive">Active</label></div>
        <button type="submit" class="btn btn-fm-primary btn-sm w-100"><?= $editingType ? 'Update type' : 'Add type' ?></button>
        <?php if ($editingType): ?><a href="<?= base_url('settings/contract-templates') ?>" class="btn btn-fm-outline btn-sm w-100 mt-2">Cancel</a><?php endif; ?>
        <?= form_close() ?>
      </div>
    </div>

    <div class="fm-card">
      <div class="fm-card-body p-0">
        <table class="fm-table table-sm mb-0">
          <thead><tr><th>Type</th><th>Status</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($contractTypes ?? [] as $ct): ?>
            <tr>
              <td class="small fw-semibold"><?= esc($ct['name_en']) ?><?= ! empty($ct['is_system']) ? ' <span class="text-muted">(system)</span>' : '' ?></td>
              <td><?= ! empty($ct['is_active']) ? '<span class="badge bg-success-subtle text-success">Active</span>' : '<span class="badge bg-secondary">Off</span>' ?></td>
              <td class="text-end"><a href="<?= base_url('settings/contract-templates?edit_type='.(int)$ct['id']) ?>" class="btn btn-sm btn-fm-outline">Edit</a></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="fm-card mb-3">
      <div class="fm-card-body">
        <h6 class="mb-3"><?= $editing ? 'Edit template' : 'New template' ?></h6>
        <?= form_open(base_url('settings/contract-templates/save')) ?>
        <input type="hidden" name="id" value="<?= $editing ? (int) $editing['id'] : 0 ?>">
        <div class="row g-2">
          <div class="col-md-6"><label class="form-label small">Template name</label><input type="text" name="name" class="form-control form-control-sm" required value="<?= esc($editing['name'] ?? old('name') ?? '') ?>"></div>
          <div class="col-md-6">
            <label class="form-label small">Contract type</label>
            <select name="contract_type_id" class="form-select form-select-sm" required>
              <option value="">— Select type —</option>
              <?php foreach ($contractTypes ?? [] as $ct): ?>
              <option value="<?= (int) $ct['id'] ?>" <?= (int)($editing['contract_type_id'] ?? 0) === (int)$ct['id'] ? 'selected' : '' ?>><?= esc($ct['name_en']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-check my-2"><input type="checkbox" name="is_active" value="1" class="form-check-input" id="tplActive" <?= ($editing['is_active'] ?? 1) ? 'checked' : '' ?>><label class="form-check-label" for="tplActive">Active</label></div>
        <div class="mb-2"><label class="form-label small">Body (English)</label><textarea name="content_en" class="form-control fm-tinymce" rows="6"><?= esc($editing['content_en'] ?? old('content_en') ?? '') ?></textarea></div>
        <div class="mb-2"><label class="form-label small">Body (Arabic)</label><textarea name="content_ar" class="form-control fm-tinymce-rtl" rows="6"><?= esc($editing['content_ar'] ?? old('content_ar') ?? '') ?></textarea></div>
        <div class="mb-2"><label class="form-label small">Terms & conditions (EN)</label><textarea name="terms_en" class="form-control fm-tinymce" rows="4"><?= esc($editing['terms_en'] ?? old('terms_en') ?? '') ?></textarea></div>
        <div class="mb-2"><label class="form-label small">Terms & conditions (AR)</label><textarea name="terms_ar" class="form-control fm-tinymce-rtl" rows="4"><?= esc($editing['terms_ar'] ?? old('terms_ar') ?? '') ?></textarea></div>
        <div class="form-text mb-2">Placeholders: <?= esc(implode(', ', $placeholders)) ?></div>
        <button type="submit" class="btn btn-fm-primary"><?= $editing ? 'Update template' : 'Create template' ?></button>
        <?php if ($editing): ?><a href="<?= base_url('settings/contract-templates') ?>" class="btn btn-fm-outline ms-2">Cancel edit</a><?php endif; ?>
        <?= form_close() ?>
      </div>
    </div>

    <div class="fm-card">
      <div class="fm-card-body p-0" style="overflow-x:auto">
        <table class="fm-table table-sm">
          <thead><tr><th>Name</th><th>Type</th><th>Status</th><th></th></tr></thead>
          <tbody>
          <?php if (empty($templates)): ?>
            <tr><td colspan="4" class="text-muted text-center py-4">No templates yet.</td></tr>
          <?php else: ?>
            <?php foreach ($templates as $tpl): ?>
            <tr>
              <td class="fw-semibold"><?= esc($tpl['name']) ?></td>
              <td class="small"><?= esc($tpl['type_name'] ?? '—') ?></td>
              <td><?= ! empty($tpl['is_active']) ? '<span class="badge bg-success-subtle text-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
              <td class="text-end"><a href="<?= base_url('settings/contract-templates?edit='.(int)$tpl['id']) ?>" class="btn btn-sm btn-fm-outline">Edit</a></td>
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
<?= $this->section('scripts') ?>
<?= $this->include('partials/tinymce', ['tinymceHeight' => 220]) ?>
<?= $this->endSection() ?>
