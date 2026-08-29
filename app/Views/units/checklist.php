<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$primaryColor = $settings['primary_color'] ?? '#76002b';
$typeLabel    = ['move_in'=>'Move-In','move_out'=>'Move-Out','routine'=>'Routine Inspection','handover'=>'Handover'][$type] ?? ucfirst(str_replace('_',' ',$type));

$defaultItems = [
    'move_in' => [
        ['id'=>'elec',  'label'=>'Electricity — meter reading recorded, supply working'],
        ['id'=>'water', 'label'=>'Water — supply working, no leaks'],
        ['id'=>'ac',    'label'=>'Air conditioning / HVAC functioning'],
        ['id'=>'doors', 'label'=>'Doors and windows — open/close properly, locks working'],
        ['id'=>'walls', 'label'=>'Walls, ceilings and floors — clean, no damage'],
        ['id'=>'kitchen','label'=>'Kitchen fittings and appliances (if applicable)'],
        ['id'=>'bath',  'label'=>'Bathrooms — sanitary fittings clean and working'],
        ['id'=>'keys',  'label'=>'Keys handed over (main door, mailbox, parking)'],
        ['id'=>'assets','label'=>'Furniture / assets inventory confirmed'],
        ['id'=>'fire',  'label'=>'Fire safety equipment in place'],
        ['id'=>'clean', 'label'=>'Unit is clean and ready for occupation'],
    ],
    'move_out' => [
        ['id'=>'damage','label'=>'Inspect for damage beyond normal wear and tear'],
        ['id'=>'elec',  'label'=>'Electricity — meter reading recorded, final reading noted'],
        ['id'=>'water', 'label'=>'Water — meter reading, no pending bills'],
        ['id'=>'keys',  'label'=>'All keys returned (main door, mailbox, parking, intercom)'],
        ['id'=>'clean', 'label'=>'Unit cleaned and vacated properly'],
        ['id'=>'assets','label'=>'Furniture / assets checked against inventory'],
        ['id'=>'ac',    'label'=>'A/C filters cleaned, units working'],
        ['id'=>'maint', 'label'=>'Any maintenance issues identified and documented'],
        ['id'=>'bills', 'label'=>'All utility bills cleared'],
        ['id'=>'deposit','label'=>'Security deposit refund amount agreed'],
    ],
    'routine' => [
        ['id'=>'gen',   'label'=>'General cleanliness and housekeeping'],
        ['id'=>'elec',  'label'=>'Electrical systems and light fixtures'],
        ['id'=>'plumb', 'label'=>'Plumbing and water fittings'],
        ['id'=>'ac',    'label'=>'Air conditioning / HVAC'],
        ['id'=>'doors', 'label'=>'Doors, windows and locks'],
        ['id'=>'fire',  'label'=>'Fire safety equipment (extinguishers, alarms)'],
        ['id'=>'pest',  'label'=>'Pest control status'],
        ['id'=>'common','label'=>'Common area access (corridors, elevators)'],
    ],
    'handover' => [
        ['id'=>'docs',  'label'=>'All documents and keys handed over'],
        ['id'=>'elec',  'label'=>'Electricity supply confirmed'],
        ['id'=>'water', 'label'=>'Water supply confirmed'],
        ['id'=>'clean', 'label'=>'Unit clean and ready'],
        ['id'=>'assets','label'=>'Asset inventory confirmed'],
    ],
];
$items = $defaultItems[$type] ?? $defaultItems['routine'];
?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-clipboard2-check me-2"></i><?= $typeLabel ?> — Unit <?= esc($unit['unit_number']) ?></h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= base_url('facilities/'.$unit['facility_id'].'/units') ?>">Units</a></li>
      <li class="breadcrumb-item"><a href="<?= base_url('units/view/'.$unit['id']) ?>">Unit <?= esc($unit['unit_number']) ?></a></li>
      <li class="breadcrumb-item active"><?= $typeLabel ?></li>
    </ol></nav>
  </div>
  <button class="btn btn-fm-outline btn-sm no-print" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print</button>
</div>

<?= form_open_multipart(base_url('units/checklist/store')) ?>
<?= form_hidden('unit_id', $unit['id']) ?>
<?= form_hidden('type', $type) ?>

