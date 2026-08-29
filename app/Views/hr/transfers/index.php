<?= $this->extend('layouts/hr') ?>
<?= $this->section('hr_content') ?>
<div class="page-header"><div><h1><i class="bi bi-arrow-left-right me-2 text-primary"></i>Employee Transfers</h1></div>
<a href="<?= base_url('hr/approvals') ?>" class="btn btn-fm-outline btn-sm">Approvals</a></div>
<?php if (!empty($migrationRequired)): ?><div class="alert alert-warning">Run consolidated SQL patch.</div>
<?php else: ?>
<div class="fm-card mb-3"><div class="card-header-fm"><h5>Request Transfer</h5></div>
<div class="fm-card-body">
<?= form_open(base_url('hr/transfers/store')) ?><?= csrf_field() ?>
<div class="row g-2">
<div class="col-md-2"><label class="small">Employee ID</label><input type="number" name="employee_id" class="form-control form-control-sm" required></div>
<div class="col-md-2"><label class="small">To Department</label><select name="to_department_id" class="form-select form-select-sm"><option value="">—</option><?php foreach ($departments as $d): ?><option value="<?= (int)$d['id'] ?>"><?= esc($d['name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><label class="small">To Facility</label><select name="to_facility_id" class="form-select form-select-sm"><option value="">—</option><?php foreach ($facilities as $f): ?><option value="<?= (int)$f['id'] ?>"><?= esc($f['name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><label class="small">To Branch</label><select name="to_operating_company_id" class="form-select form-select-sm"><option value="">—</option><?php foreach ($branches as $b): ?><option value="<?= (int)$b['id'] ?>"><?= esc($b['name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-2"><label class="small">Effective</label><input type="date" name="effective_date" class="form-control form-control-sm" value="<?= esc(date('Y-m-d')) ?>"></div>
<div class="col-md-2"><label class="small">Reason</label><input name="reason" class="form-control form-control-sm" required></div>
<div class="col-12"><button class="btn btn-fm-primary btn-sm">Submit</button></div>
</div><?= form_close() ?>
</div></div>
<div class="fm-card"><div class="card-header-fm"><h5>Pending</h5></div>
<div class="fm-card-body p-0"><table class="fm-table"><thead><tr><th>Employee</th><th>Effective</th><th>Reason</th></tr></thead><tbody>
<?php foreach ($pending as $p): ?><tr><td><?= esc($p['employee_name']) ?></td><td><?= date('d M Y', strtotime($p['effective_date'])) ?></td><td class="small"><?= esc($p['reason'] ?? '') ?></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<?php endif; ?>
<?= $this->endSection() ?>
