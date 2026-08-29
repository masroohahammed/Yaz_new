<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $isEdit = !empty($lead['id']); ?>
<div class="page-header"><div><h1><?= esc($title ?? 'Lead') ?></h1></div><a href="<?= base_url('crm') ?>" class="btn btn-fm-outline btn-sm">Back</a></div>
<div class="form-card"><form method="post" action="<?= $isEdit ? base_url('crm/'.$lead['id'].'/update') : base_url('crm/store') ?>"><?= csrf_field() ?>
<div class="row g-3">
  <div class="col-md-6"><label class="form-label">Full name</label><input name="full_name" class="form-control" required value="<?= esc(old('full_name',$lead['full_name']??'')) ?>"></div>
  <div class="col-md-3"><label class="form-label">Phone</label><input name="phone" class="form-control" value="<?= esc(old('phone',$lead['phone']??'')) ?>"></div>
  <div class="col-md-3"><label class="form-label">Email</label><input name="email" type="email" class="form-control" value="<?= esc(old('email',$lead['email']??'')) ?>"></div>
  <div class="col-md-3"><label class="form-label">Interest</label><select name="interest_type" class="form-select"><?php foreach (['Buy','Rent','Both'] as $i): ?><option value="<?= $i ?>" <?= old('interest_type',$lead['interest_type']??'Rent')===$i?'selected':'' ?>><?= $i ?></option><?php endforeach; ?></select></div>
  <div class="col-md-3"><label class="form-label">Stage</label><select name="stage" class="form-select"><?php foreach (['new','contacted','qualified','viewing','negotiation','won','lost'] as $s): ?><option value="<?= $s ?>" <?= old('stage',$lead['stage']??'new')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-3"><label class="form-label">Temperature</label><select name="temperature" class="form-select"><?php foreach (['Hot','Warm','Cold'] as $tmp): ?><option value="<?= $tmp ?>" <?= old('temperature',$lead['temperature']??'Warm')===$tmp?'selected':'' ?>><?= $tmp ?></option><?php endforeach; ?></select></div>
  <div class="col-md-3"><label class="form-label">Assigned to</label><select name="assigned_to" class="form-select"><option value="">—</option><?php foreach ($users as $u): ?><option value="<?= $u['id'] ?>" <?= old('assigned_to',$lead['assigned_to']??'')==$u['id']?'selected':'' ?>><?= esc($u['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"><?= esc(old('notes',$lead['notes']??'')) ?></textarea></div>
</div>
<div class="mt-3"><button class="btn btn-fm-primary"><?= $isEdit?'Update':'Save' ?></button></div>
</form></div>
<?= $this->endSection() ?>