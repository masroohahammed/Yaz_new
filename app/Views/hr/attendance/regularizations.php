<?= $this->extend('layouts/hr') ?>
<?= $this->section('hr_content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-pencil-square me-2 text-primary"></i>Attendance Regularizations</h1>
    <p class="text-muted small mb-0">Correction requests — original punches preserved; approved changes link via regularization_id.</p>
  </div>
  <a href="<?= base_url('hr/attendance') ?>" class="btn btn-fm-outline btn-sm">Back to Attendance</a>
</div>

<?php if (!empty($migrationRequired)): ?>
<div class="alert alert-warning">Run <code>database/patch_hr_upgrade_complete.sql</code> to enable regularizations.</div>
<?php else: ?>

<?php if ($canAdjust ?? false): ?>
<div class="fm-card mb-3">
  <div class="card-header-fm"><h5>Submit Correction</h5></div>
  <div class="fm-card-body">
    <?= form_open(base_url('hr/attendance/regularize')) ?>
    <?= csrf_field() ?>
    <div class="row g-2">
      <div class="col-md-2"><label class="small">Employee ID</label><input type="number" name="employee_id" class="form-control form-control-sm" required></div>
      <div class="col-md-2"><label class="small">Date</label><input type="date" name="attendance_date" class="form-control form-control-sm" value="<?= esc(date('Y-m-d')) ?>" required></div>
      <div class="col-md-2"><label class="small">Check In</label><input type="datetime-local" name="requested_check_in" class="form-control form-control-sm"></div>
      <div class="col-md-2"><label class="small">Check Out</label><input type="datetime-local" name="requested_check_out" class="form-control form-control-sm"></div>
      <div class="col-md-2"><label class="small">Status</label><select name="requested_status" class="form-select form-select-sm"><option value="present">Present</option><option value="late">Late</option><option value="half_day">Half Day</option><option value="absent">Absent</option></select></div>
      <div class="col-md-4"><label class="small">Reason</label><input name="reason" class="form-control form-control-sm" required></div>
      <div class="col-md-2 d-flex align-items-end"><button type="submit" class="btn btn-fm-primary btn-sm w-100">Submit</button></div>
    </div>
    <?= form_close() ?>
  </div>
</div>
<?php endif; ?>

<div class="fm-card">
  <div class="card-header-fm"><h5>Pending Approval</h5></div>
  <div class="fm-card-body p-0"><div class="table-responsive">
    <table class="fm-table">
      <thead><tr><th>Employee</th><th>Date</th><th>Requested</th><th>Reason</th><?php if ($canApprove ?? false): ?><th></th><?php endif; ?></tr></thead>
      <tbody>
      <?php foreach ($pending as $p): ?>
      <tr>
        <td><?= esc($p['employee_name'] ?? '—') ?> <span class="text-muted small">(<?= esc($p['emp_code'] ?? '') ?>)</span></td>
        <td><?= date('d M Y', strtotime($p['attendance_date'])) ?></td>
        <td class="small">
          <?= $p['requested_check_in'] ? 'In: '.date('H:i', strtotime($p['requested_check_in'])) : '' ?>
          <?= $p['requested_check_out'] ? ' Out: '.date('H:i', strtotime($p['requested_check_out'])) : '' ?>
          <?= !empty($p['requested_status']) ? ' · '.ucfirst(str_replace('_',' ',$p['requested_status'])) : '' ?>
        </td>
        <td class="small"><?= esc($p['reason'] ?? '') ?></td>
        <?php if ($canApprove ?? false): ?>
        <td class="text-nowrap">
          <?= form_open(base_url('hr/attendance/regularizations/approve/'.$p['id']), ['class' => 'd-inline']) ?>
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-sm btn-success">Approve</button>
          <?= form_close() ?>
          <?= form_open(base_url('hr/attendance/regularizations/reject/'.$p['id']), ['class' => 'd-inline']) ?>
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-sm btn-fm-outline">Reject</button>
          <?= form_close() ?>
        </td>
        <?php endif; ?>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($pending)): ?><tr><td colspan="5" class="text-center py-4 text-muted">No pending regularizations.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div></div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>
