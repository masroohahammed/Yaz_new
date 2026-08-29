<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-graph-up me-2 text-primary"></i>Revenue Report</h1>
    <div class="small text-muted"><?= esc($landlord['full_name']) ?></div>
  </div>
  <a href="<?= base_url('landlords/'.$landlord['id'].'/show') ?>" class="btn btn-fm-outline btn-sm">Back</a>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="form-card text-center">
      <div class="text-muted small text-uppercase">Total Rent Collected</div>
      <div class="fs-4 fw-bold text-success"><?= number_format((float)$totalRevenue, 2) ?> <?= esc($currency) ?></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-card text-center">
      <div class="text-muted small text-uppercase">Total Payouts</div>
      <div class="fs-4 fw-bold text-primary"><?= number_format((float)$totalPayouts, 2) ?> <?= esc($currency) ?></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-card text-center">
      <div class="text-muted small text-uppercase">Active Leases</div>
      <div class="fs-4 fw-bold"><?= count(array_filter($contracts, fn($c) => $c['status'] === 'active')) ?></div>
    </div>
  </div>
</div>

<?php if (!empty($contracts)): ?>
<div class="form-card mb-3">
  <h6 class="text-muted text-uppercase small mb-2">Lease Contracts</h6>
  <div class="table-responsive">
  <table class="table table-sm table-registry mb-0">
    <thead><tr><th>Contract</th><th>Tenant</th><th>Property</th><th>Unit</th><th>Rent</th><th>Period</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($contracts as $c): ?>
    <tr>
      <td><a href="<?= base_url('contracts/'.$c['id']) ?>"><?= esc($c['contract_number']??'—') ?></a></td>
      <td><?= esc($c['tenant_name']??'—') ?></td>
      <td><?= esc($c['facility_name']??'—') ?></td>
      <td><?= esc($c['unit_number']??'—') ?></td>
      <td><?= number_format((float)($c['rent_amount']??0),2) ?> <?= esc($currency) ?></td>
      <td class="small"><?= esc($c['start_date']??'—') ?> – <?= esc($c['end_date']??'—') ?></td>
      <td><span class="badge bg-secondary"><?= esc($c['status']) ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php endif; ?>

<?php if (!empty($payments)): ?>
<div class="form-card mb-3">
  <h6 class="text-muted text-uppercase small mb-2">Payment History</h6>
  <div class="table-responsive">
  <table class="table table-sm table-registry mb-0">
    <thead><tr><th>Payment</th><th>Contract</th><th>Due</th><th>Paid</th><th>Amount</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($payments as $p): ?>
    <tr>
      <td><?= esc($p['payment_number']) ?></td>
      <td><?= esc($p['contract_number']??'—') ?></td>
      <td><?= esc($p['due_date']??'—') ?></td>
      <td><?= esc($p['payment_date']??'—') ?></td>
      <td><?= number_format((float)$p['amount'],2) ?></td>
      <td><span class="badge bg-secondary"><?= esc($p['status']) ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php endif; ?>

<?php if (!empty($payouts)): ?>
<div class="form-card mb-3">
  <h6 class="text-muted text-uppercase small mb-2">Payout History</h6>
  <div class="table-responsive">
  <table class="table table-sm table-registry mb-0">
    <thead><tr><th>Period</th><th>Gross</th><th>Commission</th><th>Deductions</th><th>Net</th><th>Method</th><th>Status</th><th>Paid Date</th></tr></thead>
    <tbody>
    <?php foreach ($payouts as $po): ?>
    <tr>
      <td class="small"><?= esc($po['period_from']??'—') ?> – <?= esc($po['period_to']??'—') ?></td>
      <td><?= number_format((float)($po['gross_rent']??0),2) ?></td>
      <td><?= number_format((float)($po['commission']??0),2) ?></td>
      <td><?= number_format((float)($po['deductions']??0),2) ?></td>
      <td><strong><?= number_format((float)($po['net_amount']??0),2) ?></strong></td>
      <td><?= esc($po['payment_method']??'—') ?></td>
      <td><span class="badge bg-<?= $po['status']==='paid'?'success':'warning' ?>"><?= esc($po['status']) ?></span></td>
      <td><?= esc($po['paid_date']??'—') ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
