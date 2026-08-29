<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-clipboard-check me-2"></i>New Safety Audit</h1></div><a href="<?= base_url('compliance') ?>" class="btn btn-fm-outline btn-sm">← Back</a></div>
<div class="row"><div class="col-lg-7"><div class="fm-card"><div class="fm-card-body">
<?= form_open('compliance/audit/store') ?>
<div class="row g-3">
  <div class="col-md-6"><label class="form-label">Facility *</label><select name="facility_id" class="form-select" required><option value="">Select...</option><?php foreach($facilities as $f): ?><option value="<?= $f['id'] ?>"><?= esc($f['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-6"><label class="form-label">Audit Type *</label><select name="audit_type" class="form-select" required><option value="Fire Safety">Fire Safety</option><option value="Electrical Safety">Electrical Safety</option><option value="HVAC Compliance">HVAC Compliance</option><option value="Environmental">Environmental</option><option value="General Safety">General Safety</option><option value="Regulatory">Regulatory</option></select></div>
  <div class="col-md-6"><label class="form-label">Audit Date *</label><input type="date" name="audit_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
  <div class="col-md-6"><label class="form-label">Score (0-100)</label><input type="number" name="score" class="form-control" min="0" max="100" placeholder="e.g. 85"></div>
  <div class="col-md-6"><label class="form-label">Status</label><select name="status" class="form-select"><option value="open">Open</option><option value="in_progress">In Progress</option><option value="passed">Passed</option><option value="failed">Failed</option><option value="closed">Closed</option></select></div>
  <div class="col-12"><label class="form-label">Findings / Notes</label><textarea name="findings" class="form-control" rows="4"></textarea></div>
  <div class="col-12"><button type="submit" class="btn btn-fm-primary">Save Audit</button></div>
</div>
<?= form_close() ?>
</div></div></div></div>
<?= $this->endSection() ?>
