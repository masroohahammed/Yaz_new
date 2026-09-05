<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$primaryColor = $settings['primary_color'] ?? '#76002b';
helper('fm');
$daysLeft       = fm_contract_days_until($unit['contract_end'] ?? null);
$daysUntil      = ($daysLeft !== null && $daysLeft > 0) ? $daysLeft : null;
$daysExpiredAgo = ($daysLeft !== null && $daysLeft < 0) ? abs($daysLeft) : null;
$isExpired      = $daysExpiredAgo !== null;
$expWarning     = $daysUntil !== null && $daysUntil <= 30 && $unit['status'] === 'occupied';
$expCritical    = $daysUntil !== null && $daysUntil <= 7 && $unit['status'] === 'occupied';
$rbac         = new \App\Services\RbacService(\Config\Database::connect());
$canEditUnit  = $rbac->can((string) (session()->get('user_role') ?? 'client'), 'units.edit');
$canViewUnit  = $rbac->can((string) (session()->get('user_role') ?? 'client'), 'units.view');
$renewUrl = fm_unit_renew_url($unit, $activeLeaseContract ?? null);
$parkingContractUrl = fm_unit_parking_contract_url((int) $unit['id'], isset($activeLeaseContract['id']) ? (int) $activeLeaseContract['id'] : null);
?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-grid me-2"></i>Unit <?= esc($unit['unit_number']) ?></h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= base_url('facilities') ?>">Facilities</a></li>
      <li class="breadcrumb-item"><a href="<?= base_url('facilities/view/'.$unit['facility_id']) ?>"><?= esc($unit['facility_name']) ?></a></li>
      <li class="breadcrumb-item"><a href="<?= base_url('facilities/'.$unit['facility_id'].'/units') ?>">Units</a></li>
      <li class="breadcrumb-item active">Unit <?= esc($unit['unit_number']) ?></li>
    </ol></nav>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <span class="fm-badge badge-status-<?= esc($unit['status']) ?> align-self-center"><?= ucfirst($unit['status']) ?></span>
    <?php if ($canEditUnit): ?>
    <a href="<?= base_url('units/edit/'.$unit['id']) ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
    <?php endif; ?>
    <?php if (!empty($isParkingUnit)): ?>
    <a href="<?= esc($parkingContractUrl) ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-file-earmark-pdf me-1"></i>Parking Contract</a>
    <?php if ($unit['status'] === 'occupied' && !empty($unit['contract_end'])): ?>
    <a href="<?= esc($parkingContractUrl . (str_contains($parkingContractUrl, '?') ? '&' : '?') . 'renew=1') ?>" class="btn btn-success btn-sm"><i class="bi bi-arrow-repeat me-1"></i>Renew</a>
    <?php endif; ?>
    <?php endif; ?>
    <?php if($unit['status']==='vacant'): ?>
    <a href="<?= base_url('units/checklist/'.$unit['id'].'/move_in') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-box-arrow-in-right me-1"></i>Move-In</a>
    <?php elseif($unit['status']==='occupied'): ?>
    <a href="<?= base_url('units/checklist/'.$unit['id'].'/move_out') ?>" class="btn btn-sm btn-warning"><i class="bi bi-box-arrow-right me-1"></i>Move-Out</a>
    <?php endif; ?>
    <a href="<?= base_url('workorders/create?facility_id='.$unit['facility_id'].'&unit_id='.$unit['id']) ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-tools me-1"></i>New WO</a>
  </div>
</div>

<!-- Contract Expiry Banner -->
<?php if ($isExpired && $unit['status'] === 'occupied'): ?>
<div class="alert alert-danger d-flex align-items-center gap-2 mb-3">
  <i class="bi bi-exclamation-octagon-fill fs-5"></i>
  <div><strong>Contract expired</strong> on <?= date('d M Y', strtotime($unit['contract_end'])) ?> (<?= $daysExpiredAgo ?> day<?= $daysExpiredAgo === 1 ? '' : 's' ?> ago). Renew to continue tenancy.</div>
  <a href="<?= esc($renewUrl) ?>" class="btn btn-sm btn-danger ms-auto">Renew Now</a>
