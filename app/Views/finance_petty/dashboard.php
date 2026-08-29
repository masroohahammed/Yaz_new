<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div><h1><i class="bi bi-wallet2 me-2"></i>Petty Cash Dashboard</h1>
  <p class="text-muted small mb-0">Separate petty cash tracking integrated with the financial ledger</p></div>
  <?php if ($can ?? true): ?><a href="<?= base_url('finance-petty/accounts/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>New Account</a><?php endif; ?>
</div>

<div class="row g-3 mb-4">
  <?php
  $cards = [
    ['Total Petty Cash', $kpis['total_petty_cash'], 'bi-cash', 'finance-petty/accounts'],
    ['Active Accounts', $kpis['active_accounts'], 'bi-collection', 'finance-petty/accounts', true],
    ['Pending Replenishment', $kpis['pending_replenishment'], 'bi-arrow-repeat', 'finance-petty/replenishments', true],
    ['Pending Expenses', $kpis['pending_expenses'], 'bi-receipt', 'finance-petty/expenses', true],
    ['Pending Approvals', $kpis['pending_approvals'], 'bi-hourglass', 'finance-petty/advances', true],
    ['Outstanding Advances', $kpis['outstanding_advances'], 'bi-person-lines-fill', 'finance-petty/advances'],
    ['Cash Shortage', $kpis['cash_shortage'], 'bi-dash-circle', 'finance-petty/counts'],
    ['Cash Excess', $kpis['cash_excess'], 'bi-plus-circle', 'finance-petty/counts'],
  ];
  foreach ($cards as $card):
    [$label, $val, $icon, $link] = $card;
    $isCount = $card[4] ?? false;
  ?>
  <div class="col-6 col-md-4 col-xl-3">
    <a href="<?= base_url($link) ?>" class="text-decoration-none">
      <div class="fm-card p-3 h-100">
        <div class="small text-muted"><?= esc($label) ?></div>
        <div class="fs-5 fw-bold text-dark"><?= ($isCount ?? false) ? (int)$val : ($currency.' '.number_format((float)$val,2)) ?></div>
      </div>
    </a>
  </div>
  <?php endforeach; ?>
</div>

<div class="fm-card"><div class="fm-card-header"><h5 class="mb-0">Petty Cash Accounts</h5></div><div class="fm-card-body p-0">
<?php if (empty($accounts)): ?><p class="text-center py-4 text-muted mb-0">No petty cash accounts configured.</p><?php else: ?>
<table class="fm-table"><thead><tr>
  <th>Account</th><th>Custodian</th><th>Opening</th><th>Received</th><th>Spent</th><th>Balance</th><th>Physical</th><th>Diff</th><th></th>
</tr></thead><tbody>
<?php foreach ($accounts as $a):
  $phys = $a['physical_balance'] ?? null;
  $diff = $phys !== null ? (float)$phys - (float)$a['current_balance'] : null;
?>
<tr class="<?= !empty($a['needs_replenishment']) ? 'table-warning' : '' ?>">
  <td class="small fw-semibold"><?= esc($a['name']) ?><?php if (!empty($a['needs_replenishment'])): ?> <span class="fm-badge badge-status-pending">Replenish</span><?php endif; ?></td>
  <td class="small"><?= esc($a['custodian_name'] ?? '—') ?></td>
  <td class="small"><?= number_format((float)$a['opening_balance'],2) ?></td>
  <td class="small text-success"><?= number_format((float)($a['summary']['cash_received'] ?? 0),2) ?></td>
  <td class="small text-danger"><?= number_format((float)($a['summary']['expenses'] ?? 0),2) ?></td>
  <td class="small fw-bold"><?= number_format((float)$a['current_balance'],2) ?></td>
  <td class="small"><?= $phys !== null ? number_format((float)$phys,2) : '—' ?></td>
  <td class="small <?= $diff && abs($diff)>0.01 ? 'text-danger fw-bold' : '' ?>"><?= $diff !== null ? number_format($diff,2) : '—' ?></td>
  <td><a href="<?= base_url('finance-petty/accounts/view/'.$a['id']) ?>" class="btn btn-sm btn-outline-secondary">View</a></td>
</tr>
<?php endforeach; ?>
</tbody></table>
<?php endif; ?>
</div></div>
<?= $this->endSection() ?>
