<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1>New Inspection</h1></div>
<?= form_open(base_url('pm-inspections/store')) ?>
<?= csrf_field() ?>
<div class="row g-2">
  <div class="col-md-4"><label class="form-label small">Property *</label>
    <select name="property_id" id="propertyId" class="form-select form-select-sm" required>
      <?php foreach ($facilities as $f): ?><option value="<?= $f['id'] ?>"><?= esc($f['name']) ?></option><?php endforeach; ?>
    </select></div>
  <div class="col-md-4"><label class="form-label small">Unit *</label>
    <select name="unit_id" id="unitId" class="form-select form-select-sm" required></select></div>
  <div class="col-md-4"><label class="form-label small">Type *</label>
    <select name="inspection_type" class="form-select form-select-sm">
      <option value="move_in">Move-in</option><option value="move_out">Move-out</option><option value="periodic">Periodic</option>
    </select></div>
  <div class="col-md-4"><label class="form-label small">Date</label><input type="date" name="inspection_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>"></div>
  <div class="col-md-4"><label class="form-label small">Inspector</label><input type="text" name="inspector" class="form-control form-control-sm"></div>
  <div class="col-12"><button type="submit" class="btn btn-fm-primary btn-sm">Create & Open Checklist</button></div>
</div>
<?= form_close() ?>
<script>
document.getElementById('propertyId').addEventListener('change', loadUnits);
function loadUnits() {
  const pid = document.getElementById('propertyId').value;
  fetch('<?= base_url('helpdesk/ajax/units/') ?>' + pid).then(r=>r.json()).then(d=>{
    const sel = document.getElementById('unitId');
    sel.innerHTML = '';
    (Array.isArray(d) ? d : (d.units || [])).forEach(u => {
      const o = document.createElement('option');
      o.value = u.id; o.textContent = u.unit_number || u.name;
      sel.appendChild(o);
    });
  }).catch(()=>{});
}
loadUnits();
</script>
<?= $this->endSection() ?>
