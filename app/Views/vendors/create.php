<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-building me-2"></i>Add Vendor</h1></div><a href="<?= base_url('vendors') ?>" class="btn btn-fm-outline btn-sm">← Back</a></div>
<div class="row"><div class="col-lg-7"><div class="fm-card"><div class="fm-card-body">
<?= form_open('vendors/store') ?>
<div class="row g-3">
  <div class="col-md-8"><label class="form-label">Vendor Name *</label><input type="text" name="name" class="form-control" required value="<?= old('name') ?>"></div>
  <div class="col-md-4"><label class="form-label">Category *</label><select name="category" class="form-select" required><option value="HVAC">HVAC</option><option value="Electrical">Electrical</option><option value="Plumbing">Plumbing</option><option value="Civil">Civil</option><option value="Cleaning">Cleaning</option><option value="Security">Security</option><option value="IT">IT</option><option value="Fire Safety">Fire Safety</option><option value="General">General</option></select></div>
  <div class="col-md-6"><label class="form-label">Contact Person</label><input type="text" name="contact" class="form-control" value="<?= old('contact') ?>"></div>
  <div class="col-md-6"><label class="form-label">Phone</label><input type="tel" name="phone" class="form-control" value="<?= old('phone') ?>"></div>
  <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= old('email') ?>"></div>
  <div class="col-md-6"><label class="form-label">Rating (1-5)</label><select name="rating" class="form-select"><option value="0">Unrated</option><option value="1">★ Poor</option><option value="2">★★ Fair</option><option value="3">★★★ Good</option><option value="4">★★★★ Very Good</option><option value="5">★★★★★ Excellent</option></select></div>
  <div class="col-12"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2"></textarea></div>
  <div class="col-12"><button type="submit" class="btn btn-fm-primary">Add Vendor</button></div>
</div>
<?= form_close() ?>
</div></div></div></div>
<?= $this->endSection() ?>
