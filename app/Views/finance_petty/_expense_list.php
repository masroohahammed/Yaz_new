<?php /** Petty cash expense/advance list with workflow actions */ ?>
<div class="page-header d-flex justify-content-between align-items-center">
  <h1><i class="bi <?= esc($icon ?? 'bi-file') ?> me-2"></i><?= esc($title ?? 'Records') ?></h1>
  <?php if (! empty($canCreate)): ?><a href="<?= base_url($createUrl ?? '#') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>New</a><?php endif; ?>
</div>
<div class="fm-card"><div class="fm-card-body p-0">
<?php if (empty($items)): ?><p class="text-center py-4 text-muted">No records.</p><?php else: ?>
<table class="fm-table"><thead><tr><th>Number</th><th>Date</th><th>Account</th><th>Amount</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($items as $row):
  $num = $row['expense_number'] ?? $row['advance_number'] ?? $row['replenishment_number'] ?? '';
  $date = $row['expense_date'] ?? $row['created_at'] ?? '';
  $type = isset($row['expense_number']) ? 'expense' : (isset($row['advance_number']) ? 'advance' : 'replenishment');
?>
<tr>
  <td class="small font-monospace fw-semibold"><?= esc($num) ?></td>
  <td class="small"><?= $date ? date('d M Y', strtotime($date)) : '—' ?></td>
  <td class="small text-muted"><?= esc($row['account_name'] ?? '—') ?></td>
  <td class="small fw-bold"><?= number_format((float)($row['amount'] ?? 0),2) ?></td>
  <td><span class="fm-badge badge-status-<?= esc(str_replace('_','-',$row['status'])) ?>"><?= ucwords(str_replace('_',' ',$row['status'])) ?></span></td>
  <td class="text-nowrap">
    <?php if ($type === 'expense'): ?>
      <?php if ($row['status'] === 'draft'): ?><form method="post" action="<?= base_url('finance-petty/expenses/submit/'.$row['id']) ?>" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-outline-primary">Submit</button></form><?php endif; ?>
      <?php if ($row['status'] === 'pending_approval'): ?><form method="post" action="<?= base_url('finance-petty/expenses/approve/'.$row['id']) ?>" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-success">Approve</button></form><?php endif; ?>
      <?php if ($row['status'] === 'approved'): ?><form method="post" action="<?= base_url('finance-petty/expenses/post/'.$row['id']) ?>" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-fm-primary" onclick="return confirm('Post to ledger?')">Post</button></form><?php endif; ?>
    <?php elseif ($type === 'advance'): ?>
      <?php if (in_array($row['status'], ['requested','pending_approval'], true)): ?><form method="post" action="<?= base_url('finance-petty/advances/approve/'.$row['id']) ?>" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-success">Approve</button></form><?php endif; ?>
      <?php if ($row['status'] === 'approved'): ?><form method="post" action="<?= base_url('finance-petty/advances/issue/'.$row['id']) ?>" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-fm-primary">Issue</button></form><?php endif; ?>
      <?php if (in_array($row['status'], ['issued','outstanding'], true)): ?><a href="<?= base_url('finance-petty/advances/settle/'.$row['id']) ?>" class="btn btn-sm btn-outline-secondary">Settle</a><?php endif; ?>
    <?php endif; ?>
  </td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>
</div></div>
