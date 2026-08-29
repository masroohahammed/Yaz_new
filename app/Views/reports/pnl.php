<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$currency   = $currency   ?? 'QAR';
$groupBy    = $groupBy    ?? 'property';
$period     = $period     ?? 'month';
$from       = $from       ?? date('Y-01-01');
$to         = $to         ?? date('Y-m-d');
$facilityId = $facilityId ?? 0;
$rows       = $rows       ?? [];
$totRevenue = $totRevenue ?? 0;
$totCost    = $totCost    ?? 0;
$totProfit  = $totProfit  ?? 0;
$facilities = $facilities ?? [];
$margin     = $totRevenue > 0 ? round(($totProfit / $totRevenue) * 100, 1) : 0;

$groupLabels  = ['property' => 'Property', 'workorder' => 'Work Order', 'customer' => 'Customer'];
$periodLabels = ['day' => 'Daily', 'month' => 'Monthly', 'year' => 'Yearly'];

// Group rows by group key for summary cards
$summary = [];
foreach ($rows as $row) {
    $g = $row['group'];
    if (!isset($summary[$g])) $summary[$g] = ['revenue' => 0, 'cost' => 0, 'profit' => 0, 'estimated_cost' => 0];
    $summary[$g]['revenue']        += $row['revenue'];
    $summary[$g]['cost']           += $row['cost'];
    $summary[$g]['profit']         += $row['profit'];
    $summary[$g]['estimated_cost'] += $row['estimated_cost'] ?? 0;
}
arsort($summary);
?>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div>
    <h1><i class="bi bi-currency-dollar me-2"></i>P&amp;L Report</h1>
    <p class="text-muted small mb-0">
      <?= esc($groupLabels[$groupBy] ?? 'Property') ?>-wise &middot;
      <?= esc($periodLabels[$period] ?? 'Monthly') ?> &middot;
      <?= date('d M Y', strtotime($from)) ?> – <?= date('d M Y', strtotime($to)) ?>
    </p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('reports/export/pnl/csv?'.http_build_query(['from'=>$from,'to'=>$to,'group_by'=>$groupBy,'period'=>$period,'facility'=>$facilityId])) ?>"
       class="btn btn-fm-outline btn-sm"><i class="bi bi-filetype-csv me-1"></i>CSV</a>
    <a href="<?= base_url('reports/export/pnl/pdf?'.http_build_query(['from'=>$from,'to'=>$to,'group_by'=>$groupBy,'period'=>$period,'facility'=>$facilityId])) ?>"
       class="btn btn-fm-outline btn-sm"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
    <a href="<?= base_url('reports') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-arrow-left me-1"></i>Reports</a>
  </div>
</div>

<!-- Loader overlay -->
<div id="pnlLoader" class="d-none position-fixed top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
     style="background:rgba(0,0,0,.35);z-index:9999">
  <div class="text-center text-white">
    <div class="spinner-border mb-3" style="width:3rem;height:3rem;border-width:.3rem"></div>
    <div class="fw-semibold fs-6">Generating report…</div>
  </div>
</div>

