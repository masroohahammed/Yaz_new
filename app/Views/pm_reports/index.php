<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div>
    <h1><i class="bi bi-bar-chart-line me-2"></i>Property Management Reports</h1>
    <div class="small text-muted">Leasing, collections, occupancy, and financial analytics</div>
  </div>
  <a href="<?= base_url('reports/pm/portal') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-grid me-1"></i>PM Reports Portal</a>
</div>

<?= view('reports/_pm_report_cards', ['urlPrefix' => 'reports/pm']) ?>
<?= $this->endSection() ?>
