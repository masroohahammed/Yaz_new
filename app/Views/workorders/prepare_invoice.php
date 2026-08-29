<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-receipt-cutoff me-2"></i><?= esc($title) ?></h1>
    <p class="text-muted small mb-0">Set client-facing prices. Internal costs are hidden from the client invoice PDF.</p>
  </div>
  <a href="<?= base_url('workorders/view/' . $wo['id']) . '#tab-closure' ?>" class="btn btn-fm-outline btn-sm">
    <i class="bi bi-arrow-left me-1"></i>Back to Work Order
  </a>
</div>

<?= form_open(base_url('workorders/prepare-invoice/' . $wo['id']), ['id' => 'invPrepForm']) ?>
<?= csrf_field() ?>

<!-- Line items -->
<div class="fm-form-section mb-3">
  <h6><i class="bi bi-list-ul me-1"></i>Line Items</h6>
  <p class="small text-muted mb-3">Edit descriptions and selling prices. The <em>Internal Cost</em> column is for your records only — it will not appear on the client invoice.</p>
  <div class="table-responsive mb-2">
    <table class="table table-sm" id="lineTable">
      <thead class="table-light">
        <tr>
          <th>Description</th>
          <th style="width:80px">Qty</th>
          <th style="width:120px">Unit Price (<?= esc($currency) ?>)</th>
          <th style="width:110px">Total</th>
          <th style="width:110px">Int. Cost</th>
          <th style="width:40px"></th>
        </tr>
      </thead>
      <tbody id="lineBody">

        <?php foreach ($materials as $i => $m): ?>
        <tr>
          <td><input type="text" name="lines[<?= $i ?>][description]" class="form-control form-control-sm"
                     value="<?= esc($m['item_name'] ?? $m['description'] ?? '') ?>" required></td>
          <td><input type="number" name="lines[<?= $i ?>][qty]" class="form-control form-control-sm lp-qty"
                     step="0.01" min="0.01" value="<?= (float)($m['quantity'] ?? 1) ?>"></td>
          <td><input type="number" name="lines[<?= $i ?>][unit_price]" class="form-control form-control-sm lp-price"
                     step="0.01" min="0" value="<?= (float)($m['unit_price'] ?? $m['unit_cost'] ?? 0) ?>"></td>
          <td><input type="text" class="form-control form-control-sm lp-total bg-light" readonly
                     value="<?= number_format((float)($m['total_cost'] ?? 0), 2) ?>"></td>
          <td><input type="number" name="lines[<?= $i ?>][internal_cost]" class="form-control form-control-sm"
                     step="0.01" value="<?= (float)($m['total_cost'] ?? 0) ?>" tabindex="-1"></td>
          <td><button type="button" class="btn btn-sm btn-outline-danger lp-del"><i class="bi bi-x"></i></button></td>
        </tr>
        <?php endforeach; ?>

        <?php if (!empty($labor)): ?>
        <?php $li = count($materials); ?>
        <tr>
          <td><input type="text" name="lines[<?= $li ?>][description]" class="form-control form-control-sm" value="Labor &amp; Service Charges"></td>
          <td><input type="number" name="lines[<?= $li ?>][qty]" class="form-control form-control-sm lp-qty" value="1"></td>
          <td><input type="number" name="lines[<?= $li ?>][unit_price]" class="form-control form-control-sm lp-price"
                     step="0.01" value="<?= number_format($laborTotal, 2) ?>"></td>
          <td><input type="text" class="form-control form-control-sm lp-total bg-light" readonly value="<?= number_format($laborTotal, 2) ?>"></td>
          <td><input type="number" name="lines[<?= $li ?>][internal_cost]" class="form-control form-control-sm"
                     step="0.01" value="<?= number_format($laborTotal, 2) ?>" tabindex="-1"></td>
          <td><button type="button" class="btn btn-sm btn-outline-danger lp-del"><i class="bi bi-x"></i></button></td>
        </tr>
        <?php endif; ?>

      </tbody>
    </table>
  </div>
  <button type="button" class="btn btn-sm btn-outline-secondary" id="addLine">
    <i class="bi bi-plus-lg me-1"></i>Add line
  </button>
</div>

