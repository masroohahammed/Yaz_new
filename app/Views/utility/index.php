<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-lightning-charge me-2 text-warning"></i>Utility & Energy</h1><div class="small text-muted">Monitor electricity, water, and gas consumption</div></div>
  <a href="<?= base_url('utility/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Log Reading</a>
</div>
<div class="row g-3 mb-3">
  <div class="col-md-3"><div class="kpi-card kpi-blue"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-lightning"></i></div><div><div class="kpi-label">Electricity Cost</div><div class="kpi-value"><?= $currency ?> <?= number_format($elecTotal,0) ?></div></div></div></div></div>
  <div class="col-md-3"><div class="kpi-card kpi-teal"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-droplet"></i></div><div><div class="kpi-label">Water Cost</div><div class="kpi-value"><?= $currency ?> <?= number_format($waterTotal,0) ?></div></div></div></div></div>
  <div class="col-md-3"><div class="kpi-card kpi-green"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-graph-down"></i></div><div><div class="kpi-label">Total Utility Cost</div><div class="kpi-value"><?= $currency ?> <?= number_format($elecTotal+$waterTotal,0) ?></div></div></div></div></div>
  <div class="col-md-3"><div class="kpi-card kpi-orange"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-bar-chart"></i></div><div><div class="kpi-label">Readings This Period</div><div class="kpi-value"><?= count($utilities) ?></div></div></div></div></div>
</div>
<!-- Date filter -->
<div class="fm-card mb-3"><div class="fm-card-body">
<?= form_open('utility',['method'=>'get','class'=>'row g-2 align-items-end']) ?>
<div class="col-md-3"><label class="form-label small">From</label><input type="date" name="from" class="form-control form-control-sm" value="<?= $from ?>"></div>
<div class="col-md-3"><label class="form-label small">To</label><input type="date" name="to" class="form-control form-control-sm" value="<?= $to ?>"></div>
<div class="col-md-2"><button type="submit" class="btn btn-fm-primary btn-sm w-100">Filter</button></div>
<?= form_close() ?>
</div></div>
<div class="fm-card"><div class="fm-card-body p-0"><div class="table-responsive">
<table class="fm-table"><thead><tr><th>Date</th><th>Facility</th><th>Type</th><th>Units</th><th>Meter Reading</th><th>Cost (<?= $currency ?>)</th><th>Notes</th></tr></thead><tbody>
<?php foreach($utilities as $u): ?>
<tr>
  <td class="small"><?= date('d M Y', strtotime($u['reading_date'])) ?></td>
  <td class="small"><?= esc($u['facility_name']) ?></td>
  <td><span class="fm-badge badge-status-<?= $u['type']==='electricity'?'in_progress':($u['type']==='water'?'assigned':'new') ?>"><?= ucfirst($u['type']) ?></span></td>
  <td class="small"><?= number_format($u['units'],2) ?></td>
  <td class="small"><?= $u['meter_reading'] ? number_format($u['meter_reading'],2) : '—' ?></td>
  <td class="fw-semibold"><?= number_format($u['cost'],2) ?></td>
  <td class="small text-muted"><?= esc($u['notes']??'—') ?></td>
</tr>
<?php endforeach; ?>
<?php if(empty($utilities)): ?><tr><td colspan="7" class="text-center py-4 text-muted">No readings found for this period</td></tr><?php endif; ?>
</tbody></table></div></div></div>
<?= $this->endSection() ?>
