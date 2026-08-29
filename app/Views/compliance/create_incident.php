<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-exclamation-triangle me-2"></i>Report Incident</h1></div><a href="<?= base_url('compliance') ?>" class="btn btn-fm-outline btn-sm">← Back</a></div>
<div class="row"><div class="col-lg-7"><div class="fm-card"><div class="fm-card-body">
<?= form_open('compliance/incident/store') ?>
<div class="row g-3">
  <div class="col-md-6"><label class="form-label">Facility *</label><select name="facility_id" class="form-select" required><option value="">Select...</option><?php foreach($facilities as $f): ?><option value="<?= $f['id'] ?>"><?= esc($f['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-6"><label class="form-label">Incident Date *</label><input type="date" name="incident_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
  <div class="col-12"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" placeholder="Brief incident description" required></div>
  <div class="col-md-6"><label class="form-label">Severity *</label><select name="severity" class="form-select" required><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option><option value="critical">Critical</option></select></div>
  <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4" placeholder="Detailed description of what happened..."></textarea></div>
  <div class="col-12"><button type="submit" class="btn btn-fm-primary">Report Incident</button></div>
</div>
<?= form_close() ?>
</div></div></div></div>
<?= $this->endSection() ?>
