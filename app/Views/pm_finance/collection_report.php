<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1>Collection Report</h1></div>
<form method="get" class="row g-2 mb-3">
  <div class="col-auto"><input type="date" name="date_from" value="<?= esc($from) ?>" class="form-control form-control-sm"></div>
  <div class="col-auto"><input type="date" name="date_to" value="<?= esc($to) ?>" class="form-control form-control-sm"></div>
  <div class="col-auto"><button class="btn btn-sm btn-secondary">Generate</button></div>
</form>
<div class="row g-3 mb-3">
  <div class="col-md-4"><div class="kpi-card"><div class="kpi-label">Expected</div><div class="kpi-value"><?= number_format($report['expected'],0) ?></div></div></div>
  <div class="col-md-4"><div class="kpi-card kpi-green"><div class="kpi-label">Collected</div><div class="kpi-value"><?= number_format($report['collected'],0) ?></div></div></div>
</div>
<?= $this->endSection() ?>
