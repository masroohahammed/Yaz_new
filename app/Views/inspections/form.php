<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-plus-circle me-2 text-primary"></i>New Inspection</h1>
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

    <div class="fm-card mb-3">
      <div class="card-header-fm"><h5 class="mb-0"><i class="bi bi-building me-2"></i>Location</h5></div>
      <div class="fm-card-body">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold small">Property <span class="text-danger">*</span></label>
            <select name="property_id" id="propertyId" class="form-select" required>
              <?php foreach ($facilities as $f): ?>
              <?php $selProp = (int) ($preselectProperty ?? 0); ?>
              <option value="<?= $f['id'] ?>" <?= $selProp === (int) $f['id'] ? 'selected' : '' ?>><?= esc($f['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
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
        </div>
      </div>
    </div>

    <div class="fm-card mb-3">
      <div class="card-header-fm"><h5 class="mb-0"><i class="bi bi-tag me-2"></i>Inspection Type</h5></div>
      <div class="fm-card-body">
        <div class="inspection-type-grid" id="typeGrid">
          <?php foreach ([
            ['move_in', 'box-arrow-in-right', 'Move-In', 'Tenant arrival checklist', 'success'],
            ['move_out', 'box-arrow-right', 'Move-Out', 'Departure & handback', 'warning'],
            ['routine', 'clipboard2-check', 'Routine', 'Periodic unit check', 'primary'],
          ] as [$val, $icon, $label, $desc, $color]): ?>
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
      <button type="submit" id="submitBtn" class="btn btn-fm-primary" disabled><i class="bi bi-arrow-right-circle me-1"></i>Create &amp; Open Checklist</button>
      <a href="<?= base_url('pm-inspections') ?>" class="btn btn-fm-outline">Cancel</a>
    </div>
    <?= form_close() ?>
  </div>

  <div class="col-lg-4">
    <div class="fm-card">
      <div class="card-header-fm"><h5 class="mb-0"><i class="bi bi-lightbulb me-2"></i>Tips</h5></div>
      <div class="fm-card-body small text-muted">
        <ul class="mb-0 ps-3">
          <li class="mb-2"><strong>Move-In</strong> — document unit condition before tenant occupancy.</li>
          <li class="mb-2"><strong>Move-Out</strong> — compare against move-in and note damage.</li>
          <li><strong>Routine</strong> — scheduled maintenance and compliance checks.</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  const form = document.querySelector('form');
  const propertyEl = document.getElementById('propertyId');
  const unitEl = document.getElementById('unitId');
  const loadingEl = document.getElementById('unitLoading');
  const hintEl = document.getElementById('unitHint');
  const submitBtn = document.getElementById('submitBtn');
  const typeCards = document.querySelectorAll('.inspection-type-card');
  const preselectUnit = <?= (int) ($preselectUnit ?? 0) ?>;
  const unitsUrl = '<?= base_url('helpdesk/ajax/units/') ?>';
  const fallbackUnitsUrl = '<?= base_url('contracts/ajax/units/') ?>';
  let unitsReady = false;

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

  function setSubmitState() {
    const hasUnit = unitEl.value && unitEl.value !== '0';
    submitBtn.disabled = !unitsReady || !hasUnit;
  }

  function fetchUnits(url) {
    return fetch(url + propertyEl.value, { headers: { Accept: 'application/json' } })
      .then(function(r) { return r.json(); });
  }

  function loadUnits() {
    const pid = propertyEl.value;
    unitsReady = false;
    setSubmitState();
    loadingEl.classList.remove('d-none');
    unitEl.disabled = true;
    unitEl.innerHTML = '<option value="">Loading units…</option>';
    hintEl.textContent = 'Loading units…';
    hintEl.classList.remove('text-danger');

    fetchUnits(unitsUrl)
      .catch(function() { return fetchUnits(fallbackUnitsUrl); })
      .then(function(d) {
        const list = Array.isArray(d) ? d : (d.units || []);
        unitEl.innerHTML = '';
        if (!list.length) {
          unitEl.innerHTML = '<option value="">No units found</option>';
          hintEl.textContent = 'This property has no units yet.';
          unitsReady = false;
          return;
        }

        let selected = false;
        if (list.length > 1) {
          const placeholder = document.createElement('option');
          placeholder.value = '';
          placeholder.textContent = '— Select unit —';
          unitEl.appendChild(placeholder);
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

        if (!selected && list.length === 1) {
          unitEl.options[unitEl.options.length - 1].selected = true;
          selected = true;
        }

        hintEl.textContent = list.length + ' unit(s) available.' + (selected ? '' : ' Select a unit to continue.');
        unitsReady = true;
      })
      .catch(function() {
        unitEl.innerHTML = '<option value="">Failed to load units</option>';
        hintEl.textContent = 'Could not load units. Try again or pick another property.';
        hintEl.classList.add('text-danger');
        unitsReady = false;
      })
      .finally(function() {
        loadingEl.classList.add('d-none');
        unitEl.disabled = false;
        setSubmitState();
      });
  }

  unitEl.addEventListener('change', setSubmitState);

  form.addEventListener('submit', function(e) {
    if (!unitsReady || !unitEl.value || unitEl.value === '0') {
      e.preventDefault();
      hintEl.textContent = 'Please wait for units to load and select a unit before submitting.';
      hintEl.classList.add('text-danger');
    }
  });

  propertyEl.addEventListener('change', function() {
    loadUnits();
  });
  loadUnits();
})();
</script>
<?= $this->endSection() ?>
