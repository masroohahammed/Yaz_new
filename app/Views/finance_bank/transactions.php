<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <h1><i class="bi bi-journal-text me-2"></i>Financial Transactions</h1>
  <form method="get" class="d-flex flex-wrap gap-2">
    <input type="date" name="date_from" class="form-control form-control-sm" value="<?= esc($filters['date_from'] ?? '') ?>" placeholder="From">
    <input type="date" name="date_to" class="form-control form-control-sm" value="<?= esc($filters['date_to'] ?? '') ?>" placeholder="To">
    <select name="transaction_type" class="form-select form-select-sm"><option value="">All types</option>
      <?php foreach (['deposit','withdrawal','income','expense','bank_transfer','opening_balance','payment','receipt'] as $tt): ?>
      <option value="<?= $tt ?>" <?= ($filters['transaction_type']??'')===$tt?'selected':'' ?>><?= ucwords(str_replace('_',' ',$tt)) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-sm btn-outline-secondary">Filter</button>
  </form>
</div>
<div class="fm-card"><div class="fm-card-body p-0">
<?php if (empty($transactions)): ?><p class="text-center py-4 text-muted">No transactions.</p><?php else: ?>
<table class="fm-table"><thead><tr><th>Date</th><th>Number</th><th>Account</th><th>Type</th><th>Debit</th><th>Credit</th><th>Balance</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($transactions as $t): ?>
<tr>
  <td class="small"><?= date('d M Y', strtotime($t['transaction_date'])) ?></td>
  <td class="small font-monospace"><?= esc($t['transaction_number']) ?></td>
  <td class="small"><?= esc(ucfirst($t['account_type']).' #'.$t['account_id']) ?></td>
  <td class="small"><?= esc(str_replace('_',' ',$t['transaction_type'])) ?></td>
  <td class="small"><?= (float)$t['debit'] ? number_format((float)$t['debit'],2) : '—' ?></td>
  <td class="small"><?= (float)$t['credit'] ? number_format((float)$t['credit'],2) : '—' ?></td>
  <td class="small fw-bold"><?= number_format((float)$t['balance_after'],2) ?></td>
  <td><span class="fm-badge badge-status-<?= esc(str_replace('_','-',$t['status'])) ?>"><?= ucfirst($t['status']) ?></span></td>
  <td>
    <?php if ($canReverse && $t['status']==='posted' && !(int)$t['is_reversal']): ?>
    <form method="post" action="<?= base_url('finance-bank/transactions/reverse/'.$t['id']) ?>" class="d-inline" onsubmit="return confirm('Reverse this transaction?')">
      <?= csrf_field() ?>
      <input type="hidden" name="reason" value="Manual reversal">
      <button class="btn btn-sm btn-outline-danger">Reverse</button>
    </form>
    <?php endif; ?>
  </td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>
</div></div>
<?= $this->endSection() ?>
