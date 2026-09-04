<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$isEdit = !empty($contract['id']);
$preUnit = $preUnit ?? null;
?>
<div class="page-header"><div><h1><?= esc($title ?? 'Contract') ?></h1></div><a href="<?= base_url('contracts') ?>" class="btn btn-fm-outline btn-sm">Back</a></div>
<div class="form-card">
<form method="post" action="<?= $isEdit ? base_url('contracts/'.$contract['id'].'/update') : base_url('contracts') ?>" id="leaseContractForm">
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-md-4"><label class="form-label">Tenant</label><select name="tenant_id" id="tenant_id" class="form-select" required><option value="">Select…</option><?php foreach ($tenants as $t): ?><?php $tenantQid = trim((string)($t['qid_no'] ?? $t['passport_no'] ?? '')); ?><option value="<?= $t['id'] ?>" data-qid="<?= esc($tenantQid) ?>" <?= old('tenant_id',$contract['tenant_id']??'')==$t['id']?'selected':'' ?>><?= esc($t['full_name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-4"><label class="form-label">Property</label><select name="facility_id" id="facility_id" class="form-select" required><?php foreach ($facilities as $f): ?><option value="<?= $f['id'] ?>" <?= old('facility_id',$contract['facility_id']??($preUnit['facility_id']??''))==$f['id']?'selected':'' ?>><?= esc($f['name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-4"><label class="form-label">Unit</label><select name="unit_id" id="unit_id" class="form-select" required><?php foreach ($units as $u): ?><option value="<?= $u['id'] ?>" data-type="<?= esc($u['unit_type'] ?? '') ?>" data-plate="<?= esc($u['plate_number'] ?? '') ?>" <?= old('unit_id',$contract['unit_id']??($preUnit['id']??''))==$u['id']?'selected':'' ?>><?= esc($u['unit_number']) ?><?= strtolower((string)($u['unit_type']??''))==='parking' ? ' (Parking)' : '' ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><label class="form-label">Tenant QID / Passport</label><input type="text" name="tenant_qid" id="tenant_qid" class="form-control" value="<?= esc(old('tenant_qid', $contract['tenant_qid'] ?? ($contract['qid_no'] ?? $contract['passport_no'] ?? ''))) ?>" placeholder="Qatar ID or passport"></div>
    <div class="col-md-3"><label class="form-label">Start</label><input type="date" name="start_date" class="form-control" required value="<?= esc(old('start_date',$contract['start_date']??'')) ?>"></div>
    <div class="col-md-3"><label class="form-label">End</label><input type="date" name="end_date" class="form-control" required value="<?= esc(old('end_date',$contract['end_date']??'')) ?>"></div>
    <div class="col-md-3"><label class="form-label">Rent</label><input type="number" step="0.01" name="rent_amount" class="form-control" required value="<?= esc(old('rent_amount',$contract['rent_amount']??'')) ?>"></div>
    <div class="col-md-3"><label class="form-label">Status</label><select name="status" class="form-select"><?php foreach (['draft','active','expired','terminated','renewed'] as $s): ?><option value="<?= $s ?>" <?= old('status',$contract['status']??'draft')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><label class="form-label">Signed date</label><input type="date" name="signed_date" class="form-control" value="<?= esc(old('signed_date',$contract['signed_date']??'')) ?>"></div>
    <div class="col-md-3"><label class="form-label">Security deposit</label><input type="number" step="0.01" name="security_deposit" class="form-control" value="<?= esc(old('security_deposit',$contract['security_deposit']??'')) ?>"></div>
    <div class="col-md-3"><label class="form-label">Payment frequency</label><select name="payment_frequency" class="form-select"><?php foreach (['monthly','quarterly','yearly'] as $pf): ?><option value="<?= $pf ?>" <?= old('payment_frequency',$contract['payment_frequency']??'monthly')===$pf?'selected':'' ?>><?= ucfirst($pf) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><label class="form-label">Payment method</label><select name="payment_type" class="form-select"><?php foreach (['cash','cheque','bank_transfer','card'] as $pt): ?><option value="<?= $pt ?>" <?= old('payment_type',$contract['payment_type']??'cash')===$pt?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$pt)) ?></option><?php endforeach; ?></select></div>
  </div>

  <div id="parkingFields" class="mt-4 pt-3 border-top" style="display:none">
    <h6 class="text-muted text-uppercase small mb-3"><i class="bi bi-car-front me-1"></i>Parking contract details</h6>
    <div class="row g-3">
      <div class="col-md-3"><label class="form-label">Plate number</label><input type="text" name="plate_number" id="plate_number" class="form-control" value="<?= esc(old('plate_number',$contract['plate_number']??($preUnit['plate_number']??''))) ?>"></div>
      <div class="col-md-3"><label class="form-label">Vehicle type</label><input type="text" name="vehicle_type" class="form-control" value="<?= esc(old('vehicle_type',$contract['vehicle_type']??'Motorcycle')) ?>" placeholder="Car, Motorcycle…"></div>
      <div class="col-md-3"><label class="form-label">Vehicle description</label><input type="text" name="vehicle_description" class="form-control" value="<?= esc(old('vehicle_description',$contract['vehicle_description']??'')) ?>"></div>
      <div class="col-md-3"><label class="form-label">Title deed no.</label><input type="text" name="title_deed_no" class="form-control" value="<?= esc(old('title_deed_no',$contract['title_deed_no']??'')) ?>"></div>
      <div class="col-md-3"><label class="form-label">Building no.</label><input type="text" name="building_no" class="form-control" value="<?= esc(old('building_no',$contract['building_no']??'')) ?>"></div>
      <div class="col-md-3"><label class="form-label">Zone no.</label><input type="text" name="zone_no" class="form-control" value="<?= esc(old('zone_no',$contract['zone_no']??'')) ?>"></div>
      <div class="col-md-3"><label class="form-label">Street no.</label><input type="text" name="street_no" class="form-control" value="<?= esc(old('street_no',$contract['street_no']??'')) ?>"></div>
    </div>
    <p class="small text-muted mt-2">After saving, use <strong>Print Parking Agreement</strong> on the contract page for the bilingual PDF.</p>
  </div>

  <div class="row g-3 mt-2">
    <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"><?= esc(old('notes',$contract['notes']??'')) ?></textarea></div>
  </div>
  <div class="mt-3"><button class="btn btn-fm-primary"><?= $isEdit ? 'Update' : 'Create' ?> Contract</button></div>
