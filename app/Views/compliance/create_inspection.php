<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-clipboard2-check me-2 text-success"></i>New Inspection Checklist</h1></div>
  <a href="<?= base_url('compliance/inspections') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<form action="<?= base_url('compliance/inspections/store') ?>" method="post">
<?= csrf_field() ?>
<div class="row g-3">
  <div class="col-lg-8">
    <div class="fm-card mb-3">
      <div class="card-header-fm"><h5><i class="bi bi-info-circle me-2"></i>Inspection Details</h5></div>
      <div class="fm-card-body">
        <div class="row g-3">
          <div class="col-md-12">
            <label class="form-label">Inspection Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" placeholder="e.g. Monthly Fire Safety Inspection" value="<?= old('title') ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Facility <span class="text-danger">*</span></label>
            <select name="facility_id" class="form-select" required>
              <option value="">Select Facility</option>
              <?php foreach($facilities as $f): ?>
              <option value="<?= $f['id'] ?>" <?= old('facility_id')==$f['id']?'selected':'' ?>><?= esc($f['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Inspection Type <span class="text-danger">*</span></label>
            <select name="type" class="form-select" required>
              <option value="">Select Type</option>
              <?php foreach(['Fire Safety','Electrical','Plumbing','HVAC','Structural','Health & Hygiene','Security','General Facility','PPE & Safety Equipment','Elevator','Emergency Systems'] as $t): ?>
              <option value="<?= $t ?>" <?= old('type')===$t?'selected':'' ?>><?= $t ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Inspection Date <span class="text-danger">*</span></label>
            <input type="date" name="inspection_date" class="form-control" value="<?= old('inspection_date', date('Y-m-d')) ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Inspector Name</label>
            <input type="text" name="inspector_name" class="form-control" placeholder="Inspector's name" value="<?= old('inspector_name') ?>">
          </div>
          <div class="col-12">
            <label class="form-label">General Notes</label>
            <textarea name="notes" class="form-control" rows="2" placeholder="Any pre-inspection notes..."><?= old('notes') ?></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- Checklist Items -->
    <div class="fm-card">
      <div class="card-header-fm">
        <h5><i class="bi bi-list-check me-2"></i>Checklist Items</h5>
        <button type="button" class="btn btn-fm-outline btn-sm" onclick="addItem()"><i class="bi bi-plus-lg me-1"></i>Add Item</button>
      </div>
      <div class="fm-card-body">
        <div id="items-container">
          <!-- Items will be added here -->
        </div>
        <div class="text-center py-3" id="empty-msg">
          <span class="text-muted small">No items yet. Click "Add Item" or use a template.</span>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="fm-card mb-3">
      <div class="card-header-fm"><h5><i class="bi bi-lightning me-2"></i>Quick Templates</h5></div>
      <div class="fm-card-body">
        <p class="small text-muted mb-2">Load a pre-defined checklist template:</p>
        <div class="d-flex flex-column gap-2">
          <?php
          $templates = [
            'Fire Safety'    => ['Fire extinguishers accessible & charged','Smoke detectors functional','Emergency exit signs illuminated','Fire doors not blocked','Sprinkler heads clear of obstructions','Evacuation plan posted','Fire alarm pull stations accessible','Hose reels in working order'],
            'Electrical'     => ['All electrical panels accessible','No exposed wiring visible','GFCI outlets tested & functional','Circuit breakers labeled','Emergency lighting operational','Extension cords used properly','Electrical rooms locked','No overloaded outlets'],
            'HVAC'           => ['Air filters replaced/clean','Thermostats functional','Vents unobstructed','No unusual odors or noises','Belts and motors inspected','Condensate drains clear','Refrigerant levels checked','Controls and sensors operational'],
            'Health & Hygiene'=> ['Restrooms clean and stocked','Hand sanitiser stations filled','Waste bins emptied regularly','Kitchen/pantry areas hygienic','No pest evidence','Drinking water accessible','Cleaning schedule followed','PPE available for cleaning staff'],
          ];
          foreach($templates as $name => $items): ?>
          <button type="button" class="btn btn-fm-outline btn-sm text-start" onclick='loadTemplate(<?= json_encode($items) ?>)'><i class="bi bi-download me-1"></i><?= $name ?></button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <div class="fm-card">
      <div class="fm-card-body">
        <button type="submit" class="btn btn-fm-primary w-100"><i class="bi bi-check-lg me-1"></i>Create Inspection</button>
        <a href="<?= base_url('compliance/inspections') ?>" class="btn btn-fm-outline w-100 mt-2">Cancel</a>
      </div>
    </div>
  </div>
</div>
</form>

<?= $this->section('scripts') ?>
<script>
let itemCount = 0;
function addItem(text='') {
  itemCount++;
  document.getElementById('empty-msg').style.display = 'none';
  const container = document.getElementById('items-container');
  const div = document.createElement('div');
  div.className = 'd-flex align-items-center gap-2 mb-2';
  div.id = 'item-row-' + itemCount;
  div.innerHTML = `
    <span class="text-muted small" style="width:22px;text-align:right">${container.children.length + 1}.</span>
    <input type="text" name="items[]" class="form-control form-control-sm" placeholder="Inspection item..." value="${text}" required>
    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem('item-row-${itemCount}')"><i class="bi bi-trash"></i></button>
  `;
  container.appendChild(div);
}
function removeItem(id) {
  document.getElementById(id).remove();
  if (!document.getElementById('items-container').children.length) {
    document.getElementById('empty-msg').style.display = '';
  }
  // Re-number
  document.querySelectorAll('#items-container .d-flex').forEach((row, i) => {
    row.querySelector('span').textContent = (i + 1) + '.';
  });
}
function loadTemplate(items) {
  document.getElementById('items-container').innerHTML = '';
  itemCount = 0;
  document.getElementById('empty-msg').style.display = 'none';
  items.forEach(t => addItem(t));
}
</script>
<?= $this->endSection() ?>
<?= $this->endSection() ?>
