<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-pencil me-2 text-primary"></i>Edit Asset</h1></div></div>
<?= form_open(base_url('asset-register/update/'.$asset['id'])) ?>
<div class="row g-3"><div class="col-lg-8">
<div class="fm-form-section"><h6>Asset Information</h6><div class="row g-3">
<div class="col-md-8"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" value="<?= esc($asset['name']) ?>" required></div>
<div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select"><?php foreach(['active','under_maintenance','faulty','retired','disposed'] as $s): ?><option value="<?= $s ?>" <?= $asset['status']===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option><?php endforeach; ?></select></div>
<div class="col-md-4"><label class="form-label">Criticality</label><select name="criticality" class="form-select"><?php foreach(['low','medium','high','critical'] as $cr): ?><option value="<?= $cr ?>" <?= ($asset['criticality']??'medium')===$cr?'selected':'' ?>><?= ucfirst($cr) ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">Facility</label><select name="facility_id" class="form-select"><?php foreach($facilities as $f): ?><option value="<?= $f['id'] ?>" <?= $asset['facility_id']==$f['id']?'selected':'' ?>><?= esc($f['name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">Category</label><select name="category" class="form-select"><?php foreach(['hvac','elevator','electrical','plumbing','fire_safety','security','it','civil','other'] as $c): ?><option value="<?= $c ?>" <?= $asset['category']===$c?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$c)) ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">Manufacturer</label><input type="text" name="manufacturer" class="form-control" value="<?= esc($asset['manufacturer'] ?? $asset['brand'] ?? '') ?>"></div>
<div class="col-md-6"><label class="form-label">Brand</label><input type="text" name="brand" class="form-control" value="<?= esc($asset['brand']) ?>"></div>
<div class="col-md-6"><label class="form-label">Assigned To</label><select name="assigned_to" class="form-select"><option value="">—</option><?php foreach($users ?? [] as $u): ?><option value="<?= $u['id'] ?>" <?= (string)($asset['assigned_to']??'')===(string)$u['id']?'selected':'' ?>><?= esc($u['name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-6"><label class="form-label">Model</label><input type="text" name="model" class="form-control" value="<?= esc($asset['model']) ?>"></div>
<div class="col-md-6"><label class="form-label">Serial Number</label><input type="text" name="serial_number" class="form-control" value="<?= esc($asset['serial_number']) ?>"></div>
<div class="col-md-6"><label class="form-label">Location in Facility</label><input type="text" name="location_in_facility" class="form-control" value="<?= esc($asset['location_in_facility']) ?>"></div>
<div class="col-md-4"><label class="form-label">Purchase Date</label><input type="date" name="purchase_date" class="form-control" value="<?= $asset['purchase_date'] ?>"></div>
<div class="col-md-4"><label class="form-label">Purchase Cost</label><input type="number" name="purchase_cost" step="0.01" class="form-control" value="<?= $asset['purchase_cost'] ?>"></div>
<div class="col-md-4"><label class="form-label">Health Score (0-100)</label><input type="number" name="health_score" class="form-control" min="0" max="100" value="<?= $asset['health_score'] ?>"></div>
<div class="col-md-6"><label class="form-label">Warranty Expiry</label><input type="date" name="warranty_expiry" class="form-control" value="<?= $asset['warranty_expiry'] ?>"></div>
<div class="col-md-6"><label class="form-label">AMC Expiry</label><input type="date" name="amc_expiry" class="form-control" value="<?= $asset['amc_expiry'] ?>"></div>
<div class="col-md-6"><label class="form-label">Next Maintenance</label><input type="date" name="next_maintenance" class="form-control" value="<?= $asset['next_maintenance'] ?? '' ?>"></div>
<div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="3"><?= esc($asset['notes']) ?></textarea></div>
</div></div></div>
<div class="col-lg-4"><div class="fm-form-section"><h6>Actions</h6><button type="submit" class="btn btn-fm-primary w-100 mb-2">Save Changes</button><a href="<?= base_url('asset-register/view/'.$asset['id']) ?>" class="btn btn-fm-outline w-100">Cancel</a></div></div></div>
<?= form_close() ?>
<?= $this->endSection() ?>
