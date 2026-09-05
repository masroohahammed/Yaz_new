<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-buildings me-2"></i>Property Management Dashboard</h1>
    <div class="small text-muted"><?= date('l, d F Y') ?> · Occupancy &amp; leasing overview</div>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= base_url('properties') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-building me-1"></i>Properties</a>
    <a href="<?= base_url('tenants') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-people me-1"></i>Tenants</a>
    <a href="<?= base_url('contracts') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-file-earmark-text me-1"></i>Contracts</a>
    <a href="<?= base_url('finance/invoices') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-receipt me-1"></i>Invoices</a>
  </div>
</div>

<?= view('partials/ai_alert_banner', ['aiFlags' => $aiFlags ?? []]) ?>

<?php helper('fm'); ?>
<?php if (fm_can_view_kpis()): ?>
<div class="row g-3 mb-3">
  <div class="col-6 col-sm-6 col-md-3 col-lg-3">
    <a href="<?= base_url('properties') ?>" class="text-decoration-none d-block h-100">
      <div class="kpi-card kpi-blue h-100">
        <div class="d-flex align-items-center gap-3">
          <div class="kpi-icon"><i class="bi bi-building"></i></div>
          <div><div class="kpi-label">Properties</div><div class="kpi-value"><?= (int) $totalFacilities ?></div></div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-6 col-sm-6 col-md-3 col-lg-3">
    <a href="<?= base_url('reports/pm/occupancy') ?>" class="text-decoration-none d-block h-100">
      <div class="kpi-card kpi-teal h-100">
        <div class="d-flex align-items-center gap-3">
          <div class="kpi-icon"><i class="bi bi-door-closed"></i></div>
          <div>
            <div class="kpi-label">Occupancy</div>
            <div class="kpi-value"><?= esc($occupancy) ?>%</div>
            <div class="kpi-sub"><?= (int) $totalUnits ?> units</div>
          </div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-6 col-sm-6 col-md-3 col-lg-3">
    <a href="<?= base_url('contracts') ?>" class="text-decoration-none d-block h-100">
      <div class="kpi-card kpi-green h-100">
        <div class="d-flex align-items-center gap-3">
          <div class="kpi-icon"><i class="bi bi-file-earmark-text"></i></div>
          <div>
            <div class="kpi-label">Active Contracts</div>
            <div class="kpi-value"><?= (int) $activeContracts ?></div>
            <?php if ($expiringSoon > 0): ?><div class="kpi-sub text-warning"><?= (int) $expiringSoon ?> expiring</div><?php endif; ?>
          </div>
        </div>
      </div>
    </a>
  </div>
  <div class="col-6 col-sm-6 col-md-3 col-lg-3">
    <a href="<?= base_url('finance/invoices') ?>" class="text-decoration-none d-block h-100">
      <div class="kpi-card kpi-red h-100">
        <div class="d-flex align-items-center gap-3">
          <div class="kpi-icon"><i class="bi bi-exclamation-circle"></i></div>
          <div>
            <div class="kpi-label">Overdue Invoices</div>
            <div class="kpi-value"><?= (int) $overdueCount ?></div>
            <div class="kpi-sub"><?= number_format((float) $overdueAmount, 0) ?> <?= esc($currency) ?></div>
          </div>
        </div>
      </div>
    </a>
  </div>
</div>
<?php endif; ?>

