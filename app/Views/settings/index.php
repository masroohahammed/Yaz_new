<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-gear me-2"></i>System Settings</h1>
    <div class="small text-muted mt-1">Changes reflect everywhere: login, header, invoices, PDFs &amp; checklists.</div>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <a href="<?= base_url('settings/users') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-people me-1"></i>Users</a>
    <a href="<?= base_url('settings/companies') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-buildings me-1"></i>Companies</a>
    <a href="<?= base_url('settings/roles') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-shield-lock me-1"></i>Roles</a>
    <a href="<?= base_url('settings/workflow') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-diagram-3 me-1"></i>Workflow</a>
    <a href="<?= base_url('reports/activity-log') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-activity me-1"></i>Activity Log</a>
    <a href="<?= base_url('settings/finance-module') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-diagram-3 me-1"></i>Finance Modules</a>
  </div>
</div>

<?= form_open_multipart(base_url('settings/update')) ?>
<div class="row g-3">

  <!-- LEFT COLUMN -->
  <div class="col-lg-6">

    <!-- BRANDING -->
    <div class="fm-form-section">
      <h6><i class="bi bi-palette"></i>Branding &amp; Identity</h6>

      <div class="mb-3">
        <label class="form-label">Company Name</label>
        <input type="text" name="company_name" class="form-control" value="<?= esc($settings['company_name'] ?? 'FM ERP') ?>" required>
        <div class="form-text">Appears on login page, sidebar, topbar, invoices, and all PDFs.</div>
      </div>

      <div class="mb-3">
        <label class="form-label">Company Tagline / Sub-title</label>
        <input type="text" name="company_tagline" class="form-control" value="<?= esc($settings['company_tagline'] ?? 'Facility Management ERP') ?>" placeholder="Facility Management ERP">
        <div class="form-text">Shown below the company name on the login page and sidebar.</div>
      </div>

      <div class="mb-3">
        <label class="form-label">Company Address</label>
        <textarea name="company_address" class="form-control" rows="2" placeholder="Building, street, city, country"><?= esc($settings['company_address'] ?? '') ?></textarea>
        <div class="form-text">Printed on invoices, job cards, and PDF documents.</div>
      </div>
      <div class="row g-2 mb-3">
        <div class="col-md-6">
          <label class="form-label">Phone</label>
          <input type="text" name="company_phone" class="form-control" value="<?= esc($settings['company_phone'] ?? '') ?>" placeholder="+974 …">
        </div>
        <div class="col-md-6">
          <label class="form-label">Email</label>
          <input type="email" name="company_email" class="form-control" value="<?= esc($settings['company_email'] ?? '') ?>" placeholder="accounts@company.com">
        </div>
      </div>

      <!-- Logo upload + preview -->
      <div class="mb-3">
        <label class="form-label">Company Logo</label>
        <div class="d-flex align-items-center gap-3 mb-2">
          <?php if (!empty($companyLogoUrl)): ?>
          <div style="background:#f0f4f8;border-radius:10px;padding:10px;border:1px solid #e2e8f0">
            <img src="<?= esc($companyLogoUrl) ?>" alt="Current Logo" style="max-height:50px;max-width:160px;object-fit:contain">
          </div>
          <div class="small text-muted">Current logo</div>
          <?php else: ?>
          <div class="small text-muted fst-italic">No logo uploaded yet — company name shown as text.</div>
          <?php endif; ?>
        </div>
        <input type="file" name="company_logo" class="form-control" accept="image/png,image/jpeg,image/gif,image/svg+xml,image/webp" onchange="previewLogo(this)">
        <div class="form-text">PNG, JPG, SVG, or WebP recommended. Max 2MB. Logo replaces icon text in sidebar &amp; login.</div>
        <div id="logoPreviewWrap" class="mt-2" style="display:none">
          <div class="small text-muted mb-1">Preview:</div>
          <div style="background:#f0f4f8;border-radius:10px;padding:10px;border:1px dashed #c7ba9a;display:inline-block">
            <img id="logoPreview" src="" alt="Preview" style="max-height:50px;max-width:160px;object-fit:contain">
          </div>
        </div>
      </div>

      <!-- Color pickers -->
      <div class="row g-2">
        <div class="col-6">
          <label class="form-label">Primary Color</label>
          <div class="input-group">
            <input type="color" name="primary_color" class="form-control form-control-color" value="<?= esc($settings['primary_color'] ?? '#76002b') ?>" style="width:50px;padding:3px" oninput="updateColorPreview(this,'primarySwatch','primaryHex')">
            <span class="input-group-text" id="primarySwatch" style="background:<?= esc($settings['primary_color'] ?? '#76002b') ?>;width:36px"></span>
            <input type="text" id="primaryHex" class="form-control" value="<?= esc($settings['primary_color'] ?? '#76002b') ?>" style="max-width:90px" oninput="syncColorPicker(this,'primary_color')">
          </div>
          <div class="form-text">Sidebar, buttons, headings</div>
        </div>
        <div class="col-6">
          <label class="form-label">Secondary / Accent Color</label>
          <div class="input-group">
            <input type="color" name="secondary_color" class="form-control form-control-color" value="<?= esc($settings['secondary_color'] ?? '#c7ba9a') ?>" style="width:50px;padding:3px" oninput="updateColorPreview(this,'secondarySwatch','secondaryHex')">
            <span class="input-group-text" id="secondarySwatch" style="background:<?= esc($settings['secondary_color'] ?? '#c7ba9a') ?>;width:36px"></span>
            <input type="text" id="secondaryHex" class="form-control" value="<?= esc($settings['secondary_color'] ?? '#c7ba9a') ?>" style="max-width:90px" oninput="syncColorPicker(this,'secondary_color')">
          </div>
          <div class="form-text">Active nav highlight, accents</div>
        </div>
      </div>

      <!-- Live preview strip -->
      <div class="mt-3 p-3 rounded-3 border" id="brandPreview" style="background:#fafafa">
        <div class="small fw-bold mb-2 text-muted">Live Preview</div>
        <div class="d-flex align-items-center gap-3 flex-wrap">
          <div id="previewSidebar" class="rounded-2 px-3 py-2 text-white small fw-bold" style="background:<?= esc($settings['primary_color'] ?? '#76002b') ?>">
            <i class="bi bi-buildings me-1"></i><?= esc($settings['company_name'] ?? 'FM ERP') ?>
          </div>
          <button type="button" class="btn btn-sm text-white rounded-2 fw-bold" id="previewBtn" style="background:<?= esc($settings['primary_color'] ?? '#76002b') ?>;border:none">
            Save Settings
          </button>
          <span class="small px-2 py-1 rounded-2 fw-semibold" id="previewAccent" style="background:<?= esc($settings['secondary_color'] ?? '#c7ba9a') ?>;color:#fff">
            Active
          </span>
        </div>
      </div>
    </div>

    <!-- FINANCE SETTINGS -->
    <div class="fm-form-section">
      <h6><i class="bi bi-currency-dollar"></i>Finance Settings</h6>
      <div class="mb-3">
        <label class="form-label">Default Currency</label>
        <select name="currency" class="form-select">
          <?php foreach(['QAR','USD','EUR','GBP','AED','SAR','KWD','BHD','OMR'] as $c): ?>
          <option value="<?= $c ?>" <?= ($settings['currency'] ?? 'QAR') === $c ? 'selected' : '' ?>><?= $c ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-3 d-flex align-items-center justify-content-between">
        <div>
          <label class="form-label mb-0 fw-semibold">Enable VAT</label>
          <div class="small text-muted">Toggle VAT across all invoices, reports &amp; dashboards</div>
        </div>
        <div class="form-check form-switch ms-3">
          <input type="checkbox" name="vat_enabled" class="form-check-input" role="switch"
            style="width:48px;height:24px" value="1" id="vatSwitch"
            <?= ($settings['vat_enabled'] ?? '0') === '1' ? 'checked' : '' ?>
            onchange="document.getElementById('vatRateDiv').style.display=this.checked?'block':'none'">
        </div>
      </div>
      <div class="mb-0" id="vatRateDiv" style="display:<?= ($settings['vat_enabled'] ?? '0') === '1' ? 'block' : 'none' ?>">
        <label class="form-label">VAT Rate (%)</label>
        <input type="number" name="vat_rate" step="0.01" min="0" max="100" class="form-control" value="<?= esc($settings['vat_rate'] ?? '5') ?>">
      </div>
    </div>
  </div>

  <!-- RIGHT COLUMN -->
  <div class="col-lg-6">

    <!-- GENERAL SETTINGS -->
    <div class="fm-form-section">
      <h6><i class="bi bi-globe"></i>General Settings</h6>
      <div class="mb-3">
        <label class="form-label">Timezone</label>
        <select name="timezone" class="form-select">
          <?php foreach(['Asia/Qatar','Asia/Dubai','Asia/Riyadh','Asia/Kuwait','Asia/Bahrain','Asia/Muscat','Europe/London','UTC'] as $tz): ?>
          <option value="<?= $tz ?>" <?= ($settings['timezone'] ?? 'Asia/Qatar') === $tz ? 'selected' : '' ?>><?= $tz ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <!-- NOTIFICATIONS -->
    <div class="fm-form-section">
      <h6><i class="bi bi-bell"></i>Notifications</h6>
      <div class="mb-0 d-flex align-items-center justify-content-between">
        <div>
          <label class="form-label mb-0">SLA Breach Notifications</label>
          <div class="small text-muted">Alert when SLA is about to breach</div>
        </div>
        <div class="form-check form-switch ms-3">
          <input type="checkbox" name="sla_breach_notify" class="form-check-input" role="switch"
            style="width:48px;height:24px" value="1"
            <?= ($settings['sla_breach_notify'] ?? '1') === '1' ? 'checked' : '' ?>>
        </div>
      </div>
    </div>

    <!-- ALERTS -->
    <div class="fm-form-section">
      <h6><i class="bi bi-bell-fill"></i>Alerts (Email &amp; WhatsApp)</h6>
      <div class="mb-3 d-flex align-items-center justify-content-between">
        <div><label class="form-label mb-0">Email alerts</label><div class="small text-muted">Notify users by email (uses SMTP below)</div></div>
        <div class="form-check form-switch"><input type="checkbox" name="alert_email_enabled" class="form-check-input" value="1" <?= ($settings['alert_email_enabled'] ?? '1') === '1' ? 'checked' : '' ?>></div>
      </div>
      <div class="mb-3 d-flex align-items-center justify-content-between">
        <div><label class="form-label mb-0">WhatsApp webhook</label><div class="small text-muted">POST JSON {phone, message} to your provider</div></div>
        <div class="form-check form-switch"><input type="checkbox" name="alert_whatsapp_enabled" class="form-check-input" value="1" <?= ($settings['alert_whatsapp_enabled'] ?? '0') === '1' ? 'checked' : '' ?>></div>
      </div>
      <div class="mb-3">
        <label class="form-label">WhatsApp webhook URL</label>
        <input type="url" name="alert_whatsapp_webhook" class="form-control" value="<?= esc($settings['alert_whatsapp_webhook'] ?? '') ?>" placeholder="https://…">
      </div>
      <div class="mb-0 d-flex align-items-center justify-content-between">
        <div><label class="form-label mb-0">Require 3-way match before vendor payment</label><div class="small text-muted">Blocks AP pay until PO/GRN/bill matched</div></div>
        <div class="form-check form-switch"><input type="checkbox" name="procurement_match_required" class="form-check-input" value="1" <?= ($settings['procurement_match_required'] ?? '1') === '1' ? 'checked' : '' ?>></div>
      </div>
    </div>

    <!-- EMAIL / SMTP -->
    <div class="fm-form-section">
      <h6><i class="bi bi-envelope"></i>Email (SMTP)</h6>
      <div class="mb-3">
        <label class="form-label">SMTP Host</label>
        <input type="text" name="smtp_host" class="form-control" value="<?= esc($settings['smtp_host'] ?? '') ?>" placeholder="smtp.gmail.com">
      </div>
      <div class="mb-3">
        <label class="form-label">SMTP User</label>
        <input type="email" name="smtp_user" class="form-control" value="<?= esc($settings['smtp_user'] ?? '') ?>">
      </div>
      <div class="mb-0">
        <label class="form-label">SMTP Port</label>
        <input type="number" name="smtp_port" class="form-control" value="<?= esc($settings['smtp_port'] ?? '587') ?>">
      </div>
    </div>

    <button type="submit" class="btn btn-fm-primary w-100 py-2">
      <i class="bi bi-check-lg me-2"></i>Save All Settings
    </button>
    <div class="small text-muted text-center mt-2">
      <i class="bi bi-info-circle me-1"></i>
      Color and branding changes take effect on next page load.
    </div>
  </div>

