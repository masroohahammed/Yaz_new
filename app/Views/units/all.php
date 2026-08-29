<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-grid me-2"></i>Units</h1>
    <p class="text-muted small mb-0">All units in your company facilities</p>
  </div>
  <a href="<?= base_url('facilities') ?>" class="btn btn-fm-outline btn-sm">Open a property to add units</a>
</div>

<div class="row g-3 mb-3">
  <div class="col-6 col-md-3"><div class="kpi-card kpi-primary"><div class="kpi-label">Total</div><div class="kpi-value"><?= (int) $kpi['total'] ?></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-green"><div class="kpi-label">Occupied</div><div class="kpi-value"><?= (int) $kpi['occupied'] ?></div><div class="kpi-sub"><?= (int) $kpi['occupancy_pct'] ?>%</div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-teal"><div class="kpi-label">Vacant</div><div class="kpi-value"><?= (int) $kpi['vacant'] ?></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-orange"><div class="kpi-label">Maintenance</div><div class="kpi-value"><?= (int) $kpi['maintenance'] ?></div></div></div>
</div>

<div class="fm-card mb-3">
  <div class="fm-card-body py-2">
    <form method="get" class="row g-2 align-items-end">
      <div class="col-md-4">
        <label class="form-label small mb-1">Search</label>
        <input type="search" name="search" class="form-control form-control-sm" value="<?= esc($search) ?>" placeholder="Unit, property, tenant">
      </div>
      <div class="col-md-3">
        <label class="form-label small mb-1">Status</label>
        <select name="status" class="form-select form-select-sm">
          <option value="">All</option>
          <?php foreach (['occupied', 'vacant', 'maintenance'] as $s): ?>
          <option value="<?= $s ?>" <?= $statusFilter === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <button class="btn btn-fm-primary btn-sm" type="submit">Filter</button>
      </div>
    </form>
  </div>
</div>

<div class="fm-card">
  <div class="table-responsive">
    <table class="fm-table table-sm mb-0">
      <thead><tr><th>Unit</th><th>Property</th><th>Type</th><th>Tenant</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($units as $u): ?>
        <tr>
          <td class="fw-semibold"><?= esc($u['unit_number']) ?></td>
          <td class="small"><?= esc($u['facility_name'] ?? '') ?></td>
          <td class="small"><?= esc($u['unit_type'] ?? '') ?></td>
          <td class="small"><?= esc($u['tenant_name'] ?? '—') ?></td>
          <td><span class="fm-badge badge-status-<?= esc($u['status'] ?? '') ?>"><?= esc($u['status'] ?? '') ?></span></td>
          <td class="text-end"><a href="<?= base_url('units/view/' . (int) $u['id']) ?>" class="btn btn-sm btn-fm-outline">View</a></td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($units)): ?>
        <tr><td colspan="6" class="text-muted text-center py-4">No units found.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?= $this->endSection() ?>
