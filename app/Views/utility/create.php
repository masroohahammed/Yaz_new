<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-lightning-charge me-2 text-warning"></i>Log Utility Reading</h1></div><a href="<?= base_url('utility') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a></div>
<div class="row"><div class="col-lg-7">
<div class="fm-card"><div class="fm-card-body">
<?= form_open('utility/store') ?>
<div class="row g-3">
  <div class="col-md-6"><label class="form-label">Facility <span class="text-danger">*</span></label><select name="facility_id" class="form-select" required><option value="">Select...</option><?php foreach($facilities as $f): ?><option value="<?= $f['id'] ?>"><?= esc($f['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-6"><label class="form-label">Type <span class="text-danger">*</span></label><select name="type" class="form-select" required><option value="electricity">Electricity</option><option value="water">Water</option><option value="gas">Gas</option><option value="diesel">Diesel</option><option value="other">Other</option></select></div>
  <div class="col-md-6"><label class="form-label">Reading Date <span class="text-danger">*</span></label><input type="date" name="reading_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
  <div class="col-md-6"><label class="form-label">Units Consumed <span class="text-danger">*</span></label><input type="number" name="units" class="form-control" placeholder="e.g. 1500 kWh" step="0.01" required></div>
  <div class="col-md-6"><label class="form-label">Meter Reading</label><input type="number" name="meter_reading" class="form-control" placeholder="Current meter value" step="0.01"></div>
  <div class="col-md-6"><label class="form-label">Cost (<?= $currency ?>) <span class="text-danger">*</span></label><input type="number" name="cost" class="form-control" placeholder="0.00" step="0.01" required></div>
  <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2" placeholder="Any notes..."></textarea></div>
  <div class="col-12"><button type="submit" class="btn btn-fm-primary"><i class="bi bi-save me-2"></i>Save Reading</button></div>
</div>
<?= form_close() ?>
</div></div>
</div></div>
<?= $this->endSection() ?>
