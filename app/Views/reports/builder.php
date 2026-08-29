<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$prefill = $prefill ?? [];
$pType   = $prefill['report_type'] ?? '';
$pCols   = $prefill['columns'] ?? [];
$pFilters= $prefill['filters'] ?? [];
$pShowCost = ! empty($prefill['show_cost']);
?>
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div><h1><i class="bi bi-sliders me-2"></i>Custom Report Builder</h1></div>
  <a href="<?= base_url('reports/portal') ?>" class="btn btn-fm-outline btn-sm">Reports portal</a>
</div>
<?php if (session()->getFlashdata('success')): ?>
<div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<div class="row g-3">
  <div class="col-lg-8">
    <div class="fm-card"><div class="fm-card-body">
      <?= form_open(base_url('reports/builder/run'), ['method' => 'post', 'class' => 'fm-submit-form', 'id' => 'builderForm']) ?>
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label small">Report type</label>
          <select name="report_type" id="reportType" class="form-select form-select-sm" required>
            <?php foreach ($types as $key => $label): ?>
            <option value="<?= esc($key) ?>" <?= $pType === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3"><label class="form-label small">From</label><input type="date" name="from" id="filterFrom" class="form-control form-control-sm" value="<?= esc($pFilters['from'] ?? date('Y-m-01')) ?>"></div>
        <div class="col-md-3"><label class="form-label small">To</label><input type="date" name="to" id="filterTo" class="form-control form-control-sm" value="<?= esc($pFilters['to'] ?? date('Y-m-d')) ?>"></div>
        <div class="col-md-4">
          <label class="form-label small">Facility <span class="text-muted">(optional)</span></label>
          <select name="facility" id="filterFacility" class="form-select form-select-sm">
            <option value="">All facilities</option>
            <?php foreach ($facilities as $f): ?>
            <option value="<?= $f['id'] ?>" <?= (string)($pFilters['facility'] ?? '') === (string)$f['id'] ? 'selected' : '' ?>><?= esc($f['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label small">Unit <span class="text-muted">(optional)</span></label>
          <select name="unit" id="filterUnit" class="form-select form-select-sm">
            <option value="">All units</option>
            <?php foreach ($units ?? [] as $u): ?>
            <option value="<?= (int)$u['id'] ?>"
                    data-facility="<?= (int)($u['facility_id'] ?? 0) ?>"
                    <?= (string)($pFilters['unit'] ?? '') === (string)$u['id'] ? 'selected' : '' ?>>
              <?= esc($u['unit_number']) ?><?= !empty($u['facility_name']) ? ' — ' . esc($u['facility_name']) : '' ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="show_cost" value="1" id="showCost" <?= $pShowCost ? 'checked' : '' ?>>
            <label class="form-check-label small" for="showCost">Show cost columns (finance roles)</label>
          </div>
        </div>
        <div class="col-12">
          <label class="form-label small">Columns</label>
          <div id="columnChecks" class="row g-1"></div>
        </div>
      </div>
      <button type="submit" class="btn btn-fm-primary btn-sm mt-3 fm-submit-btn">Run report</button>
      <?= form_close() ?>
    </div></div>
  </div>
  <div class="col-lg-4">
    <div class="fm-card"><div class="card-header-fm"><h5>Save template</h5></div><div class="fm-card-body">
      <?= form_open(base_url('reports/builder/save'), ['method' => 'post', 'id' => 'saveTemplateForm']) ?>
      <?= csrf_field() ?>
      <input type="hidden" name="report_type" id="saveReportType">
      <input type="hidden" name="from" id="saveFrom">
      <input type="hidden" name="to" id="saveTo">
      <input type="hidden" name="facility" id="saveFacility">
      <input type="hidden" name="unit" id="saveUnit">
      <input type="hidden" name="show_cost" id="saveShowCost" value="0">
      <div id="saveColumnsHidden"></div>
      <input type="text" name="save_name" class="form-control form-control-sm mb-2" placeholder="Template name" value="<?= esc($prefill['name'] ?? '') ?>" required>
      <button type="submit" class="btn btn-fm-outline btn-sm w-100">Save current selection</button>
      <?= form_close() ?>
    </div></div>
    <?php if (! empty($saved)): ?>
    <div class="fm-card mt-3"><div class="card-header-fm"><h5>Saved templates</h5></div><div class="fm-card-body p-0">
      <ul class="list-group list-group-flush small">
        <?php foreach ($saved as $s): ?>
        <li class="list-group-item d-flex justify-content-between align-items-center gap-2">
          <a href="<?= base_url('reports/builder?load='.$s['id']) ?>" class="text-decoration-none"><?= esc($s['name']) ?> <span class="text-muted">(<?= esc($s['report_type']) ?>)</span></a>
          <a href="<?= base_url('reports/builder/delete/'.$s['id']) ?>" class="text-danger" onclick="return confirm('Delete this template?')"><i class="bi bi-trash"></i></a>
        </li>
        <?php endforeach; ?>
      </ul>
    </div></div>
    <?php endif; ?>
  </div>
</div>
<script>
const columnMap = <?= json_encode($columns) ?>;
let prefillCols = <?= json_encode(array_values($pCols)) ?>;
const prefillType = <?= json_encode($pType) ?>;
function renderColumns(type) {
  const box = document.getElementById('columnChecks');
  const cols = columnMap[type] || [];
  const selected = prefillCols.length && document.getElementById('reportType').value === prefillType ? new Set(prefillCols) : null;
  box.innerHTML = cols.map(c => {
    const checked = !selected || selected.has(c) ? ' checked' : '';
    return '<div class="col-md-4"><label class="small"><input type="checkbox" name="columns[]" value="'+c+'"'+checked+'> '+c.replace(/_/g,' ')+'</label></div>';
  }).join('');
  document.getElementById('saveReportType').value = type;
}
document.getElementById('reportType').addEventListener('change', () => { prefillCols = []; renderColumns(document.getElementById('reportType').value); });
renderColumns(document.getElementById('reportType').value);

// Facility → Unit cascade filter
(function () {
  const facSel  = document.getElementById('filterFacility');
  const unitSel = document.getElementById('filterUnit');
  if (!facSel || !unitSel) return;

  function filterUnits() {
    const fid = facSel.value;
    let hasVisible = false;
    Array.from(unitSel.options).forEach(opt => {
      if (!opt.value) return; // keep placeholder
      const match = !fid || String(opt.dataset.facility) === String(fid);
      opt.hidden = !match;
      if (match) hasVisible = true;
    });
    // Clear unit if it no longer matches selected facility
    const cur = unitSel.value;
    if (cur) {
      const curOpt = unitSel.querySelector('option[value="'+cur+'"]');
      if (curOpt && curOpt.hidden) unitSel.value = '';
    }
  }
  facSel.addEventListener('change', filterUnits);
  filterUnits(); // apply on load (handles prefill)
})();

document.getElementById('saveTemplateForm').addEventListener('submit', function() {
  const f = document.getElementById('builderForm');
  document.getElementById('saveFrom').value = f.from.value;
  document.getElementById('saveTo').value = f.to.value;
  document.getElementById('saveFacility').value = f.facility.value;
  document.getElementById('saveUnit').value = f.unit ? f.unit.value : '';
  document.getElementById('saveShowCost').value = f.show_cost && f.show_cost.checked ? '1' : '0';
  const hid = document.getElementById('saveColumnsHidden');
  hid.innerHTML = '';
  f.querySelectorAll('input[name="columns[]"]:checked').forEach(cb => {
    const i = document.createElement('input');
    i.type = 'hidden'; i.name = 'columns[]'; i.value = cb.value;
    hid.appendChild(i);
  });
});
</script>
<?= $this->endSection() ?>
