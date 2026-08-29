<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1><i class="bi bi-bar-chart-line me-2"></i>Reports Portal</h1></div>
<div class="row g-3">
  <?php
  $cards = [
    ['reports/workorders', 'Work orders', 'Date, facility, status filters'],
    ['reports/finance', 'Finance / P&L', 'Revenue & expenses'],
    ['reports/kpi', 'KPI analytics', 'SLA, occupancy, trends'],
    ['finance/cash-flow', 'Cash flow', 'In/out with filters'],
    ['finance/ledger', 'Ledger', 'Income & expense trail'],
    ['costing', 'Job profit / costing', 'WO cost vs revenue'],
    ['reports/builder', 'Custom report builder', 'Pick columns & filters'],
    ['reports/profit', 'Profit report', 'Cost vs revenue export'],
    ['reports/qc', 'QC / QA report', 'QA and client approval'],
    ['site-visits', 'Site visits', 'Schedule & complete with signatures'],
  ];
  foreach ($cards as [$url, $title, $sub]):
  ?>
  <div class="col-md-4"><a href="<?= base_url($url) ?>" class="fm-card d-block text-decoration-none p-3 h-100">
    <strong><?= esc($title) ?></strong><div class="small text-muted"><?= esc($sub) ?></div>
  </a></div>
  <?php endforeach; ?>
</div>
<p class="small text-muted mt-3">Export: append <code>/csv</code>, <code>/xls</code>, or <code>/pdf</code> to <code>/reports/export/{type}/</code> (PDF requires <code>dompdf/dompdf</code>).</p>
<?= $this->endSection() ?>
