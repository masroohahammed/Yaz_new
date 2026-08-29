<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1>New Deposit</h1></div>
<form method="post" action="<?= base_url('finance-bank/deposits/store') ?>"><?= csrf_field() ?>
<div class="fm-card"><div class="fm-card-body row g-3">
  <div class="col-md-4"><label class="form-label">Date *</label><input type="date" name="deposit_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
  <div class="col-md-4"><label class="form-label">Bank Account *</label><select name="bank_account_id" class="form-select" required><option value="">Select…</option><?php foreach ($banks as $b): ?><option value="<?= $b['id'] ?>"><?= esc($b['name'].' — '.($b['bank_name']??'')) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-4"><label class="form-label">Amount *</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
  <div class="col-md-4"><label class="form-label">Currency</label><input name="currency" class="form-control" value="<?= esc($currency) ?>"></div>
  <div class="col-md-4"><label class="form-label">Deposit Source</label><input name="deposit_source" class="form-control" placeholder="Client Payment, Rental Income…"></div>
  <div class="col-md-4"><label class="form-label">Category</label><select name="category_id" class="form-select"><option value="">—</option><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>"><?= esc($c['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-6"><label class="form-label">Property</label><select name="facility_id" class="form-select"><option value="">—</option><?php foreach ($facilities as $f): ?><option value="<?= $f['id'] ?>"><?= esc($f['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-6"><label class="form-label">Reference</label><input name="reference_number" class="form-control"></div>
  <div class="col-12"><label class="form-label">Description</label><input name="description" class="form-control"></div>
  <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
</div></div>
<button class="btn btn-fm-primary">Save Draft</button>
</form>
<?= $this->endSection() ?>
