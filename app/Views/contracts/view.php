<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
helper('fm');
$c = $contract;
$d = $data360 ?? [];
$cid = (int) $c['id'];
$documents = $d['documents'] ?? [];
?>

<div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
  <div>
    <h1><i class="bi bi-file-earmark-text me-2"></i><?= esc($c['contract_number'] ?? 'Contract') ?></h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb small mb-0">
      <li class="breadcrumb-item"><a href="<?= base_url('contracts') ?>">Contracts</a></li>
      <li class="breadcrumb-item active"><?= esc($c['contract_number'] ?? $cid) ?></li>
    </ol></nav>
    <?php if (! empty($c['is_blacklisted'])): ?>
    <div class="alert alert-danger py-1 px-2 small mt-2 d-inline-block">⚠ Tenant is blacklisted</div>
    <?php endif; ?>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <span class="fm-badge badge-status-<?= esc($c['status']) ?> align-self-center"><?= ucfirst($c['status']) ?></span>
    <a href="<?= base_url('contracts/' . $cid . '/edit') ?>" class="btn btn-sm btn-fm-outline">Edit</a>
    <a href="<?= base_url('contracts/' . $cid . '/print') ?>" class="btn btn-sm btn-outline-info" target="_blank">Print</a>
    <a href="<?= base_url('contracts/' . $cid . '/print?pdf=1') ?>" class="btn btn-sm btn-outline-info">PDF</a>
    <?php if ($c['status'] === 'active'): ?>
    <a href="<?= base_url('contracts/' . $cid . '/amendment') ?>" class="btn btn-sm btn-outline-secondary">Amendment</a>
    <a href="<?= base_url('contracts/' . $cid . '/renew') ?>" class="btn btn-sm btn-outline-primary">Renew</a>
    <a href="<?= base_url('contracts/' . $cid . '/terminate') ?>" class="btn btn-sm btn-outline-danger">Terminate</a>
  <?= form_open(base_url('contracts/' . $cid . '/penalties'), ['class' => 'd-inline fm-submit-form', 'onsubmit' => 'return confirm("Apply late penalties using this contract\'s configured rate?");']) ?><?= csrf_field() ?><button class="btn btn-sm btn-warning fm-submit-btn">Apply Penalties</button><?= form_close() ?>
  <?= form_open(base_url('contracts/' . $cid . '/generate-invoices'), ['class' => 'd-inline fm-submit-form', 'onsubmit' => 'return confirm("Generate invoices? Existing periods will be skipped.");']) ?><?= csrf_field() ?><button class="btn btn-sm btn-outline-secondary fm-submit-btn">Regenerate Invoices</button><?= form_close() ?>
    <?php endif; ?>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-3"><div class="fm-card p-3 small"><div class="text-muted">Tenant</div><div class="fw-semibold"><a href="<?= base_url('tenants/' . $c['tenant_id']) ?>"><?= esc($c['tenant_name']) ?></a></div></div></div>
  <div class="col-md-3"><div class="fm-card p-3 small"><div class="text-muted">Property / Unit</div><div><?= esc($c['property_name']) ?> — Unit <?= esc($c['unit_number']) ?></div></div></div>
  <div class="col-md-3"><div class="fm-card p-3 small"><div class="text-muted">Rent</div><div class="fw-bold"><?= $currency ?> <?= number_format((float) $c['rent_amount'], 0) ?></div></div></div>
  <div class="col-md-3"><div class="fm-card p-3 small"><div class="text-muted">Period</div><div><?= esc($c['start_date']) ?> → <?= esc($c['end_date']) ?></div></div></div>
</div>

<ul class="nav fm-entity-tabs">
  <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-details">Details</a></li>
  <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-payments">Payments</a></li>
  <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-schedule">Rent schedule</a></li>
  <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-amendments">Amendments</a></li>
  <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-documents">Documents <span class="badge bg-secondary ms-1"><?= count($documents) ?></span></a></li>
</ul>

