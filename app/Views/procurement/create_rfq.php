<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-envelope-plus me-2"></i>New Request for Quotation</h1></div></div>
<?= form_open(base_url('procurement/rfq/store')) ?>
<div class="row g-3">
  <div class="col-lg-8">
    <div class="fm-form-section">
      <h6><i class="bi bi-info-circle"></i>RFQ Details</h6>
      <div class="mb-3"><label class="form-label">Title <span class="text-danger">*</span></label><input type="text" name="title" class="form-control" required></div>
      <div class="mb-3"><label class="form-label">Description / Items Required</label><textarea name="description" class="form-control" rows="4"></textarea></div>
      <div class="mb-0"><label class="form-label">Response Deadline <span class="text-danger">*</span></label><input type="date" name="deadline" class="form-control" required min="<?= date('Y-m-d') ?>"></div>
    </div>
    <div class="fm-form-section">
      <h6><i class="bi bi-link"></i>Link Approved Requests</h6>
      <?php foreach($requests as $req): ?><div class="form-check mb-2"><input type="checkbox" name="request_ids[]" value="<?= $req['id'] ?>" class="form-check-input"><label class="form-check-label small"><?= esc($req['item_name']??'Unknown') ?> — Qty: <?= $req['quantity'] ?></label></div><?php endforeach; ?>
      <?php if(empty($requests)): ?><p class="small text-muted">No approved purchase requests.</p><?php endif; ?>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="fm-form-section">
      <h6><i class="bi bi-truck"></i>Send to Vendors</h6>
      <?php foreach($vendors as $v): ?><div class="form-check mb-2"><input type="checkbox" name="vendor_ids[]" value="<?= $v['id'] ?>" class="form-check-input"><label class="form-check-label small"><?= esc($v['name']) ?><?php if($v['rating']): ?> <span class="x-small text-warning">★ <?= $v['rating'] ?></span><?php endif; ?></label></div><?php endforeach; ?>
      <?php if(empty($vendors)): ?><p class="small text-muted">No active vendors. <a href="<?= base_url('vendors/create') ?>">Add one</a>.</p><?php endif; ?>
    </div>
    <div class="d-grid gap-2">
      <button type="submit" class="btn btn-fm-primary"><i class="bi bi-send me-2"></i>Create & Send RFQ</button>
      <a href="<?= base_url('procurement') ?>" class="btn btn-fm-outline">Cancel</a>
    </div>
  </div>
</div>
<?= form_close() ?>
<?= $this->endSection() ?>
