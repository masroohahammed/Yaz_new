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
              <option value="<?= $f['id'] ?>" <?= (int) ($preselectProperty ?? 0) === (int) $f['id'] ? 'selected' : '' ?>><?= esc($f['name']) ?></option>
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
      <button type="submit" class="btn btn-fm-primary"><i class="bi bi-arrow-right-circle me-1"></i>Create &amp; Open Checklist</button>
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
  const propertyEl = document.getElementById('propertyId');
  const unitEl = document.getElementById('unitId');
  const loadingEl = document.getElementById('unitLoading');
  const hintEl = document.getElementById('unitHint');
  const typeCards = document.querySelectorAll('.inspection-type-card');
  const preselectUnit = <?= (int) ($preselectUnit ?? 0) ?>;

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

  function loadUnits() {
    const pid = propertyEl.value;
    loadingEl.classList.remove('d-none');
    unitEl.disabled = true;
    hintEl.textContent = 'Loading units…';

    fetch('<?= base_url('helpdesk/ajax/units/') ?>' + pid)
      .then(function(r) { return r.json(); })
      .then(function(d) {
        const list = Array.isArray(d) ? d : (d.units || []);
        unitEl.innerHTML = '';
        if (!list.length) {
          unitEl.innerHTML = '<option value="">No units found</option>';
          hintEl.textContent = 'This property has no units yet.';
        } else {
          list.forEach(function(u) {
            const o = document.createElement('option');
            o.value = u.id;
            o.textContent = (u.unit_number || u.name || 'Unit') + (u.status ? ' (' + u.status + ')' : '');
            if (preselectUnit && String(u.id) === String(preselectUnit)) o.selected = true;
            unitEl.appendChild(o);
          });
          hintEl.textContent = list.length + ' unit(s) available.';
        }
      })
      .catch(function() {
        unitEl.innerHTML = '<option value="">Failed to load units</option>';
        hintEl.textContent = 'Could not load units. Try again.';
      })
      .finally(function() {
        loadingEl.classList.add('d-none');
        unitEl.disabled = false;
      });
  }

  propertyEl.addEventListener('change', loadUnits);
  loadUnits();
})();
</script>
<?= $this->endSection() ?>
