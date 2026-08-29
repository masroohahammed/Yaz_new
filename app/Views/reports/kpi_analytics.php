<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-graph-up-arrow me-2"></i>KPI Analytics</h1>
    <div class="small text-muted">Operational and financial performance indicators</div>
  </div>
  <a href="<?= base_url('reports') ?>" class="btn btn-fm-outline btn-sm">← Reports</a>
</div>

<div class="row g-3 mb-3">
  <div class="col-6 col-md-3"><div class="kpi-card kpi-blue"><div class="kpi-label">SLA Compliance</div><div class="kpi-value"><?= esc($slaCompliance) ?>%</div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-orange"><div class="kpi-label">Open Work Orders</div><div class="kpi-value"><?= (int)$openWO ?></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-red"><div class="kpi-label">SLA Breaches</div><div class="kpi-value"><?= (int)$breachedWO ?></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-green"><div class="kpi-label">Revenue (paid)</div><div class="kpi-value" style="font-size:1rem"><?= esc($currency) ?> <?= number_format($revenue, 0) ?></div></div></div>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="fm-card"><div class="fm-card-header"><h5>Revenue vs Expenses (12 mo)</h5></div>
      <div class="fm-card-body"><canvas id="revChart" height="200"></canvas></div>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="fm-card"><div class="fm-card-header"><h5>Work Orders by Priority</h5></div>
      <div class="fm-card-body"><canvas id="prioChart" height="200"></canvas></div>
    </div>
  </div>
  <div class="col-12">
    <div class="fm-card"><div class="fm-card-header"><h5>Facility Performance</h5></div>
      <div class="fm-card-body p-0">
        <table class="fm-table">
          <thead><tr><th>Facility</th><th>Open WO</th><th>SLA Breaches</th><th>Occupancy</th><th>Revenue</th><th>Health</th></tr></thead>
          <tbody>
          <?php foreach ($facilityStats as $f):
            $occ = ($f['total_units'] ?? 0) > 0 ? round(($f['occupied_units'] / $f['total_units']) * 100, 1) : 0;
          ?>
          <tr>
            <td class="fw-semibold small"><?= esc($f['name']) ?></td>
            <td><?= (int)($f['open_wo'] ?? 0) ?></td>
            <td><?= (int)($f['sla_breaches'] ?? 0) ?></td>
            <td><?= $occ ?>%</td>
            <td><?= number_format((float)($f['revenue'] ?? 0), 0) ?></td>
            <td><?= (int)($f['avg_health'] ?? 100) ?>%</td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($facilityStats)): ?><tr><td colspan="6" class="text-center text-muted py-4">No facility data</td></tr><?php endif; ?>
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
const prioData = <?= json_encode($woPriority) ?>;
if (document.getElementById('revChart')) {
  new Chart(document.getElementById('revChart'), {
    type: 'line',
    data: {
      labels: revData.map(r => r.mon),
      datasets: [
        { label: 'Revenue', data: revData.map(r => r.revenue), borderColor: '#76002b', tension: 0.3 },
        { label: 'Expenses', data: revData.map(r => r.expenses), borderColor: '#c7ba9a', tension: 0.3 }
      ]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
  });
}
if (document.getElementById('prioChart')) {
  new Chart(document.getElementById('prioChart'), {
    type: 'doughnut',
    data: {
      labels: prioData.map(p => p.priority),
      datasets: [{ data: prioData.map(p => p.cnt), backgroundColor: ['#dc3545','#fd7e14','#ffc107','#198754'] }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
  });
}
</script>
<?= $this->endSection() ?>
