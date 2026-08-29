<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-graph-up-arrow me-2"></i>PM KPI Analytics</h1>
    <div class="small text-muted">Property portfolio performance indicators</div>
  </div>
  <a href="<?= base_url('reports/pm') ?>" class="btn btn-fm-outline btn-sm">← Reports</a>
</div>

<div class="row g-3 mb-3">
  <div class="col-6 col-md-3"><div class="kpi-card kpi-blue"><div class="kpi-label">Occupancy</div><div class="kpi-value"><?= esc($occupancy) ?>%</div><div class="kpi-sub"><?= (int)$occupiedUnits ?> / <?= (int)$totalUnits ?> units</div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-orange"><div class="kpi-label">Active Leases</div><div class="kpi-value"><?= (int)$activeLeases ?></div><div class="kpi-sub"><?= (int)$expiringSoon ?> expiring (60d)</div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-green"><div class="kpi-label">Revenue (paid)</div><div class="kpi-value" style="font-size:1rem"><?= esc($currency) ?> <?= number_format($revenue, 0) ?></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-red"><div class="kpi-label">Outstanding AR</div><div class="kpi-value" style="font-size:1rem"><?= esc($currency) ?> <?= number_format($pending, 0) ?></div><div class="kpi-sub"><?= (int)$overdueCount ?> overdue</div></div></div>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-4"><div class="kpi-card kpi-teal"><div class="kpi-label">Overdue Amount</div><div class="kpi-value" style="font-size:1rem"><?= esc($currency) ?> <?= number_format($overdueAmount, 0) ?></div></div></div>
  <div class="col-md-4"><div class="kpi-card kpi-secondary"><div class="kpi-label">Open Maintenance</div><div class="kpi-value"><?= (int)$openMaint ?></div></div></div>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="fm-card"><div class="fm-card-header"><h5>Revenue vs Expenses (12 mo)</h5></div>
      <div class="fm-card-body"><canvas id="revChart" height="200"></canvas></div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="fm-card"><div class="fm-card-header"><h5>Property Occupancy</h5></div>
      <div class="fm-card-body p-0">
        <table class="fm-table">
          <thead><tr><th>Property</th><th>Units</th><th>Occupied</th><th>Occupancy</th><th>Revenue</th></tr></thead>
          <tbody>
          <?php foreach ($facilityStats as $f):
            $occ = ($f['total_units'] ?? 0) > 0 ? round(($f['occupied_units'] / $f['total_units']) * 100, 1) : 0;
          ?>
          <tr>
            <td class="fw-semibold small"><?= esc($f['name']) ?></td>
            <td><?= (int)($f['total_units'] ?? 0) ?></td>
            <td><?= (int)($f['occupied_units'] ?? 0) ?></td>
            <td><?= $occ ?>%</td>
            <td><?= number_format((float)($f['revenue'] ?? 0), 0) ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($facilityStats)): ?><tr><td colspan="5" class="text-center text-muted py-4">No property data</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
const revData = <?= json_encode($revTrend) ?>;
if (document.getElementById('revChart') && revData.length) {
  new Chart(document.getElementById('revChart'), {
    type: 'bar',
    data: {
      labels: revData.map(r => r.mon),
      datasets: [
        { label: 'Revenue', data: revData.map(r => r.revenue), backgroundColor: '#10b981' },
        { label: 'Expenses', data: revData.map(r => r.expenses), backgroundColor: '#ef4444' }
      ]
    },
    options: { responsive: true, scales: { y: { beginAtZero: true } } }
  });
}
</script>
<?= $this->endSection() ?>
