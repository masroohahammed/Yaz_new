<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1>Cash Acknowledgement</h1><p class="small text-muted">Reconcile field collections into the finance ledger.</p></div>
<?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>

<?= form_open(base_url('finance/pm/cash-acknowledge')) ?>
<?= csrf_field() ?>
<div class="row g-2 mb-3">
  <div class="col-md-3"><label class="form-label small">Deposit date *</label><input type="date" name="deposit_date" class="form-control form-control-sm" required value="<?= date('Y-m-d') ?>"></div>
  <div class="col-md-3"><label class="form-label small">Deposit ref</label><input type="text" name="deposit_ref" class="form-control form-control-sm"></div>
  <div class="col-md-4"><label class="form-label small">Notes</label><input type="text" name="notes" class="form-control form-control-sm"></div>
  <div class="col-md-2 align-self-end"><button type="submit" class="btn btn-sm btn-success w-100">Bulk Acknowledge Selected</button></div>
</div>
<table class="table table-sm">
  <thead><tr><th></th><th>Date</th><th>Tenant</th><th>Property</th><th>Method</th><th class="text-end">Amount</th><th>Collector</th></tr></thead>
  <tbody>
  <?php foreach ($pending as $p): ?>
  <tr>
    <td><input type="checkbox" name="collection_ids[]" value="<?= (int)$p['id'] ?>"></td>
    <td><?= esc($p['payment_date'] ?? '') ?></td>
    <td><?= esc($p['tenant_name'] ?? '') ?></td>
    <td class="small"><?= esc($p['property_name'] ?? '') ?></td>
    <td><?= esc($p['payment_method'] ?? '') ?></td>
    <td class="text-end"><?= number_format((float)$p['amount'], 2) ?></td>
    <td class="small"><?= esc($p['collector_name'] ?? '') ?></td>
  </tr>
  <?php endforeach; ?>
  <?php if (empty($pending)): ?><tr><td colspan="7" class="text-center text-muted py-3">No pending collections.</td></tr><?php endif; ?>
  </tbody>
</table>
<?= form_close() ?>
<?= $this->endSection() ?>
