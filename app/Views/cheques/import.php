<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-upload me-2 text-primary"></i>Import Cheques (CSV)</h1></div>
<a href="<?= base_url('cheques') ?>" class="btn btn-fm-outline btn-sm">Back</a></div>

<div class="form-card">
  <p class="text-muted mb-3">Upload a CSV file with columns: <code>cheque_no, amount, bank_name, cheque_date, contract_id</code> (first row = headers).</p>
  <form method="post" action="<?= base_url('cheques/import') ?>" enctype="multipart/form-data"><?= csrf_field() ?>
    <div class="mb-3">
      <label class="form-label">CSV File <span class="text-danger">*</span></label>
      <input type="file" name="csv_file" class="form-control" accept=".csv,text/csv" required>
    </div>
    <button class="btn btn-fm-primary">Import</button>
  </form>
</div>
<?= $this->endSection() ?>
