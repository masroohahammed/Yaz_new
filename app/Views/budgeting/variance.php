<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-arrow-left-right me-2 text-primary"></i>Budget vs Actual</h1>
    <div class="small text-muted"><?= esc($facility['name']) ?> — <?= $year ?></div>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('budgets/'.$facility['id'].'/forecast') ?>" class="btn btn-fm-outline btn-sm">Forecast</a>
    <a href="<?= base_url('budgets') ?>" class="btn btn-fm-outline btn-sm">Back</a>
  </div>
</div>

<div class="row g-3 mb-3">
  <?php
  $vars = [
    ['Income', $totalBudgetIncome, $totalActualIncome, 'success'],
    ['Expense', $totalBudgetExpense, $totalActualExpense, 'danger'],
  ];
  foreach ($vars as [$label, $budget, $actual, $color]):
    $diff = $actual - $budget;
    $pct  = $budget > 0 ? round(abs($diff) / $budget * 100, 1) : 0;
  ?>
  <div class="col-md-4">
    <div class="form-card text-center">
      <div class="small text-muted text-uppercase"><?= $label ?></div>
      <div class="fs-5 fw-bold text-<?= $color ?>"><?= number_format($actual, 0) ?></div>
      <div class="small">Budget: <?= number_format($budget, 0) ?></div>
      <div class="small <?= $diff < 0 ? 'text-danger' : 'text-success' ?>"><?= $diff >= 0 ? '+' : '' ?><?= number_format($diff, 0) ?> (<?= $pct ?>%)</div>
    </div>
  </div>
  <?php endforeach; ?>
  <div class="col-md-4">
    <div class="form-card text-center">
      <div class="small text-muted text-uppercase">Net</div>
      <?php $netBudget = $totalBudgetIncome - $totalBudgetExpense; $netActual = $totalActualIncome - $totalActualExpense; ?>
      <div class="fs-5 fw-bold <?= $netActual >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($netActual, 0) ?></div>
      <div class="small">Budget Net: <?= number_format($netBudget, 0) ?></div>
      <div class="small <?= ($netActual-$netBudget) >= 0 ? 'text-success' : 'text-danger' ?>"><?= ($netActual-$netBudget) >= 0 ? '+' : '' ?><?= number_format($netActual-$netBudget, 0) ?></div>
    </div>
  </div>
</div>

<div class="form-card p-0">
  <div class="table-responsive">
    <table class="table table-sm table-bordered text-center mb-0">
      <thead class="table-light">
        <tr>
          <th class="text-start">Month</th>
          <th>Bud. Income</th><th>Act. Income</th><th>Var. Income</th>
          <th>Bud. Expense</th><th>Act. Expense</th><th>Var. Expense</th>
          <th>Net Variance</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($months as $m => $row): ?>
        <tr>
          <td class="text-start"><?= date('F', mktime(0,0,0,$m,1)) ?></td>
          <td><?= number_format($row['budget_income'],0) ?></td>
          <td><?= number_format($row['actual_income'],0) ?></td>
          <td class="<?= $row['income_variance']>=0?'text-success':'text-danger' ?> fw-semibold"><?= ($row['income_variance']>=0?'+':'').number_format($row['income_variance'],0) ?></td>
          <td><?= number_format($row['budget_expense'],0) ?></td>
          <td><?= number_format($row['actual_expense'],0) ?></td>
          <td class="<?= $row['expense_variance']<=0?'text-success':'text-danger' ?> fw-semibold"><?= ($row['expense_variance']>=0?'+':'').number_format($row['expense_variance'],0) ?></td>
          <?php $netVar = $row['income_variance'] - $row['expense_variance']; ?>
          <td class="<?= $netVar>=0?'text-success':'text-danger' ?> fw-semibold"><?= ($netVar>=0?'+':'').number_format($netVar,0) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot class="table-light fw-semibold">
        <tr>
          <td class="text-start">Total</td>
          <td><?= number_format($totalBudgetIncome,0) ?></td>
          <td><?= number_format($totalActualIncome,0) ?></td>
          <?php $iVar=$totalActualIncome-$totalBudgetIncome; ?>
          <td class="<?= $iVar>=0?'text-success':'text-danger' ?>"><?= ($iVar>=0?'+':'').number_format($iVar,0) ?></td>
          <td><?= number_format($totalBudgetExpense,0) ?></td>
          <td><?= number_format($totalActualExpense,0) ?></td>
          <?php $eVar=$totalActualExpense-$totalBudgetExpense; ?>
          <td class="<?= $eVar<=0?'text-success':'text-danger' ?>"><?= ($eVar>=0?'+':'').number_format($eVar,0) ?></td>
          <?php $nVar=$iVar-$eVar; ?>
          <td class="<?= $nVar>=0?'text-success':'text-danger' ?>"><?= ($nVar>=0?'+':'').number_format($nVar,0) ?></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
<?= $this->endSection() ?>
