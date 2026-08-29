<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div><h1><i class="bi bi-pencil-square me-2 text-primary"></i>Edit <?= esc($wo['wo_number']) ?></h1></div>
  <a href="<?= base_url('workorders/view/' . $wo['id']) ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<?= form_open('workorders/update/' . $wo['id'], ['class' => 'needs-validation', 'novalidate' => true]) ?>
<div class="row g-3">

  <div class="col-lg-8">

    <!-- Details -->
    <div class="fm-form-section">
      <h6><i class="bi bi-file-text"></i> Work Order Details</h6>
      <div class="mb-3">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" value="<?= old('title', $wo['title']) ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="4"><?= old('description', $wo['description']) ?></textarea>
      </div>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Facility</label>
          <select name="facility_id" class="form-select" required>
            <?php foreach ($facilities as $f): ?>
            <option <?= $wo['facility_id'] == $f['id'] ? 'selected' : '' ?> value="<?= $f['id'] ?>"><?= esc($f['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Asset</label>
          <select name="asset_id" class="form-select">
            <option value="">None</option>
            <?php foreach ($assets as $a): ?>
            <option <?= ($wo['asset_id'] ?? null) == $a['id'] ? 'selected' : '' ?> value="<?= $a['id'] ?>"><?= esc($a['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Maintenance Type</label>
          <select name="type" class="form-select">
            <?php foreach (['corrective' => 'Corrective', 'preventive' => 'Preventive (PM)', 'predictive' => 'Predictive', 'breakdown' => 'Breakdown', 'inspection' => 'Inspection', 'emergency' => 'Emergency', 'project' => 'Project'] as $k => $v): ?>
            <option <?= $wo['type'] == $k ? 'selected' : '' ?> value="<?= $k ?>"><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Category</label>
          <select name="category" class="form-select">
            <option value="">— None —</option>
            <?php foreach (['electrical' => 'Electrical', 'hvac' => 'HVAC', 'plumbing' => 'Plumbing', 'cleaning' => 'Cleaning', 'civil' => 'Civil', 'it' => 'IT', 'fire_safety' => 'Fire Safety', 'security' => 'Security', 'other' => 'Other'] as $k => $v): ?>
            <option <?= ($wo['category'] ?? '') == $k ? 'selected' : '' ?> value="<?= $k ?>"><?= $v ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Priority</label>
          <select name="priority" class="form-select">
            <?php foreach (['critical', 'high', 'medium', 'low'] as $p): ?>
            <option <?= $wo['priority'] == $p ? 'selected' : '' ?> value="<?= $p ?>"><?= ucfirst($p) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Status</label>
          <div class="form-control bg-light">
            <span class="fm-badge badge-status-<?= esc($wo['status']) ?>"><?= ucfirst(str_replace('_', ' ', $wo['status'])) ?></span>
            <span class="small text-muted ms-2">Updated by workflow only</span>
          </div>
        </div>
        <div class="col-md-6">
          <label class="form-label">Assign Technician</label>
          <select name="assigned_to" class="form-select">
            <option value="">Unassigned</option>
            <?php foreach ($technicians as $t): ?>
            <option <?= $wo['assigned_to'] == $t['id'] ? 'selected' : '' ?> value="<?= $t['id'] ?>"><?= esc($t['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Vendor</label>
          <select name="vendor_id" class="form-select">
            <option value="">No Vendor</option>
            <?php foreach ($vendors as $v): ?>
            <option <?= ($wo['vendor_id'] ?? '') == $v['id'] ? 'selected' : '' ?> value="<?= $v['id'] ?>"><?= esc($v['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Planned Start</label>
          <input type="datetime-local" name="planned_start" class="form-control" value="<?= $wo['planned_start'] ? date('Y-m-d\TH:i', strtotime($wo['planned_start'])) : '' ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Planned End</label>
          <input type="datetime-local" name="planned_end" class="form-control" value="<?= $wo['planned_end'] ? date('Y-m-d\TH:i', strtotime($wo['planned_end'])) : '' ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label">Estimated Cost (<?= $currency ?>)</label>
          <input type="number" name="estimated_cost" class="form-control" value="<?= $wo['estimated_cost'] ?>" step="0.01">
        </div>
        <div class="col-md-6">
          <label class="form-label">Actual Cost (<?= $currency ?>)</label>
          <input type="number" name="actual_cost" class="form-control" value="<?= $wo['actual_cost'] ?>" step="0.01">
        </div>
        <div class="col-12">
          <label class="form-label">Completion Notes</label>
          <textarea name="completion_notes" class="form-control" rows="3"><?= old('completion_notes', $wo['completion_notes']) ?></textarea>
        </div>
      </div>
    </div>

  </div>

  <div class="col-lg-4">
    <!-- Requester -->
    <div class="fm-form-section">
      <h6><i class="bi bi-person-lines-fill"></i> Requester Details</h6>
      <div class="mb-2">
        <label class="form-label">Name</label>
        <input type="text" name="requester_name" class="form-control" value="<?= old('requester_name', $wo['requester_name'] ?? '') ?>">
      </div>
      <div class="mb-2">
        <label class="form-label">Phone</label>
        <input type="text" name="requester_phone" class="form-control" value="<?= old('requester_phone', $wo['requester_phone'] ?? '') ?>">
      </div>
      <div class="mb-2">
        <label class="form-label">Email</label>
        <input type="email" name="requester_email" class="form-control" value="<?= old('requester_email', $wo['requester_email'] ?? '') ?>">
      </div>
    </div>

    <!-- Info panel -->
    <div class="fm-form-section">
      <h6><i class="bi bi-info-circle"></i> Record Info</h6>
      <div class="small text-muted">
        <div class="mb-1"><strong>WO #:</strong> <?= esc($wo['wo_number']) ?></div>
        <div class="mb-1"><strong>Created:</strong> <?= date('d M Y H:i', strtotime($wo['created_at'])) ?></div>
        <?php if ($wo['started_at']): ?><div class="mb-1"><strong>Started:</strong> <?= date('d M Y H:i', strtotime($wo['started_at'])) ?></div><?php endif; ?>
        <?php if ($wo['completed_at']): ?><div class="mb-1"><strong>Completed:</strong> <?= date('d M Y H:i', strtotime($wo['completed_at'])) ?></div><?php endif; ?>
        <div class="mb-1"><strong>SLA Due:</strong> <?= $wo['sla_due'] ? date('d M Y H:i', strtotime($wo['sla_due'])) : '—' ?></div>
      </div>
    </div>
  </div>

</div>

<div class="d-flex gap-2 mt-3">
  <button type="submit" class="btn btn-fm-primary"><i class="bi bi-check-lg me-2"></i>Save Changes</button>
  <a href="<?= base_url('workorders/view/' . $wo['id']) ?>" class="btn btn-fm-outline">Cancel</a>
</div>
<?= form_close() ?>
<?= $this->endSection() ?>
