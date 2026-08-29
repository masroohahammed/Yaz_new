<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <title><?= esc($title ?? 'Scan') ?> — <?= esc($settings['company_name'] ?? 'FM ERP') ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    body { background:#f4f7fb; font-family:system-ui,sans-serif; }
    .scan-hero { background:linear-gradient(135deg,#76002b,#a0003a); color:#fff; padding:1.25rem 1rem 1.5rem; }
    .scan-card { border:0; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,.08); }
    .action-btn { border-radius:10px; padding:.75rem; font-weight:600; }
  </style>
</head>
<body>
<?php
$entityType = $entityType ?? 'property';
$entity     = $entity ?? [];
$isLoggedIn = ! empty($isLoggedIn);
$entityId   = (int) ($entity['id'] ?? 0);
$facilityId = (int) ($entity['facility_id'] ?? $entityId);
$unitId     = $entityType === 'unit' ? $entityId : 0;

$typeLabel = match ($entityType) {
    'property' => 'Property Scan',
    'unit'     => 'Unit Scan',
    'asset'    => 'Asset Scan',
    default    => 'Scan',
};
?>
<div class="scan-hero">
  <div class="small opacity-75 mb-1"><i class="bi bi-qr-code-scan me-1"></i><?= esc($typeLabel) ?></div>
  <h1 class="h4 mb-1 fw-bold"><?= esc($title ?? '') ?></h1>
  <?php if (! empty($subtitle)): ?><div class="small"><?= esc($subtitle) ?></div><?php endif; ?>
</div>

<div class="container px-3 py-3" style="max-width:520px">
  <?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success small"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger small"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <?php if ($isLoggedIn): ?>
  <div class="card scan-card mb-3">
    <div class="card-body">
      <?php if ($entityType === 'property'): ?>
      <div class="row g-2 small">
        <div class="col-6"><span class="text-muted">Code</span><br><strong><?= esc($entity['code'] ?? '—') ?></strong></div>
        <div class="col-6"><span class="text-muted">Status</span><br><strong><?= ucfirst((string) ($entity['status'] ?? '—')) ?></strong></div>
        <div class="col-12"><span class="text-muted">Address</span><br><?= esc($entity['address'] ?? '—') ?></div>
        <div class="col-6"><span class="text-muted">City</span><br><?= esc($entity['city'] ?? '—') ?></div>
        <div class="col-6"><span class="text-muted">Manager</span><br><?= esc($entity['manager_name'] ?? '—') ?></div>
      </div>
      <?php elseif ($entityType === 'unit'): ?>
      <div class="row g-2 small">
        <div class="col-6"><span class="text-muted">Property</span><br><strong><?= esc($entity['facility_name'] ?? '—') ?></strong></div>
        <div class="col-6"><span class="text-muted">Status</span><br><strong><?= ucfirst((string) ($entity['status'] ?? '—')) ?></strong></div>
        <div class="col-6"><span class="text-muted">Type</span><br><?= esc(ucfirst((string) ($entity['unit_type'] ?? '—'))) ?></div>
        <div class="col-6"><span class="text-muted">Floor</span><br><?= esc($entity['floor'] ?? '—') ?></div>
        <?php if (! empty($entity['tenant_name'])): ?>
        <div class="col-12"><span class="text-muted">Tenant</span><br><?= esc($entity['tenant_name']) ?></div>
        <?php endif; ?>
      </div>
      <?php elseif ($entityType === 'asset'): ?>
      <div class="row g-2 small">
        <div class="col-6"><span class="text-muted">Code</span><br><strong><?= esc($entity['asset_code'] ?? '—') ?></strong></div>
        <div class="col-6"><span class="text-muted">Status</span><br><strong><?= ucfirst(str_replace('_', ' ', (string) ($entity['status'] ?? '—'))) ?></strong></div>
        <div class="col-6"><span class="text-muted">Category</span><br><?= ucfirst(str_replace('_', ' ', (string) ($entity['category'] ?? '—'))) ?></div>
        <div class="col-6"><span class="text-muted">Location</span><br><?= esc($entity['location_in_facility'] ?: $entity['floor_room'] ?: '—') ?></div>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($isLoggedIn && ! empty($openMaintenance)): ?>
  <div class="card scan-card mb-3">
    <div class="card-body py-2">
      <div class="small fw-bold mb-2 text-warning"><i class="bi bi-exclamation-circle me-1"></i>Open Maintenance</div>
      <?php foreach ($openMaintenance as $mr): ?>
      <div class="d-flex justify-content-between small border-bottom py-1">
        <span><?= esc($mr['ticket_number']) ?></span>
        <span class="text-muted"><?= ucfirst((string) $mr['status']) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($isLoggedIn && ! empty($maintenanceHistory)): ?>
  <div class="card scan-card mb-3">
    <div class="card-body py-2">
      <div class="small fw-bold mb-2"><i class="bi bi-clock-history me-1"></i>Recent Maintenance</div>
      <?php foreach ($maintenanceHistory as $mr): ?>
      <div class="d-flex justify-content-between small border-bottom py-1">
        <span><?= esc($mr['ticket_number']) ?></span>
        <span class="text-muted"><?= esc(substr((string) ($mr['created_at'] ?? ''), 0, 10)) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="d-grid gap-2 mb-3">
    <?php if ($isLoggedIn): ?>
      <?php if ($entityType === 'property'): ?>
      <a href="<?= base_url('properties/view/' . $entityId) ?>" class="btn btn-primary action-btn"><i class="bi bi-building me-2"></i>View Property Details</a>
      <a href="<?= base_url('properties/view/' . $entityId . '#tab-inspections') ?>" class="btn btn-outline-primary action-btn"><i class="bi bi-clipboard2-check me-2"></i>Inspections<?php if (($inspectionCount ?? 0) > 0): ?> (<?= (int) $inspectionCount ?>)<?php endif; ?></a>
      <a href="<?= base_url('helpdesk/create?facility_id=' . $entityId) ?>" class="btn btn-outline-primary action-btn"><i class="bi bi-megaphone me-2"></i>Start Maintenance Request</a>
      <a href="<?= base_url('workorders/create?facility_id=' . $entityId) ?>" class="btn btn-outline-secondary action-btn"><i class="bi bi-tools me-2"></i>Create Work Order</a>
      <a href="<?= base_url('properties/view/' . $entityId . '#tab-maintenance') ?>" class="btn btn-outline-secondary action-btn"><i class="bi bi-wrench me-2"></i>Maintenance History</a>
      <?php elseif ($entityType === 'unit'): ?>
      <a href="<?= base_url('units/view/' . $entityId) ?>" class="btn btn-primary action-btn"><i class="bi bi-grid me-2"></i>View Unit Details</a>
      <a href="<?= base_url('units/view/' . $entityId . '#tab-inspections') ?>" class="btn btn-outline-primary action-btn"><i class="bi bi-clipboard2-check me-2"></i>Inspections<?php if (($inspectionCount ?? 0) > 0): ?> (<?= (int) $inspectionCount ?>)<?php endif; ?></a>
      <a href="<?= base_url('units/checklist/' . $entityId . '/routine') ?>" class="btn btn-outline-primary action-btn"><i class="bi bi-plus-circle me-2"></i>New Inspection</a>
      <a href="<?= base_url('helpdesk/create?facility_id=' . $facilityId . '&unit_id=' . $entityId) ?>" class="btn btn-outline-primary action-btn"><i class="bi bi-megaphone me-2"></i>Start Maintenance Request</a>
      <a href="<?= base_url('workorders/create?facility_id=' . $facilityId . '&unit_id=' . $entityId) ?>" class="btn btn-outline-secondary action-btn"><i class="bi bi-tools me-2"></i>Create Work Order</a>
      <?php elseif ($entityType === 'asset'): ?>
      <a href="<?= base_url('asset-register/view/' . $entityId) ?>" class="btn btn-primary action-btn"><i class="bi bi-box-seam me-2"></i>View Asset Details</a>
      <a href="<?= base_url('compliance/inspections/create?asset_id=' . $entityId) ?>" class="btn btn-outline-primary action-btn"><i class="bi bi-clipboard2-check me-2"></i>Perform Inspection</a>
      <a href="<?= base_url('helpdesk/create?asset_id=' . $entityId . '&facility_id=' . $facilityId) ?>" class="btn btn-outline-primary action-btn"><i class="bi bi-megaphone me-2"></i>Start Maintenance Request</a>
      <a href="<?= base_url('workorders/create?asset_id=' . $entityId . '&facility_id=' . $facilityId) ?>" class="btn btn-outline-secondary action-btn"><i class="bi bi-tools me-2"></i>Create Work Order</a>
      <a href="<?= base_url('asset-register/view/' . $entityId . '#tab-maintenance') ?>" class="btn btn-outline-secondary action-btn"><i class="bi bi-clock-history me-2"></i>Maintenance History</a>
      <?php endif; ?>
    <?php else: ?>
    <button class="btn btn-danger action-btn" type="button" data-bs-toggle="collapse" data-bs-target="#reportForm">
      <i class="bi bi-exclamation-triangle me-2"></i>Submit Maintenance Request
    </button>
    <?php endif; ?>
  </div>

  <?php if (! $isLoggedIn): ?>
  <div class="collapse" id="reportForm">
    <div class="card scan-card mb-3">
      <div class="card-body">
        <h2 class="h6 fw-bold mb-3">Maintenance Request</h2>
        <p class="small text-muted">You can report an issue without logging in. Staff will follow up on your request.</p>
        <?= form_open($complaintAction ?? '#') ?>
        <div class="mb-2">
          <label class="form-label small">Your Name *</label>
          <input type="text" name="requester_name" class="form-control form-control-sm" required value="<?= esc(old('requester_name')) ?>">
        </div>
        <div class="mb-2">
          <label class="form-label small">Phone</label>
          <input type="text" name="requester_phone" class="form-control form-control-sm" value="<?= esc(old('requester_phone')) ?>">
        </div>
        <div class="mb-2">
          <label class="form-label small">Email</label>
          <input type="email" name="requester_email" class="form-control form-control-sm" value="<?= esc(old('requester_email')) ?>">
        </div>
        <?php if ($entityType === 'property' && ! empty($units)): ?>
        <div class="mb-2">
          <label class="form-label small">Unit (optional)</label>
          <select name="unit_id" class="form-select form-select-sm">
            <option value="">— Property / common area —</option>
            <?php foreach ($units as $u): ?>
            <option value="<?= (int) $u['id'] ?>">Unit <?= esc($u['unit_number']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>
        <div class="mb-2">
          <label class="form-label small">Priority</label>
          <select name="priority" class="form-select form-select-sm">
            <option value="high">High</option>
            <option value="medium" selected>Medium</option>
            <option value="low">Low</option>
            <option value="critical">Critical</option>
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label small">Issue Description *</label>
          <textarea name="description" class="form-control form-control-sm" rows="3" required placeholder="Describe the problem..."><?= esc(old('description')) ?></textarea>
        </div>
        <button type="submit" class="btn btn-danger btn-sm w-100">Submit Request</button>
        <?= form_close() ?>
      </div>
    </div>
  </div>
  <a href="<?= base_url('login') ?>" class="btn btn-link btn-sm text-center d-block">Staff login for full access</a>
  <?php endif; ?>

  <div class="text-center small text-muted py-3">
    <?php if (! empty($qrImageUrl)): ?>
    <img src="<?= esc($qrImageUrl) ?>" alt="QR" width="100" class="mb-2 opacity-50">
    <?php endif; ?>
    <div><?= esc($settings['company_name'] ?? 'FM ERP') ?></div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
