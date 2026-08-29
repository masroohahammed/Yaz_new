<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between"><h1><i class="bi bi-cash me-2"></i>Cash Accounts</h1>
<?php if ($canCreate): ?><a href="<?= base_url('finance-bank/cash-accounts/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>Add Cash Account</a><?php endif; ?></div>
<div class="fm-card"><div class="fm-card-body p-0">
<?php if (empty($accounts)): ?><p class="text-center py-4 text-muted">No cash accounts.</p><?php else: ?>
<table class="fm-table"><thead><tr><th>Name</th><th>Type</th><th>Branch</th><th>Property</th><th>Responsible</th><th>Balance</th><th>Status</th></tr></thead><tbody>
<?php foreach ($accounts as $a): ?>
<tr>
  <td class="fw-semibold small"><?= esc($a['name']) ?></td>
  <td class="small"><?= esc(ucfirst($a['account_type'])) ?></td>
  <td class="small text-muted"><?= esc($a['branch_label'] ?? '—') ?></td>
  <td class="small text-muted"><?= esc($a['facility_name'] ?? '—') ?></td>
  <td class="small"><?= esc($a['responsible_name'] ?? '—') ?></td>
  <td class="small fw-bold"><?= number_format((float)$a['current_balance'],2) ?> <?= esc($a['currency']) ?></td>
  <td><span class="fm-badge badge-status-<?= esc($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>
</div></div>
<?= $this->endSection() ?>
