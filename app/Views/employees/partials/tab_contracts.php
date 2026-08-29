<?php
/** @var array<string, mixed> $emp */
/** @var list<array<string, mixed>> $contracts */
/** @var list<array<string, mixed>> $suppliers */
/** @var array<string, bool> $perms */
$perms = $perms ?? [];
$canViewRates = !empty($perms['employee.contract.view_rate']);
$canEdit = !empty($perms['employee.contract.edit']);
?>
<div class="fm-form-section mb-3">
  <h6><i class="bi bi-file-earmark-text"></i> Employment Contracts</h6>
  <?php if (empty($contracts)): ?>
  <p class="text-muted small mb-0">No employment contracts recorded.</p>
  <?php else: ?>
  <div class="table-responsive">
    <table class="fm-table table-sm">
      <thead><tr><th>Number</th><th>Type</th><th>Period</th><th>Status</th><th>Supplier</th><?php if ($canViewRates): ?><th>Cost Rate</th><?php endif; ?><th></th></tr></thead>
      <tbody>
      <?php foreach ($contracts as $c): ?>
      <tr class="<?= !empty($c['is_current']) ? 'table-light' : '' ?>">
        <td class="small fw-semibold"><?= esc($c['contract_number'] ?: ('#'.$c['id'])) ?><?= !empty($c['is_current']) ? ' <span class="badge bg-primary-subtle text-primary">Current</span>' : '' ?></td>
        <td class="small"><?= esc(ucwords(str_replace('_', ' ', $c['contract_type'] ?? ''))) ?></td>
        <td class="small"><?= $c['contract_start_date'] ? date('d M Y', strtotime($c['contract_start_date'])) : '—' ?> – <?= $c['contract_end_date'] ? date('d M Y', strtotime($c['contract_end_date'])) : '—' ?></td>
        <td><span class="fm-badge badge-status-<?= esc($c['contract_status']) ?>"><?= esc($contractStatuses[$c['contract_status']] ?? ucfirst($c['contract_status'])) ?></span></td>
        <td class="small"><?= esc($c['supplier_name'] ?? '—') ?></td>
        <?php if ($canViewRates): ?><td class="small"><?= $c['cost_rate'] !== null ? number_format((float)$c['cost_rate'], 2) : '—' ?></td><?php endif; ?>
        <td>
          <?php if ($canEdit && !empty($c['is_current']) && in_array($c['contract_status'], ['active','expiring_soon','renewal_pending'], true)): ?>
          <?= form_open(base_url('hr/contracts/renew/'.$c['id']), ['class' => 'd-inline']) ?>
          <?= csrf_field() ?>
          <input type="hidden" name="contract_start_date" value="<?= esc(date('Y-m-d')) ?>">
          <input type="hidden" name="contract_end_date" value="<?= esc($c['contract_end_date']) ?>">
          <button type="submit" class="btn btn-sm btn-fm-outline" onclick="return confirm('Create renewed contract? Old contract will be archived.')">Renew</button>
          <?= form_close() ?>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php if ($canEdit): ?>
<div class="fm-form-section">
  <h6>Add / Update Contract</h6>
  <?= form_open(base_url('hr/contracts/store')) ?>
  <?= csrf_field() ?>
  <input type="hidden" name="employee_id" value="<?= (int)$emp['id'] ?>">
  <div class="row g-2">
    <div class="col-md-3"><label class="form-label small">Contract Number</label><input name="contract_number" class="form-control form-control-sm"></div>
    <div class="col-md-3"><label class="form-label small">Type</label><select name="contract_type" class="form-select form-select-sm"><option value="fixed_term">Fixed Term</option><option value="internal">Internal Contract</option><option value="external">External / Outsourced</option><option value="consultant">Consultant</option></select></div>
    <div class="col-md-3"><label class="form-label small">Start Date</label><input type="date" name="contract_start_date" class="form-control form-control-sm"></div>
    <div class="col-md-3"><label class="form-label small">End Date</label><input type="date" name="contract_end_date" class="form-control form-control-sm"></div>
    <div class="col-md-3"><label class="form-label small">Status</label><select name="contract_status" class="form-select form-select-sm"><?php foreach ($contractStatuses as $code => $label): ?><option value="<?= esc($code) ?>"><?= esc($label) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><label class="form-label small">Payroll Responsibility</label><select name="payroll_responsibility" class="form-select form-select-sm"><option value="our_company">Our Company</option><option value="supplier">Supplier</option><option value="external">External</option><option value="consultant">Consultant</option><option value="none">Non-Payroll</option></select></div>
    <div class="col-md-3"><label class="form-label small">Supplier (manpower)</label><select name="supplier_id" class="form-select form-select-sm"><option value="">— Direct —</option><?php foreach ($suppliers as $s): ?><option value="<?= (int)$s['id'] ?>"><?= esc($s['name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><label class="form-label small">Billing Type</label><select name="billing_type" class="form-select form-select-sm"><option value="">—</option><option value="monthly_rate">Monthly Rate</option><option value="daily_rate">Daily Rate</option><option value="hourly_rate">Hourly Rate</option><option value="shift_rate">Shift Rate</option></select></div>
    <?php if ($canViewRates && !empty($perms['employee.contract.edit_rate'])): ?>
    <div class="col-md-3"><label class="form-label small">Cost Rate</label><input type="number" step="0.01" name="cost_rate" class="form-control form-control-sm"></div>
    <div class="col-md-3"><label class="form-label small">Billing Rate</label><input type="number" step="0.01" name="billing_rate" class="form-control form-control-sm"></div>
    <div class="col-md-3"><label class="form-label small">Client Billing Rate</label><input type="number" step="0.01" name="client_billing_rate" class="form-control form-control-sm"></div>
    <?php endif; ?>
    <div class="col-md-6"><label class="form-label small">Remarks</label><input name="remarks" class="form-control form-control-sm"></div>
    <div class="col-md-3 d-flex align-items-end"><button type="submit" class="btn btn-fm-primary btn-sm w-100">Save Contract</button></div>
  </div>
  <?= form_close() ?>
</div>
<?php elseif (!empty($perms['employee.contract.view'])): ?>
<p class="text-muted small"><i class="bi bi-lock me-1"></i>Contract editing requires <code>employee.contract.edit</code> permission.</p>
<?php endif; ?>
