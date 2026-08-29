<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-graph-up-arrow me-2 text-primary"></i>AI Reports</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= base_url('ai') ?>">AI Insights</a></li><li class="breadcrumb-item active">Reports</li></ol></nav>
  </div>
</div>

<!-- 6-Month Revenue Forecast -->
<?php if(!empty($forecast)): ?>
<div class="fm-card mb-4">
  <div class="card-header-fm">
    <h5><i class="bi bi-bar-chart-line me-2"></i>6-Month Lease Revenue Forecast</h5>
    <span class="small text-muted">Based on active lease contracts</span>
  </div>
  <div class="fm-card-body">
    <div class="row g-3">
      <?php
      $maxVal = max(array_values($forecast)) ?: 1;
      foreach($forecast as $month => $amount):
        $pct = $maxVal > 0 ? round($amount / $maxVal * 100) : 0;
      ?>
      <div class="col-sm-4 col-md-2">
        <div class="text-center">
          <div class="text-muted small mb-1"><?= date('M Y', strtotime($month.'-01')) ?></div>
          <div class="fw-bold"><?= $currency ?> <?= number_format($amount, 0) ?></div>
          <div class="health-bar mt-1"><div style="width:<?= $pct ?>%;background:var(--fm-primary);height:8px;border-radius:99px"></div></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="row g-3">
  <!-- Occupancy Analysis -->
  <div class="col-lg-6">
    <div class="fm-card h-100">
      <div class="card-header-fm">
        <h5><i class="bi bi-building me-2"></i>Occupancy Analysis</h5>
      </div>
      <div class="fm-card-body p-0">
        <?php if(empty($occupancyData)): ?>
        <p class="text-muted text-center py-4 small">No occupancy data available.</p>
        <?php else: ?>
        <table class="fm-table">
          <thead><tr><th>Property</th><th class="text-center">Units</th><th class="text-center">Occupied</th><th>Rate</th></tr></thead>
          <tbody>
          <?php foreach($occupancyData as $f):
            $rate = $f['total_units'] > 0 ? round($f['occupied'] / $f['total_units'] * 100) : 0;
          ?>
          <tr>
            <td class="fw-semibold small"><?= esc($f['name']) ?></td>
            <td class="text-center small"><?= $f['total_units'] ?></td>
            <td class="text-center small"><?= $f['occupied'] ?></td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <div class="health-bar flex-grow-1"><div style="width:<?= $rate ?>%;background:<?= $rate>=80?'var(--fm-green)':($rate>=50?'var(--fm-gold)':'var(--fm-red)') ?>;height:6px;border-radius:99px"></div></div>
                <span class="small fw-bold"><?= $rate ?>%</span>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Property AI Scores -->
  <div class="col-lg-6">
    <div class="fm-card h-100">
      <div class="card-header-fm">
        <h5><i class="bi bi-speedometer2 me-2"></i>Property Health Scores</h5>
        <span class="small text-muted">AI-computed</span>
      </div>
      <div class="fm-card-body p-0">
        <?php if(empty($propertyScores)): ?>
        <p class="text-muted text-center py-4 small">No property scores computed yet.</p>
        <?php else: ?>
        <table class="fm-table">
          <thead><tr><th>Property</th><th class="text-center">Score</th><th>Occupancy</th><th>Revenue</th><th>Maintenance</th></tr></thead>
          <tbody>
          <?php foreach($propertyScores as $ps): ?>
          <tr>
            <td class="small fw-semibold"><?= esc($ps['facility_name'] ?? 'Property #'.$ps['facility_id']) ?></td>
            <td class="text-center"><span class="badge bg-<?= $ps['score']>=70?'success':($ps['score']>=50?'warning':'danger') ?>"><?= $ps['score'] ?></span></td>
            <td class="small text-muted"><?= $ps['occupancy_health'] ?? '—' ?></td>
            <td class="small text-muted"><?= $ps['revenue_health'] ?? '—' ?></td>
            <td class="small text-muted"><?= $ps['maintenance_index'] ?? '—' ?></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Risk Flags -->
<div class="fm-card mt-4">
  <div class="card-header-fm">
    <h5><i class="bi bi-exclamation-triangle me-2 text-warning"></i>Risk Summary</h5>
    <span class="badge bg-danger"><?= count($riskFlags) ?> active flags</span>
  </div>
  <div class="fm-card-body p-0">
    <?php if(empty($riskFlags)): ?>
    <div class="text-center py-4 text-muted small"><i class="bi bi-check-circle-fill text-success me-2"></i>No active risk flags.</div>
    <?php else: ?>
    <table class="fm-table">
      <thead><tr><th>Severity</th><th>Module</th><th>Title</th><th>Message</th><th>Workspace</th><th>Flagged</th></tr></thead>
      <tbody>
      <?php foreach($riskFlags as $f): ?>
      <tr>
        <td><span class="badge bg-<?= $f['severity']==='critical'?'danger':($f['severity']==='warning'?'warning':'info') ?>"><?= ucfirst($f['severity']) ?></span></td>
        <td class="small"><?= esc($f['module']) ?> #<?= $f['ref_id'] ?></td>
        <td class="fw-semibold small"><?= esc($f['title']) ?></td>
        <td class="small text-muted"><?= esc(substr($f['message'] ?? '', 0, 80)) ?></td>
        <td class="small"><?= ucfirst($f['workspace']) ?></td>
        <td class="small text-muted"><?= $f['created_at'] ? date('d M Y', strtotime($f['created_at'])) : '—' ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<!-- Tenant Risk Scores -->
<?php if(!empty($tenantRisks)): ?>
<div class="fm-card mt-4">
  <div class="card-header-fm">
    <h5><i class="bi bi-person-exclamation me-2"></i>Tenant Risk Scores</h5>
  </div>
  <div class="fm-card-body p-0">
    <table class="fm-table">
      <thead><tr><th>Tenant</th><th>Phone</th><th class="text-center">Score</th><th>Risk Level</th><th>Computed</th></tr></thead>
      <tbody>
      <?php foreach($tenantRisks as $t): ?>
      <tr>
        <td class="fw-semibold small"><?= esc($t['full_name'] ?? 'Tenant #'.$t['tenant_id']) ?></td>
        <td class="small text-muted"><?= esc($t['phone'] ?? '—') ?></td>
        <td class="text-center"><span class="badge bg-<?= $t['risk_level']==='high'?'danger':'warning' ?>"><?= $t['score'] ?></span></td>
        <td><span class="fm-badge badge-status-<?= $t['risk_level']==='high'?'cancelled':'pending' ?>"><?= ucfirst($t['risk_level']) ?></span></td>
        <td class="small text-muted"><?= $t['calculated_at'] ? date('d M Y', strtotime($t['calculated_at'])) : '—' ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
