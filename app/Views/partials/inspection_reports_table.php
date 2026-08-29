<?php
/** @var list<array<string,mixed>> $reports */
/** @var bool $showUnit */
/** @var int|null $unitId */
/** @var int|null $facilityId */
/** @var list<array<string,mixed>> $facilityUnits */
$reports = $reports ?? [];
$showUnit = (bool) ($showUnit ?? false);
$unitId = isset($unitId) ? (int) $unitId : null;
$facilityId = isset($facilityId) ? (int) $facilityId : null;
$facilityUnits = $facilityUnits ?? [];
$typeLabels = ['move_in' => 'Move-In', 'move_out' => 'Move-Out', 'routine' => 'Routine', 'handover' => 'Handover'];
?>
<div class="fm-card">
  <div class="card-header-fm d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h5 class="mb-0"><i class="bi bi-clipboard2-check me-2"></i>Inspection Reports</h5>
    <div class="d-flex gap-1 flex-wrap inspection-unit-actions">
      <?php if ($unitId): ?>
      <a href="<?= base_url('units/checklist/' . $unitId . '/move_in') ?>" class="btn btn-sm btn-success"><i class="bi bi-box-arrow-in-right me-1"></i>Move-In</a>
      <a href="<?= base_url('units/checklist/' . $unitId . '/move_out') ?>" class="btn btn-sm btn-warning"><i class="bi bi-box-arrow-right me-1"></i>Move-Out</a>
      <a href="<?= base_url('units/checklist/' . $unitId . '/routine') ?>" class="btn btn-sm btn-fm-outline"><i class="bi bi-clipboard2-check me-1"></i>Routine</a>
      <a href="<?= base_url('pm-inspections/create') ?>?property_id=<?= (int) ($facilityId ?? 0) ?>&unit_id=<?= $unitId ?>" class="btn btn-sm btn-fm-primary"><i class="bi bi-plus-lg me-1"></i>PM Inspection</a>
      <?php elseif ($facilityId): ?>
      <a href="<?= base_url('public/inspections?facility_id=' . $facilityId) ?>" class="btn btn-sm btn-fm-primary"><i class="bi bi-plus-circle me-1"></i>Start Inspection</a>
      <a href="<?= base_url('compliance/unit-inspections?facility_id=' . $facilityId) ?>" class="btn btn-sm btn-fm-outline"><i class="bi bi-door-open me-1"></i>All unit forms</a>
      <a href="<?= base_url('pm-inspections?property_id=' . $facilityId) ?>" class="btn btn-sm btn-fm-outline"><i class="bi bi-list me-1"></i>PM list</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="fm-card-body p-0">
    <?php if (empty($reports)): ?>
    <p class="text-muted text-center py-4 small mb-0">No inspection reports yet. Use the buttons above to start one.</p>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-registry table-sm mb-0">
        <thead>
          <tr>
            <th>Date</th>
            <?php if ($showUnit): ?><th>Unit</th><?php endif; ?>
            <th>Taken by</th>
            <th>Type</th>
            <th>Status</th>
            <th class="text-end">Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($reports as $row): ?>
          <?php
            $viewUrl = base_url('pm-inspections/view/' . (int) $row['id']);
            $status = $row['status'] ?? 'draft';
          ?>
          <tr class="inspection-row-clickable" data-href="<?= esc($viewUrl) ?>">
            <td class="small"><?= ! empty($row['created_at']) ? date('d M Y', strtotime((string) $row['created_at'])) : '—' ?></td>
            <?php if ($showUnit): ?>
            <td class="small">
              <?php if (! empty($row['unit_id']) && ! empty($row['unit_number'])): ?>
              <a href="<?= base_url('units/view/' . (int) $row['unit_id']) ?>" class="text-primary fw-semibold" onclick="event.stopPropagation()">Unit <?= esc($row['unit_number']) ?></a>
              <?php else: ?>—<?php endif; ?>
            </td>
            <?php endif; ?>
            <td class="small"><?= esc($row['created_by_name'] ?? '—') ?></td>
            <td class="small fw-semibold"><?= esc($typeLabels[$row['type'] ?? ''] ?? ucfirst(str_replace('_', ' ', (string) ($row['type'] ?? '—')))) ?></td>
            <td><span class="fm-badge badge-status-<?= esc($status) ?>"><?= esc(ucfirst((string) $status)) ?></span></td>
            <td class="text-end text-nowrap">
              <a href="<?= esc($viewUrl) ?>" class="btn btn-fm-outline btn-sm" title="View" onclick="event.stopPropagation()"><i class="bi bi-eye"></i></a>
              <a href="<?= base_url('pm-inspections/checklist/' . (int) $row['id']) ?>" class="btn btn-fm-outline btn-sm" title="Edit checklist" onclick="event.stopPropagation()"><i class="bi bi-pencil"></i></a>
              <a href="<?= base_url('units/checklist/print/' . (int) $row['id']) ?>" class="btn btn-fm-outline btn-sm" title="Print" target="_blank" onclick="event.stopPropagation()"><i class="bi bi-printer"></i></a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
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
