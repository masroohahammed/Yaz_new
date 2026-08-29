<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$l = $landlord;
$p = $payout ?? [];
$isMarkPaid = ! empty($isMarkPaid);
$payMethods = ['cash' => 'Cash', 'cheque' => 'Cheque', 'transfer' => 'Transfer', 'card' => 'Card'];
$commissionPct = (float) ($commissionPct ?? 0);
?>

<div class="page-header">
  <h1><?= $isMarkPaid ? 'Confirm Paid' : 'Create Payout' ?></h1>
  <nav aria-label="breadcrumb"><ol class="breadcrumb small">
    <li class="breadcrumb-item"><a href="<?= base_url('landlords') ?>">Landlords</a></li>
    <li class="breadcrumb-item"><a href="<?= base_url('landlords/' . $l['id']) ?>"><?= esc($l['full_name']) ?></a></li>
    <li class="breadcrumb-item active"><?= $isMarkPaid ? 'Confirm Paid' : 'Create Payout' ?></li>
  </ol></nav>
</div>

<?php if (session()->getFlashdata('errors')): ?>
<div class="alert alert-danger"><?php foreach ((array) session()->getFlashdata('errors') as $e): ?><div><?= esc($e) ?></div><?php endforeach; ?></div>
<?php endif; ?>

<?php if ($isMarkPaid): ?>
<?= form_open(base_url('landlords/payouts/' . $p['id'] . '/mark-paid')) ?>
<?= csrf_field() ?>
<div class="fm-form-section">
  <p class="small text-muted mb-3">
    Payout net amount: <strong><?= number_format((float) ($p['net_amount'] ?? 0), 2) ?></strong>
    <?php if (! empty($p['period_from'])): ?> · Period <?= esc($p['period_from']) ?> to <?= esc($p['period_to']) ?><?php endif; ?>
  </p>
  <div class="row g-2">
    <div class="col-md-4">
      <label class="form-label small">Paid date *</label>
      <input type="date" name="paid_date" class="form-control form-control-sm" required value="<?= esc(old('paid_date', date('Y-m-d'))) ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label small">Payment method</label>
      <select name="payment_method" class="form-select form-select-sm">
        <option value="">—</option>
        <?php foreach ($payMethods as $k => $label): ?>
        <option value="<?= $k ?>" <?= old('payment_method', $p['payment_method'] ?? '') === $k ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label small">Reference no</label>
      <input type="text" name="reference_no" class="form-control form-control-sm" value="<?= esc(old('reference_no', $p['reference_no'] ?? '')) ?>">
    </div>
  </div>
  <div class="mt-3 d-flex gap-2">
    <button type="submit" class="btn btn-sm btn-success">Confirm Paid</button>
    <a href="<?= base_url('landlords/' . $l['id']) ?>" class="btn btn-sm btn-outline-secondary">Cancel</a>
  </div>
</div>
<?= form_close() ?>

<?php else: ?>
<?= form_open(base_url('landlords/' . $l['id'] . '/payout')) ?>
<?= csrf_field() ?>
<div class="fm-form-section">
  <div class="row g-2">
    <div class="col-md-6">
      <label class="form-label small">Property *</label>
      <select name="property_id" class="form-select form-select-sm" required>
        <option value="">Select property</option>
        <?php foreach ($properties as $prop): ?>
        <option value="<?= (int) $prop['id'] ?>" <?= (int) old('property_id') === (int) $prop['id'] ? 'selected' : '' ?>><?= esc($prop['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label small">Period from *</label>
      <input type="date" name="period_from" id="periodFrom" class="form-control form-control-sm" required value="<?= esc(old('period_from')) ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label small">Period to *</label>
      <input type="date" name="period_to" id="periodTo" class="form-control form-control-sm" required value="<?= esc(old('period_to')) ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label small">Gross rent *</label>
      <input type="number" step="0.01" min="0.01" name="gross_rent" id="grossRent" class="form-control form-control-sm" required value="<?= esc(old('gross_rent')) ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label small">Commission (<?= number_format($commissionPct, 2) ?>% default)</label>
      <input type="number" step="0.01" min="0" name="commission" id="commission" class="form-control form-control-sm" value="<?= esc(old('commission')) ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label small">Deductions</label>
      <input type="number" step="0.01" min="0" name="deductions" id="deductions" class="form-control form-control-sm" value="<?= esc(old('deductions', '0')) ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label small">Net amount</label>
      <input type="text" id="netAmount" class="form-control form-control-sm" readonly value="0.00">
    </div>
    <div class="col-12">
      <label class="form-label small">Notes</label>
      <textarea name="notes" class="form-control form-control-sm" rows="2"><?= esc(old('notes')) ?></textarea>
    </div>
  </div>
  <div class="mt-3 d-flex gap-2">
    <button type="submit" class="btn btn-sm btn-fm-primary">Create Payout</button>
    <a href="<?= base_url('landlords/' . $l['id']) ?>" class="btn btn-sm btn-outline-secondary">Cancel</a>
  </div>
</div>
<?= form_close() ?>

<script>
(function () {
  const pct = <?= json_encode($commissionPct) ?>;
  const gross = document.getElementById('grossRent');
  const comm = document.getElementById('commission');
  const ded = document.getElementById('deductions');
  const net = document.getElementById('netAmount');

  function recalc() {
    const g = parseFloat(gross.value) || 0;
    let c = parseFloat(comm.value);
    if (isNaN(c) && g > 0) {
      c = Math.round(g * pct / 100 * 100) / 100;
      comm.value = c.toFixed(2);
    }
    c = parseFloat(comm.value) || 0;
    const d = parseFloat(ded.value) || 0;
    net.value = (g - c - d).toFixed(2);
  }

  ['input', 'change'].forEach(evt => {
    gross.addEventListener(evt, recalc);
    comm.addEventListener(evt, recalc);
    ded.addEventListener(evt, recalc);
  });
  recalc();
})();
</script>
<?php endif; ?>

<?= $this->endSection() ?>
