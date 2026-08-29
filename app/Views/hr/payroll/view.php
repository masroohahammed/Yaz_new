<?= $this->extend('layouts/hr') ?>
<?= $this->section('hr_content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-calculator me-2 text-primary"></i><?= esc($run['run_number'] ?? 'Payroll Run') ?></h1>
    <p class="text-muted small mb-0"><?= date('d M Y', strtotime($run['period_start'])) ?> – <?= date('d M Y', strtotime($run['period_end'])) ?> · <?= esc($run['branch_name'] ?? 'All branches') ?></p>
  </div>
  <a href="<?= base_url('hr/payroll') ?>" class="btn btn-fm-outline btn-sm">Back</a>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-3"><div class="fm-card"><div class="fm-card-body small"><div class="text-muted">Status</div><span class="fm-badge badge-status-<?= esc($run['status']) ?>"><?= esc($statuses[$run['status']] ?? $run['status']) ?></span></div></div></div>
  <div class="col-md-3"><div class="fm-card"><div class="fm-card-body small"><div class="text-muted">Employees</div><strong><?= (int)$run['employee_count'] ?></strong></div></div></div>
  <div class="col-md-3"><div class="fm-card"><div class="fm-card-body small"><div class="text-muted">Total Net</div><strong><?= number_format((float)$run['total_net'], 2) ?> <?= esc($run['currency']) ?></strong></div></div></div>
  <div class="col-md-3"><div class="fm-card"><div class="fm-card-body small"><div class="text-muted">GL Journal</div><?= !empty($run['journal_entry_id']) ? '#'.$run['journal_entry_id'] : '—' ?></div></div></div>
</div>

<div class="d-flex flex-wrap gap-2 mb-3">
  <?php if (in_array($run['status'], ['draft','calculated','approved'], true)): ?>
  <?= form_open(base_url('hr/payroll/calculate/'.$run['id']), ['class'=>'d-inline']) ?><?= csrf_field() ?><button class="btn btn-fm-primary btn-sm">Calculate</button><?= form_close() ?>
  <?php endif; ?>
  <?php if ($run['status'] === 'calculated' && ($canApprove ?? false)): ?>
  <?= form_open(base_url('hr/payroll/approve/'.$run['id']), ['class'=>'d-inline']) ?><?= csrf_field() ?><button class="btn btn-success btn-sm">Approve</button><?= form_close() ?>
  <?php endif; ?>
  <?php if ($run['status'] === 'approved' && ($canApprove ?? false)): ?>
  <?= form_open(base_url('hr/payroll/lock/'.$run['id']), ['class'=>'d-inline']) ?><?= csrf_field() ?><button class="btn btn-warning btn-sm">Lock</button><?= form_close() ?>
  <?php endif; ?>
  <?php if ($run['status'] === 'locked' && ($canApprove ?? false)): ?>
  <?= form_open(base_url('hr/payroll/post-gl/'.$run['id']), ['class'=>'d-inline']) ?><?= csrf_field() ?><button class="btn btn-fm-primary btn-sm">Post to GL</button><?= form_close() ?>
  <a href="<?= base_url('hr/wps?run_id='.$run['id']) ?>" class="btn btn-fm-outline btn-sm">Generate WPS</a>
  <?php endif; ?>
  <?php if (in_array($run['status'], ['locked','posted'], true) && ($canUnlock ?? false)): ?>
  <?= form_open(base_url('hr/payroll/unlock/'.$run['id']), ['class'=>'d-inline']) ?>
  <?= csrf_field() ?>
  <input type="text" name="reason" class="form-control form-control-sm d-inline-block w-auto" placeholder="Unlock reason" required>
  <button class="btn btn-fm-outline btn-sm">Unlock</button>
  <?= form_close() ?>
  <?php endif; ?>
  <?php if (!in_array($run['status'], ['locked','posted','cancelled'], true)): ?>
  <?= form_open(base_url('hr/payroll/cancel/'.$run['id']), ['class'=>'d-inline']) ?><?= csrf_field() ?><button class="btn btn-fm-outline btn-sm text-danger">Cancel</button><?= form_close() ?>
  <?php endif; ?>
</div>

<?php if (!empty($validation['errors']) || !empty($validation['warnings'])): ?>
<div class="alert alert-warning small">
  <?php if (!empty($validation['errors'])): ?><div><strong>Errors:</strong> <?= esc(implode('; ', $validation['errors'])) ?></div><?php endif; ?>
  <?php if (!empty($validation['warnings'])): ?><div><strong>Warnings:</strong> <?= esc(implode('; ', $validation['warnings'])) ?></div><?php endif; ?>
</div>
<?php endif; ?>

<div class="fm-card"><div class="card-header-fm"><h5>Employee Lines</h5></div>
<div class="fm-card-body p-0"><table class="fm-table table-sm"><thead><tr><th>Employee</th><th>IBAN</th><th>Gross</th><th>Deductions</th><th>Net</th><th>WPS</th><th>Status</th></tr></thead><tbody>
<?php foreach ($lines as $l): ?>
<tr>
  <td class="small"><?= esc($l['employee_name']) ?> <span class="text-muted">(<?= esc($l['emp_code']) ?>)</span></td>
  <td class="small"><?= esc($l['iban'] ?: '—') ?></td>
  <td><?= number_format((float)$l['gross_salary'], 2) ?></td>
  <td><?= number_format((float)$l['total_deductions'], 2) ?></td>
  <td class="fw-semibold"><?= number_format((float)$l['net_salary'], 2) ?></td>
  <td><?= !empty($l['wps_applicable']) ? 'Yes' : 'No' ?></td>
  <td><span class="fm-badge badge-status-<?= esc($l['status']) ?>"><?= esc(ucfirst($l['status'])) ?></span><?php if (!empty($l['error_message'])): ?><div class="text-danger small"><?= esc($l['error_message']) ?></div><?php endif; ?></td>
</tr>
<?php endforeach; ?>
<?php if (empty($lines)): ?><tr><td colspan="7" class="text-center py-3 text-muted">No lines — run Calculate.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?= $this->endSection() ?>
