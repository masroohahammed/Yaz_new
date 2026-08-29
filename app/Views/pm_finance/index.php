<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1>PM Finance</h1></div>
<div class="row g-3 mb-3">
  <div class="col-md-4"><div class="kpi-card kpi-green"><div class="kpi-label">Income (MTD)</div><div class="kpi-value"><?= number_format($income, 0) ?></div></div></div>
  <div class="col-md-4"><div class="kpi-card kpi-red"><div class="kpi-label">Expense (MTD)</div><div class="kpi-value"><?= number_format($expense, 0) ?></div></div></div>
  <div class="col-md-4"><div class="kpi-card kpi-primary"><div class="kpi-label">Net</div><div class="kpi-value"><?= number_format($net, 0) ?></div></div></div>
</div>
<div class="d-flex flex-wrap gap-2 mb-3">
  <a href="<?= base_url('finance/pm/ledger') ?>" class="btn btn-sm btn-fm-outline">Ledger</a>
  <a href="<?= base_url('finance/pm/cash-acknowledge') ?>" class="btn btn-sm btn-warning">Cash Acknowledge (<?= (int)$pendingAck ?>)</a>
  <a href="<?= base_url('finance/pm/collection-report') ?>" class="btn btn-sm btn-outline-secondary">Collection Report</a>
  <a href="<?= base_url('finance/pm/trial-balance') ?>" class="btn btn-sm btn-outline-secondary">Trial Balance</a>
  <a href="<?= base_url('finance/pm/owner-statement') ?>" class="btn btn-sm btn-outline-secondary">Owner Statement</a>
  <a href="<?= base_url('finance/pm/vat-report') ?>" class="btn btn-sm btn-outline-secondary">VAT Report</a>
  <a href="<?= base_url('finance/pm/aging') ?>" class="btn btn-sm btn-outline-secondary">Aging</a>
  <a href="<?= base_url('finance/pm/transaction') ?>" class="btn btn-sm btn-fm-primary">Record Transaction</a>
</div>
<?= $this->endSection() ?>