<!-- ── Filters ───────────────────────────────────────────────── -->
<form method="get" id="pnlForm" class="fm-card mb-4">
  <div class="fm-card-body py-3">
    <div class="row g-2 align-items-end">

      <div class="col-md-2">
        <label class="form-label small fw-semibold">Group By</label>
        <select name="group_by" class="form-select form-select-sm">
          <option value="property"  <?= $groupBy==='property'  ? 'selected':'' ?>>Property</option>
          <option value="workorder" <?= $groupBy==='workorder' ? 'selected':'' ?>>Work Order</option>
          <option value="customer"  <?= $groupBy==='customer'  ? 'selected':'' ?>>Customer</option>
        </select>
      </div>

      <div class="col-md-2">
        <label class="form-label small fw-semibold">Period</label>
        <select name="period" class="form-select form-select-sm">
          <option value="day"   <?= $period==='day'   ? 'selected':'' ?>>Daily</option>
          <option value="month" <?= $period==='month' ? 'selected':'' ?>>Monthly</option>
          <option value="year"  <?= $period==='year'  ? 'selected':'' ?>>Yearly</option>
        </select>
      </div>

      <!-- Quick date range dropdown -->
      <div class="col-md-3">
        <label class="form-label small fw-semibold">Quick Range</label>
        <select id="quickRangeSelect" class="form-select form-select-sm">
          <option value="">— Select Range —</option>
          <option value="today">Today</option>
          <option value="mtd">Month to Date (MTD)</option>
          <option value="qtd">Quarter to Date (QTD)</option>
          <option value="ytd">Year to Date (YTD)</option>
          <option value="last30">Last 30 Days</option>
          <option value="last12m">Last 12 Months</option>
          <option value="all">All Time</option>
        </select>
      </div>

      <div class="col-md-2">
        <label class="form-label small fw-semibold">From</label>
        <input type="date" name="from" id="fromDate" class="form-control form-control-sm" value="<?= esc($from) ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold">To</label>
        <input type="date" name="to" id="toDate" class="form-control form-control-sm" value="<?= esc($to) ?>">
      </div>

      <div class="col-md-3 col-xl-2">
        <label class="form-label small fw-semibold">Property</label>
        <select name="facility" class="form-select form-select-sm">
          <option value="0">All Properties</option>
          <?php foreach ($facilities as $f): ?>
          <option value="<?= $f['id'] ?>" <?= $facilityId==$f['id'] ? 'selected':'' ?>><?= esc($f['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-auto">
        <button type="submit" class="btn btn-fm-primary btn-sm" id="runBtn">
          <span class="spinner-border spinner-border-sm d-none me-1" id="btnSpinner"></span>
          <i class="bi bi-funnel me-1" id="btnIcon"></i>Run Report
        </button>
      </div>
    </div>
  </div>
</form>

<!-- ── KPI Summary ──────────────────────────────────────────── -->
<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="kpi-card kpi-green">
      <div class="d-flex gap-3 align-items-center">
        <div class="kpi-icon"><i class="bi bi-arrow-up-circle"></i></div>
        <div>
          <div class="kpi-label">Total Revenue</div>
          <div class="kpi-value"><?= $currency ?> <?= number_format($totRevenue/1000,1) ?>K</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="kpi-card kpi-red">
      <div class="d-flex gap-3 align-items-center">
        <div class="kpi-icon"><i class="bi bi-arrow-down-circle"></i></div>
        <div>
          <div class="kpi-label">Total Cost</div>
          <div class="kpi-value"><?= $currency ?> <?= number_format($totCost/1000,1) ?>K</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="kpi-card <?= $totProfit >= 0 ? 'kpi-teal' : 'kpi-red' ?>">
      <div class="d-flex gap-3 align-items-center">
        <div class="kpi-icon"><i class="bi bi-currency-exchange"></i></div>
        <div>
          <div class="kpi-label">Net Profit</div>
          <div class="kpi-value"><?= $currency ?> <?= number_format($totProfit/1000,1) ?>K</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="kpi-card kpi-blue">
      <div class="d-flex gap-3 align-items-center">
        <div class="kpi-icon"><i class="bi bi-percent"></i></div>
        <div>
          <div class="kpi-label">Profit Margin</div>
          <div class="kpi-value <?= $margin < 0 ? 'text-danger':'' ?>"><?= $margin ?>%</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ── Summary by Group ────────────────────────────────────── -->
<?php if (!empty($summary)): ?>
<div class="fm-card mb-4">
  <div class="card-header-fm d-flex justify-content-between align-items-center">
    <h5><i class="bi bi-bar-chart me-2"></i>Summary by <?= esc($groupLabels[$groupBy] ?? 'Group') ?></h5>
    <span class="small text-muted"><?= count($summary) ?> <?= $groupLabels[$groupBy] ?? 'Group'?>(s)</span>
  </div>
  <div class="fm-card-body p-0">
    <table class="fm-table">
      <thead>
        <tr>
          <th><?= esc($groupLabels[$groupBy] ?? 'Group') ?></th>
          <th class="text-end">Est. Cost</th>
          <th class="text-end">Actual Cost</th>
          <th class="text-end">Revenue</th>
          <th class="text-end">Profit / Loss</th>
          <th class="text-center">Margin</th>
          <th>Revenue Bar</th>
        </tr>
      </thead>
      <tbody>
        <?php $maxRev = max(array_column(array_values($summary), 'revenue') ?: [1]); ?>
        <?php foreach ($summary as $grp => $s): ?>
        <?php $m = $s['revenue'] > 0 ? round(($s['profit']/$s['revenue'])*100,1) : 0; ?>
        <?php $barW = $maxRev > 0 ? round(($s['revenue']/$maxRev)*100) : 0; ?>
        <tr>
          <td class="fw-semibold small"><?= esc($grp) ?></td>
          <td class="small text-end text-secondary"><?= $currency ?> <?= number_format($s['estimated_cost']??0,2) ?></td>
          <td class="small text-end text-danger"><?= $currency ?> <?= number_format($s['cost'],2) ?></td>
          <td class="small text-end fw-bold text-success"><?= $currency ?> <?= number_format($s['revenue'],2) ?></td>
          <td class="small text-end fw-bold <?= $s['profit']>=0?'text-success':'text-danger' ?>"><?= $currency ?> <?= number_format($s['profit'],2) ?></td>
          <td class="text-center">
            <span class="badge <?= $m>=20?'bg-success':($m>=0?'bg-warning text-dark':'bg-danger') ?>"><?= $m ?>%</span>
          </td>
          <td style="min-width:120px">
            <div class="progress" style="height:8px;border-radius:4px">
              <div class="progress-bar bg-success" style="width:<?= $barW ?>%"></div>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr class="fw-bold" style="background:var(--fm-surface-2,#f8f9fa)">
          <td>TOTAL</td>
          <td class="text-end text-secondary"><?= $currency ?> <?= number_format($totEstCost??0,2) ?></td>
          <td class="text-end text-danger"><?= $currency ?> <?= number_format($totCost,2) ?></td>
          <td class="text-end text-success"><?= $currency ?> <?= number_format($totRevenue,2) ?></td>
          <td class="text-end <?= $totProfit>=0?'text-success':'text-danger' ?>"><?= $currency ?> <?= number_format($totProfit,2) ?></td>
          <td class="text-center"><span class="badge <?= $margin>=20?'bg-success':($margin>=0?'bg-warning text-dark':'bg-danger') ?>"><?= $margin ?>%</span></td>
          <td></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- ── Detailed Breakdown ──────────────────────────────────── -->
<div class="fm-card">
  <div class="card-header-fm d-flex justify-content-between align-items-center">
    <h5><i class="bi bi-table me-2"></i>Detailed Breakdown
      <span class="text-muted small fw-normal ms-1">(<?= esc($groupLabels[$groupBy]??'Group') ?> × <?= esc($periodLabels[$period]??'Period') ?>)</span>
    </h5>
    <div class="input-group input-group-sm" style="max-width:220px">
      <span class="input-group-text"><i class="bi bi-search"></i></span>
      <input type="text" class="form-control" id="tableSearch" placeholder="Search…">
    </div>
  </div>
  <div class="fm-card-body p-0">
    <?php if (empty($rows)): ?>
    <div class="text-center text-muted py-5">
      <i class="bi bi-inbox fs-3 d-block mb-2"></i>
      No data for the selected period. Try widening the date range or changing filters.
    </div>
    <?php else: ?>
    <table class="fm-table" id="detailTable">
      <thead>
        <tr>
          <th><?= esc($groupLabels[$groupBy] ?? 'Group') ?></th>
          <th>Period</th>
          <th class="text-end">Est. Cost</th>
          <th class="text-end">Actual Cost</th>
          <th class="text-end">Revenue</th>
          <th class="text-end">Profit / Loss</th>
          <th class="text-center">Margin</th>
          <th>P/L Bar</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $row): ?>
        <?php $m = $row['revenue'] > 0 ? round(($row['profit']/$row['revenue'])*100,1) : 0; ?>
        <tr>
          <td class="small fw-semibold"><?= esc($row['group']) ?></td>
          <td class="small text-muted"><?= esc($row['period']) ?></td>
          <td class="small text-end text-secondary"><?= $currency ?> <?= number_format($row['estimated_cost']??0,2) ?></td>
          <td class="small text-end text-danger"><?= $currency ?> <?= number_format($row['cost'],2) ?></td>
          <td class="small text-end"><?= $currency ?> <?= number_format($row['revenue'],2) ?></td>
          <td class="small text-end fw-bold <?= $row['profit']>=0?'text-success':'text-danger' ?>">
            <?= ($row['profit'] >= 0 ? '' : '−') ?><?= $currency ?> <?= number_format(abs($row['profit']),2) ?>
          </td>
          <td class="text-center">
            <span class="badge <?= $m>=20?'bg-success':($m>=0?'bg-warning text-dark':'bg-danger') ?>"><?= $m ?>%</span>
          </td>
          <td style="min-width:100px">
            <?php $bw = min(abs($m), 100); ?>
            <div class="progress" style="height:6px;border-radius:3px">
              <div class="progress-bar <?= $m>=0?'bg-success':'bg-danger' ?>" style="width:<?= $bw ?>%"></div>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<script>
(function () {
  // ── Loader ────────────────────────────────────────────────
  const form    = document.getElementById('pnlForm');
  const loader  = document.getElementById('pnlLoader');
  const spinner = document.getElementById('btnSpinner');
  const icon    = document.getElementById('btnIcon');

  form.addEventListener('submit', function () {
    loader.classList.remove('d-none');
    loader.classList.add('d-flex');
    if (spinner) { spinner.classList.remove('d-none'); }
    if (icon)    { icon.classList.add('d-none'); }
  });

  // ── Quick date ranges ─────────────────────────────────────
  const fromEl = document.getElementById('fromDate');
  const toEl   = document.getElementById('toDate');
  const today  = new Date();
  const fmt    = d => d.toISOString().slice(0,10);

  function applyQuickRange(r) {
    if (!r) return;
    const now = new Date();
    let f, t = fmt(now);
    switch (r) {
      case 'today':   f = t; break;
      case 'mtd':     f = fmt(new Date(now.getFullYear(), now.getMonth(), 1)); break;
      case 'qtd': {
        const qm = Math.floor(now.getMonth() / 3) * 3;
        f = fmt(new Date(now.getFullYear(), qm, 1)); break;
      }
      case 'ytd':     f = fmt(new Date(now.getFullYear(), 0, 1)); break;
      case 'last30':  { const d = new Date(now); d.setDate(d.getDate()-30); f = fmt(d); break; }
      case 'last12m': { const d = new Date(now); d.setFullYear(d.getFullYear()-1); f = fmt(d); break; }
      case 'all':     f = '2020-01-01'; break;
      default:        f = fmt(new Date(now.getFullYear(), 0, 1));
    }
    fromEl.value = f;
    toEl.value   = t;
  }
  const quickSel = document.getElementById('quickRangeSelect');
  if (quickSel) {
    quickSel.addEventListener('change', function () { applyQuickRange(this.value); });
  }
  // Legacy - remove old buttons handler stub
  document.querySelectorAll('.quick-range').forEach(btn => {
    btn.addEventListener('click', function () {
      const r   = this.dataset.range;
      const now = new Date();
      let f, t = fmt(now);
      switch (r) {
        case 'today':
          f = t; break;
        case 'mtd':
          f = fmt(new Date(now.getFullYear(), now.getMonth(), 1)); break;
        case 'qtd': {
          const qm = Math.floor(now.getMonth() / 3) * 3;
          f = fmt(new Date(now.getFullYear(), qm, 1)); break;
        }
        case 'ytd':
          f = fmt(new Date(now.getFullYear(), 0, 1)); break;
        case 'last30': {
          const d = new Date(now); d.setDate(d.getDate()-30);
          f = fmt(d); break;
        }
        case 'last12m': {
          const d = new Date(now); d.setFullYear(d.getFullYear()-1);
          f = fmt(d); break;
        }
        case 'all':
          f = '2020-01-01'; break;
        default: f = fmt(new Date(now.getFullYear(), 0, 1));
      }
      fromEl.value = f;
      toEl.value   = t;
    });
  });

  // ── Table search ──────────────────────────────────────────
  const searchEl = document.getElementById('tableSearch');
  if (searchEl) {
    searchEl.addEventListener('input', function () {
      const q = this.value.toLowerCase();
      document.querySelectorAll('#detailTable tbody tr').forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  }
})();
</script>
<?= $this->endSection() ?>