<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="fm-card h-100">
      <div class="card-header-fm"><h5><i class="bi bi-cash-stack me-2"></i>Finance snapshot</h5></div>
      <div class="fm-card-body">
        <div class="d-flex justify-content-between mb-2 small"><span class="text-muted">Collected revenue</span><strong><?= number_format((float) $revenue, 2) ?> <?= esc($currency) ?></strong></div>
        <div class="d-flex justify-content-between mb-2 small"><span class="text-muted">Outstanding</span><strong class="text-warning"><?= number_format((float) $pendingReceivable, 2) ?> <?= esc($currency) ?></strong></div>
        <div class="d-flex justify-content-between small"><span class="text-muted">Overdue</span><strong class="text-danger"><?= number_format((float) $overdueAmount, 2) ?> <?= esc($currency) ?></strong></div>
        <div class="d-flex justify-content-between small mt-2"><span class="text-muted">Cancelled / voided</span><strong><?= number_format((float) ($cancelledAmount ?? 0), 2) ?> <?= esc($currency) ?></strong></div>
        <div class="mt-3">
          <a href="<?= base_url('finance/invoices') ?>" class="btn btn-fm-outline btn-sm">View invoices</a>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="fm-card h-100">
      <div class="card-header-fm"><h5><i class="bi bi-bell me-2"></i>Leasing alerts</h5></div>
      <div class="fm-card-body">
        <div class="d-flex justify-content-between align-items-center mb-2 small">
          <span class="text-muted">Contracts expiring (60d)</span>
          <a href="<?= base_url('reports/pm/leases?expiring=1') ?>" class="badge bg-warning text-dark text-decoration-none"><?= (int) $expiringSoon ?></a>
        </div>
        <div class="d-flex justify-content-between align-items-center small">
          <span class="text-muted">Open maintenance (view)</span>
          <a href="<?= base_url('maintenance/list') ?>" class="badge bg-secondary text-decoration-none"><?= (int) $openMaintenance ?></a>
        </div>
        <?php if ($expiringSoon > 0): ?>
        <div class="mt-3">
          <a href="<?= base_url('reports/pm/leases?expiring=1') ?>" class="btn btn-fm-outline btn-sm">Review expiring leases</a>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="fm-card h-100">
      <div class="card-header-fm"><h5><i class="bi bi-grid me-2"></i>Portfolio</h5></div>
      <div class="fm-card-body">
        <div class="d-flex justify-content-between mb-2 small"><span class="text-muted">Total units</span><strong><?= (int) $totalUnits ?></strong></div>
        <div class="d-flex justify-content-between small"><span class="text-muted">Occupied</span><strong><?= (int) round($totalUnits * $occupancy / 100) ?></strong></div>
        <div class="mt-3">
          <a href="<?= base_url('reports/pm/occupancy') ?>" class="btn btn-fm-outline btn-sm">Occupancy report</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php if (!empty($facilityStats)): ?>
