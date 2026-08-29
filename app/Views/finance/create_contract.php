<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-file-earmark-plus me-2"></i>New Contract</h1></div></div>
<?= form_open_multipart(base_url('finance/contracts/store')) ?>
<div class="row g-3">
  <div class="col-lg-8">
    <div class="fm-form-section">
      <h6><i class="bi bi-person"></i>Client / Tenant Information</h6>
      <div class="row g-2">
        <div class="col-md-8"><label class="form-label">Client Name <span class="text-danger">*</span></label><input type="text" name="client_name" class="form-control" required value="<?= old('client_name') ?>"></div>
        <div class="col-md-4"><label class="form-label">Contract Type</label><select name="contract_type" class="form-select"><?php foreach(['fm_services'=>'FM Services','amc'=>'AMC','cleaning'=>'Cleaning','security'=>'Security','it_support'=>'IT Support','other'=>'Other'] as $v=>$l): ?><option value="<?= $v ?>" <?= (isset($contract) && $contract['contract_type']===$v)?'selected':'' ?>><?= $l ?></option><?php endforeach; ?></select></div>
        <div class="col-md-6"><label class="form-label">Client Email</label><input type="email" name="client_email" class="form-control" value="<?= old('client_email') ?>"></div>
        <div class="col-md-6"><label class="form-label">Client Mobile</label><input type="text" name="client_mobile" class="form-control" value="<?= old('client_mobile') ?>"></div>
      </div>
    </div>
    <div class="fm-form-section">
      <h6><i class="bi bi-calendar"></i>Contract Period & Value</h6>
      <div class="row g-2">
        <div class="col-md-6"><label class="form-label">Facility <span class="text-danger">*</span></label><select name="facility_id" class="form-select" required><option value="">— Select —</option><?php foreach($facilities as $f): ?><option value="<?= $f['id'] ?>" <?= (old('facility_id')==$f['id'])?'selected':'' ?>><?= esc($f['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-6"><label class="form-label">Contract Value (<?= $currency ?>) <span class="text-danger">*</span></label><input type="number" name="value" class="form-control" step="0.01" min="0" required value="<?= old('value') ?>"></div>
        <div class="col-md-6"><label class="form-label">Start Date <span class="text-danger">*</span></label><input type="date" name="start_date" class="form-control" required value="<?= old('start_date',date('Y-m-d')) ?>"></div>
        <div class="col-md-6"><label class="form-label">End Date <span class="text-danger">*</span></label><input type="date" name="end_date" class="form-control" required value="<?= old('end_date') ?>"></div>
        <div class="col-md-6"><label class="form-label">Billing Frequency</label><select name="billing_frequency" class="form-select"><?php foreach(['monthly'=>'Monthly','quarterly'=>'Quarterly','annual'=>'Annual','one_time'=>'One-time'] as $v=>$l): ?><option value="<?= $v ?>" <?= old('billing_frequency', $contract['billing_frequency'] ?? 'quarterly')===$v?'selected':'' ?>><?= $l ?></option><?php endforeach; ?></select></div>
        <div class="col-md-6"><label class="form-label">Billing Day (1–28)</label><input type="number" name="billing_day" class="form-control" min="1" max="28" value="<?= old('billing_day', $contract['billing_day'] ?? 1) ?>"></div>
        <div class="col-12"><label class="form-label">Payment Terms</label><input type="text" name="payment_terms" class="form-control" placeholder="e.g. Net 30" value="<?= old('payment_terms') ?>"></div>
        <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="3"><?= old('notes', isset($contract) ? $contract['notes'] : '') ?></textarea></div>
        <?php if(isset($contract)): ?>
        <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select"><?php foreach(['active'=>'Active','expired'=>'Expired','terminated'=>'Terminated','draft'=>'Draft'] as $v=>$l): ?><option value="<?= $v ?>" <?= $contract['status']===$v?'selected':'' ?>><?= $l ?></option><?php endforeach; ?></select></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<div class="d-flex gap-2 mt-2">
  <?php if(isset($contract)): ?>
  <input type="hidden" name="_method" value="edit">
  <?php endif; ?>
  <button type="submit" class="btn btn-fm-primary"><i class="bi bi-check-lg me-2"></i><?= isset($contract) ? 'Update Contract' : 'Create Contract' ?></button>
  <a href="<?= base_url('finance/contracts') ?>" class="btn btn-fm-outline">Cancel</a>
</div>
<?= form_close() ?>
<?= $this->endSection() ?>
