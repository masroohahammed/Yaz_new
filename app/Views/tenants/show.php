<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$statusColors = ['active' => 'success', 'inactive' => 'secondary', 'blacklisted' => 'danger'];
$statusColor  = $statusColors[$tenant['status']] ?? 'secondary';
?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-person me-2 text-primary"></i><?= esc($tenant['full_name']) ?></h1>
    <div class="small text-muted"><?= esc($tenant['tenant_type']) ?> tenant</div>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('tenants/' . $tenant['id'] . '/edit') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
    <a href="<?= base_url('tenants') ?>" class="btn btn-fm-outline btn-sm">Back to list</a>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-3">
    <div class="form-card text-center h-100">
      <div class="text-muted small text-uppercase">Active Contracts</div>
      <div class="fs-5 fw-bold"><?= (int) ($activeContracts ?? 0) ?></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-card text-center h-100">
      <div class="text-muted small text-uppercase">Total Contracts</div>
      <div class="fs-5 fw-bold"><?= count($contracts ?? []) ?></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-card text-center h-100">
      <div class="text-muted small text-uppercase">Open Payments</div>
      <div class="fs-5 fw-bold text-warning"><?= (int) ($openPayments ?? 0) ?></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="form-card text-center h-100">
      <div class="text-muted small text-uppercase">Status</div>
      <div class="mt-1"><span class="badge bg-<?= $statusColor ?>"><?= esc($tenant['status']) ?></span></div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-lg-6">
    <div class="form-card h-100">
      <h6 class="text-muted text-uppercase small mb-3">Contact Details</h6>
      <p class="mb-1"><strong>Phone:</strong> <?= esc($tenant['phone'] ?? '—') ?></p>
      <p class="mb-1"><strong>WhatsApp:</strong> <?= esc($tenant['whatsapp'] ?? '—') ?></p>
      <p class="mb-1"><strong>Email:</strong> <?= esc($tenant['email'] ?? '—') ?></p>
      <p class="mb-1"><strong>Nationality:</strong> <?= esc($tenant['nationality'] ?? '—') ?></p>
      <p class="mb-1"><strong>Gender:</strong> <?= esc($tenant['gender'] ?? '—') ?></p>
      <p class="mb-0"><strong>Date of birth:</strong> <?= esc($tenant['dob'] ?? '—') ?></p>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="form-card h-100">
      <h6 class="text-muted text-uppercase small mb-3">Identification</h6>
      <p class="mb-1"><strong>QID:</strong> <?= esc($tenant['qid_no'] ?? '—') ?><?= ! empty($tenant['qid_expiry']) ? ' (exp. ' . esc($tenant['qid_expiry']) . ')' : '' ?></p>
      <p class="mb-1"><strong>Passport:</strong> <?= esc($tenant['passport_no'] ?? '—') ?><?= ! empty($tenant['passport_expiry']) ? ' (exp. ' . esc($tenant['passport_expiry']) . ')' : '' ?></p>
      <?php if (($tenant['tenant_type'] ?? '') === 'Corporate'): ?>
      <p class="mb-1"><strong>Company:</strong> <?= esc($tenant['company_name'] ?? '—') ?></p>
      <p class="mb-0"><strong>CR No:</strong> <?= esc($tenant['company_cr'] ?? '—') ?></p>
      <?php else: ?>
      <p class="mb-0 text-muted small">Personal tenant</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-lg-6">
    <div class="form-card h-100">
      <h6 class="text-muted text-uppercase small mb-3">Emergency Contact</h6>
      <p class="mb-1"><strong>Name:</strong> <?= esc($tenant['emergency_name'] ?? '—') ?></p>
      <p class="mb-1"><strong>Phone:</strong> <?= esc($tenant['emergency_phone'] ?? '—') ?></p>
      <p class="mb-0"><strong>Relation:</strong> <?= esc($tenant['emergency_relation'] ?? '—') ?></p>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="form-card h-100">
      <h6 class="text-muted text-uppercase small mb-3">Notes &amp; Record</h6>
      <p class="mb-1"><strong>Created:</strong> <?= esc($tenant['created_at'] ?? '—') ?></p>
      <p class="mb-1"><strong>Updated:</strong> <?= esc($tenant['updated_at'] ?? '—') ?></p>
      <p class="mb-0"><strong>Notes:</strong> <?= nl2br(esc($tenant['notes'] ?? '—')) ?></p>
    </div>
  </div>
</div>

<?php if (! empty($contracts)): ?>
<div class="form-card mb-3">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="text-muted text-uppercase small mb-0">Lease Contracts</h6>
    <a href="<?= base_url('contracts/create') ?>" class="btn btn-sm btn-fm-outline">New Contract</a>
  </div>
  <div class="table-responsive">
    <table class="table table-sm table-registry mb-0">
      <thead><tr><th>Contract</th><th>Property</th><th>Unit</th><th>Rent</th><th>Period</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($contracts as $c): ?>
        <tr>
          <td><a href="<?= base_url('contracts/' . $c['id']) ?>"><?= esc($c['contract_number'] ?? '—') ?></a></td>
          <td><?= esc($c['facility_name'] ?? '—') ?></td>
          <td><?= esc($c['unit_number'] ?? '—') ?></td>
          <td><?= number_format((float) ($c['rent_amount'] ?? 0), 2) ?> <?= esc($currency) ?></td>
          <td class="small"><?= esc($c['start_date'] ?? '—') ?> – <?= esc($c['end_date'] ?? '—') ?></td>
          <td><span class="badge bg-secondary"><?= esc($c['status']) ?></span></td>
          <td class="text-end"><a href="<?= base_url('contracts/' . $c['id']) ?>" class="btn btn-sm btn-fm-outline">View</a></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php else: ?>
<div class="form-card mb-3 text-muted">No lease contracts linked to this tenant yet.</div>
<?php endif; ?>

<?php if (! empty($payments)): ?>
<div class="form-card mb-3">
  <h6 class="text-muted text-uppercase small mb-2">Recent Payments</h6>
  <div class="table-responsive">
    <table class="table table-sm table-registry mb-0">
      <thead><tr><th>Payment</th><th>Contract</th><th>Due</th><th>Paid</th><th>Amount</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($payments as $p): ?>
        <tr>
          <td><?= esc($p['payment_number'] ?? '—') ?></td>
          <td><?php if (! empty($p['contract_id'])): ?><a href="<?= base_url('contracts/' . $p['contract_id']) ?>"><?= esc($p['contract_number'] ?? '—') ?></a><?php else: ?><?= esc($p['contract_number'] ?? '—') ?><?php endif; ?></td>
          <td><?= esc($p['due_date'] ?? '—') ?></td>
          <td><?= esc($p['payment_date'] ?? '—') ?></td>
          <td><?= number_format((float) ($p['amount'] ?? 0), 2) ?> <?= esc($currency) ?></td>
          <td><span class="badge bg-<?= ($p['status'] ?? '') === 'paid' ? 'success' : (($p['status'] ?? '') === 'overdue' ? 'danger' : 'secondary') ?>"><?= esc($p['status'] ?? '—') ?></span></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
