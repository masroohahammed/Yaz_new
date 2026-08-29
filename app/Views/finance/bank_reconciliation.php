<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= $this->include('finance/_page_header', ['title' => 'Bank Reconciliation', 'subtitle' => 'Customer receipts vs vendor payments', 'backUrl' => 'finance/reports']) ?>
<?= form_open(base_url('finance/bank-reconciliation'), ['method' => 'get', 'class' => 'row g-2 align-items-end mb-3']) ?>
<div class="col-auto"><label class="small">From</label><input type="date" name="from" class="form-control form-control-sm" value="<?= esc($from) ?>"></div>
<div class="col-auto"><label class="small">To</label><input type="date" name="to" class="form-control form-control-sm" value="<?= esc($to) ?>"></div>
<div class="col-auto"><button class="btn btn-fm-primary btn-sm">Filter</button></div>
<?= form_close() ?>
<div class="row g-3 mb-3">
  <div class="col-md-4"><div class="fm-card p-3"><div class="small text-muted">Money in</div><div class="fs-5 text-success fw-bold"><?= $currency ?> <?= number_format($activity['total_in'] ?? 0, 2) ?></div></div></div>
  <div class="col-md-4"><div class="fm-card p-3"><div class="small text-muted">Money out</div><div class="fs-5 text-danger fw-bold"><?= $currency ?> <?= number_format($activity['total_out'] ?? 0, 2) ?></div></div></div>
  <div class="col-md-4"><div class="fm-card p-3"><div class="small text-muted">Net</div><div class="fs-5 fw-bold"><?= $currency ?> <?= number_format(($activity['total_in'] ?? 0) - ($activity['total_out'] ?? 0), 2) ?></div></div></div>
</div>
<div class="fm-card"><div class="fm-card-body p-0">
<table class="fm-table"><thead><tr><th>Date</th><th>Type</th><th>Reference</th><th>Method</th><th class="text-end">Amount</th></tr></thead>
<tbody>
<?php foreach ($activity['payments'] ?? [] as $p): ?>
<tr>
  <td class="small"><?= esc($p['date']) ?></td>
  <td><span class="fm-badge <?= $p['type'] === 'in' ? 'badge-status-completed' : 'badge-status-cancelled' ?>"><?= $p['type'] === 'in' ? 'In' : 'Out' ?></span></td>
  <td><?= esc($p['ref']) ?></td><td class="small"><?= esc($p['method']) ?></td>
  <td class="text-end"><?= number_format((float)$p['amount'], 2) ?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div></div>
<?= $this->endSection() ?>
