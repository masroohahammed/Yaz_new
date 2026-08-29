<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-cash-coin me-2 text-primary"></i>Create Payout</h1>
    <div class="small text-muted"><?= esc($landlord['full_name']) ?></div>
  </div>
  <a href="<?= base_url('landlords/'.$landlord['id'].'/show') ?>" class="btn btn-fm-outline btn-sm">Back</a>
</div>

<div class="form-card">
  <form method="post" action="<?= base_url('landlords/'.$landlord['id'].'/payout') ?>"><?= csrf_field() ?>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Period From</label><input type="date" name="period_from" class="form-control"></div>
      <div class="col-md-6"><label class="form-label">Period To</label><input type="date" name="period_to" class="form-control"></div>
      <div class="col-md-4"><label class="form-label">Gross Rent</label><input type="number" step="0.01" name="gross_rent" class="form-control"></div>
      <div class="col-md-4"><label class="form-label">Commission</label><input type="number" step="0.01" name="commission" class="form-control" placeholder="<?= esc($landlord['commission_pct']??'') ?>%"></div>
      <div class="col-md-4"><label class="form-label">Deductions</label><input type="number" step="0.01" name="deductions" class="form-control" value="0"></div>
      <div class="col-md-6"><label class="form-label">Net Amount (leave blank to auto-calculate)</label><input type="number" step="0.01" name="net_amount" class="form-control"></div>
      <div class="col-md-6"><label class="form-label">Payment Method</label><input type="text" name="payment_method" class="form-control" placeholder="bank transfer, cheque…"></div>
      <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="3"></textarea></div>
    </div>
    <div class="mt-3"><button class="btn btn-fm-primary">Create Payout</button></div>
  </form>
</div>
<?= $this->endSection() ?>
