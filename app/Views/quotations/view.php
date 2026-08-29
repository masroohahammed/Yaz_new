<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-file-earmark-text me-2 text-primary"></i>Quotation — <?= esc($quotation['vendor_name']) ?></h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= base_url('quotations') ?>">Quotations</a></li>
      <li class="breadcrumb-item active"><?= esc($quotation['vendor_name']) ?></li>
    </ol></nav>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('quotations/'.$quotation['id'].'/edit') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
    <a href="<?= base_url('quotations') ?>" class="btn btn-outline-secondary btn-sm">← Back</a>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="fm-card mb-3">
      <div class="card-header-fm">
        <h5><i class="bi bi-list-ul me-2"></i>Line Items</h5>
        <span class="fw-bold"><?= $currency ?? 'QAR' ?> <?= number_format($subtotal, 2) ?></span>
      </div>
      <div class="fm-card-body p-0">
        <?php if(empty($items)): ?>
        <p class="text-muted text-center py-4 small">No line items.</p>
        <?php else: ?>
        <table class="fm-table">
          <thead><tr><th>#</th><th>Description</th><th class="text-center">Qty</th><th>Unit</th><th class="text-end">Unit Price</th><th class="text-end">Total</th></tr></thead>
          <tbody>
          <?php foreach($items as $i => $item): ?>
          <tr>
            <td class="small text-muted"><?= $i+1 ?></td>
            <td><?= esc($item['description']) ?></td>
            <td class="text-center small"><?= number_format($item['qty'], 2) ?></td>
            <td class="small text-muted"><?= esc($item['unit']) ?></td>
            <td class="text-end small"><?= number_format($item['unit_price'], 2) ?></td>
            <td class="text-end fw-semibold small"><?= number_format((float)$item['qty'] * (float)$item['unit_price'], 2) ?></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
          <tfoot class="table-light">
            <tr><td colspan="5" class="text-end fw-bold">Subtotal</td><td class="text-end fw-bold"><?= number_format($subtotal, 2) ?></td></tr>
          </tfoot>
        </table>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="fm-card">
      <div class="card-header-fm"><h5><i class="bi bi-info-circle me-2"></i>Details</h5></div>
      <div class="fm-card-body">
        <table class="w-100" style="font-size:.83rem">
          <tr><td class="text-muted py-1" style="width:40%">Facility</td><td class="fw-semibold"><?= esc($quotation['facility_name'] ?? '—') ?></td></tr>
          <tr><td class="text-muted py-1">Vendor</td><td><?= esc($quotation['vendor_name']) ?></td></tr>
          <?php if($quotation['vendor_contact']): ?><tr><td class="text-muted py-1">Contact</td><td><?= esc($quotation['vendor_contact']) ?></td></tr><?php endif; ?>
          <tr><td class="text-muted py-1">Status</td><td><span class="fm-badge badge-status-<?= esc($quotation['status']) ?>"><?= ucfirst($quotation['status']) ?></span></td></tr>
          <?php if($quotation['valid_until']): ?><tr><td class="text-muted py-1">Valid Until</td><td><?= date('d M Y', strtotime($quotation['valid_until'])) ?></td></tr><?php endif; ?>
          <tr><td class="text-muted py-1">Created</td><td><?= date('d M Y', strtotime($quotation['created_at'])) ?></td></tr>
        </table>
        <?php if(!empty($quotation['description'])): ?>
        <hr><div class="small"><strong>Description:</strong><br><?= nl2br(esc($quotation['description'])) ?></div>
        <?php endif; ?>
        <?php if(!empty($quotation['notes'])): ?>
        <hr><div class="small text-muted"><?= nl2br(esc($quotation['notes'])) ?></div>
        <?php endif; ?>
        <div class="mt-3 d-grid">
          <a href="<?= base_url('quotations/'.$quotation['id'].'/edit') ?>" class="btn btn-fm-primary btn-sm">Edit Quotation</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
