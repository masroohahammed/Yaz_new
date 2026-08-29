<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div>
    <h1>Payment <?= esc($payment['payment_number'] ?? '') ?></h1>
    <div class="small text-muted"><?= esc($payment['tenant_name'] ?? '') ?> · <?= esc($payment['facility_name'] ?? '') ?></div>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('payments') ?>" class="btn btn-fm-outline btn-sm">Back</a>
    <a href="<?= base_url('payments/'.$payment['id'].'/edit') ?>" class="btn btn-fm-primary btn-sm">Edit</a>
  </div>
</div>

<div class="row g-3">
  <div class="col-md-6">
    <div class="fm-card">
      <div class="fm-card-body">
        <table class="table table-sm mb-0">
          <tr><th>Contract</th><td><?= esc($payment['contract_number'] ?? '—') ?></td></tr>
          <tr><th>Unit</th><td><?= esc($payment['unit_number'] ?? '—') ?></td></tr>
          <tr><th>Type</th><td><?= esc($payment['payment_type'] ?? '') ?></td></tr>
          <tr><th>Method</th><td><?= esc($payment['payment_method'] ?? '') ?></td></tr>
          <tr><th>Amount</th><td><?= number_format((float) ($payment['amount'] ?? 0), 2) ?></td></tr>
          <tr><th>Status</th><td><span class="badge bg-secondary"><?= esc($payment['status'] ?? '') ?></span></td></tr>
          <tr><th>Due date</th><td><?= esc($payment['due_date'] ?? '—') ?></td></tr>
          <tr><th>Payment date</th><td><?= esc($payment['payment_date'] ?? '—') ?></td></tr>
          <tr><th>Period</th><td><?= esc(($payment['period_from'] ?? '') . ' – ' . ($payment['period_to'] ?? '')) ?></td></tr>
        </table>
      </div>
    </div>
  </div>
  <?php if (!empty($partials)): ?>
  <div class="col-md-6">
    <div class="fm-card">
      <div class="card-header-fm"><h5 class="mb-0">Partial payments</h5></div>
      <div class="fm-card-body p-0">
        <table class="table table-sm mb-0">
          <thead><tr><th>Date</th><th>Amount</th><th>Method</th></tr></thead>
          <tbody>
          <?php foreach ($partials as $p): ?>
            <tr>
              <td><?= esc($p['paid_date'] ?? '') ?></td>
              <td><?= number_format((float) ($p['amount'] ?? 0), 2) ?></td>
              <td><?= esc($p['method'] ?? '') ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>
<?= $this->endSection() ?>
