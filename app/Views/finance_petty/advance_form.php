<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1>Request Petty Cash Advance</h1></div>
<form method="post" action="<?= base_url('finance-petty/advances/store') ?>"><?= csrf_field() ?>
<div class="fm-card"><div class="fm-card-body row g-3">
  <div class="col-md-6"><label class="form-label">Petty Cash Account</label><select name="petty_account_id" class="form-select" required><?php foreach ($accounts as $a): ?><option value="<?= $a['id'] ?>"><?= esc($a['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-6"><label class="form-label">Employee</label><select name="employee_id" class="form-select" required><?php foreach ($users as $u): ?><option value="<?= $u['id'] ?>"><?= esc($u['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-4"><label class="form-label">Amount</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
  <div class="col-md-4"><label class="form-label">Required Date</label><input type="date" name="required_date" class="form-control"></div>
  <div class="col-md-4"><label class="form-label">Expected Settlement</label><input type="date" name="expected_settlement_date" class="form-control"></div>
  <div class="col-12"><label class="form-label">Purpose</label><textarea name="purpose" class="form-control" rows="2" required></textarea></div>
</div></div>
<button class="btn btn-fm-primary">Submit Request</button>
</form>
<?= $this->endSection() ?>
