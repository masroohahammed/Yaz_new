<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1><i class="bi bi-bar-chart-line me-2"></i>PM Reports Portal</h1></div>
<div class="row g-3">
  <?php
  $cards = [
    ['reports/pm/kpi', 'KPI analytics', 'Occupancy, leases, revenue trends'],
    ['reports/pm/occupancy', 'Occupancy', 'Vacancy and rent roll by property'],
    ['reports/pm/leases', 'Lease expiry', 'Contracts ending soon'],
    ['reports/pm/invoices', 'Invoices', 'Period and status filters'],
    ['reports/pm/payments', 'Lease payments', 'Collected vs outstanding rent'],
    ['reports/pm/cheques', 'Cheques (PDC)', 'Post-dated cheque register'],
    ['finance/pm/collection-report', 'Collection report', 'Rent collection summary'],
    ['finance/pm/owner-statement', 'Owner statement', 'Landlord P&amp;L statement'],
    ['finance/pm/aging', 'AR aging', 'Receivables by bucket'],
    ['finance/pm/trial-balance', 'Trial balance', 'PM GL as of date'],
    ['finance/pm/vat-report', 'VAT report', 'Tax reporting'],
    ['reports/pm/properties', 'Property P&amp;L', 'Per-property profit and loss'],
    ['crm/reports', 'CRM funnel', 'Leads and conversion'],
    ['ai/reports', 'AI insights', 'Risk and occupancy scores'],
    ['collector/report', 'Collector daily', 'Field collection summary'],
  ];
  foreach ($cards as [$url, $title, $sub]):
  ?>
  <div class="col-md-4">
    <a href="<?= base_url($url) ?>" class="fm-card d-block text-decoration-none p-3 h-100">
      <strong><?= esc($title) ?></strong>
      <div class="small text-muted"><?= esc($sub) ?></div>
    </a>
  </div>
  <?php endforeach; ?>
</div>
<p class="small text-muted mt-3"><a href="<?= base_url('reports/pm') ?>">← Back to PM Reports hub</a></p>
<?= $this->endSection() ?>
