<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<h1>Compare Inspections</h1>
<p class="small"><?= esc($left['type']) ?> (<?= esc($left['inspection_date'] ?? $left['created_at']) ?>) vs <?= esc($right['type']) ?> (<?= esc($right['inspection_date'] ?? $right['created_at']) ?>)</p>
<?php if (empty($diff)): ?><p class="text-muted">No significant discrepancies flagged.</p>
<?php else: ?>
<table class="table table-sm"><thead><tr><th>Area</th><th>Before</th><th>After</th></tr></thead>
<tbody><?php foreach ($diff as $d): ?><tr class="table-warning">
  <td><?= esc($d['area']) ?></td><td><?= esc($d['before']) ?></td><td><?= esc($d['after']) ?></td>
</tr><?php endforeach; ?></tbody></table>
<?php endif; ?>
<?= $this->endSection() ?>
