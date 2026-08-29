<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div><h1><i class="bi bi-plus-circle me-2 text-primary"></i>Create Work Order</h1></div>
  <a href="<?= base_url('workorders') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<?php if (session()->getFlashdata('errors') || isset($errors)): ?>
<div class="alert alert-danger mb-3">
  <ul class="mb-0 ps-3">
    <?php foreach ((session()->getFlashdata('errors') ?? $errors ?? []) as $e): ?>
    <li><?= esc($e) ?></li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<?= form_open_multipart('workorders/store', ['class' => 'needs-validation', 'novalidate' => true]) ?>
<div class="row g-3">

  <!-- LEFT COLUMN -->
  <div class="col-lg-8">

    <!-- Basic Info -->
    <div class="fm-form-section">
      <h6><i class="bi bi-file-text"></i> Work Order Information</h6>
      <div class="mb-3">
        <label class="form-label">Title <span class="text-danger">*</span></label>
        <input type="text" name="title" class="form-control" value="<?= old('title') ?>" required placeholder="Brief description of the work required">
      </div>
      <div class="mb-3">
        <label class="form-label">Description / Scope of Work</label>
        <textarea name="description" class="form-control" rows="4" placeholder="Detailed description, findings, scope..."><?= old('description') ?></textarea>
      </div>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Unit <span class="text-muted small">(optional)</span></label>
          <select name="unit_id" id="wo_unit_id" class="form-select">
            <option value="">— No specific unit —</option>
            <?php foreach ($units ?? [] as $u): ?>
            <option value="<?= (int)$u['id'] ?>"
                    data-facility="<?= (int)($u['facility_id'] ?? 0) ?>"
                    <?= (string)old('unit_id') === (string)$u['id'] ? 'selected' : '' ?>>
              <?= esc($u['unit_number']) ?><?= !empty($u['facility_name']) ? ' · ' . esc($u['facility_name']) : '' ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Facility <span class="text-muted small">(optional)</span></label>
          <select name="facility_id" id="wo_facility_id" class="form-select">
            <option value="">— No facility —</option>
            <?php foreach ($facilities as $f): ?>
            <option value="<?= $f['id'] ?>" <?= (string)(old('facility_id') ?: ($prefillFacilityId ?? '')) === (string)$f['id'] ? 'selected' : '' ?>><?= esc($f['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Linked Asset <span class="text-muted small">(optional)</span></label>
          <select name="asset_id" id="wo_asset_id" class="form-select">
            <option value="">No specific asset</option>
            <?php foreach ($assets as $a): ?>
            <option value="<?= $a['id'] ?>" data-facility="<?= (int)($a['facility_id'] ?? 0) ?>"
                    <?= (string)(old('asset_id') ?: ($prefillAssetId ?? '')) === (string)$a['id'] ? 'selected' : '' ?>>
              <?= esc($a['name'] . ' (' . $a['asset_code'] . ')') ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Assign Supervisor <span class="text-muted small">(optional)</span></label>
          <select name="supervisor_id" class="form-select">
            <option value="">— Unassigned —</option>
            <?php foreach ($supervisors ?? [] as $s): ?>
            <option value="<?= $s['id'] ?>" <?= old('supervisor_id') == $s['id'] ? 'selected' : '' ?>><?= esc($s['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>

    <!-- Classification -->
    <div class="fm-form-section">
      <h6><i class="bi bi-sliders"></i> Classification & Priority</h6>
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Maintenance Type <span class="text-danger">*</span></label>
          <select name="type" class="form-select" required>
            <option value="corrective"  <?= old('type') == 'corrective'  ? 'selected' : '' ?>>Corrective</option>
            <option value="preventive"  <?= old('type') == 'preventive'  ? 'selected' : '' ?>>Preventive (PM)</option>
            <option value="predictive"  <?= old('type') == 'predictive'  ? 'selected' : '' ?>>Predictive</option>
            <option value="breakdown"   <?= old('type') == 'breakdown'   ? 'selected' : '' ?>>Breakdown</option>
            <option value="inspection"  <?= old('type') == 'inspection'  ? 'selected' : '' ?>>Inspection</option>
            <option value="emergency"   <?= old('type') == 'emergency'   ? 'selected' : '' ?>>Emergency</option>
            <option value="project"     <?= old('type') == 'project'     ? 'selected' : '' ?>>Project</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Category <span class="text-danger">*</span></label>
          <select name="category" class="form-select" required>
            <option value="">Select Category</option>
            <?php foreach (['electrical' => 'Electrical', 'hvac' => 'HVAC', 'plumbing' => 'Plumbing', 'cleaning' => 'Cleaning', 'civil' => 'Civil', 'it' => 'IT', 'fire_safety' => 'Fire Safety', 'security' => 'Security', 'other' => 'Other'] as $k => $v): ?>
            <option value="<?= $k ?>" <?= old('category') == $k ? 'selected' : '' ?>><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Priority <span class="text-danger">*</span></label>
          <select name="priority" class="form-select" required>
            <option value="low"      <?= old('priority') == 'low'      ? 'selected' : '' ?>>Low</option>
            <option value="medium"   <?= old('priority', 'medium') == 'medium' ? 'selected' : '' ?>>Medium</option>
            <option value="high"     <?= old('priority') == 'high'     ? 'selected' : '' ?>>High</option>
            <option value="critical" <?= old('priority') == 'critical' ? 'selected' : '' ?>>Critical / Emergency</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Assignment & Scheduling -->\n    <div class="fm-form-section">\n      <h6><i class="bi bi-person-gear"></i> Assignment & Scheduling</h6>\n      <div class="row g-3">\n        <div class="col-md-6">\n          <label class="form-label">Assign Technician</label>\n          <select name="assigned_to" class="form-select">\n            <option value="">Unassigned</option>\n            <?php foreach ($technicians as $t): ?>\n            <option value="<?= $t['id'] ?>" <?= old('assigned_to') == $t['id'] ? 'selected' : '' ?>><?= esc($t['name']) ?></option>\n            <?php endforeach; ?>\n          </select>\n        </div>\n        <div class="col-md-6">\n          <label class="form-label">Assign Vendor <span class="text-muted small">(optional)</span></label>\n          <select name="vendor_id" class="form-select">\n            <option value="">No Vendor</option>\n            <?php foreach ($vendors ?? [] as $v): ?>\n            <option value="<?= $v['id'] ?>" <?= old('vendor_id') == $v['id'] ? 'selected' : '' ?>><?= esc($v['name']) ?></option>\n            <?php endforeach; ?>\n          </select>\n        </div>
        <div class="col-md-6">
          <label class="form-label">Planned Start Date / Time</label>
          <input type="datetime-local" name="planned_start" class="form-control" value="<?= old('planned_start') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Planned End Date / Time</label>
          <input type="datetime-local" name="planned_end" class="form-control" value="<?= old('planned_end') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Estimated Cost (<?= $currency ?>)</label>
          <input type="number" name="estimated_cost" class="form-control" placeholder="0.00" step="0.01" value="<?= old('estimated_cost') ?>">
        </div>
      </div>
    </div>

    <!-- Attachment -->
    <div class="fm-form-section">
      <h6><i class="bi bi-paperclip"></i> Initial Attachment (Photo / Document)</h6>
      <input type="file" name="attachment" class="form-control" accept="image/*,.pdf,.doc,.docx">
      <div class="form-text">Optional — upload a photo or document related to this work order.</div>
    </div>

  </div>

  <!-- RIGHT COLUMN -->
  <div class="col-lg-4">

    <!-- Requester Details -->
    <div class="fm-form-section">
      <h6><i class="bi bi-person-lines-fill"></i> Requester Details</h6>
      <div class="mb-2">
        <label class="form-label">Requester Name</label>
        <input type="text" name="requester_name" class="form-control" value="<?= old('requester_name') ?>" placeholder="Full name">
      </div>
      <div class="mb-2">
        <label class="form-label">Phone</label>
        <input type="text" name="requester_phone" class="form-control" value="<?= old('requester_phone') ?>" placeholder="+974 XXXX XXXX">
      </div>
      <div class="mb-2">
        <label class="form-label">Email</label>
        <input type="email" name="requester_email" class="form-control" value="<?= old('requester_email') ?>" placeholder="email@example.com">
      </div>
    </div>

    <!-- SLA Info (read-only preview) -->
    <div class="fm-form-section">
      <h6><i class="bi bi-clock-history"></i> SLA Reference</h6>
      <div class="small text-muted">
        <div class="d-flex justify-content-between py-1 border-bottom"><span>Critical</span><strong>Resolve in 4h</strong></div>
        <div class="d-flex justify-content-between py-1 border-bottom"><span>High</span><strong>Resolve in 12h</strong></div>
        <div class="d-flex justify-content-between py-1 border-bottom"><span>Medium</span><strong>Resolve in 24h</strong></div>
        <div class="d-flex justify-content-between py-1"><span>Low</span><strong>Resolve in 72h</strong></div>
      </div>
    </div>

  </div>
</div>

<div class="d-flex gap-2 mt-3">
  <button type="submit" class="btn btn-fm-primary"><i class="bi bi-check-lg me-2"></i>Create Work Order</button>
  <a href="<?= base_url('workorders') ?>" class="btn btn-fm-outline">Cancel</a>
</div>
<?= form_close() ?>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
(function () {
  const unitSel    = document.getElementById('wo_unit_id');
  const facSel     = document.getElementById('wo_facility_id');
  const assetSel   = document.getElementById('wo_asset_id');

  // Unit → auto-fill Facility
  unitSel?.addEventListener('change', function () {
    const fid = this.selectedOptions[0]?.dataset.facility;
    if (fid && fid !== '0' && facSel) facSel.value = fid;
  });

  // Facility → filter assets to matching facility (show all if no facility)
  facSel?.addEventListener('change', function () {
    const fid = this.value;
    Array.from(assetSel?.options ?? []).forEach(opt => {
      if (!opt.value) return; // keep "No asset"
      opt.hidden = fid && opt.dataset.facility && opt.dataset.facility !== fid;
    });
    // Reset asset if now hidden
    if (assetSel?.selectedOptions[0]?.hidden) assetSel.value = '';
  });
})();
</script>
<?= $this->endSection() ?>
