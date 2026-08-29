<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1>New Petty Cash Expense</h1></div>
<form method="post" action="<?= base_url('finance-petty/expenses/store') ?>"><?= csrf_field() ?>
<div class="fm-card"><div class="fm-card-body row g-3">
  <div class="col-md-4"><label class="form-label">Date</label><input type="date" name="expense_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
  <div class="col-md-4"><label class="form-label">Petty Cash Account *</label><select name="petty_account_id" class="form-select" required><?php foreach ($accounts as $a): ?><option value="<?= $a['id'] ?>"><?= esc($a['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-4"><label class="form-label">Amount *</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
  <div class="col-md-4"><label class="form-label">Category</label><select name="category_id" class="form-select"><option value="">—</option><?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>"><?= esc($c['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-4"><label class="form-label">Property</label><select name="facility_id" class="form-select"><option value="">—</option><?php foreach ($facilities as $f): ?><option value="<?= $f['id'] ?>"><?= esc($f['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-4"><label class="form-label">Work Order</label><select name="work_order_id" class="form-select"><option value="">—</option><?php foreach ($workOrders as $w): ?><option value="<?= $w['id'] ?>"><?= esc($w['wo_number'].' — '.$w['title']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-4"><label class="form-label">Vendor</label><select name="vendor_id" class="form-select"><option value="">—</option><?php foreach ($vendors as $v): ?><option value="<?= $v['id'] ?>"><?= esc($v['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-4"><label class="form-label">Receipt #</label><input name="receipt_number" class="form-control"></div>
  <div class="col-12"><label class="form-label">Description</label><input name="description" class="form-control"></div>
</div></div>
<button class="btn btn-fm-primary">Save Draft</button>
</form>
<?= $this->endSection() ?>
