<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1>Petty Cash Audit Log</h1></div>
<div class="fm-card"><div class="fm-card-body p-0">
<table class="fm-table"><thead><tr><th>When</th><th>User</th><th>Action</th><th>Module</th><th>Reason</th></tr></thead><tbody>
<?php foreach ($logs as $l): ?>
<tr><td class="small"><?= date('d M Y H:i', strtotime($l['created_at'])) ?></td><td class="small">#<?= (int)$l['user_id'] ?></td><td class="small"><?= esc($l['action']) ?></td><td class="small"><?= esc($l['module']) ?></td><td class="small text-muted"><?= esc($l['reason'] ?? '') ?></td></tr>
<?php endforeach; ?>
</tbody></table>
</div></div>
<?= $this->endSection() ?>
