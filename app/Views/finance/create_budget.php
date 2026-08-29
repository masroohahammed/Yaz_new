<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= $this->include('finance/_page_header', ['title' => 'New Budget', 'backUrl' => 'finance/budgets']) ?>
<div class="fm-card"><div class="fm-card-body">
<?= form_open(base_url('finance/budgets/store')) ?>
<?= csrf_field() ?>
<div class="row g-3 mb-3">
  <div class="col-md-6"><label class="form-label">Budget name</label><input type="text" name="name" class="form-control" required></div>
  <div class="col-md-3"><label class="form-label">Fiscal year</label><input type="number" name="fiscal_year" class="form-control" value="<?= date('Y') ?>" required></div>
  <div class="col-md-3"><label class="form-label">Total amount</label><input type="number" name="total_amount" class="form-control" step="0.01" min="0" value="0"></div>
</div>
<h6 class="fw-bold">Line categories (optional)</h6>
<div id="budgetLines">
  <div class="row g-2 mb-2 budget-line"><div class="col-7"><input type="text" name="categories[]" class="form-control form-control-sm" placeholder="Category e.g. Maintenance"></div>
  <div class="col-4"><input type="number" name="amounts[]" class="form-control form-control-sm" step="0.01" placeholder="Amount"></div></div>
</div>
<button type="button" class="btn btn-sm btn-outline-secondary mb-3" id="addBudgetLine">+ Line</button>
<button type="submit" class="btn btn-fm-primary">Save budget</button>
<?= form_close() ?>
</div></div>
<script>
document.getElementById('addBudgetLine')?.addEventListener('click', () => {
  const row = document.querySelector('.budget-line').cloneNode(true);
  row.querySelectorAll('input').forEach(i => i.value = '');
  document.getElementById('budgetLines').appendChild(row);
});
</script>
<?= $this->endSection() ?>
