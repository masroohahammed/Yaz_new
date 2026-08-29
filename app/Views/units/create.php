<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $isEdit = isset($unit) && !empty($unit['id']); $u = $unit ?? []; ?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-grid-plus me-2"></i><?= $isEdit ? 'Edit Unit '.esc($u['unit_number']) : 'Add Unit' ?></h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= base_url('facilities') ?>">Facilities</a></li>
      <?php if($isEdit): ?>
      <li class="breadcrumb-item"><a href="<?= base_url('facilities/'.$u['facility_id'].'/units') ?>"><?= esc($u['facility_name']??'Units') ?></a></li>
      <li class="breadcrumb-item active">Edit <?= esc($u['unit_number']) ?></li>
      <?php else: ?>
      <li class="breadcrumb-item"><a href="<?= base_url('facilities/'.$facility['id'].'/units') ?>"><?= esc($facility['name']) ?></a></li>
      <li class="breadcrumb-item active">Add Unit</li>
      <?php endif; ?>
    </ol></nav>
  </div>
</div>

<?php if(session()->getFlashdata('errors')): ?>
<div class="alert alert-danger"><?php foreach(session()->getFlashdata('errors') as $e): ?><div><?= esc($e) ?></div><?php endforeach; ?></div>
<?php endif; ?>

<?php
$formUrl = $isEdit ? base_url('units/update/'.$u['id']) : base_url('facilities/'.$facility['id'].'/units/store');
?>
<?= form_open_multipart($formUrl) ?>

<div class="row g-3">
  <!-- LEFT -->
  <div class="col-lg-6">

    <!-- Unit Details -->
    <div class="fm-form-section">
      <h6><i class="bi bi-grid"></i>Unit Details</h6>
      <div class="row g-2">
        <div class="col-6">
          <label class="form-label">Unit Number <span class="text-danger">*</span></label>
          <input type="text" name="unit_number" class="form-control" value="<?= esc($u['unit_number']??old('unit_number')) ?>" required placeholder="e.g. A-101">
        </div>
        <div class="col-6">
          <label class="form-label">Floor</label>
          <input type="text" name="floor" class="form-control" value="<?= esc($u['floor']??old('floor')) ?>" placeholder="e.g. Ground, 1st">
        </div>
        <div class="col-6">
          <label class="form-label">Unit Type</label>
          <select name="unit_type" class="form-select" id="unitTypeSelect" onchange="togglePlateNumberField()">
            <?php foreach(['apartment'=>'Apartment','studio'=>'Studio','villa'=>'Villa','office'=>'Office','retail'=>'Retail','warehouse'=>'Warehouse','parking'=>'Parking','other'=>'Other'] as $v=>$l): ?>
            <option value="<?= $v ?>" <?= ($u['unit_type']??'')===$v?'selected':'' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-6" id="plateNumberWrap" style="display:none">
          <label class="form-label">Plate Number</label>
          <input type="text" name="plate_number" class="form-control" value="<?= esc($u['plate_number']??old('plate_number')) ?>" placeholder="e.g. ABC 1234">
        </div>
        <div class="col-6">
          <label class="form-label">Area (sqft)</label>
          <input type="number" name="area_sqft" class="form-control" value="<?= esc($u['area_sqft']??'') ?>" placeholder="0">
        </div>
        <div class="col-12">
          <label class="form-label">Status <span class="text-danger">*</span></label>
          <select name="status" class="form-select" id="statusSelect" onchange="toggleTenantSection()">
            <option value="vacant" <?= ($u['status']??'')==='vacant'?'selected':'' ?>>Vacant</option>
            <option value="occupied" <?= ($u['status']??'')==='occupied'?'selected':'' ?>>Occupied</option>
            <option value="maintenance" <?= ($u['status']??'')==='maintenance'?'selected':'' ?>>Maintenance</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Owner Info -->
    <div class="fm-form-section">
      <h6><i class="bi bi-person-badge"></i>Owner Information</h6>
      <div class="row g-2">
        <div class="col-12">
          <label class="form-label">Owner Name</label>
          <input type="text" name="owner_name" class="form-control" value="<?= esc($u['owner_name']??'') ?>">
        </div>
        <div class="col-6">
          <label class="form-label">Owner Mobile</label>
          <input type="text" name="owner_mobile" class="form-control" value="<?= esc($u['owner_mobile']??'') ?>">
        </div>
        <div class="col-6">
          <label class="form-label">Owner Email</label>
          <input type="email" name="owner_email" class="form-control" value="<?= esc($u['owner_email']??'') ?>">
        </div>
      </div>
    </div>

    <!-- Notes -->
    <div class="fm-form-section">
      <h6><i class="bi bi-card-text"></i>Notes</h6>
      <textarea name="notes" class="form-control" rows="3" placeholder="Any additional notes..."><?= esc($u['notes']??'') ?></textarea>
    </div>
  </div>

  <!-- RIGHT -->
  <div class="col-lg-6">

    <!-- Tenant Info -->
    <div class="fm-form-section" id="tenantSection">
      <h6><i class="bi bi-person-check"></i>Tenant Information</h6>
      <div class="row g-2">
        <div class="col-12">
          <label class="form-label">Tenant Name</label>
          <input type="text" name="tenant_name" class="form-control" value="<?= esc($u['tenant_name']??'') ?>" placeholder="Full name">
        </div>
        <div class="col-6">
          <label class="form-label">Tenant Mobile <span class="text-danger">*</span></label>
          <input type="text" name="tenant_mobile" class="form-control" value="<?= esc($u['tenant_mobile']??'') ?>" placeholder="+974 xxxx xxxx">
        </div>
        <div class="col-6">
          <label class="form-label">Tenant Email</label>
          <input type="email" name="tenant_email" class="form-control" value="<?= esc($u['tenant_email']??'') ?>">
        </div>
      </div>
    </div>

    <!-- Contract Details -->
    <div class="fm-form-section" id="contractSection">
      <h6><i class="bi bi-file-earmark-text"></i>Contract Details</h6>
      <div class="row g-2">
        <div class="col-12">
          <label class="form-label">Contract Number</label>
          <input type="text" name="contract_number" class="form-control" value="<?= esc($u['contract_number']??'') ?>" placeholder="e.g. CON-2026-001">
        </div>
        <div class="col-6">
          <label class="form-label">Contract Start <span class="text-danger">*</span></label>
          <input type="date" name="contract_start" class="form-control" value="<?= esc($u['contract_start']??'') ?>">
        </div>
        <div class="col-6">
          <label class="form-label">Contract End <span class="text-danger">*</span></label>
          <input type="date" name="contract_end" class="form-control" value="<?= esc($u['contract_end']??'') ?>" id="contractEnd" onchange="calcDuration()">
        </div>
        <div class="col-12" id="durationInfo" style="display:none">
          <div class="alert alert-info py-1 px-2 small" id="durationText"></div>
        </div>
        <div class="col-6">
          <label class="form-label">Monthly Rent (<?= $currency ?>)</label>
          <input type="number" name="rent_amount" class="form-control" step="0.01" value="<?= esc($u['rent_amount']??'') ?>" placeholder="0.00">
        </div>
        <div class="col-6">
          <label class="form-label">Security Deposit (<?= $currency ?>)</label>
          <input type="number" name="security_deposit" class="form-control" step="0.01" value="<?= esc($u['security_deposit']??'') ?>" placeholder="0.00">
        </div>
        <div class="col-12">
          <label class="form-label">Contract Attachment</label>
          <?php if(!empty($u['contract_attachment'])): ?>
          <div class="small text-success mb-1"><i class="bi bi-paperclip me-1"></i>Current: <a href="<?= base_url('file/contracts/'.basename($u['contract_attachment'])) ?>" target="_blank">View Contract</a></div>
          <?php endif; ?>
          <input type="file" name="contract_attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp">
          <div class="form-text">PDF or image. Max 10MB.</div>
        </div>
      </div>
    </div>

    <!-- Financial Summary -->
    <div class="fm-form-section" id="financialSummary" style="display:none">
      <h6><i class="bi bi-calculator"></i>Financial Summary</h6>
      <div id="summaryContent" class="small text-muted">Fill in contract dates and rent to see summary.</div>
    </div>
  </div>
