<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1><i class="bi bi-journal-bookmark me-2"></i>Finance Audit Log</h1></div>
<div class="fm-card"><div class="fm-card-body p-0">
<?php if (empty($logs)): ?><p class="text-center py-4 text-muted">No audit entries.</p><?php else: ?>
<table class="fm-table"><thead><tr><th>When</th><th>User</th><th>Role</th><th>Action</th><th>Module</th><th>Reason</th></tr></thead><tbody>
<?php foreach ($logs as $l): ?>
<tr>
  <td class="small text-muted"><?= date('d M Y H:i', strtotime($l['created_at'])) ?></td>
  <td class="small">#<?= (int)$l['user_id'] ?></td>
  <td class="small"><?= esc($l['user_role'] ?? '') ?></td>
  <td class="small fw-semibold"><?= esc($l['action']) ?></td>
  <td class="small"><?= esc($l['module']) ?> #<?= (int)($l['record_id']??0) ?></td>
  <td class="small text-muted"><?= esc($l['reason'] ?? '') ?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>
</div></div>
<?= $this->endSection() ?>
