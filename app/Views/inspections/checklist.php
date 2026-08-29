<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$i = $inspection;
$defaultAreas = ['Kitchen', 'Bathroom', 'Living Room', 'Bedroom'];
$saved = $savedData ?? [];
$areas = ! empty($saved['areas']) ? $saved['areas'] : $defaultAreas;
$ratings = $saved['ratings'] ?? [];
$notes = $saved['notes'] ?? [];
$conditions = ['excellent', 'good', 'fair', 'poor', 'damaged'];
$typeLabel = ucfirst(str_replace('_', ' ', (string) ($i['type'] ?? 'routine')));
$isDraft = ($i['status'] ?? 'draft') === 'draft';
?>
<div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
  <div>
    <h1><i class="bi bi-list-check me-2 text-primary"></i>Inspection Checklist</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="<?= base_url('pm-inspections') ?>">Inspections</a></li>
      <li class="breadcrumb-item"><a href="<?= base_url('pm-inspections/view/' . $i['id']) ?>">Unit <?= esc($i['unit_number']) ?></a></li>
      <li class="breadcrumb-item active">Checklist</li>
    </ol></nav>
  </div>
  <a href="<?= base_url('pm-inspections/view/' . $i['id']) ?>" class="btn btn-sm btn-fm-outline"><i class="bi bi-eye me-1"></i>View Report</a>
</div>

<div class="fm-form-section mb-3">
  <div class="row g-3 text-center">
    <div class="col-6 col-md-3"><div class="x-small text-muted">UNIT</div><div class="fw-bold"><?= esc($i['unit_number']) ?></div></div>
    <div class="col-6 col-md-3"><div class="x-small text-muted">PROPERTY</div><div class="fw-bold"><?= esc($i['property_name']) ?></div></div>
    <div class="col-6 col-md-3"><div class="x-small text-muted">TYPE</div><div class="fw-bold"><?= esc($typeLabel) ?></div></div>
    <div class="col-6 col-md-3"><div class="x-small text-muted">DATE</div><div class="fw-bold"><?= esc($i['inspection_date'] ? date('d M Y', strtotime((string) $i['inspection_date'])) : date('d M Y')) ?></div></div>
  </div>
</div>

<?= form_open(base_url('pm-inspections/checklist/' . $i['id']), ['id' => 'checklistForm']) ?>
<?= csrf_field() ?>

<div class="fm-card mb-3">
  <div class="card-header-fm d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h5 class="mb-0"><i class="bi bi-house-door me-2"></i>Room Areas</h5>
    <div class="d-flex align-items-center gap-2" style="min-width:180px">
      <span class="small text-muted" id="progressLabel">0 / <?= count($areas) ?> rated</span>
      <div class="inspection-progress-wrap flex-grow-1" style="max-width:120px">
        <div class="inspection-progress-bar" id="progressBar" style="width:0%"></div>
      </div>
    </div>
  </div>
  <div class="fm-card-body" id="areasContainer">
    <?php foreach ($areas as $idx => $area): ?>
    <?php $rating = $ratings[$idx] ?? 'good'; $note = $notes[$idx] ?? ''; ?>
    <div class="inspection-area-card" data-area-index="<?= $idx ?>">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
        <h6 class="mb-0 fw-bold"><i class="bi bi-geo-alt me-1 text-muted"></i><?= esc($area) ?></h6>
        <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-area-btn" title="Remove area"><i class="bi bi-x-lg"></i></button>
      </div>
      <input type="hidden" name="areas[]" value="<?= esc($area) ?>">
      <input type="hidden" name="condition_rating[]" class="condition-input" value="<?= esc($rating) ?>">
      <div class="inspection-condition-group mb-2" role="group" aria-label="Condition for <?= esc($area) ?>">
        <?php foreach ($conditions as $c): ?>
        <button type="button" class="inspection-condition-btn<?= $rating === $c ? ' active' : '' ?>" data-value="<?= $c ?>"><?= ucfirst($c) ?></button>
        <?php endforeach; ?>
      </div>
      <input type="text" name="item_notes[]" class="form-control form-control-sm area-notes" placeholder="Notes for <?= esc($area) ?> (optional)" value="<?= esc($note) ?>">
    </div>
    <?php endforeach; ?>
  </div>
  <div class="fm-card-body border-top pt-2 pb-2">
    <button type="button" class="btn btn-sm btn-fm-outline" id="addAreaBtn"><i class="bi bi-plus-lg me-1"></i>Add Area</button>
  </div>
</div>

