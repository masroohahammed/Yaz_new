<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-upload me-2 text-primary"></i>Import Cheques (Excel / CSV)</h1></div>
<a href="<?= base_url('cheques') ?>" class="btn btn-fm-outline btn-sm">Back</a></div>

<?php if ($errs = session()->getFlashdata('import_errors')): ?>
<div class="alert alert-warning small">
  <strong>Import notes:</strong>
  <ul class="mb-0 mt-1"><?php foreach ((array) $errs as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>

<div class="form-card">
  <p class="text-muted mb-3">Upload <strong>.xlsx</strong> or <strong>.csv</strong> with header row. Supported columns:</p>
  <p class="small font-monospace mb-3">cheque_no, amount, bank_name, cheque_date, due_date, received_date, contract_id, payment_id, payable_to_type, payable_to_id, landlord_id, account_name, account_no</p>
  <form method="post" action="<?= base_url('cheques/import') ?>" enctype="multipart/form-data"><?= csrf_field() ?>
    <div class="mb-3">
      <label class="form-label">Excel or CSV file <span class="text-danger">*</span></label>
      <input type="file" name="import_file" class="form-control" accept=".csv,.xlsx,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
    </div>
    <button class="btn btn-fm-primary">Import cheques</button>
  </form>
</div>
<?= $this->endSection() ?>
