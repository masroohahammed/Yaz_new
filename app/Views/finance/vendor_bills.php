<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= $this->include('finance/_page_header', ['title' => 'Accounts Payable', 'subtitle' => 'Vendor bills — 3-way match required before payment when enabled in Settings', 'backUrl' => 'finance']) ?>
<a href="<?= base_url('procurement') ?>" class="btn btn-fm-outline btn-sm mb-3">Procurement</a>
<?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= session()->getFlashdata('success') ?></div><?php endif; ?>
<?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div><?php endif; ?>
<?php if (empty($bills)): ?>
<div class="alert alert-info">No vendor bills yet. Approve a <strong>Purchase Order</strong> to auto-create an AP bill.</div>
<?php else: ?>
<div class="fm-card"><div class="fm-card-body p-0">
<table class="table table-sm mb-0">
  <thead><tr><th>Bill #</th><th>Vendor</th><th>PO</th><th>Date</th><th class="text-end">Total</th><th>3-Way</th><th>Status</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($bills as $b): ?>
  <tr>
    <td><?= esc($b['bill_number']) ?></td>
    <td><?= esc($b['vendor_name'] ?? '') ?></td>
    <td class="small">
      <?php if (! empty($b['po_id'])): ?>
      <a href="<?= base_url('procurement/order/three-way/'.$b['po_id']) ?>"><?= esc($b['po_number'] ?? '—') ?></a>
      <?php else: ?><?= esc($b['po_number'] ?? '—') ?><?php endif; ?>
    </td>
    <td><?= esc($b['bill_date']) ?></td>
    <td class="text-end"><?= esc($b['currency'] ?? $currency) ?> <?= number_format((float)$b['total'],2) ?></td>
    <td><span class="fm-badge"><?= esc($b['three_way_status'] ?? '—') ?></span></td>
    <td><span class="fm-badge badge-status-<?= esc($b['status']) ?>"><?= esc($b['status']) ?></span></td>
    <td class="text-end">
      <?php if (! empty($b['can_pay'])): ?>
      <?= form_open(base_url('finance/vendor-bills/pay/'.$b['id']), ['class'=>'d-inline']) ?>
      <?= csrf_field() ?>
      <input type="hidden" name="reference_no" value="">
      <button type="submit" class="btn btn-sm btn-fm-primary" onclick="return confirm('Mark bill as paid?')">Pay</button>
      <?= form_close() ?>
      <?php elseif (! empty($b['pay_block_reason']) && $b['status'] !== 'paid'): ?>
      <span class="small text-danger" title="<?= esc($b['pay_block_reason']) ?>">Blocked</span>
      <?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div></div>
<?php endif; ?>
<?= $this->endSection() ?>
