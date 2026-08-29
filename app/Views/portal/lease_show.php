<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-file-earmark-text me-2 text-primary"></i>Lease <?= esc($lease['contract_number']) ?></h1>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0 small">
        <li class="breadcrumb-item"><a href="<?= base_url('portal') ?>">Portal</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('portal/leases') ?>">My Leases</a></li>
        <li class="breadcrumb-item active"><?= esc($lease['contract_number']) ?></li>
      </ol>
    </nav>
  </div>
  <span class="fm-badge badge-status-<?= esc($lease['status']) ?> fs-6 px-3 py-2"><?= ucfirst(esc($lease['status'])) ?></span>
</div>

<div class="row g-3">
  <!-- Lease Details -->
  <div class="col-lg-5">
    <div class="fm-card mb-3">
      <div class="card-header-fm"><h5><i class="bi bi-info-circle me-2"></i>Lease Details</h5></div>
      <div class="fm-card-body">
        <table class="table table-sm table-borderless mb-0 small">
          <tr><th class="text-muted fw-normal" style="width:40%">Contract #</th><td class="fw-semibold"><?= esc($lease['contract_number']) ?></td></tr>
          <tr><th class="text-muted fw-normal">Property</th><td><?= esc($lease['facility_name'] ?? '—') ?></td></tr>
          <?php if (! empty($lease['facility_address'])): ?>
          <tr><th class="text-muted fw-normal">Address</th><td><?= esc($lease['facility_address']) ?></td></tr>
          <?php endif; ?>
          <tr><th class="text-muted fw-normal">Unit</th><td><?= esc($lease['unit_number'] ?? '—') ?><?= $lease['floor_number'] ? ' (Floor ' . esc($lease['floor_number']) . ')' : '' ?></td></tr>
          <?php if (! empty($lease['area_sqm'])): ?>
          <tr><th class="text-muted fw-normal">Area</th><td><?= number_format((float)$lease['area_sqm'], 1) ?> m²</td></tr>
          <?php endif; ?>
          <tr><th class="text-muted fw-normal">Start Date</th><td><?= $lease['start_date'] ? date('d M Y', strtotime($lease['start_date'])) : '—' ?></td></tr>
          <tr><th class="text-muted fw-normal">End Date</th><td>
            <?= $lease['end_date'] ? date('d M Y', strtotime($lease['end_date'])) : '—' ?>
            <?php if ($lease['end_date'] && $lease['status'] === 'active'): ?>
              <?php $d = ceil((strtotime($lease['end_date']) - time()) / 86400); ?>
              <?php if ($d < 0): ?>
                <span class="badge bg-danger-subtle text-danger-emphasis ms-1" style="font-size:.6rem">Expired</span>
              <?php elseif ($d <= 60): ?>
                <span class="badge bg-warning-subtle text-warning-emphasis ms-1" style="font-size:.6rem"><?= $d ?>d left</span>
              <?php endif; ?>
            <?php endif; ?>
          </td></tr>
          <tr><th class="text-muted fw-normal">Rent Amount</th><td class="fw-bold"><?= $currency ?> <?= number_format((float)$lease['rent_amount'], 2) ?></td></tr>
          <tr><th class="text-muted fw-normal">Frequency</th><td><?= ucfirst(esc($lease['payment_frequency'] ?? 'monthly')) ?></td></tr>
          <tr><th class="text-muted fw-normal">Payment Type</th><td><?= ucfirst(esc($lease['payment_type'] ?? '—')) ?></td></tr>
          <?php if (! empty($lease['security_deposit'])): ?>
          <tr><th class="text-muted fw-normal">Security Deposit</th><td><?= $currency ?> <?= number_format((float)$lease['security_deposit'], 2) ?></td></tr>
          <?php endif; ?>
          <?php if (! empty($lease['vat_applicable'])): ?>
          <tr><th class="text-muted fw-normal">VAT</th><td><?= number_format((float)$lease['vat_rate'], 2) ?>%</td></tr>
          <?php endif; ?>
          <?php if (! empty($lease['auto_renew'])): ?>
          <tr><th class="text-muted fw-normal">Auto Renew</th><td><span class="badge bg-success-subtle text-success-emphasis">Yes</span></td></tr>
          <?php endif; ?>
        </table>
      </div>
    </div>

    <?php if (! empty($lease['contract_terms'])): ?>
    <div class="fm-card">
      <div class="card-header-fm"><h5><i class="bi bi-journal-text me-2"></i>Contract Terms</h5></div>
      <div class="fm-card-body small" style="white-space:pre-wrap"><?= esc($lease['contract_terms']) ?></div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Payment Schedule -->
  <div class="col-lg-7">
    <div class="fm-card">
      <div class="card-header-fm d-flex justify-content-between align-items-center">
        <h5><i class="bi bi-list-check me-2"></i>Payment Schedule</h5>
        <a href="<?= base_url('portal/payments') ?>" class="small text-primary">All payments</a>
      </div>
      <div class="fm-card-body p-0">
        <?php if (empty($payments)): ?>
          <div class="p-4 text-center text-muted small">
            <i class="bi bi-receipt fs-3 d-block mb-2 opacity-50"></i>
            No payment records for this contract yet.
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="fm-table">
              <thead>
                <tr><th>#</th><th>Payment #</th><th>Amount</th><th>Due Date</th><th>Paid Date</th><th>Status</th></tr>
              </thead>
              <tbody>
              <?php foreach ($payments as $i => $p): ?>
              <tr>
                <td class="small text-muted"><?= $i + 1 ?></td>
                <td class="small fw-semibold"><?= esc($p['payment_number']) ?></td>
                <td class="small fw-bold"><?= $currency ?> <?= number_format((float)$p['amount'], 2) ?></td>
                <td class="small <?= ($p['status'] === 'overdue') ? 'text-danger' : '' ?>">
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
          <!-- Summary row -->
          <?php
            $totalPaid    = array_sum(array_column(array_filter($payments, fn($p) => $p['status'] === 'paid'), 'amount'));
            $totalPending = array_sum(array_column(array_filter($payments, fn($p) => in_array($p['status'], ['pending', 'overdue'])), 'amount'));
          ?>
          <div class="d-flex justify-content-end gap-4 px-3 py-2 border-top small fw-semibold">
            <span class="text-success">Paid: <?= $currency ?> <?= number_format($totalPaid, 2) ?></span>
            <span class="text-danger">Outstanding: <?= $currency ?> <?= number_format($totalPending, 2) ?></span>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
