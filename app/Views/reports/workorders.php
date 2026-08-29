<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-tools me-2"></i>Work Order Report</h1></div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('reports/export/workorders/csv') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-filetype-csv me-1"></i>CSV</a>
    <a href="<?= base_url('reports/export/workorders/excel') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
  </div>
</div>
<form method="get" class="fm-card mb-3"><div class="fm-card-body py-2"><div class="row g-2 align-items-end">
  <div class="col-md-2"><label class="form-label small">From</label><input type="date" name="from" class="form-control form-control-sm" value="<?= $from ?>"></div>
  <div class="col-md-2"><label class="form-label small">To</label><input type="date" name="to" class="form-control form-control-sm" value="<?= $to ?>"></div>
  <div class="col-md-3"><label class="form-label small">Facility</label><select name="facility" class="form-select form-select-sm"><option value="">All</option><?php foreach($facilities as $f): ?><option value="<?= $f['id'] ?>" <?= $filterFacility==$f['id']?'selected':'' ?>><?= esc($f['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-2"><label class="form-label small">Status</label><select name="status" class="form-select form-select-sm"><option value="">All</option><?php foreach(['new','assigned','in_progress','completed','cancelled'] as $s): ?><option value="<?= $s ?>" <?= $filterStatus===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-2"><label class="form-label small">Priority</label><select name="priority" class="form-select form-select-sm"><option value="">All</option><?php foreach(['critical','high','medium','low'] as $p): ?><option value="<?= $p ?>" <?= $filterPriority===$p?'selected':'' ?>><?= ucfirst($p) ?></option><?php endforeach; ?></select></div>
  <div class="col-auto"><button type="submit" class="btn btn-fm-primary btn-sm">Filter</button></div>
</div></div></form>
<div class="row g-3 mb-3">
  <div class="col-6 col-md-3"><div class="kpi-card kpi-primary"><div class="d-flex gap-3 align-items-center"><div class="kpi-icon"><i class="bi bi-list"></i></div><div><div class="kpi-label">Total</div><div class="kpi-value"><?= $stats['total'] ?></div></div></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-green"><div class="d-flex gap-3 align-items-center"><div class="kpi-icon"><i class="bi bi-check-circle"></i></div><div><div class="kpi-label">Completed</div><div class="kpi-value"><?= $stats['completed'] ?></div><div class="kpi-sub"><?= $stats['total']>0?round($stats['completed']/$stats['total']*100).'%':0 ?></div></div></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-red"><div class="d-flex gap-3 align-items-center"><div class="kpi-icon"><i class="bi bi-exclamation-circle"></i></div><div><div class="kpi-label">SLA Breached</div><div class="kpi-value"><?= $stats['breached'] ?></div></div></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-secondary"><div class="d-flex gap-3 align-items-center"><div class="kpi-icon"><i class="bi bi-currency-dollar"></i></div><div><div class="kpi-label">Total Cost</div><div class="kpi-value"><?= $currency ?> <?= number_format($stats['cost']/1000,1) ?>K</div></div></div></div></div>
</div>
<div class="fm-card"><div class="fm-card-body p-0">
  <?php if(empty($wos)): ?><p class="text-center py-4 text-muted small">No work orders found for selected filters.</p><?php else: ?>
  <table class="fm-table">
    <thead><tr><th>WO #</th><th>Title</th><th>Facility</th><th>Status</th><th>Priority</th><th>Assigned</th><th>Created</th><th>Completed</th><th>Cost</th><th>SLA</th></tr></thead>
    <tbody>
    <?php foreach($wos as $w): ?>
    <tr>
      <td class="fw-bold small"><a href="<?= base_url('workorders/view/'.$w['id']) ?>" class="text-primary"><?= esc($w['wo_number']) ?></a></td>
      <td class="small"><?= esc(substr($w['title'],0,30)) ?></td>
      <td class="small text-muted"><?= esc($w['facility_name']??'—') ?></td>
      <td><span class="fm-badge badge-status-<?= esc($w['status']) ?>"><?= ucfirst(str_replace('_',' ',$w['status'])) ?></span></td>
      <td><span class="fm-badge badge-priority-<?= esc($w['priority']) ?>"><?= ucfirst($w['priority']) ?></span></td>
      <td class="small text-muted"><?= esc($w['assigned_name']??'—') ?></td>
      <td class="small text-muted"><?= date('d M Y',strtotime($w['created_at'])) ?></td>
      <td class="small text-muted"><?= $w['completed_at']?date('d M Y',strtotime($w['completed_at'])):'—' ?></td>
      <td class="small"><?= $w['actual_cost']?$currency.' '.number_format($w['actual_cost'],0):'—' ?></td>
      <td><?= $w['sla_breached']?'<span class="fm-badge badge-status-overdue">Breached</span>':'<span class="fm-badge badge-status-active">Met</span>' ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div></div>

<div class="fm-card mt-3">
  <div class="card-header-fm">
    <h5 class="mb-0"><i class="bi bi-journal-text me-2"></i>Activity Log (Work Orders & Job Cards)</h5>
    <span class="small text-muted"><?= count($activityLogs ?? []) ?> events in date range</span>
  </div>
  <div class="fm-card-body p-0">
    <?php if (empty($activityLogs)): ?>
    <p class="text-center py-3 text-muted small mb-0">No logged activity for this period.</p>
    <?php else: ?>
    <div class="table-responsive">
      <table class="fm-table table-sm mb-0">
        <thead><tr><th>When</th><th>User</th><th>Action</th><th>Module</th><th>Record</th><th>Detail</th><th>IP</th></tr></thead>
        <tbody>
        <?php foreach ($activityLogs as $l): ?>
        <tr>
          <td class="small text-muted text-nowrap"><?= date('d M Y H:i', strtotime($l['created_at'])) ?></td>
          <td class="small"><?= esc($l['user_name'] ?? 'System') ?></td>
          <td><span class="fm-badge"><?= esc($l['action']) ?></span></td>
          <td class="small"><?= esc($l['module']) ?></td>
          <td class="small"><?php if (!empty($l['record_id']) && ($l['module'] ?? '') === 'work_orders'): ?><a href="<?= base_url('workorders/view/' . (int)$l['record_id']) ?>">#<?= (int)$l['record_id'] ?></a><?php elseif (!empty($l['record_id'])): ?>#<?= (int)$l['record_id'] ?><?php else: ?>—<?php endif; ?></td>
          <td class="small text-muted" style="max-width:280px"><?= esc($l['description'] ?? '') ?></td>
          <td class="x-small text-muted"><?= esc($l['ip_address'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div class="p-2 border-top text-end">
      <a href="<?= base_url('reports/activity-log?from=' . urlencode($from) . '&to=' . urlencode($to) . '&module=work_orders') ?>" class="btn btn-fm-outline btn-sm">Full activity log</a>
    </div>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>
