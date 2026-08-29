<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
helper('fm');
$rbac = new \App\Services\RbacService(\Config\Database::connect());
$role = (string) (session()->get('user_role') ?? 'client');
$canAddUnit = $rbac->can($role, 'units.create');
$canEditUnit = $rbac->can($role, 'units.edit');
$canViewUnit = $rbac->can($role, 'units.view');
?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-grid me-2"></i><?= esc($facility['name']) ?> — Units</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= base_url('facilities') ?>">Facilities</a></li>
      <li class="breadcrumb-item"><a href="<?= fm_property_url((int) $facility['id']) ?>"><?= esc($facility['name']) ?></a></li>
      <li class="breadcrumb-item"><a href="<?= fm_property_units_url((int) $facility['id']) ?>">Units</a></li>
      <li class="breadcrumb-item active">Units</li>
    </ol></nav>
  </div>
  <?php if ($canAddUnit): ?>
  <a href="<?= fm_property_url((int) $facility['id'], 'units/create') ?>" class="btn btn-fm-primary btn-sm">
    <i class="bi bi-plus me-1"></i>Add Unit
  </a>
  <?php endif; ?>
</div>

<!-- KPI Strip -->
<div class="row g-3 mb-3">
  <div class="col-6 col-md-3"><div class="kpi-card kpi-primary"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-grid"></i></div><div><div class="kpi-label">Total Units</div><div class="kpi-value"><?= $kpi['total'] ?></div></div></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-green"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-person-check"></i></div><div><div class="kpi-label">Occupied</div><div class="kpi-value"><?= $kpi['occupied'] ?></div><div class="kpi-sub"><?= $kpi['occupancy_pct'] ?>% rate</div></div></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-teal"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-house"></i></div><div><div class="kpi-label">Vacant</div><div class="kpi-value"><?= $kpi['vacant'] ?></div></div></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-orange"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-tools"></i></div><div><div class="kpi-label">Maintenance</div><div class="kpi-value"><?= $kpi['maintenance'] ?></div></div></div></div></div>
</div>

<!-- Filter & View -->
<div class="fm-card mb-3">
  <div class="fm-card-body py-2 d-flex gap-2 flex-wrap align-items-center justify-content-between">
    <div class="d-flex gap-2 flex-wrap align-items-center">
      <div class="small fw-semibold text-muted me-1">Filter:</div>
      <?php foreach([''=>'All',  'occupied'=>'Occupied','vacant'=>'Vacant','maintenance'=>'Maintenance'] as $v=>$l): ?>
      <a href="?<?= $v ? 'status='.$v.'&' : '' ?>view=<?= esc($viewMode ?? 'grid') ?>" class="btn btn-sm <?= $statusFilter===$v?'btn-fm-primary':'btn-outline-secondary' ?>"><?= $l ?></a>
      <?php endforeach; ?>
    </div>
    <div class="d-flex gap-1">
      <a href="?<?= $statusFilter ? 'status='.esc($statusFilter).'&' : '' ?>view=grid" class="btn btn-sm <?= ($viewMode ?? 'grid') === 'grid' ? 'btn-fm-primary' : 'btn-outline-secondary' ?>" title="Grid view"><i class="bi bi-grid-3x3-gap"></i></a>
      <a href="?<?= $statusFilter ? 'status='.esc($statusFilter).'&' : '' ?>view=list" class="btn btn-sm <?= ($viewMode ?? 'grid') === 'list' ? 'btn-fm-primary' : 'btn-outline-secondary' ?>" title="List view"><i class="bi bi-list-ul"></i></a>
    </div>
  </div>
</div>

<!-- Units -->
<?php if(empty($units)): ?>
<div class="fm-card"><div class="fm-card-body text-center py-5">
  <i class="bi bi-grid fs-1 text-muted d-block mb-3"></i>
  <p class="text-muted">No units found. <a href="<?= base_url('facilities/'.$facility['id'].'/units/create') ?>">Add the first unit</a>.</p>
