<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $isEdit = !empty($quotation['id']); ?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-file-earmark-plus me-2 text-primary"></i><?= $isEdit ? 'Edit Quotation' : 'New Quotation' ?></h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= base_url('quotations') ?>">Quotations</a></li>
      <li class="breadcrumb-item active"><?= $isEdit ? 'Edit' : 'New' ?></li>
    </ol></nav>
  </div>
  <a href="<?= base_url('quotations') ?>" class="btn btn-fm-outline btn-sm">← Back</a>
</div>

<form action="<?= $isEdit ? base_url('quotations/'.$quotation['id'].'/update') : base_url('quotations') ?>" method="post" id="quotationForm">
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-lg-8">
      <div class="fm-card p-4 mb-3">
        <h6 class="fw-semibold mb-3">Quotation Details</h6>
        <div class="row g-3">
          <div class="col-sm-6">
            <label class="form-label fw-medium">Facility <span class="text-danger">*</span></label>
            <select name="facility_id" class="form-select" required>
              <option value="">Select facility…</option>
              <?php foreach($facilities as $f): ?>
              <option value="<?= $f['id'] ?>" <?= old('facility_id', $quotation['facility_id'] ?? '') == $f['id'] ? 'selected' : '' ?>><?= esc($f['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-sm-6">
            <label class="form-label fw-medium">Status</label>
            <select name="status" class="form-select">
              <?php foreach(['draft'=>'Draft','submitted'=>'Submitted','approved'=>'Approved','rejected'=>'Rejected','expired'=>'Expired'] as $v=>$l): ?>
              <option value="<?= $v ?>" <?= old('status', $quotation['status'] ?? 'draft') === $v ? 'selected' : '' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-sm-6">
            <label class="form-label fw-medium">Vendor Name <span class="text-danger">*</span></label>
            <input type="text" name="vendor_name" class="form-control" required value="<?= old('vendor_name', $quotation['vendor_name'] ?? '') ?>">
          </div>
          <div class="col-sm-6">
            <label class="form-label fw-medium">Vendor Contact</label>
            <input type="text" name="vendor_contact" class="form-control" value="<?= old('vendor_contact', $quotation['vendor_contact'] ?? '') ?>" placeholder="Phone / email">
          </div>
          <div class="col-sm-6">
            <label class="form-label fw-medium">Valid Until</label>
            <input type="date" name="valid_until" class="form-control" value="<?= old('valid_until', $quotation['valid_until'] ?? '') ?>">
          </div>
          <div class="col-12">
            <label class="form-label fw-medium">Description / Scope of Work</label>
            <textarea name="description" class="form-control" rows="3"><?= old('description', $quotation['description'] ?? '') ?></textarea>
          </div>
          <div class="col-12">
            <label class="form-label fw-medium">Notes</label>
            <textarea name="notes" class="form-control" rows="2"><?= old('notes', $quotation['notes'] ?? '') ?></textarea>
          </div>
        </div>
      </div>

      <!-- Line items -->
      <div class="fm-card p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="fw-semibold mb-0">Line Items</h6>
          <button type="button" class="btn btn-sm btn-outline-primary" id="addItem"><i class="bi bi-plus me-1"></i>Add Item</button>
        </div>
        <table class="table table-sm" id="itemsTable">
          <thead class="table-light"><tr><th>Description</th><th style="width:80px">Qty</th><th style="width:80px">Unit</th><th style="width:110px">Unit Price</th><th style="width:110px">Total</th><th style="width:40px"></th></tr></thead>
          <tbody id="itemsBody">
            <?php foreach($items as $i => $item): ?>
            <tr class="item-row">
              <td><input type="text" name="items[<?= $i ?>][description]" class="form-control form-control-sm" value="<?= esc($item['description']) ?>" required></td>
              <td><input type="number" name="items[<?= $i ?>][qty]" class="form-control form-control-sm item-qty" step="0.01" min="0" value="<?= $item['qty'] ?>"></td>
              <td><input type="text" name="items[<?= $i ?>][unit]" class="form-control form-control-sm" value="<?= esc($item['unit']) ?>" placeholder="pcs"></td>
              <td><input type="number" name="items[<?= $i ?>][unit_price]" class="form-control form-control-sm item-price" step="0.01" min="0" value="<?= $item['unit_price'] ?>"></td>
              <td class="item-total small pt-2"><?= number_format((float)$item['qty'] * (float)$item['unit_price'], 2) ?></td>
              <td><button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="bi bi-trash"></i></button></td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($items)): ?>
            <tr class="item-row">
              <td><input type="text" name="items[0][description]" class="form-control form-control-sm" placeholder="Item description"></td>
              <td><input type="number" name="items[0][qty]" class="form-control form-control-sm item-qty" step="0.01" min="0" value="1"></td>
              <td><input type="text" name="items[0][unit]" class="form-control form-control-sm" value="pcs"></td>
              <td><input type="number" name="items[0][unit_price]" class="form-control form-control-sm item-price" step="0.01" min="0" value="0"></td>
              <td class="item-total small pt-2">0.00</td>
              <td><button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="bi bi-trash"></i></button></td>
            </tr>
            <?php endif; ?>
          </tbody>
          <tfoot>
            <tr><td colspan="4" class="text-end fw-bold">Subtotal</td><td id="subtotalDisplay" class="fw-bold">0.00</td><td></td></tr>
          </tfoot>
        </table>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="fm-card p-3">
        <div class="d-grid gap-2">
          <button type="submit" class="btn btn-primary-brand"><?= $isEdit ? 'Save Changes' : 'Create Quotation' ?></button>
          <a href="<?= base_url('quotations') ?>" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </div>
    </div>
  </div>
</form>

<script>
let itemIdx = <?= max(count($items), 1) ?>;

function recalc() {
  let sub = 0;
  document.querySelectorAll('#itemsBody .item-row').forEach((row, idx) => {
    const qty   = parseFloat(row.querySelector('.item-qty')?.value || 0);
    const price = parseFloat(row.querySelector('.item-price')?.value || 0);
    const total = qty * price;
    sub += total;
    const td = row.querySelector('.item-total');
    if (td) td.textContent = total.toFixed(2);
    // Re-index
    row.querySelectorAll('[name]').forEach(el => {
      el.name = el.name.replace(/items\[\d+\]/, `items[${idx}]`);
    });
  });
  document.getElementById('subtotalDisplay').textContent = sub.toFixed(2);
}

document.getElementById('addItem').addEventListener('click', () => {
  const body = document.getElementById('itemsBody');
  const tr = document.createElement('tr');
  tr.className = 'item-row';
  tr.innerHTML = `<td><input type="text" name="items[${itemIdx}][description]" class="form-control form-control-sm" placeholder="Item description"></td>
    <td><input type="number" name="items[${itemIdx}][qty]" class="form-control form-control-sm item-qty" step="0.01" min="0" value="1"></td>
    <td><input type="text" name="items[${itemIdx}][unit]" class="form-control form-control-sm" value="pcs"></td>
    <td><input type="number" name="items[${itemIdx}][unit_price]" class="form-control form-control-sm item-price" step="0.01" min="0" value="0"></td>
    <td class="item-total small pt-2">0.00</td>
    <td><button type="button" class="btn btn-sm btn-outline-danger remove-item"><i class="bi bi-trash"></i></button></td>`;
  body.appendChild(tr);
  itemIdx++;
  attachRemove(tr);
  recalc();
});

function attachRemove(row) {
  row.querySelector('.remove-item').addEventListener('click', () => { row.remove(); recalc(); });
}

document.querySelectorAll('#itemsBody .remove-item').forEach(btn => {
  attachRemove(btn.closest('.item-row'));
});

document.getElementById('itemsBody').addEventListener('input', recalc);
recalc();
</script>

<?= $this->endSection() ?>
