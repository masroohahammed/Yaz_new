<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-file-earmark-text me-2 text-primary"></i>My Leases</h1></div>
  <a href="<?= base_url('portal') ?>" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left me-1"></i>Dashboard
  </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= esc(session()->getFlashdata('success')) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<?php if (! $tenant): ?>
  <div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-2"></i>
    Your account is not linked to a tenant record. Please contact the property management office.
  </div>
<?php elseif (empty($leases)): ?>
  <div class="fm-card">
    <div class="fm-card-body text-center py-5">
      <i class="bi bi-file-earmark-text fs-1 text-muted opacity-50 d-block mb-3"></i>
      <h5 class="text-muted">No lease contracts found</h5>
      <p class="text-muted small">Your lease history will appear here once contracts are created by the management team.</p>
    </div>
  </div>
<?php else: ?>
  <div class="fm-card">
    <div class="fm-card-body p-0">
      <div class="table-responsive">
        <table class="fm-table">
          <thead>
            <tr>
              <th>Contract #</th>
              <th>Property</th>
              <th>Unit</th>
              <th>Start Date</th>
              <th>End Date</th>
              <th>Rent / Period</th>
              <th>Payment</th>
              <th>Status</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($leases as $l): ?>
          <tr>
            <td class="fw-semibold small">
              <a href="<?= base_url('portal/leases/'.(int)$l['id']) ?>">
                <?= esc($l['contract_number']) ?>
              </a>
            </td>
            <td class="small"><?= esc($l['facility_name'] ?? '—') ?></td>
            <td class="small"><?= esc($l['unit_number'] ?? '—') ?></td>
            <td class="small"><?= $l['start_date'] ? date('d M Y', strtotime($l['start_date'])) : '—' ?></td>
            <td class="small">
              <?php
                $daysLeft = $l['end_date'] ? ceil((strtotime($l['end_date']) - time()) / 86400) : null;
              ?>
              <?= $l['end_date'] ? date('d M Y', strtotime($l['end_date'])) : '—' ?>
              <?php if ($daysLeft !== null && $l['status'] === 'active'): ?>
                <?php if ($daysLeft < 0): ?>
                  <span class="badge bg-danger-subtle text-danger-emphasis ms-1" style="font-size:.6rem">Expired</span>
                <?php elseif ($daysLeft <= 60): ?>
                  <span class="badge bg-warning-subtle text-warning-emphasis ms-1" style="font-size:.6rem"><?= $daysLeft ?>d left</span>
                <?php endif; ?>
              <?php endif; ?>
            </td>
            <td class="small fw-semibold"><?= $currency ?> <?= number_format((float)$l['rent_amount'], 2) ?> / <?= esc($l['payment_frequency'] ?? 'month') ?></td>
            <td class="small"><?= ucfirst(esc($l['payment_type'] ?? '—')) ?></td>
            <td><span class="fm-badge badge-status-<?= esc($l['status']) ?>"><?= ucfirst(esc($l['status'])) ?></span></td>
            <td>
              <a href="<?= base_url('portal/leases/'.(int)$l['id']) ?>" class="btn-action bg-primary bg-opacity-10 text-primary" title="View">
                <i class="bi bi-eye"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php endif; ?>
<?= $this->endSection() ?>
