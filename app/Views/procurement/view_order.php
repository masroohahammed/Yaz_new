<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-bag-check me-2"></i><?= esc($po['po_number']) ?></h1><span class="fm-badge badge-status-<?= esc($po['status']) ?>"><?= ucfirst($po['status']) ?></span></div>
  <div class="d-flex gap-2">
    <?php if($po['status']==='pending' && in_array(session()->get('user_role'),['super_admin','facility_manager'])): ?>
    <?= form_open(base_url('procurement/order/approve/'.$po['id'])) ?>
    <button type="submit" class="btn btn-fm-primary btn-sm" onclick="return confirm('Approve PO?')"><i class="bi bi-check me-1"></i>Approve</button>
    <?= form_close() ?>
    <?php endif; ?>
    <?php if($po['status']==='approved'): ?>
    <a href="<?= base_url('procurement/grn/create/'.$po['id']) ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-truck me-1"></i>Record GRN</a>
    <?php endif; ?>
    <a href="<?= base_url('procurement/order/print/'.$po['id']) ?>" class="btn btn-fm-outline btn-sm" target="_blank"><i class="bi bi-printer me-1"></i>Print PO</a>
    <?php if (in_array(session()->get('user_role'), ['super_admin','facility_manager','finance_manager','procurement_officer'], true)): ?>
    <a href="<?= base_url('procurement/order/three-way/'.$po['id']) ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-intersect me-1"></i>3-Way Match</a>
    <?php endif; ?>
    <a href="<?= base_url('procurement') ?>" class="btn btn-fm-outline btn-sm">Back</a>
  </div>
</div>
<div class="row g-3">
  <div class="col-md-5">
    <div class="fm-form-section mb-3">
      <h6><i class="bi bi-truck"></i>Vendor</h6>
      <div class="small mb-1 fw-bold"><?= esc($po['vendor_name']??'—') ?></div>
      <?php if($po['vendor_email']): ?><div class="small text-muted"><i class="bi bi-envelope me-1"></i><?= esc($po['vendor_email']) ?></div><?php endif; ?>
      <?php if($po['vendor_phone']): ?><div class="small text-muted"><i class="bi bi-telephone me-1"></i><?= esc($po['vendor_phone']) ?></div><?php endif; ?>
    </div>
    <div class="fm-form-section">
      <h6><i class="bi bi-info-circle"></i>PO Info</h6>
      <div class="small mb-2"><span class="text-muted">PO #:</span> <strong><?= esc($po['po_number']) ?></strong></div>
      <div class="small mb-2"><span class="text-muted">Created by:</span> <?= esc($po['created_by_name']??'—') ?></div>
      <div class="small mb-2"><span class="text-muted">Delivery Date:</span> <?= date('d M Y',strtotime($po['delivery_date'])) ?></div>
      <div class="small mb-2"><span class="text-muted">Total Amount:</span> <strong class="text-primary"><?= $currency ?> <?= number_format($po['total_amount'],2) ?></strong></div>
      <?php if($po['notes']): ?><div class="small"><span class="text-muted">Notes:</span> <?= esc($po['notes']) ?></div><?php endif; ?>
    </div>
  </div>
  <div class="col-md-7">
    <div class="fm-card">
      <div class="card-header-fm"><h5>Line Items</h5></div>
      <div class="fm-card-body p-0">
        <?php if(empty($lineItems)): ?><p class="text-center py-3 text-muted small">No linked purchase requests.</p><?php else: ?>
        <table class="fm-table">
          <thead><tr><th>Item</th><th>Qty</th><th>Unit</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach($lineItems as $li): ?><tr>
            <td class="small fw-semibold"><?= esc($li['item_name']??'—') ?></td>
            <td class="small"><?= $li['quantity'] ?></td>
            <td class="small text-muted"><?= esc($li['unit']??'') ?></td>
            <td><span class="fm-badge badge-status-<?= esc($li['status']) ?>"><?= ucfirst($li['status']) ?></span></td>
          </tr><?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
