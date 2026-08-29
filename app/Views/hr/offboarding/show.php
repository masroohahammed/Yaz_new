<?= $this->extend('layouts/hr') ?>
<?= $this->section('hr_content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-person-dash me-2 text-primary"></i>Offboarding</h1><p class="text-muted small mb-0"><?= esc($emp['name'] ?? $emp['emp_code']) ?></p></div>
  <a href="<?= base_url('employees/view/'.$emp['id']) ?>" class="btn btn-fm-outline btn-sm">Profile</a>
</div>
<?php if (!empty($migrationRequired)): ?>
<div class="alert alert-warning">Run consolidated SQL patch first.</div>
<?php else: ?>
<div class="row g-3">
  <div class="col-lg-6">
    <div class="fm-card mb-3"><div class="card-header-fm"><h5>Exit Clearance</h5></div>
    <div class="fm-card-body">
      <?php if (empty($instance)): ?>
      <?= form_open(base_url('hr/offboarding/'.$emp['id'].'/clearance/start')) ?><?= csrf_field() ?>
      <div class="row g-2"><div class="col-md-6"><select name="separation_type" class="form-select form-select-sm"><option value="resignation">Resignation</option><option value="termination">Termination</option><option value="contract_end">Contract End</option></select></div>
      <div class="col-md-6"><button class="btn btn-fm-primary btn-sm">Start Clearance</button></div></div><?= form_close() ?>
      <?php else: ?>
      <p class="small mb-2">Status: <span class="fm-badge badge-status-<?= esc($instance['status']) ?>"><?= esc(ucfirst($instance['status'])) ?></span></p>
      <table class="fm-table table-sm"><thead><tr><th>Item</th><th>Dept</th><th>Status</th><th></th></tr></thead><tbody>
      <?php foreach ($clearanceItems as $item): ?>
      <tr><td class="small"><?= esc($item['name']) ?></td><td class="small"><?= esc($item['department'] ?? '') ?></td>
      <td><?= esc(ucfirst($item['status'])) ?></td>
      <td><?php if ($item['status']==='pending'): ?><?= form_open(base_url('hr/offboarding/clearance/item/'.$item['id'].'/clear'),['class'=>'d-inline']) ?><?= csrf_field() ?><button class="btn btn-sm btn-success">Clear</button><?= form_close() ?><?php endif; ?></td></tr>
      <?php endforeach; ?>
      </tbody></table>
      <?php endif; ?>
    </div></div>
  </div>
  <div class="col-lg-6">
    <div class="fm-card"><div class="card-header-fm"><h5>Final Settlement</h5></div>
    <div class="fm-card-body">
      <?php if (empty($settlement)): ?>
      <?= form_open(base_url('hr/offboarding/'.$emp['id'].'/settlement/calculate')) ?><?= csrf_field() ?>
      <label class="small">Last Working Date</label><input type="date" name="last_working_date" class="form-control form-control-sm mb-2" value="<?= esc(date('Y-m-d')) ?>" required>
      <button class="btn btn-fm-primary btn-sm">Calculate Settlement</button><?= form_close() ?>
      <?php else: ?>
      <div class="small mb-2">#<?= esc($settlement['settlement_number']) ?> — <?= esc(ucwords(str_replace('_',' ',$settlement['status']))) ?></div>
      <div class="row g-2 small mb-2"><div class="col-4">Earnings: <?= number_format((float)$settlement['total_earnings'],2) ?></div><div class="col-4">Deductions: <?= number_format((float)$settlement['total_deductions'],2) ?></div><div class="col-4 fw-semibold">Net: <?= number_format((float)$settlement['net_payable'],2) ?></div></div>
      <?php if (!empty($settlementLines)): ?><table class="fm-table table-sm"><tbody><?php foreach ($settlementLines as $l): ?><tr><td class="small"><?= esc($l['component_name']) ?></td><td><?= number_format((float)$l['amount'],2) ?></td></tr><?php endforeach; ?></tbody></table><?php endif; ?>
      <?php if (($canApprove ?? false) && in_array($settlement['status'],['calculated','pending_approval'],true)): ?>
      <?= form_open(base_url('hr/offboarding/settlement/'.$settlement['id'].'/approve')) ?><?= csrf_field() ?><button class="btn btn-success btn-sm mt-2">Approve Settlement</button><?= form_close() ?>
      <?php endif; ?>
      <?php endif; ?>
    </div></div>
  </div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>
