<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-calendar-check me-2 text-primary"></i>Maintenance Schedule</h1>
    <p class="text-muted small mb-0">Preventive &amp; Predictive maintenance work orders</p>
  </div>
  <?php if (in_array(session()->get('user_role'), ['super_admin', 'facility_manager'])): ?>
  <a href="<?= base_url('workorders/create?type=preventive') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Schedule PM</a>
  <?php endif; ?>
</div>

<?php helper('fm'); ?>
<!-- KPI -->
<?php if (fm_can_view_kpis()): ?>
<div class="row g-3 mb-3">
  <?php
  $total     = count($upcoming);
  $overdue   = count(array_filter($upcoming, fn($w) => $w['sla_due'] && strtotime($w['sla_due']) < time()));
  $dueToday  = count(array_filter($upcoming, fn($w) => $w['sla_due'] && date('Y-m-d', strtotime($w['sla_due'])) === date('Y-m-d')));
  $dueWeek   = count(array_filter($upcoming, fn($w) => $w['sla_due'] && strtotime($w['sla_due']) <= strtotime('+7 days') && strtotime($w['sla_due']) >= time()));
  ?>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-blue"><div class="kpi-label">Scheduled</div><div class="kpi-value"><?= $total ?></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-red"><div class="kpi-label">Overdue</div><div class="kpi-value"><?= $overdue ?></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-gold"><div class="kpi-label">Due Today</div><div class="kpi-value"><?= $dueToday ?></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-teal"><div class="kpi-label">Due This Week</div><div class="kpi-value"><?= $dueWeek ?></div></div></div>
</div>
<?php endif; ?>

<div class="fm-card">
  <div class="fm-card-body p-0">
    <div class="table-responsive">
      <table class="fm-table">
        <thead>
          <tr>
            <th>WO Number</th>
            <th>Title</th>
            <th>Type</th>
            <th>Facility</th>
            <th>Asset</th>
            <th>Assigned</th>
            <th>Planned Start</th>
            <th>Planned End</th>
            <th>SLA Due</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($upcoming as $wo): ?>
          <?php
          $overdue  = $wo['sla_due'] && strtotime($wo['sla_due']) < time();
          $today    = $wo['sla_due'] && date('Y-m-d', strtotime($wo['sla_due'])) === date('Y-m-d');
          $rowClass = $overdue ? 'sla-warn' : '';
          ?>
          <tr class="<?= $rowClass ?>">
            <td class="fw-semibold">
              <a href="<?= base_url('workorders/view/' . $wo['id']) ?>" class="text-primary"><?= esc($wo['wo_number']) ?></a>
            </td>
            <td class="small text-truncate" style="max-width:160px"><?= esc($wo['title']) ?></td>
            <td><span class="fm-badge" style="background:#e0f2fe;color:#0277bd"><?= ucfirst($wo['type']) ?></span></td>
            <td class="small"><?= esc($wo['facility_name']) ?></td>
            <td class="small text-muted"><?= esc($wo['asset_name'] ?? '—') ?></td>
            <td class="small"><?= esc($wo['assigned_name'] ?? 'Unassigned') ?></td>
            <td class="small text-muted"><?= ($wo['planned_start'] ?? false) ? date('d M Y', strtotime($wo['planned_start'])) : '—' ?></td>
            <td class="small text-muted"><?= ($wo['planned_end'] ?? false) ? date('d M Y', strtotime($wo['planned_end'])) : '—' ?></td>
            <td class="small <?= $overdue ? 'text-danger fw-bold' : ($today ? 'text-warning fw-bold' : '') ?>">
              <?= $wo['sla_due'] ? date('d M Y H:i', strtotime($wo['sla_due'])) : '—' ?>
              <?= $overdue ? ' ⚠' : '' ?>
            </td>
            <td><span class="fm-badge badge-priority-<?= $wo['priority'] ?>"><?= ucfirst($wo['priority']) ?></span></td>
            <td><span class="fm-badge badge-status-<?= $wo['status'] ?>"><?= ucfirst(str_replace('_', ' ', $wo['status'])) ?></span></td>
            <td>
              <div class="d-flex gap-1">
                <a href="<?= base_url('workorders/view/' . $wo['id']) ?>" class="btn-action bg-primary bg-opacity-10 text-primary" title="View"><i class="bi bi-eye"></i></a>
                <?php if (in_array(session()->get('user_role'), ['super_admin', 'facility_manager'])): ?>
                <a href="<?= base_url('workorders/edit/' . $wo['id']) ?>" class="btn-action bg-warning bg-opacity-10 text-warning" title="Edit"><i class="bi bi-pencil"></i></a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($upcoming)): ?>
          <tr><td colspan="12" class="text-center py-5 text-muted"><i class="bi bi-calendar-x fs-2 d-block mb-2"></i>No preventive / predictive maintenance scheduled</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
