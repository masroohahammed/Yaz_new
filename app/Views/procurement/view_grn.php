<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-truck me-2"></i><?= esc($grn['grn_number']) ?></h1><span class="fm-badge badge-status-<?= esc($grn['status']) ?>"><?= ucfirst($grn['status']) ?></span></div>
  <a href="<?= base_url('procurement') ?>" class="btn btn-fm-outline btn-sm">Back</a>
</div>
<div class="row g-3">
  <div class="col-md-4">
    <div class="fm-form-section">
      <h6><i class="bi bi-info-circle"></i>GRN Details</h6>
      <div class="small mb-2"><span class="text-muted">GRN #:</span> <strong><?= esc($grn['grn_number']) ?></strong></div>
      <div class="small mb-2"><span class="text-muted">PO #:</span> <a href="<?= base_url('procurement/order/view/'.$grn['po_id']) ?>" class="text-primary"><?= esc($grn['po_number']??'—') ?></a></div>
      <div class="small mb-2"><span class="text-muted">Vendor:</span> <strong><?= esc($grn['vendor_name']??'—') ?></strong></div>
      <div class="small mb-2"><span class="text-muted">Received Date:</span> <?= date('d M Y',strtotime($grn['received_date'])) ?></div>
      <div class="small mb-2"><span class="text-muted">Received By:</span> <?= esc($grn['received_by_name']??'—') ?></div>
      <?php if($grn['notes']): ?><div class="small"><span class="text-muted">Notes:</span> <?= esc($grn['notes']) ?></div><?php endif; ?>
    </div>
  </div>
  <div class="col-md-8">
    <div class="fm-card">
      <div class="card-header-fm"><h5><i class="bi bi-box me-2"></i>Items Received</h5></div>
      <div class="fm-card-body p-0">
        <table class="fm-table">
          <thead><tr><th>Item</th><th>Unit</th><th>Received Qty</th></tr></thead>
          <tbody><?php foreach($items as $i): ?><tr>
            <td class="small fw-semibold"><?= esc($i['item_name']??'—') ?></td>
            <td class="small text-muted"><?= esc($i['unit']??'') ?></td>
            <td class="small fw-bold text-success"><?= $i['received_qty'] ?></td>
          </tr><?php endforeach; ?>
          <?php if(empty($items)): ?><tr><td colspan="3" class="text-center py-3 text-muted">No items.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
