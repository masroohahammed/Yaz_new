<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $l = $landlord; ?>

<div class="page-header">
  <div>
    <h1>Payouts — <?= esc($l['full_name']) ?></h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb small">
      <li class="breadcrumb-item"><a href="<?= base_url('landlords') ?>">Landlords</a></li>
      <li class="breadcrumb-item"><a href="<?= base_url('landlords/' . $l['id']) ?>"><?= esc($l['full_name']) ?></a></li>
      <li class="breadcrumb-item active">Payouts</li>
    </ol></nav>
  </div>
  <a href="<?= base_url('landlords/' . $l['id'] . '/payout') ?>" class="btn btn-sm btn-fm-primary">Create Payout</a>
</div>

<div class="fm-card p-0">
  <table class="table table-registry table-hover mb-0">
    <thead class="table-light">
      <tr>
        <th>Period</th>
        <th>Property</th>
        <th>Gross</th>
        <th>Commission</th>
        <th>Deductions</th>
        <th>Net</th>
        <th>Status</th>
        <th>Paid</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($payouts)): ?>
      <tr><td colspan="9" class="text-center text-muted py-4">No payouts.</td></tr>
      <?php else: ?>
      <?php foreach ($payouts as $po): ?>
      <tr>
        <td class="small"><?= esc($po['period_from'] ?? '') ?> — <?= esc($po['period_to'] ?? '') ?></td>
        <td class="small"><?= esc($po['property_name'] ?? '—') ?></td>
        <td><?= number_format((float) ($po['gross_rent'] ?? 0), 2) ?></td>
        <td><?= number_format((float) ($po['commission'] ?? 0), 2) ?></td>
        <td><?= number_format((float) ($po['deductions'] ?? 0), 2) ?></td>
        <td><?= number_format((float) ($po['net_amount'] ?? 0), 2) ?></td>
        <td><span class="fm-badge badge-status-<?= esc($po['status']) ?>"><?= ucfirst($po['status']) ?></span></td>
        <td class="small"><?= esc($po['paid_date'] ?? '—') ?></td>
        <td>
          <?php if (($po['status'] ?? '') !== 'paid'): ?>
          <?= form_open(base_url('landlords/payouts/' . $po['id'] . '/mark-paid'), ['class' => 'd-inline', 'onsubmit' => 'return confirm("Mark this payout as paid?");']) ?>
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-sm btn-outline-success">Confirm Paid</button>
          <?= form_close() ?>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?= $this->endSection() ?>
