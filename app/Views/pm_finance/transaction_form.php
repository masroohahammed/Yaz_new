<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1>Record Transaction</h1></div>
<?= form_open(base_url('finance/pm/transaction')) ?>
<?= csrf_field() ?>
<div class="row g-2">
  <div class="col-md-4"><label class="form-label small">Amount *</label><input type="number" step="0.01" name="amount" class="form-control form-control-sm" required></div>
  <div class="col-md-4"><label class="form-label small">Cost type *</label>
    <select name="cost_type_id" class="form-select form-select-sm" required>
      <?php foreach ($costTypes as $ct): ?><option value="<?= $ct['id'] ?>"><?= esc($ct['name']) ?></option><?php endforeach; ?>
    </select></div>
  <div class="col-md-4"><label class="form-label small">Payment date *</label><input type="date" name="payment_date" class="form-control form-control-sm" required value="<?= date('Y-m-d') ?>"></div>
  <div class="col-md-4"><label class="form-label small">Property</label>
    <select name="property_id" class="form-select form-select-sm"><option value="">—</option>
      <?php foreach ($facilities as $f): ?><option value="<?= $f['id'] ?>"><?= esc($f['name']) ?></option><?php endforeach; ?>
    </select></div>
  <div class="col-md-4"><label class="form-label small">Method</label>
    <select name="payment_method" class="form-select form-select-sm"><option value="cash">Cash</option><option value="cheque">Cheque</option><option value="transfer">Transfer</option></select></div>
  <div class="col-12"><label class="form-label small">Description</label><textarea name="description" class="form-control form-control-sm" rows="2"></textarea></div>
  <div class="col-12"><button type="submit" class="btn btn-fm-primary btn-sm">Post Entry</button></div>
</div>
<?= form_close() ?>
<?= $this->endSection() ?>
