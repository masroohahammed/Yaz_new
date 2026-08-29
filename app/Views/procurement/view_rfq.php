<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-envelope me-2"></i><?= esc($rfq['rfq_number']) ?></h1><span class="fm-badge badge-status-<?= esc($rfq['status']) ?>"><?= ucfirst($rfq['status']) ?></span></div>
  <div class="d-flex gap-2">
    <?php if(count($quotations)>=2): ?><a href="<?= base_url('procurement/rfq/compare/'.$rfq['id']) ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-bar-chart me-1"></i>Compare Quotations</a><?php endif; ?>
    <a href="<?= base_url('procurement') ?>" class="btn btn-fm-outline btn-sm">Back</a>
  </div>
</div>
<div class="row g-3">
  <div class="col-md-4">
    <div class="fm-form-section mb-3">
      <h6><i class="bi bi-info-circle"></i>RFQ Details</h6>
      <div class="small mb-2"><span class="text-muted">Title:</span> <strong><?= esc($rfq['title']) ?></strong></div>
      <div class="small mb-2"><span class="text-muted">Deadline:</span> <?= date('d M Y',strtotime($rfq['deadline'])) ?></div>
      <?php if($rfq['description']): ?><div class="small"><span class="text-muted">Description:</span><div class="mt-1 p-2 bg-light rounded"><?= esc($rfq['description']) ?></div></div><?php endif; ?>
    </div>
    <div class="fm-form-section">
      <h6><i class="bi bi-truck"></i>Vendors Invited (<?= count($vendors) ?>)</h6>
      <?php foreach($vendors as $v): ?><div class="small mb-1"><i class="bi bi-circle-fill me-1" style="color:<?= $v['status']==='responded'?'green':($v['status']==='declined'?'red':'gray') ?>;font-size:.5rem"></i><?= esc($v['vendor_name']??'—') ?> <span class="x-small text-muted">(<?= $v['status'] ?>)</span></div><?php endforeach; ?>
    </div>
  </div>
  <div class="col-md-8">
    <div class="fm-card mb-3">
      <div class="card-header-fm"><h5>Quotations Received (<?= count($quotations) ?>)</h5></div>
      <div class="fm-card-body p-0">
        <?php if(empty($quotations)): ?><p class="text-center py-3 text-muted small">No quotations received yet.</p><?php else: ?>
        <table class="fm-table"><thead><tr><th>Vendor</th><th>Unit Price</th><th>Total</th><th>Lead Time</th><th>Valid Until</th></tr></thead><tbody>
        <?php foreach($quotations as $q): ?><tr class="<?= $q['is_selected']?'table-success':'' ?>">
          <td class="small fw-semibold"><?= esc($q['vendor_name']??'—') ?></td>
          <td class="small"><?= $currency ?> <?= number_format($q['unit_price'],2) ?></td>
          <td class="small fw-bold"><?= $currency ?> <?= number_format($q['total_amount'],2) ?></td>
          <td class="small text-muted"><?= esc($q['lead_time']??'—') ?></td>
          <td class="small text-muted"><?= $q['validity']?date('d M Y',strtotime($q['validity'])):'—' ?></td>
        </tr><?php endforeach; ?>
        </tbody></table><?php endif; ?>
      </div>
    </div>
    <!-- Add Quotation Form -->
    <div class="fm-form-section">
      <h6><i class="bi bi-plus-circle"></i>Add Quotation</h6>
      <?= form_open(base_url('procurement/rfq/quotation/'.$rfq['id'])) ?>
      <div class="row g-2">
        <div class="col-md-4"><label class="form-label small">Vendor</label><select name="vendor_id" class="form-select form-select-sm" required><option value="">— Select —</option><?php foreach($vendors as $v): ?><option value="<?= $v['vendor_id'] ?>"><?= esc($v['vendor_name']??'—') ?></option><?php endforeach; ?></select></div>
        <div class="col-md-3"><label class="form-label small">Unit Price</label><input type="number" name="unit_price" class="form-control form-control-sm" step="0.01" min="0" required></div>
        <div class="col-md-3"><label class="form-label small">Total</label><input type="number" name="total_amount" class="form-control form-control-sm" step="0.01" min="0" required></div>
        <div class="col-md-2"><label class="form-label small">&nbsp;</label><button type="submit" class="btn btn-fm-primary btn-sm w-100">Add</button></div>
        <div class="col-md-4"><label class="form-label small">Lead Time</label><input type="text" name="lead_time" class="form-control form-control-sm" placeholder="e.g. 5 days"></div>
        <div class="col-md-4"><label class="form-label small">Validity</label><input type="date" name="validity" class="form-control form-control-sm"></div>
        <div class="col-12"><input type="text" name="notes" class="form-control form-control-sm" placeholder="Notes (optional)"></div>
      </div>
      <?= form_close() ?>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
