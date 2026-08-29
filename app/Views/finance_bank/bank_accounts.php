<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
  <h1><i class="bi bi-bank me-2"></i>Bank Accounts</h1>
  <?php if ($canCreate): ?><a href="<?= base_url('finance-bank/bank-accounts/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>Add Bank Account</a><?php endif; ?>
</div>
<div class="fm-card"><div class="fm-card-body p-0">
  <?php if (empty($accounts)): ?><p class="text-center py-4 text-muted">No bank accounts yet.</p><?php else: ?>
  <table class="fm-table">
    <thead><tr><th>Account</th><th>Bank</th><th>Number</th><th>Type</th><th>Currency</th><th>Balance</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($accounts as $a): ?>
    <tr>
      <td class="fw-semibold small"><?= esc($a['name']) ?></td>
      <td class="small"><?= esc($a['bank_name'] ?? '—') ?></td>
      <td class="small font-monospace"><?= esc($a['account_number_display']) ?></td>
      <td class="small"><?= esc(ucfirst($a['account_type'] ?? 'current')) ?></td>
      <td class="small"><?= esc($a['currency']) ?></td>
      <td class="small fw-bold"><?= number_format((float)$a['current_balance'],2) ?></td>
      <td><span class="fm-badge badge-status-<?= esc($a['status'] ?? 'active') ?>"><?= ucfirst($a['status'] ?? 'active') ?></span></td>
      <td><a href="<?= base_url('finance-bank/bank-accounts/view/'.$a['id']) ?>" class="btn btn-sm btn-outline-secondary">Open</a></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div></div>
<?= $this->endSection() ?>
