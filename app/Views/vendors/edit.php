<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-truck me-2"></i>Edit Vendor</h1></div></div>
<?= form_open(base_url('vendors/update/'.$vendor['id'])) ?>
<div class="row g-3">
  <div class="col-lg-8">
    <div class="fm-form-section">
      <h6><i class="bi bi-info-circle"></i>Vendor Details</h6>
      <div class="row g-2">
        <div class="col-md-8"><label class="form-label">Vendor Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" value="<?= esc($vendor['name']) ?>" required></div>
        <div class="col-md-4"><label class="form-label">Category</label><select name="category" class="form-select"><?php foreach(['general'=>'General','electrical'=>'Electrical','plumbing'=>'Plumbing','hvac'=>'HVAC','cleaning'=>'Cleaning','civil'=>'Civil','it'=>'IT','security'=>'Security','other'=>'Other'] as $v=>$l): ?><option value="<?= $v ?>" <?= ($vendor['category']??'')===$v?'selected':'' ?>><?= $l ?></option><?php endforeach; ?></select></div>
        <div class="col-md-6"><label class="form-label">Contact Person</label><input type="text" name="contact_person" class="form-control" value="<?= esc($vendor['contact_person']??$vendor['contact']??'') ?>"></div>
        <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="<?= esc($vendor['phone']??'') ?>"></div>
        <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?= esc($vendor['email']??'') ?>"></div>
        <div class="col-md-6"><label class="form-label">VAT Number</label><input type="text" name="vat_number" class="form-control" value="<?= esc($vendor['vat_number']??'') ?>"></div>
        <div class="col-12"><label class="form-label">Address</label><textarea name="address" class="form-control" rows="2"><?= esc($vendor['address']??'') ?></textarea></div>
        <div class="col-md-4"><label class="form-label">Rating (1-5)</label><input type="number" name="rating" class="form-control" min="1" max="5" value="<?= $vendor['rating']??3 ?>"></div>
        <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active" <?= ($vendor['status']??'')==='active'?'selected':'' ?>>Active</option><option value="inactive" <?= ($vendor['status']??'')==='inactive'?'selected':'' ?>>Inactive</option></select></div>
        <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"><?= esc($vendor['notes']??'') ?></textarea></div>
      </div>
    </div>
  </div>
</div>
<div class="d-flex gap-2 mt-2">
  <button type="submit" class="btn btn-fm-primary"><i class="bi bi-check-lg me-2"></i>Save Changes</button>
  <a href="<?= base_url('vendors/view/'.$vendor['id']) ?>" class="btn btn-fm-outline">Cancel</a>
</div>
<?= form_close() ?>
<?= $this->endSection() ?>
