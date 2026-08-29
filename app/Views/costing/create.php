<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-calculator me-2"></i>Log Maintenance Cost</h1></div><a href="<?= base_url('costing') ?>" class="btn btn-fm-outline btn-sm">← Back</a></div>
<div class="row"><div class="col-lg-8"><div class="fm-card"><div class="fm-card-body">
<?= form_open('costing/store') ?>
<div class="row g-3">
  <div class="col-12"><label class="form-label">Work Order *</label><select name="wo_id" class="form-select" required><option value="">Select Work Order...</option><?php foreach($workOrders as $w): ?><option value="<?= $w['id'] ?>"><?= esc($w['wo_number']) ?> — <?= esc($w['title']) ?></option><?php endforeach; ?></select></div>
  <div class="col-12"><h6 class="fw-semibold text-primary mt-2">Cost Breakdown (<?= $currency ?>)</h6></div>
  <div class="col-md-4"><label class="form-label">Labor Hours</label><input type="number" name="labor_hours" class="form-control" placeholder="0" step="0.5" oninput="calcTotal()"></div>
  <div class="col-md-4"><label class="form-label">Labor Cost</label><input type="number" name="labor_cost" id="labor_cost" class="form-control" placeholder="0.00" step="0.01" oninput="calcTotal()"></div>
  <div class="col-md-4"><label class="form-label">Spare Parts Cost</label><input type="number" name="parts_cost" id="parts_cost" class="form-control" placeholder="0.00" step="0.01" oninput="calcTotal()"></div>
  <div class="col-md-4"><label class="form-label">Vendor / Subcontractor Cost</label><input type="number" name="vendor_cost" id="vendor_cost" class="form-control" placeholder="0.00" step="0.01" oninput="calcTotal()"></div>
  <div class="col-md-4"><label class="form-label">Emergency Surcharge</label><input type="number" name="emergency_surcharge" id="surcharge" class="form-control" placeholder="0.00" step="0.01" oninput="calcTotal()"></div>
  <div class="col-md-4"><label class="form-label">Cost Estimate / Quote</label><input type="number" name="cost_estimate" id="estimate" class="form-control" placeholder="0.00" step="0.01" oninput="calcProfit()"></div>
  <div class="col-12">
    <div class="p-3 rounded" style="background:#f0f4f8;border:2px solid var(--fm-navy)">
      <div class="d-flex justify-content-between"><span class="fw-semibold">Total Cost:</span><span class="fw-bold text-danger" id="total-display"><?= $currency ?> 0.00</span></div>
      <div class="d-flex justify-content-between mt-1"><span class="fw-semibold">Job Profit:</span><span class="fw-bold" id="profit-display"><?= $currency ?> 0.00</span></div>
    </div>
  </div>
  <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
  <div class="col-12"><button type="submit" class="btn btn-fm-primary">Save Costing Record</button></div>
</div>
<?= form_close() ?>
</div></div></div></div>
<?= $this->section('scripts') ?>
<script>
function calcTotal(){
  const l=+document.getElementById('labor_cost').value||0;
  const p=+document.getElementById('parts_cost').value||0;
  const v=+document.getElementById('vendor_cost').value||0;
  const s=+document.getElementById('surcharge').value||0;
  const total=l+p+v+s;
  document.getElementById('total-display').textContent='<?= $currency ?> '+total.toFixed(2);
  calcProfit(total);
}
function calcProfit(total){
  total=total||((+document.getElementById('labor_cost').value||0)+(+document.getElementById('parts_cost').value||0)+(+document.getElementById('vendor_cost').value||0)+(+document.getElementById('surcharge').value||0));
  const est=+document.getElementById('estimate').value||0;
  const profit=est-total;
  const el=document.getElementById('profit-display');
  el.textContent='<?= $currency ?> '+profit.toFixed(2);
  el.className='fw-bold '+(profit>=0?'text-success':'text-danger');
}
</script>
<?= $this->endSection() ?>
<?= $this->endSection() ?>
