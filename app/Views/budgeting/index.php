<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-bar-chart me-2 text-primary"></i>Property Budgets</h1></div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('budgets/reconcile') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-arrow-left-right me-1"></i>Reconcile</a>
    <a href="<?= base_url('budgets/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>Set Budget</a>
  </div>
</div>

<?php if (!empty($migrationRequired)): ?>
  <div class="alert alert-warning">Run database migration to enable budgeting module.</div>
<?php else: ?>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<form class="filters-inline form-card mb-3" method="get">
  <select name="facility_id" class="form-select form-select-sm">
    <option value="">All properties</option>
    <?php foreach ($facilities as $f): ?>
      <option value="<?= $f['id'] ?>" <?= ($facilityId??0)==$f['id']?'selected':'' ?>><?= esc($f['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="year" class="form-select form-select-sm">
    <?php for ($y=date('Y')+1; $y>=date('Y')-3; $y--): ?>
      <option value="<?= $y ?>" <?= ($year??date('Y'))==$y?'selected':'' ?>><?= $y ?></option>
    <?php endfor; ?>
  </select>
  <button class="btn btn-fm-outline btn-sm" type="submit">View</button>
</form>

<?php
// Group budgets by facility
$grouped = [];
foreach ($budgets as $b) {
    $key = $b['facility_id'] . '|' . ($b['facility_name']??'Unknown');
    $grouped[$key][(int)$b['month']] = $b;
}
?>

<?php if (empty($grouped)): ?>
  <div class="form-card text-center py-5">
    <i class="bi bi-bar-chart display-4 text-muted"></i>
    <p class="text-muted mt-3">No budgets for <?= $year ?>. <a href="<?= base_url('budgets/create') ?>">Create one</a>.</p>
  </div>
<?php endif; ?>

<?php foreach ($grouped as $key => $monthData): ?>
  <?php [$facId, $facName] = explode('|', $key, 2); ?>
  <div class="form-card mb-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h6 class="mb-0"><?= esc($facName) ?> — <?= $year ?></h6>
      <div class="d-flex gap-2">
        <a href="<?= base_url('budgets/'.$facId.'/'.$year.'/variance') ?>" class="btn btn-sm btn-fm-outline">Variance</a>
        <a href="<?= base_url('budgets/'.$facId.'/forecast') ?>" class="btn btn-sm btn-fm-outline">Forecast</a>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-sm table-bordered text-center mb-0">
        <thead class="table-light">
          <tr>
            <th class="text-start">Metric</th>
            <?php for ($m=1;$m<=12;$m++): ?>
              <th><?= date('M', mktime(0,0,0,$m,1)) ?></th>
            <?php endfor; ?>
            <th>Total</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $incomeRow = [];
          $expenseRow = [];
          for ($m=1;$m<=12;$m++) {
            $incomeRow[$m]  = (float)($monthData[$m]['income']  ?? 0);
            $expenseRow[$m] = (float)($monthData[$m]['expense'] ?? 0);
          }
          ?>
          <tr>
            <td class="text-start fw-semibold text-success">Budgeted Income</td>
            <?php foreach ($incomeRow as $v): ?>
              <td><?= $v ? number_format($v,0) : '—' ?></td>
            <?php endforeach; ?>
            <td class="fw-semibold"><?= number_format(array_sum($incomeRow),0) ?></td>
          </tr>
          <tr>
            <td class="text-start fw-semibold text-danger">Budgeted Expense</td>
            <?php foreach ($expenseRow as $v): ?>
              <td><?= $v ? number_format($v,0) : '—' ?></td>
            <?php endforeach; ?>
            <td class="fw-semibold"><?= number_format(array_sum($expenseRow),0) ?></td>
          </tr>
          <tr class="table-light">
            <td class="text-start fw-semibold">Net</td>
            <?php for ($m=1;$m<=12;$m++): ?>
              <?php $net = $incomeRow[$m] - $expenseRow[$m]; ?>
              <td class="<?= $net>=0?'text-success':'text-danger' ?>"><?= number_format($net,0) ?></td>
            <?php endfor; ?>
            <?php $totalNet = array_sum($incomeRow) - array_sum($expenseRow); ?>
            <td class="fw-semibold <?= $totalNet>=0?'text-success':'text-danger' ?>"><?= number_format($totalNet,0) ?></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
<?php endforeach; ?>
<?php endif; ?>
<?= $this->endSection() ?>
