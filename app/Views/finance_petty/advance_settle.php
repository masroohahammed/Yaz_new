<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1>Settle Advance <?= esc($advance['advance_number']) ?></h1></div>
<div class="alert alert-info">Issued: <?= $currency ?> <?= number_format((float)$advance['issued_amount'],2) ?></div>
<form method="post"><?= csrf_field() ?>
<div class="fm-card"><div class="fm-card-body row g-3">
  <div class="col-md-4"><label class="form-label">Actual Expense</label><input type="number" step="0.01" name="expense_amount" class="form-control" required></div>
  <div class="col-md-4"><label class="form-label">Cash Returned</label><input type="number" step="0.01" name="return_amount" class="form-control" value="0"></div>
  <div class="col-md-4"><label class="form-label">Additional Payment (if over)</label><input type="number" step="0.01" name="additional_payment" class="form-control" value="0"></div>
  <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
  <div class="col-12"><small class="text-muted">Expense + Return − Additional Payment must equal issued amount (<?= number_format((float)$advance['issued_amount'],2) ?>).</small></div>
</div></div>
<button class="btn btn-fm-primary">Complete Settlement</button>
</form>
<?= $this->endSection() ?>
