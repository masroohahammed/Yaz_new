<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-file-earmark-plus me-2"></i>Create Purchase Order</h1></div><a href="<?= base_url('procurement') ?>" class="btn btn-fm-outline btn-sm">← Back</a></div>
<div class="row"><div class="col-lg-8"><div class="fm-card"><div class="fm-card-body">
<?= form_open('procurement/order/store') ?>
<div class="row g-3">
  <div class="col-md-6"><label class="form-label">Vendor *</label><select name="vendor_id" class="form-select" required><option value="">Select vendor...</option><?php foreach($vendors as $v): ?><option value="<?= $v['id'] ?>"><?= esc($v['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-6"><label class="form-label">Required Delivery Date *</label><input type="date" name="delivery_date" class="form-control" required></div>
  <div class="col-md-6"><label class="form-label">Total Amount (<?= $currency ?>) *</label><input type="number" name="total_amount" class="form-control" step="0.01" placeholder="0.00" required></div>
  <?php if(!empty($requests)): ?>
  <div class="col-12"><label class="form-label">Link Approved Requests</label>
    <?php foreach($requests as $r): ?>
    <div class="form-check"><input class="form-check-input" type="checkbox" name="request_ids[]" value="<?= $r['id'] ?>"><label class="form-check-label small"><?= esc($r['item_name']) ?> × <?= $r['quantity'] ?></label></div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
  <div class="col-12"><button type="submit" class="btn btn-fm-primary">Create Purchase Order</button></div>
</div>
<?= form_close() ?>
</div></div></div></div>
<?= $this->endSection() ?>
