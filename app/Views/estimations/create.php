<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$isEdit = isset($est) && !empty($est['id']);
$e = $est ?? [];
$canViewInternal = $canViewInternal ?? true;
$existingItems = $items ?? [[
  'type' => 'material', 'item_name' => '', 'description' => '', 'quantity' => 1,
  'unit' => 'unit', 'unit_price' => 0, 'estimated_unit_cost' => 0, 'actual_unit_cost' => 0,
]];
?>
<div class="page-header">
  <div><h1><i class="bi bi-calculator me-2"></i><?= $isEdit ? 'Edit '.esc($e['est_number']) : 'New Estimation' ?></h1></div>
</div>
<?php $formUrl = $isEdit ? base_url('estimations/update/'.$e['id']) : base_url('estimations/store'); ?>
<?= form_open($formUrl) ?>
<div class="row g-3">
  <div class="col-lg-8">
    <div class="fm-form-section">
      <h6><i class="bi bi-info-circle"></i>Estimation Details</h6>
      <div class="row g-2">
        <div class="col-md-8">
          <label class="form-label">Title <span class="text-danger">*</span></label>
          <input type="text" name="title" class="form-control" value="<?= esc($e['title']??old('title')) ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Facility <span class="text-danger">*</span></label>
          <select name="facility_id" class="form-select" required>
            <option value="">— Select —</option>
            <?php foreach($facilities as $f): ?>
            <option value="<?= $f['id'] ?>" <?= ($e['facility_id']??'')==$f['id']?'selected':'' ?>><?= esc($f['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Linked Work Order</label>
          <select name="wo_id" class="form-select">
            <option value="">— None —</option>
            <?php foreach($workOrders as $wo): ?>
            <option value="<?= $wo['id'] ?>" <?= ($e['wo_id']??'')==$wo['id']?'selected':'' ?>><?= esc($wo['wo_number']) ?> — <?= esc(substr($wo['title'],0,50)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label">Description / Scope of Work</label>
          <textarea name="description" class="form-control" rows="3"><?= esc($e['description']??'') ?></textarea>
        </div>
      </div>
    </div>

    <div class="fm-form-section">
      <h6><i class="bi bi-list-ol"></i>Line Items — Selling Price &amp; Cost</h6>
      <p class="small text-muted mb-2">Each row includes customer selling price and internal estimated/actual cost. Profit and margin are calculated automatically.</p>
      <div class="table-responsive">
        <table class="table table-sm align-middle" id="lineItems">
          <thead>
            <tr>
              <th>Type</th><th>Item / Service</th><th>Description</th><th>Qty</th><th>Unit</th><th>Sell Price</th>
              <?php if($canViewInternal): ?><th>Est. Cost</th><th>Act. Cost</th><th>Profit</th><th>Margin</th><?php endif; ?>
              <th>Line Total</th><th></th>
            </tr>
          </thead>
          <tbody id="lineBody">
            <?php foreach($existingItems as $item): ?>
            <tr class="line-row">
              <td>
                <select name="item_type[]" class="form-select form-select-sm">
                  <?php foreach(['material'=>'Material','labor'=>'Labor','service'=>'Service','other'=>'Other'] as $v=>$l): ?>
                  <option value="<?= $v ?>" <?= ($item['type']??'material')===$v?'selected':'' ?>><?= $l ?></option>
                  <?php endforeach; ?>
                </select>
              </td>
              <td><input type="text" name="item_name[]" class="form-control form-control-sm" value="<?= esc($item['item_name']??$item['description']??'') ?>" required></td>
              <td><input type="text" name="item_desc[]" class="form-control form-control-sm" value="<?= esc($item['description']??'') ?>"></td>
              <td><input type="number" name="item_qty[]" class="form-control form-control-sm qty-input" value="<?= $item['quantity']??1 ?>" min="0" step="0.01" oninput="calcRow(this)"></td>
              <td><input type="text" name="item_unit[]" class="form-control form-control-sm" value="<?= esc($item['unit']??'unit') ?>"></td>
              <td><input type="number" name="item_price[]" class="form-control form-control-sm price-input" value="<?= $item['unit_price']??0 ?>" min="0" step="0.01" oninput="calcRow(this)"></td>
              <?php if($canViewInternal): ?>
              <td><input type="number" name="item_est_cost[]" class="form-control form-control-sm est-input" value="<?= $item['estimated_unit_cost']??$item['unit_cost']??0 ?>" min="0" step="0.01" oninput="calcRow(this)"></td>
              <td><input type="number" name="item_act_cost[]" class="form-control form-control-sm act-input" value="<?= $item['actual_unit_cost']??0 ?>" min="0" step="0.01" oninput="calcRow(this)"></td>
              <td><input type="text" class="form-control form-control-sm profit-out" readonly style="background:#f9f9f9"></td>
              <td><input type="text" class="form-control form-control-sm margin-out" readonly style="background:#f9f9f9"></td>
              <?php endif; ?>
              <td><input type="text" class="form-control form-control-sm line-total-out" readonly style="background:#f0f4f8;font-weight:600"></td>
              <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)"><i class="bi bi-trash"></i></button></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <button type="button" class="btn btn-fm-outline btn-sm" onclick="addRow()"><i class="bi bi-plus me-1"></i>Add Line Item</button>
    </div>

    <?php if($canViewInternal): ?>
    <div class="fm-form-section">
      <h6><i class="bi bi-currency-exchange"></i>Actual Cost Breakdown</h6>
      <div class="row g-2">
        <?php foreach([
          'actual_labor_cost'=>'Labor','actual_material_cost'=>'Materials','actual_transport_cost'=>'Transportation',
          'actual_equipment_cost'=>'Equipment','actual_misc_cost'=>'Miscellaneous','actual_other_cost'=>'Other',
        ] as $field=>$label): ?>
        <div class="col-md-4">
          <label class="form-label small"><?= $label ?> (<?= $currency ?>)</label>
          <input type="number" name="<?= $field ?>" class="form-control form-control-sm breakdown-input" step="0.01" min="0" value="<?= $e[$field]??0 ?>" oninput="updateSummary()">
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="fm-form-section">
      <h6><i class="bi bi-card-text"></i>Notes</h6>
      <textarea name="notes" class="form-control" rows="2"><?= esc($e['notes']??'') ?></textarea>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="fm-form-section position-sticky" style="top:80px">
      <h6><i class="bi bi-receipt"></i>Financial Summary</h6>
      <div class="d-flex justify-content-between small mb-2"><span class="text-muted">Selling Subtotal</span><strong id="sellingSubtotal">0.00</strong></div>
      <?php if($canViewInternal): ?>
      <div class="d-flex justify-content-between small mb-2"><span class="text-muted">Estimated Cost</span><strong id="estimatedSubtotal">0.00</strong></div>
      <div class="d-flex justify-content-between small mb-2"><span class="text-muted">Actual Cost</span><strong id="actualSubtotal">0.00</strong></div>
      <div class="d-flex justify-content-between small mb-2"><span class="text-muted">Profit</span><strong id="profitTotal">0.00</strong></div>
      <div class="d-flex justify-content-between small mb-2"><span class="text-muted">Margin</span><strong id="marginTotal">0%</strong></div>
      <div class="d-flex justify-content-between small mb-2"><span class="text-muted">Variance</span><strong id="varianceTotal">0.00</strong></div>
      <hr>
      <?php endif; ?>
      <?php if($vatEnabled): ?>
      <div class="d-flex justify-content-between small mb-2"><span>VAT (<?= $vatRate ?>%)</span><strong id="vatDisplay">0.00</strong></div>
      <?php endif; ?>
      <div class="d-flex justify-content-between fw-bold mb-3"><span>Customer Total</span><span id="grandTotalDisplay">0.00</span></div>
      <button type="submit" class="btn btn-fm-primary w-100"><i class="bi bi-check-lg me-2"></i><?= $isEdit?'Update':'Save Estimation' ?></button>
      <a href="<?= base_url('estimations') ?>" class="btn btn-fm-outline w-100 mt-2">Cancel</a>
    </div>
  </div>
</div>
<?= form_close() ?>

<?= $this->section('scripts') ?>
<script>
const VAT_ENABLED=<?= $vatEnabled?'true':'false' ?>;const VAT_RATE=<?= $vatRate ?>;const CAN_VIEW_INTERNAL=<?= $canViewInternal?'true':'false' ?>;
function calcRow(inp){const row=inp.closest('.line-row');const qty=parseFloat(row.querySelector('.qty-input').value)||0;const price=parseFloat(row.querySelector('.price-input').value)||0;const lt=qty*price;row.querySelector('.line-total-out').value=lt.toFixed(2);if(CAN_VIEW_INTERNAL){const est=parseFloat(row.querySelector('.est-input').value)||0;const act=parseFloat(row.querySelector('.act-input').value)||0;const profit=lt-(qty*act);const margin=lt>0?(profit/lt)*100:0;row.querySelector('.profit-out').value=profit.toFixed(2);row.querySelector('.margin-out').value=margin.toFixed(1)+'%';}updateSummary();}
function removeRow(btn){if(document.querySelectorAll('.line-row').length>1){btn.closest('tr').remove();updateSummary();}}
function addRow(){const tbody=document.getElementById('lineBody');const tr=document.createElement('tr');tr.className='line-row';const internal=<?= $canViewInternal ? 'true' : 'false' ?>;tr.innerHTML=`<td><select name="item_type[]" class="form-select form-select-sm"><option value="material">Material</option><option value="labor">Labor</option><option value="service">Service</option><option value="other">Other</option></select></td><td><input type="text" name="item_name[]" class="form-control form-control-sm" required></td><td><input type="text" name="item_desc[]" class="form-control form-control-sm"></td><td><input type="number" name="item_qty[]" class="form-control form-control-sm qty-input" value="1" min="0" step="0.01" oninput="calcRow(this)"></td><td><input type="text" name="item_unit[]" class="form-control form-control-sm" value="unit"></td><td><input type="number" name="item_price[]" class="form-control form-control-sm price-input" value="0" min="0" step="0.01" oninput="calcRow(this)"></td>${internal?'<td><input type="number" name="item_est_cost[]" class="form-control form-control-sm est-input" value="0" min="0" step="0.01" oninput="calcRow(this)"></td><td><input type="number" name="item_act_cost[]" class="form-control form-control-sm act-input" value="0" min="0" step="0.01" oninput="calcRow(this)"></td><td><input type="text" class="form-control form-control-sm profit-out" readonly style="background:#f9f9f9"></td><td><input type="text" class="form-control form-control-sm margin-out" readonly style="background:#f9f9f9"></td>':''}<td><input type="text" class="form-control form-control-sm line-total-out" readonly style="background:#f0f4f8;font-weight:600"></td><td><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)"><i class="bi bi-trash"></i></button></td>`;tbody.appendChild(tr);}
function updateSummary(){let selling=0,estimated=0,actual=0,breakdown=0;document.querySelectorAll('.line-row').forEach(row=>{const qty=parseFloat(row.querySelector('.qty-input').value)||0;const price=parseFloat(row.querySelector('.price-input').value)||0;selling+=qty*price;if(CAN_VIEW_INTERNAL){estimated+=qty*(parseFloat(row.querySelector('.est-input').value)||0);actual+=qty*(parseFloat(row.querySelector('.act-input').value)||0);}});if(CAN_VIEW_INTERNAL){document.querySelectorAll('.breakdown-input').forEach(inp=>{breakdown+=parseFloat(inp.value)||0;});const actTotal=breakdown>0?breakdown:actual;const profit=selling-actTotal;const margin=selling>0?(profit/selling)*100:0;document.getElementById('estimatedSubtotal').textContent=estimated.toFixed(2);document.getElementById('actualSubtotal').textContent=actTotal.toFixed(2);document.getElementById('profitTotal').textContent=profit.toFixed(2);document.getElementById('marginTotal').textContent=margin.toFixed(1)+'%';document.getElementById('varianceTotal').textContent=(actTotal-estimated).toFixed(2);}const vat=VAT_ENABLED?Math.round(selling*VAT_RATE/100*100)/100:0;document.getElementById('sellingSubtotal').textContent=selling.toFixed(2);if(document.getElementById('vatDisplay'))document.getElementById('vatDisplay').textContent=vat.toFixed(2);document.getElementById('grandTotalDisplay').textContent=(selling+vat).toFixed(2);}
document.addEventListener('DOMContentLoaded',()=>{document.querySelectorAll('.line-row .qty-input').forEach(inp=>calcRow(inp));});
</script>
<?= $this->endSection() ?>
<?= $this->endSection() ?>