<!-- Totals & settings -->
<div class="row g-3 mb-3">
  <div class="col-md-6">
    <div class="fm-form-section h-100">
      <h6><i class="bi bi-sliders me-1"></i>Tax &amp; Discounts</h6>
      <div class="row g-2">
        <div class="col-6">
          <label class="form-label small fw-semibold">Tax Rate %</label>
          <input type="number" name="tax_rate" id="invTax" class="form-control" step="0.01" min="0" value="5">
        </div>
        <div class="col-6">
          <label class="form-label small fw-semibold">Discount (<?= esc($currency) ?>)</label>
          <input type="number" name="discount" id="invDiscount" class="form-control" step="0.01" min="0" value="0">
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="fm-form-section h-100">
      <h6><i class="bi bi-calculator me-1"></i>Summary</h6>
      <table class="table table-sm mb-0">
        <tr><td class="text-muted">Subtotal</td><td class="text-end fw-semibold" id="iSubtotal">0.00</td></tr>
        <tr><td class="text-muted">Tax</td><td class="text-end" id="iTax">0.00</td></tr>
        <tr><td class="text-muted">Discount</td><td class="text-end text-danger" id="iDiscount">0.00</td></tr>
        <tr class="table-primary fw-bold"><td>Total (<?= esc($currency) ?>)</td><td class="text-end fs-5" id="iTotal">0.00</td></tr>
        <tr><td class="text-muted small">Internal Cost</td><td class="text-end small text-muted" id="iIntCost">0.00</td></tr>
        <tr><td class="text-muted small">Est. Profit</td><td class="text-end small text-success fw-semibold" id="iProfit">0.00</td></tr>
      </table>
    </div>
  </div>
</div>

<!-- Notes -->
<div class="fm-form-section mb-3">
  <h6><i class="bi bi-card-text me-1"></i>Invoice Notes / Terms</h6>
  <textarea name="notes" class="form-control" rows="3"
            placeholder="Payment terms, bank details, references…"></textarea>
</div>

<div class="d-flex justify-content-end gap-2">
  <a href="<?= base_url('workorders/view/' . $wo['id']) ?>" class="btn btn-secondary">Cancel</a>
  <button type="submit" class="btn btn-fm-primary">
    <i class="bi bi-receipt me-1"></i>Create Draft Invoice
  </button>
</div>

<?= form_close() ?>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
(function () {
  function recalc() {
    let sub = 0, intCost = 0;
    document.querySelectorAll('#lineBody tr').forEach(function (tr) {
      const qty   = parseFloat(tr.querySelector('.lp-qty')?.value   || 0);
      const price = parseFloat(tr.querySelector('.lp-price')?.value || 0);
      const tot   = qty * price;
      const totEl = tr.querySelector('.lp-total');
      if (totEl) totEl.value = tot.toFixed(2);
      sub += tot;
      intCost += parseFloat(tr.querySelector('input[name*="internal_cost"]')?.value || 0);
    });
    const taxRate  = parseFloat(document.getElementById('invTax')?.value      || 0);
    const discount = parseFloat(document.getElementById('invDiscount')?.value || 0);
    const taxAmt   = sub * taxRate / 100;
    const total    = sub + taxAmt - discount;
    const profit   = total - intCost;
    document.getElementById('iSubtotal').textContent = sub.toFixed(2);
    document.getElementById('iTax').textContent      = taxAmt.toFixed(2);
    document.getElementById('iDiscount').textContent = discount.toFixed(2);
    document.getElementById('iTotal').textContent    = total.toFixed(2);
    document.getElementById('iIntCost').textContent  = intCost.toFixed(2);
    document.getElementById('iProfit').textContent   = profit.toFixed(2);
    document.getElementById('iProfit').className     = 'text-end small fw-semibold ' + (profit >= 0 ? 'text-success' : 'text-danger');
  }

  document.getElementById('invPrepForm').addEventListener('input', recalc);

  let extraIdx = 0;
  document.getElementById('addLine').addEventListener('click', function () {
    const idx = 'e' + (extraIdx++);
    const tr  = document.createElement('tr');
    tr.innerHTML = `
      <td><input type="text" name="extra[${idx}][description]" class="form-control form-control-sm" placeholder="Description…" required></td>
      <td><input type="number" name="extra[${idx}][qty]" class="form-control form-control-sm lp-qty" step="0.01" value="1"></td>
      <td><input type="number" name="extra[${idx}][unit_price]" class="form-control form-control-sm lp-price" step="0.01" value="0"></td>
      <td><input type="text" class="form-control form-control-sm lp-total bg-light" readonly value="0.00"></td>
      <td><input type="number" name="extra[${idx}][internal_cost]" class="form-control form-control-sm" step="0.01" value="0" tabindex="-1"></td>
      <td><button type="button" class="btn btn-sm btn-outline-danger lp-del"><i class="bi bi-x"></i></button></td>`;
    document.getElementById('lineBody').appendChild(tr);
    tr.querySelector('input').focus();
    recalc();
  });

  document.getElementById('lineBody').addEventListener('click', function (e) {
    if (e.target.closest('.lp-del')) {
      e.target.closest('tr').remove();
      recalc();
    }
  });

  recalc(); // initial
})();
</script>
<?= $this->endSection() ?>
