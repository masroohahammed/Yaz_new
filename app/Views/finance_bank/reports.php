<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1><i class="bi bi-file-earmark-bar-graph me-2"></i>Financial Reports</h1></div>
<div class="row g-3">
  <?php
  $reports = [
    ['Bank Account Statement', 'bank-statement'],
    ['Bank Balance Report', 'bank-balance'],
    ['Bank Deposit Report', 'deposits'],
    ['Bank Withdrawal Report', 'withdrawals'],
    ['Bank Transfer Report', 'transfers'],
    ['Income vs Expense', 'income-vs-expense'],
    ['Net Cash Flow', 'cash-flow'],
    ['Property Profitability', 'property-profit'],
    ['Transaction Audit', 'audit'],
  ];
  foreach ($reports as [$label, $slug]):
  ?>
  <div class="col-md-4"><a href="<?= base_url('finance-bank/reports/'.$slug) ?>" class="fm-card p-3 d-block text-decoration-none h-100"><div class="fw-semibold text-dark"><?= esc($label) ?></div><div class="small text-muted">View & export</div></a></div>
  <?php endforeach; ?>
</div>
<?= $this->endSection() ?>
