<?php
/**
 * PM report card definitions — shared by PM hub and admin reports hub.
 *
 * @var string $urlPrefix Base path before slug (default: reports/pm)
 */
$urlPrefix = $urlPrefix ?? 'reports/pm';

$reports = [
  ['kpi',           'bi-graph-up-arrow',      'KPI Analytics',           'Occupancy, leases, revenue, receivables, maintenance', 'blue'],
  ['occupancy',     'bi-grid',                'Occupancy',               'Unit occupancy, vacancies, expiring unit contracts',   'secondary'],
  ['leases',        'bi-file-earmark-text',   'Lease Expiry',            'Active leases, expiring contracts, tenant details',    'orange'],
  ['invoices',      'bi-receipt',             'Invoices',                'Invoice listing by period, status, and property',      'green'],
  ['payments',      'bi-credit-card',         'Lease Payments',          'Rent schedules, collected vs due',                     'teal'],
  ['cheques',       'bi-bank',                'Cheques (PDC)',           'Post-dated cheques by status and property',            'gold'],
  ['expenses',      'bi-wallet2',             'Expenses',                'Approved and pending expenses by property',            'red'],
  ['properties',    'bi-building',            'Property P&amp;L',         'Open profit &amp; loss for each property',             'primary'],
];

$financeReports = [
  ['finance/pm/collection-report', 'bi-cash-stack',       'Collection Report',  'Rent collected by period and property'],
  ['finance/pm/vat-report',        'bi-percent',          'VAT Report',         'VAT collected and reportable amounts'],
  ['finance/pm/aging',             'bi-hourglass-split',  'AR Aging',           'Outstanding receivables by age bucket'],
  ['finance/pm/trial-balance',     'bi-journal-text',     'Trial Balance',      'PM ledger trial balance as of date'],
  ['finance/pm/owner-statement',   'bi-person-badge',     'Owner Statement',    'Landlord income and expense statement'],
  ['finance/pm/ledger',            'bi-journal',          'Finance Ledger',     'PM general ledger entries'],
  ['finance/reports',              'bi-file-earmark-bar-graph', 'Financial Reports Hub', 'Cash flow, GL, balance sheet, budgets'],
];

$otherReports = [
  ['crm/reports',        'bi-person-lines-fill', 'CRM Reports',       'Lead funnel, sources, and locations'],
  ['ai/reports',         'bi-robot',             'AI Reports',        'Risk flags and occupancy scores'],
  ['collector/report',   'bi-printer',           'Collector Daily',   'Daily cash collection summary'],
  ['budgets',            'bi-bar-chart',         'Budgets',           'Annual budget lines and variance'],
  ['utilities',          'bi-lightning-charge',  'Utilities',         'Utility billing and consumption'],
  ['pm-inspections',     'bi-clipboard2-pulse',  'Inspections',       'Unit inspection records and printouts'],
];
?>

<div class="row g-3 mb-3">
<?php foreach ($reports as [$slug, $icon, $name, $desc, $color]): ?>
<div class="col-md-4">
  <a href="<?= base_url($urlPrefix.'/'.$slug) ?>" class="text-decoration-none">
    <div class="fm-card h-100" style="transition:.2s;cursor:pointer"
         onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,.12)'"
         onmouseout="this.style.transform='';this.style.boxShadow=''">
      <div class="fm-card-body">
        <div class="d-flex align-items-center gap-3 mb-2">
          <div class="kpi-icon kpi-<?= $color ?>" style="border-radius:12px"><i class="bi <?= $icon ?> fs-5"></i></div>
          <div class="fw-bold"><?= $name ?></div>
        </div>
        <p class="small text-muted mb-0"><?= $desc ?></p>
      </div>
    </div>
  </a>
</div>
<?php endforeach; ?>
</div>

<div class="mb-2"><h6 class="text-muted small fw-bold">FINANCE &amp; LEDGER</h6></div>
<div class="row g-3 mb-3">
<?php foreach ($financeReports as [$url, $icon, $name, $desc]): ?>
<div class="col-md-4">
  <a href="<?= base_url($url) ?>" class="text-decoration-none">
    <div class="fm-card h-100 p-3">
      <div class="d-flex align-items-center gap-3 mb-2">
        <div class="kpi-icon kpi-green" style="border-radius:12px"><i class="bi <?= $icon ?> fs-5"></i></div>
        <div class="fw-bold"><?= esc($name) ?></div>
      </div>
      <p class="small text-muted mb-0"><?= esc($desc) ?></p>
    </div>
  </a>
</div>
<?php endforeach; ?>
</div>

<div class="mb-2"><h6 class="text-muted small fw-bold">CRM, AI &amp; OPERATIONS</h6></div>
<div class="row g-3">
<?php foreach ($otherReports as [$url, $icon, $name, $desc]): ?>
<div class="col-md-4">
  <a href="<?= base_url($url) ?>" class="text-decoration-none">
    <div class="fm-card h-100 p-3">
      <div class="d-flex align-items-center gap-3 mb-2">
        <div class="kpi-icon kpi-primary" style="border-radius:12px"><i class="bi <?= $icon ?> fs-5"></i></div>
        <div class="fw-bold"><?= esc($name) ?></div>
      </div>
      <p class="small text-muted mb-0"><?= esc($desc) ?></p>
    </div>
  </a>
</div>
<?php endforeach; ?>
</div>
