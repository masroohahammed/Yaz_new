<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-credit-card me-2 text-primary"></i>Payments &amp; Invoices</h1></div>
  <a href="<?= base_url('portal') ?>" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left me-1"></i>Dashboard
  </a>
</div>

<?php if (! $tenant): ?>
  <div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-2"></i>
    Your account is not linked to a tenant record. Showing invoices linked to your user account only.
  </div>
<?php endif; ?>

<!-- Lease Payments -->
<?php if (! empty($payments)): ?>
<div class="fm-card mb-3">
  <div class="card-header-fm"><h5><i class="bi bi-cash-coin me-2"></i>Lease Payment Schedule</h5></div>
  <div class="fm-card-body p-0">
    <div class="table-responsive">
      <table class="fm-table">
        <thead>
          <tr><th>Payment #</th><th>Contract</th><th>Property / Unit</th><th>Amount</th><th>Type</th><th>Due Date</th><th>Paid Date</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php foreach ($payments as $p): ?>
        <tr>
          <td class="fw-semibold small"><?= esc($p['payment_number']) ?></td>
          <td class="small"><?= esc($p['contract_number'] ?? '—') ?></td>
          <td class="small"><?= esc($p['facility_name'] ?? '—') ?><?= $p['unit_number'] ? ' · ' . esc($p['unit_number']) : '' ?></td>
          <td class="small fw-bold"><?= $currency ?> <?= number_format((float)$p['amount'], 2) ?></td>
          <td class="small"><?= ucfirst(esc($p['payment_type'] ?? 'rent')) ?></td>
          <td class="small <?= ($p['status'] === 'overdue') ? 'text-danger fw-semibold' : '' ?>">
            <?= $p['due_date'] ? date('d M Y', strtotime($p['due_date'])) : '—' ?>
          </td>
          <td class="small text-success">
            <?= $p['payment_date'] ? date('d M Y', strtotime($p['payment_date'])) : '—' ?>
          </td>
          <td><span class="fm-badge badge-status-<?= esc($p['status']) ?>"><?= ucfirst(esc($p['status'])) ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <!-- Totals -->
    <?php
      $paid     = array_sum(array_column(array_filter($payments, fn($p) => $p['status'] === 'paid'), 'amount'));
      $pending  = array_sum(array_column(array_filter($payments, fn($p) => in_array($p['status'], ['pending'])), 'amount'));
      $overdue  = array_sum(array_column(array_filter($payments, fn($p) => $p['status'] === 'overdue'), 'amount'));
    ?>
    <div class="d-flex justify-content-end gap-4 px-3 py-2 border-top small fw-semibold">
      <span class="text-success"><i class="bi bi-check-circle me-1"></i>Paid: <?= $currency ?> <?= number_format($paid, 2) ?></span>
      <span class="text-warning"><i class="bi bi-clock me-1"></i>Pending: <?= $currency ?> <?= number_format($pending, 2) ?></span>
      <?php if ($overdue > 0): ?>
        <span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Overdue: <?= $currency ?> <?= number_format($overdue, 2) ?></span>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Invoices -->
<?php if (! empty($invoices)): ?>
<div class="fm-card">
  <div class="card-header-fm"><h5><i class="bi bi-receipt me-2"></i>Invoices</h5></div>
  <div class="fm-card-body p-0">
    <div class="table-responsive">
      <table class="fm-table">
        <thead>
          <tr><th>Invoice #</th><th>Property</th><th>Amount</th><th>Issued</th><th>Due Date</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php foreach ($invoices as $inv): ?>
        <tr>
          <td class="fw-semibold small"><?= esc($inv['invoice_number']) ?></td>
          <td class="small"><?= esc($inv['facility_name'] ?? '—') ?></td>
          <td class="small fw-bold"><?= $currency ?> <?= number_format((float)$inv['total'], 2) ?></td>
          <td class="small"><?= isset($inv['issue_date']) && $inv['issue_date'] ? date('d M Y', strtotime($inv['issue_date'])) : '—' ?></td>
          <td class="small <?= ($inv['status'] === 'overdue') ? 'text-danger fw-semibold' : '' ?>">
            <?= $inv['due_date'] ? date('d M Y', strtotime($inv['due_date'])) : '—' ?>
          </td>
          <td><span class="fm-badge badge-status-<?= esc($inv['status']) ?>"><?= ucfirst(esc($inv['status'])) ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if (empty($payments) && empty($invoices)): ?>
<div class="fm-card">
  <div class="fm-card-body text-center py-5">
    <i class="bi bi-receipt fs-1 text-muted opacity-50 d-block mb-3"></i>
    <h5 class="text-muted">No payment or invoice records found</h5>
    <p class="text-muted small">Your payment history will appear here once your lease is set up.</p>
  </div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>