<!-- Info strip -->
<div class="fm-form-section mb-3">
  <div class="row g-3 text-center">
    <div class="col-6 col-md-3"><div class="x-small text-muted">UNIT</div><div class="fw-bold"><?= esc($unit['unit_number']) ?></div></div>
    <div class="col-6 col-md-3"><div class="x-small text-muted">FACILITY</div><div class="fw-bold"><?= esc($unit['facility_name']) ?></div></div>
    <div class="col-6 col-md-3"><div class="x-small text-muted">TENANT</div><div class="fw-bold"><?= esc($unit['tenant_name'] ?: '—') ?></div></div>
    <div class="col-6 col-md-3"><div class="x-small text-muted">DATE</div><div class="fw-bold"><?= date('d M Y') ?></div></div>
  </div>
</div>

<!-- Checklist Items -->
<div class="fm-card mb-3">
  <div class="card-header-fm"><h5><i class="bi bi-list-check me-2"></i><?= $typeLabel ?> Items</h5></div>
  <div class="fm-card-body">
    <?php foreach($items as $i => $item): ?>
    <div class="d-flex align-items-start gap-3 py-2 border-bottom border-light">
      <div class="form-check mt-1">
        <input class="form-check-input" type="checkbox" name="items[<?= $item['id'] ?>]" value="1"
          id="item_<?= $item['id'] ?>" style="width:20px;height:20px;border-color:<?= $primaryColor ?>">
      </div>
      <div class="flex-grow-1">
        <label class="form-check-label fw-semibold small" for="item_<?= $item['id'] ?>"><?= esc($item['label']) ?></label>
        <div class="mt-1">
          <input type="text" name="item_notes[<?= $item['id'] ?>]" class="form-control form-control-sm"
            placeholder="Notes / observations (optional)">
        </div>
      </div>
      <select name="item_status[<?= $item['id'] ?>]" class="form-select form-select-sm" style="width:100px">
        <option value="ok">✓ OK</option>
        <option value="issue">⚠ Issue</option>
        <option value="na">— N/A</option>
      </select>
    </div>
    <?php endforeach; ?>

    <!-- Photo Attachments -->
    <div class="mt-3">
      <label class="form-label fw-semibold small"><i class="bi bi-camera me-1"></i>Photo Attachments</label>
      <input type="file" name="photos[]" class="form-control form-control-sm" multiple accept="image/*">
      <div class="form-text">Upload before/after photos (optional). Multiple files allowed.</div>
    </div>
  </div>
</div>

<!-- General Notes -->
<div class="fm-card mb-3">
  <div class="card-header-fm"><h5><i class="bi bi-card-text me-2"></i>General Remarks</h5></div>
  <div class="fm-card-body">
    <textarea name="general_notes" class="form-control" rows="3" placeholder="Any additional observations, issues found, or actions required..."></textarea>
  </div>
</div>

<!-- Signatures -->
<div class="fm-card mb-3">
  <div class="card-header-fm"><h5><i class="bi bi-pen me-2"></i>Acknowledgement</h5></div>
  <div class="fm-card-body">
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Inspector / FM Officer</label>
        <input type="text" name="inspector_name" class="form-control form-control-sm" value="<?= esc($currentUser['name'] ?? '') ?>">
        <div style="border-bottom:1px dashed #999;height:50px;margin-top:8px"></div>
        <div class="x-small text-muted text-center mt-1">Signature &amp; Date</div>
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Tenant / Resident</label>
        <input type="text" name="tenant_sig_name" class="form-control form-control-sm" value="<?= esc($unit['tenant_name']??'') ?>">
        <div style="border-bottom:1px dashed #999;height:50px;margin-top:8px"></div>
        <div class="x-small text-muted text-center mt-1">Signature &amp; Date</div>
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Facility Manager</label>
        <input type="text" name="fm_sig_name" class="form-control form-control-sm" placeholder="Manager name">
        <div style="border-bottom:1px dashed #999;height:50px;margin-top:8px"></div>
        <div class="x-small text-muted text-center mt-1">Signature &amp; Date</div>
      </div>
    </div>
  </div>
</div>

<!-- Actions -->
<div class="d-flex gap-2 no-print">
  <button type="submit" name="submit_action" value="draft" class="btn btn-fm-outline">
    <i class="bi bi-save me-2"></i>Save as Draft
  </button>
  <button type="submit" name="submit_action" value="complete" class="btn btn-fm-primary">
    <i class="bi bi-check-circle me-2"></i>Complete &amp; Save
  </button>
  <a href="<?= base_url('units/view/'.$unit['id']) ?>" class="btn btn-outline-secondary">Cancel</a>
</div>

<?= form_close() ?>

<style>
@media print {
  .no-print,.topbar,.sidebar,.page-header .d-flex,.btn{display:none!important}
  .main-wrapper{margin-left:0!important}
  body{background:#fff!important}
  * {-webkit-print-color-adjust:exact;print-color-adjust:exact}
}
</style>

<?= $this->endSection() ?>
