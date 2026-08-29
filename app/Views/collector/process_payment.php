<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1>Process Payment — <?= esc($tenant['full_name']) ?></h1></div>

<?php if (empty($openSession)): ?>
<div class="alert alert-warning">Start a <a href="<?= base_url('collector/session') ?>">collection session</a> before taking payments.</div>
<?php endif; ?>

<?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
<?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="fm-card p-3">
      <h6 class="mb-3">Outstanding invoices</h6>
      <?php if (empty($invoices)): ?>
      <p class="text-muted small">No outstanding invoices.</p>
      <?php else: ?>
      <table class="table table-sm">
        <thead><tr><th>Due</th><th>Amount</th><th>Property</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($invoices as $inv): ?>
        <tr>
          <td><?= esc($inv['due_date'] ?? '') ?></td>
          <td><?= number_format((float)$inv['amount'], 2) ?></td>
          <td class="small"><?= esc($inv['property_name'] ?? '') ?></td>
          <td><a href="<?= base_url('collector/collect/' . $inv['id']) ?>" class="btn btn-sm btn-outline-primary">Collect</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-5">
    <?php if (! empty($invoices) && ! empty($openSession)): ?>
    <div class="fm-form-section">
      <h6>Quick collect (first invoice)</h6>
      <?= form_open(base_url('collector/process-payment/' . $tenant['id'])) ?>
      <?= csrf_field() ?>
      <input type="hidden" name="payment_id" value="<?= (int) $invoices[0]['id'] ?>">
      <div class="mb-2"><label class="form-label small">Amount *</label>
        <input type="number" step="0.01" name="collected_amount" class="form-control form-control-sm" required value="<?= esc($invoices[0]['amount']) ?>"></div>
      <div class="mb-2"><label class="form-label small">Method *</label>
        <select name="payment_method" class="form-select form-select-sm" required id="payMethod">
          <option value="cash">Cash</option><option value="cheque">Cheque</option><option value="transfer">Transfer</option>
        </select></div>
      <div class="mb-2"><label class="form-label small">Notes</label><input type="text" name="notes" class="form-control form-control-sm"></div>
      <button type="submit" class="btn btn-fm-primary btn-sm">Confirm Collection</button>
      <?= form_close() ?>
    </div>
    <?php endif; ?>
  </div>
</div>
<?= $this->endSection() ?>
