<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
helper('fm');
$primaryColor = $settings['primary_color'] ?? '#76002b';
$totalUnits   = isset($kpi['total_units'])    ? $kpi['total_units']    : count($units ?? []);
$occupied     = isset($kpi['occupied_units'])  ? $kpi['occupied_units'] : 0;
$occupancyPct = $totalUnits > 0 ? round(($occupied / $totalUnits) * 100) : 0;
$ws           = $workspace ?? session()->get('workspace') ?? 'fm';
$isPm         = in_array($ws, ['pm', 'both'], true);
$isFm         = in_array($ws, ['fm', 'both'], true);
$rbac         = new \App\Services\RbacService(\Config\Database::connect());
$role         = session()->get('user_role') ?? 'client';
$canEditFacility = $rbac->can((string) $role, 'facilities.edit');
$canAddUnit      = $rbac->can((string) $role, 'units.create');
$canEditUnit     = $rbac->can((string) $role, 'units.edit');
$canViewUnit     = $rbac->can((string) $role, 'units.view');
?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-buildings me-2"></i><?= esc($facility['name']) ?></h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= base_url('properties') ?>">Properties</a></li>
      <li class="breadcrumb-item active"><?= esc($facility['name']) ?></li>
    </ol></nav>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <span class="fm-badge badge-status-<?= esc($facility['status']) ?> align-self-center"><?= ucfirst($facility['status']) ?></span>
    <a href="<?= fm_property_units_url((int) $facility['id']) ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-grid me-1"></i>Units</a>
    <?php if ($isFm): ?>
    <a href="<?= base_url('workorders/create?facility_id='.$facility['id']) ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>Work Order</a>
    <?php endif; ?>
    <?php if ($canEditFacility): ?>
    <a href="<?= base_url('facilities/edit/'.$facility['id']) ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-pencil"></i></a>
    <?php endif; ?>
  </div>
</div>

<!-- KPI Row -->
<div class="row g-3 mb-3">
  <div class="col-6 col-md-3"><div class="kpi-card kpi-primary"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-tools"></i></div><div><div class="kpi-label">Open WOs</div><div class="kpi-value"><?= count($openWO ?? []) ?></div></div></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-green"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-grid"></i></div><div><div class="kpi-label">Occupancy</div><div class="kpi-value"><?= $occupancyPct ?>%</div><div class="kpi-sub"><?= $occupied ?>/<?= $totalUnits ?> units</div></div></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-blue"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-cpu"></i></div><div><div class="kpi-label">Assets</div><div class="kpi-value"><?= count($assets ?? []) ?></div></div></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-teal"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-file-earmark-text"></i></div><div><div class="kpi-label">Contracts</div><div class="kpi-value"><?= count($contracts ?? []) ?></div></div></div></div></div>
</div>

<!-- Tabs -->
<ul class="nav fm-entity-tabs mb-0" role="tablist">
  <li class="nav-item"><a class="nav-link active" href="#tab-overview" data-bs-toggle="tab" role="tab"><i class="bi bi-speedometer2 me-1"></i>Overview</a></li>
  <li class="nav-item"><a class="nav-link" href="#tab-units" data-bs-toggle="tab" role="tab"><i class="bi bi-grid me-1"></i>Units <span class="badge bg-secondary ms-1"><?= $totalUnits ?></span></a></li>
  <li class="nav-item"><a class="nav-link" href="#tab-inspections" data-bs-toggle="tab" role="tab"><i class="bi bi-clipboard2-check me-1"></i>Inspection Reports <span class="badge bg-secondary ms-1"><?= count($inspectionReports ?? []) ?></span></a></li>
  <?php if ($isPm): ?>
  <li class="nav-item"><a class="nav-link" href="#tab-finance" data-bs-toggle="tab" role="tab"><i class="bi bi-cash-stack me-1"></i>Finance</a></li>
  <li class="nav-item"><a class="nav-link" href="#tab-leases" data-bs-toggle="tab" role="tab"><i class="bi bi-file-earmark-text me-1"></i>Leases <span class="badge bg-secondary ms-1"><?= count($leaseContracts ?? []) ?></span></a></li>
  <?php endif; ?>
  <?php if ($isFm): ?>
  <li class="nav-item"><a class="nav-link" href="#tab-assets" data-bs-toggle="tab" role="tab"><i class="bi bi-cpu me-1"></i>Assets <span class="badge bg-secondary ms-1"><?= count($assets ?? []) ?></span></a></li>
  <li class="nav-item"><a class="nav-link" href="#tab-wo" data-bs-toggle="tab" role="tab"><i class="bi bi-tools me-1"></i>Work Orders <span class="badge bg-secondary ms-1"><?= count($openWO ?? []) ?></span></a></li>
  <li class="nav-item"><a class="nav-link" href="#tab-maintenance" data-bs-toggle="tab" role="tab"><i class="bi bi-wrench me-1"></i>Maintenance</a></li>
  <?php endif; ?>
  <?php if ($isPm && ! $isFm): ?>
  <li class="nav-item"><a class="nav-link" href="#tab-maintenance" data-bs-toggle="tab" role="tab"><i class="bi bi-wrench me-1"></i>Maintenance <span class="badge bg-secondary">View</span></a></li>
  <?php endif; ?>
  <li class="nav-item"><a class="nav-link" href="#tab-documents" data-bs-toggle="tab" role="tab"><i class="bi bi-folder2-open me-1"></i>Documents <span class="badge bg-secondary ms-1"><?= count($propertyDocuments ?? []) ?></span></a></li>
  <li class="nav-item"><a class="nav-link" href="#tab-qr" data-bs-toggle="tab" role="tab"><i class="bi bi-qr-code me-1"></i>QR Code</a></li>
