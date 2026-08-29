<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1><i class="bi bi-file-earmark-bar-graph me-2"></i>Financial Reports</h1></div>
<div class="row g-3">
  <div class="col-md-4"><a href="<?= base_url('reports/finance') ?>" class="fm-card d-block text-decoration-none p-3"><strong>P&amp;L / Finance Report</strong><div class="small text-muted">Operational revenue &amp; expense</div></a></div>
  <div class="col-md-4"><a href="<?= base_url('finance/cash-flow') ?>" class="fm-card d-block text-decoration-none p-3"><strong>Cash Flow</strong><div class="small text-muted">Money in/out with filters</div></a></div>
  <div class="col-md-4"><a href="<?= base_url('finance/ledger') ?>" class="fm-card d-block text-decoration-none p-3"><strong>Transaction Ledger</strong><div class="small text-muted">Paid invoices + approved expenses</div></a></div>
  <div class="col-md-4"><a href="<?= base_url('finance/trial-balance') ?>" class="fm-card d-block text-decoration-none p-3"><strong>Trial Balance</strong><div class="small text-muted">GL debits &amp; credits</div></a></div>
  <div class="col-md-4"><a href="<?= base_url('finance/balance-sheet') ?>" class="fm-card d-block text-decoration-none p-3"><strong>Balance Sheet</strong><div class="small text-muted">Assets, liabilities, equity</div></a></div>
  <div class="col-md-4"><a href="<?= base_url('finance/ar-aging') ?>" class="fm-card d-block text-decoration-none p-3"><strong>AR Aging</strong><div class="small text-muted">Outstanding invoices by bucket</div></a></div>
  <div class="col-md-4"><a href="<?= base_url('finance/bank-reconciliation') ?>" class="fm-card d-block text-decoration-none p-3"><strong>Bank Reconciliation</strong><div class="small text-muted">Receipts vs payments</div></a></div>
  <div class="col-md-4"><a href="<?= base_url('finance/gl') ?>" class="fm-card d-block text-decoration-none p-3"><strong>General Ledger</strong><div class="small text-muted">Journal entries</div></a></div>
  <div class="col-md-4"><a href="<?= base_url('reports/contracts') ?>" class="fm-card d-block text-decoration-none p-3"><strong>AMC / Contracts</strong></a></div>
  <div class="col-md-4"><a href="<?= base_url('reports/workorders') ?>" class="fm-card d-block text-decoration-none p-3"><strong>WO Costing Report</strong></a></div>
  <div class="col-md-4"><a href="<?= base_url('costing') ?>" class="fm-card d-block text-decoration-none p-3"><strong>Maintenance Costing</strong></a></div>
  <div class="col-md-4"><a href="<?= base_url('finance/payroll-finance') ?>" class="fm-card d-block text-decoration-none p-3"><strong>Payroll Finance</strong><div class="small text-muted">HR + GL payroll</div></a></div>
  <div class="col-md-4"><a href="<?= base_url('finance/budgets') ?>" class="fm-card d-block text-decoration-none p-3"><strong>Budgets</strong><div class="small text-muted">Annual budget lines</div></a></div>
</div>
<?= $this->endSection() ?>
