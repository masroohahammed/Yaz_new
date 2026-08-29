<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><?= $account ? 'Edit Utility Account' : 'New Utility Account' ?></h1></div>
  <a href="<?= base_url('utilities') ?>" class="btn btn-fm-outline btn-sm">Back</a>
</div>
<div class="form-card">
  <form method="post" action="<?= $account ? base_url('utilities/'.$account['id'].'/update') : base_url('utilities') ?>"><?= csrf_field() ?>
  <?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ((array)session()->getFlashdata('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <div class="row g-3">
    <div class="col-md-4">
      <label class="form-label">Utility Name <span class="text-danger">*</span></label>
      <input name="utility_name" class="form-control" required value="<?= esc($account['utility_name']??'') ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Property</label>
      <select name="facility_id" class="form-select">
        <option value="">—</option>
        <?php foreach ($facilities as $f): ?>
          <option value="<?= $f['id'] ?>" <?= ($account['facility_id']??'')==$f['id']?'selected':'' ?>><?= esc($f['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Billing Mode</label>
      <select name="billing_mode" class="form-select">
        <?php foreach (['included','billed_separately','tenant_pays_direct','complimentary'] as $m): ?>
          <option value="<?= $m ?>" <?= ($account['billing_mode']??'included')===$m?'selected':'' ?>><?= str_replace('_',' ',ucfirst($m)) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Provider Name</label>
      <input name="provider_name" class="form-control" value="<?= esc($account['provider_name']??'') ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Account Number</label>
      <input name="account_number" class="form-control" value="<?= esc($account['account_number']??'') ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Meter Number</label>
      <input name="meter_number" class="form-control" value="<?= esc($account['meter_number']??'') ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Managed By</label>
      <input name="managed_by" class="form-control" value="<?= esc($account['managed_by']??'') ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Monthly Charge</label>
      <input type="number" step="0.01" name="monthly_charge" class="form-control" value="<?= esc($account['monthly_charge']??'') ?>">
    </div>
    <div class="col-12">
      <label class="form-label">Notes</label>
      <textarea name="notes" class="form-control" rows="2"><?= esc($account['notes']??'') ?></textarea>
    </div>
  </div>
  <div class="mt-3">
    <button class="btn btn-fm-primary"><?= $account ? 'Update Account' : 'Create Account' ?></button>
    <a href="<?= base_url('utilities') ?>" class="btn btn-fm-outline ms-2">Cancel</a>
  </div>
  </form>
</div>
<?= $this->endSection() ?>
