<?php if (empty($lines) || empty($wo)): return; endif; ?>
<div class="modal fade" id="invoicePrepModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-receipt-cutoff me-2"></i>Invoice Preparation — <?= esc($wo['wo_number']) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <?= form_open(base_url('workorders/prepare-invoice/' . (int) $wo['id']), ['class' => 'fm-submit-form']) ?>
      <?= csrf_field() ?>
      <div class="modal-body">
        <p class="small text-muted">Adjust client descriptions and selling prices. Unit cost column is internal only.</p>
        <div class="table-responsive">
          <table class="table table-sm">
            <thead><tr><th>Description</th><th>Qty</th><th class="text-end">Cost</th><th class="text-end">Sell</th></tr></thead>
            <tbody>
            <?php foreach ($lines as $i => $line): ?>
            <tr>
              <td>
                <input type="hidden" name="lines[<?= $i ?>][line_type]" value="<?= esc($line['line_type']) ?>">
                <input type="hidden" name="lines[<?= $i ?>][sort_order]" value="<?= $i ?>">
                <input type="text" name="lines[<?= $i ?>][description]" class="form-control form-control-sm" value="<?= esc($line['description']) ?>" required>
              </td>
              <td style="width:80px"><input type="number" name="lines[<?= $i ?>][quantity]" class="form-control form-control-sm" step="0.01" min="0.01" value="<?= esc($line['quantity']) ?>"></td>
              <td style="width:100px"><input type="number" name="lines[<?= $i ?>][unit_cost_internal]" class="form-control form-control-sm text-end" step="0.01" value="<?= esc($line['unit_cost_internal']) ?>" readonly></td>
              <td style="width:100px"><input type="number" name="lines[<?= $i ?>][unit_price]" class="form-control form-control-sm text-end" step="0.01" min="0" value="<?= esc($line['unit_price']) ?>"></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-fm-primary fm-submit-btn">Generate draft invoice</button>
      </div>
      <?= form_close() ?>
    </div>
  </div>
</div>
