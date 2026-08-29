<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-house-door me-2 text-primary"></i>My Portal</h1>
    <?php if ($tenant): ?>
      <p class="text-muted mb-0">Welcome back, <strong><?= esc($tenant['full_name']) ?></strong></p>
    <?php else: ?>
      <p class="text-muted mb-0">Welcome, <strong><?= esc($currentUser['name']) ?></strong>
        <span class="ms-2 badge bg-warning-subtle text-warning-emphasis">No tenant record linked — contact admin</span></p>
    <?php endif; ?>
  </div>
  <a href="<?= base_url('portal/tickets/create') ?>" class="btn btn-fm-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i>New Ticket
  </a>
</div>

<!-- KPI Row -->
<div class="row g-3 mb-4">
  <div class="col-sm-4">
    <div class="kpi-card kpi-blue">
      <div class="d-flex align-items-center gap-3">
        <div class="kpi-icon"><i class="bi bi-file-earmark-text"></i></div>
        <div>
          <div class="kpi-label">Active Leases</div>
          <div class="kpi-value"><?= (int)$activeLeases ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="kpi-card kpi-orange">
      <div class="d-flex align-items-center gap-3">
        <div class="kpi-icon"><i class="bi bi-cash-coin"></i></div>
        <div>
          <div class="kpi-label">Pending Payments</div>
          <div class="kpi-value"><?= (int)$pendingPayments ?></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="kpi-card kpi-red">
      <div class="d-flex align-items-center gap-3">
        <div class="kpi-icon"><i class="bi bi-tools"></i></div>
        <div>
          <div class="kpi-label">Open Tickets</div>
          <div class="kpi-value"><?= (int)$openTickets ?></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <!-- Recent Leases -->
  <div class="col-lg-6">
    <div class="fm-card h-100">
      <div class="card-header-fm d-flex justify-content-between align-items-center">
        <h5><i class="bi bi-file-earmark-text me-2"></i>My Leases</h5>
        <a href="<?= base_url('portal/leases') ?>" class="small text-primary">View all</a>
      </div>
      <div class="fm-card-body p-0">
        <?php if (empty($recentLeases)): ?>
          <div class="p-4 text-center text-muted small">
            <i class="bi bi-inbox fs-3 d-block mb-2 opacity-50"></i>
            No active leases found.
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="fm-table">
              <thead><tr><th>Contract #</th><th>Property</th><th>Unit</th><th>Rent</th><th>Status</th></tr></thead>
              <tbody>
              <?php foreach ($recentLeases as $l): ?>
              <tr>
                <td class="fw-semibold small">
                  <a href="<?= base_url('portal/leases/'.(int)$l['id']) ?>"><?= esc($l['contract_number']) ?></a>
                </td>
                <td class="small"><?= esc($l['facility_name'] ?? '—') ?></td>
                <td class="small"><?= esc($l['unit_number'] ?? '—') ?></td>
                <td class="small fw-semibold"><?= $currency ?> <?= number_format((float)$l['rent_amount'], 2) ?></td>
                <td><span class="fm-badge badge-status-<?= esc($l['status']) ?>"><?= ucfirst(esc($l['status'])) ?></span></td>
              </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Recent Payments -->
  <div class="col-lg-6">
    <div class="fm-card h-100">
      <div class="card-header-fm d-flex justify-content-between align-items-center">
        <h5><i class="bi bi-credit-card me-2"></i>Recent Payments</h5>
        <a href="<?= base_url('portal/payments') ?>" class="small text-primary">View all</a>
      </div>
      <div class="fm-card-body p-0">
        <?php if (empty($recentPayments)): ?>
          <div class="p-4 text-center text-muted small">
            <i class="bi bi-receipt fs-3 d-block mb-2 opacity-50"></i>
            No payment records yet.
          </div>
        <?php else: ?>
          <?php foreach ($recentPayments as $p): ?>
          <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom border-light">
            <div>
              <div class="small fw-semibold"><?= esc($p['payment_number']) ?></div>
              <div class="x-small text-muted"><?= esc($p['facility_name'] ?? '—') ?> · Due <?= $p['due_date'] ? date('d M Y', strtotime($p['due_date'])) : '—' ?></div>
            </div>
            <div class="text-end">
              <div class="small fw-bold"><?= $currency ?> <?= number_format((float)$p['amount'], 2) ?></div>
              <span class="fm-badge badge-status-<?= esc($p['status']) ?>"><?= ucfirst(esc($p['status'])) ?></span>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Recent Tickets -->
  <div class="col-12">
    <div class="fm-card">
      <div class="card-header-fm d-flex justify-content-between align-items-center">
        <h5><i class="bi bi-tools me-2"></i>Recent Maintenance Tickets</h5>
        <div class="d-flex gap-2">
          <a href="<?= base_url('portal/tickets/create') ?>" class="btn btn-sm btn-fm-primary">
            <i class="bi bi-plus-lg me-1"></i>New Ticket
          </a>
          <a href="<?= base_url('portal/tickets') ?>" class="small text-primary align-self-center">View all</a>
        </div>
      </div>
      <div class="fm-card-body p-0">
        <?php if (empty($recentTickets)): ?>
          <div class="p-4 text-center text-muted small">
            <i class="bi bi-tools fs-3 d-block mb-2 opacity-50"></i>
            No tickets yet. <a href="<?= base_url('portal/tickets/create') ?>">Submit one now.</a>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="fm-table">
              <thead><tr><th>Ticket #</th><th>Title</th><th>Category</th><th>Priority</th><th>Status</th><th>Date</th></tr></thead>
              <tbody>
              <?php foreach ($recentTickets as $t): ?>
              <tr>
                <td class="fw-semibold small"><?= esc($t['ticket_number']) ?></td>
                <td class="small"><?= esc($t['title'] ?? $t['category'] ?? '—') ?></td>
                <td class="small"><?= esc($t['category'] ?? '—') ?></td>
                <td><span class="fm-badge badge-priority-<?= esc($t['priority']) ?>"><?= ucfirst(esc($t['priority'])) ?></span></td>
                <td><span class="fm-badge badge-status-<?= esc($t['status']) ?>"><?= ucfirst(esc($t['status'])) ?></span></td>
                <td class="small"><?= $t['created_at'] ? date('d M Y', strtotime($t['created_at'])) : '—' ?></td>
              </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
