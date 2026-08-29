<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $i = $inspection; ?>
<div class="page-header d-flex justify-content-between flex-wrap gap-2">
  <h1>Inspection — Unit <?= esc($i['unit_number']) ?></h1>
  <div class="d-flex gap-2">
    <a href="<?= base_url('pm-inspections/checklist/'.$i['id']) ?>" class="btn btn-sm btn-fm-primary">Save Checklist</a>
    <a href="<?= base_url('pm-inspections/link/'.$i['id']) ?>" class="btn btn-sm btn-outline-secondary">Link</a>
    <a href="<?= base_url('pm-inspections/print/'.$i['id']) ?>" class="btn btn-sm btn-outline-primary">Print Report</a>
  </div>
</div>
<p class="small text-muted"><?= esc(str_replace('_',' ',$i['type'])) ?> · <?= esc($i['property_name']) ?> · <?= esc($i['status']) ?></p>
<?php if (! empty($i['notes'])): ?><p><?= esc($i['notes']) ?></p><?php endif; ?>
<?= $this->endSection() ?>
