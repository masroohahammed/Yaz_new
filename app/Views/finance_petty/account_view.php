<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between"><div><h1><?= esc($account['name']) ?></h1><p class="text-muted small mb-0">Custodian: <?= esc($account['custodian_name'] ?? 'Unassigned') ?></p></div></div>
<div class="row g-3 mb-3">
  <div class="col-md-3"><div class="fm-card p-3"><div class="small text-muted">System Balance</div><div class="fw-bold"><?= $currency ?> <?= number_format((float)$account['current_balance'],2) ?></div></div></div>
  <div class="col-md-3"><div class="fm-card p-3"><div class="small text-muted">Replenishment Level</div><div class="fw-bold"><?= $account['replenishment_level'] ? number_format((float)$account['replenishment_level'],2) : '—' ?></div></div></div>
  <div class="col-md-3"><div class="fm-card p-3"><div class="small text-muted">Expenses (posted)</div><div class="fw-bold"><?= number_format((float)($summary['expenses'] ?? 0),2) ?></div></div></div>
  <div class="col-md-3"><div class="fm-card p-3"><div class="small text-muted">Advances (posted)</div><div class="fw-bold"><?= number_format((float)($summary['advances'] ?? 0),2) ?></div></div></div>
</div>
<?php if ($canEdit): ?>
<div class="fm-card mb-3"><div class="fm-card-header"><h5 class="mb-0">Transfer Custodian</h5></div><div class="fm-card-body">
<form method="post" action="<?= base_url('finance-petty/accounts/custodian/'.$account['id']) ?>" class="row g-2"><?= csrf_field() ?>
  <div class="col-md-5"><select name="custodian_user_id" class="form-select" required><option value="">Select custodian…</option><?php foreach ($users as $u): ?><option value="<?= $u['id'] ?>" <?= (int)($account['custodian_user_id']??0)===(int)$u['id']?'selected':'' ?>><?= esc($u['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-5"><input name="reason" class="form-control" placeholder="Reason"></div>
  <div class="col-md-2"><button class="btn btn-outline-secondary w-100">Transfer</button></div>
</form></div></div>
<?php endif; ?>
<div class="fm-card"><div class="fm-card-header"><h5 class="mb-0">Ledger Transactions</h5></div><div class="fm-card-body p-0">
<table class="fm-table"><thead><tr><th>Date</th><th>Number</th><th>Type</th><th>Debit</th><th>Credit</th><th>Balance</th></tr></thead><tbody>
<?php foreach ($txs as $t): ?>
<tr>
  <td class="small"><?= date('d M Y', strtotime($t['transaction_date'])) ?></td>
  <td class="small font-monospace"><?= esc($t['transaction_number']) ?></td>
  <td class="small"><?= esc(str_replace('_',' ',$t['transaction_type'])) ?></td>
  <td class="small"><?= (float)$t['debit'] ? number_format((float)$t['debit'],2) : '—' ?></td>
  <td class="small"><?= (float)$t['credit'] ? number_format((float)$t['credit'],2) : '—' ?></td>
  <td class="small fw-bold"><?= number_format((float)$t['balance_after'],2) ?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div></div>
<?= $this->endSection() ?>
