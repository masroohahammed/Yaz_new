<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $isEdit = !empty($landlord['id']); $ro = !empty($readOnly); ?>
<div class="page-header">
  <div><h1><?= esc($title ?? 'Landlord') ?></h1></div>
  <a href="<?= base_url('landlords') ?>" class="btn btn-fm-outline btn-sm">Back</a>
</div>
<div class="form-card">
<?php if ($ro): ?>
  <dl class="row mb-0">
    <dt class="col-sm-3">Name</dt><dd class="col-sm-9"><?= esc($landlord['full_name']) ?></dd>
    <dt class="col-sm-3">Phone</dt><dd class="col-sm-9"><?= esc($landlord['phone'] ?? '—') ?></dd>
    <dt class="col-sm-3">Email</dt><dd class="col-sm-9"><?= esc($landlord['email'] ?? '—') ?></dd>
    <dt class="col-sm-3">Status</dt><dd class="col-sm-9"><?= esc($landlord['status']) ?></dd>
  </dl>
<?php else: ?>
<form method="post" action="<?= $isEdit ? base_url('landlords/'.$landlord['id'].'/update') : base_url('landlords/store') ?>">
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-md-6"><label class="form-label">Full name</label><input name="full_name" class="form-control" required value="<?= esc(old('full_name', $landlord['full_name'] ?? '')) ?>"></div>
    <div class="col-md-6"><label class="form-label">Arabic name</label><input name="full_name_ar" class="form-control" value="<?= esc(old('full_name_ar', $landlord['full_name_ar'] ?? '')) ?>"></div>
    <div class="col-md-4"><label class="form-label">Phone</label><input name="phone" class="form-control" value="<?= esc(old('phone', $landlord['phone'] ?? '')) ?>"></div>
    <div class="col-md-4"><label class="form-label">Email</label><input name="email" type="email" class="form-control" value="<?= esc(old('email', $landlord['email'] ?? '')) ?>"></div>
    <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select"><?php foreach (['active','inactive'] as $s): ?><option value="<?= $s ?>" <?= old('status',$landlord['status']??'active')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-4"><label class="form-label">ID number</label><input name="id_number" class="form-control" value="<?= esc(old('id_number', $landlord['id_number'] ?? '')) ?>"></div>
    <div class="col-md-4"><label class="form-label">Bank IBAN</label><input name="bank_iban" class="form-control" value="<?= esc(old('bank_iban', $landlord['bank_iban'] ?? '')) ?>"></div>
    <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"><?= esc(old('notes', $landlord['notes'] ?? '')) ?></textarea></div>
  </div>
  <div class="mt-3"><button class="btn btn-fm-primary"><?= $isEdit ? 'Update' : 'Save' ?></button></div>
</form>
<?php endif; ?>
</div>
<?= $this->endSection() ?>
