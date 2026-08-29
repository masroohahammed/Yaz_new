<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= $this->include('finance/_page_header', ['title' => 'Budget Management', 'backUrl' => 'finance']) ?>
<div class="d-flex justify-content-between mb-3">
  <p class="small text-muted mb-0">Annual budgets with category lines. Compare to expenses and procurement on reports.</p>
  <a href="<?= base_url('finance/budgets/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New budget</a>
</div>
<?php if (empty($budgets)): ?>
<div class="alert alert-info">No budgets yet. <a href="<?= base_url('finance/budgets/create') ?>">Create your first budget</a>.</div>
<?php else: ?>
<div class="fm-card"><div class="fm-card-body p-0">
<table class="fm-table"><thead><tr><th>Name</th><th>Year</th><th class="text-end">Total</th><th>Status</th></tr></thead>
<tbody><?php foreach ($budgets as $b): ?>
<tr><td class="fw-semibold"><?= esc($b['name']) ?></td><td><?= esc($b['fiscal_year']) ?></td><td class="text-end"><?= number_format((float)$b['total_amount'],2) ?></td><td><span class="fm-badge"><?= esc($b['status']) ?></span></td></tr>
<?php endforeach; ?></tbody></table>
</div></div>
<?php endif; ?>
<?= $this->endSection() ?>
