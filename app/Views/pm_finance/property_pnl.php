<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1>Property P&L — <?= esc($property['name'] ?? '') ?></h1></div>
<div class="row g-3 mb-3">
  <div class="col-md-4"><div class="kpi-card kpi-green"><div class="kpi-label">Income</div><div class="kpi-value"><?= number_format($pnl['income'],0) ?></div></div></div>
  <div class="col-md-4"><div class="kpi-card kpi-red"><div class="kpi-label">Expense</div><div class="kpi-value"><?= number_format($pnl['expense'],0) ?></div></div></div>
  <div class="col-md-4"><div class="kpi-card"><div class="kpi-label">Net</div><div class="kpi-value"><?= number_format($pnl['net'],0) ?></div></div></div>
</div>
<?= $this->endSection() ?>
