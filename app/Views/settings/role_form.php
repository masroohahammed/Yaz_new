<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $isEdit = ! empty($role['id']); ?>
<div class="page-header">
  <div><h1><i class="bi bi-person-badge me-2"></i><?= esc($title ?? 'Role') ?></h1></div>
  <a href="<?= base_url('settings/roles') ?>" class="btn btn-fm-outline btn-sm">← Roles</a>
</div>

<div class="form-card mb-3">
<?= form_open($isEdit ? base_url('settings/roles/update/'.$role['id']) : base_url('settings/roles/store')) ?>
  <?= csrf_field() ?>
  <div class="row g-3">
    <?php if (! $isEdit): ?>
    <div class="col-md-4">
      <label class="form-label">Role slug <span class="text-danger">*</span></label>
      <input name="name" class="form-control" required pattern="[a-z0-9_]+" placeholder="e.g. regional_manager" value="<?= esc(old('name')) ?>">
      <div class="form-text">Lowercase letters, numbers, underscores only.</div>
    </div>
    <?php else: ?>
    <div class="col-md-4">
      <label class="form-label">Role slug</label>
      <input class="form-control" value="<?= esc($role['name']) ?>" disabled>
    </div>
    <?php endif; ?>
    <div class="col-md-4">
      <label class="form-label">Display name <span class="text-danger">*</span></label>
      <input name="display_name" class="form-control" required value="<?= esc(old('display_name', $role['display_name'] ?? '')) ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Workspace <span class="text-danger">*</span></label>
      <select name="workspace" class="form-select" required>
        <?php foreach ($workspaces as $v => $l): ?>
        <option value="<?= esc($v) ?>" <?= old('workspace', $role['workspace'] ?? 'fm') === $v ? 'selected' : '' ?>><?= esc($l) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-12">
      <label class="form-label">Description</label>
      <input name="description" class="form-control" value="<?= esc(old('description', $role['description'] ?? '')) ?>">
    </div>
  </div>

  <hr class="my-4">
  <h6 class="text-primary mb-3"><i class="bi bi-check2-square me-2"></i>Approval workflow permissions</h6>
  <p class="small text-muted">Control which approval actions this role can perform. Global workflow gates are configured under <a href="<?= base_url('settings/workflow') ?>">Workflow Settings</a>.</p>

  <div class="row g-3">
    <div class="col-md-6">
      <div class="border rounded p-3 h-100">
        <h6 class="text-muted text-uppercase small">Facility Management (FM)</h6>
        <?php foreach ($approvalKeys['fm'] ?? [] as $key => $label): ?>
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" name="approval[fm][<?= esc($key) ?>]" value="1" id="ap_fm_<?= esc($key) ?>"
            <?= ! empty($approvals['fm'][$key]) ? 'checked' : '' ?>>
          <label class="form-check-label small" for="ap_fm_<?= esc($key) ?>"><?= esc($label) ?></label>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="col-md-6">
      <div class="border rounded p-3 h-100">
        <h6 class="text-muted text-uppercase small">Property Management (PM)</h6>
        <?php foreach ($approvalKeys['pm'] ?? [] as $key => $label): ?>
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" name="approval[pm][<?= esc($key) ?>]" value="1" id="ap_pm_<?= esc($key) ?>"
            <?= ! empty($approvals['pm'][$key]) ? 'checked' : '' ?>>
          <label class="form-check-label small" for="ap_pm_<?= esc($key) ?>"><?= esc($label) ?></label>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div class="mt-3">
    <button type="submit" class="btn btn-fm-primary"><?= $isEdit ? 'Save Role' : 'Create Role' ?></button>
  </div>
<?= form_close() ?>
</div>

<?php if ($isEdit): ?>
<p class="small text-muted">After saving, configure module access on the <a href="<?= base_url('settings/roles') ?>#permissions">permissions matrix</a>.</p>
<?php endif; ?>
<?= $this->endSection() ?>
