<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
helper('fm');
$paidTotal = (float) ($paidTotal ?? $payment['amount_paid'] ?? 0);
$balance = (float) ($balance ?? max(0, round((float) ($payment['amount'] ?? 0) - $paidTotal, 2)));
$st = $statusBadge ?? [ucfirst($payment['status'] ?? 'pending'), 'secondary'];
$history = $paymentHistory ?? [];
?>
<div class="page-header">
  <div>
    <h1>Rent Invoice <?= esc($payment['payment_number'] ?? '') ?></h1>
    <div class="small text-muted"><?= esc($payment['tenant_name'] ?? '') ?> · <?= esc($payment['facility_name'] ?? '') ?> · <?= esc($payment['contract_number'] ?? '') ?></div>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('payments') ?>" class="btn btn-fm-outline btn-sm">Back</a>
    <a href="<?= base_url('payments/'.$payment['id'].'/edit') ?>" class="btn btn-fm-primary btn-sm">Edit</a>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-3"><div class="fm-card p-3"><div class="small text-muted">Invoice amount</div><div class="fs-5 fw-bold"><?= number_format((float) ($payment['amount'] ?? 0), 2) ?> <?= esc($currency) ?></div></div></div>
  <div class="col-md-3"><div class="fm-card p-3"><div class="small text-muted">Paid</div><div class="fs-5 fw-bold text-success"><?= number_format($paidTotal, 2) ?> <?= esc($currency) ?></div></div></div>
  <div class="col-md-3"><div class="fm-card p-3"><div class="small text-muted">Remaining</div><div class="fs-5 fw-bold text-danger"><?= number_format($balance, 2) ?> <?= esc($currency) ?></div></div></div>
  <div class="col-md-3"><div class="fm-card p-3"><div class="small text-muted">Status</div><div><span class="badge bg-<?= $st[1] ?>"><?= esc($st[0]) ?></span></div></div></div>
