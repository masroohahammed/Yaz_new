<?= $this->extend('layouts/hr') ?>
<?= $this->section('hr_content') ?>
<div class="page-header"><div><h1><i class="bi bi-inbox me-2 text-primary"></i>HR Requests</h1></div>
<a href="<?= base_url('hr/approvals') ?>" class="btn btn-fm-outline btn-sm">Approvals</a></div>
<?php if (!empty($migrationRequired)): ?><div class="alert alert-warning">Run consolidated SQL patch.</div>
<?php else: ?>
<div class="fm-card"><div class="fm-card-body p-0"><table class="fm-table"><thead><tr><th>Request</th><th>Employee</th><th>Module</th><th>Status</th><th>Submitted</th></tr></thead><tbody>
<?php foreach ($requests as $r): ?>
<tr><td class="small fw-semibold"><?= esc($r['request_number']) ?> — <?= esc($r['title']) ?></td><td><?= esc($r['employee_name']) ?></td><td><?= esc($r['module']) ?></td><td><?= esc($r['status']) ?></td><td class="small"><?= !empty($r['submitted_at']) ? date('d M Y', strtotime($r['submitted_at'])) : '—' ?></td></tr>
<?php endforeach; ?>
<?php if (empty($requests)): ?><tr><td colspan="5" class="text-center py-3 text-muted">No pending requests.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php endif; ?>
<?= $this->endSection() ?>
