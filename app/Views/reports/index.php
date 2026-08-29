<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div><h1><i class="bi bi-bar-chart-line me-2"></i>Reports</h1></div>
  <a href="<?= base_url('reports/portal') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-grid me-1"></i>Reports Portal</a>
</div>

<?php
$reports = [
  ['kpi',          'bi-graph-up-arrow',     'KPI Analytics',          'SLA, revenue, facility performance dashboards',          'blue'],
  ['workorders',   'bi-tools',              'Work Orders',             'Analyse WO status, priority, SLA, costs',                'primary'],
  ['finance',      'bi-receipt',            'Finance',                 'Revenue, expenses, invoices, profitability',              'green'],
  ['pnl',          'bi-currency-dollar',    'P&amp;L Report',          'Property, work-order &amp; customer-wise profit &amp; loss — day / month / year', 'teal'],
  ['sla',          'bi-shield-check',       'SLA Performance',         'Compliance rate, breach analysis by priority',            'blue'],
  ['assets',       'bi-cpu',                'Assets',                  'Asset register, health scores, warranty status',          'teal'],
  ['occupancy',    'bi-grid',               'Occupancy',               'Unit occupancy, vacancies, expiring contracts',           'secondary'],
  ['contracts',    'bi-file-earmark-text',  'Contracts / Expiry',      'Active, expiring, renewed, cancelled contracts',          'orange'],
  ['technician',   'bi-person-gear',        'Technician Performance',  'Completion rates, resolution time, SLA breaches',         'purple'],
  ['procurement',  'bi-cart',               'Procurement',             'Purchase orders, vendor spend, request status',           'gold'],
  ['inventory',    'bi-box-seam',           'Inventory',               'Stock levels, movements, low stock alerts, valuation',    'red'],
  ['builder',      'bi-sliders',            'Custom Report Builder',   'Build any report — pick columns, filters, date range, export', 'primary'],
];
if (in_array(session()->get('user_role'), ['super_admin','facility_manager','finance_manager'])) {
  array_splice($reports, 3, 0, [[
    'financial-internal', 'bi-lock', 'Internal Financial',
    'Est vs actual, WO profitability, material variance, monthly summary', 'red',
  ]]);
}
if (session()->get('user_role') === 'super_admin') {
  array_unshift($reports, ['activity-log', 'bi-journal-text', 'System Activity Log', 'Who changed what — users, actions, IP, timestamps', 'secondary']);
}
?>

<div class="row g-3">
<?php foreach($reports as [$slug,$icon,$name,$desc,$color]): ?>
<div class="col-md-4">
  <a href="<?= base_url('reports/'.$slug) ?>" class="text-decoration-none">
    <div class="fm-card h-100"
         style="transition:.2s;cursor:pointer"
         onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,.12)'"
         onmouseout="this.style.transform='';this.style.boxShadow=''">
      <div class="fm-card-body">
        <div class="d-flex align-items-center gap-3 mb-2">
          <div class="kpi-icon kpi-<?= $color ?>" style="border-radius:12px"><i class="bi <?= $icon ?> fs-5"></i></div>
          <div class="fw-bold"><?= $name ?></div>
        </div>
        <p class="small text-muted mb-2"><?= $desc ?></p>
        <div class="d-flex gap-1">
          <span class="x-small text-muted"><i class="bi bi-filetype-csv me-1"></i>CSV</span>
          <span class="x-small text-muted ms-2"><i class="bi bi-file-earmark-excel me-1"></i>Excel</span>
        </div>
      </div>
    </div>
  </a>
</div>
<?php endforeach; ?>
</div>
<?= $this->endSection() ?>
