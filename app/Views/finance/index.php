<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$primaryColor = $settings['primary_color']   ?? '#76002b';
$netProfit    = ($totalRevenue??0) - ($totalExpenses??0);
$profitClass  = $netProfit >= 0 ? 'kpi-green' : 'kpi-red';
?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-graph-up me-2"></i>Finance</h1>
    <div class="small text-muted">Financial overview · <?= date('F Y') ?></div>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <?php if (in_array(session()->get('user_role'), ['super_admin', 'finance_manager'], true)): ?>
    <a href="<?= base_url('settings/finance-module') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-diagram-3 me-1"></i>Finance Setup</a>
    <?php endif; ?>
    <a href="<?= base_url('finance/invoices/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>New Invoice</a>
    <a href="<?= base_url('finance/expenses/create') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-receipt me-1"></i>Log Expense</a>
  </div>
</div>

<!-- KPI Strip -->
<div class="row g-3 mb-3">
  <div class="col-6 col-md-3">
    <div class="kpi-card kpi-green">
      <div class="d-flex align-items-center gap-3">
        <div class="kpi-icon"><i class="bi bi-cash-stack"></i></div>
        <div>
          <div class="kpi-label">Revenue (YTD)</div>
          <div class="kpi-value"><?= $currency ?> <?= number_format(($totalRevenue??0)/1000,1) ?>K</div>
          <div class="kpi-sub">From paid invoices</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kpi-card kpi-red">
      <div class="d-flex align-items-center gap-3">
        <div class="kpi-icon"><i class="bi bi-credit-card"></i></div>
        <div>
          <div class="kpi-label">Total Expenses</div>
          <div class="kpi-value"><?= $currency ?> <?= number_format(($totalExpenses??0)/1000,1) ?>K</div>
          <div class="kpi-sub">Approved expenses</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kpi-card <?= $profitClass ?>">
      <div class="d-flex align-items-center gap-3">
        <div class="kpi-icon"><i class="bi bi-graph-up-arrow"></i></div>
        <div>
          <div class="kpi-label">Net Profit</div>
          <div class="kpi-value"><?= $currency ?> <?= number_format($netProfit/1000,1) ?>K</div>
          <div class="kpi-sub"><?= ($totalRevenue??0)>0?round($netProfit/($totalRevenue??1)*100,1).'% margin':'' ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kpi-card <?= ($overdueInv??0)>0?'kpi-red':'kpi-teal' ?>">
      <div class="d-flex align-items-center gap-3">
        <div class="kpi-icon"><i class="bi bi-exclamation-circle"></i></div>
        <div>
          <div class="kpi-label">Overdue Invoices</div>
          <div class="kpi-value"><?= $overdueInv??0 ?></div>
          <div class="kpi-sub"><?= ($pendingExp??0) ?> expense<?= ($pendingExp??0)!=1?'s':'' ?> pending</div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">

  <!-- Revenue Trend -->
  <div class="col-lg-8">
    <div class="fm-card">
      <div class="card-header-fm">
        <h5><i class="bi bi-bar-chart me-2"></i>Revenue Trend (6 months)</h5>
        <a href="<?= base_url('reports/finance') ?>" class="small text-primary">Full Report</a>
      </div>
      <div class="fm-card-body"><canvas id="finChart" height="100"></canvas></div>
    </div>
  </div>

  <!-- Quick Links -->
  <div class="col-lg-4">
    <div class="fm-card mb-3">
      <div class="card-header-fm"><h5><i class="bi bi-lightning-charge me-2"></i>Quick Actions</h5></div>
      <div class="fm-card-body p-0">
        <?php
        $links = [
          ['finance/invoices',           'bi-receipt',         'Invoices',           'View & manage all invoices'],
          ['finance/contracts',          'bi-file-earmark-text','Contracts',          'Client & tenant contracts'],
          ['finance/expenses',           'bi-credit-card',     'Expenses',            'Approve pending expenses'],
          ['finance/petty-cash',         'bi-wallet',          'Petty Cash',          'Requests & settlements'],
          ['finance/reimbursements',     'bi-arrow-return-left','Reimbursements',     'Employee reimbursements'],
          ['reports/finance',            'bi-bar-chart-line',  'Finance Reports',     'CSV / Excel export'],
        ];
        foreach($links as [$url,$icon,$label,$desc]):
        ?>
        <a href="<?= base_url($url) ?>" class="d-flex align-items-center gap-3 px-3 py-2 border-bottom border-light text-decoration-none">
          <i class="bi <?= $icon ?> text-primary fs-5" style="color:var(--fm-primary)!important"></i>
          <div>
            <div class="small fw-semibold text-dark"><?= $label ?></div>
            <div class="x-small text-muted"><?= $desc ?></div>
          </div>
          <i class="bi bi-chevron-right ms-auto text-muted small"></i>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Recent Invoices -->
  <div class="col-lg-8">
    <div class="fm-card">
      <div class="card-header-fm">
        <h5><i class="bi bi-receipt me-2"></i>Recent Invoices</h5>
        <a href="<?= base_url('finance/invoices') ?>" class="small text-primary">View all</a>
      </div>
      <div class="fm-card-body p-0">
        <?php if(empty($recentInv)): ?>
        <p class="text-center py-4 text-muted small">No invoices yet. <a href="<?= base_url('finance/invoices/create') ?>">Create one</a>.</p>
        <?php else: ?>
        <table class="fm-table">
          <thead><tr><th>Invoice #</th><th>Facility</th><th>Amount</th><th>Due</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach($recentInv as $i): ?>
          <tr>
            <td><a href="<?= base_url('finance/invoices/view/'.$i['id']) ?>" class="fw-semibold text-primary small"><?= esc($i['invoice_number']) ?></a></td>
            <td class="small text-muted"><?= esc($i['facility_name']??'—') ?></td>
            <td class="small fw-bold"><?= $currency ?> <?= number_format($i['total'],2) ?></td>
            <td class="small <?= $i['status']==='overdue'?'text-danger fw-bold':'' ?>"><?= date('d M Y',strtotime($i['due_date'])) ?></td>
            <td><span class="fm-badge badge-status-<?= esc($i['status']) ?>"><?= ucfirst($i['status']) ?></span></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Expiring Contracts -->
  <div class="col-lg-4">
    <div class="fm-card">
      <div class="card-header-fm">
        <h5><i class="bi bi-clock-history me-2 text-warning"></i>Expiring Contracts</h5>
        <a href="<?= base_url('finance/contracts') ?>" class="small text-primary">View all</a>
      </div>
      <div class="fm-card-body p-0">
        <?php if(empty($expiringContracts)): ?>
        <p class="text-center py-3 text-success small"><i class="bi bi-check-circle me-1"></i>None expiring soon</p>
        <?php else: ?>
        <?php foreach($expiringContracts as $c):
          $days = (int)ceil((strtotime($c['end_date']) - time()) / 86400);
        ?>
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom border-light">
          <div>
            <div class="small fw-semibold"><a href="<?= base_url('finance/contracts/view/'.$c['id']) ?>" class="text-primary"><?= esc($c['contract_number']) ?></a></div>
            <div class="x-small text-muted"><?= esc($c['client_name']) ?></div>
          </div>
          <span class="fm-badge badge-status-<?= $days<=7?'overdue':($days<=30?'pending':'active') ?>"><?= $days ?>d</span>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
