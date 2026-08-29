<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-graph-up-arrow me-2"></i>Internal Financial Reports</h1>
  <p class="text-muted small mb-0">Estimated vs actual, profitability, material variance — internal roles only.</p></div>
</div>

<form method="get" class="fm-form-section mb-3">
  <div class="row g-2 align-items-end">
    <div class="col-md-2"><label class="form-label small">From</label><input type="date" name="from" class="form-control form-control-sm" value="<?= esc($from) ?>"></div>
    <div class="col-md-2"><label class="form-label small">To</label><input type="date" name="to" class="form-control form-control-sm" value="<?= esc($to) ?>"></div>
    <div class="col-md-3">
      <label class="form-label small">Report</label>
      <select name="report" class="form-select form-select-sm">
        <option value="est_vs_act" <?= $report==='est_vs_act'?'selected':'' ?>>Estimated vs Actual Cost</option>
        <option value="wo_profit" <?= $report==='wo_profit'?'selected':'' ?>>Work Order Profitability</option>
        <option value="materials" <?= $report==='materials'?'selected':'' ?>>Material Variance &amp; Wastage</option>
        <option value="monthly" <?= $report==='monthly'?'selected':'' ?>>Monthly Financial Summary</option>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label small">Facility</label>
      <select name="facility" class="form-select form-select-sm">
        <option value="">All</option>
        <?php foreach($facilities as $f): ?>
        <option value="<?= $f['id'] ?>" <?= ($filterFacility??0)==$f['id']?'selected':'' ?>><?= esc($f['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2"><button type="submit" class="btn btn-fm-primary btn-sm w-100">Run Report</button></div>
  </div>
</form>

<div class="fm-card">
  <div class="fm-card-body p-0">
    <table class="fm-table">
      <thead><tr>
        <?php if($report==='monthly'): ?>
        <th>Period</th><th>Revenue</th><th>Collected</th><th>Cost</th><th>Profit</th>
        <?php elseif($report==='materials'): ?>
        <th>WO #</th><th>Material</th><th>Qty</th><th>Est. Cost</th><th>Act. Cost</th><th>Variance</th><th>Wastage</th>
        <?php elseif($report==='wo_profit'): ?>
        <th>WO #</th><th>Facility</th><th>Revenue</th><th>Actual Cost</th><th>Profit</th><th>Margin</th>
        <?php else: ?>
        <th>EST #</th><th>Title</th><th>Facility</th><th>Selling</th><th>Est. Cost</th><th>Act. Cost</th><th>Variance</th><th>Profit</th><th>Margin</th>
        <?php endif; ?>
      </tr></thead>
      <tbody>
      <?php if(empty($rows)): ?>
      <tr><td colspan="10" class="text-center text-muted py-4">No data for selected filters.</td></tr>
      <?php else: ?>
      <?php foreach($rows as $r): ?>
      <tr>
        <?php if($report==='monthly'): ?>
        <td><?= esc($r['period']) ?></td>
        <td><?= $currency ?> <?= number_format($r['revenue'],2) ?></td>
        <td><?= $currency ?> <?= number_format($r['collected'],2) ?></td>
        <td><?= $currency ?> <?= number_format($r['cost'],2) ?></td>
        <td class="<?= ($r['profit']??0)>=0?'text-success':'text-danger' ?>"><?= $currency ?> <?= number_format($r['profit'],2) ?></td>
        <?php elseif($report==='materials'): ?>
        <td><?= esc($r['wo_number']??'—') ?></td>
        <td><?= esc($r['item_name']) ?></td>
        <td><?= $r['quantity'] ?></td>
        <td><?= $currency ?> <?= number_format($r['estimated_cost']??$r['total_cost']??0,2) ?></td>
        <td><?= $currency ?> <?= number_format($r['actual_cost']??$r['total_cost']??0,2) ?></td>
        <td><?= $currency ?> <?= number_format($r['variance']??0,2) ?></td>
        <td><?= $currency ?> <?= number_format($r['wastage_total']??0,2) ?></td>
        <?php elseif($report==='wo_profit'): ?>
        <td><?= esc($r['wo_number']) ?></td>
        <td><?= esc($r['facility_name']??'—') ?></td>
        <td><?= $currency ?> <?= number_format($r['revenue']??0,2) ?></td>
        <td><?= $currency ?> <?= number_format($r['actual_cost']??0,2) ?></td>
        <td class="<?= ($r['profit']??0)>=0?'text-success':'text-danger' ?>"><?= $currency ?> <?= number_format($r['profit']??0,2) ?></td>
        <td><?= number_format($r['margin_percent']??0,1) ?>%</td>
        <?php else: ?>
        <td><?= esc($r['est_number']) ?></td>
        <td><?= esc($r['title']) ?></td>
        <td><?= esc($r['facility_name']??'—') ?></td>
        <td><?= $currency ?> <?= number_format($r['selling_subtotal']??0,2) ?></td>
        <td><?= $currency ?> <?= number_format($r['estimated_subtotal']??0,2) ?></td>
        <td><?= $currency ?> <?= number_format($r['actual_total_cost']??$r['actual_subtotal']??0,2) ?></td>
        <td><?= $currency ?> <?= number_format($r['cost_variance']??0,2) ?></td>
        <td><?= $currency ?> <?= number_format($r['total_profit']??0,2) ?></td>
        <td><?= number_format($r['total_margin']??0,1) ?>%</td>
        <?php endif; ?>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?= $this->endSection() ?>
