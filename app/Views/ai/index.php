<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-robot me-2" style="color:var(--fm-gold)"></i>AI Smart Insights</h1><div class="small text-muted">Intelligent analysis powered by your live facility data</div></div>
</div>
<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="kpi-card kpi-red"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-exclamation-triangle"></i></div><div><div class="kpi-label">SLA Breaches</div><div class="kpi-value"><?= $breachedWO ?></div></div></div></div></div>
  <div class="col-md-3"><div class="kpi-card kpi-orange"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-box-seam"></i></div><div><div class="kpi-label">Low Stock Items</div><div class="kpi-value"><?= $lowStock ?></div></div></div></div></div>
  <div class="col-md-3"><div class="kpi-card kpi-gold"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-file-earmark-x"></i></div><div><div class="kpi-label">Expiring Docs</div><div class="kpi-value"><?= $expiringDocs ?></div></div></div></div></div>
  <div class="col-md-3"><div class="kpi-card kpi-navy"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-cpu"></i></div><div><div class="kpi-label">Assets Monitored</div><div class="kpi-value"><?= count($assets) ?></div></div></div></div></div>
</div>
<div class="row g-3">
  <div class="col-lg-6"><div class="fm-card h-100">
    <div class="card-header-fm"><h5><i class="bi bi-graph-up-arrow me-2 text-primary"></i>Predictive Maintenance</h5><button class="btn btn-fm-primary btn-sm" onclick="runAI('<?= base_url('ai/predictive') ?>','predictive-result')"><i class="bi bi-play-fill me-1"></i>Analyze</button></div>
    <div class="fm-card-body"><p class="small text-muted mb-3">Predicts asset failures based on health scores and maintenance history.</p>
    <div id="predictive-result" class="ai-box" style="display:none"></div>
    <?php foreach(array_slice($assets,0,5) as $a): ?>
    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
      <span class="small"><?= esc($a['asset_code']) ?> — <?= esc($a['name']) ?></span>
      <div class="d-flex gap-2 align-items-center">
        <div class="progress" style="width:50px;height:6px"><div class="progress-bar <?= $a['health_score']>=80?'bg-success':($a['health_score']>=60?'bg-warning':'bg-danger') ?>" style="width:<?= $a['health_score'] ?>%"></div></div>
        <span class="small fw-bold"><?= $a['health_score'] ?>%</span>
      </div>
    </div>
    <?php endforeach; ?>
    </div>
  </div></div>

  <div class="col-lg-6"><div class="fm-card h-100">
    <div class="card-header-fm"><h5><i class="bi bi-shield-exclamation me-2 text-danger"></i>Facility Risk Analysis</h5><button class="btn btn-fm-primary btn-sm" onclick="runAI('<?= base_url('ai/risk') ?>','risk-result')"><i class="bi bi-play-fill me-1"></i>Analyze</button></div>
    <div class="fm-card-body"><p class="small text-muted mb-3">Facility-level risk scoring based on SLA breaches, asset health, and open work orders.</p><div id="risk-result" class="ai-box" style="display:none"></div></div>
  </div></div>

  <div class="col-lg-6"><div class="fm-card h-100">
    <div class="card-header-fm"><h5><i class="bi bi-piggy-bank me-2 text-success"></i>Cost Optimization</h5><button class="btn btn-fm-primary btn-sm" onclick="runAI('<?= base_url('ai/cost') ?>','cost-result')"><i class="bi bi-play-fill me-1"></i>Analyze</button></div>
    <div class="fm-card-body"><p class="small text-muted mb-3">Reviews 6-month expense trends and recommends cost savings opportunities.</p><div id="cost-result" class="ai-box" style="display:none"></div></div>
  </div></div>

  <div class="col-lg-6"><div class="fm-card h-100">
    <div class="card-header-fm"><h5><i class="bi bi-person-check me-2 text-info"></i>Smart Technician Assignment</h5></div>
    <div class="fm-card-body"><p class="small text-muted mb-3">Recommends the best available technician based on current workload.</p>
    <div class="d-flex gap-2 mb-3">
      <input type="number" id="wo-id-input" class="form-control form-control-sm" placeholder="Enter Work Order ID">
      <button class="btn btn-fm-primary btn-sm" onclick="smartAssign()"><i class="bi bi-person-check me-1"></i>Recommend</button>
    </div>
    <div id="assign-result" class="ai-box" style="display:none"></div>
    </div>
  </div></div>
</div>
<style>
.ai-box{background:#f8fafc;border:1px solid #e8edf3;border-radius:8px;padding:14px;font-size:.82rem;line-height:1.7;white-space:pre-wrap;max-height:260px;overflow-y:auto;margin-top:4px;font-family:'DM Sans',monospace}
</style>
<?= $this->section('scripts') ?>
<script>
function runAI(url, resultId) {
  const el = document.getElementById(resultId);
  el.style.display = 'block';
  el.innerHTML = '<span class="text-muted"><span class="spinner-border spinner-border-sm me-2"></span>Analyzing data...</span>';
  fetch(url).then(r => r.json()).then(d => {
    el.textContent = d.analysis || d.suggestions || 'No data available.';
  }).catch(e => { el.textContent = 'Error: ' + e; });
}
function smartAssign() {
  const id = document.getElementById('wo-id-input').value;
  if (!id) return alert('Please enter a Work Order ID');
  const el = document.getElementById('assign-result');
  el.style.display = 'block';
  el.innerHTML = '<span class="text-muted"><span class="spinner-border spinner-border-sm me-2"></span>Analyzing...</span>';
  const fd = new FormData(); fd.append('wo_id', id);
  fetch('<?= base_url('ai/assign') ?>', { method: 'POST', body: fd })
    .then(r => r.json()).then(d => {
      try {
        const o = JSON.parse(d.recommendation);
        el.textContent = '✅ Recommended: ' + o.name + '\n\n📝 Reason: ' + o.reason;
      } catch { el.textContent = d.recommendation || d.message || 'No recommendation'; }
    });
}
</script>
<?= $this->endSection() ?>
<?= $this->endSection() ?>
