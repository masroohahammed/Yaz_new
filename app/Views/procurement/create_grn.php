<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-truck me-2"></i>Goods Receipt — <?= esc($po['po_number']) ?></h1></div></div>
<?= form_open(base_url('procurement/grn/store')) ?>
<?= form_hidden('po_id',$po['id']) ?>
<div class="fm-form-section mb-3">
  <h6><i class="bi bi-info-circle"></i>PO Info</h6>
  <div class="row g-2">
    <div class="col-md-4 small"><span class="text-muted">Vendor:</span> <strong><?= esc($po['vendor_name']??'—') ?></strong></div>
    <div class="col-md-4 small"><span class="text-muted">PO #:</span> <?= esc($po['po_number']) ?></div>
    <div class="col-md-4"><label class="form-label small">Received Date <span class="text-danger">*</span></label><input type="date" name="received_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>" required></div>
  </div>
</div>
<div class="fm-card mb-3">
  <div class="card-header-fm"><h5>Items to Receive</h5></div>
  <div class="fm-card-body p-0">
    <table class="fm-table">
      <thead><tr><th>Item</th><th>Ordered Qty</th><th>Unit</th><th>Received Qty</th></tr></thead>
      <tbody>
      <?php foreach($lineItems as $i=>$li): ?>
      <tr>
        <?= form_hidden("item_ids[]",$li['item_id']) ?>
        <?= form_hidden("pr_ids[]",$li['id']) ?>
        <td class="small fw-semibold"><?= esc($li['item_name']??'—') ?></td>
        <td class="small text-center"><?= $li['quantity'] ?></td>
        <td class="small text-muted"><?= esc($li['unit']??'') ?></td>
        <td><input type="number" name="received_qty[]" class="form-control form-control-sm" value="<?= $li['quantity'] ?>" min="0" max="<?= $li['quantity'] ?>"></td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($lineItems)): ?><tr><td colspan="4" class="text-center py-3 text-muted">No items linked.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<div class="fm-form-section mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2" placeholder="Delivery notes, condition, partial delivery reason..."></textarea></div>
<div class="d-flex gap-2">
  <button type="submit" class="btn btn-fm-primary"><i class="bi bi-check-lg me-2"></i>Record GRN & Update Stock</button>
  <a href="<?= base_url('procurement') ?>" class="btn btn-fm-outline">Cancel</a>
</div>
<?= form_close() ?>
<?= $this->endSection() ?>
