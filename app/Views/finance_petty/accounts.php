<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between"><h1><i class="bi bi-wallet me-2"></i>Petty Cash Accounts</h1>
<a href="<?= base_url('finance-petty/accounts/create') ?>" class="btn btn-fm-primary btn-sm">Add Account</a></div>
<div class="fm-card"><div class="fm-card-body p-0">
<table class="fm-table"><thead><tr><th>Code</th><th>Name</th><th>Custodian</th><th>Branch</th><th>Property</th><th>Balance</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($accounts as $a): ?>
<tr>
  <td class="small font-monospace"><?= esc($a['account_code']) ?></td>
  <td class="fw-semibold small"><?= esc($a['name']) ?></td>
  <td class="small"><?= esc($a['custodian_name'] ?? '—') ?></td>
  <td class="small text-muted"><?= esc($a['branch_name'] ?? '—') ?></td>
  <td class="small text-muted"><?= esc($a['facility_name'] ?? '—') ?></td>
  <td class="small fw-bold"><?= number_format((float)$a['current_balance'],2) ?> <?= esc($a['currency']) ?></td>
  <td><span class="fm-badge badge-status-<?= esc($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
  <td><a href="<?= base_url('finance-petty/accounts/view/'.$a['id']) ?>" class="btn btn-sm btn-outline-secondary">Open</a></td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div></div>
<?= $this->endSection() ?>
