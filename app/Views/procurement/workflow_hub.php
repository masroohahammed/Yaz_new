<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1><i class="bi bi-diagram-3 me-2"></i>Procurement Workflow</h1></div>
<ol class="list-group list-group-numbered mb-4">
  <li class="list-group-item d-flex justify-content-between"><span>Purchase Request (PR)</span><a href="<?= base_url('procurement') ?>">Open PRs</a></li>
  <li class="list-group-item">Approval workflow <span class="text-muted small">— on each PR</span></li>
  <li class="list-group-item d-flex justify-content-between"><span>RFQ to suppliers</span><a href="<?= base_url('procurement') ?>">RFQ list</a></li>
  <li class="list-group-item">Supplier quotations &amp; comparison <span class="text-muted small">— inside RFQ</span></li>
  <li class="list-group-item d-flex justify-content-between"><span>PO / LPO</span><a href="<?= base_url('purchase-orders') ?>">Purchase orders</a></li>
  <li class="list-group-item">Supplier delivery</li>
  <li class="list-group-item">GRN / stock update <span class="text-muted small">— from PO</span></li>
  <li class="list-group-item d-flex justify-content-between"><span>Supplier invoice / AP</span><a href="<?= base_url('finance/vendor-bills') ?>">Vendor bills</a></li>
  <li class="list-group-item">3-way matching <span class="badge bg-secondary">Phase 2</span></li>
  <li class="list-group-item d-flex justify-content-between"><span>Payment</span><a href="<?= base_url('finance/payments') ?>">Payments</a></li>
</ol>
<a href="<?= base_url('procurement') ?>" class="btn btn-fm-primary btn-sm">Go to procurement</a>
<?= $this->endSection() ?>
