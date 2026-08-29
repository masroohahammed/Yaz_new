<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$i = $inspection;
$data = $items ?? [];
$areas = $data['areas'] ?? [];
$ratings = $data['ratings'] ?? [];
$notes = $data['notes'] ?? [];
$typeLabel = ucfirst(str_replace('_', ' ', (string) ($i['type'] ?? 'routine')));
$dateVal = $i['inspection_date'] ?? $i['created_at'] ?? '';
$dateFmt = $dateVal ? date('d M Y', strtotime((string) $dateVal)) : '—';

$conditionScore = function (string $c): int {
    return match ($c) {
        'excellent' => 5,
        'good'      => 4,
        'fair'      => 3,
        'poor'      => 2,
        'damaged'   => 1,
        default     => 0,
    };
};
$scores = array_map(fn ($r) => $conditionScore((string) $r), $ratings);
$avgScore = count($scores) ? round(array_sum($scores) / count($scores) * 20) : 0;
$issueCount = count(array_filter($ratings, fn ($r) => in_array($r, ['poor', 'damaged'], true)));
?>

<div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
  <div>
    <h1><i class="bi bi-clipboard2-check me-2 text-success"></i>Unit <?= esc($i['unit_number']) ?> — <?= esc($typeLabel) ?></h1>
    <div class="small text-muted"><?= esc($i['property_name']) ?> · <?= esc($dateFmt) ?> · <?= esc($i['inspector_name'] ?: 'Inspector not recorded') ?></div>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0 mt-1">
      <li class="breadcrumb-item"><a href="<?= base_url('pm-inspections') ?>">Inspections</a></li>
      <li class="breadcrumb-item active">Report #<?= (int) $i['id'] ?></li>
    </ol></nav>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= base_url('pm-inspections/checklist/' . $i['id']) ?>" class="btn btn-sm btn-fm-primary"><i class="bi bi-pencil me-1"></i><?= ($i['status'] ?? '') === 'completed' ? 'Edit Checklist' : 'Continue Checklist' ?></a>
    <a href="<?= base_url('pm-inspections/link/' . $i['id']) ?>" class="btn btn-sm btn-fm-outline"><i class="bi bi-link-45deg me-1"></i>Link</a>
    <a href="<?= base_url('pm-inspections/print/' . $i['id']) ?>" class="btn btn-sm btn-fm-outline" target="_blank"><i class="bi bi-printer me-1"></i>Print</a>
    <a href="<?= base_url('units/view/' . (int) ($i['unit_id'] ?? 0)) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-door-open me-1"></i>Unit</a>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-3">
    <div class="kpi-card kpi-<?= $avgScore >= 80 ? 'green' : ($avgScore >= 60 ? 'gold' : 'red') ?>">
      <div class="kpi-label">Condition Score</div>
      <div class="kpi-value"><?= count($areas) ? $avgScore . '%' : '—' ?></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="kpi-card kpi-blue">
      <div class="kpi-label">Areas Checked</div>
      <div class="kpi-value"><?= count($areas) ?></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="kpi-card kpi-<?= $issueCount ? 'red' : 'green' ?>">
      <div class="kpi-label">Issues Found</div>
      <div class="kpi-value"><?= $issueCount ?></div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="kpi-card kpi-primary">
      <div class="kpi-label">Status</div>
      <div class="kpi-value" style="font-size:1.1rem"><span class="fm-badge badge-status-<?= esc($i['status'] ?? 'draft') ?>"><?= esc(ucfirst((string) ($i['status'] ?? 'draft'))) ?></span></div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="fm-card">
      <div class="card-header-fm">
        <h5 class="mb-0"><i class="bi bi-list-check me-2"></i>Area Breakdown</h5>
        <?php if (! empty($i['overall_condition'])): ?>
        <span class="fm-badge condition-badge-<?= esc($i['overall_condition']) ?>">Overall: <?= ucfirst(esc($i['overall_condition'])) ?></span>
        <?php endif; ?>
      </div>
      <div class="fm-card-body p-0">
        <?php if (empty($areas)): ?>
        <div class="text-center py-5 text-muted">
          <i class="bi bi-clipboard2 d-block mb-2" style="font-size:2rem"></i>
          No checklist data yet. <a href="<?= base_url('pm-inspections/checklist/' . $i['id']) ?>">Fill in the checklist</a>.
        </div>
        <?php else: ?>
        <?php foreach ($areas as $idx => $area): ?>
        <?php
          $rating = $ratings[$idx] ?? '';
          $note = $notes[$idx] ?? '';
          $badgeClass = $rating ? 'condition-badge-' . $rating : '';
        ?>
        <div class="d-flex align-items-start gap-3 px-4 py-3 border-bottom <?= in_array($rating, ['poor', 'damaged'], true) ? 'sla-warn' : '' ?>">
          <div class="text-muted small pt-1" style="min-width:24px"><?= $idx + 1 ?>.</div>
          <div class="flex-grow-1">
            <div class="fw-semibold mb-1"><?= esc($area) ?></div>
            <?php if ($rating): ?>
            <span class="fm-badge <?= esc($badgeClass) ?>"><?= ucfirst(esc($rating)) ?></span>
            <?php else: ?>
            <span class="text-muted small">Not rated</span>
            <?php endif; ?>
            <?php if ($note): ?>
            <div class="small text-muted mt-1"><?= esc($note) ?></div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="fm-card mb-3">
      <div class="card-header-fm"><h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Details</h5></div>
      <div class="fm-card-body small">
        <dl class="mb-0">
          <dt class="text-muted">Property</dt><dd><?= esc($i['property_name']) ?></dd>
          <dt class="text-muted">Unit</dt><dd><a href="<?= base_url('units/view/' . (int) ($i['unit_id'] ?? 0)) ?>"><?= esc($i['unit_number']) ?></a></dd>
          <dt class="text-muted">Type</dt><dd><?= esc($typeLabel) ?></dd>
          <dt class="text-muted">Inspection date</dt><dd><?= esc($dateFmt) ?></dd>
          <dt class="text-muted">Inspector</dt><dd><?= esc($i['inspector_name'] ?: '—') ?></dd>
          <?php if (! empty($i['link_to']) && ! empty($i['ref_id'])): ?>
          <dt class="text-muted">Linked to</dt><dd><?= esc(ucfirst(str_replace('_', ' ', (string) $i['link_to']))) ?> #<?= (int) $i['ref_id'] ?></dd>
          <?php endif; ?>
        </dl>
      </div>
    </div>

    <?php if (! empty($i['notes'])): ?>
    <div class="fm-card">
      <div class="card-header-fm"><h5 class="mb-0"><i class="bi bi-card-text me-2"></i>Summary Notes</h5></div>
      <div class="fm-card-body small"><?= nl2br(esc($i['notes'])) ?></div>
    </div>
    <?php endif; ?>
  </div>
</div>
<?= $this->endSection() ?>
