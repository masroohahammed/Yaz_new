<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-door-open me-2 text-primary"></i>Move-In / Move-Out Inspections</h1>
    <div class="small text-muted">Unit checklists for tenant handover — updates unit status on completion.</div>
  </div>
  <a href="<?= base_url('compliance') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-arrow-left me-1"></i>Compliance</a>
</div>

<div class="fm-card mb-3">
  <div class="fm-card-body py-3">
    <form method="get" class="row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label small">Facility</label>
        <select name="facility_id" class="form-select form-select-sm">
          <option value="">All facilities</option>
          <?php foreach ($facilities as $f): ?>
          <option value="<?= $f['id'] ?>" <?= (int)$filterFacility === (int)$f['id'] ? 'selected' : '' ?>><?= esc($f['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label small">Unit status</label>
        <select name="status" class="form-select form-select-sm">
          <option value="">Any</option>
          <?php foreach (['vacant','occupied','maintenance'] as $s): ?>
          <option value="<?= $s ?>" <?= $filterStatus === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-fm-primary btn-sm w-100">Filter</button>
      </div>
    </form>
  </div>
</div>

<div class="fm-card">
  <div class="fm-card-body p-0">
    <div class="table-responsive">
      <table class="fm-table">
        <thead>
          <tr><th>Facility</th><th>Unit</th><th>Floor</th><th>Status</th><th>Tenant</th><th class="text-end">Inspections</th></tr>
        </thead>
        <tbody>
        <?php foreach ($units as $u): ?>
        <tr>
          <td class="small"><?= esc($u['facility_name']) ?></td>
          <td><a href="<?= base_url('units/view/'.$u['id']) ?>" class="fw-semibold"><?= esc($u['unit_number']) ?></a></td>
          <td class="small"><?= esc($u['floor'] ?? '—') ?></td>
          <td><span class="fm-badge"><?= ucfirst($u['status'] ?? '') ?></span></td>
          <td class="small text-muted"><?= esc($u['tenant_name'] ?? '—') ?></td>
          <td class="text-end text-nowrap inspection-unit-actions">
            <a href="<?= base_url('units/checklist/'.$u['id'].'/move_in') ?>" class="btn btn-sm btn-success"><i class="bi bi-box-arrow-in-right me-1"></i>Move-In</a>
            <a href="<?= base_url('units/checklist/'.$u['id'].'/move_out') ?>" class="btn btn-sm btn-warning"><i class="bi bi-box-arrow-right me-1"></i>Move-Out</a>
            <a href="<?= base_url('units/checklist/'.$u['id'].'/routine') ?>" class="btn btn-sm btn-fm-outline">Routine</a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($units)): ?>
        <tr><td colspan="6" class="text-center py-4 text-muted">No units found. Add units under Facilities → Units.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
