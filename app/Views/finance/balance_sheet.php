<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $t = $sheet['totals'] ?? []; ?>
<?= $this->include('finance/_page_header', ['title' => 'Balance Sheet', 'backUrl' => 'finance/reports']) ?>
<?= form_open(base_url('finance/balance-sheet'), ['method' => 'get', 'class' => 'row g-2 align-items-end mb-3']) ?>
<div class="col-auto"><label class="small">As of</label><input type="date" name="as_of" class="form-control form-control-sm" value="<?= esc($asOf) ?>"></div>
<div class="col-auto"><button class="btn btn-fm-primary btn-sm">Run</button></div>
<?= form_close() ?>
<?php if (empty($glEnabled)): ?>
<div class="alert alert-warning">GL tables required — run finance ERP migration.</div>
<?php else: ?>
<div class="row g-3">
  <div class="col-md-6">
    <div class="fm-card"><div class="card-header-fm"><h5>Assets</h5></div><div class="fm-card-body p-0">
      <table class="table table-sm mb-0"><?php foreach ($sheet['assets'] as $r): ?><tr><td><?= esc($r['code']) ?> <?= esc($r['name']) ?></td><td class="text-end"><?= number_format($r['balance'], 2) ?></td></tr><?php endforeach; ?>
      <tr class="fw-bold"><td>Total assets</td><td class="text-end"><?= number_format($t['assets'] ?? 0, 2) ?></td></tr></table>
    </div></div>
  </div>
  <div class="col-md-6">
    <div class="fm-card mb-3"><div class="card-header-fm"><h5>Liabilities</h5></div><div class="fm-card-body p-0">
      <table class="table table-sm mb-0"><?php foreach ($sheet['liabilities'] as $r): ?><tr><td><?= esc($r['name']) ?></td><td class="text-end"><?= number_format($r['balance'], 2) ?></td></tr><?php endforeach; ?>
      <tr class="fw-bold"><td>Total liabilities</td><td class="text-end"><?= number_format($t['liabilities'] ?? 0, 2) ?></td></tr></table>
    </div></div>
    <div class="fm-card"><div class="card-header-fm"><h5>Equity + Net income</h5></div><div class="fm-card-body">
      <p class="mb-1 small">Equity accounts: <strong><?= number_format($t['equity'] ?? 0, 2) ?></strong></p>
      <p class="mb-1 small">Net income (YTD): <strong><?= number_format($t['net_income'] ?? 0, 2) ?></strong></p>
      <p class="mb-0 fw-bold">Liabilities + Equity: <?= number_format($t['liabilities_eq'] ?? 0, 2) ?></p>
    </div></div>
  </div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>
