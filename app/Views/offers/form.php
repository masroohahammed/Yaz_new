<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><?= $offer ? 'Edit Complimentary Offer' : 'New Complimentary Offer' ?></h1></div>
  <a href="<?= base_url('complimentary-offers') ?>" class="btn btn-fm-outline btn-sm">Back</a>
</div>
<div class="form-card">
  <form method="post" action="<?= $offer ? base_url('complimentary-offers/'.$offer['id'].'/update') : base_url('complimentary-offers') ?>"><?= csrf_field() ?>
  <?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ((array)session()->getFlashdata('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <div class="row g-3">
    <?php if (!$offer): ?>
    <div class="col-md-6">
      <label class="form-label">Lease Contract <span class="text-danger">*</span></label>
      <select name="contract_id" class="form-select" required>
        <option value="">— select contract —</option>
        <?php foreach ($contracts as $c): ?>
          <option value="<?= $c['id'] ?>"><?= esc($c['contract_number']) ?> — <?= esc($c['tenant_name']??'') ?> (<?= esc($c['facility_name']??'') ?>)</option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php else: ?>
    <input type="hidden" name="contract_id" value="<?= $offer['contract_id'] ?>">
    <?php endif; ?>
    <div class="col-md-6">
      <label class="form-label">Offer Type <span class="text-danger">*</span></label>
      <select name="offer_type" class="form-select" required>
        <option value="">— select —</option>
        <?php foreach (['free_months','discount_percent','fit_out_allowance','parking_free','furniture_package','other'] as $t): ?>
          <option value="<?= $t ?>" <?= ($offer['offer_type']??'')===$t?'selected':'' ?>><?= str_replace('_',' ',ucfirst($t)) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Free Period (months)</label>
      <input type="number" min="0" name="free_period_value" class="form-control" value="<?= esc($offer['free_period_value']??'') ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Discount %</label>
      <input type="number" step="0.01" min="0" max="100" name="discount_percent" class="form-control" value="<?= esc($offer['discount_percent']??'') ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">&nbsp;</label><div></div>
    </div>
    <div class="col-md-4">
      <label class="form-label">Start Date</label>
      <input type="date" name="start_date" class="form-control" value="<?= esc($offer['start_date']??'') ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">End Date</label>
      <input type="date" name="end_date" class="form-control" value="<?= esc($offer['end_date']??'') ?>">
    </div>
    <div class="col-12">
      <label class="form-label">Notes</label>
      <textarea name="notes" class="form-control" rows="2"><?= esc($offer['notes']??'') ?></textarea>
    </div>
  </div>
  <div class="mt-3">
    <button class="btn btn-fm-primary"><?= $offer ? 'Update Offer' : 'Create Offer' ?></button>
    <a href="<?= base_url('complimentary-offers') ?>" class="btn btn-fm-outline ms-2">Cancel</a>
  </div>
  </form>
</div>
<?= $this->endSection() ?>
