<?= $this->extend('layouts/hr') ?>
<?= $this->section('hr_content') ?>
<div class="page-header"><div><h1><i class="bi bi-clock-history me-2"></i>Attendance</h1></div></div>
<?= form_open('employees/attendance',['method'=>'get','class'=>'fm-card mb-3']) ?>
<div class="fm-card-body"><div class="row g-2 align-items-end">
  <div class="col-md-3"><label class="form-label small">Month</label><input type="month" name="month" class="form-control form-control-sm" value="<?= $month ?>"></div>
  <div class="col-md-3"><label class="form-label small">Employee</label><select name="emp_id" class="form-select form-select-sm"><option value="">All Employees</option><?php foreach($employees as $e): ?><option value="<?= $e['id'] ?>" <?= $selectedEmp==$e['id']?'selected':'' ?>><?= esc($e['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-2"><button type="submit" class="btn btn-fm-primary btn-sm w-100">Filter</button></div>
</div></div>
<?= form_close() ?>
<div class="fm-card"><div class="fm-card-body p-0"><div class="table-responsive">
<table class="fm-table"><thead><tr><th>Date</th><th>Employee</th><th>Check In</th><th>Check Out</th><th>Hours</th><th>Overtime</th><th>Status</th></tr></thead><tbody>
<?php foreach($records as $r): ?>
<tr>
  <td class="small"><?= date('d M Y',strtotime($r['date'])) ?></td>
  <td class="fw-semibold small"><?= esc($r['name']??'—') ?></td>
  <td class="small"><?= $r['check_in'] ? date('H:i',strtotime($r['check_in'])) : '—' ?></td>
  <td class="small"><?= $r['check_out'] ? date('H:i',strtotime($r['check_out'])) : '—' ?></td>
  <td class="small"><?= isset($r['hours_worked']) ? number_format($r['hours_worked'],1).'h' : '—' ?></td>
  <td class="small <?= !empty($r['overtime_hrs'])&&$r['overtime_hrs']>0?'text-warning fw-semibold':'' ?>"><?= !empty($r['overtime_hrs'])&&$r['overtime_hrs']>0 ? number_format($r['overtime_hrs'],1).'h' : '—' ?></td>
  <td><span class="fm-badge badge-status-<?= $r['status']==='present'?'completed':($r['status']==='absent'?'cancelled':($r['status']==='late'?'assigned':'new')) ?>"><?= ucfirst($r['status']) ?></span></td>
</tr>
<?php endforeach; ?>
<?php if(empty($records)): ?><tr><td colspan="7" class="text-center py-4 text-muted">No attendance records</td></tr><?php endif; ?>
</tbody></table></div></div></div>
<?= $this->endSection() ?>
