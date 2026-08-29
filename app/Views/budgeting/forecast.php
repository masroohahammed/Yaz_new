<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-graph-up-arrow me-2 text-primary"></i>6-Month Forecast</h1>
    <div class="small text-muted"><?= esc($facility['name']) ?></div>
  </div>
  <a href="<?= base_url('budgets') ?>" class="btn btn-fm-outline btn-sm">Back</a>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="form-card text-center">
      <div class="small text-muted text-uppercase">3-Month Trailing Avg</div>
      <div class="fs-4 fw-bold text-primary"><?= number_format($trailingAvg, 0) ?> <small class="fs-6 text-muted"><?= $currency??'QAR' ?>/mo</small></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-card text-center">
      <div class="small text-muted text-uppercase">Active Lease Income</div>
      <div class="fs-4 fw-bold text-success"><?= number_format($activeIncome, 0) ?> <small class="fs-6 text-muted"><?= $currency??'QAR' ?>/mo</small></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-card text-center">
      <div class="small text-muted text-uppercase">Blended Forecast/mo</div>
      <?php $blended = round(($trailingAvg + $activeIncome) / 2, 0); ?>
      <div class="fs-4 fw-bold text-info"><?= number_format($blended, 0) ?> <small class="fs-6 text-muted"><?= $currency??'QAR' ?>/mo</small></div>
    </div>
  </div>
</div>

<div class="form-card p-0">
  <table class="table table-sm table-registry mb-0">
    <thead><tr><th>Month</th><th>Active Lease Income</th><th>3-mo Trailing Avg</th><th>Blended Forecast</th></tr></thead>
    <tbody>
      <?php foreach ($forecast as $row): ?>
      <tr>
        <td class="fw-semibold"><?= esc($row['label']) ?></td>
        <td><?= number_format($row['lease_income'], 2) ?></td>
        <td><?= number_format($row['trailing_avg'], 2) ?></td>
        <td class="text-primary fw-semibold"><?= number_format($row['blended'], 2) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot class="table-light">
      <tr>
        <td class="fw-semibold">6-Month Total</td>
        <td><?= number_format(array_sum(array_column($forecast,'lease_income')),2) ?></td>
        <td><?= number_format(array_sum(array_column($forecast,'trailing_avg')),2) ?></td>
        <td class="text-primary fw-semibold"><?= number_format(array_sum(array_column($forecast,'blended')),2) ?></td>
      </tr>
    </tfoot>
  </table>
</div>
<?= $this->endSection() ?>
