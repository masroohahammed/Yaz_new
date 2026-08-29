<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= esc($title ?? 'Inspections') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= base_url('assets/css/fm-workspace-ui.css') ?>" rel="stylesheet">
<?= view('partials/public_scan_theme', ['settings' => $settings ?? []]) ?>
</head>
<body class="scan-public">
<?php helper('fm'); $scope = $scope ?? []; $logoUrl = fm_logo_url($settings['company_logo'] ?? ''); ?>
<div class="scan-wrap">
  <div class="scan-brand">
    <?php if ($logoUrl): ?><img src="<?= esc($logoUrl) ?>" alt=""><?php else: ?><div class="auth-logo"><i class="bi bi-clipboard2-check"></i></div><?php endif; ?>
    <h1 class="h4 fw-bold mb-1">Inspection Forms</h1>
    <div class="small opacity-75"><?= esc($entityLabel ?? '') ?></div>
    <?php if (! empty($scope['subtitle'])): ?><div class="small opacity-75"><?= esc($scope['subtitle']) ?></div><?php endif; ?>
  </div>

  <?php if (($scope['type'] ?? '') === 'unit' && ! empty($scope['unit_id'])): ?>
  <div class="public-card">
    <h2 class="h6 fw-bold mb-3"><i class="bi bi-play-circle me-1"></i>Start inspection</h2>
    <div class="d-grid gap-2 inspection-unit-actions">
      <a href="<?= base_url('units/checklist/' . (int) $scope['unit_id'] . '/move_in') ?>" class="btn btn-success action-btn"><i class="bi bi-box-arrow-in-right me-2"></i>Move-In Inspection</a>
      <a href="<?= base_url('units/checklist/' . (int) $scope['unit_id'] . '/move_out') ?>" class="btn btn-warning action-btn"><i class="bi bi-box-arrow-right me-2"></i>Move-Out Inspection</a>
      <a href="<?= base_url('units/checklist/' . (int) $scope['unit_id'] . '/routine') ?>" class="btn btn-fm-outline action-btn"><i class="bi bi-clipboard2-check me-2"></i>Routine Inspection</a>
    </div>
  </div>
  <?php elseif (($scope['type'] ?? '') === 'property' && ! empty($units)): ?>
  <div class="public-card">
    <h2 class="h6 fw-bold mb-3"><i class="bi bi-building me-1"></i>Unit inspection forms</h2>
    <div class="d-none d-md-block table-responsive">
      <table class="table table-registry table-sm mb-0">
        <thead><tr><th>Unit</th><th>Status</th><th class="text-end">Forms</th></tr></thead>
        <tbody>
        <?php foreach ($units as $u): ?>
        <tr>
          <td class="fw-semibold">Unit <?= esc($u['unit_number']) ?></td>
          <td class="small"><span class="fm-badge"><?= esc(ucfirst($u['status'] ?? '')) ?></span></td>
          <td class="text-end text-nowrap">
            <a href="<?= base_url('units/checklist/' . (int) $u['id'] . '/move_in') ?>" class="btn btn-sm btn-success">Move-In</a>
            <a href="<?= base_url('units/checklist/' . (int) $u['id'] . '/move_out') ?>" class="btn btn-sm btn-warning">Move-Out</a>
            <a href="<?= base_url('units/checklist/' . (int) $u['id'] . '/routine') ?>" class="btn btn-sm btn-fm-outline">Routine</a>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="d-md-none">
      <?php foreach ($units as $u): ?>
      <div class="border rounded-3 p-3 mb-2 bg-white">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="fw-bold">Unit <?= esc($u['unit_number']) ?></span>
          <span class="fm-badge"><?= esc(ucfirst($u['status'] ?? '')) ?></span>
        </div>
        <div class="d-grid gap-2 inspection-unit-actions">
          <a href="<?= base_url('units/checklist/' . (int) $u['id'] . '/move_in') ?>" class="btn btn-sm btn-success action-btn">Move-In</a>
          <a href="<?= base_url('units/checklist/' . (int) $u['id'] . '/move_out') ?>" class="btn btn-sm btn-warning action-btn">Move-Out</a>
          <a href="<?= base_url('units/checklist/' . (int) $u['id'] . '/routine') ?>" class="btn btn-sm btn-fm-outline action-btn">Routine</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php elseif (($scope['type'] ?? '') === 'asset' && ! empty($scope['asset_id'])): ?>
  <div class="public-card">
    <h2 class="h6 fw-bold mb-3">Asset inspection</h2>
    <a href="<?= base_url('compliance/inspections/create?asset_id=' . (int) $scope['asset_id'] . '&facility_id=' . (int) ($scope['facility_id'] ?? 0)) ?>" class="btn btn-fm-primary action-btn w-100"><i class="bi bi-plus-circle me-2"></i>New Asset Inspection</a>
  </div>
  <?php endif; ?>

  <div class="public-card">
    <h2 class="h6 fw-bold mb-3"><i class="bi bi-journal-text me-1"></i>Inspection reports</h2>
    <?php if (empty($reports)): ?>
    <p class="text-muted small mb-0">No inspection reports yet.</p>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-registry table-sm mb-0">
        <thead><tr><th>Date</th><?php if (($scope['type'] ?? '') === 'property'): ?><th>Unit</th><?php endif; ?><th>Type</th><th>Status</th><th>By</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($reports as $row): ?>
        <tr>
          <td class="small"><?= ! empty($row['created_at']) ? date('d M Y', strtotime((string) $row['created_at'])) : '—' ?></td>
          <?php if (($scope['type'] ?? '') === 'property'): ?><td class="small fw-semibold">Unit <?= esc($row['unit_number'] ?? '—') ?></td><?php endif; ?>
          <td class="small fw-semibold"><?= esc(ucfirst(str_replace('_', ' ', (string) ($row['type'] ?? '')))) ?></td>
          <td><span class="fm-badge badge-status-<?= esc($row['status'] ?? 'draft') ?>"><?= esc(ucfirst((string) ($row['status'] ?? 'draft'))) ?></span></td>
          <td class="small"><?= esc($row['created_by_name'] ?? '—') ?></td>
          <td class="text-end"><a href="<?= base_url('units/checklist/print/' . (int) $row['id']) ?>" class="btn btn-sm btn-fm-outline" target="_blank"><i class="bi bi-printer"></i></a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <div class="text-center">
    <a href="<?= esc($backUrl ?? base_url('dashboard')) ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to scan</a>
  </div>
</div>
</body>
</html>
