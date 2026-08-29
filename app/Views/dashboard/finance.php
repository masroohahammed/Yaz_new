<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$currency     = $settings['currency'] ?? 'QAR';
$totalRevenue = (float)($finRow['revenue']  ?? 0);
$overdueTot   = (float)($finRow['overdue']  ?? 0);
$pendingTot   = (float)($finRow['pending']  ?? 0);
$overdueCount = (int)  ($finRow['overdue_count'] ?? 0);
$totalExp     = (float)($expRow['approved'] ?? 0);
$pendingExp   = (float)($expRow['pending_amount'] ?? 0);
$pendingExpC  = (int)  ($expRow['pending_count']  ?? 0);
$pcPending    = (int)  ($pettyCashRow['pending_count'] ?? 0);
$rmbPending   = (int)  ($reimbRow['pending_count'] ?? 0);
$netProfit    = $totalRevenue - $totalExp;
?>

<div class="page-header">
  <div><h1><i class="bi bi-graph-up me-2"></i>Finance Dashboard</h1><div class="small text-muted mt-1"><?= date('l, d F Y') ?></div></div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('finance/invoices/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>New Invoice</a>
    <a href="<?= base_url('finance/expenses/create') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-receipt me-1"></i>Log Expense</a>
  </div>
</div>

<!-- KPI Row -->
<div class="row g-3 mb-3">
  <div class="col-6 col-md-3">
    <div class="kpi-card kpi-green">
      <div class="d-flex align-items-center gap-3">
        <div class="kpi-icon"><i class="bi bi-cash-stack"></i></div>
        <div>
          <div class="kpi-label">Revenue (YTD)</div>
          <div class="kpi-value" style="font-size:1.2rem"><?= $currency ?> <?= number_format($totalRevenue/1000,1) ?>K</div>
          <div class="kpi-sub">Net: <?= $currency ?> <?= number_format($netProfit/1000,1) ?>K</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kpi-card <?= $overdueCount > 0 ? 'kpi-red' : 'kpi-teal' ?>">
      <div class="d-flex align-items-center gap-3">
        <div class="kpi-icon"><i class="bi bi-exclamation-circle"></i></div>
        <div>
          <div class="kpi-label">Overdue Invoices</div>
          <div class="kpi-value"><?= $overdueCount ?></div>
          <div class="kpi-sub"><?= $currency ?> <?= number_format($overdueTot) ?> outstanding</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kpi-card kpi-orange">
      <div class="d-flex align-items-center gap-3">
        <div class="kpi-icon"><i class="bi bi-hourglass-split"></i></div>
        <div>
          <div class="kpi-label">Pending Invoices</div>
          <div class="kpi-value"><?= $currency ?> <?= number_format($pendingTot/1000,1) ?>K</div>
          <div class="kpi-sub">Awaiting payment</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kpi-card <?= ($pendingExpC + $pcPending + $rmbPending) > 0 ? 'kpi-primary' : 'kpi-teal' ?>">
      <div class="d-flex align-items-center gap-3">
        <div class="kpi-icon"><i class="bi bi-clock-history"></i></div>
        <div>
          <div class="kpi-label">Pending Approvals</div>
          <div class="kpi-value"><?= $pendingExpC + $pcPending + $rmbPending ?></div>
          <div class="kpi-sub"><?= $pendingExpC ?> exp · <?= $pcPending ?> PC · <?= $rmbPending ?> reimb</div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">

  <!-- OVERDUE INVOICES -->
  <div class="col-lg-6">
    <div class="fm-card">
      <div class="card-header-fm">
        <h5><i class="bi bi-exclamation-triangle me-2 text-danger"></i>Overdue Invoices</h5>
        <a href="<?= base_url('finance/invoices?status=overdue') ?>" class="small text-primary">View all</a>
      </div>
      <div class="fm-card-body p-0">
        <?php if(empty($overdueInvoices)): ?>
        <p class="text-muted text-center py-4 small">No overdue invoices</p>
        <?php else: ?>
        <table class="fm-table">
          <thead><tr><th>Invoice</th><th>Facility</th><th>Amount</th><th>Due</th><th></th></tr></thead>
          <tbody>
          <?php foreach($overdueInvoices as $inv): ?>
          <tr>
            <td><a href="<?= base_url('finance/invoices/view/'.$inv['id']) ?>" class="fw-semibold text-primary"><?= esc($inv['invoice_number']) ?></a></td>
            <td class="small text-muted"><?= esc($inv['facility_name'] ?? '—') ?></td>
            <td class="fw-bold text-danger"><?= $currency ?> <?= number_format($inv['total'],2) ?></td>
            <td><span class="small text-danger"><?= date('d M', strtotime($inv['due_date'])) ?></span></td>
            <td>
              <form method="post" action="<?= base_url('finance/invoices/status/'.$inv['id']) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="status" value="paid">
                <button type="submit" class="btn btn-success btn-sm py-0 px-2" onclick="return confirm('Mark as paid?')">Mark Paid</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- PENDING INVOICES -->
  <div class="col-lg-6">
    <div class="fm-card">
      <div class="card-header-fm">
        <h5><i class="bi bi-receipt me-2 text-warning"></i>Pending Invoices</h5>
        <a href="<?= base_url('finance/invoices?status=sent') ?>" class="small text-primary">View all</a>
      </div>
      <div class="fm-card-body p-0">
        <?php if(empty($pendingInvoices)): ?>
        <p class="text-muted text-center py-4 small">No pending invoices</p>
        <?php else: ?>
        <table class="fm-table">
          <thead><tr><th>Invoice</th><th>Facility</th><th>Amount</th><th>Due</th></tr></thead>
          <tbody>
          <?php foreach($pendingInvoices as $inv): ?>
          <tr>
            <td><a href="<?= base_url('finance/invoices/view/'.$inv['id']) ?>" class="fw-semibold text-primary"><?= esc($inv['invoice_number']) ?></a></td>
            <td class="small text-muted"><?= esc($inv['facility_name'] ?? '—') ?></td>
            <td class="fw-semibold"><?= $currency ?> <?= number_format($inv['total'],2) ?></td>
            <td><span class="small <?= strtotime($inv['due_date']) < time() ? 'text-danger fw-bold' : 'text-muted' ?>"><?= date('d M', strtotime($inv['due_date'])) ?></span></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- PENDING EXPENSES -->
  <div class="col-lg-6">
    <div class="fm-card">
      <div class="card-header-fm">
        <h5><i class="bi bi-credit-card me-2 text-primary"></i>Pending Expenses</h5>
        <a href="<?= base_url('finance/expenses') ?>" class="small text-primary">View all</a>
      </div>
      <div class="fm-card-body p-0">
        <?php if(empty($pendingExpenses)): ?>
        <p class="text-muted text-center py-4 small">No pending expenses</p>
        <?php else: ?>
        <table class="fm-table">
          <thead><tr><th>Description</th><th>By</th><th>Amount</th><th>Actions</th></tr></thead>
          <tbody>
          <?php foreach($pendingExpenses as $e): ?>
          <tr>
            <td>
              <div class="small fw-semibold"><?= esc(substr($e['description'],0,40)) ?></div>
              <span class="x-small text-muted"><?= esc(ucfirst(str_replace('_',' ',$e['category']))) ?></span>
            </td>
            <td class="small text-muted"><?= esc($e['created_by_name'] ?? '—') ?></td>
            <td class="fw-semibold"><?= $currency ?> <?= number_format($e['amount'],2) ?></td>
            <td>
              <form method="post" action="<?= base_url('finance/expenses/approve/'.$e['id']) ?>" class="d-inline">
                <?= csrf_field() ?>
                <button class="btn-action bg-success text-white" title="Approve" onclick="return confirm('Approve expense?')"><i class="bi bi-check-lg"></i></button>
              </form>
              <form method="post" action="<?= base_url('finance/expenses/reject/'.$e['id']) ?>" class="d-inline">
                <?= csrf_field() ?>
                <button class="btn-action bg-danger text-white" title="Reject" onclick="return confirm('Reject?')"><i class="bi bi-x-lg"></i></button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- PETTY CASH APPROVALS -->
  <div class="col-lg-6">
    <div class="fm-card">
      <div class="card-header-fm">
        <h5><i class="bi bi-wallet me-2 text-secondary"></i>Petty Cash &amp; Reimbursements</h5>
        <a href="<?= base_url('finance/petty-cash') ?>" class="small text-primary">View all</a>
      </div>
      <div class="fm-card-body p-0">
        <?php if(empty($pendingPettyCash)): ?>
        <p class="text-muted text-center py-4 small">No pending petty cash requests</p>
        <?php else: ?>
        <table class="fm-table">
          <thead><tr><th>Ref</th><th>Requested By</th><th>Amount</th><th>Purpose</th><th>Actions</th></tr></thead>
          <tbody>
          <?php foreach($pendingPettyCash as $pc): ?>
          <tr>
            <td class="small fw-semibold"><?= esc($pc['pc_number']) ?></td>
            <td class="small text-muted"><?= esc($pc['requested_by_name'] ?? '—') ?></td>
            <td class="fw-semibold"><?= $currency ?> <?= number_format($pc['amount'],2) ?></td>
            <td class="small text-muted"><?= esc(substr($pc['purpose'],0,30)) ?></td>
            <td>
              <form method="post" action="<?= base_url('finance/petty-cash/approve/'.$pc['id']) ?>" class="d-inline">
                <?= csrf_field() ?>
                <button class="btn-action bg-success text-white" title="Approve"><i class="bi bi-check-lg"></i></button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- QUICK LINKS -->
  <div class="col-12">
    <div class="fm-card">
      <div class="card-header-fm"><h5><i class="bi bi-lightning-charge me-2"></i>Quick Actions</h5></div>
      <div class="fm-card-body">
        <div class="row g-2">
          <?php
          $links = [
            ['finance/invoices/create',      'bi-plus-circle',     'Create Invoice',    'primary'],
            ['finance/invoices',             'bi-receipt',         'View Invoices',     'secondary'],
            ['finance/contracts',            'bi-file-earmark-text','Contracts',        'blue'],
            ['finance/expenses',             'bi-credit-card',     'Expenses',          'orange'],
            ['finance/petty-cash',           'bi-wallet',          'Petty Cash',        'green'],
            ['finance/reimbursements',       'bi-arrow-return-left','Reimbursements',   'teal'],
            ['reports/finance',              'bi-bar-chart-line',  'Finance Reports',   'gold'],
          ];
          foreach($links as [$url,$icon,$label,$color]):
          ?>
          <div class="col-6 col-md-3">
            <a href="<?= base_url($url) ?>" class="text-decoration-none">
              <div class="fm-form-section text-center py-3 h-100" style="cursor:pointer">
                <i class="bi <?= $icon ?> fs-4 mb-2 d-block text-<?= $color ?>"></i>
                <div class="small fw-semibold"><?= $label ?></div>
              </div>
            </a>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

</div>
<?= $this->endSection() ?>
