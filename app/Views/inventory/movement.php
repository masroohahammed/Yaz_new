<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-arrow-left-right me-2 text-primary"></i>Stock Movements</h1></div><a href="<?= base_url('inventory') ?>" class="btn btn-fm-outline btn-sm">Back to Inventory</a></div>
<div class="row g-3">
<div class="col-lg-4">
<div class="fm-form-section"><h6><i class="bi bi-plus-circle"></i>Record Movement</h6>
<?= form_open(base_url('inventory/addMovement')) ?>
<div class="mb-3"><label class="form-label">Item *</label><select name="item_id" class="form-select" required><option value="">— Select Item —</option><?php foreach($items as $i): ?><option value="<?= $i['id'] ?>"><?= esc($i['name']) ?> (<?= $i['quantity'] ?> <?= esc($i['unit']) ?>)</option><?php endforeach; ?></select></div>
<div class="mb-3"><label class="form-label">Type *</label><select name="movement_type" class="form-select" required><option value="in">Stock In</option><option value="out">Stock Out</option><option value="adjustment">Adjustment (set to)</option></select></div>
<div class="mb-3"><label class="form-label">Quantity *</label><input type="number" name="quantity" class="form-control" min="1" required></div>
<div class="mb-3"><label class="form-label">Reference</label><input type="text" name="reference" class="form-control" placeholder="PO-001, WO-001..."></div>
<div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
<button type="submit" class="btn btn-fm-primary w-100">Record Movement</button>
<?= form_close() ?>
</div></div>
<div class="col-lg-8">
<div class="fm-card"><div class="card-header-fm"><h5>Recent Movements</h5></div>
<div class="fm-card-body p-0"><table class="fm-table"><thead><tr><th>Date</th><th>Item</th><th>Type</th><th>Qty</th><th>Reference</th><th>By</th></tr></thead><tbody>
<?php foreach($movements as $m): ?><tr>
<td class="small"><?= date('d M Y H:i',strtotime($m['created_at'])) ?></td>
<td><?= esc($m['item_name']) ?></td>
<td><?php $tc=$m['movement_type']==='in'?'success':($m['movement_type']==='out'?'danger':'warning'); ?><span class="fm-badge" style="background:rgba(<?= $m['movement_type']==='in'?'39,174,96':($m['movement_type']==='out'?'231,76,60':'243,156,18') ?>,.1);color:<?= $m['movement_type']==='in'?'#27AE60':($m['movement_type']==='out'?'#E74C3C':'#F39C12') ?>;border:1px solid currentColor"><?= ucfirst($m['movement_type']) ?></span></td>
<td class="fw-bold"><?= $m['movement_type']==='out'?'-':'+' ?><?= $m['quantity'] ?></td>
<td class="small text-muted"><?= esc($m['reference']??'—') ?></td>
<td class="small"><?= esc($m['created_by_name']??'—') ?></td>
</tr><?php endforeach; ?>
<?php if(empty($movements)): ?><tr><td colspan="6" class="text-center py-4 text-muted">No movements recorded</td></tr><?php endif; ?>
</tbody></table></div></div></div></div>
<?= $this->endSection() ?>
