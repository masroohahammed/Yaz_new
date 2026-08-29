<?= $this->extend('layouts/hr') ?>
<?= $this->section('hr_content') ?>
<div class="page-header"><div><h1><i class="bi bi-check2-square me-2 text-primary"></i>HR Approvals</h1></div></div>
<?php if (!empty($migrationRequired)): ?><div class="alert alert-warning">Run consolidated SQL patch.</div>
<?php else: ?>
<?php if ($canTransfer ?? false): ?>
<div class="fm-card mb-3"><div class="card-header-fm"><h5>Pending Transfers</h5></div>
<div class="fm-card-body p-0"><table class="fm-table"><thead><tr><th>Employee</th><th>Effective</th><th>Reason</th><th></th></tr></thead><tbody>
<?php foreach ($transfers as $t): ?>
<tr><td><?= esc($t['employee_name']) ?> (<?= esc($t['emp_code']) ?>)</td><td class="small"><?= date('d M Y', strtotime($t['effective_date'])) ?></td><td class="small"><?= esc($t['reason'] ?? '') ?></td>
<td><?= form_open(base_url('hr/transfers/approve/'.$t['id']),['class'=>'d-inline']) ?><?= csrf_field() ?><button class="btn btn-sm btn-success">Approve</button><?= form_close() ?>
<?= form_open(base_url('hr/transfers/reject/'.$t['id']),['class'=>'d-inline']) ?><?= csrf_field() ?><button class="btn btn-sm btn-fm-outline">Reject</button><?= form_close() ?></td></tr>
<?php endforeach; ?>
<?php if (empty($transfers)): ?><tr><td colspan="4" class="text-center py-3 text-muted">No pending transfers.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php endif; ?>
<div class="fm-card"><div class="card-header-fm"><h5>All Pending Requests</h5></div>
<div class="fm-card-body p-0"><table class="fm-table"><thead><tr><th>Request</th><th>Module</th><th>Employee</th><th>Amount</th></tr></thead><tbody>
<?php foreach ($requests as $r): ?>
<tr><td><?= esc($r['request_number']) ?></td><td><?= esc($r['module']) ?></td><td><?= esc($r['employee_name']) ?></td><td><?= $r['amount'] !== null ? number_format((float)$r['amount'],2) : '—' ?></td></tr>
<?php endforeach; ?>
</tbody></table></div></div>
<?php endif; ?>
<?= $this->endSection() ?>
