<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $isEdit=isset($company)&&!empty($company['id']); $c=$company??[]; ?>
<div class="page-header"><div><h1><i class="bi bi-building-plus me-2"></i><?= $isEdit?'Edit Company':'Add Company' ?></h1></div></div>
<?= form_open_multipart($isEdit?base_url('settings/companies/update/'.$c['id']):base_url('settings/companies/store')) ?>
<div class="row g-3">
  <div class="col-lg-7">
    <div class="fm-form-section">
      <h6><i class="bi bi-info-circle"></i>Company Details</h6>
      <div class="row g-2">
        <div class="col-md-8"><label class="form-label">Company Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="<?= esc($c['name']??old('name')) ?>" required></div>
        <div class="col-md-4"><label class="form-label">Company Code <span class="text-danger">*</span></label><input type="text" name="code" class="form-control" value="<?= esc($c['code']??old('code')) ?>" required placeholder="e.g. FM01" style="text-transform:uppercase" maxlength="20"></div>
        <div class="col-md-6"><label class="form-label">Contact Person</label><input type="text" name="contact_person" class="form-control" value="<?= esc($c['contact_person']??'') ?>"></div>
        <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= esc($c['email']??'') ?>"></div>
        <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="<?= esc($c['phone']??'') ?>"></div>
        <div class="col-md-6"><label class="form-label">VAT / Tax Number</label><input type="text" name="vat_number" class="form-control" value="<?= esc($c['vat_number']??'') ?>"></div>
        <div class="col-12"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="3"><?= esc($c['address']??'') ?></textarea></div>
        <div class="col-md-6"><label class="form-label">Logo</label><input type="file" name="logo" class="form-control" accept="image/*"><?php if(!empty($c['logo'])): ?><div class="small text-muted mt-1">Current: <a href="<?= base_url('file/companies/'.basename($c['logo'])) ?>" target="_blank">View</a></div><?php endif; ?></div>
        <?php if($isEdit): ?><div class="col-md-6"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active" <?= ($c['status']??'')==='active'?'selected':'' ?>>Active</option><option value="inactive" <?= ($c['status']??'')==='inactive'?'selected':'' ?>>Inactive</option></select></div><?php endif; ?>
      </div>
    </div>
  </div>
</div>
<div class="d-flex gap-2 mt-2">
  <button type="submit" class="btn btn-fm-primary"><i class="bi bi-check-lg me-2"></i><?= $isEdit?'Update':'Add Company' ?></button>
  <a href="<?= base_url('settings/companies') ?>" class="btn btn-fm-outline">Cancel</a>
</div>
<?= form_close() ?>
<?= $this->endSection() ?>
