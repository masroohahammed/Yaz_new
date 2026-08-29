<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-boxes me-2 text-primary"></i>Inventory</h1><?php if($lowStock>0): ?><span class="fm-badge badge-priority-high ms-2"><?= $lowStock ?> Low Stock Items</span><?php endif; ?></div>
<div class="d-flex gap-2"><a href="<?= base_url('inventory/create') ?>" class="btn btn-fm-primary btn-sm">Add Item</a><a href="<?= base_url('inventory/movement') ?>" class="btn btn-fm-outline btn-sm">Stock Movements</a></div></div>
<div class="fm-card mb-3"><div class="fm-card-body"><?= form_open(base_url('inventory'),['method'=>'GET','class'=>'d-flex gap-2 align-items-end']) ?>
<div><label class="form-label">Search</label><input type="text" name="search" class="form-control form-control-sm" value="<?= esc($search) ?>" placeholder="Item name or code..."></div>
<button type="submit" class="btn btn-fm-primary btn-sm">Search</button>
<a href="<?= base_url('inventory') ?>" class="btn btn-fm-outline btn-sm">Clear</a>
<?= form_close() ?></div></div>
<div class="fm-card"><div class="fm-card-body p-0"><div class="table-responsive">
<table class="fm-table"><thead><tr><th>Code</th><th>Name</th><th>Category</th><th>Qty</th><th>Min Qty</th><th>Unit Cost</th><th>Total Value</th><th>Location</th><th>Supplier</th><th>Actions</th></tr></thead><tbody>
<?php foreach($items as $i): ?>
<?php $low = $i['quantity'] <= $i['min_quantity']; ?>
<tr class="<?= $low?'sla-warn':'' ?>">
<td class="fw-semibold small"><?= esc($i['item_code']) ?></td>
<td><?= esc($i['name']) ?><?= $low?' <span class="fm-badge badge-priority-high ms-1">Low</span>':'' ?></td>
<td class="small"><?= esc($i['category']??'—') ?></td>
<td class="fw-bold <?= $low?'text-danger':'' ?>"><?= $i['quantity'] ?> <?= esc($i['unit']) ?></td>
<td class="small text-muted"><?= $i['min_quantity'] ?></td>
<td class="small"><?= $currency ?> <?= number_format($i['unit_cost'],2) ?></td>
<td class="small fw-semibold"><?= $currency ?> <?= number_format($i['quantity']*$i['unit_cost'],2) ?></td>
<td class="small text-muted"><?= esc($i['location']??'—') ?></td>
<td class="small text-muted"><?= esc($i['supplier']??'—') ?></td>
<td><div class="d-flex gap-1"><a href="<?= base_url('inventory/edit/'.$i['id']) ?>" class="btn-action bg-warning bg-opacity-10 text-warning"><i class="bi bi-pencil"></i></a></div></td>
</tr><?php endforeach; ?>
<?php if(empty($items)): ?><tr><td colspan="10" class="text-center py-4 text-muted">No items found</td></tr><?php endif; ?>
</tbody></table></div></div></div>
<?= $this->endSection() ?>
