<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-receipt me-2"></i>Create Invoice</h1></div></div>
<?= form_open(base_url('finance/invoices/store')) ?>
<div class="row g-3">
  <div class="col-lg-8">
    <div class="fm-form-section">
      <h6><i class="bi bi-info-circle"></i>Invoice Details</h6>
      <div class="row g-2">
        <div class="col-md-6"><label class="form-label">Facility <span class="text-danger">*</span></label><select name="facility_id" class="form-select" required><option value="">— Select —</option><?php foreach($facilities as $f): ?><option value="<?= $f['id'] ?>"><?= esc($f['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-6"><label class="form-label">Contract</label><select name="contract_id" class="form-select"><option value="">— None —</option><?php foreach($contracts as $c): ?><option value="<?= $c['id'] ?>"><?= esc($c['contract_number']) ?> — <?= esc($c['client_name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-6"><label class="form-label">Invoice Type</label><select name="invoice_type" class="form-select"><?php foreach(['monthly'=>'Monthly','quarterly'=>'Quarterly','annual'=>'Annual','adhoc'=>'Ad-Hoc','wo_based'=>'WO Based'] as $v=>$l): ?><option value="<?= $v ?>"><?= $l ?></option><?php endforeach; ?></select></div>
        <div class="col-md-6"><label class="form-label">Linked Work Order</label><select name="work_order_id" class="form-select"><option value="">— None —</option><?php foreach($workOrders as $wo): ?><option value="<?= $wo['id'] ?>"><?= esc($wo['wo_number']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-6"><label class="form-label">Issue Date <span class="text-danger">*</span></label><input type="date" name="issue_date" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
        <div class="col-md-6"><label class="form-label">Due Date <span class="text-danger">*</span></label><input type="date" name="due_date" class="form-control" value="<?= date('Y-m-d',strtotime('+30 days')) ?>" required></div>
        <div class="col-md-6"><label class="form-label">Subtotal (<?= $currency ?>) <span class="text-danger">*</span></label><input type="number" name="subtotal" id="subtotalInp" class="form-control" step="0.01" min="0" required oninput="calcVat()"></div>
        <?php if($vatEnabled): ?><div class="col-md-3"><label class="form-label">VAT (<?= $vatRate ?>%)</label><input type="text" id="vatDisplay" class="form-control" readonly style="background:#f9f9f9"></div><div class="col-md-3"><label class="form-label">Total</label><input type="text" id="totalDisplay" class="form-control" readonly style="background:#f0f4f8;font-weight:700"></div><?php endif; ?>
        <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
      </div>
    </div>
  </div>
</div>
<div class="d-flex gap-2 mt-2"><button type="submit" class="btn btn-fm-primary"><i class="bi bi-check-lg me-2"></i>Create Invoice</button><a href="<?= base_url('finance/invoices') ?>" class="btn btn-fm-outline">Cancel</a></div>
<?= form_close() ?>
<script>
const VAT_RATE=<?= $vatRate ?>,VAT_ENABLED=<?= $vatEnabled?'true':'false' ?>;
function calcVat(){const s=parseFloat(document.getElementById('subtotalInp').value)||0;const v=VAT_ENABLED?Math.round(s*VAT_RATE/100*100)/100:0;if(document.getElementById('vatDisplay'))document.getElementById('vatDisplay').value=v.toFixed(2);if(document.getElementById('totalDisplay'))document.getElementById('totalDisplay').value=(s+v).toFixed(2);}
</script>
<?= $this->endSection() ?>
