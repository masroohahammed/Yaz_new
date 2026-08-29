<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1>New Transfer</h1></div>
<form method="post" action="<?= base_url('finance-bank/transfers/store') ?>"><?= csrf_field() ?>
<div class="fm-card"><div class="fm-card-body row g-3">
  <div class="col-md-4"><label class="form-label">Date</label><input type="date" name="transfer_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
  <div class="col-md-4"><label class="form-label">Amount *</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
  <div class="col-md-4"><label class="form-label">Transfer Fee</label><input type="number" step="0.01" name="transfer_fee" class="form-control" value="0"></div>
  <div class="col-md-3"><label class="form-label">From Type</label><select name="from_account_type" class="form-select"><option value="bank">Bank</option><option value="cash">Cash</option></select></div>
  <div class="col-md-3"><label class="form-label">From Account ID</label><input type="number" name="from_account_id" class="form-control" required placeholder="Account ID"></div>
  <div class="col-md-3"><label class="form-label">To Type</label><select name="to_account_type" class="form-select"><option value="bank">Bank</option><option value="cash">Cash</option></select></div>
  <div class="col-md-3"><label class="form-label">To Account ID</label><input type="number" name="to_account_id" class="form-control" required placeholder="Account ID"></div>
  <div class="col-12"><label class="form-label">Purpose</label><input name="purpose" class="form-control"></div>
  <div class="col-12"><small class="text-muted">Transfers do not count as income or expense — they only move balances between accounts.</small></div>
</div></div>
<button class="btn btn-fm-primary">Save Draft</button>
</form>
<?= $this->endSection() ?>
