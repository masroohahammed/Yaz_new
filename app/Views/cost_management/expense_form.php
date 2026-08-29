<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1>Add Property Expense</h1></div>
  <a href="<?= base_url('cost-management') ?>" class="btn btn-fm-outline btn-sm">Back</a>
</div>
<div class="form-card">
  <form method="post" action="<?= base_url('cost-management/expense/store') ?>"><?= csrf_field() ?>
  <?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ((array)session()->getFlashdata('errors') as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul></div>
  <?php endif; ?>
  <div class="row g-3">
    <div class="col-md-8">
      <label class="form-label">Title / Description <span class="text-danger">*</span></label>
      <input name="title" class="form-control" required value="<?= esc(old('title')) ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Category</label>
      <select name="category" class="form-select">
        <option value="">— select —</option>
        <?php foreach (\App\Support\PmExpenseCategories::labels() as $c => $lab): ?>
          <option value="<?= $c ?>"><?= esc($lab) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-5">
      <label class="form-label">Property</label>
      <select name="facility_id" class="form-select">
        <option value="">—</option>
        <?php foreach ($facilities as $f): ?>
          <option value="<?= $f['id'] ?>"><?= esc($f['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Amount <span class="text-danger">*</span></label>
      <input type="number" step="0.01" name="amount" class="form-control" required value="<?= esc(old('amount')) ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Date <span class="text-danger">*</span></label>
      <input type="date" name="expense_date" class="form-control" required value="<?= esc(old('expense_date', date('Y-m-d'))) ?>">
    </div>
    <div class="col-12">
      <label class="form-label">Notes</label>
      <textarea name="notes" class="form-control" rows="2"></textarea>
    </div>
  </div>
  <div class="mt-3">
    <button class="btn btn-fm-primary">Add Expense</button>
    <a href="<?= base_url('cost-management') ?>" class="btn btn-fm-outline ms-2">Cancel</a>
  </div>
  </form>
</div>
<?= $this->endSection() ?>