</div></div>
<?php else: ?>
<?php if(($viewMode ?? 'grid') === 'list'): ?>
<div class="fm-card">
  <div class="fm-card-body p-0">
    <table class="fm-table">
      <thead><tr><th>Unit</th><th>Type</th><?php if (!empty($hasParkingUnits)): ?><th>Plate No.</th><?php endif; ?><th>Status</th><th>Tenant</th><th>Contract End</th><th>Rent</th><th></th></tr></thead>
      <tbody>
      <?php foreach($units as $u):
        $expiring = $u['contract_end'] && strtotime($u['contract_end']) < strtotime('+30 days') && $u['status']==='occupied';
        $isParking = strtolower((string)($u['unit_type'] ?? '')) === 'parking';
      ?>
      <tr class="<?= $expiring?'sla-warn':'' ?>">
        <td>
          <?php if ($canViewUnit): ?>
          <a href="<?= fm_unit_view_url((int) $u['id']) ?>" class="fw-semibold text-primary">Unit <?= esc($u['unit_number']) ?></a>
          <?php else: ?>
          Unit <?= esc($u['unit_number']) ?>
          <?php endif; ?>
          <?php if($u['floor']): ?><br><span class="x-small text-muted">Floor <?= esc($u['floor']) ?></span><?php endif; ?>
        </td>
        <td class="small"><?= esc(ucfirst($u['unit_type'] ?? '—')) ?></td>
        <?php if (!empty($hasParkingUnits)): ?>
        <td class="small"><?= $isParking ? esc($u['plate_number'] ?? '—') : '—' ?></td>
        <?php endif; ?>
        <td><span class="fm-badge badge-status-<?= esc($u['status']) ?>"><?= ucfirst($u['status']) ?></span></td>
        <td class="small"><?= esc($u['tenant_name'] ?? '—') ?></td>
        <td class="small <?= $expiring?'text-danger fw-bold':'' ?>"><?= $u['contract_end'] ? date('d M Y', strtotime($u['contract_end'])) : '—' ?></td>
        <td class="small"><?= $u['rent_amount'] ? $currency.' '.number_format($u['rent_amount'],0) : '—' ?></td>
        <td>
          <?php if ($canViewUnit): ?>
          <a href="<?= fm_unit_view_url((int) $u['id']) ?>" class="btn-action bg-primary text-white" title="View"><i class="bi bi-eye"></i></a>
          <?php if ($isParking): ?>
          <a href="<?= base_url('units/'.$u['id'].'/parking-contract') ?>" class="btn-action bg-danger text-white ms-1" title="Parking contract"><i class="bi bi-file-earmark-pdf"></i></a>
          <?php endif; ?>
          <?php endif; ?>
          <?php if ($canEditUnit): ?>
          <a href="<?= base_url('units/edit/'.$u['id']) ?>" class="btn-action bg-secondary text-white ms-1" title="Edit"><i class="bi bi-pencil"></i></a>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php else: ?>
<div class="row g-3">
<?php foreach($units as $u):
  $statusColor = ['occupied'=>'success','vacant'=>'teal','maintenance'=>'orange'][$u['status']] ?? 'secondary';
  $expiring = $u['contract_end'] && strtotime($u['contract_end']) < strtotime('+30 days') && $u['status']==='occupied';
  $isParking = strtolower((string)($u['unit_type'] ?? '')) === 'parking';
