<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-calculator me-2 text-primary"></i>Maintenance Costing</h1></div>
  <a href="<?= base_url('costing/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Log Cost</a>
</div>
<div class="row g-3 mb-3">
  <div class="col-md-3"><div class="kpi-card kpi-blue"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-person-gear"></i></div><div><div class="kpi-label">Total Labor</div><div class="kpi-value"><?= $currency ?> <?= number_format($totals['labor'],0) ?></div></div></div></div></div>
  <div class="col-md-3"><div class="kpi-card kpi-orange"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-gear"></i></div><div><div class="kpi-label">Spare Parts</div><div class="kpi-value"><?= $currency ?> <?= number_format($totals['parts'],0) ?></div></div></div></div></div>
  <div class="col-md-3"><div class="kpi-card kpi-teal"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-truck"></i></div><div><div class="kpi-label">Vendor Cost</div><div class="kpi-value"><?= $currency ?> <?= number_format($totals['vendor'],0) ?></div></div></div></div></div>
  <div class="col-md-3"><div class="kpi-card kpi-red"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-lightning"></i></div><div><div class="kpi-label">Emergency Surcharge</div><div class="kpi-value"><?= $currency ?> <?= number_format($totals['emergency'],0) ?></div></div></div></div></div>
</div>
<div class="fm-card"><div class="fm-card-body p-0"><div class="table-responsive">
<table class="fm-table"><thead><tr><th>Work Order</th><th>Facility</th><th>Labor</th><th>Parts</th><th>Vendor</th><th>Surcharge</th><th>Total</th><th>Estimate</th><th>Job Profit</th><th>Actions</th></tr></thead><tbody>
<?php foreach($costings as $c): ?>
<tr>
  <td class="fw-semibold small"><?= esc($c['wo_number']) ?></td>
  <td class="small"><?= esc($c['facility_name']) ?></td>
  <td class="small"><?= number_format($c['labor_cost'],2) ?></td>
  <td class="small"><?= number_format($c['parts_cost'],2) ?></td>
  <td class="small"><?= number_format($c['vendor_cost'],2) ?></td>
  <td class="small"><?= number_format($c['emergency_surcharge'],2) ?></td>
  <td class="fw-semibold"><?= $currency ?> <?= number_format($c['total_cost'],2) ?></td>
  <td class="small text-muted"><?= $c['cost_estimate'] ? $currency.' '.number_format($c['cost_estimate'],2) : '—' ?></td>
  <td><span class="fw-semibold <?= $c['job_profit']>=0?'text-success':'text-danger' ?>"><?= $currency ?> <?= number_format($c['job_profit'],2) ?></span></td>
  <td><a href="<?= base_url('costing/view/'.$c['id']) ?>" class="btn-action bg-primary bg-opacity-10 text-primary"><i class="bi bi-eye"></i></a></td>
</tr>
<?php endforeach; ?>
<?php if(empty($costings)): ?><tr><td colspan="10" class="text-center py-4 text-muted">No costing records yet</td></tr><?php endif; ?>
</tbody></table></div></div></div>
<?= $this->endSection() ?>
