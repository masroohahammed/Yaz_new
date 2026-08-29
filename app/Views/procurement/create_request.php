<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-cart-plus me-2"></i>Purchase Request</h1></div><a href="<?= base_url('procurement') ?>" class="btn btn-fm-outline btn-sm">← Back</a></div>
<div class="row"><div class="col-lg-6"><div class="fm-card"><div class="fm-card-body">
<?= form_open('procurement/request/store') ?>
<div class="row g-3">
  <div class="col-12"><label class="form-label">Item *</label><select name="item_id" class="form-select" required><option value="">Select inventory item...</option><?php foreach($items as $i): ?><option value="<?= $i['id'] ?>"><?= esc($i['name']) ?> (Stock: <?= $i['quantity'] ?>)</option><?php endforeach; ?></select></div>
  <div class="col-md-6"><label class="form-label">Quantity *</label><input type="number" name="quantity" class="form-control" min="1" required></div>
  <div class="col-md-6"><label class="form-label">Priority</label><select name="priority" class="form-select"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option><option value="critical">Critical</option></select></div>
  <div class="col-12"><label class="form-label">Reason *</label><textarea name="reason" class="form-control" rows="3" required placeholder="Why is this item needed?"></textarea></div>
  <div class="col-12"><button type="submit" class="btn btn-fm-primary">Submit Request</button></div>
</div>
<?= form_close() ?>
</div></div></div></div>
<?= $this->endSection() ?>
