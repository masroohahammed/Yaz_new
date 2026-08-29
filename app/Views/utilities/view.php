<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $a = $account; ?>
<div class="page-header d-flex justify-content-between">
  <h1><?= esc($a['utility_name']) ?></h1>
  <div class="d-flex gap-2">
    <a href="<?= base_url('utilities/'.$a['id'].'/transfer-to-tenant') ?>" class="btn btn-sm btn-outline-primary">Transfer to Tenant</a>
    <a href="<?= base_url('utilities/'.$a['id'].'/transfer-back') ?>" class="btn btn-sm btn-outline-secondary">Transfer Back</a>
    <a href="<?= base_url('utilities/'.$a['id'].'/bills') ?>" class="btn btn-sm btn-fm-primary">Add Bill</a>
  </div>
</div>
<p class="small">Billing: <?= esc($a['billing_mode']) ?> · Paid by: <?= esc($a['paid_by'] ?? 'company') ?></p>
<table class="table table-sm"><thead><tr><th>Bill date</th><th>Amount</th><th>Due</th><th>Status</th><th></th></tr></thead>
<tbody><?php foreach ($bills as $b): ?><tr>
  <td><?= esc($b['bill_date']) ?></td><td><?= number_format((float)$b['amount'],2) ?></td>
  <td><?= esc($b['due_date']) ?></td><td><?= esc($b['status']) ?></td>
  <td><?php if ($b['status']!=='paid'): ?><a href="<?= base_url('utilities/bills/'.$b['id'].'/pay') ?>" class="btn btn-sm btn-outline-success">Pay Bill</a><?php endif; ?></td>
</tr><?php endforeach; ?></tbody></table>
<?= $this->endSection() ?>