</div>
<?php elseif($expCritical): ?>
<div class="alert alert-danger d-flex align-items-center gap-2 mb-3">
  <i class="bi bi-exclamation-triangle-fill fs-5"></i>
  <div><strong>Contract expiring in <?= $daysUntil ?> day<?= $daysUntil===1?'':'s' ?>!</strong> Immediate renewal action required.</div>
  <a href="<?= esc($renewUrl) ?>" class="btn btn-sm btn-danger ms-auto">Renew Now</a>
</div>
<?php elseif($expWarning): ?>
<div class="alert alert-warning d-flex align-items-center gap-2 mb-3">
  <i class="bi bi-clock-history fs-5"></i>
  <div>Contract expires on <strong><?= date('d M Y',strtotime($unit['contract_end'])) ?></strong> (<?= $daysUntil ?> days remaining).</div>
  <a href="<?= esc($renewUrl) ?>" class="btn btn-sm btn-warning ms-auto">Renew Contract</a>
</div>
<?php endif; ?>

<?php if (empty($activeLeaseContract) && ($signSql = session()->getFlashdata('sign_sql'))): ?>
<div class="alert alert-warning">
  <div class="small fw-semibold mb-1">Run this SQL in phpMyAdmin to enable digital signatures:</div>
  <pre class="small mb-0" style="white-space:pre-wrap"><?= esc($signSql) ?></pre>
</div>
<?php endif; ?>

<?php if (!empty($activeLeaseContract)): ?>
<?= view('partials/_lease_signature_panel', [
    'lease' => $activeLeaseContract,
    'signLink' => session()->getFlashdata('sign_link'),
    'signatureReady' => $signatureReady ?? null,
]) ?>
<?php elseif (!empty($leaseContracts)): ?>
<div class="alert alert-info small mb-3">
  Open the <a href="<?= base_url('contracts/' . (int) $leaseContracts[0]['id']) ?>">lease contract</a> to generate a tenant signing link.
</div>
<?php endif; ?>

