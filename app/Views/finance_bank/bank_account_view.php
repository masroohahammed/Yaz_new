<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center">
  <div><h1><?= esc($account['name']) ?></h1><p class="text-muted small mb-0"><?= esc($account['bank_name'] ?? '') ?> · <?= esc($account['account_number_display']) ?></p></div>
  <div class="d-flex gap-2">
    <?php if ($canEdit): ?><a href="<?= base_url('finance-bank/bank-accounts/edit/'.$account['id']) ?>" class="btn btn-outline-secondary btn-sm">Edit</a><?php endif; ?>
    <?php if ($canClose && ($account['status']??'') !== 'closed'): ?>
    <form method="post" action="<?= base_url('finance-bank/bank-accounts/close/'.$account['id']) ?>" onsubmit="return confirm('Close this account?')"><?= csrf_field() ?><button class="btn btn-outline-danger btn-sm">Close</button></form>
    <?php endif; ?>
  </div>
</div>
<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="fm-card p-3"><div class="small text-muted">Opening Balance</div><div class="fw-bold"><?= $currency ?> <?= number_format((float)$account['opening_balance'],2) ?></div></div></div>
  <div class="col-md-3"><div class="fm-card p-3"><div class="small text-muted">Current Balance</div><div class="fw-bold"><?= $currency ?> <?= number_format((float)$account['current_balance'],2) ?></div></div></div>
  <div class="col-md-3"><div class="fm-card p-3"><div class="small text-muted">Available</div><div class="fw-bold"><?= $currency ?> <?= number_format((float)$account['available_balance'],2) ?></div></div></div>
  <div class="col-md-3"><div class="fm-card p-3"><div class="small text-muted">Last Transaction</div><div class="fw-bold"><?= $account['last_transaction_date'] ? date('d M Y', strtotime($account['last_transaction_date'])) : '—' ?></div></div></div>
</div>
<div class="fm-card"><div class="fm-card-header"><h5 class="mb-0">Recent Transactions</h5></div><div class="fm-card-body p-0">
  <?php if (empty($txs)): ?><p class="text-center py-4 text-muted mb-0">No posted transactions.</p><?php else: ?>
  <table class="fm-table"><thead><tr><th>Date</th><th>Number</th><th>Type</th><th>Debit</th><th>Credit</th><th>Balance</th></tr></thead><tbody>
  <?php foreach ($txs as $t): ?>
  <tr>
    <td class="small"><?= date('d M Y', strtotime($t['transaction_date'])) ?></td>
    <td class="small font-monospace"><?= esc($t['transaction_number']) ?></td>
    <td class="small"><?= esc(str_replace('_',' ',$t['transaction_type'])) ?></td>
    <td class="small text-danger"><?= (float)$t['debit'] ? number_format((float)$t['debit'],2) : '—' ?></td>
    <td class="small text-success"><?= (float)$t['credit'] ? number_format((float)$t['credit'],2) : '—' ?></td>
    <td class="small fw-bold"><?= number_format((float)$t['balance_after'],2) ?></td>
  </tr>
  <?php endforeach; ?>
  </tbody></table>
  <?php endif; ?>
</div></div>
<?= $this->endSection() ?>
