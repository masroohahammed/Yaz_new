<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$isEdit = ! empty($contract['id']);
$c = $contract ?? [];
$propertyId = (int) ($c['facility_id'] ?? service('request')->getGet('property_id') ?? service('request')->getGet('facility_id') ?? 0);
$unitId = (int) ($c['unit_id'] ?? service('request')->getGet('unit_id') ?? 0);
$tenantId = (int) ($c['tenant_id'] ?? service('request')->getGet('tenant_id') ?? 0);
$formUrl = $isEdit ? base_url('contracts/update/' . $c['id']) : base_url('contracts/store');
?>

<div class="page-header">
  <h1><i class="bi bi-file-earmark-plus me-2"></i><?= $isEdit ? 'Edit Contract' : 'New Contract' ?></h1>
  <nav aria-label="breadcrumb"><ol class="breadcrumb small">
    <li class="breadcrumb-item"><a href="<?= base_url('contracts') ?>">Contracts</a></li>
    <li class="breadcrumb-item active"><?= $isEdit ? 'Edit' : 'Create' ?></li>
  </ol></nav>
</div>

<?php if (session()->getFlashdata('errors')): ?>
<div class="alert alert-danger"><?php foreach ((array) session()->getFlashdata('errors') as $e): ?><div><?= esc($e) ?></div><?php endforeach; ?></div>
<?php endif; ?>