<div class="row g-3">

  <!-- LEFT COLUMN -->
  <div class="col-lg-4">

    <!-- Unit Info -->
    <div class="fm-form-section mb-3">
      <h6><i class="bi bi-info-circle"></i>Unit Details</h6>
      <div class="small mb-2"><span class="text-muted">Facility:</span> <strong><?= esc($unit['facility_name']) ?></strong></div>
      <?php if($unit['floor']): ?><div class="small mb-2"><span class="text-muted">Floor:</span> <strong><?= esc($unit['floor']) ?></strong></div><?php endif; ?>
      <?php if($unit['unit_type']): ?><div class="small mb-2"><span class="text-muted">Type:</span> <?= esc(ucfirst($unit['unit_type'])) ?></div><?php endif; ?>
      <?php if(strtolower((string)($unit['unit_type'] ?? '')) === 'parking'): ?>
      <div class="small mb-2"><span class="text-muted">Plate Number:</span> <strong><?= esc($unit['plate_number'] ?? '—') ?></strong></div>
      <?php endif; ?>
      <?php if($unit['area_sqft']): ?><div class="small mb-2"><span class="text-muted">Area:</span> <?= number_format($unit['area_sqft'],0) ?> sqft</div><?php endif; ?>
    </div>

    <!-- Owner Info -->
    <?php if($unit['owner_name']): ?>
    <div class="fm-form-section mb-3">
      <h6><i class="bi bi-person-badge"></i>Owner</h6>
      <div class="small mb-1 fw-semibold"><?= esc($unit['owner_name']) ?></div>
      <?php if($unit['owner_mobile']): ?><div class="small text-muted"><i class="bi bi-telephone me-1"></i><a href="tel:<?= esc($unit['owner_mobile']) ?>"><?= esc($unit['owner_mobile']) ?></a></div><?php endif; ?>
      <?php if($unit['owner_email']): ?><div class="small text-muted"><i class="bi bi-envelope me-1"></i><a href="mailto:<?= esc($unit['owner_email']) ?>"><?= esc($unit['owner_email']) ?></a></div><?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Tenant Info -->
    <?php if($unit['status']==='occupied'): ?>
    <div class="fm-form-section mb-3" style="border-left:3px solid <?= $primaryColor ?>">
      <h6><i class="bi bi-person-check"></i>Current Tenant</h6>
      <?php if($unit['tenant_name']): ?>
      <div class="small fw-semibold mb-1"><?= esc($unit['tenant_name']) ?></div>
      <?php if($unit['tenant_mobile']): ?><div class="small text-muted mb-1"><i class="bi bi-telephone me-1"></i><a href="tel:<?= esc($unit['tenant_mobile']) ?>"><?= esc($unit['tenant_mobile']) ?></a></div><?php endif; ?>
      <?php if($unit['tenant_email']): ?><div class="small text-muted"><i class="bi bi-envelope me-1"></i><a href="mailto:<?= esc($unit['tenant_email']) ?>"><?= esc($unit['tenant_email']) ?></a></div><?php endif; ?>
      <?php else: ?><p class="small text-muted mb-0">No tenant details recorded.</p><?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Contract Details -->
    <?php if($unit['contract_number'] || $unit['contract_start']): ?>
    <div class="fm-form-section mb-3">
      <h6><i class="bi bi-file-earmark-text"></i>Contract</h6>
      <?php if($unit['contract_number']): ?><div class="small mb-1"><span class="text-muted">Ref:</span> <strong><?= esc($unit['contract_number']) ?></strong></div><?php endif; ?>
      <?php if($unit['contract_start']): ?><div class="small mb-1"><span class="text-muted">Start:</span> <?= date('d M Y',strtotime($unit['contract_start'])) ?></div><?php endif; ?>
      <?php if($unit['contract_end']): ?>
      <div class="small mb-1 <?= $expCritical?'text-danger fw-bold':($expWarning?'text-warning':'') ?>">
        <span class="text-muted">End:</span> <?= date('d M Y',strtotime($unit['contract_end'])) ?>
        <?php if($daysUntil !== null): ?><span class="badge bg-<?= $expCritical?'danger':($expWarning?'warning':'secondary') ?> ms-1"><?= $daysUntil ?>d</span><?php elseif($isExpired): ?><span class="badge bg-danger ms-1">Expired</span><?php endif; ?>
      </div>
      <?php endif; ?>
      <?php if($unit['rent_amount']): ?><div class="small mb-1"><span class="text-muted">Rent:</span> <strong><?= $currency ?> <?= number_format($unit['rent_amount'],0) ?>/mo</strong></div><?php endif; ?>
      <?php if($unit['security_deposit']): ?><div class="small"><span class="text-muted">Deposit:</span> <?= $currency ?> <?= number_format($unit['security_deposit'],0) ?></div><?php endif; ?>
      <?php if(!empty($unit['contract_attachment'])): ?>
      <a href="<?= base_url('file/contracts/'.basename($unit['contract_attachment'])) ?>" target="_blank" class="btn btn-sm btn-outline-secondary mt-2 w-100"><i class="bi bi-paperclip me-1"></i>View Contract</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="fm-form-section">
      <h6><i class="bi bi-lightning-charge"></i>Quick Actions</h6>
      <div class="d-grid gap-2">
        <a href="<?= base_url('workorders/create?facility_id='.$unit['facility_id'].'&unit_id='.$unit['id']) ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-tools me-2"></i>Create Work Order</a>
        <a href="<?= base_url('units/checklist/'.$unit['id'].'/routine') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-clipboard2-check me-2"></i>Routine Inspection</a>
    <a href="<?= base_url('utilities/by-unit/'.$unit['id']) ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-lightning me-2"></i>Utilities</a>
        <a href="<?= !empty($isParkingUnit) ? esc($parkingContractUrl) : base_url('contracts/create?unit_id='.$unit['id']) ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-file-earmark-plus me-2"></i>New Contract</a>
        <?php if (!empty($isParkingUnit)): ?>
        <a href="<?= esc($parkingContractUrl) ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-file-earmark-pdf me-2"></i>Print Parking Contract</a>
        <a href="<?= esc($parkingContractUrl . (str_contains($parkingContractUrl, '?') ? '&' : '?') . 'renew=1') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-arrow-repeat me-2"></i>Renew Parking Contract</a>
        <?php endif; ?>
        <a href="<?= base_url('finance/invoices/create?unit_id='.$unit['id']) ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-receipt me-2"></i>Create Invoice</a>
      </div>
    </div>

  </div><!-- /.col-lg-4 -->

  <!-- RIGHT COLUMN -->
  <div class="col-lg-8">

    <?php
    $ws = $workspace ?? 'fm';
    $isPm = ($ws === 'pm' || $ws === 'both');
    $isFm = ($ws === 'fm' || $ws === 'both');
    ?>
    <!-- Tabs -->
    <ul class="nav fm-entity-tabs mb-0" role="tablist">
      <?php if($isFm): ?>
      <li class="nav-item"><a class="nav-link active" href="#tab-wo" data-bs-toggle="tab" role="tab"><i class="bi bi-tools me-1"></i>Work Orders <span class="badge bg-secondary ms-1"><?= count($workOrders) ?></span></a></li>
      <?php endif; ?>
      <li class="nav-item"><a class="nav-link" href="#tab-inspections" data-bs-toggle="tab" role="tab"><i class="bi bi-clipboard2-check me-1"></i>Inspection Reports <span class="badge bg-secondary ms-1"><?= count($checklists) ?></span></a></li>
      <?php if($isFm): ?>
      <li class="nav-item"><a class="nav-link" href="#tab-assets" data-bs-toggle="tab" role="tab"><i class="bi bi-box-seam me-1"></i>Assets <span class="badge bg-secondary ms-1"><?= count($assets ?? []) ?></span></a></li>
      <?php endif; ?>
      <?php if($isPm): ?>
      <li class="nav-item"><a class="nav-link <?= !$isFm ? 'active' : '' ?>" href="#tab-tenants" data-bs-toggle="tab" role="tab"><i class="bi bi-person-check me-1"></i>Tenant / Contract</a></li>
      <li class="nav-item"><a class="nav-link" href="#tab-rent" data-bs-toggle="tab" role="tab"><i class="bi bi-cash-stack me-1"></i>Rent Payments <span class="badge bg-secondary ms-1"><?= count($leasePayments ?? []) ?></span></a></li>
      <?php endif; ?>
      <li class="nav-item"><a class="nav-link <?= (!$isFm && !$isPm) ? 'active' : '' ?>" href="#tab-finance" data-bs-toggle="tab" role="tab"><i class="bi bi-receipt me-1"></i>Finance</a></li>
      <li class="nav-item"><a class="nav-link" href="#tab-documents" data-bs-toggle="tab" role="tab"><i class="bi bi-folder2-open me-1"></i>Documents <span class="badge bg-secondary ms-1"><?= count($unitDocuments ?? []) ?></span></a></li>
      <li class="nav-item"><a class="nav-link" href="#tab-qr" data-bs-toggle="tab" role="tab"><i class="bi bi-qr-code me-1"></i>QR Code</a></li>
    </ul>

    <div class="tab-content fm-tab-pane">

      <?php if($isFm): ?>
      <!-- Work Orders Tab (FM) -->
      <div class="tab-pane fade show active" id="tab-wo">
        <div class="fm-card">
          <div class="card-header-fm">
            <h5><i class="bi bi-tools me-2"></i>Work Orders</h5>
            <a href="<?= base_url('workorders/create?facility_id='.$unit['facility_id'].'&unit_id='.$unit['id']) ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>New</a>
          </div>
          <div class="fm-card-body p-0">
            <?php if(empty($workOrders)): ?>
            <p class="text-muted text-center py-4 small">No work orders for this unit.</p>
            <?php else: ?>
            <div class="table-responsive">
            <table class="table table-registry table-sm mb-0">
              <thead><tr><th>WO #</th><th>Title</th><th>Status</th><th>Priority</th><th>Assigned</th><th>Date</th></tr></thead>
              <tbody>
              <?php foreach($workOrders as $w): ?>
              <tr>
                <td><a href="<?= base_url('workorders/view/'.$w['id']) ?>" class="fw-semibold text-primary"><?= esc($w['wo_number']) ?></a></td>
                <td class="small"><?= esc(substr($w['title'],0,35)) ?><?= strlen($w['title'])>35?'…':'' ?></td>
                <td><span class="fm-badge badge-status-<?= esc($w['status']) ?>"><?= ucfirst(str_replace('_',' ',$w['status'])) ?></span></td>
                <td><span class="fm-badge badge-priority-<?= esc($w['priority']) ?>"><?= ucfirst($w['priority']) ?></span></td>
                <td class="small text-muted"><?= esc($w['assigned_name']??'—') ?></td>
                <td class="small text-muted"><?= date('d M Y',strtotime($w['created_at'])) ?></td>
              </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endif; // isFm ?>

      <!-- Inspection Reports Tab -->
      <div class="tab-pane fade" id="tab-inspections">
        <?= view('partials/inspection_reports_table', [
          'reports' => $checklists,
          'showUnit' => false,
          'unitId' => (int) $unit['id'],
        ]) ?>
      </div>

      <?php if($isFm): ?>
      <!-- Assets Tab (FM) -->
      <div class="tab-pane fade" id="tab-assets">
        <div class="fm-card">
          <div class="card-header-fm">
            <h5><i class="bi bi-box-seam me-2"></i>Assets in Facility</h5>
            <a href="<?= base_url('assets/create?facility_id='.$unit['facility_id']) ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>New</a>
          </div>
          <div class="fm-card-body p-0">
            <?php if(empty($assets)): ?>
            <p class="text-muted text-center py-4 small">No assets found.</p>
            <?php else: ?>
            <div class="table-responsive">
            <table class="table table-registry table-sm mb-0">
              <thead><tr><th>Code</th><th>Name</th><th>Category</th><th>Health</th><th>Status</th></tr></thead>
              <tbody>
              <?php foreach($assets as $a): ?>
              <tr>
                <td class="small"><a href="<?= base_url('assets/view/'.($a['id']??0)) ?>" class="text-primary"><?= esc($a['asset_code']??'—') ?></a></td>
                <td class="small"><?= esc($a['name']??'') ?></td>
                <td class="small text-muted"><?= esc($a['category']??'—') ?></td>
                <td><div class="health-bar" style="width:80px"><div style="width:<?= (int)($a['health_score']??100) ?>%;background:var(--fm-green);height:5px;border-radius:99px"></div></div></td>
                <td><span class="fm-badge badge-status-<?= esc($a['status']??'active') ?>"><?= ucfirst($a['status']??'active') ?></span></td>
              </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endif; // isFm ?>

      <?php if($isPm): ?>
      <!-- Tenant / Contract Tab (PM) -->
      <div class="tab-pane fade <?= !$isFm ? 'show active' : '' ?>" id="tab-tenants">
        <div class="fm-card">
          <div class="card-header-fm">
            <h5><i class="bi bi-person-check me-2"></i>Tenant &amp; Lease Contracts</h5>
            <a href="<?= !empty($isParkingUnit) ? esc($parkingContractUrl) : base_url('contracts/create?unit_id='.$unit['id']) ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>New Lease</a>
          </div>
          <div class="fm-card-body">
            <?php if(!empty($unit['tenant_name'])): ?>
            <div class="row g-3 mb-3">
              <div class="col-6"><span class="text-muted small">Tenant</span><div class="fw-semibold"><?= esc($unit['tenant_name']) ?></div></div>
              <div class="col-6"><span class="text-muted small">Phone</span><div><?= esc($unit['tenant_mobile']??'—') ?></div></div>
              <?php if($unit['contract_start']): ?><div class="col-6"><span class="text-muted small">Contract Start</span><div><?= date('d M Y',strtotime($unit['contract_start'])) ?></div></div><?php endif; ?>
              <?php if($unit['contract_end']): ?><div class="col-6"><span class="text-muted small">Contract End</span><div class="<?= ($daysToExpiry!==null&&$daysToExpiry<=30)?'text-danger fw-bold':'' ?>"><?= date('d M Y',strtotime($unit['contract_end'])) ?></div></div><?php endif; ?>
            </div>
            <?php endif; ?>
            <?php if(!empty($leaseContracts)): ?>
            <div class="table-responsive">
            <table class="table table-registry table-sm mb-0 mt-2">
              <thead><tr><th>Contract #</th><th>Start</th><th>End</th><th>Rent</th><th>Status</th></tr></thead>
              <tbody>
              <?php foreach($leaseContracts as $lc): ?>
              <tr>
                <td><a href="<?= base_url('contracts/'.$lc['id']) ?>" class="fw-semibold small text-primary"><?= esc($lc['contract_number']) ?></a></td>
                <td class="small"><?= $lc['start_date'] ? date('d M Y',strtotime($lc['start_date'])) : '—' ?></td>
                <td class="small"><?= $lc['end_date'] ? date('d M Y',strtotime($lc['end_date'])) : '—' ?></td>
                <td class="small"><?= $currency ?> <?= number_format($lc['rent_amount']??0,0) ?></td>
                <td><span class="fm-badge badge-status-<?= esc($lc['status']) ?>"><?= ucfirst($lc['status']) ?></span></td>
              </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
            </div>
            <?php elseif(empty($unit['tenant_name'])): ?>
            <p class="text-muted small text-center py-3">No active lease. <a href="<?= !empty($isParkingUnit) ? esc($parkingContractUrl) : base_url('contracts/create?unit_id='.$unit['id']) ?>">Create one</a>.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Rent Payments Tab (PM) -->
      <div class="tab-pane fade" id="tab-rent">
        <div class="fm-card">
          <div class="card-header-fm">
            <h5><i class="bi bi-cash-stack me-2"></i>Rent Payments</h5>
            <a href="<?= base_url('payments/create?unit_id='.$unit['id']) ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>Record</a>
          </div>
          <div class="fm-card-body p-0">
            <?php if(empty($leasePayments)): ?>
            <p class="text-muted text-center py-4 small">No rent payments recorded.</p>
            <?php else: ?>
            <div class="table-responsive">
            <table class="table table-registry table-sm mb-0">
              <thead><tr><th>Payment #</th><th>Type</th><th>Amount</th><th>Due Date</th><th>Status</th></tr></thead>
              <tbody>
              <?php foreach($leasePayments as $lp): ?>
              <tr>
                <td class="small fw-semibold"><?= esc($lp['payment_number']??'—') ?></td>
                <td class="small"><?= ucfirst(str_replace('_',' ',$lp['payment_type']??'rent')) ?></td>
                <td class="small"><?= $currency ?> <?= number_format($lp['amount']??0,0) ?></td>
                <td class="small"><?= $lp['due_date'] ? date('d M Y',strtotime($lp['due_date'])) : '—' ?></td>
                <td><span class="fm-badge badge-status-<?= esc($lp['status']??'pending') ?>"><?= ucfirst($lp['status']??'pending') ?></span></td>
              </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endif; // isPm ?>

      <!-- Finance Tab (always visible) -->
      <div class="tab-pane fade <?= (!$isFm && !$isPm) ? 'show active' : '' ?>" id="tab-finance">
        <div class="fm-card">
          <div class="card-header-fm">
            <h5><i class="bi bi-receipt me-2"></i>Finance Summary</h5>
            <a href="<?= base_url('finance/invoices/create?unit_id='.$unit['id']) ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>Invoice</a>
          </div>
          <div class="fm-card-body">
            <?php if($contract): ?>
            <div class="row g-3 mb-3">
              <div class="col-4">
                <div class="fm-form-section text-center">
                  <div class="kpi-label">Monthly Rent</div>
                  <div class="fw-bold" style="font-size:1.1rem"><?= $currency ?> <?= number_format($unit['rent_amount']??0,0) ?></div>
                </div>
              </div>
              <div class="col-4">
                <div class="fm-form-section text-center">
                  <div class="kpi-label">Security Deposit</div>
                  <div class="fw-bold"><?= $currency ?> <?= number_format($unit['security_deposit']??0,0) ?></div>
                </div>
              </div>
              <div class="col-4">
                <div class="fm-form-section text-center">
                  <div class="kpi-label">Contract Status</div>
                  <span class="fm-badge badge-status-<?= esc($contract['status']) ?>"><?= ucfirst($contract['status']) ?></span>
                </div>
              </div>
            </div>
            <?php else: ?>
            <p class="text-muted small text-center py-3">No active contract. <a href="<?= base_url('finance/contracts/create?unit_id='.$unit['id']) ?>">Create one</a>.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Documents Tab -->
      <div class="tab-pane fade" id="tab-documents">
        <?= view('documents/panel', [
          'module' => 'unit',
          'refId' => (int) $unit['id'],
          'embed' => true,
          'documents' => $unitDocuments ?? [],
          'facilityId' => (int) $unit['facility_id'],
        ]) ?>
      </div>

      <!-- QR Code Tab -->
      <div class="tab-pane fade" id="tab-qr">
        <?= view('partials/qr_display', [
          'scanUrl' => $scanUrl ?? '',
          'qrImageUrl' => $qrImageUrl ?? '',
          'entityLabel' => 'Unit',
          'printUrl' => base_url('units/qrcode/' . (int) $unit['id']),
        ]) ?>
      </div>

    </div><!-- /.tab-content -->
  </div><!-- /.col-lg-8 -->
</div>

<?= view('partials/entity_tab_hash') ?>
<?= $this->endSection() ?>
