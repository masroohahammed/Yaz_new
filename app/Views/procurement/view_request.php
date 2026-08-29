<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-clipboard-plus me-2"></i>Purchase Request</h1><span class="fm-badge badge-status-<?= esc($req['status']) ?>"><?= ucfirst($req['status']) ?></span></div>
  <div class="d-flex gap-2">
    <?php if($req['status']==='pending' && in_array(session()->get('user_role'),['super_admin','facility_manager','procurement_officer'])): ?>
    <?= form_open(base_url('procurement/request/approve/'.$req['id'])) ?>
    <button type="submit" class="btn btn-fm-primary btn-sm" onclick="return confirm('Approve?')"><i class="bi bi-check me-1"></i>Approve</button>
    <?= form_close() ?>
    <?= form_open(base_url('procurement/request/reject/'.$req['id'])) ?>
    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Reject?')"><i class="bi bi-x me-1"></i>Reject</button>
    <?= form_close() ?>
    <?php endif; ?>
    <a href="<?= base_url('procurement') ?>" class="btn btn-fm-outline btn-sm">Back</a>
  </div>
</div>
<div class="row g-3">
  <div class="col-md-6"><div class="fm-form-section">
    <h6><i class="bi bi-info-circle"></i>Request Details</h6>
    <div class="small mb-2"><span class="text-muted">Item:</span> <strong><?= esc($req['item_name']??'—') ?></strong> (<?= esc($req['item_code']??'') ?>)</div>
    <div class="small mb-2"><span class="text-muted">Quantity:</span> <strong><?= $req['quantity'] ?> <?= esc($req['unit']??'') ?></strong></div>
    <div class="small mb-2"><span class="text-muted">Current Stock:</span> <?= $req['stock_qty']??0 ?> <?= esc($req['unit']??'') ?></div>
    <div class="small mb-2"><span class="text-muted">Priority:</span> <span class="fm-badge badge-priority-<?= esc($req['priority']??'medium') ?>"><?= ucfirst($req['priority']??'medium') ?></span></div>
    <div class="small mb-2"><span class="text-muted">Reason:</span> <div class="mt-1 p-2 bg-light rounded"><?= esc($req['reason']??'—') ?></div></div>
  </div></div>
  <div class="col-md-6"><div class="fm-form-section">
    <h6><i class="bi bi-person"></i>Request Info</h6>
    <div class="small mb-2"><span class="text-muted">Requested by:</span> <strong><?= esc($req['requested_by_name']??'—') ?></strong></div>
    <div class="small mb-2"><span class="text-muted">Date:</span> <?= date('d M Y H:i',strtotime($req['created_at'])) ?></div>
    <div class="small mb-2"><span class="text-muted">Status:</span> <span class="fm-badge badge-status-<?= esc($req['status']) ?>"><?= ucfirst($req['status']) ?></span></div>
    <?php if($req['approved_by_name']): ?>
    <div class="small mb-2"><span class="text-muted">Actioned by:</span> <?= esc($req['approved_by_name']) ?></div>
    <div class="small"><span class="text-muted">Actioned:</span> <?= date('d M Y',strtotime($req['approved_at'])) ?></div>
    <?php endif; ?>
  </div></div>
</div>
<?= $this->endSection() ?>
