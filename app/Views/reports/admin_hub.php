<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div>
    <h1><i class="bi bi-bar-chart-line me-2"></i>Reports</h1>
    <div class="small text-muted">Facility Management and Property Management — full report catalog</div>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= base_url('reports/portal') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-grid me-1"></i>FM Portal</a>
    <a href="<?= base_url('reports/pm/portal') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-grid me-1"></i>PM Portal</a>
  </div>
</div>

<div id="fm-reports" class="fm-card mb-4 border-primary" style="border-width:2px">
  <div class="fm-card-body py-3">
    <div class="d-flex flex-wrap gap-3 align-items-center">
      <div class="kpi-icon kpi-primary" style="border-radius:12px"><i class="bi bi-building-gear fs-5"></i></div>
      <div class="flex-grow-1">
        <div class="fw-bold">Facility Management Reports</div>
        <div class="small text-muted">Maintenance, SLA, assets, procurement, inventory, compliance inspections</div>
      </div>
      <a href="<?= base_url('reports/portal') ?>" class="btn btn-sm btn-fm-outline">FM Portal</a>
    </div>
  </div>
</div>

<?= view('reports/_fm_report_cards', ['urlPrefix' => 'reports']) ?>

<hr class="my-4">

<div id="pm-reports" class="fm-card mb-4 border-success" style="border-width:2px">
  <div class="fm-card-body py-3">
    <div class="d-flex flex-wrap gap-3 align-items-center">
      <div class="kpi-icon kpi-green" style="border-radius:12px"><i class="bi bi-building fs-5"></i></div>
      <div class="flex-grow-1">
        <div class="fw-bold">Property Management Reports</div>
        <div class="small text-muted">Leasing, collections, cheques, landlord statements, CRM</div>
      </div>
      <a href="<?= base_url('reports/pm/portal') ?>" class="btn btn-sm btn-outline-success">PM Portal</a>
    </div>
  </div>
</div>

<?= view('reports/_pm_report_cards', ['urlPrefix' => 'reports/pm']) ?>
<?= $this->endSection() ?>
