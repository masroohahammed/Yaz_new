<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1>Replenish Petty Cash</h1></div>
<form method="post" action="<?= base_url('finance-petty/replenishments/store') ?>"><?= csrf_field() ?>
<div class="fm-card"><div class="fm-card-body row g-3">
  <div class="col-md-6"><label class="form-label">Petty Cash Account</label><select name="petty_account_id" class="form-select" required><?php foreach ($accounts as $a): ?><option value="<?= $a['id'] ?>"><?= esc($a['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-3"><label class="form-label">Amount</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
  <div class="col-md-3"><label class="form-label">Date</label><input type="date" name="replenishment_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
  <div class="col-md-4"><label class="form-label">Source Type</label><select name="source_account_type" class="form-select"><option value="bank">Bank</option><option value="cash">Cash</option></select></div>
  <div class="col-md-4"><label class="form-label">Source Account ID</label><input type="number" name="source_account_id" class="form-control" required placeholder="Bank or cash account ID"></div>
  <div class="col-12"><small class="text-muted">Replenishment posts as transfer — not income or expense.</small></div>
</div></div>
<button class="btn btn-fm-primary">Request Replenishment</button>
</form>
<?= $this->endSection() ?>
