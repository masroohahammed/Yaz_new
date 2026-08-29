<?= $this->extend('layouts/hr') ?>
<?= $this->section('hr_content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-bank me-2 text-primary"></i>Employee Loans</h1></div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('hr/salary') ?>" class="btn btn-fm-outline btn-sm">Salary</a>
    <a href="<?= base_url('hr/compensation/advances') ?>" class="btn btn-fm-outline btn-sm">Advances</a>
  </div>
</div>

<?php if (!empty($migrationRequired)): ?>
<div class="alert alert-warning">Run <code>database/patch_hr_upgrade_complete.sql</code> to enable loans.</div>
<?php else: ?>

<div class="fm-card mb-3">
  <div class="card-header-fm"><h5>Request Loan</h5></div>
  <div class="fm-card-body">
    <?= form_open(base_url('hr/compensation/loans/store')) ?>
    <?= csrf_field() ?>
    <div class="row g-2">
      <div class="col-md-2"><label class="small">Employee ID</label><input type="number" name="employee_id" class="form-control form-control-sm" required></div>
      <div class="col-md-2"><label class="small">Principal</label><input type="number" step="0.01" name="principal" class="form-control form-control-sm" required></div>
      <div class="col-md-2"><label class="small">Tenure (months)</label><input type="number" name="tenure_months" class="form-control form-control-sm" value="12" min="1"></div>
      <div class="col-md-2"><label class="small">Interest %</label><input type="number" step="0.01" name="interest_rate" class="form-control form-control-sm" value="0"></div>
      <div class="col-md-4"><label class="small">Reason</label><input name="reason" class="form-control form-control-sm" required></div>
      <div class="col-12"><button type="submit" class="btn btn-fm-primary btn-sm">Submit</button></div>
    </div>
    <?= form_close() ?>
  </div>
</div>

<?php if ($canApprove ?? false): ?>
<div class="fm-card mb-3"><div class="card-header-fm"><h5>Pending Approval</h5></div>
<div class="fm-card-body p-0"><table class="fm-table"><thead><tr><th>Employee</th><th>Principal</th><th>Tenure</th><th>Reason</th><th></th></tr></thead><tbody>
<?php foreach ($pending as $p): ?>
<tr>
  <td><?= esc($p['employee_name']) ?> <span class="text-muted small">(<?= esc($p['emp_code']) ?>)</span></td>
  <td><?= number_format((float)$p['principal'], 2) ?></td>
  <td><?= (int)$p['tenure_months'] ?> mo</td>
  <td class="small"><?= esc($p['reason'] ?? '') ?></td>
  <td>
    <?= form_open(base_url('hr/compensation/loans/approve/'.$p['id']), ['class'=>'d-inline']) ?><?= csrf_field() ?><button class="btn btn-sm btn-success">Approve</button><?= form_close() ?>
    <?= form_open(base_url('hr/compensation/loans/reject/'.$p['id']), ['class'=>'d-inline']) ?><?= csrf_field() ?><button class="btn btn-sm btn-fm-outline">Reject</button><?= form_close() ?>
  </td>
</tr>
<?php endforeach; ?>
<?php if (empty($pending)): ?><tr><td colspan="5" class="text-center py-3 text-muted">No pending loans.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php endif; ?>
<?php endif; ?>
<?= $this->endSection() ?>