?>
<div class="col-md-4 col-lg-3">
  <div class="fm-card h-100 <?= $expiring?'border border-warning':'' ?>">
    <div class="fm-card-body">
      <!-- Unit Header -->
      <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
          <div class="fw-bold" style="font-size:1.1rem">Unit <?= esc($u['unit_number']) ?></div>
          <?php if($u['floor']): ?><div class="x-small text-muted">Floor <?= esc($u['floor']) ?></div><?php endif; ?>
          <?php if($u['unit_type']): ?><div class="x-small text-muted"><?= esc(ucfirst($u['unit_type'])) ?></div><?php endif; ?>
          <?php if($isParking): ?><div class="x-small text-muted"><i class="bi bi-car-front me-1"></i><?= esc($u['plate_number'] ?? '—') ?></div><?php endif; ?>
        </div>
        <span class="fm-badge badge-status-<?= esc($u['status']) ?>"><?= ucfirst($u['status']) ?></span>
      </div>

      <!-- Tenant Info -->
      <?php if($u['status']==='occupied' && $u['tenant_name']): ?>
      <div class="fm-form-section mb-2 py-2 px-2">
        <div class="x-small text-muted mb-1">TENANT</div>
        <div class="small fw-semibold"><i class="bi bi-person me-1 text-primary"></i><?= esc($u['tenant_name']) ?></div>
        <?php if($u['tenant_mobile']): ?><div class="x-small text-muted"><i class="bi bi-telephone me-1"></i><?= esc($u['tenant_mobile']) ?></div><?php endif; ?>
        <?php if($u['contract_end']): ?>
        <?php $days = (int)ceil((strtotime($u['contract_end'])-time())/86400); ?>
        <div class="x-small <?= $days<30?'text-danger fw-bold':($days<60?'text-warning':'text-muted') ?>">
          <i class="bi bi-calendar me-1"></i>Exp: <?= date('d M Y',strtotime($u['contract_end'])) ?>
          <?= $days<30?"(⚠️ $days days)":'' ?>
        </div>
        <?php endif; ?>
      </div>
      <?php elseif($u['owner_name']): ?>
      <div class="small text-muted mb-2"><i class="bi bi-person me-1"></i><?= esc($u['owner_name']) ?></div>
      <?php endif; ?>

      <!-- Rent -->
      <?php if($u['rent_amount']): ?>
      <div class="small mb-2"><span class="text-muted">Rent:</span> <strong><?= $currency ?> <?= number_format($u['rent_amount'],0) ?>/mo</strong></div>
      <?php endif; ?>

      <?php if($expiring): ?>
      <div class="alert alert-warning py-1 px-2 mb-2" style="font-size:.72rem"><i class="bi bi-exclamation-triangle me-1"></i>Contract expiring soon</div>
      <?php endif; ?>

      <!-- Actions -->
      <div class="d-flex gap-1 mt-auto pt-2 flex-wrap">
        <?php if ($canViewUnit): ?>
        <a href="<?= fm_unit_view_url((int) $u['id']) ?>" class="btn btn-fm-primary btn-sm flex-fill"><i class="bi bi-eye me-1"></i>View</a>
        <?php if ($isParking): ?>
        <a href="<?= base_url('units/'.$u['id'].'/parking-contract') ?>" class="btn btn-sm btn-danger" title="Parking contract"><i class="bi bi-file-earmark-pdf"></i></a>
        <?php endif; ?>
        <?php endif; ?>
        <?php if ($canEditUnit): ?>
        <a href="<?= base_url('units/edit/'.$u['id']) ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-pencil"></i></a>
        <?php endif; ?>
        <?php if($u['status']==='vacant'): ?>
        <a href="<?= base_url('units/checklist/'.$u['id'].'/move_in') ?>" class="btn btn-sm btn-success" title="Move-In"><i class="bi bi-box-arrow-in-right"></i></a>
        <?php elseif($u['status']==='occupied'): ?>
        <a href="<?= base_url('units/checklist/'.$u['id'].'/move_out') ?>" class="btn btn-sm btn-warning" title="Move-Out"><i class="bi bi-box-arrow-right"></i></a>
        <?php endif; ?>
        <a href="<?= base_url('workorders/create?facility_id='.$facility['id'].'&unit_id='.$u['id']) ?>" class="btn btn-sm btn-outline-secondary" title="New Work Order"><i class="bi bi-tools"></i></a>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<?= $this->endSection() ?>
