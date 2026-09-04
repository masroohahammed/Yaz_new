<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-tools me-2 text-primary"></i>Work Orders</h1>
    <p class="text-muted small mb-0">All maintenance and service work orders</p>
  </div>
  <?php if (in_array(session()->get('user_role'), ['super_admin', 'facility_manager'])): ?>
  <a href="<?= base_url('workorders/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Work Order</a>
  <?php endif; ?>
</div>

<?php helper('fm'); ?>
<!-- KPI Row -->
<?php if (fm_can_view_kpis()): ?>
<div class="row g-3 mb-3">
  <div class="col-6 col-md-3">
    <div class="kpi-card kpi-blue">
      <div class="d-flex align-items-center justify-content-between">
        <div><div class="kpi-label">Open</div><div class="kpi-value"><?= $kpi['open'] ?></div></div>
        <div class="kpi-icon"><i class="bi bi-folder2-open"></i></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kpi-card kpi-teal">
      <div class="d-flex align-items-center justify-content-between">
        <div><div class="kpi-label">In Progress</div><div class="kpi-value"><?= $kpi['in_progress'] ?></div></div>
        <div class="kpi-icon"><i class="bi bi-gear-wide-connected"></i></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kpi-card kpi-red">
      <div class="d-flex align-items-center justify-content-between">
        <div><div class="kpi-label">SLA Breached</div><div class="kpi-value"><?= $kpi['overdue'] ?></div></div>
        <div class="kpi-icon"><i class="bi bi-exclamation-triangle"></i></div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kpi-card kpi-green">
      <div class="d-flex align-items-center justify-content-between">
        <div><div class="kpi-label">Completed</div><div class="kpi-value"><?= $kpi['completed'] ?></div></div>
        <div class="kpi-icon"><i class="bi bi-check-circle"></i></div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Filters -->
<div class="fm-card mb-3">
  <?= form_open('workorders', ['method' => 'get', 'class' => 'fm-card-body']) ?>
  <div class="row g-2 align-items-end">
    <div class="col-md-3">
      <input type="text" name="search" class="form-control form-control-sm" placeholder="Search WO#, title, requester..." value="<?= esc($filters['search'] ?? '') ?>">
    </div>
    <div class="col-md-2">
      <select name="status" class="form-select form-select-sm">
        <option value="">All Statuses</option>
        <?php foreach (['new', 'assigned', 'in_progress', 'on_hold', 'completed', 'closed', 'cancelled'] as $s): ?>
        <option <?= ($filters['status'] ?? '') == $s ? 'selected' : '' ?> value="<?= $s ?>"><?= ucfirst(str_replace('_', ' ', $s)) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="priority" class="form-select form-select-sm">
        <option value="">All Priorities</option>
        <?php foreach (['critical', 'high', 'medium', 'low'] as $p): ?>
        <option <?= ($filters['priority'] ?? '') == $p ? 'selected' : '' ?> value="<?= $p ?>"><?= ucfirst($p) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="type" class="form-select form-select-sm">
        <option value="">All Types</option>
        <?php foreach (['corrective', 'preventive', 'predictive', 'breakdown', 'inspection', 'emergency', 'project'] as $t): ?>
        <option <?= ($filters['type'] ?? '') == $t ? 'selected' : '' ?> value="<?= $t ?>"><?= ucfirst($t) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <select name="category" class="form-select form-select-sm">
        <option value="">All Categories</option>
        <?php foreach (['electrical', 'hvac', 'plumbing', 'cleaning', 'civil', 'it', 'fire_safety', 'security', 'other'] as $c): ?>
        <option <?= ($filters['category'] ?? '') == $c ? 'selected' : '' ?> value="<?= $c ?>"><?= ucfirst(str_replace('_', ' ', $c)) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-1">
      <button type="submit" class="btn btn-fm-primary btn-sm w-100"><i class="bi bi-search"></i></button>
    </div>
  </div>
  <?= form_close() ?>
</div>

<!-- Table -->
<div class="fm-card">
  <div class="fm-card-body p-0">
    <div class="table-responsive">
      <table class="fm-table">
        <thead>
          <tr>
            <th>WO #</th>
            <th>Title</th>
            <th>Category</th>
            <th>Type</th>
            <th>Facility / Asset</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Assigned To</th>
            <th>Planned Start</th>
            <th>SLA Due</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($workOrders as $w): ?>
          <?php $slaPast = $w['sla_due'] && strtotime($w['sla_due']) < time() && !in_array($w['status'], ['completed', 'closed', 'cancelled']); ?>
          <tr class="<?= $w['sla_breached'] ? 'sla-warn' : '' ?>">
            <td>
              <a href="<?= base_url('workorders/view/' . $w['id']) ?>" class="fw-semibold text-primary"><?= esc($w['wo_number']) ?></a>
              <?php if (($w['approval_status'] ?? 'approved') === 'pending'): ?>
              <span class="fm-badge" style="background:#fff3cd;color:#856404;font-size:.6rem">Pending Approval</span>
              <?php endif; ?>
            </td>
            <td class="small text-truncate" style="max-width:160px" title="<?= esc($w['title']) ?>"><?= esc($w['title']) ?></td>
            <td class="small text-muted"><?= $w['category'] ? ucfirst(str_replace('_', ' ', $w['category'])) : '—' ?></td>
            <td class="small"><span class="fm-badge badge-type-<?= $w['type'] ?>"><?= ucfirst($w['type']) ?></span></td>
            <td class="small text-muted">
              <?= esc($w['facility_name']) ?>
              <?php if ($w['asset_name']): ?><br><span class="text-muted x-small"><i class="bi bi-cpu me-1"></i><?= esc(substr($w['asset_name'], 0, 20)) ?></span><?php endif; ?>
            </td>
            <td><span class="fm-badge badge-priority-<?= $w['priority'] ?>"><?= ucfirst($w['priority']) ?></span></td>
            <td><span class="fm-badge badge-status-<?= $w['status'] ?>"><?= ucfirst(str_replace('_', ' ', $w['status'])) ?></span></td>
            <td class="small"><?= esc($w['assigned_name'] ?? '—') ?></td>
            <td class="small text-muted"><?= $w['planned_start'] ? date('d M Y', strtotime($w['planned_start'])) : '—' ?></td>
            <td class="small <?= $slaPast ? 'text-danger fw-bold' : '' ?>">
              <?= $w['sla_due'] ? date('d M H:i', strtotime($w['sla_due'])) : '—' ?>
              <?= $slaPast ? '<i class="bi bi-exclamation-triangle ms-1"></i>' : '' ?>
            </td>
            <td>
              <div class="d-flex gap-1">
                <a href="<?= base_url('workorders/view/' . $w['id']) ?>" class="btn-action bg-primary bg-opacity-10 text-primary" title="View"><i class="bi bi-eye"></i></a>
                <?php if (in_array(session()->get('user_role'), ['super_admin', 'facility_manager'])): ?>
                <a href="<?= base_url('workorders/edit/' . $w['id']) ?>" class="btn-action bg-warning bg-opacity-10 text-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($workOrders)): ?>
          <tr><td colspan="11" class="text-center py-5 text-muted"><i class="bi bi-inbox fs-2 d-block mb-2"></i>No work orders found</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
