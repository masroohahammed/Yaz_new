<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-building me-2"></i>Property P&amp;L Reports</h1>
    <div class="small text-muted">Open profit and loss for each property in your portfolio</div>
  </div>
  <a href="<?= base_url('reports/pm') ?>" class="btn btn-fm-outline btn-sm">← Reports</a>
</div>
<div class="row g-3">
<?php foreach ($properties as $p): ?>
<div class="col-md-4">
  <a href="<?= base_url('finance/pm/property/'.$p['id']) ?>" class="text-decoration-none">
    <div class="fm-card h-100 p-3">
      <div class="fw-bold"><?= esc($p['name']) ?></div>
      <?php if (!empty($p['code'])): ?><div class="small text-muted"><?= esc($p['code']) ?></div><?php endif; ?>
      <div class="small text-primary mt-2">View P&amp;L →</div>
    </div>
  </a>
</div>
<?php endforeach; ?>
<?php if (empty($properties)): ?>
<div class="col-12"><div class="fm-card p-4 text-center text-muted">No properties in your scope.</div></div>
<?php endif; ?>
</div>
<?= $this->endSection() ?>
