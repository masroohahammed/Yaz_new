<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-shield-check me-2 text-success"></i>Compliance & Safety</h1><div class="small text-muted">Audits, incidents, and regulatory compliance</div></div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= base_url('compliance/unit-inspections') ?>" class="btn btn-success btn-sm"><i class="bi bi-door-open me-1"></i>Move-In / Move-Out</a>
    <a href="<?= base_url('compliance/audit/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-clipboard-check me-1"></i>New Audit</a>
    <a href="<?= base_url('compliance/incident/create') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-exclamation-triangle me-1"></i>Report Incident</a>
  </div>
</div>
<?php if(!empty($expiring)): ?>
<div class="alert alert-warning border-0 rounded-3 mb-3"><i class="bi bi-exclamation-triangle-fill me-2"></i><strong><?= count($expiring) ?> document(s)</strong> expiring within 30 days — please review compliance documents.</div>
<?php endif; ?>
<div class="row g-3">
<div class="col-lg-7">
  <div class="fm-card"><div class="card-header-fm"><h5><i class="bi bi-clipboard-check me-2"></i>Recent Audits</h5></div>
  <div class="fm-card-body p-0"><div class="table-responsive">
  <table class="fm-table"><thead><tr><th>Date</th><th>Facility</th><th>Type</th><th>Score</th><th>Status</th></tr></thead><tbody>
  <?php foreach($audits as $a): ?>
  <tr>
    <td class="small"><?= date('d M Y', strtotime($a['audit_date'])) ?></td>
    <td class="small"><?= esc($a['facility_name']) ?></td>
    <td class="small"><?= esc($a['audit_type']) ?></td>
    <td><?php if($a['score']!==null): ?><span class="fw-bold <?= $a['score']>=80?'text-success':($a['score']>=60?'text-warning':'text-danger') ?>"><?= $a['score'] ?>%</span><?php else: ?>—<?php endif; ?></td>
    <td><span class="fm-badge badge-status-<?= $a['status']==='passed'?'completed':($a['status']==='failed'?'cancelled':$a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
  </tr>
  <?php endforeach; ?>
  <?php if(empty($audits)): ?><tr><td colspan="5" class="text-center py-4 text-muted">No audits recorded yet</td></tr><?php endif; ?>
  </tbody></table></div></div></div>
</div>
<div class="col-lg-5">
  <div class="fm-card"><div class="card-header-fm"><h5><i class="bi bi-exclamation-triangle me-2"></i>Recent Incidents</h5></div>
  <div class="fm-card-body p-0"><div class="table-responsive">
  <table class="fm-table"><thead><tr><th>Date</th><th>Title</th><th>Severity</th><th>Status</th></tr></thead><tbody>
  <?php foreach($incidents as $i): ?>
  <tr>
    <td class="small"><?= date('d M Y', strtotime($i['incident_date'])) ?></td>
    <td class="small"><?= esc($i['title']) ?></td>
    <td><span class="fm-badge badge-priority-<?= $i['severity'] ?>"><?= ucfirst($i['severity']) ?></span></td>
    <td><span class="fm-badge badge-status-<?= $i['status']==='resolved'||$i['status']==='closed'?'completed':'assigned' ?>"><?= ucfirst($i['status']) ?></span></td>
  </tr>
  <?php endforeach; ?>
  <?php if(empty($incidents)): ?><tr><td colspan="4" class="text-center py-4 text-muted">No incidents reported</td></tr><?php endif; ?>
  </tbody></table></div></div></div>
</div>
</div>
<?= $this->endSection() ?>
