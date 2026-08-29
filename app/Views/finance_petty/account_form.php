<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1>New Petty Cash Account</h1></div>
<form method="post" action="<?= base_url('finance-petty/accounts/store') ?>"><?= csrf_field() ?>
<div class="fm-card"><div class="fm-card-body row g-3">
  <div class="col-md-6"><label class="form-label">Account Name *</label><input name="name" class="form-control" required></div>
  <div class="col-md-6"><label class="form-label">Account Code</label><input name="account_code" class="form-control" placeholder="Auto-generated if blank"></div>
  <div class="col-md-6"><label class="form-label">Custodian</label><select name="custodian_user_id" class="form-select"><option value="">—</option><?php foreach ($users as $u): ?><option value="<?= $u['id'] ?>"><?= esc($u['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-3"><label class="form-label">Opening Balance</label><input type="number" step="0.01" name="opening_balance" class="form-control" value="0"></div>
  <div class="col-md-3"><label class="form-label">Opening Date</label><input type="date" name="opening_balance_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
  <div class="col-md-4"><label class="form-label">Max Cash Limit</label><input type="number" step="0.01" name="max_cash_limit" class="form-control"></div>
  <div class="col-md-4"><label class="form-label">Replenishment Level</label><input type="number" step="0.01" name="replenishment_level" class="form-control"></div>
  <div class="col-md-4"><label class="form-label">Currency</label><input name="currency" class="form-control" value="<?= esc($currency) ?>"></div>
  <div class="col-md-6"><label class="form-label">Branch</label><select name="branch_id" class="form-select"><option value="">—</option><?php foreach ($branches as $b): ?><option value="<?= $b['id'] ?>"><?= esc($b['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-6"><label class="form-label">Property</label><select name="facility_id" class="form-select"><option value="">—</option><?php foreach ($facilities as $f): ?><option value="<?= $f['id'] ?>"><?= esc($f['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
</div></div>
<button class="btn btn-fm-primary">Create Account</button>
</form>
<?= $this->endSection() ?>