<div class="fm-card mb-3">
  <div class="card-header-fm"><h5 class="mb-0"><i class="bi bi-card-text me-2"></i>Overall Assessment</h5></div>
  <div class="fm-card-body">
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label fw-semibold small">Overall condition</label>
        <select name="overall_condition" class="form-select form-select-sm">
          <?php foreach (['excellent', 'good', 'fair', 'poor'] as $c): ?>
          <option value="<?= $c ?>" <?= ($i['overall_condition'] ?? '') === $c ? 'selected' : '' ?>><?= ucfirst($c) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12">
        <label class="form-label fw-semibold small">Summary notes</label>
        <textarea name="overall_notes" class="form-control" rows="3" placeholder="Overall observations, follow-up actions, tenant acknowledgement…"><?= esc($i['notes'] ?? '') ?></textarea>
      </div>
    </div>
  </div>
  <div class="inspection-sticky-actions d-flex gap-2 flex-wrap">
    <?php if ($isDraft): ?>
    <button type="submit" name="submit_action" value="save" class="btn btn-fm-outline"><i class="bi bi-save me-1"></i>Save Draft</button>
    <?php endif; ?>
    <button type="submit" name="submit_action" value="complete" class="btn btn-fm-primary"><i class="bi bi-check-circle me-1"></i><?= $isDraft ? 'Complete Checklist' : 'Save Changes' ?></button>
    <a href="<?= base_url('pm-inspections/view/' . $i['id']) ?>" class="btn btn-outline-secondary">Cancel</a>
  </div>
</div>
<?= form_close() ?>

<script>
(function() {
  const conditions = <?= json_encode($conditions) ?>;
  const container = document.getElementById('areasContainer');
  const progressBar = document.getElementById('progressBar');
  const progressLabel = document.getElementById('progressLabel');

  function bindAreaCard(card) {
    const hidden = card.querySelector('.condition-input');
    card.querySelectorAll('.inspection-condition-btn').forEach(function(btn) {
      btn.addEventListener('click', function() {
        card.querySelectorAll('.inspection-condition-btn').forEach(function(b) { b.classList.remove('active'); });
        btn.classList.add('active');
        hidden.value = btn.dataset.value;
        updateProgress();
      });
    });
    const removeBtn = card.querySelector('.remove-area-btn');
    if (removeBtn) {
      removeBtn.addEventListener('click', function() {
        if (container.querySelectorAll('.inspection-area-card').length <= 1) return;
        card.remove();
        updateProgress();
      });
    }
  }

  function updateProgress() {
    const cards = container.querySelectorAll('.inspection-area-card');
    let rated = 0;
    cards.forEach(function(card) {
      const val = card.querySelector('.condition-input').value;
      if (val) {
        rated++;
        card.classList.add('area-complete');
      } else {
        card.classList.remove('area-complete');
      }
    });
    const total = cards.length || 1;
    const pct = Math.round((rated / total) * 100);
    progressBar.style.width = pct + '%';
    progressLabel.textContent = rated + ' / ' + total + ' rated';
  }

  container.querySelectorAll('.inspection-area-card').forEach(bindAreaCard);
  updateProgress();

  document.getElementById('addAreaBtn').addEventListener('click', function() {
    const name = prompt('Area name (e.g. Balcony, Storage):');
    if (!name || !name.trim()) return;
    const idx = container.querySelectorAll('.inspection-area-card').length;
    const div = document.createElement('div');
    div.className = 'inspection-area-card';
    div.dataset.areaIndex = idx;
    let btns = conditions.map(function(c) {
      return '<button type="button" class="inspection-condition-btn' + (c === 'good' ? ' active' : '') + '" data-value="' + c + '">' + c.charAt(0).toUpperCase() + c.slice(1) + '</button>';
    }).join('');
    div.innerHTML =
      '<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">' +
        '<h6 class="mb-0 fw-bold"><i class="bi bi-geo-alt me-1 text-muted"></i>' + name.trim() + '</h6>' +
        '<button type="button" class="btn btn-sm btn-link text-danger p-0 remove-area-btn"><i class="bi bi-x-lg"></i></button>' +
      '</div>' +
      '<input type="hidden" name="areas[]" value="' + name.trim().replace(/"/g, '&quot;') + '">' +
      '<input type="hidden" name="condition_rating[]" class="condition-input" value="good">' +
      '<div class="inspection-condition-group mb-2">' + btns + '</div>' +
      '<input type="text" name="item_notes[]" class="form-control form-control-sm area-notes" placeholder="Notes (optional)">';
    container.appendChild(div);
    bindAreaCard(div);
    updateProgress();
  });
})();
</script>
<?= $this->endSection() ?>
