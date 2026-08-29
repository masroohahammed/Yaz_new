<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-calculator me-2"></i><?= esc($costing['wo_number']) ?> Costing</h1></div><a href="<?= base_url('costing') ?>" class="btn btn-fm-outline btn-sm">← Back</a></div>
<div class="row g-3">
<div class="col-md-6"><div class="fm-card"><div class="fm-card-body">
<h6 class="fw-semibold mb-3" style="color:var(--fm-navy)">Cost Breakdown</h6>
<?php $rows=[['Labor Cost',$costing['labor_cost']],['Labor Hours',$costing['labor_hours'].' hrs'],['Spare Parts',$costing['parts_cost']],['Vendor Cost',$costing['vendor_cost']],['Emergency Surcharge',$costing['emergency_surcharge']],['Total Cost',$costing['total_cost']],['Cost Estimate',$costing['cost_estimate']],['Job Profit',$costing['job_profit']]]; ?>
<?php foreach($rows as [$l,$v]): ?>
<div class="d-flex justify-content-between py-2 border-bottom border-light">
  <span class="text-muted small"><?= $l ?></span>
  <span class="small fw-semibold <?= $l==='Job Profit'?($v>=0?'text-success':'text-danger'):'' ?>"><?= is_numeric($v) ? $currency.' '.number_format($v,2) : esc($v) ?></span>
</div>
<?php endforeach; ?>
</div></div></div>
<div class="col-md-6"><div class="fm-card"><div class="fm-card-body">
<h6 class="fw-semibold mb-3" style="color:var(--fm-navy)">Work Order Details</h6>
<?php $rows2=[['WO Number',$costing['wo_number']],['Title',$costing['wo_title']],['Facility',$costing['facility_name']],['Type',ucfirst($costing['type'])],['Logged By',$costing['created_by_name']]]; ?>
<?php foreach($rows2 as [$l,$v]): ?>
<div class="d-flex justify-content-between py-2 border-bottom border-light"><span class="text-muted small"><?= $l ?></span><span class="small fw-semibold"><?= esc($v) ?></span></div>
<?php endforeach; ?>
<?php if($costing['notes']): ?><div class="mt-3 p-3 rounded" style="background:#f8fafc"><p class="small mb-0"><?= nl2br(esc($costing['notes'])) ?></p></div><?php endif; ?>
</div></div></div>
</div>
<?= $this->endSection() ?>
