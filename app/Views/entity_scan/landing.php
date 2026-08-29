<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <title><?= esc($title ?? 'Scan') ?> — <?= esc($settings['company_name'] ?? 'FM ERP') ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <?= view('partials/public_scan_theme', ['settings' => $settings ?? []]) ?>
</head>
<body class="scan-public">
<?php
helper('fm');
$entityType = $entityType ?? 'property';
$entity     = $entity ?? [];
$isLoggedIn = ! empty($isLoggedIn);
$entityId   = (int) ($entity['id'] ?? 0);
$facilityId = (int) ($entity['facility_id'] ?? $entityId);
$logoUrl    = fm_logo_url($settings['company_logo'] ?? '');

$typeLabel = match ($entityType) {
    'property' => 'Property Scan',
    'unit'     => 'Unit Scan',
    'asset'    => 'Asset Scan',
    default    => 'Scan',
};

$inspectionsUrl     = $inspectionsUrl ?? base_url('public/inspections');
$maintenanceUrl     = $maintenanceUrl ?? base_url('public/maintenance');
$workOrdersUrl      = $workOrdersUrl ?? base_url('workorders');
$workOrderCreateUrl = $workOrderCreateUrl ?? base_url('workorders/create');
$detailsUrl         = $detailsUrl ?? '#';
?>
<div class="scan-wrap">
  <div class="scan-brand">
    <?php if ($logoUrl): ?><img src="<?= esc($logoUrl) ?>" alt=""><?php else: ?><div class="auth-logo"><i class="bi bi-qr-code-scan"></i></div><?php endif; ?>
    <div class="small opacity-75 mb-1"><i class="bi bi-qr-code-scan me-1"></i><?= esc($typeLabel) ?></div>
    <h1 class="h4 fw-bold mb-1"><?= esc($title ?? '') ?></h1>
    <?php if (! empty($subtitle)): ?><div class="small opacity-75"><?= esc($subtitle) ?></div><?php endif; ?>
  </div>

  <?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success border-0 rounded-3 small"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger border-0 rounded-3 small"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <?php if ($isLoggedIn): ?>
  <div class="public-card mb-3">
    <div class="row g-2 small">
      <?php if ($entityType === 'property'): ?>
      <div class="col-6"><span class="text-muted">Code</span><br><strong><?= esc($entity['code'] ?? '—') ?></strong></div>
      <div class="col-6"><span class="text-muted">Status</span><br><strong><?= ucfirst((string) ($entity['status'] ?? '—')) ?></strong></div>
      <div class="col-12"><span class="text-muted">Address</span><br><?= esc($entity['address'] ?? '—') ?></div>
      <?php elseif ($entityType === 'unit'): ?>
      <div class="col-6"><span class="text-muted">Property</span><br><strong><?= esc($entity['facility_name'] ?? '—') ?></strong></div>
      <div class="col-6"><span class="text-muted">Status</span><br><strong><?= ucfirst((string) ($entity['status'] ?? '—')) ?></strong></div>
      <?php elseif ($entityType === 'asset'): ?>
      <div class="col-6"><span class="text-muted">Code</span><br><strong><?= esc($entity['asset_code'] ?? '—') ?></strong></div>
      <div class="col-6"><span class="text-muted">Category</span><br><?= esc(ucfirst(str_replace('_', ' ', (string) ($entity['category'] ?? '—')))) ?></div>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="d-grid gap-2 mb-3">
    <?php if ($isLoggedIn): ?>
    <a href="<?= esc($detailsUrl) ?>" class="btn btn-fm-primary action-btn"><i class="bi bi-eye me-2"></i>View Details</a>
    <a href="<?= esc($inspectionsUrl) ?>" class="btn btn-fm-outline action-btn"><i class="bi bi-clipboard2-check me-2"></i>Inspection Forms<?php if (($inspectionCount ?? 0) > 0): ?> (<?= (int) $inspectionCount ?>)<?php endif; ?></a>
    <a href="<?= esc($maintenanceUrl) ?>" class="btn btn-fm-outline action-btn"><i class="bi bi-wrench me-2"></i>Maintenance</a>
    <a href="<?= esc($workOrdersUrl) ?>" class="btn btn-fm-outline action-btn"><i class="bi bi-list-task me-2"></i>Work Orders</a>
    <a href="<?= esc($workOrderCreateUrl) ?>" class="btn btn-fm-outline action-btn"><i class="bi bi-tools me-2"></i>New Work Order</a>
    <?php else: ?>
    <a href="<?= esc($maintenanceUrl) ?>" class="btn btn-fm-primary action-btn"><i class="bi bi-wrench me-2"></i>Submit Maintenance Request</a>
    <a href="<?= base_url('login') ?>" class="btn btn-fm-outline action-btn"><i class="bi bi-box-arrow-in-right me-2"></i>Staff Login</a>
    <?php endif; ?>
  </div>

  <?php if ($isLoggedIn && ! empty($openMaintenance)): ?>
  <div class="public-card mb-3 py-2">
    <div class="small fw-bold mb-2 text-warning"><i class="bi bi-exclamation-circle me-1"></i>Open Maintenance</div>
    <?php foreach ($openMaintenance as $mr): ?>
    <div class="d-flex justify-content-between small border-bottom py-1">
      <span><?= esc($mr['ticket_number']) ?></span>
      <span class="text-muted"><?= ucfirst((string) $mr['status']) ?></span>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="text-center small text-muted py-2">
    <?php if (! empty($qrImageUrl)): ?><img src="<?= esc($qrImageUrl) ?>" alt="QR" width="90" class="mb-2 opacity-50"><?php endif; ?>
    <div><?= esc($settings['company_name'] ?? 'FM ERP') ?></div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