</form></div>

<?php if ($isEdit && ! empty($contract['id'])): ?>
<?= view('partials/_lease_signature_panel', [
    'lease' => $contract,
    'signLink' => session()->getFlashdata('sign_link'),
    'signatureReady' => $signatureReady ?? null,
]) ?>
<?php endif; ?>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
(function () {
  const facilitySel = document.getElementById('facility_id');
  const unitSel = document.getElementById('unit_id');
  const tenantSel = document.getElementById('tenant_id');
  const tenantQid = document.getElementById('tenant_qid');
  const parkingBox = document.getElementById('parkingFields');
  const plateInput = document.getElementById('plate_number');

  function fillTenantQidFromSelect() {
    const opt = tenantSel?.selectedOptions[0];
    if (!opt || !tenantQid) return;
    const qid = opt.getAttribute('data-qid') || '';
    if (qid) tenantQid.value = qid;
  }

  tenantSel?.addEventListener('change', fillTenantQidFromSelect);

  function isParkingOption(opt) {
    return opt && String(opt.getAttribute('data-type') || '').toLowerCase() === 'parking';
  }

  function toggleParking() {
    const opt = unitSel?.selectedOptions[0];
    const show = isParkingOption(opt);
    if (parkingBox) parkingBox.style.display = show ? '' : 'none';
    if (show && plateInput && !plateInput.value && opt) {
      plateInput.value = opt.getAttribute('data-plate') || '';
    }
  }

  function loadUnits(facilityId, selectedId) {
    if (!facilityId) return;
    fetch('<?= base_url('contracts/ajax/units/') ?>' + facilityId, { headers: { 'Accept': 'application/json' } })
      .then(r => r.json())
      .then(rows => {
        unitSel.innerHTML = '';
        rows.forEach(u => {
          const o = document.createElement('option');
          o.value = u.id;
          o.textContent = u.unit_number + (String(u.unit_type).toLowerCase() === 'parking' ? ' (Parking)' : '');
          o.setAttribute('data-type', u.unit_type || '');
          o.setAttribute('data-plate', u.plate_number || '');
          if (String(selectedId) === String(u.id)) o.selected = true;
          unitSel.appendChild(o);
        });
        toggleParking();
      });
  }

  facilitySel?.addEventListener('change', function () {
    loadUnits(this.value, '');
  });
  unitSel?.addEventListener('change', toggleParking);

  if (facilitySel?.value && unitSel?.options.length <= 1) {
    loadUnits(facilitySel.value, '<?= (int) old('unit_id', $contract['unit_id'] ?? ($preUnit['id'] ?? 0)) ?>');
  } else {
    toggleParking();
  }
})();
</script>
<?= $this->endSection() ?>
