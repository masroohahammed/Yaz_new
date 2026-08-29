<?= $this->extend('layouts/hr') ?>
<?= $this->section('hr_content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-calendar2-range me-2 text-primary"></i>Shift Master</h1>
    <p class="text-muted small mb-0">Define shifts and assign roster to employees (replaces inline shift times over time).</p>
  </div>
  <a href="<?= base_url('hr/attendance') ?>" class="btn btn-fm-outline btn-sm">Attendance</a>
</div>

<?php if (!empty($migrationRequired)): ?>
<div class="alert alert-warning">Run <code>database/patch_hr_upgrade_complete.sql</code> to enable shift management.</div>
<?php else: ?>

<div class="row g-3">
  <div class="col-lg-5">
    <div class="fm-card">
      <div class="card-header-fm"><h5>Shifts</h5></div>
      <div class="fm-card-body p-0"><table class="fm-table table-sm">
        <thead><tr><th>Code</th><th>Name</th><th>Hours</th><th>Grace</th></tr></thead>
        <tbody>
        <?php foreach ($shifts as $s): ?>
        <tr>
          <td class="fw-semibold small"><?= esc($s['code']) ?></td>
          <td class="small"><?= esc($s['name']) ?></td>
          <td class="small"><?= substr($s['start_time'],0,5) ?> – <?= substr($s['end_time'],0,5) ?><?= !empty($s['is_overnight']) ? ' (+1)' : '' ?></td>
          <td class="small"><?= (int)$s['grace_in_minutes'] ?> / <?= (int)$s['grace_out_minutes'] ?> min</td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>
    </div>
    <div class="fm-card mt-3">
      <div class="card-header-fm"><h5>Add Shift</h5></div>
      <div class="fm-card-body">
        <?= form_open(base_url('hr/shifts/store')) ?>
        <?= csrf_field() ?>
        <div class="row g-2">
          <div class="col-4"><label class="small">Code</label><input name="code" class="form-control form-control-sm" placeholder="DAY" required></div>
          <div class="col-8"><label class="small">Name</label><input name="name" class="form-control form-control-sm" required></div>
          <div class="col-6"><label class="small">Start</label><input type="time" name="start_time" class="form-control form-control-sm" value="08:00"></div>
          <div class="col-6"><label class="small">End</label><input type="time" name="end_time" class="form-control form-control-sm" value="17:00"></div>
          <div class="col-4"><label class="small">Break (min)</label><input type="number" name="break_minutes" class="form-control form-control-sm" value="60"></div>
          <div class="col-4"><label class="small">Grace In</label><input type="number" name="grace_in_minutes" class="form-control form-control-sm" value="15"></div>
          <div class="col-4"><label class="small">Grace Out</label><input type="number" name="grace_out_minutes" class="form-control form-control-sm" value="15"></div>
          <div class="col-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="is_overnight" value="1" id="overnight"><label class="form-check-label small" for="overnight">Overnight shift</label></div></div>
          <div class="col-12"><button type="submit" class="btn btn-fm-primary btn-sm w-100">Save Shift</button></div>
        </div>
        <?= form_close() ?>
      </div>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="fm-card">
      <div class="card-header-fm"><h5>Assign Shift to Employee</h5></div>
      <div class="fm-card-body">
        <?= form_open(base_url('hr/shifts/assign')) ?>
        <?= csrf_field() ?>
        <div class="row g-2">
          <div class="col-md-6"><label class="small">Employee</label><select name="employee_id" class="form-select form-select-sm" required><option value="">Select…</option><?php foreach ($employees as $e): ?><option value="<?= (int)$e['id'] ?>"><?= esc($e['name']) ?> (<?= esc($e['emp_code']) ?>)</option><?php endforeach; ?></select></div>
          <div class="col-md-6"><label class="small">Shift</label><select name="shift_id" class="form-select form-select-sm" required><?php foreach ($shifts as $s): ?><option value="<?= (int)$s['id'] ?>"><?= esc($s['name']) ?></option><?php endforeach; ?></select></div>
          <div class="col-md-6"><label class="small">Facility (optional)</label><select name="facility_id" class="form-select form-select-sm"><option value="">—</option><?php foreach ($facilities as $f): ?><option value="<?= (int)$f['id'] ?>"><?= esc($f['name']) ?></option><?php endforeach; ?></select></div>
          <div class="col-md-3"><label class="small">From</label><input type="date" name="effective_from" class="form-control form-control-sm" value="<?= esc(date('Y-m-d')) ?>"></div>
          <div class="col-md-3"><label class="small">To</label><input type="date" name="effective_to" class="form-control form-control-sm"></div>
          <div class="col-12"><button type="submit" class="btn btn-fm-primary btn-sm">Assign Shift</button></div>
        </div>
        <?= form_close() ?>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>
