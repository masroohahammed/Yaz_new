<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-pencil me-2 text-primary"></i>Edit Facility</h1></div></div>
<?= form_open(base_url('facilities/update/'.$facility['id'])) ?>
<div class="row g-3"><div class="col-lg-8"><div class="fm-form-section"><h6>Facility Details</h6><div class="row g-3">
<div class="col-md-8"><label class="form-label">Name *</label><input type="text" name="name" class="form-control" value="<?= esc($facility['name']) ?>" required></div>
<div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select"><?php foreach(['active','inactive','under_maintenance'] as $s): ?><option value="<?= $s ?>" <?= $facility['status']===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option><?php endforeach; ?></select></div>
<div class="col-12"><label class="form-label">Address</label><input type="text" name="address" class="form-control" value="<?= esc($facility['address']) ?>"></div>
<div class="col-md-6"><label class="form-label">City *</label><input type="text" name="city" class="form-control" value="<?= esc($facility['city']) ?>" required></div>
<div class="col-md-6"><label class="form-label">Country</label><input type="text" name="country" class="form-control" value="<?= esc($facility['country']) ?>"></div>
<div class="col-md-6"><label class="form-label">Manager</label><select name="manager_id" class="form-select"><option value="">— None —</option><?php foreach($managers as $m): ?><option value="<?= $m['id'] ?>" <?= $facility['manager_id']==$m['id']?'selected':'' ?>><?= esc($m['name']) ?></option><?php endforeach; ?></select></div>
<div class="col-md-3"><label class="form-label">Area (sqm)</label><input type="number" name="area_sqm" step="0.01" class="form-control" value="<?= $facility['area_sqm'] ?>"></div>
<div class="col-md-3"><label class="form-label">Floors</label><input type="number" name="floors" class="form-control" value="<?= $facility['floors'] ?>"></div>
</div></div></div>
<div class="col-lg-4"><div class="fm-form-section"><h6>Actions</h6><button type="submit" class="btn btn-fm-primary w-100 mb-2">Save</button><a href="<?= base_url('facilities/view/'.$facility['id']) ?>" class="btn btn-fm-outline w-100">Cancel</a></div></div></div>
<?= form_close() ?>
<?= $this->endSection() ?>
