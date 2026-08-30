<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$scopeType = $scopeType ?? 'unit';
$isProperty = $scopeType === 'property';
$isAsset = $scopeType === 'asset';
$isUnit = $scopeType === 'unit';
$scopeLabels = ['property' => 'Property', 'unit' => 'Unit', 'asset' => 'Asset'];
$propertyFloors = max(1, (int) ($propertyFloors ?? 1));
?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-plus-circle me-2 text-primary"></i>New <?= esc($scopeLabels[$scopeType] ?? 'Unit') ?> Inspection</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="<?= base_url('pm-inspections') ?>">Inspections</a></li>
      <li class="breadcrumb-item active">New</li>
    </ol></nav>
  </div>
</div>

<?php if (session()->getFlashdata('error')): ?>
<div class="alert alert-danger border-0 rounded-3"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-8">
    <?= form_open(base_url('pm-inspections/store')) ?>
    <?= csrf_field() ?>
    <input type="hidden" name="scope_type" value="<?= esc($scopeType) ?>">
    <?php if ($isAsset && ! empty($preselectAsset)): ?>
    <input type="hidden" name="asset_id" value="<?= (int) $preselectAsset ?>">
    <?php endif; ?>

    <div class="fm-card mb-3">
      <div class="card-header-fm"><h5 class="mb-0"><i class="bi bi-building me-2"></i>Location</h5></div>
      <div class="fm-card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold small">Property <span class="text-danger">*</span></label>
            <select name="property_id" id="propertyId" class="form-select" required <?= $isAsset && ! empty($assetRow) ? 'disabled' : '' ?>>
              <?php foreach ($facilities as $f): ?>
              <?php $selProp = (int) ($preselectProperty ?? 0); ?>
              <option value="<?= $f['id'] ?>" data-floors="<?= max(1, (int) ($f['floors'] ?? 1)) ?>" <?= $selProp === (int) $f['id'] ? 'selected' : '' ?>><?= esc($f['name']) ?></option>
              <?php endforeach; ?>
            </select>
            <?php if ($isAsset && ! empty($assetRow)): ?>
            <input type="hidden" name="property_id" value="<?= (int) ($assetRow['facility_id'] ?? $preselectProperty) ?>">
            <?php endif; ?>
          </div>

          <?php if ($isAsset): ?>
          <div class="col-md-6">
            <label class="form-label fw-semibold small">Asset</label>
            <input type="text" class="form-control" readonly value="<?= esc(($assetRow['name'] ?? '') . (! empty($assetRow['asset_code']) ? ' · ' . $assetRow['asset_code'] : '')) ?>">
          </div>
          <?php elseif ($isProperty): ?>
          <div class="col-md-6">
            <label class="form-label fw-semibold small">Floor</label>
            <select name="floor_label" id="floorLabel" class="form-select">
              <option value="">All floors / whole property</option>
              <?php for ($fl = 1; $fl <= $propertyFloors; $fl++): ?>
              <?php $flLabel = 'Floor ' . $fl; ?>
              <option value="<?= esc($flLabel) ?>" <?= ($preselectFloor ?? '') === $flLabel ? 'selected' : '' ?>><?= esc($flLabel) ?></option>
              <?php endfor; ?>
              <option value="custom">Custom floor / area label…</option>
            </select>
            <input type="text" name="floor_label_custom" id="floorCustom" class="form-control mt-2 d-none" placeholder="e.g. Basement, Roof, Parking Level B1">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold small">Unit <span class="text-muted">(optional)</span></label>
            <div class="position-relative">
              <select name="unit_id" id="unitId" class="form-select">
                <option value="">Property-wide inspection</option>
              </select>
              <div id="unitLoading" class="position-absolute top-50 end-0 translate-middle-y me-3 d-none">
                <span class="spinner-border spinner-border-sm text-muted" role="status"></span>
              </div>
            </div>
            <div id="unitHint" class="form-text">Leave blank to inspect common areas and the whole property.</div>
          </div>
          <?php else: ?>
          <div class="col-md-6">
            <label class="form-label fw-semibold small">Unit <span class="text-danger">*</span></label>
            <div class="position-relative">
              <select name="unit_id" id="unitId" class="form-select" required>
                <option value="">Loading units…</option>
              </select>
              <div id="unitLoading" class="position-absolute top-50 end-0 translate-middle-y me-3 d-none">
                <span class="spinner-border spinner-border-sm text-muted" role="status"></span>
              </div>
            </div>
            <div id="unitHint" class="form-text">Select a property first, then choose the unit to inspect.</div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="fm-card mb-3">
      <div class="card-header-fm"><h5 class="mb-0"><i class="bi bi-tag me-2"></i>Inspection Type</h5></div>
      <div class="fm-card-body">
        <div class="inspection-type-grid" id="typeGrid">
          <?php
          $types = $isProperty || $isAsset
              ? [['routine', 'clipboard2-check', 'Routine', 'Periodic inspection check', 'primary']]
              : [
                  ['move_in', 'box-arrow-in-right', 'Move-In', 'Tenant arrival checklist', 'success'],
                  ['move_out', 'box-arrow-right', 'Move-Out', 'Departure & handback', 'warning'],
                  ['routine', 'clipboard2-check', 'Routine', 'Periodic unit check', 'primary'],
              ];
          foreach ($types as [$val, $icon, $label, $desc, $color]):
          ?>
          <label class="inspection-type-card" data-type="<?= $val ?>">
            <input type="radio" name="inspection_type" value="<?= $val ?>" <?= $val === 'routine' ? 'checked' : '' ?>>
            <span class="type-icon text-<?= $color ?>"><i class="bi bi-<?= $icon ?>"></i></span>
            <div class="fw-bold small"><?= $label ?></div>
            <div class="x-small text-muted"><?= $desc ?></div>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <div class="fm-card mb-3">
      <div class="card-header-fm"><h5 class="mb-0"><i class="bi bi-person-badge me-2"></i>Details</h5></div>
      <div class="fm-card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold small">Inspection date</label>
            <input type="date" name="inspection_date" class="form-control" value="<?= date('Y-m-d') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold small">Inspector name</label>
            <input type="text" name="inspector" class="form-control" placeholder="Who is conducting this inspection?">
          </div>
        </div>
      </div>
    </div>

    <div class="d-flex gap-2 flex-wrap">
      <button type="submit" id="submitBtn" class="btn btn-fm-primary" <?= $isUnit ? 'disabled' : '' ?>><i class="bi bi-arrow-right-circle me-1"></i>Create &amp; Open Checklist</button>
      <a href="<?= base_url('pm-inspections') ?>" class="btn btn-fm-outline">Cancel</a>
    </div>
    <?= form_close() ?>
  </div>

  <div class="col-lg-4">
    <div class="fm-card">
      <div class="card-header-fm"><h5 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Tips</h5></div>
      <div class="fm-card-body small text-muted">
        <?php if ($isProperty): ?>
        <ul class="mb-0 ps-3">
          <li class="mb-2">Inspect common areas, exterior, roof, basement, and landscaping.</li>
          <li class="mb-2">Select a floor when the property has multiple levels.</li>
          <li>Add custom inspection areas on the checklist page.</li>
        </ul>
        <?php elseif ($isAsset): ?>
        <ul class="mb-0 ps-3">
          <li class="mb-2">Document safety, compliance, and operational condition.</li>
          <li>Mark critical issues that need immediate attention.</li>
        </ul>
        <?php else: ?>
        <ul class="mb-0 ps-3">
          <li class="mb-2"><strong>Move-In</strong> — document unit condition before tenant occupancy.</li>
          <li class="mb-2"><strong>Move-Out</strong> — compare against move-in and note damage.</li>
          <li><strong>Routine</strong> — scheduled maintenance and compliance checks.</li>
        </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  const isUnitScope = <?= $isUnit ? 'true' : 'false' ?>;
  const isPropertyScope = <?= $isProperty ? 'true' : 'false' ?>;
  const form = document.querySelector('form');
  const propertyEl = document.getElementById('propertyId');
  const unitEl = document.getElementById('unitId');
  const loadingEl = document.getElementById('unitLoading');
  const hintEl = document.getElementById('unitHint');
  const submitBtn = document.getElementById('submitBtn');
  const typeCards = document.querySelectorAll('.inspection-type-card');
  const preselectUnit = <?= (int) ($preselectUnit ?? 0) ?>;
  const preselectType = <?= json_encode($preselectType ?? '') ?>;
  const unitsUrl = '<?= base_url('helpdesk/ajax/units/') ?>';
  const fallbackUnitsUrl = '<?= base_url('contracts/ajax/units/') ?>';
  let unitsReady = !isUnitScope;

  const floorEl = document.getElementById('floorLabel');
  const floorCustom = document.getElementById('floorCustom');

  function syncTypeCards() {
    typeCards.forEach(function(card) {
      const input = card.querySelector('input[type="radio"]');
      card.classList.toggle('selected', input && input.checked);
    });
  }

  typeCards.forEach(function(card) {
    card.addEventListener('click', function() {
      const input = card.querySelector('input[type="radio"]');
      if (input) { input.checked = true; syncTypeCards(); }
    });
  });
  syncTypeCards();

  if (preselectType) {
    typeCards.forEach(function(card) {
      const input = card.querySelector('input[type="radio"]');
      if (input && input.value === preselectType) {
        input.checked = true;
      }
    });
    syncTypeCards();
  }

  function setSubmitState() {
    if (!isUnitScope) {
      submitBtn.disabled = false;
      return;
    }
    const hasUnit = unitEl && unitEl.value && unitEl.value !== '0';
    submitBtn.disabled = !unitsReady || !hasUnit;
  }

  function fetchUnits(url) {
    return fetch(url + propertyEl.value, { headers: { Accept: 'application/json' } })
      .then(function(r) { return r.json(); });
  }

  function rebuildFloorOptions(floors) {
    if (!floorEl) return;
    const current = floorEl.value;
    floorEl.innerHTML = '<option value="">All floors / whole property</option>';
    for (let fl = 1; fl <= floors; fl++) {
      const label = 'Floor ' + fl;
      const opt = document.createElement('option');
      opt.value = label;
      opt.textContent = label;
      if (current === label) opt.selected = true;
      floorEl.appendChild(opt);
    }
    const customOpt = document.createElement('option');
    customOpt.value = 'custom';
    customOpt.textContent = 'Custom floor / area label…';
    floorEl.appendChild(customOpt);
  }

  function loadUnits() {
    if (!unitEl) return;
    const pid = propertyEl.value;
    if (isUnitScope) {
      unitsReady = false;
      setSubmitState();
    }
    loadingEl.classList.remove('d-none');
    unitEl.disabled = true;
    const placeholder = isPropertyScope ? 'Property-wide inspection' : 'Loading units…';
    unitEl.innerHTML = '<option value="">' + placeholder + '</option>';
    if (hintEl) {
      hintEl.textContent = 'Loading units…';
      hintEl.classList.remove('text-danger');
    }

    fetchUnits(unitsUrl)
      .catch(function() { return fetchUnits(fallbackUnitsUrl); })
      .then(function(d) {
        const list = Array.isArray(d) ? d : (d.units || []);
        unitEl.innerHTML = '';
        if (isPropertyScope) {
          const allOpt = document.createElement('option');
          allOpt.value = '';
          allOpt.textContent = 'Property-wide inspection';
          unitEl.appendChild(allOpt);
        }
        if (!list.length && isUnitScope) {
          unitEl.innerHTML = '<option value="">No units found</option>';
          if (hintEl) hintEl.textContent = 'This property has no units yet.';
          unitsReady = false;
          return;
        }

        let selected = false;
        if (list.length > 1 && isUnitScope) {
          const ph = document.createElement('option');
          ph.value = '';
          ph.textContent = '— Select unit —';
          unitEl.appendChild(ph);
        }

        list.forEach(function(u) {
          const o = document.createElement('option');
          o.value = u.id;
          o.textContent = (u.unit_number || u.name || 'Unit') + (u.status ? ' (' + u.status + ')' : '');
          if (preselectUnit && String(u.id) === String(preselectUnit)) {
            o.selected = true;
            selected = true;
          }
          unitEl.appendChild(o);
        });

        if (!selected && isUnitScope && list.length === 1) {
          unitEl.options[unitEl.options.length - 1].selected = true;
          selected = true;
        }

        if (hintEl) {
          hintEl.textContent = isPropertyScope
            ? list.length + ' unit(s). Leave blank for property-wide inspection.'
            : list.length + ' unit(s) available.' + (selected ? '' : ' Select a unit to continue.');
        }
        unitsReady = true;
      })
      .catch(function() {
        unitEl.innerHTML = '<option value="">Failed to load units</option>';
        if (hintEl) {
          hintEl.textContent = 'Could not load units. Try again or pick another property.';
          hintEl.classList.add('text-danger');
        }
        unitsReady = false;
      })
      .finally(function() {
        loadingEl.classList.add('d-none');
        unitEl.disabled = false;
        setSubmitState();
      });
  }

  if (unitEl) {
    unitEl.addEventListener('change', setSubmitState);
  }

  if (floorEl && floorCustom) {
    floorEl.addEventListener('change', function() {
      if (floorEl.value === 'custom') {
        floorCustom.classList.remove('d-none');
        floorCustom.name = 'floor_label';
        floorEl.removeAttribute('name');
      } else {
        floorCustom.classList.add('d-none');
        floorCustom.removeAttribute('name');
        floorEl.name = 'floor_label';
      }
    });
  }

  form.addEventListener('submit', function(e) {
    if (isUnitScope && (!unitsReady || !unitEl.value || unitEl.value === '0')) {
      e.preventDefault();
      if (hintEl) {
        hintEl.textContent = 'Please wait for units to load and select a unit before submitting.';
        hintEl.classList.add('text-danger');
      }
    }
  });

  propertyEl.addEventListener('change', function() {
    const opt = propertyEl.options[propertyEl.selectedIndex];
    const floors = parseInt(opt.dataset.floors || '1', 10);
    rebuildFloorOptions(floors);
    loadUnits();
  });

  if (unitEl) loadUnits();
})();
</script>
<?= $this->endSection() ?>