</ul>

<div class="tab-content fm-tab-pane">

  <!-- Overview Tab -->
  <div class="tab-pane fade show active" id="tab-overview">
    <div class="row g-3">
      <div class="col-md-6">
        <div class="fm-form-section">
          <h6><i class="bi bi-info-circle"></i>Facility Details</h6>
          <div class="small mb-2"><span class="text-muted">Code:</span> <strong><?= esc($facility['code']) ?></strong></div>
          <div class="small mb-2"><span class="text-muted">Address:</span> <?= esc($facility['address']) ?></div>
          <div class="small mb-2"><span class="text-muted">City:</span> <strong><?= esc($facility['city']??'') ?>, <?= esc($facility['country']??'') ?></strong></div>
          <div class="small mb-2"><span class="text-muted">Manager:</span> <strong><?= esc($facility['manager_name']??'—') ?></strong></div>
          <?php if($facility['area_sqm']): ?><div class="small mb-2"><span class="text-muted">Area:</span> <?= number_format($facility['area_sqm'],0) ?> sqm</div><?php endif; ?>
          <?php if($facility['floors']): ?><div class="small"><span class="text-muted">Floors:</span> <?= esc($facility['floors']) ?></div><?php endif; ?>
        </div>
      </div>
      <div class="col-md-6">
        <div class="fm-form-section">
          <h6><i class="bi bi-bar-chart"></i>Occupancy</h6>
          <?php if($totalUnits > 0): ?>
          <div class="mb-2">
            <div class="d-flex justify-content-between small mb-1">
              <span><?= $occupied ?> Occupied</span><span class="fw-bold"><?= $occupancyPct ?>%</span>
            </div>
            <div class="progress" style="height:10px;border-radius:5px">
              <div class="progress-bar" style="width:<?= $occupancyPct ?>%;background:<?= $primaryColor ?>"></div>
            </div>
          </div>
          <div class="row g-2 mt-2">
            <div class="col-4 text-center"><div class="small fw-bold text-success"><?= $occupied ?></div><div class="x-small text-muted">Occupied</div></div>
            <div class="col-4 text-center"><div class="small fw-bold text-warning"><?= ($totalUnits - $occupied) ?></div><div class="x-small text-muted">Vacant</div></div>
            <div class="col-4 text-center"><div class="small fw-bold"><?= $totalUnits ?></div><div class="x-small text-muted">Total</div></div>
          </div>
          <?php else: ?>
          <p class="small text-muted mb-2">No units configured yet.</p>
          <?php endif; ?>
          <a href="<?= fm_property_units_url((int) $facility['id']) ?>" class="btn btn-fm-outline btn-sm w-100 mt-2"><i class="bi bi-grid me-1"></i>Manage Units</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Units Tab -->
  <div class="tab-pane fade" id="tab-units">
    <div class="fm-card cc-card">
      <div class="card-header-fm">
        <h5><i class="bi bi-grid me-2"></i>Units</h5>
        <?php if ($canAddUnit): ?>
        <a href="#" data-bs-toggle="modal" data-bs-target="#unitAddModal" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>Add Unit</a>
        <?php endif; ?>
      </div>
      <div class="fm-card-body p-0">
        <?php if(empty($facilityUnits)): ?>
        <div class="text-center py-4">
          <i class="bi bi-grid fs-2 text-muted d-block mb-2"></i>
          <p class="small text-muted">No units added yet.</p>
          <?php if ($canAddUnit): ?>
          <a href="#" data-bs-toggle="modal" data-bs-target="#unitAddModal" class="btn btn-fm-primary btn-sm">Add First Unit</a>
          <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
        <table class="table table-registry table-sm mb-0">
          <thead><tr><th>Unit</th><th>Type</th><?php if (!empty($hasParkingUnits)): ?><th>Plate No.</th><?php endif; ?><th>Status</th><th>Tenant</th><th>Contract End</th><th>Rent</th><th></th></tr></thead>
          <tbody>
          <?php foreach($facilityUnits as $u):
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
            <td class="small"><?= esc(ucfirst($u['unit_type']??'—')) ?></td>
            <?php if (!empty($hasParkingUnits)): ?>
            <td class="small"><?= $isParking ? esc($u['plate_number'] ?? '—') : '—' ?></td>
            <?php endif; ?>
            <td><span class="fm-badge badge-status-<?= esc($u['status']) ?>"><?= ucfirst($u['status']) ?></span></td>
            <td class="small">
              <?= esc($u['tenant_name']??'—') ?>
              <?php if($u['tenant_mobile']): ?><br><span class="x-small text-muted"><?= esc($u['tenant_mobile']) ?></span><?php endif; ?>
            </td>
            <td class="small <?= $expiring?'text-danger fw-bold':'' ?>">
              <?= $u['contract_end'] ? date('d M Y',strtotime($u['contract_end'])) : '—' ?>
              <?= $expiring?'<br><span class="x-small">⚠️ Expiring</span>':'' ?>
            </td>
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
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Inspection Reports Tab -->
  <div class="tab-pane fade" id="tab-inspections">
    <?= view('partials/inspection_reports_table', [
      'reports' => $inspectionReports ?? [],
      'showUnit' => true,
      'facilityId' => (int) $facility['id'],
    ]) ?>
  </div>

  <?php if ($isPm): ?>
  <!-- Finance tab (PM) -->
  <div class="tab-pane fade" id="tab-finance">
    <div class="form-card">
      <h6 class="text-muted text-uppercase small mb-3">Property finance</h6>
      <div class="row g-3 small">
        <div class="col-md-4"><span class="text-muted">Expected monthly income</span><div class="fw-semibold"><?= $facility['expected_monthly_income'] ? number_format((float)$facility['expected_monthly_income'], 2) : '—' ?></div></div>
        <div class="col-md-4"><span class="text-muted">Landlord share %</span><div class="fw-semibold"><?= esc($facility['landlord_share_pct'] ?? '—') ?></div></div>
        <div class="col-md-4"><span class="text-muted">Management fee %</span><div class="fw-semibold"><?= esc($facility['management_fee_pct'] ?? '—') ?></div></div>
      </div>
      <a href="<?= base_url('properties/edit/'.$facility['id']) ?>" class="btn btn-fm-outline btn-sm mt-3">Edit finance settings</a>
    </div>
  </div>

  <!-- Leases tab (PM) -->
  <div class="tab-pane fade" id="tab-leases">
    <div class="fm-card">
      <div class="card-header-fm d-flex justify-content-between">
        <h5><i class="bi bi-file-earmark-text me-2"></i>Lease contracts</h5>
        <a href="<?= base_url('contracts/create?facility_id='.$facility['id']) ?>" class="btn btn-fm-primary btn-sm">New lease</a>
      </div>
      <div class="fm-card-body p-0">
        <div class="table-responsive">
        <table class="table table-registry table-sm mb-0">
          <thead><tr><th>Contract</th><th>Tenant</th><th>Unit</th><th>Period</th><th>Rent</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach ($leaseContracts ?? [] as $lc): ?>
            <tr>
              <td><a href="<?= base_url('contracts/'.$lc['id']) ?>"><?= esc($lc['contract_number']) ?></a></td>
              <td><?= esc($lc['tenant_name'] ?? '—') ?></td>
              <td><?= esc($lc['unit_number'] ?? '—') ?></td>
              <td class="small"><?= esc($lc['start_date']) ?> → <?= esc($lc['end_date']) ?></td>
              <td><?= number_format((float)$lc['rent_amount'], 0) ?></td>
              <td><span class="fm-badge badge-status-<?= esc($lc['status']) ?>"><?= ucfirst($lc['status']) ?></span></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($leaseContracts)): ?><tr><td colspan="6" class="text-center text-muted py-3">No lease contracts</td></tr><?php endif; ?>
          </tbody>
        </table>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($isFm): ?>
  <div class="tab-pane fade" id="tab-assets">
    <div class="fm-card">
      <div class="card-header-fm">
        <h5><i class="bi bi-cpu me-2"></i>Assets</h5>
        <a href="<?= base_url('asset-register/create?facility_id='.$facility['id']) ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>Add Asset</a>
      </div>
      <div class="fm-card-body p-0">
        <div class="table-responsive">
        <table class="table table-registry table-sm mb-0">
          <thead><tr><th>Code</th><th>Name</th><th>Category</th><th>Status</th><th>Health</th></tr></thead>
          <tbody>
          <?php foreach($assets??[] as $a): ?>
          <tr>
            <td class="small fw-semibold"><?= esc($a['asset_code']) ?></td>
            <td><a href="<?= base_url('asset-register/view/'.$a['id']) ?>" class="text-primary"><?= esc($a['name']) ?></a></td>
            <td class="small"><?= ucfirst(str_replace('_',' ',$a['category'])) ?></td>
            <td><span class="fm-badge badge-status-<?= esc($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
            <td><?php $h=(int)($a['health_score']??100); ?><div class="d-flex align-items-center gap-2"><div class="progress" style="width:50px;height:6px"><div class="progress-bar <?= $h>=80?'bg-success':($h>=50?'bg-warning':'bg-danger') ?>" style="width:<?= $h ?>%"></div></div><span class="small"><?= $h ?>%</span></div></td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($assets)): ?><tr><td colspan="5" class="text-center py-3 text-muted">No assets</td></tr><?php endif; ?>
          </tbody>
        </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Contracts Tab -->
  <div class="tab-pane fade" id="tab-contracts">
    <div class="fm-card">
      <div class="card-header-fm">
        <h5><i class="bi bi-file-earmark-text me-2"></i>Contracts</h5>
        <a href="<?= base_url('finance/contracts/create?facility_id='.$facility['id']) ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>New Contract</a>
      </div>
      <div class="fm-card-body p-0">
        <div class="table-responsive">
        <table class="table table-registry table-sm mb-0">
          <thead><tr><th>Contract #</th><th>Client / Tenant</th><th>Type</th><th>Period</th><th>Value</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach($contracts??[] as $c): $expiring = strtotime($c['end_date']) < strtotime('+60 days') && $c['status']==='active'; ?>
          <tr class="<?= $expiring?'sla-warn':'' ?>">
            <td class="fw-semibold small"><?= esc($c['contract_number']) ?></td>
            <td>
              <div class="small"><?= esc($c['client_name']) ?></div>
              <?php if(!empty($c['client_mobile'])): ?><div class="x-small text-muted"><i class="bi bi-telephone me-1"></i><?= esc($c['client_mobile']) ?></div><?php endif; ?>
              <?php if(!empty($c['client_email'])): ?><div class="x-small text-muted"><i class="bi bi-envelope me-1"></i><?= esc($c['client_email']) ?></div><?php endif; ?>
            </td>
            <td class="small"><?= ucfirst(str_replace('_',' ',$c['contract_type'])) ?></td>
            <td class="small">
              <?= date('d M Y',strtotime($c['start_date'])) ?> →
              <span class="<?= strtotime($c['end_date'])<time()?'text-danger':($expiring?'text-warning':'') ?>"><?= date('d M Y',strtotime($c['end_date'])) ?></span>
              <?= $expiring?'⚠️':'' ?>
            </td>
            <td class="small fw-semibold"><?= $currency ?> <?= number_format($c['value'],0) ?></td>
            <td><span class="fm-badge badge-status-<?= esc($c['status']) ?>"><?= ucfirst($c['status']) ?></span></td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($contracts)): ?><tr><td colspan="6" class="text-center py-3 text-muted">No contracts</td></tr><?php endif; ?>
          </tbody>
        </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Work Orders Tab -->
  <div class="tab-pane fade" id="tab-wo">
    <div class="fm-card">
      <div class="card-header-fm">
        <h5><i class="bi bi-tools me-2"></i>Work Orders</h5>
        <a href="<?= base_url('workorders/create?facility_id='.$facility['id']) ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>New WO</a>
      </div>
      <div class="fm-card-body p-0">
        <div class="table-responsive">
        <table class="table table-registry table-sm mb-0">
          <thead><tr><th>WO #</th><th>Title</th><th>Unit</th><th>Status</th><th>Priority</th><th>Assigned</th></tr></thead>
          <tbody>
          <?php foreach($openWO??[] as $w): ?>
          <tr>
            <td><a href="<?= base_url('workorders/view/'.$w['id']) ?>" class="fw-semibold text-primary"><?= esc($w['wo_number']) ?></a></td>
            <td class="small"><?= esc(substr($w['title'],0,35)) ?></td>
            <td class="small text-muted"><?= isset($w['unit_number']) ? 'Unit '.esc($w['unit_number']) : '—' ?></td>
            <td><span class="fm-badge badge-status-<?= esc($w['status']) ?>"><?= ucfirst(str_replace('_',' ',$w['status'])) ?></span></td>
            <td><span class="fm-badge badge-priority-<?= esc($w['priority']) ?>"><?= ucfirst($w['priority']) ?></span></td>
            <td class="small text-muted"><?= esc($w['assigned_name']??'—') ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($openWO)): ?><tr><td colspan="6" class="text-center py-3 text-muted">No open work orders</td></tr><?php endif; ?>
          </tbody>
        </table>
        </div>
      </div>
    </div>
  </div>

  <div class="tab-pane fade" id="tab-maintenance">
    <div class="fm-card">
      <div class="card-header-fm d-flex justify-content-between">
        <h5><i class="bi bi-wrench me-2"></i>Maintenance <?= ($isPm && ! $isFm) ? '<span class="badge bg-secondary">Read-only</span>' : '' ?></h5>
        <?php if ($isFm): ?><a href="<?= base_url('maintenance/create?facility_id='.$facility['id']) ?>" class="btn btn-fm-primary btn-sm">New request</a><?php endif; ?>
      </div>
      <div class="fm-card-body p-0">
        <div class="table-responsive">
        <table class="table table-registry table-sm mb-0">
          <thead><tr><th>Ticket</th><th>Unit</th><th>Category</th><th>Priority</th><th>Status</th><th>Date</th></tr></thead>
          <tbody>
          <?php foreach ($maintenanceHistory ?? [] as $mr): ?>
            <tr>
              <td><a href="<?= base_url('maintenance/'.$mr['id']) ?>"><?= esc($mr['ticket_number']) ?></a></td>
              <td><?= esc($mr['unit_number'] ?? '—') ?></td>
              <td><?= esc($mr['category']) ?></td>
              <td><?= esc(ucfirst($mr['priority'])) ?></td>
              <td><?= esc(ucfirst($mr['status'])) ?></td>
              <td><?= esc(substr($mr['created_at'], 0, 10)) ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($maintenanceHistory)): ?><tr><td colspan="6" class="text-center text-muted py-3">No maintenance records</td></tr><?php endif; ?>
          </tbody>
        </table>
        </div>
      </div>
    </div>
  </div>

  <?php endif; ?>

  <!-- Documents Tab -->
  <div class="tab-pane fade" id="tab-documents">
    <?= view('documents/panel', [
      'module' => 'facility',
      'refId' => (int) $facility['id'],
      'embed' => true,
      'documents' => $propertyDocuments ?? [],
      'facilityId' => (int) $facility['id'],
    ]) ?>
  </div>

  <!-- QR Code Tab -->
  <div class="tab-pane fade" id="tab-qr">
    <?= view('partials/qr_display', [
      'scanUrl' => $scanUrl ?? '',
      'qrImageUrl' => $qrImageUrl ?? '',
      'entityLabel' => 'Property',
      'printUrl' => base_url('properties/qrcode/' . (int) $facility['id']),
    ]) ?>
  </div>

</div><!-- /.tab-content -->

<?= view('facilities/_unit_modal') ?>
<?= view('partials/entity_tab_hash') ?>
<?= $this->endSection() ?>
