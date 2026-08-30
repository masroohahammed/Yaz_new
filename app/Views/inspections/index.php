<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$typeLabels = ['move_in' => 'Move-In', 'move_out' => 'Move-Out', 'routine' => 'Routine', 'handover' => 'Handover'];
?>
<div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
  <div>
    <h1><i class="bi bi-clipboard2-pulse me-2 text-primary"></i>Inspections</h1>
    <div class="small text-muted">Property, unit, and asset inspection records</div>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= base_url('compliance/unit-inspections') ?>" class="btn btn-sm btn-fm-outline"><i class="bi bi-door-open me-1"></i>Unit Forms</a>
    <a href="<?= base_url('pm-inspections/create') ?>" class="btn btn-sm btn-fm-primary"><i class="bi bi-plus-lg me-1"></i>New Inspection</a>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="kpi-card kpi-blue">
      <div class="kpi-label">Total Records</div>
      <div class="kpi-value"><?= (int) ($totalCount ?? count($inspections)) ?></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="kpi-card kpi-gold">
      <div class="kpi-label">Draft</div>
      <div class="kpi-value"><?= (int) ($draftCount ?? 0) ?></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="kpi-card kpi-green">
      <div class="kpi-label">Completed</div>
      <div class="kpi-value"><?= (int) ($completedCount ?? 0) ?></div>
    </div>
  </div>
</div>

<div class="fm-card mb-3">
  <div class="fm-card-body py-3">
    <form method="get" class="row g-2 align-items-end">
      <div class="col-md-3">
        <label class="form-label small mb-1">Property</label>
        <select name="property_id" class="form-select form-select-sm">
          <option value="">All properties</option>
          <?php foreach ($facilities as $f): ?>
          <option value="<?= $f['id'] ?>" <?= ($filters['property_id'] ?? 0) == $f['id'] ? 'selected' : '' ?>><?= esc($f['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small mb-1">Type</label>
        <select name="inspection_type" class="form-select form-select-sm">
          <option value="">All types</option>
          <?php foreach (['move_in', 'move_out', 'routine', 'handover'] as $t): ?>
          <option value="<?= $t ?>" <?= ($filters['type'] ?? '') === $t ? 'selected' : '' ?>><?= $typeLabels[$t] ?? ucfirst($t) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label small mb-1">Status</label>
        <select name="status" class="form-select form-select-sm">
          <option value="">All statuses</option>
          <?php foreach (['draft', 'completed'] as $s): ?>
          <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-fm-primary btn-sm w-100">Apply</button>
      </div>
      <div class="col-md-2">
        <a href="<?= base_url('pm-inspections') ?>" class="btn btn-fm-outline btn-sm w-100">Reset</a>
      </div>
    </form>
  </div>
</div>

<div class="fm-card">
  <div class="card-header-fm d-flex justify-content-between align-items-center">
    <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Inspection Records</h5>
    <?php if (! empty($totalCount)): ?>
    <span class="small text-muted"><?= (int) $totalCount ?> record<?= $totalCount === 1 ? '' : 's' ?></span>
    <?php endif; ?>
  </div>
  <div class="fm-card-body p-0">
    <div class="table-responsive">
      <table class="table table-registry table-sm mb-0">
        <thead>
          <tr>
            <th>Date</th>
            <th>Scope</th>
            <th>Type</th>
            <th>Subject</th>
            <th>Property</th>
            <th>Inspector</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if (empty($inspections)): ?>
          <tr>
            <td colspan="8" class="text-center py-5 text-muted">
              <i class="bi bi-clipboard2-check d-block mb-2" style="font-size:2rem"></i>
              No inspections found. <a href="<?= base_url('pm-inspections/create') ?>">Create one</a>.
            </td>
          </tr>
        <?php else: ?>
        <?php foreach ($inspections as $i): ?>
          <?php
            $viewUrl = base_url('pm-inspections/view/' . $i['id']);
            $dateVal = $i['inspection_date'] ?? $i['created_at'] ?? '';
            $dateFmt = $dateVal ? date('d M Y', strtotime((string) $dateVal)) : '—';
            $scope = (string) ($i['scope_type'] ?? 'unit');
            $scopeLabel = ucfirst($scope);
            $subject = match ($scope) {
                'property' => ($i['floor_label'] ?? '') !== '' ? 'Property · ' . $i['floor_label'] : 'Whole property',
                'asset'    => $i['asset_name'] ?? 'Asset',
                default    => 'Unit ' . ($i['unit_number'] ?? '—'),
            };
          ?>
          <tr class="inspection-row-clickable" data-href="<?= esc($viewUrl) ?>">
            <td class="small"><?= esc($dateFmt) ?></td>
            <td><span class="fm-badge"><?= esc($scopeLabel) ?></span></td>
            <td><span class="fm-badge" style="background:#eff6ff;color:#1d4ed8"><?= esc($typeLabels[$i['type']] ?? ucfirst(str_replace('_', ' ', (string) $i['type']))) ?></span></td>
            <td class="small fw-semibold"><?= esc($subject) ?></td>
            <td class="small"><?= esc($i['property_name'] ?? '—') ?></td>
            <td class="small"><?= esc($i['inspector_name'] ?: '—') ?></td>
            <td><span class="fm-badge badge-status-<?= esc($i['status'] ?? 'draft') ?>"><?= esc(ucfirst((string) ($i['status'] ?? 'draft'))) ?></span></td>
            <td class="text-end text-nowrap">
              <a href="<?= esc($viewUrl) ?>" class="btn btn-fm-outline btn-sm" title="View" onclick="event.stopPropagation()"><i class="bi bi-eye"></i></a>
              <a href="<?= base_url('pm-inspections/checklist/' . $i['id']) ?>" class="btn btn-fm-outline btn-sm" title="Checklist" onclick="event.stopPropagation()"><i class="bi bi-pencil"></i></a>
              <a href="<?= base_url('pm-inspections/print/' . $i['id']) ?>" class="btn btn-fm-outline btn-sm" title="Print" target="_blank" onclick="event.stopPropagation()"><i class="bi bi-printer"></i></a>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
    <?= view('partials/pagination', ['totalCount' => $totalCount ?? 0, 'perPage' => $perPage ?? 25, 'currentPage' => $currentPage ?? 1]) ?>
  </div>
</div>

<script>
document.querySelectorAll('.inspection-row-clickable[data-href]').forEach(function(row) {
  row.addEventListener('click', function(e) {
    if (e.target.closest('a, button')) return;
    window.location = row.dataset.href;
  });
});
</script>
<?= $this->endSection() ?>
