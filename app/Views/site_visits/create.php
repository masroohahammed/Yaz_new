<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1>Schedule Site Visit</h1></div>
<div class="fm-card"><div class="fm-card-body">
<?= form_open(base_url('site-visits/store'), ['class'=>'fm-submit-form']) ?>
<?= csrf_field() ?>
<div class="row g-3">
  <div class="col-md-6"><label class="form-label">Facility</label>
    <select name="facility_id" class="form-select"><option value="">— Optional —</option>
      <?php foreach ($facilities as $f): ?><option value="<?= $f['id'] ?>"><?= esc($f['name']) ?></option><?php endforeach; ?>
    </select></div>
  <div class="col-md-6"><label class="form-label">Unit</label>
    <select name="unit_id" class="form-select"><option value="">— Optional —</option>
      <?php foreach ($units as $u): ?><option value="<?= $u['id'] ?>"><?= esc($u['unit_number'] ?? $u['id']) ?></option><?php endforeach; ?>
    </select></div>
  <div class="col-md-6"><label class="form-label">Scheduled date/time</label>
    <input type="datetime-local" name="scheduled_at" class="form-control" required></div>
  <div class="col-md-6"><label class="form-label">Technician</label>
    <select name="technician_id" class="form-select"><option value="">—</option>
      <?php foreach ($technicians as $t): ?><option value="<?= $t['id'] ?>"><?= esc($t['name']) ?></option><?php endforeach; ?>
    </select></div>
  <div class="col-12"><label class="form-label">Purpose</label><textarea name="purpose" class="form-control" rows="2"></textarea></div>
  <div class="col-12"><label class="form-label">Requirements to collect</label><textarea name="requirements" class="form-control" rows="3"></textarea></div>
</div>
<button type="submit" class="btn btn-fm-primary mt-3 fm-submit-btn">Schedule</button>
<?= form_close() ?>
</div></div>
<?= $this->endSection() ?>
