<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<h1>Unit Utilities — <?= esc($unit['unit_number'] ?? '') ?></h1>
<p class="small text-muted"><?= esc($unit['facility_name'] ?? '') ?></p>
<?php foreach ($accounts as $acc): ?>
<div class="fm-card mb-2 p-3">
  <strong><?= esc($acc['utility_name']) ?></strong> — <?= esc($acc['billing_mode']) ?> (<?= esc($acc['paid_by'] ?? 'company') ?>)
  <a href="<?= base_url('utilities/view/'.$acc['id']) ?>" class="btn btn-sm btn-link">View</a>
</div>
<?php endforeach; ?>
<?php if (empty($accounts)): ?><p class="text-muted">No utility accounts for this unit.</p><?php endif; ?>
<?= $this->endSection() ?>
