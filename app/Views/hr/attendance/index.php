<?= $this->extend('layouts/hr') ?>
<?= $this->section('hr_content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-clock-history me-2 text-primary"></i>Attendance</h1>
    <p class="text-muted small mb-0">Processed attendance with shift and facility context. Raw punch logs are immutable.</p>
  </div>
  <div class="d-flex gap-2">
    <?php if ($canApprove ?? false): ?>
    <a href="<?= base_url('hr/attendance/regularizations') ?>" class="btn btn-fm-outline btn-sm">Regularizations</a>
    <?php endif; ?>
    <?php if ($canAdjust ?? false): ?>
    <a href="<?= base_url('hr/shifts') ?>" class="btn btn-fm-outline btn-sm">Shifts</a>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($migrationRequired)): ?>
<div class="alert alert-warning">Run <code>database/patch_hr_upgrade_complete.sql</code> to enable HR attendance upgrades.</div>
<?php else: ?>

<?= form_open(base_url('hr/attendance'), ['method' => 'get', 'class' => 'fm-card mb-3']) ?>
<div class="fm-card-body"><div class="row g-2 align-items-end">
  <div class="col-md-3"><label class="form-label small">Month</label><input type="month" name="month" class="form-control form-control-sm" value="<?= esc($filters['month'] ?? date('Y-m')) ?>"></div>
  <div class="col-md-3"><label class="form-label small">Employee</label><select name="emp_id" class="form-select form-select-sm"><option value="">All</option><?php foreach ($employees as $e): ?><option value="<?= (int)$e['id'] ?>" <?= ($filters['employee_id'] ?? '') == $e['id'] ? 'selected' : '' ?>><?= esc($e['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-2"><button type="submit" class="btn btn-fm-primary btn-sm w-100">Filter</button></div>
</div></div>
<?= form_close() ?>

<div class="fm-card"><div class="fm-card-body p-0"><div class="table-responsive">
<table class="fm-table"><thead><tr><th>Date</th><th>Employee</th><th>Facility</th><th>Shift</th><th>In</th><th>Out</th><th>Hours</th><th>OT</th><th>Status</th><?php if ($canAdjust ?? false): ?><th></th><?php endif; ?></tr></thead><tbody>
<?php foreach ($records as $r): ?>
<tr>
  <td class="small"><?= date('d M Y', strtotime($r['date'])) ?></td>
  <td class="small fw-semibold"><?= esc($r['employee_name'] ?? '—') ?> <span class="text-muted">(<?= esc($r['emp_code'] ?? '') ?>)</span></td>
  <td class="small"><?= esc($r['facility_name'] ?? '—') ?></td>
  <td class="small"><?= esc($r['shift_name'] ?? '—') ?></td>
  <td class="small"><?= $r['check_in'] ? date('H:i', strtotime($r['check_in'])) : '—' ?></td>
  <td class="small"><?= $r['check_out'] ? date('H:i', strtotime($r['check_out'])) : '—' ?></td>
  <td class="small"><?= isset($r['hours_worked']) ? number_format((float)$r['hours_worked'], 1).'h' : '—' ?></td>
  <td class="small"><?= !empty($r['overtime_hrs']) ? number_format((float)$r['overtime_hrs'], 1).'h' : '—' ?></td>
  <td><span class="fm-badge badge-status-<?= esc($r['status']) ?>"><?= ucfirst($r['status']) ?></span></td>
  <?php if ($canAdjust ?? false): ?>
  <td><button type="button" class="btn btn-sm btn-fm-outline" data-bs-toggle="collapse" data-bs-target="#adj-<?= (int)$r['id'] ?>">Adjust</button></td>
  <?php endif; ?>
</tr>
<?php if ($canAdjust ?? false): ?>
<tr class="collapse" id="adj-<?= (int)$r['id'] ?>"><td colspan="10">
  <?= form_open(base_url('hr/attendance/adjust/'.(int)$r['id'])) ?>
  <?= csrf_field() ?>
  <div class="row g-2 p-2 bg-light rounded">
    <div class="col-md-2"><label class="small">Check In</label><input type="datetime-local" name="check_in" class="form-control form-control-sm" value="<?= $r['check_in'] ? date('Y-m-d\TH:i', strtotime($r['check_in'])) : '' ?>"></div>
    <div class="col-md-2"><label class="small">Check Out</label><input type="datetime-local" name="check_out" class="form-control form-control-sm" value="<?= $r['check_out'] ? date('Y-m-d\TH:i', strtotime($r['check_out'])) : '' ?>"></div>
    <div class="col-md-2"><label class="small">Status</label><select name="status" class="form-select form-select-sm"><?php foreach (['present','absent','late','half_day','leave'] as $st): ?><option value="<?= $st ?>" <?= ($r['status'] ?? '') === $st ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$st)) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><label class="small">Notes</label><input name="notes" class="form-control form-control-sm" value="<?= esc($r['notes'] ?? '') ?>"></div>
    <div class="col-md-2 d-flex align-items-end"><button type="submit" class="btn btn-fm-primary btn-sm w-100">Save</button></div>
  </div>
  <?= form_close() ?>
</td></tr>
<?php endif; ?>
<?php endforeach; ?>
<?php if (empty($records)): ?><tr><td colspan="10" class="text-center py-4 text-muted">No attendance records</td></tr><?php endif; ?>
</tbody></table></div></div></div>
<?php endif; ?>
<?= $this->endSection() ?>
