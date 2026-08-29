<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $isEdit = !empty($tenant['id']); $ro = !empty($readOnly); ?>
<div class="page-header">
  <div><h1><?= esc($title ?? 'Tenant') ?></h1></div>
  <a href="<?= base_url('tenants') ?>" class="btn btn-fm-outline btn-sm">Back</a>
</div>
<div class="form-card">
<?php if ($ro): ?>
  <dl class="row mb-0">
    <dt class="col-sm-3">Type</dt><dd class="col-sm-9"><?= esc($tenant['tenant_type']) ?></dd>
    <dt class="col-sm-3">Name</dt><dd class="col-sm-9"><?= esc($tenant['full_name']) ?></dd>
    <dt class="col-sm-3">Phone</dt><dd class="col-sm-9"><?= esc($tenant['phone']) ?></dd>
    <dt class="col-sm-3">Email</dt><dd class="col-sm-9"><?= esc($tenant['email'] ?? '—') ?></dd>
    <dt class="col-sm-3">QID</dt><dd class="col-sm-9"><?= esc($tenant['qid_no'] ?? '—') ?></dd>
    <dt class="col-sm-3">Passport</dt><dd class="col-sm-9"><?= esc($tenant['passport_no'] ?? '—') ?></dd>
    <dt class="col-sm-3">Status</dt><dd class="col-sm-9"><?= esc($tenant['status']) ?></dd>
  </dl>
<?php else: ?>
<form method="post" action="<?= $isEdit ? base_url('tenants/'.$tenant['id'].'/update') : base_url('tenants/store') ?>">
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-md-4"><label class="form-label">Tenant type</label>
      <select name="tenant_type" class="form-select" required>
        <?php foreach (['Personal','Corporate'] as $t): ?><option value="<?= $t ?>" <?= old('tenant_type', $tenant['tenant_type'] ?? 'Personal') === $t ? 'selected' : '' ?>><?= $t ?></option><?php endforeach; ?>
      </select></div>
    <div class="col-md-8"><label class="form-label">Full name</label><input name="full_name" class="form-control" required value="<?= esc(old('full_name', $tenant['full_name'] ?? '')) ?>"></div>
    <div class="col-md-4"><label class="form-label">Phone</label><input name="phone" class="form-control" required value="<?= esc(old('phone', $tenant['phone'] ?? '')) ?>"></div>
    <div class="col-md-4"><label class="form-label">WhatsApp</label><input name="whatsapp" class="form-control" value="<?= esc(old('whatsapp', $tenant['whatsapp'] ?? '')) ?>"></div>
    <div class="col-md-4"><label class="form-label">Email</label><input name="email" type="email" class="form-control" value="<?= esc(old('email', $tenant['email'] ?? '')) ?>"></div>
    <div class="col-md-4"><label class="form-label">QID no</label><input name="qid_no" class="form-control" value="<?= esc(old('qid_no', $tenant['qid_no'] ?? '')) ?>"></div>
    <div class="col-md-4"><label class="form-label">QID expiry</label><input name="qid_expiry" type="date" class="form-control" value="<?= esc(old('qid_expiry', $tenant['qid_expiry'] ?? '')) ?>"></div>
    <div class="col-md-4"><label class="form-label">Passport no</label><input name="passport_no" class="form-control" value="<?= esc(old('passport_no', $tenant['passport_no'] ?? '')) ?>"></div>
    <div class="col-md-4"><label class="form-label">Passport expiry</label><input name="passport_expiry" type="date" class="form-control" value="<?= esc(old('passport_expiry', $tenant['passport_expiry'] ?? '')) ?>"></div>
    <div class="col-md-4"><label class="form-label">Company name</label><input name="company_name" class="form-control" value="<?= esc(old('company_name', $tenant['company_name'] ?? '')) ?>"></div>
    <div class="col-md-4"><label class="form-label">Company CR</label><input name="company_cr" class="form-control" value="<?= esc(old('company_cr', $tenant['company_cr'] ?? '')) ?>"></div>
    <div class="col-md-4"><label class="form-label">Nationality</label><input name="nationality" class="form-control" value="<?= esc(old('nationality', $tenant['nationality'] ?? '')) ?>"></div>
    <div class="col-md-4"><label class="form-label">Status</label>
      <select name="status" class="form-select"><?php foreach (['active','inactive','blacklisted'] as $s): ?><option value="<?= $s ?>" <?= old('status', $tenant['status'] ?? 'active') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
    <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"><?= esc(old('notes', $tenant['notes'] ?? '')) ?></textarea></div>
  </div>
  <div class="mt-3"><button class="btn btn-fm-primary"><?= $isEdit ? 'Update' : 'Save' ?> Tenant</button></div>
</form>
<?php endif; ?>
</div>
<?= $this->endSection() ?>
