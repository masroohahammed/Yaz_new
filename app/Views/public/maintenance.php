<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= esc($title ?? 'Maintenance') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= base_url('assets/css/fm-workspace-ui.css') ?>" rel="stylesheet">
<?= view('partials/public_scan_theme', ['settings' => $settings ?? []]) ?>
</head>
<body class="scan-public">
<?php
helper('fm');
$scope = $scope ?? [];
$units = $units ?? [];
$user = $user ?? null;
$qs = http_build_query(array_filter([
  'facility_id' => ($scope['type'] ?? '') === 'property' ? ($scope['facility_id'] ?? null) : null,
  'unit_id'     => $scope['unit_id'] ?? null,
  'asset_id'    => $scope['asset_id'] ?? null,
]));
$logoUrl = fm_logo_url($settings['company_logo'] ?? '');
$oldUnit = (int) old('unit_id');
?>
<div class="scan-wrap">
  <div class="scan-brand">
    <?php if ($logoUrl): ?><img src="<?= esc($logoUrl) ?>" alt=""><?php else: ?><div class="auth-logo"><i class="bi bi-buildings-fill"></i></div><?php endif; ?>
    <h1 class="h4 fw-bold mb-1">Maintenance Request</h1>
    <div class="small opacity-75"><?= esc($entityLabel ?? '') ?></div>
    <?php if (! empty($scope['subtitle'])): ?><div class="small opacity-75"><?= esc($scope['subtitle']) ?></div><?php endif; ?>
  </div>

  <?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success border-0 rounded-3 small"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger border-0 rounded-3 small"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('errors')): ?>
  <div class="alert alert-danger border-0 rounded-3 small">
    <?php foreach ((array) session()->getFlashdata('errors') as $e): ?><div><?= esc($e) ?></div><?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="public-card">
    <h2 class="h6 fw-bold mb-1"><i class="bi bi-wrench-adjustable me-1"></i>Submit Maintenance Request</h2>
    <p class="text-muted small mb-3">Describe the issue and attach a photo if helpful. A ticket number will be generated for tracking.</p>

    <?= form_open_multipart(base_url('public/maintenance?' . $qs)) ?>
    <input type="hidden" name="facility_id" value="<?= (int) ($scope['facility_id'] ?? 0) ?>">
    <input type="hidden" name="unit_id" id="hiddenUnitId" value="<?= (int) ($scope['unit_id'] ?? $oldUnit) ?>">
    <input type="hidden" name="asset_id" value="<?= (int) ($scope['asset_id'] ?? 0) ?>">

    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label fw-semibold small">Your name <span class="text-danger">*</span></label>
        <input type="text" name="requester_name" class="form-control" required
          value="<?= esc(old('requester_name', $user['name'] ?? '')) ?>">
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold small">Phone</label>
        <input type="text" name="requester_phone" class="form-control" value="<?= esc(old('requester_phone')) ?>" placeholder="+974 XXXX XXXX">
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold small">Email</label>
        <input type="email" name="requester_email" class="form-control" value="<?= esc(old('requester_email', $user['email'] ?? '')) ?>">
      </div>

      <?php if (($scope['type'] ?? '') === 'property' && ! empty($units)): ?>
      <div class="col-md-6">
        <label class="form-label fw-semibold small">Unit (optional)</label>
        <select name="unit_id" id="unitSelect" class="form-select">
          <option value="">— Property-wide / not unit-specific —</option>
          <?php foreach ($units as $u): ?>
          <option value="<?= (int) $u['id'] ?>" <?= $oldUnit === (int) $u['id'] ? 'selected' : '' ?>>
            Unit <?= esc($u['unit_number']) ?><?= ! empty($u['floor']) ? ' · Floor ' . esc($u['floor']) : '' ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>

      <div class="col-md-6">
        <label class="form-label fw-semibold small">Category</label>
        <select name="category" class="form-select" required>
          <option value="">— Select category —</option>
          <?php foreach (['Electrical', 'HVAC', 'Plumbing', 'Fire Safety', 'Security', 'Civil / Structural', 'Cleaning', 'IT / Telecom', 'General', 'Other'] as $c): ?>
          <option value="<?= esc($c) ?>" <?= old('category') === $c ? 'selected' : '' ?>><?= esc($c) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold small">Priority <span class="text-danger">*</span></label>
        <select name="priority" class="form-select" required>
          <?php foreach (['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'] as $val => $label): ?>
          <option value="<?= $val ?>" <?= old('priority', 'medium') === $val ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12">
        <label class="form-label fw-semibold small">Description <span class="text-danger">*</span></label>
        <textarea name="description" class="form-control" rows="4" required placeholder="Describe the issue, location, when it started, and any access instructions…"><?= esc(old('description')) ?></textarea>
      </div>
      <div class="col-12">
        <label class="form-label fw-semibold small"><i class="bi bi-camera me-1"></i>Photo (optional)</label>
        <input type="file" name="image" class="form-control" accept="image/*">
        <div class="form-text">Upload a photo of the issue if available.</div>
      </div>
      <div class="col-12">
        <button type="submit" class="btn btn-fm-primary w-100 action-btn py-2"><i class="bi bi-send me-1"></i>Submit Request</button>
      </div>
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
        <thead><tr><th>Ticket</th><th>Unit</th><th>Category</th><th>Priority</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($records as $r): ?>
        <tr>
          <td class="fw-semibold small"><?= esc($r['ticket_number']) ?></td>
          <td class="small"><?= esc($r['unit_number'] ?? '—') ?></td>
          <td class="small"><?= esc($r['category'] ?? '—') ?></td>
          <td class="small"><?= esc(ucfirst($r['priority'] ?? '')) ?></td>
          <td><span class="fm-badge badge-status-<?= esc($r['status'] ?? 'pending') ?>"><?= esc(ucfirst($r['status'] ?? '')) ?></span></td>
          <td class="small text-muted"><?= esc(substr((string) ($r['created_at'] ?? ''), 0, 10)) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <div class="d-flex gap-2 flex-wrap justify-content-center">
    <a href="<?= esc($backUrl ?? base_url('request')) ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    <?php if ($isLoggedIn): ?>
    <a href="<?= base_url('helpdesk/create?' . http_build_query(array_filter(['facility_id' => $scope['facility_id'] ?? null, 'unit_id' => $scope['unit_id'] ?? null]))) ?>" class="btn btn-fm-outline btn-sm">Staff helpdesk form</a>
    <?php endif; ?>
  </div>
</div>
<script>
(function() {
  const unitSelect = document.getElementById('unitSelect');
  const hiddenUnit = document.getElementById('hiddenUnitId');
  if (unitSelect && hiddenUnit) {
    unitSelect.addEventListener('change', function() {
      hiddenUnit.value = unitSelect.value || '';
    });
  }
})();
</script>
</body>
</html>