(function(){
  const PRIMARY   = '<?= $primaryColor ?>';
  const CURRENCY  = '<?= $currency ?>';
  const trend     = <?= json_encode($trend??[]) ?>;
  const ctx       = document.getElementById('finChart');
  if(!ctx || !trend.length) return;
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: trend.map(d => d.mon),
      datasets: [
        {
          label: 'Revenue',
          data: trend.map(d => parseFloat(d.revenue||0)),
          borderColor: PRIMARY,
          backgroundColor: PRIMARY + '22',
          borderWidth: 2.5,
          pointRadius: 4,
          pointBackgroundColor: PRIMARY,
          fill: true,
          tension: 0.4
        },
        {
          label: 'Outstanding',
          data: trend.map(d => parseFloat(d.outstanding||0)),
          borderColor: '#e65100',
          backgroundColor: 'transparent',
          borderWidth: 2,
          borderDash: [5,5],
          pointRadius: 3,
          tension: 0.4
        }
      ]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } },
        tooltip: { callbacks: { label: ctx => CURRENCY + ' ' + parseFloat(ctx.raw).toLocaleString() } }
      },
      scales: {
        y: { ticks: { callback: v => CURRENCY + ' ' + (v >= 1000 ? Math.round(v/1000)+'K' : v) }, grid: { color: '#f0f4f8' } },
        x: { grid: { display: false } }
      }
    }
  });
})();
</script>
<?= $this->endSection() ?>
