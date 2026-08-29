<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1><i class="bi bi-check2-square me-2"></i>Pending Approvals</h1></div>
<div class="fm-card"><div class="fm-card-body p-0">
<?php if (empty($pending)): ?><p class="text-center py-4 text-muted">No pending approvals.</p><?php else: ?>
<table class="fm-table"><thead><tr><th>Type</th><th>Number</th><th>Date</th><th>Amount</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($pending as $p): ?>
<tr>
  <td class="small"><?= esc(ucfirst($p['ref_type'])) ?></td>
  <td class="small font-monospace"><?= esc($p['number']) ?></td>
  <td class="small"><?= date('d M Y', strtotime($p['date'])) ?></td>
  <td class="small fw-bold"><?= number_format((float)$p['amount'],2) ?></td>
  <td><span class="fm-badge badge-status-pending"><?= ucwords(str_replace('_',' ',$p['status'])) ?></span></td>
  <td>
    <form method="post" action="<?= base_url('finance-bank/'.$p['ref_type'].'s/approve/'.$p['ref_id']) ?>" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-success">Approve</button></form>
    <a href="<?= base_url('finance-bank/'.$p['ref_type'].'s') ?>" class="btn btn-sm btn-outline-secondary">View list</a>
  </td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>
</div></div>
<?= $this->endSection() ?>
