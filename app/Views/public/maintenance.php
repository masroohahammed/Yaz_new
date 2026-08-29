<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= esc($title ?? 'Maintenance') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<?= view('partials/public_scan_theme', ['settings' => $settings ?? []]) ?>
</head>
<body class="scan-public">
<?php
helper('fm');
$scope = $scope ?? [];
$qs = http_build_query(array_filter([
  'facility_id' => ($scope['type'] ?? '') === 'property' ? ($scope['facility_id'] ?? null) : null,
  'unit_id'     => $scope['unit_id'] ?? null,
  'asset_id'    => $scope['asset_id'] ?? null,
]));
$logoUrl = fm_logo_url($settings['company_logo'] ?? '');
?>
<div class="scan-wrap">
  <div class="scan-brand">
    <?php if ($logoUrl): ?><img src="<?= esc($logoUrl) ?>" alt=""><?php else: ?><div class="auth-logo"><i class="bi bi-buildings-fill"></i></div><?php endif; ?>
    <h1 class="h4 fw-bold mb-1">Maintenance</h1>
    <div class="small opacity-75"><?= esc($entityLabel ?? '') ?></div>
    <?php if (! empty($scope['subtitle'])): ?><div class="small opacity-75"><?= esc($scope['subtitle']) ?></div><?php endif; ?>
  </div>

  <?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success border-0 rounded-3 small"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger border-0 rounded-3 small"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <div class="public-card">
    <h2 class="h6 fw-bold mb-3"><i class="bi bi-plus-circle me-1"></i>New Maintenance Request</h2>
    <?= form_open(base_url('public/maintenance?' . $qs)) ?>
    <input type="hidden" name="facility_id" value="<?= (int) ($scope['facility_id'] ?? 0) ?>">
    <input type="hidden" name="unit_id" value="<?= (int) ($scope['unit_id'] ?? 0) ?>">
    <input type="hidden" name="asset_id" value="<?= (int) ($scope['asset_id'] ?? 0) ?>">
    <div class="row g-2">
      <div class="col-md-6"><label class="form-label">Your Name *</label><input type="text" name="requester_name" class="form-control form-control-sm" required value="<?= esc(old('requester_name')) ?>"></div>
      <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="requester_phone" class="form-control form-control-sm" value="<?= esc(old('requester_phone')) ?>"></div>
      <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="requester_email" class="form-control form-control-sm" value="<?= esc(old('requester_email')) ?>"></div>
      <div class="col-md-6"><label class="form-label">Priority</label><select name="priority" class="form-select form-select-sm"><option value="medium" selected>Medium</option><option value="high">High</option><option value="low">Low</option><option value="critical">Critical</option></select></div>
      <div class="col-12"><label class="form-label">Description *</label><textarea name="description" class="form-control form-control-sm" rows="3" required><?= esc(old('description')) ?></textarea></div>
      <div class="col-12"><button type="submit" class="btn btn-fm-primary w-100 action-btn"><i class="bi bi-send me-1"></i>Submit Request</button></div>
    </div>
    <?= form_close() ?>
  </div>

  <div class="public-card">
    <h2 class="h6 fw-bold mb-3"><i class="bi bi-list-check me-1"></i>Maintenance Requests</h2>
    <?php if (empty($records)): ?>
    <p class="text-muted small mb-0">No maintenance requests for this <?= esc($scope['type'] ?? 'entity') ?> yet.</p>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-registry table-sm mb-0">
        <thead><tr><th>Ticket</th><th>Category</th><th>Priority</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($records as $r): ?>
        <tr>
          <td class="fw-semibold small"><?= esc($r['ticket_number']) ?></td>
          <td class="small"><?= esc($r['category'] ?? '—') ?></td>
          <td class="small"><?= esc(ucfirst($r['priority'] ?? '')) ?></td>
          <td class="small"><?= esc(ucfirst($r['status'] ?? '')) ?></td>
          <td class="small text-muted"><?= esc(substr((string) ($r['created_at'] ?? ''), 0, 10)) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <div class="d-flex gap-2 flex-wrap justify-content-center">
    <a href="<?= esc($backUrl ?? base_url('request')) ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to scan</a>
    <?php if ($isLoggedIn): ?>
    <a href="<?= base_url('helpdesk/create?' . http_build_query(array_filter(['facility_id' => $scope['facility_id'] ?? null, 'unit_id' => $scope['unit_id'] ?? null, 'asset_id' => $scope['asset_id'] ?? null]))) ?>" class="btn btn-fm-outline btn-sm">Staff form</a>
    <?php else: ?>
    <a href="<?= base_url('login') ?>" class="btn btn-fm-outline btn-sm">Staff login</a>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