<div class="tab-content fm-tab-pane">
  <div class="tab-pane fade show active" id="tab-details">
    <div class="fm-card p-3 small row g-2">
      <div class="col-md-4"><span class="text-muted">Frequency:</span> <?= esc($c['payment_frequency']) ?></div>
      <div class="col-md-4"><span class="text-muted">Deposit:</span> <?= number_format((float) ($c['security_deposit'] ?? 0), 0) ?></div>
      <div class="col-md-4"><span class="text-muted">Billing start:</span> <?= esc($c['billing_start_date'] ?? '—') ?></div>
      <?php if (! empty($c['contract_terms'])): ?><div class="col-12 mt-2"><?= esc($c['contract_terms']) ?></div><?php endif; ?>
    </div>
    <?= form_open(base_url('contracts/' . $cid . '/save-print'), ['class' => 'fm-card p-3 mt-3']) ?>
    <?= csrf_field() ?>
    <h6 class="mb-2">Print content (bilingual)</h6>
    <textarea name="custom_content_en" class="form-control form-control-sm mb-2 fm-tinymce" rows="4"><?= esc($c['custom_content_en'] ?? '') ?></textarea>
    <textarea name="custom_content_ar" class="form-control form-control-sm mb-2 fm-tinymce-rtl" rows="4"><?= esc($c['custom_content_ar'] ?? '') ?></textarea>
    <button class="btn btn-sm btn-fm-primary">Save for Print</button>
    <?= form_close() ?>
  </div>
  <div class="tab-pane fade" id="tab-payments">
    <table class="fm-table"><thead><tr><th>#</th><th>Type</th><th>Amount</th><th>Due</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($d['payments'] ?? [] as $p): ?>
    <tr>
      <td class="small"><a href="<?= base_url('payments/' . $p['id']) ?>"><?= esc($p['payment_number'] ?? $p['id']) ?></a></td>
      <td><?= esc($p['payment_type'] ?? '') ?></td>
      <td><?= number_format((float) $p['amount'], 2) ?></td>
      <td><?= esc($p['due_date'] ?? '') ?></td>
      <td><?= ucfirst($p['status'] ?? '') ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody></table>
  </div>
  <div class="tab-pane fade" id="tab-schedule">
    <table class="fm-table"><thead><tr><th>Year</th><th>Rent</th></tr></thead>
    <tbody>
    <?php foreach ($d['rentSchedule'] ?? [] as $yr): ?>
    <tr><td>Year <?= (int) $yr['year_number'] ?></td><td><?= number_format((float) $yr['rent_amount'], 2) ?></td></tr>
    <?php endforeach; ?>
    </tbody></table>
  </div>
  <div class="tab-pane fade" id="tab-amendments">
    <?php foreach ($d['amendments'] ?? [] as $a): ?>
    <div class="border-bottom py-2 small"><?= esc($a['effective_date'] ?? '') ?> — <?= esc($a['description'] ?? '') ?></div>
    <?php endforeach; ?>
    <?php if (empty($d['amendments'])): ?><p class="text-muted small">No amendments.</p><?php endif; ?>
  </div>
  <div class="tab-pane fade" id="tab-documents">
    <?= $this->include('documents/_tab', [
      'module' => 'leases',
      'refId' => $cid,
      'embed' => true,
      'documents' => $documents,
      'docTypes' => fm_document_types(),
      'facilityId' => (int) ($c['facility_id'] ?? $c['property_id'] ?? 0),
      'unitId' => (int) ($c['unit_id'] ?? 0),
      'tenantId' => (int) ($c['tenant_id'] ?? 0),
      'contractId' => $cid,
    ]) ?>
  </div>
</div>

<?= view('documents/_embed_modals', [
  'module'     => 'leases',
  'refId'      => $cid,
  'docTypes'   => fm_document_types(),
  'facilityId' => (int) ($c['facility_id'] ?? $c['property_id'] ?? 0),
  'unitId'     => (int) ($c['unit_id'] ?? 0),
  'tenantId'   => (int) ($c['tenant_id'] ?? 0),
  'contractId' => $cid,
]) ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?= $this->include('partials/tinymce') ?>
<?= $this->endSection() ?>
