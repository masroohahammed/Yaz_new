<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= $this->include('hr/_subnav', ['hrActive' => 'expenses']) ?>
<div class="page-header mb-3"><h1 class="h4">Submit Expense Claim</h1></div>
<div class="hr-page-card">
  <?= form_open_multipart(base_url('hr/expenses/store')) ?>
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-md-6"><label class="form-label">Title</label><input name="title" class="form-control" required></div>
    <div class="col-md-3"><label class="form-label">Amount</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
    <div class="col-md-3"><label class="form-label">Date</label><input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
    <div class="col-md-4"><label class="form-label">Category</label><select name="category" class="form-select"><option value="travel">Travel</option><option value="meals">Meals</option><option value="supplies">Supplies</option><option value="other">Other</option></select></div>
    <div class="col-md-8"><label class="form-label">Receipt</label><input type="file" name="receipt" class="form-control" accept=".pdf,.jpg,.jpeg,.png"></div>
    <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
    <div class="col-12"><button class="btn btn-fm-primary">Submit claim</button></div>
  </div>
  <?= form_close() ?>
</div>
<?= $this->endSection() ?>
