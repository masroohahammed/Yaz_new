<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-cart me-2 text-primary"></i>Procurement</h1></div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('procurement/request/create') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-plus-lg me-1"></i>Purchase Request</a>
    <a href="<?= base_url('procurement/order/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-file-earmark-plus me-1"></i>Purchase Order</a>
  </div>
</div>
<div class="row g-3">
<div class="col-12"><div class="fm-card"><div class="card-header-fm"><h5><i class="bi bi-list-check me-2"></i>Purchase Requests</h5></div>
<div class="fm-card-body p-0"><div class="table-responsive">
<table class="fm-table"><thead><tr><th>Item</th><th>Qty</th><th>Priority</th><th>Requested By</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($requests as $r): ?>
<tr>
  <td class="fw-semibold small"><?= esc($r['item_name']??'—') ?></td>
  <td class="small"><?= $r['quantity'] ?></td>
  <td><span class="fm-badge badge-priority-<?= $r['priority'] ?>"><?= ucfirst($r['priority']) ?></span></td>
  <td class="small"><?= esc($r['requested_by_name']??'—') ?></td>
  <td><span class="fm-badge badge-status-<?= $r['status']==='approved'?'completed':($r['status']==='ordered'?'in_progress':'pending') ?>"><?= ucfirst($r['status']) ?></span></td>
  <td>
    <?php if($r['status']==='pending' && in_array(session()->get('user_role'),['super_admin','facility_manager'])): ?>
    <a href="<?= base_url('procurement/request/approve/'.$r['id']) ?>" class="btn-action bg-success bg-opacity-10 text-success" onclick="return confirm('Approve this request?')"><i class="bi bi-check-lg"></i></a>
    <?php endif; ?>
  </td>
</tr>
<?php endforeach; ?>
<?php if(empty($requests)): ?><tr><td colspan="6" class="text-center py-4 text-muted">No purchase requests</td></tr><?php endif; ?>
</tbody></table></div></div></div></div>
<div class="col-12"><div class="fm-card"><div class="card-header-fm"><h5><i class="bi bi-file-earmark-text me-2"></i>Purchase Orders</h5></div>
<div class="fm-card-body p-0"><div class="table-responsive">
<table class="fm-table"><thead><tr><th>PO Number</th><th>Vendor</th><th>Amount</th><th>Delivery Date</th><th>Status</th></tr></thead><tbody>
<?php foreach($orders as $o): ?>
<tr>
  <td class="fw-semibold small"><?= esc($o['po_number']) ?></td>
  <td class="small"><?= esc($o['vendor_name']??'—') ?></td>
  <td class="fw-semibold"><?= $currency ?> <?= number_format($o['total_amount'],2) ?></td>
  <td class="small"><?= $o['delivery_date'] ? date('d M Y',strtotime($o['delivery_date'])) : '—' ?></td>
  <td><span class="fm-badge badge-status-<?= $o['status']==='received'?'completed':($o['status']==='pending'?'new':'assigned') ?>"><?= ucfirst($o['status']) ?></span></td>
</tr>
<?php endforeach; ?>
<?php if(empty($orders)): ?><tr><td colspan="5" class="text-center py-4 text-muted">No purchase orders yet</td></tr><?php endif; ?>
</tbody></table></div></div></div></div>
</div>
<?= $this->endSection() ?>