<?= form_open($formUrl) ?>
<?= csrf_field() ?>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="fm-form-section">
      <h6>Parties</h6>
      <div class="row g-2">
        <div class="col-md-6">
          <label class="form-label small">Tenant *</label>
          <select name="tenant_id" id="tenantSelect" class="form-select form-select-sm" required>
            <option value="">— Select tenant —</option>
            <?php foreach ($tenants as $t): ?>
            <option value="<?= $t['id'] ?>" data-blacklisted="<?= (int) ($t['is_blacklisted'] ?? 0) ?>"
              <?= $tenantId === (int) $t['id'] ? 'selected' : '' ?>><?= esc($t['full_name']) ?><?= ! empty($t['is_blacklisted']) ? ' ⚠ BLACKLISTED' : '' ?></option>
            <?php endforeach; ?>
          </select>
          <div id="blacklistWarn" class="alert alert-warning py-1 px-2 small mt-1" style="display:none">This tenant is blacklisted.</div>
        </div>
        <div class="col-md-6">
          <label class="form-label small">Property *</label>
          <select name="property_id" id="propertySelect" class="form-select form-select-sm" required>
            <option value="">— Select property —</option>
            <?php foreach ($properties as $p): ?>
            <option value="<?= $p['id'] ?>" <?= $propertyId === (int) $p['id'] ? 'selected' : '' ?>><?= esc($p['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label small">Unit *</label>
          <select name="unit_id" id="unitSelect" class="form-select form-select-sm" required>
            <option value="">— Select unit —</option>
            <?php if ($unitId): ?><option value="<?= $unitId ?>" selected>Unit #<?= $unitId ?></option><?php endif; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label small">Template</label>
          <select name="template_id" class="form-select form-select-sm">
            <option value="">— Default —</option>
            <?php foreach ($templates as $tm): ?>
            <option value="<?= $tm['id'] ?>" <?= ($c['template_id'] ?? '') == $tm['id'] ? 'selected' : '' ?>><?= esc($tm['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label small">Status</label>
          <select name="status" class="form-select form-select-sm">
            <?php foreach (['draft','active','expired','terminated','renewed'] as $st): ?>
            <option value="<?= $st ?>" <?= ($c['status'] ?? 'active') === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>

    <div class="fm-form-section mt-3">
      <h6>Term & rent</h6>
      <div class="row g-2">
        <div class="col-md-3"><label class="form-label small">Start *</label><input type="date" name="start_date" class="form-control form-control-sm" required value="<?= esc(old('start_date', $c['start_date'] ?? '')) ?>"></div>
        <div class="col-md-3"><label class="form-label small">End *</label><input type="date" name="end_date" class="form-control form-control-sm" required value="<?= esc(old('end_date', $c['end_date'] ?? '')) ?>"></div>
        <div class="col-md-3"><label class="form-label small">Signed</label><input type="date" name="signed_date" class="form-control form-control-sm" value="<?= esc(old('signed_date', $c['signed_date'] ?? '')) ?>"></div>
        <div class="col-md-3"><label class="form-label small">Rent *</label><input type="number" step="0.01" name="rent_amount" class="form-control form-control-sm" required value="<?= esc(old('rent_amount', $c['rent_amount'] ?? '')) ?>"></div>
        <div class="col-md-3"><label class="form-label small">Security deposit</label><input type="number" step="0.01" name="security_deposit" class="form-control form-control-sm" value="<?= esc(old('security_deposit', $c['security_deposit'] ?? '')) ?>"></div>
        <div class="col-md-3">
          <label class="form-label small">Frequency *</label>
          <select name="payment_frequency" class="form-select form-select-sm" required>
            <?php foreach (['monthly'=>'Monthly','quarterly'=>'Quarterly','semi-annual'=>'Semi-annual','annual'=>'Annual'] as $v=>$l): ?>
            <option value="<?= $v ?>" <?= (old('payment_frequency', $c['payment_frequency'] ?? 'monthly') === $v || ($v==='annual' && ($c['payment_frequency']??'')==='yearly')) ? 'selected' : '' ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3"><label class="form-label small">Payment day (1-31)</label><input type="number" min="1" max="31" name="payment_day" class="form-control form-control-sm" value="<?= esc(old('payment_day', $c['payment_day'] ?? '')) ?>"></div>
        <div class="col-md-3">
          <label class="form-label small">Payment type</label>
          <select name="payment_type" class="form-select form-select-sm">
            <?php foreach (['cheque','cash','transfer'] as $pt): ?>
            <option value="<?= $pt ?>" <?= ($c['payment_type'] ?? 'cheque') === $pt ? 'selected' : '' ?>><?= ucfirst($pt) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-3"><label class="form-label small">Late penalty %</label><input type="number" step="0.01" name="late_penalty_pct" class="form-control form-control-sm" value="<?= esc($c['late_penalty_pct'] ?? '') ?>"></div>
        <div class="col-md-3"><label class="form-label small">Grace days</label><input type="number" name="grace_period_days" class="form-control form-control-sm" value="<?= esc($c['grace_period_days'] ?? '') ?>"></div>
        <div class="col-md-3"><label class="form-label small">Discount %</label><input type="number" step="0.01" name="discount_pct" class="form-control form-control-sm" value="<?= esc($c['discount_pct'] ?? '') ?>"></div>
        <div class="col-md-3">
          <label class="form-label small">Pro-rata basis</label>
          <select name="prorata_basis" class="form-select form-select-sm">
            <option value="">None</option>
            <option value="30-day" <?= ($c['prorata_basis'] ?? '') === '30-day' ? 'selected' : '' ?>>30-day</option>
            <option value="actual-days" <?= ($c['prorata_basis'] ?? '') === 'actual-days' ? 'selected' : '' ?>>Actual days</option>
          </select>
        </div>
      </div>
    </div>

    <div class="fm-form-section mt-3">
      <h6>Free period</h6>
      <div class="row g-2">
        <div class="col-md-3"><div class="form-check mt-2"><input type="checkbox" name="has_free_period" value="1" class="form-check-input" id="freeChk" <?= ! empty($c['has_free_period']) ? 'checked' : '' ?>><label class="form-check-label small" for="freeChk">Has free period</label></div></div>
        <div class="col-md-3"><label class="form-label small">Months</label><input type="number" name="free_period_months" class="form-control form-control-sm" value="<?= esc($c['free_period_months'] ?? '') ?>"></div>
        <div class="col-md-3">
          <label class="form-label small">Position</label>
          <select name="free_period_position" class="form-select form-select-sm">
            <option value="beginning" <?= ($c['free_period_position'] ?? '') === 'beginning' ? 'selected' : '' ?>>Beginning</option>
            <option value="ending" <?= ($c['free_period_position'] ?? '') === 'ending' ? 'selected' : '' ?>>Ending</option>
          </select>
        </div>
        <div class="col-md-6"><label class="form-label small">Description</label><input type="text" name="free_period_desc" class="form-control form-control-sm" value="<?= esc($c['free_period_desc'] ?? '') ?>"></div>
      </div>
    </div>

    <div class="fm-form-section mt-3">
      <h6>Utilities & furnished</h6>
      <div class="row g-2">
        <div class="col-md-3"><div class="form-check"><input type="checkbox" name="includes_utilities" value="1" class="form-check-input" <?= ! empty($c['includes_utilities']) ? 'checked' : '' ?>><label class="form-check-label small">Includes utilities</label></div></div>
        <div class="col-md-9"><input type="text" name="utilities_desc" class="form-control form-control-sm" placeholder="Utilities description" value="<?= esc($c['utilities_desc'] ?? '') ?>"></div>
        <div class="col-md-3"><div class="form-check"><input type="checkbox" name="includes_furnished" value="1" class="form-check-input" <?= ! empty($c['includes_furnished']) ? 'checked' : '' ?>><label class="form-check-label small">Includes furnished</label></div></div>
        <div class="col-md-9"><input type="text" name="furnished_desc" class="form-control form-control-sm" placeholder="Furnished description" value="<?= esc($c['furnished_desc'] ?? '') ?>"></div>
      </div>
    </div>

    <div class="fm-form-section mt-3">
      <h6>Multi-year rent schedule</h6>
      <div class="row g-2">
        <?php
        $schedule = $rentSchedule ?? [];
        for ($y = 0; $y < 5; $y++):
          $yrVal = $schedule[$y]['rent_amount'] ?? '';
        ?>
        <div class="col-md-4">
          <label class="form-label small">Year <?= $y + 1 ?></label>
          <input type="number" step="0.01" name="annual_rent[<?= $y ?>]" class="form-control form-control-sm" value="<?= esc(old('annual_rent.'.$y, $yrVal)) ?>">
        </div>
        <?php endfor; ?>
      </div>
    </div>

    <div class="fm-form-section mt-3">
      <h6>Contract text</h6>
      <textarea name="contract_terms" class="form-control form-control-sm mb-2 fm-tinymce" rows="3" placeholder="Clauses / terms"><?= esc($c['contract_terms'] ?? '') ?></textarea>
      <label class="form-label small">Custom EN</label>
      <textarea name="custom_content_en" class="form-control form-control-sm mb-2 fm-tinymce" rows="4"><?= esc($c['custom_content_en'] ?? '') ?></textarea>
      <label class="form-label small">Custom AR</label>
      <textarea name="custom_content_ar" class="form-control form-control-sm mb-2 fm-tinymce-rtl" rows="4"><?= esc($c['custom_content_ar'] ?? '') ?></textarea>
      <label class="form-label small mt-2">Notes</label>
      <textarea name="notes" class="form-control form-control-sm fm-tinymce" rows="2"><?= esc($c['notes'] ?? '') ?></textarea>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="fm-form-section">
      <h6>Deposit</h6>
      <select name="deposit_payment_method" id="depositMethod" class="form-select form-select-sm mb-2">
        <option value="">—</option>
        <?php foreach (['cash','cheque','transfer'] as $dm): ?>
        <option value="<?= $dm ?>" <?= ($c['deposit_payment_method'] ?? '') === $dm ? 'selected' : '' ?>><?= ucfirst($dm) ?></option>
        <?php endforeach; ?>
      </select>
      <input type="text" name="deposit_cheque_no" id="depositChequeNo" class="form-control form-control-sm" placeholder="Cheque number" value="<?= esc($c['deposit_cheque_no'] ?? '') ?>">
    </div>
    <div class="fm-form-section mt-3">
      <h6>VAT & options</h6>
      <div class="form-check mb-2"><input type="checkbox" name="vat_applicable" value="1" class="form-check-input" id="vatChk" <?= ! empty($c['vat_applicable']) ? 'checked' : '' ?>><label class="form-check-label small" for="vatChk">VAT applicable</label></div>
      <input type="number" step="0.01" name="vat_rate" id="vatRate" class="form-control form-control-sm mb-2" placeholder="VAT %" value="<?= esc($c['vat_rate'] ?? '') ?>">
      <div class="form-check mb-2"><input type="checkbox" name="auto_renew" value="1" class="form-check-input" <?= ! empty($c['auto_renew']) ? 'checked' : '' ?>><label class="form-check-label small">Auto renew</label></div>
      <div class="form-check"><input type="checkbox" name="auto_generate_invoices" value="1" class="form-check-input" <?= ! isset($c['auto_generate_invoices']) || ! empty($c['auto_generate_invoices']) ? 'checked' : '' ?>><label class="form-check-label small">Auto-generate invoices on save</label></div>
    </div>
    <div class="d-flex gap-2 mt-3">
      <button type="submit" class="btn btn-fm-primary flex-fill"><?= $isEdit ? 'Update Contract' : 'Save Contract' ?></button>
      <a href="<?= base_url('contracts') ?>" class="btn btn-outline-secondary">Cancel</a>
    </div>
  </div>
</div>
<?= form_close() ?>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<?= $this->include('partials/tinymce') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const prop = document.getElementById('propertySelect');
  const unit = document.getElementById('unitSelect');
  const tenant = document.getElementById('tenantSelect');
  const warn = document.getElementById('blacklistWarn');

  function loadUnits(pid, selected) {
    unit.innerHTML = '<option value="">— Select unit —</option>';
    if (!pid) return;
    fetch('<?= base_url('contracts/ajax/units') ?>/' + pid, { headers: { Accept: 'application/json' } })
      .then(r => r.json())
      .then(data => {
        const rows = data.units || data || [];
        rows.forEach(u => {
          const o = document.createElement('option');
          o.value = u.id;
          o.textContent = (u.unit_number || u.id) + (u.status ? ' (' + u.status + ')' : '');
          if (String(u.id) === String(selected)) o.selected = true;
          unit.appendChild(o);
        });
      });
  }

  prop?.addEventListener('change', () => loadUnits(prop.value, ''));
  if (prop?.value) loadUnits(prop.value, '<?= $unitId ?>');

  tenant?.addEventListener('change', () => {
    const opt = tenant.options[tenant.selectedIndex];
    warn.style.display = opt?.dataset?.blacklisted === '1' ? '' : 'none';
  });
  tenant?.dispatchEvent(new Event('change'));
});
</script>
<?= $this->endSection() ?>
