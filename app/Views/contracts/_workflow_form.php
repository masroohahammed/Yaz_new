
<div class="page-header"><h1><?= esc($title) ?></h1></div>

<div class="fm-card p-4">
  <?= form_open($action) ?>
  <?= csrf_field() ?>
  <?php if ($type === 'terminate'): ?>
  <div class="mb-3"><label class="form-label small">Termination date *</label>
    <input type="date" name="termination_date" class="form-control form-control-sm" required value="<?= date('Y-m-d') ?>"></div>
  <div class="mb-3"><label class="form-label small">Reason *</label>
    <textarea name="reason" class="form-control form-control-sm fm-tinymce" rows="4" required></textarea></div>
  <div class="form-check mb-3"><input type="checkbox" name="refund_deposit" value="1" class="form-check-input" id="refundDep"><label for="refundDep" class="form-check-label small">Refund security deposit (creates refund payment)</label></div>
  <button type="submit" class="btn btn-sm btn-danger">Terminate Contract</button>
  <?php elseif ($type === 'renew'): ?>
  <div class="row g-2">
    <div class="col-md-4"><label class="form-label small">New start *</label><input type="date" name="new_start_date" class="form-control form-control-sm" required value="<?= esc($contract['end_date'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label small">New end *</label><input type="date" name="new_end_date" class="form-control form-control-sm" required></div>
    <div class="col-md-4"><label class="form-label small">New rent</label><input type="number" step="0.01" name="new_rent" class="form-control form-control-sm" value="<?= esc($contract['rent_amount'] ?? '') ?>"></div>
  </div>
  <button type="submit" class="btn btn-sm btn-fm-primary mt-3">Renew Contract</button>
  <?php elseif ($type === 'amendment'): ?>
  <div class="row g-2">
    <div class="col-md-4"><label class="form-label small">Effective date</label><input type="date" name="effective_date" class="form-control form-control-sm"></div>
    <div class="col-md-4"><label class="form-label small">New rent</label><input type="number" step="0.01" name="new_rent" class="form-control form-control-sm" value="<?= esc($contract['rent_amount'] ?? '') ?>"></div>
    <div class="col-md-4"><label class="form-label small">New end date</label><input type="date" name="new_end_date" class="form-control form-control-sm" value="<?= esc($contract['end_date'] ?? '') ?>"></div>
    <div class="col-12"><label class="form-label small">Description</label><textarea name="description" class="form-control form-control-sm fm-tinymce" rows="3"></textarea></div>
    <div class="col-12"><label class="form-label small">Custom EN</label><textarea name="custom_content_en" class="form-control form-control-sm fm-tinymce" rows="3"><?= esc($contract['custom_content_en'] ?? '') ?></textarea></div>
    <div class="col-12"><label class="form-label small">Custom AR</label><textarea name="custom_content_ar" class="form-control form-control-sm fm-tinymce-rtl" rows="3"><?= esc($contract['custom_content_ar'] ?? '') ?></textarea></div>
    <?php for ($y = 0; $y < 3; $y++): ?>
    <div class="col-md-4"><label class="form-label small">Year <?= $y+1 ?> rent</label><input type="number" step="0.01" name="annual_rent[<?= $y ?>]" class="form-control form-control-sm"></div>
    <?php endfor; ?>
    <div class="col-12"><div class="form-check"><input type="checkbox" name="refresh_payment_schedule" value="1" class="form-check-input" checked><label class="form-check-label small">Regenerate pending payments</label></div></div>
  </div>
  <button type="submit" class="btn btn-sm btn-fm-primary mt-3">Save Amendment</button>
  <?php endif; ?>
  <a href="<?= base_url('contracts/' . $contract['id']) ?>" class="btn btn-sm btn-outline-secondary ms-2">Cancel</a>
  <?= form_close() ?>
</div>
