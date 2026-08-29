<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-plus-circle me-2 text-primary"></i>Add New Asset</h1></div><a href="<?= base_url('asset-register') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a></div>
<?= form_open('asset-register/store',['class'=>'needs-validation','novalidate'=>true]) ?>
<div class="row g-3">
  <div class="col-lg-8">
    <div class="fm-form-section"><h6><i class="bi bi-info-circle"></i>Basic Information</h6>
    <div class="row g-3">
      <div class="col-md-8"><label class="form-label">Asset Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="<?= old('name') ?>" required placeholder="e.g. Central HVAC Unit A1"></div>
      <div class="col-md-4"><label class="form-label">Asset Code</label><input type="text" name="asset_code" class="form-control" value="<?= old('asset_code') ?>" placeholder="Auto-generated"></div>
      <div class="col-md-6"><label class="form-label">Facility <span class="text-danger">*</span></label><select name="facility_id" class="form-select" required><option value="">Select facility</option><?php foreach($facilities as $f): ?><option value="<?= $f['id'] ?>"><?= esc($f['name']) ?></option><?php endforeach; ?></select></div>
      <div class="col-md-6"><label class="form-label">Category <span class="text-danger">*</span></label><select name="category" class="form-select" required><option value="">Select category</option><?php foreach(['hvac','elevator','electrical','plumbing','fire_safety','security','it','civil','other'] as $c): ?><option value="<?= $c ?>"><?= ucfirst(str_replace('_',' ',$c)) ?></option><?php endforeach; ?></select></div>
      <div class="col-md-6"><label class="form-label">Asset Type</label><input type="text" name="asset_type" class="form-control" value="<?= old('asset_type') ?>" placeholder="e.g. Chiller, AHU, Pump"></div>
      <div class="col-md-6"><label class="form-label">Criticality</label><select name="criticality" class="form-select"><?php foreach(['low','medium','high','critical'] as $cr): ?><option value="<?= $cr ?>"><?= ucfirst($cr) ?></option><?php endforeach; ?></select></div>
      <div class="col-md-6"><label class="form-label">Brand</label><input type="text" name="brand" class="form-control" value="<?= old('brand') ?>" placeholder="Carrier, Kone, ABB..."></div>
      <div class="col-md-6"><label class="form-label">Manufacturer</label><input type="text" name="manufacturer" class="form-control" value="<?= old('manufacturer') ?>"></div>
      <div class="col-md-6"><label class="form-label">Model</label><input type="text" name="model" class="form-control" value="<?= old('model') ?>" placeholder="Model number"></div>
      <div class="col-md-6"><label class="form-label">Serial Number</label><input type="text" name="serial_number" class="form-control" value="<?= old('serial_number') ?>"></div>
      <div class="col-md-6"><label class="form-label">Location in Facility</label><input type="text" name="location_in_facility" class="form-control" value="<?= old('location_in_facility') ?>" placeholder="Floor 3, Pump Room"></div>
    </div></div>
    <div class="fm-form-section"><h6><i class="bi bi-calendar-range"></i>Dates & Financials</h6>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Purchase Date</label><input type="date" name="purchase_date" class="form-control" value="<?= old('purchase_date') ?>"></div>
      <div class="col-md-6"><label class="form-label">Purchase Cost (<?= $currency ?>)</label><input type="number" name="purchase_cost" class="form-control" value="<?= old('purchase_cost') ?>" step="0.01"></div>
      <div class="col-md-6"><label class="form-label">Warranty Expiry</label><input type="date" name="warranty_expiry" class="form-control" value="<?= old('warranty_expiry') ?>"></div>
      <div class="col-md-6"><label class="form-label">AMC Expiry</label><input type="date" name="amc_expiry" class="form-control" value="<?= old('amc_expiry') ?>"></div>
      <div class="col-md-6"><label class="form-label">Next Maintenance</label><input type="date" name="next_maintenance" class="form-control" value="<?= old('next_maintenance') ?>"></div>
    </div></div>
    <div class="fm-form-section"><h6><i class="bi bi-card-text"></i>Notes</h6><textarea name="notes" class="form-control" rows="3" placeholder="Additional notes..."><?= old('notes') ?></textarea></div>
  </div>
</div>
<div class="d-flex gap-2 mt-3"><button type="submit" class="btn btn-fm-primary"><i class="bi bi-check-lg me-2"></i>Add Asset</button><a href="<?= base_url('asset-register') ?>" class="btn btn-fm-outline">Cancel</a></div>
<?= form_close() ?>
<?= $this->endSection() ?>