<div class="fm-card mb-3">
  <div class="card-header-fm">
    <h5><i class="bi bi-building me-2"></i>Property occupancy</h5>
    <a href="<?= base_url('properties') ?>" class="btn btn-fm-outline btn-sm">View all</a>
  </div>
  <div class="fm-card-body p-0">
    <table class="fm-table">
      <thead><tr><th>Property</th><th>Units</th><th>Occupied</th><th>Occupancy</th></tr></thead>
      <tbody>
      <?php foreach (array_slice($facilityStats, 0, 8) as $fs):
        $totalU = (int) ($fs['total_units'] ?? 0);
        $occU   = (int) ($fs['occupied_units'] ?? 0);
        $occPct = $totalU > 0 ? round(($occU / $totalU) * 100) : 0;
      ?>
        <tr>
          <td class="small fw-semibold"><a href="<?= base_url('properties/view/'.($fs['id'] ?? 0)) ?>" class="text-primary"><?= esc($fs['name'] ?? '—') ?></a></td>
          <td class="small"><?= $totalU ?></td>
          <td class="small"><?= $occU ?></td>
          <td class="small"><?= $occPct ?>%</td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="fm-card">
      <div class="card-header-fm">
        <h5><i class="bi bi-calendar-event me-2"></i>Upcoming expiries</h5>
        <a href="<?= base_url('reports/pm/leases?expiring=1') ?>" class="btn btn-fm-outline btn-sm">View all</a>
      </div>
      <div class="fm-card-body p-0">
        <table class="fm-table">
          <thead><tr><th>Contract</th><th>Client</th><th>Property</th><th>Ends</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($expiringContracts as $c):
            $daysLeft = ! empty($c['end_date']) ? (int) ceil((strtotime($c['end_date']) - time()) / 86400) : null;
            $contractUrl = base_url('contracts/' . (int) ($c['id'] ?? 0));
          ?>
            <tr class="fm-clickable-row" role="link" tabindex="0" data-href="<?= esc($contractUrl) ?>" style="cursor:pointer">
              <td class="small fw-semibold"><a href="<?= esc($contractUrl) ?>" class="text-primary text-decoration-none"><?= esc($c['contract_number']) ?></a></td>
              <td class="small"><?= esc($c['client_name']) ?></td>
              <td class="small text-muted"><?= esc($c['facility_name'] ?? '—') ?></td>
              <td class="small">
                <?= !empty($c['end_date']) ? date('d M Y', strtotime($c['end_date'])) : '—' ?>
                <?php if ($daysLeft !== null && $daysLeft > 0 && $daysLeft <= 60): ?>
                  <br><span class="x-small text-danger"><?= (int) $daysLeft ?>d left</span>
                <?php endif; ?>
              </td>
              <td class="text-end"><a href="<?= esc($contractUrl) ?>" class="btn btn-sm btn-fm-outline">Open</a></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($expiringContracts)): ?>
            <tr><td colspan="5" class="text-muted text-center py-4 small">No contracts expiring soon.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="fm-card">
      <div class="card-header-fm">
        <h5><i class="bi bi-receipt me-2"></i>Overdue invoices</h5>
        <a href="<?= base_url('finance/invoices') ?>" class="btn btn-fm-outline btn-sm">View all</a>
      </div>
      <div class="fm-card-body p-0">
        <table class="fm-table">
          <thead><tr><th>Invoice</th><th>Property</th><th>Due</th><th class="text-end">Amount</th></tr></thead>
          <tbody>
          <?php foreach ($overdueInvoices as $inv):
            $invUrl = base_url('finance/invoices/view/' . (int) ($inv['id'] ?? 0));
          ?>
            <tr class="fm-clickable-row" role="link" tabindex="0" data-href="<?= esc($invUrl) ?>" style="cursor:pointer">
              <td class="small fw-semibold"><a href="<?= esc($invUrl) ?>" class="text-primary text-decoration-none"><?= esc($inv['invoice_number']) ?></a></td>
              <td class="small text-muted"><?= esc($inv['facility_name'] ?? '—') ?></td>
              <td class="small"><?= !empty($inv['due_date']) ? date('d M Y', strtotime($inv['due_date'])) : '—' ?></td>
              <td class="text-end small"><?= number_format((float) $inv['total'], 2) ?> <?= esc($currency) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($overdueInvoices)): ?>
            <tr><td colspan="4" class="text-muted text-center py-4 small">No overdue invoices.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="fm-card mt-3">
  <div class="card-header-fm">
    <h5><i class="bi bi-tools me-2"></i>Maintenance history <span class="badge bg-secondary-subtle text-secondary">Read-only</span></h5>
    <a href="<?= base_url('maintenance/list') ?>" class="btn btn-fm-outline btn-sm">Open list</a>
  </div>
  <div class="fm-card-body p-0">
    <table class="fm-table">
      <thead><tr><th>Ticket</th><th>Property</th><th>Category</th><th>Priority</th><th>Status</th><th>Date</th></tr></thead>
      <tbody>
      <?php foreach ($recentMaintenance as $mr): ?>
        <tr>
          <td class="small"><a href="<?= base_url('maintenance/view/' . $mr['id']) ?>" class="fw-semibold text-primary"><?= esc($mr['ticket_number']) ?></a></td>
          <td class="small text-muted"><?= esc($mr['facility_name'] ?? '—') ?></td>
          <td class="small"><?= esc($mr['category']) ?></td>
          <td><span class="fm-badge badge-priority-<?= esc($mr['priority']) ?>"><?= esc(ucfirst($mr['priority'])) ?></span></td>
          <td><span class="fm-badge badge-status-<?= esc($mr['status']) ?>"><?= esc(ucfirst($mr['status'])) ?></span></td>
          <td class="small text-muted"><?= esc(substr($mr['created_at'], 0, 10)) ?></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($recentMaintenance)): ?>
        <tr><td colspan="6" class="text-muted text-center py-4 small">No recent maintenance requests.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
(function () {
  document.querySelectorAll('.fm-clickable-row[data-href]').forEach(function (row) {
    row.addEventListener('click', function (e) {
      if (e.target.closest('a, button')) return;
      window.location = row.getAttribute('data-href');
    });
    row.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        window.location = row.getAttribute('data-href');
      }
    });
  });
})();
</script>
<?= $this->endSection() ?>