</div>

<div class="d-flex gap-2 mt-3">
  <button type="submit" class="btn btn-fm-primary px-4"><i class="bi bi-check-lg me-2"></i><?= $isEdit?'Update Unit':'Add Unit' ?></button>
  <a href="<?= base_url($isEdit ? 'units/view/'.$u['id'] : 'facilities/'.$facility['id'].'/units') ?>" class="btn btn-fm-outline">Cancel</a>
</div>

<?= form_close() ?>

<?= $this->section('scripts') ?>
<script>
function togglePlateNumberField(){
  const type = document.getElementById('unitTypeSelect').value;
  const wrap = document.getElementById('plateNumberWrap');
  if (wrap) wrap.style.display = type === 'parking' ? '' : 'none';
}

function toggleTenantSection(){
  const status = document.getElementById('statusSelect').value;
  const show   = status === 'occupied';
  document.getElementById('tenantSection').style.display   = show ? '' : 'none';
  document.getElementById('contractSection').style.display = show ? '' : 'none';
}

function calcDuration(){
  const start = document.querySelector('[name="contract_start"]').value;
  const end   = document.getElementById('contractEnd').value;
  const rent  = parseFloat(document.querySelector('[name="rent_amount"]').value) || 0;
  if(start && end){
    const s = new Date(start), e = new Date(end);
    const months = Math.round((e - s) / (1000*60*60*24*30.44));
    const total  = months * rent;
    document.getElementById('durationInfo').style.display = '';
    document.getElementById('durationText').textContent   = `Duration: ${months} months${rent?` · Total value: ${new Intl.NumberFormat().format(total)} <?= $currency ?>`:''}`;
    if(rent){
      document.getElementById('financialSummary').style.display = '';
      document.getElementById('summaryContent').innerHTML = `
        <div class="d-flex justify-content-between"><span>Monthly Rent</span><strong><?= $currency ?> ${new Intl.NumberFormat().format(rent)}</strong></div>
        <div class="d-flex justify-content-between"><span>Duration</span><strong>${months} months</strong></div>
        <div class="d-flex justify-content-between border-top mt-2 pt-2"><span class="fw-bold">Total Contract Value</span><strong class="text-primary"><?= $currency ?> ${new Intl.NumberFormat().format(total)}</strong></div>
      `;
    }
  }
}

// Init on load
togglePlateNumberField();
toggleTenantSection();
document.querySelector('[name="contract_start"]').addEventListener('change', calcDuration);
document.querySelector('[name="rent_amount"]').addEventListener('input', calcDuration);
calcDuration();
</script>
<?= $this->endSection() ?>
<?= $this->endSection() ?>
