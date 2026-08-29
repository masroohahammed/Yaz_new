<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-arrow-return-left me-2"></i>Reimbursement Request</h1></div></div>
<?= form_open_multipart(base_url('finance/reimbursements/store')) ?>
<div class="row g-3">
  <div class="col-lg-7">
    <div class="fm-form-section">
      <h6><i class="bi bi-info-circle"></i>Expense Details</h6>
      <div class="row g-2">
        <div class="col-md-6"><label class="form-label">Category</label>
          <select name="category" class="form-select"><?php foreach(['general'=>'General','travel'=>'Travel','meals'=>'Meals','accommodation'=>'Accommodation','transport'=>'Transport','office_supplies'=>'Office Supplies','other'=>'Other'] as $v=>$l): ?><option value="<?= $v ?>"><?= $l ?></option><?php endforeach; ?></select></div>
        <div class="col-md-6"><label class="form-label">Expense Date <span class="text-danger">*</span></label>
          <input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d') ?>" required max="<?= date('Y-m-d') ?>"></div>
        <div class="col-md-6"><label class="form-label">Amount (<?= $currency ?>) <span class="text-danger">*</span></label>
          <input type="number" name="amount" class="form-control" step="0.01" min="0.01" required></div>
        <div class="col-md-6"><label class="form-label">Receipt / Invoice</label>
          <input type="file" name="receipt" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp">
          <div class="form-text">PDF or image. Max 5MB.</div></div>
        <div class="col-12"><label class="form-label">Description <span class="text-danger">*</span></label>
          <textarea name="description" class="form-control" rows="3" required placeholder="What was purchased / expenses incurred..."></textarea></div>
      </div>
    </div>
  </div>
</div>
<div class="d-flex gap-2 mt-2">
  <button type="submit" class="btn btn-fm-primary"><i class="bi bi-send me-2"></i>Submit Request</button>
  <a href="<?= base_url('finance/reimbursements') ?>" class="btn btn-fm-outline">Cancel</a>
</div>
<?= form_close() ?>
<?= $this->endSection() ?>