</div>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="fm-card">
      <div class="fm-card-body">
        <table class="table table-sm mb-0">
          <tr><th>Contract</th><td><a href="<?= base_url('contracts/'.($payment['contract_id']??'')) ?>"><?= esc($payment['contract_number'] ?? '—') ?></a></td></tr>
          <tr><th>Unit</th><td><?= esc($payment['unit_number'] ?? '—') ?></td></tr>
          <tr><th>Type</th><td><?= esc($payment['payment_type'] ?? '') ?></td></tr>
          <tr><th>Method</th><td><?= esc($payment['payment_method'] ?? '') ?></td></tr>
          <tr><th>Due date</th><td><?= esc($payment['due_date'] ?? '—') ?><?php if (!empty($payment['original_due_date']) && $payment['original_due_date'] !== $payment['due_date']): ?> <span class="text-muted small">(was <?= esc($payment['original_due_date']) ?>)</span><?php endif; ?></td></tr>
          <tr><th>Payment date</th><td><?= esc($payment['payment_date'] ?? '—') ?></td></tr>
          <tr><th>Period</th><td><?= esc(($payment['period_from'] ?? '') . ' – ' . ($payment['period_to'] ?? '')) ?></td></tr>
          <?php if (!empty($payment['cheque_no'])): ?><tr><th>Cheque no</th><td><?= esc($payment['cheque_no']) ?></td></tr><?php endif; ?>
        </table>
      </div>
    </div>

    <?php if (!empty($partials)): ?>
    <div class="fm-card mt-3">
      <div class="card-header-fm"><h5 class="mb-0">Partial payments</h5></div>
      <div class="fm-card-body p-0">
        <table class="table table-sm mb-0">
          <thead><tr><th>Date</th><th>Amount</th><th>Method</th><th>Notes</th></tr></thead>
          <tbody>
          <?php foreach ($partials as $p): ?>
            <tr>
              <td><?= esc($p['paid_date'] ?? '') ?></td>
              <td><?= number_format((float) ($p['amount'] ?? 0), 2) ?></td>
              <td><?= esc($p['method'] ?? '') ?></td>
              <td class="small"><?= esc($p['notes'] ?? '') ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($history)): ?>
    <div class="fm-card mt-3">
      <div class="card-header-fm"><h5 class="mb-0">Payment history</h5></div>
      <div class="fm-card-body p-0">
        <table class="table table-sm mb-0">
          <thead><tr><th>When</th><th>From</th><th>To</th><th>Amount</th><th>Notes</th></tr></thead>
          <tbody>
          <?php foreach ($history as $h): ?>
            <tr>
              <td class="small"><?= esc($h['created_at'] ?? '') ?></td>
              <td><?= esc($h['from_status'] ?? '—') ?></td>
              <td><?= esc($h['to_status'] ?? '') ?></td>
              <td><?= isset($h['amount']) ? number_format((float) $h['amount'], 2) : '—' ?></td>
              <td class="small"><?= esc($h['notes'] ?? '') ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <div class="col-lg-5">
    <?php if ($balance > 0 && !in_array($payment['status'], ['paid', 'cancelled'], true)): ?>
    <div class="fm-card mb-3">
      <div class="card-header-fm"><h5 class="mb-0">Partial payment</h5></div>
      <div class="fm-card-body">
        <?= form_open(base_url('payments/'.$payment['id'].'/partial')) ?>
        <?= csrf_field() ?>
        <div class="mb-2"><label class="form-label small">Amount</label><input type="number" step="0.01" max="<?= $balance ?>" name="partial_amount" class="form-control form-control-sm" required></div>
        <div class="mb-2"><label class="form-label small">Date</label><input type="date" name="paid_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>"></div>
        <div class="mb-2"><label class="form-label small">Method</label>
          <select name="method" class="form-select form-select-sm">
            <?php foreach (fm_payment_methods('lease') as $m => $ml): ?>
            <option value="<?= $m ?>"><?= esc($ml) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2"><label class="form-label small">Notes</label><input name="notes" class="form-control form-control-sm"></div>
        <button class="btn btn-sm btn-fm-primary w-100">Record partial payment</button>
        <?= form_close() ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!in_array($payment['status'], ['paid', 'cancelled'], true)): ?>
    <div class="fm-card mb-3">
      <div class="card-header-fm"><h5 class="mb-0">Postpone payment</h5></div>
      <div class="fm-card-body">
        <?= form_open(base_url('payments/'.$payment['id'].'/postpone')) ?>
        <?= csrf_field() ?>
        <div class="mb-2"><label class="form-label small">New due date</label><input type="date" name="postponed_to" class="form-control form-control-sm" required></div>
        <div class="mb-2"><label class="form-label small">Reason</label><textarea name="postpone_note" class="form-control form-control-sm" rows="2" required></textarea></div>
        <button class="btn btn-sm btn-warning w-100">Postpone</button>
        <?= form_close() ?>
      </div>
    </div>

    <div class="fm-card mb-3">
      <div class="card-header-fm"><h5 class="mb-0">Collect full payment</h5></div>
      <div class="fm-card-body">
        <?= form_open(base_url('payments/'.$payment['id'].'/collect')) ?>
        <?= csrf_field() ?>
        <div class="mb-2"><label class="form-label small">Amount</label><input type="number" step="0.01" name="amount" class="form-control form-control-sm" value="<?= esc($balance > 0 ? $balance : $payment['amount']) ?>"></div>
        <div class="mb-2"><label class="form-label small">Payment date</label><input type="date" name="payment_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>"></div>
        <button class="btn btn-sm btn-success w-100">Mark as paid</button>
        <?= form_close() ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if (($payment['payment_method'] ?? '') === 'cheque' || !empty($payment['cheque_no'])): ?>
    <div class="fm-card">
      <div class="card-header-fm"><h5 class="mb-0">Cheque</h5></div>
      <div class="fm-card-body small">
        <p class="mb-1">Cheque #<?= esc($payment['cheque_no'] ?? '—') ?></p>
        <p class="text-muted mb-0">Manage status in <a href="<?= base_url('cheques') ?>">Cheque Tracking</a>.</p>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
<?= $this->endSection() ?>
