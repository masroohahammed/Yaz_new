<?php
/** Shared document list partial */
?>
<div class="page-header d-flex justify-content-between align-items-center">
  <h1><i class="bi <?= esc($icon ?? 'bi-file') ?> me-2"></i><?= esc($title ?? 'Documents') ?></h1>
  <?php if (! empty($canCreate)): ?><a href="<?= base_url($createUrl ?? '#') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>New</a><?php endif; ?>
</div>
<div class="fm-card"><div class="fm-card-body p-0">
<?php if (empty($items)): ?><p class="text-center py-4 text-muted">No records.</p><?php else: ?>
<table class="fm-table"><thead><tr><th>Number</th><th>Date</th><?php if (! empty($extraCol)): ?><th>Account</th><?php endif; ?><th>Amount</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($items as $row): ?>
<tr>
  <td class="small font-monospace fw-semibold"><?= esc($row[$numberCol]) ?></td>
  <td class="small"><?= date('d M Y', strtotime($row[$dateCol])) ?></td>
  <?php if (! empty($extraCol)): ?><td class="small text-muted"><?= esc($row[$extraCol] ?? '—') ?></td><?php endif; ?>
  <td class="small fw-bold"><?= number_format((float)$row[$amountCol],2) ?></td>
  <td><span class="fm-badge badge-status-<?= esc(str_replace('_','-',$row['status'])) ?>"><?= ucwords(str_replace('_',' ',$row['status'])) ?></span></td>
  <td class="text-nowrap">
    <?php if (in_array($row['status'], ['draft'], true)): ?>
    <form method="post" action="<?= base_url('finance-bank/'.$type.'s/submit/'.$row['id']) ?>" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-outline-primary">Submit</button></form>
    <?php endif; ?>
    <?php if ($row['status'] === 'pending_approval'): ?>
    <form method="post" action="<?= base_url('finance-bank/'.$type.'s/approve/'.$row['id']) ?>" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-success" onclick="return confirm('Approve?')">Approve</button></form>
    <?php endif; ?>
    <?php if ($row['status'] === 'approved'): ?>
    <form method="post" action="<?= base_url('finance-bank/'.$type.'s/post/'.$row['id']) ?>" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-fm-primary" onclick="return confirm('Post to ledger?')">Post</button></form>
    <?php endif; ?>
    <a href="<?= base_url('finance-bank/voucher/'.$type.'/'.$row['id']) ?>" class="btn btn-sm btn-outline-secondary" target="_blank">Print</a>
  </td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>
</div></div>
