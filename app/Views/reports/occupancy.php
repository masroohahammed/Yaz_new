<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-grid me-2"></i>Occupancy Report</h1></div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('reports/export/occupancy/csv') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-filetype-csv me-1"></i>CSV</a>
    <a href="<?= base_url('reports/export/occupancy/excel') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
  </div>
</div>

<?php
$totalUnits    = array_sum(array_column($facilities,'total_units'));
$totalOccupied = array_sum(array_column($facilities,'occupied'));
$totalRent     = array_sum(array_column($facilities,'total_rent'));
$overallPct    = $totalUnits>0 ? round($totalOccupied/$totalUnits*100,1) : 0;
?>

<div class="row g-3 mb-3">
  <div class="col-6 col-md-3"><div class="kpi-card kpi-primary"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-grid"></i></div><div><div class="kpi-label">Total Units</div><div class="kpi-value"><?= $totalUnits ?></div></div></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-green"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-person-check"></i></div><div><div class="kpi-label">Occupied</div><div class="kpi-value"><?= $totalOccupied ?></div><div class="kpi-sub"><?= $overallPct ?>% rate</div></div></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-teal"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-house"></i></div><div><div class="kpi-label">Vacant</div><div class="kpi-value"><?= $totalUnits-$totalOccupied ?></div></div></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-secondary"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-cash-stack"></i></div><div><div class="kpi-label">Monthly Rent</div><div class="kpi-value" style="font-size:1.1rem"><?= $currency ?> <?= number_format($totalRent/1000,1) ?>K</div></div></div></div></div>
</div>

<div class="fm-card mb-3">
  <div class="card-header-fm"><h5><i class="bi bi-building me-2"></i>By Facility</h5></div>
  <div class="fm-card-body p-0">
    <table class="fm-table">
      <thead><tr><th>Facility</th><th>Total</th><th>Occupied</th><th>Vacant</th><th>Maint.</th><th>Occupancy</th><th>Monthly Rent</th></tr></thead>
      <tbody>
      <?php foreach($facilities as $f): ?>
      <tr>
        <td class="fw-semibold small"><a href="<?= base_url('facilities/'.$f['id'].'/units') ?>" class="text-primary"><?= esc($f['name']) ?></a></td>
        <td class="small text-center"><?= $f['total_units'] ?></td>
        <td class="small text-center text-success fw-bold"><?= $f['occupied'] ?></td>
        <td class="small text-center"><?= $f['vacant'] ?></td>
        <td class="small text-center"><?= $f['maintenance'] ?></td>
        <td>
          <div class="d-flex align-items-center gap-2">
            <div class="progress flex-grow-1" style="height:8px;border-radius:4px">
              <div class="progress-bar" style="width:<?= $f['occupancy_pct']??0 ?>%;background:var(--fm-primary)"></div>
            </div>
            <span class="small fw-bold"><?= $f['occupancy_pct']??0 ?>%</span>
          </div>
        </td>
        <td class="small"><?= $currency ?> <?= number_format($f['total_rent'],0) ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if(!empty($expiringContracts)): ?>
<div class="fm-card">
  <div class="card-header-fm"><h5><i class="bi bi-clock-history me-2 text-warning"></i>Contracts Expiring in 60 Days</h5></div>
  <div class="fm-card-body p-0">
    <table class="fm-table">
      <thead><tr><th>Unit</th><th>Facility</th><th>Tenant</th><th>Contact</th><th>Rent</th><th>Expires</th><th>Days Left</th></tr></thead>
      <tbody>
      <?php foreach($expiringContracts as $u): $days=(int)ceil((strtotime($u['contract_end'])-time())/86400); ?>
      <tr class="<?= $days<=7?'table-danger':($days<=30?'table-warning':'') ?>">
        <td class="fw-semibold small"><a href="<?= base_url('units/view/'.$u['id']) ?>">Unit <?= esc($u['unit_number']) ?></a></td>
        <td class="small"><?= esc($u['facility_name']??'—') ?></td>
        <td class="small"><?= esc($u['tenant_name']??'—') ?></td>
        <td class="small"><?= esc($u['tenant_mobile']??'—') ?></td>
        <td class="small"><?= $currency ?> <?= number_format($u['rent_amount']??0,0) ?></td>
        <td class="small"><?= date('d M Y',strtotime($u['contract_end'])) ?></td>
        <td><span class="fm-badge badge-status-<?= $days<=7?'overdue':($days<=30?'pending':'active') ?>"><?= $days ?>d</span></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>
