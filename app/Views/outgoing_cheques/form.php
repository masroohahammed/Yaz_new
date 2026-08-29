<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $isEdit = !empty($cheque['id']); ?>
<div class="page-header"><div><h1><?= esc($title ?? 'Outgoing Cheque') ?></h1></div><a href="<?= base_url('outgoing-cheques') ?>" class="btn btn-fm-outline btn-sm">Back</a></div>
<div class="form-card"><form method="post" action="<?= $isEdit ? base_url('outgoing-cheques/'.$cheque['id'].'/update') : base_url('outgoing-cheques') ?>"><?= csrf_field() ?>
<div class="row g-3">
  <div class="col-md-3"><label class="form-label">Cheque no</label><input name="cheque_no" class="form-control" required value="<?= esc(old('cheque_no',$cheque['cheque_no']??'')) ?>"></div>
  <div class="col-md-3"><label class="form-label">Bank</label><input name="bank_name" class="form-control" required value="<?= esc(old('bank_name',$cheque['bank_name']??'')) ?>"></div>
  <div class="col-md-3"><label class="form-label">Amount</label><input type="number" step="0.01" name="amount" class="form-control" required value="<?= esc(old('amount',$cheque['amount']??'')) ?>"></div>
  <div class="col-md-3"><label class="form-label">Date</label><input type="date" name="cheque_date" class="form-control" required value="<?= esc(old('cheque_date',$cheque['cheque_date']??'')) ?>"></div>
  <div class="col-md-6"><label class="form-label">Payee</label><input name="payee_name" class="form-control" required value="<?= esc(old('payee_name',$cheque['payee_name']??'')) ?>"></div>
  <div class="col-md-6"><label class="form-label">Purpose</label><input name="purpose" class="form-control" required value="<?= esc(old('purpose',$cheque['purpose']??'')) ?>"></div>
  <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select"><?php foreach (['pending','issued','cleared','cancelled'] as $s): ?><option value="<?= $s ?>" <?= old('status',$cheque['status']??'pending')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
</div>
<div class="mt-3"><button class="btn btn-fm-primary"><?= $isEdit?'Update':'Save' ?></button></div>
</form></div>
<?= $this->endSection() ?>