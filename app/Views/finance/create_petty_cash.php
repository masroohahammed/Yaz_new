<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-wallet-plus me-2"></i>Petty Cash Request</h1></div></div>
<?= form_open(base_url('finance/petty-cash/store')) ?>
<div class="row g-3">
  <div class="col-lg-7">
    <div class="fm-form-section">
      <h6><i class="bi bi-info-circle"></i>Request Details</h6>
      <div class="row g-2">
        <div class="col-md-6"><label class="form-label">Facility</label>
          <select name="facility_id" class="form-select"><option value="">— Optional —</option>
          <?php foreach($facilities as $f): ?><option value="<?= $f['id'] ?>"><?= esc($f['name']) ?></option><?php endforeach; ?>
          </select></div>
        <div class="col-md-6"><label class="form-label">Category</label>
          <select name="category" class="form-select">
            <?php foreach(['general'=>'General','office_supplies'=>'Office Supplies','travel'=>'Travel','repairs'=>'Repairs','utilities'=>'Utilities','food'=>'Food & Beverages','other'=>'Other'] as $v=>$l): ?>
            <option value="<?= $v ?>"><?= $l ?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="col-md-6"><label class="form-label">Amount (<?= $currency ?>) <span class="text-danger">*</span></label>
          <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required></div>
        <div class="col-12"><label class="form-label">Purpose / Description <span class="text-danger">*</span></label>
          <textarea name="purpose" class="form-control" rows="4" required placeholder="Describe what the petty cash will be used for..."></textarea></div>
      </div>
    </div>
  </div>
</div>
<div class="d-flex gap-2 mt-2">
  <button type="submit" class="btn btn-fm-primary"><i class="bi bi-send me-2"></i>Submit Request</button>
  <a href="<?= base_url('finance/petty-cash') ?>" class="btn btn-fm-outline">Cancel</a>
</div>
<?= form_close() ?>
<?= $this->endSection() ?>