</div>
<?= form_close() ?>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
function updateColorPreview(picker, swatchId, hexId) {
  const v = picker.value;
  document.getElementById(swatchId).style.background = v;
  document.getElementById(hexId).value = v;
  updateBrandPreview();
}
function syncColorPicker(hexInput, pickerName) {
  const v = hexInput.value;
  if(/^#[0-9a-fA-F]{6}$/.test(v)) {
    document.querySelector('[name="'+pickerName+'"]').value = v;
    updateBrandPreview();
  }
}
function updateBrandPreview() {
  const p = document.querySelector('[name="primary_color"]').value;
  const s = document.querySelector('[name="secondary_color"]').value;
  const name = document.querySelector('[name="company_name"]').value || 'FM ERP';
  document.getElementById('previewSidebar').style.background = p;
  document.getElementById('previewSidebar').innerHTML = '<i class="bi bi-buildings me-1"></i>' + name;
  document.getElementById('previewBtn').style.background = p;
  document.getElementById('previewAccent').style.background = s;
}
function previewLogo(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      document.getElementById('logoPreview').src = e.target.result;
      document.getElementById('logoPreviewWrap').style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
  }
}
// Live preview on name change
document.querySelector('[name="company_name"]').addEventListener('input', updateBrandPreview);
</script>
<?= $this->endSection() ?>
