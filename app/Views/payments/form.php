<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $isEdit = !empty($payment['id']); ?>
<div class="page-header"><div><h1><?= esc($title ?? 'Payment') ?></h1></div><a href="<?= base_url('payments') ?>" class="btn btn-fm-outline btn-sm">Back</a></div>
<div class="form-card">
<form method="post" action="<?= $isEdit ? base_url('payments/'.$payment['id'].'/update') : base_url('payments') ?>"><?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-md-6"><label class="form-label">Contract</label><select name="contract_id" class="form-select"><option value="">—</option><?php foreach ($contracts as $c): ?><option value="<?= $c['id'] ?>" <?= old('contract_id',$payment['contract_id']??'')==$c['id']?'selected':'' ?>><?= esc($c['contract_number'].' — '.($c['tenant_name']??'')) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><label class="form-label">Amount</label><input type="number" step="0.01" name="amount" class="form-control" required value="<?= esc(old('amount',$payment['amount']??'')) ?>"></div>
    <div class="col-md-3"><label class="form-label">Due date</label><input type="date" name="due_date" class="form-control" value="<?= esc(old('due_date',$payment['due_date']??'')) ?>"></div>
    <div class="col-md-4"><label class="form-label">Payment method</label><select name="payment_method" class="form-select"><?php foreach (['cash','cheque','bank_transfer','card'] as $m): ?><option value="<?= $m ?>" <?= old('payment_method',$payment['payment_method']??'cash')===$m?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$m)) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select"><?php foreach (['pending','paid','partial','overdue','cancelled','postponed'] as $s): ?><option value="<?= $s ?>" <?= old('status',$payment['status']??'pending')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-4"><label class="form-label">Payment date</label><input type="date" name="payment_date" class="form-control" value="<?= esc(old('payment_date',$payment['payment_date']??'')) ?>"></div>
    <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"><?= esc(old('notes',$payment['notes']??'')) ?></textarea></div>
  </div>
  <div class="mt-3"><button class="btn btn-fm-primary"><?= $isEdit ? 'Update' : 'Save' ?></button></div>
</form></div>
<?= $this->endSection() ?>