<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<h1>Pay Bill</h1>
<?= form_open(base_url('utilities/bills/'.$bill['id'].'/pay')) ?>
<?= csrf_field() ?>
<div class="mb-2"><label class="form-label small">Payment date *</label><input type="date" name="payment_date" class="form-control form-control-sm" required value="<?= date('Y-m-d') ?>"></div>
<div class="mb-2"><label class="form-label small">Method</label>
  <select name="payment_method" class="form-select form-select-sm"><?php helper('fm'); foreach (fm_payment_methods('utility') as $m => $mLabel): ?><option value="<?= esc($m) ?>"><?= esc($mLabel) ?></option><?php endforeach; ?></select></div>
<div class="mb-2"><label class="form-label small">Paid by</label>
  <select name="paid_by" class="form-select form-select-sm"><option value="company">Company</option><option value="tenant">Tenant</option></select></div>
<button type="submit" class="btn btn-sm btn-success">Pay Bill</button>
<?= form_close() ?>
<?= $this->endSection() ?>
