<?php
/**
 * Record payment for an invoice (partial or full).
 *
 * Pass: invoiceId (required), balanceDue, currency, formAction (optional)
 */
$invoiceId  = (int) ($invoiceId ?? ($inv['id'] ?? 0));
$balanceDue = (float) ($balanceDue ?? ($inv['total'] ?? 0));
$currency   = (string) ($currency ?? 'QAR');
$action     = $formAction ?? base_url('finance/payments/record/' . $invoiceId);
$redirectTo = $redirectTo ?? current_url();

if ($invoiceId < 1): ?>
<p class="text-danger small mb-0">Cannot load payment form — invoice not found.</p>
<?php return; endif; ?>
<?= form_open($action, ['class' => 'fm-record-payment-form fm-submit-form', 'data-no-loader' => '']) ?>
<?= csrf_field() ?>
<input type="hidden" name="redirect_to" value="<?= esc($redirectTo) ?>">
<div class="mb-2">
  <label class="form-label small mb-1">Amount (<?= esc($currency) ?>)</label>
  <input type="number" name="amount" class="form-control" step="0.01" min="0.01" max="<?= $balanceDue ?>"
         value="<?= number_format($balanceDue, 2, '.', '') ?>" required>
  <div class="form-text">Balance due: <strong><?= esc($currency) ?> <?= number_format($balanceDue, 2) ?></strong></div>
</div>
<div class="row g-2 mb-2">
  <div class="col-6">
    <label class="form-label small mb-1">Method</label>
    <select name="payment_method" class="form-select" required>
      <option value="bank">Bank</option>
      <option value="cash">Cash</option>
      <option value="card">Card</option>
      <option value="cheque">Cheque</option>
      <option value="online">Online</option>
    </select>
  </div>
  <div class="col-6">
    <label class="form-label small mb-1">Reference</label>
    <input type="text" name="reference_no" class="form-control" maxlength="80" placeholder="Txn ref">
  </div>
</div>
<div class="mb-2">
  <label class="form-label small mb-1">Notes</label>
  <input type="text" name="notes" class="form-control" maxlength="255" placeholder="Optional">
</div>
<button type="submit" class="btn btn-success w-100 fm-submit-btn">
  <i class="bi bi-cash-coin me-1"></i>Record Payment
</button>
<?= form_close() ?>
