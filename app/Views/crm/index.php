<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-funnel me-2 text-primary"></i>CRM Leads</h1></div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= base_url('crm/reports') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-bar-chart-line me-1"></i>Reports</a>
    <?php $isKanban = ($filters['view']??'list') === 'kanban'; ?>
    <a href="<?= base_url('crm?view='.($isKanban?'list':'kanban')) ?>" class="btn btn-fm-outline btn-sm">
      <i class="bi bi-<?= $isKanban?'list-ul':'kanban' ?> me-1"></i><?= $isKanban?'List':'Kanban' ?>
    </a>
    <a href="<?= base_url('crm/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>New Lead</a>
  </div>
</div>

<?php if (!empty($migrationRequired)): ?>
  <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>Run database migration for <code><?= esc($missingTable??'crm_leads') ?></code> to enable this module.</div>
<?php else: ?>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<form class="filters-inline form-card mb-3" method="get">
  <input type="hidden" name="view" value="<?= esc($filters['view']??'list') ?>">
  <input type="text" name="search" class="form-control form-control-sm" value="<?= esc($filters['search']??'') ?>" placeholder="Search leads…">
  <select name="stage" class="form-select form-select-sm">
    <option value="">All stages</option>
    <?php foreach ($stages as $s): ?>
      <option value="<?= $s ?>" <?= ($filters['stage']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn btn-fm-outline btn-sm" type="submit">Filter</button>
  <?php if (!empty($filters['search']) || !empty($filters['stage'])): ?>
    <a href="<?= base_url('crm?view='.($filters['view']??'list')) ?>" class="btn btn-fm-outline btn-sm">Clear</a>
  <?php endif; ?>
</form>

<?php if ($isKanban): ?>
<!-- Kanban board -->
<div class="d-flex gap-2 overflow-auto pb-2" style="min-height:60vh">
  <?php
  $stageBadge = ['new'=>'secondary','contacted'=>'info','qualified'=>'primary','viewing'=>'warning','negotiation'=>'warning','won'=>'success','lost'=>'danger'];
  foreach ($stages as $stage):
    $cols = $kanban[$stage] ?? [];
  ?>
  <div style="min-width:220px;flex:0 0 220px">
    <div class="d-flex justify-content-between align-items-center mb-2">
      <span class="badge bg-<?= $stageBadge[$stage]??'secondary' ?>"><?= ucfirst($stage) ?></span>
      <small class="text-muted"><?= count($cols) ?></small>
    </div>
    <?php foreach ($cols as $lead): ?>
    <div class="border rounded p-2 mb-2 small bg-white shadow-sm">
      <a href="<?= base_url('crm/'.$lead['id']) ?>" class="fw-semibold text-decoration-none"><?= esc($lead['full_name']) ?></a>
      <div class="text-muted"><?= esc($lead['lead_number']) ?></div>
      <?php if (!empty($lead['phone'])): ?><div><i class="bi bi-telephone"></i> <?= esc($lead['phone']) ?></div><?php endif; ?>
      <?php if (!empty($lead['preferred_location'])): ?><div><i class="bi bi-geo-alt"></i> <?= esc($lead['preferred_location']) ?></div><?php endif; ?>
      <?php if (!empty($lead['temperature'])): ?>
        <span class="badge bg-<?= $lead['temperature']==='Hot'?'danger':($lead['temperature']==='Warm'?'warning':'secondary') ?> mt-1"><?= esc($lead['temperature']) ?></span>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php if (empty($cols)): ?><div class="text-center text-muted small py-3">Empty</div><?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>
<?php else: ?>
<!-- List view -->
<div class="form-card p-0">
  <table class="table table-registry table-sm mb-0">
    <thead><tr><th>Lead</th><th>Contact</th><th>Interest</th><th>Location</th><th>Stage</th><th>Temp</th><th>Assigned</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($leads as $l): ?>
      <tr>
        <td><a href="<?= base_url('crm/'.$l['id']) ?>"><?= esc($l['lead_number']) ?></a><br><small><?= esc($l['full_name']) ?></small></td>
        <td class="small"><?= esc($l['phone']??'') ?><?php if(!empty($l['email'])): ?><br><?= esc($l['email']) ?><?php endif; ?></td>
        <td><?= esc($l['interest_type']) ?></td>
        <td class="small"><?= esc($l['preferred_location']??'—') ?></td>
        <td><span class="badge bg-<?= ['new'=>'secondary','contacted'=>'info','qualified'=>'primary','viewing'=>'warning','negotiation'=>'warning','won'=>'success','lost'=>'danger'][$l['stage']]??'secondary' ?>"><?= esc($l['stage']) ?></span></td>
        <td><?= esc($l['temperature']??'') ?></td>
        <td class="small"><?= esc($l['assigned_name']??'—') ?></td>
        <td><a href="<?= base_url('crm/'.$l['id'].'/edit') ?>" class="btn btn-sm btn-fm-outline">Edit</a></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($leads)): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">No leads found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
<?php if ($total > $perPage): ?>
<div class="mt-3 d-flex justify-content-between align-items-center">
  <small class="text-muted">Showing <?= count($leads) ?> of <?= $total ?></small>
  <?php $prev=$currentPage-1; $next=$currentPage+1; ?>
  <div>
    <?php if ($prev >= 1): ?><a href="?page=<?= $prev ?>&view=<?= $filters['view']??'list' ?>&search=<?= urlencode($filters['search']??'') ?>&stage=<?= urlencode($filters['stage']??'') ?>" class="btn btn-sm btn-fm-outline">← Prev</a><?php endif; ?>
    <?php if ($next * $perPage <= $total + $perPage): ?><a href="?page=<?= $next ?>&view=<?= $filters['view']??'list' ?>&search=<?= urlencode($filters['search']??'') ?>&stage=<?= urlencode($filters['stage']??'') ?>" class="btn btn-sm btn-fm-outline ms-1">Next →</a><?php endif; ?>
  </div>
</div>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
<?= $this->endSection() ?>
