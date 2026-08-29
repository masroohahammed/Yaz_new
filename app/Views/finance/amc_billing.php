<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header mb-3">
  <div>
    <h1 class="h4"><i class="bi bi-calendar-check me-2"></i>AMC Contract Billing</h1>
    <div class="small text-muted">Recurring invoices from active contracts</div>
  </div>
  <div class="d-flex gap-2">
    <?= form_open(base_url('finance/amc-billing/run'), ['class' => 'd-inline fm-submit-form']) ?>
    <?= csrf_field() ?>
    <button type="submit" class="btn btn-fm-primary btn-sm fm-submit-btn"><i class="bi bi-lightning me-1"></i>Run Due Billing</button>
    <?= form_close() ?>
    <a href="<?= base_url('settings/finance-module') ?>" class="btn btn-fm-outline btn-sm">Finance Setup</a>
  </div>
</div>
<?php if (empty($schedules)): ?>
<div class="alert alert-info">Create an active <strong>AMC / FM Services</strong> contract to auto-create a billing schedule.</div>
<?php else: ?>
<div class="fm-card"><div class="fm-card-body p-0">
<table class="table table-sm mb-0">
  <thead><tr><th>Contract</th><th>Facility</th><th>Frequency</th><th>Next bill</th><th class="text-end">Amount</th><th>Auto</th></tr></thead>
  <tbody>
  <?php foreach ($schedules as $s): ?>
  <tr>
    <td><a href="<?= base_url('finance/contracts/view/'.$s['contract_id']) ?>"><?= esc($s['contract_number']) ?></a></td>
    <td><?= esc($s['facility_name'] ?? '') ?></td>
    <td><?= esc($s['frequency']) ?></td>
    <td class="<?= strtotime($s['next_bill_date'])<=time()?'text-danger fw-bold':'' ?>"><?= esc($s['next_bill_date']) ?></td>
    <td class="text-end"><?= $currency ?> <?= number_format((float)$s['amount'],2) ?></td>
    <td><?= !empty($s['auto_invoice'])?'Yes':'No' ?></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div></div>
<?php endif; ?>
<?= $this->endSection() ?>
