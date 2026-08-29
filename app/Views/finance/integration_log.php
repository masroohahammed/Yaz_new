<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1><i class="bi bi-link-45deg me-2"></i>Finance Integration Log</h1></div>
<div class="fm-card"><div class="fm-card-body p-0">
<table class="table table-sm mb-0">
<thead><tr><th>Time</th><th>Module</th><th>Event</th><th>Source</th><th>Target</th></tr></thead>
<tbody>
<?php foreach ($logs as $l): ?>
<tr>
  <td class="small text-nowrap"><?= esc($l['created_at'] ?? '') ?></td>
  <td><?= esc($l['module']) ?></td>
  <td><?= esc($l['event']) ?></td>
  <td class="small"><?= esc($l['source_type']) ?> #<?= (int)$l['source_id'] ?></td>
  <td class="small"><?= $l['target_type'] ? esc($l['target_type']).' #'.(int)$l['target_id'] : '—' ?></td>
</tr>
<?php endforeach; ?>
<?php if (empty($logs)): ?><tr><td colspan="5" class="text-center text-muted py-4">No integration events yet.</td></tr><?php endif; ?>
</tbody>
</table>
</div></div>
<?= $this->endSection() ?>
