<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1>VAT Report</h1></div>
<form method="get" class="row g-2 mb-3">
  <div class="col-auto"><input type="date" name="date_from" value="<?= esc($from) ?>" class="form-control form-control-sm"></div>
  <div class="col-auto"><input type="date" name="date_to" value="<?= esc($to) ?>" class="form-control form-control-sm"></div>
  <div class="col-auto"><button class="btn btn-sm btn-secondary">Generate</button></div>
</form>
<p>Gross collected: <?= number_format($report['gross'],2) ?> · VAT (est.): <?= number_format($report['vat'],2) ?></p>
<?= $this->endSection() ?>
