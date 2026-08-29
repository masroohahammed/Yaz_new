<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header d-flex flex-wrap align-items-center justify-content-between gap-2">
  <div><h1><i class="bi bi-speedometer2 me-2"></i>Finance Dashboard</h1>
  <p class="text-muted small mb-0">Bank balances, cash flow, and pending approvals</p></div>
  <form method="get" class="d-flex flex-wrap gap-2 align-items-end">
    <div><label class="form-label small mb-0">From</label><input type="date" name="date_from" class="form-control form-control-sm" value="<?= esc($filters['date_from'] ?? date('Y-01-01')) ?>"></div>
    <div><label class="form-label small mb-0">To</label><input type="date" name="date_to" class="form-control form-control-sm" value="<?= esc($filters['date_to'] ?? date('Y-m-d')) ?>"></div>
    <button class="btn btn-fm-primary btn-sm">Apply</button>
  </form>
</div>

<div class="row g-3 mb-4">
  <?php
  $cards = [
    ['Total Bank Balance', $kpis['total_bank_balance'], 'bi-bank', 'finance-bank/bank-accounts'],
    ['Total Cash Balance', $kpis['total_cash_balance'], 'bi-cash', 'finance-bank/cash-accounts'],
    ['Total Petty Cash', $kpis['total_petty_balance'] ?? 0, 'bi-wallet2', 'finance-petty'],
    ['Overall Available', $kpis['total_available_balance'], 'bi-piggy-bank', 'finance-bank/transactions'],
    ['Total Income', $kpis['total_income'], 'bi-arrow-down-circle', 'finance-bank/transactions?transaction_type=income'],
    ['Total Expenses', $kpis['total_expense'], 'bi-arrow-up-circle', 'finance-bank/transactions?transaction_type=expense'],
    ['Net Balance', $kpis['net_balance'], 'bi-graph-up', 'finance-bank/reports'],
    ['Pending Deposits', $kpis['pending_deposits'], 'bi-inbox', 'finance-bank/deposits?status=pending_approval'],
    ['Pending Withdrawals', $kpis['pending_withdrawals'], 'bi-box-arrow-up', 'finance-bank/withdrawals?status=pending_approval'],
    ['Pending Approvals', $kpis['pending_approvals'], 'bi-hourglass-split', 'finance-bank/approvals'],
    ['Receivables', $kpis['receivables'], 'bi-receipt', 'finance/invoices'],
    ['Payables', $kpis['payables'], 'bi-truck', 'finance/vendor-bills'],
  ];
  foreach ($cards as [$label, $val, $icon, $link]):
    $isCount = in_array($label, ['Pending Deposits','Pending Withdrawals','Pending Approvals'], true);
  ?>
  <div class="col-6 col-md-4 col-xl-3">
    <a href="<?= base_url($link) ?>" class="text-decoration-none">
      <div class="fm-card h-100 p-3 cc-kpi-card">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <div class="small text-muted"><?= esc($label) ?></div>
            <div class="fs-5 fw-bold text-dark"><?= $isCount ? (int)$val : ($currency.' '.number_format((float)$val,2)) ?></div>
          </div>
          <i class="bi <?= esc($icon) ?> fs-4 text-primary opacity-75"></i>
        </div>
      </div>
    </a>
  </div>
  <?php endforeach; ?>
</div>

<div class="fm-card">
  <div class="fm-card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Bank Summary</h5>
    <a href="<?= base_url('finance-bank/bank-accounts/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>Add Account</a>
  </div>
  <div class="fm-card-body p-0">
    <?php if (empty($banks)): ?>
      <p class="text-center py-4 text-muted mb-0">No bank accounts configured.</p>
    <?php else: ?>
    <table class="fm-table">
      <thead><tr>
        <th>Bank</th><th>Account</th><th>Number</th><th>Currency</th>
        <th>Opening</th><th>Current</th><th>Available</th><th>Last Tx</th><th></th>
      </tr></thead>
      <tbody>
      <?php foreach ($banks as $b): ?>
      <tr>
        <td class="small"><?= esc($b['bank_name'] ?? '—') ?></td>
        <td class="small fw-semibold"><?= esc($b['name']) ?></td>
        <td class="small font-monospace"><?= esc($canFull ? ($b['account_number'] ?? '—') : ($b['account_number_masked'] ?? '—')) ?></td>
        <td class="small"><?= esc($b['currency']) ?></td>
        <td class="small"><?= number_format((float)$b['opening_balance'],2) ?></td>
        <td class="small fw-bold"><?= number_format((float)$b['current_balance'],2) ?></td>
        <td class="small"><?= number_format((float)$b['available_balance'],2) ?></td>
        <td class="small text-muted"><?= $b['last_transaction_date'] ? date('d M Y', strtotime($b['last_transaction_date'])) : '—' ?></td>
        <td><a href="<?= base_url('finance-bank/bank-accounts/view/'.$b['id']) ?>" class="btn btn-sm btn-outline-secondary">View</a></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>
<?= $this->endSection() ?>
