<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$l = $landlord;
$d = $data360 ?? [];
helper('fm');
$docTypes = $docTypes ?? fm_document_types();
?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-person-badge me-2"></i><?= esc($l['full_name']) ?></h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= base_url('landlords') ?>">Landlords</a></li>
      <li class="breadcrumb-item active"><?= esc($l['full_name']) ?></li>
    </ol></nav>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <span class="fm-badge badge-status-<?= esc($l['status']) ?> align-self-center"><?= ucfirst($l['status']) ?></span>
    <a href="<?= base_url('reports/pm/landlord?landlord=' . (int) $l['id']) ?>" class="btn btn-sm btn-fm-outline">Landlord Reports</a>
    <a href="<?= base_url('landlords/' . $l['id'] . '/payout') ?>" class="btn btn-sm btn-fm-primary">Create Payout</a>
    <a href="<?= base_url('landlords/' . $l['id'] . '/payouts') ?>" class="btn btn-sm btn-outline-primary">All Payouts</a>
    <a href="<?= base_url('landlords/' . $l['id'] . '/edit') ?>" class="btn btn-sm btn-fm-outline"><i class="bi bi-pencil"></i> Edit</a>
  </div>
</div>

<?php if (! empty($d['reminders'])): ?>
<div class="mb-3">
  <?php foreach ($d['reminders'] as $rem): ?>
  <div class="alert alert-<?= esc($rem['severity'] ?? 'info') ?> d-flex justify-content-between align-items-center py-2">
    <span class="small"><?= esc($rem['message']) ?></span>
    <?= form_open(base_url('landlords/' . $l['id'] . '/reminders/dismiss'), ['class' => 'ms-2']) ?>
      <?= csrf_field() ?>
      <input type="hidden" name="reminder_key" value="<?= esc($rem['key']) ?>">
      <?php if (! empty($rem['reminder_id'])): ?>
      <input type="hidden" name="reminder_id" value="<?= (int) $rem['reminder_id'] ?>">
      <?php endif; ?>
      <button type="submit" class="btn btn-sm btn-outline-secondary">Dismiss</button>
    <?= form_close() ?>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="row g-3 mb-3">
  <div class="col-6 col-md-3"><div class="kpi-card kpi-primary"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-building"></i></div><div><div class="kpi-label">Properties</div><div class="kpi-value"><?= count($d['properties'] ?? []) ?></div></div></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-teal"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-cash-coin"></i></div><div><div class="kpi-label">Payouts</div><div class="kpi-value"><?= count($d['payouts'] ?? []) ?></div></div></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-blue"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-file-earmark"></i></div><div><div class="kpi-label">Documents</div><div class="kpi-value"><?= count($d['documents'] ?? []) ?></div></div></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-orange"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-file-earmark-text"></i></div><div><div class="kpi-label">Active leases</div><div class="kpi-value"><?= count($d['activeLeases'] ?? []) ?></div></div></div></div></div>
</div>

<ul class="nav nav-tabs mb-3">
  <li class="nav-item"><a class="nav-link active" href="#tab-profile" data-bs-toggle="tab">Profile</a></li>
  <li class="nav-item"><a class="nav-link" href="#tab-properties" data-bs-toggle="tab">Properties</a></li>
  <li class="nav-item"><a class="nav-link" href="#tab-payouts" data-bs-toggle="tab">Payouts</a></li>
  <li class="nav-item"><a class="nav-link" href="#tab-documents" data-bs-toggle="tab">Documents</a></li>
</ul>

<div class="tab-content">
  <div class="tab-pane fade show active" id="tab-profile">
    <div class="row g-3">
      <div class="col-md-6">
        <div class="fm-form-section">
          <h6>Contact</h6>
          <div class="small mb-1"><span class="text-muted">Phone:</span> <?= esc($l['phone'] ?? '') ?></div>
          <?php if (! empty($l['phone2'])): ?><div class="small mb-1"><span class="text-muted">Phone 2:</span> <?= esc($l['phone2']) ?></div><?php endif; ?>
          <?php if (! empty($l['email'])): ?><div class="small mb-1"><span class="text-muted">Email:</span> <?= esc($l['email']) ?></div><?php endif; ?>
          <?php if (! empty($l['nationality'])): ?><div class="small mb-1"><span class="text-muted">Nationality:</span> <?= esc($l['nationality']) ?></div><?php endif; ?>
          <?php if (! empty($l['address'])): ?><div class="small"><span class="text-muted">Address:</span> <?= esc($l['address']) ?></div><?php endif; ?>
        </div>
      </div>
      <div class="col-md-6">
        <div class="fm-form-section">
          <h6>ID & banking</h6>
          <?php if (! empty($l['id_type']) || ! empty($l['id_number'])): ?>
          <div class="small mb-1"><span class="text-muted">ID:</span> <?= esc($l['id_type'] ?? '') ?> <?= esc($l['id_number'] ?? '') ?></div>
          <?php endif; ?>
          <?php if (! empty($l['id_expiry'])): ?>
          <div class="small mb-1"><span class="text-muted">ID expiry:</span> <?= esc($l['id_expiry']) ?></div>
          <?php endif; ?>
          <?php if (! empty($l['bank_name'])): ?><div class="small mb-1"><span class="text-muted">Bank:</span> <?= esc($l['bank_name']) ?></div><?php endif; ?>
          <?php if (! empty($l['bank_iban'])): ?><div class="small mb-1"><span class="text-muted">IBAN:</span> <?= esc($l['bank_iban']) ?></div><?php endif; ?>
          <?php if (! empty($l['commission_pct'])): ?><div class="small"><span class="text-muted">Commission:</span> <?= number_format((float) $l['commission_pct'], 2) ?>%</div><?php endif; ?>
        </div>
        <?php if (! empty($l['notes'])): ?>
        <div class="fm-form-section mt-3">
          <h6>Notes</h6>
          <p class="small mb-0"><?= esc($l['notes']) ?></p>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="tab-pane fade" id="tab-properties">
    <div class="fm-card p-0">
      <table class="table table-sm mb-0">
        <thead class="table-light"><tr><th>Property</th><th>Status</th><th>City</th></tr></thead>
        <tbody>
          <?php if (empty($d['properties'])): ?>
          <tr><td colspan="3" class="text-muted text-center py-3">No linked properties.</td></tr>
          <?php else: ?>
          <?php foreach ($d['properties'] as $p): ?>
          <tr>
            <td><a href="<?= base_url('properties/view/' . $p['id']) ?>"><?= esc($p['name']) ?></a></td>
            <td><?= esc($p['status'] ?? '') ?></td>
            <td><?= esc($p['city'] ?? '—') ?></td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php if (! empty($d['activeLeases'])): ?>
    <h6 class="mt-3">Active leases on portfolio</h6>
    <div class="fm-card p-0">
      <table class="table table-sm mb-0">
        <thead class="table-light"><tr><th>Contract</th><th>Property</th><th>Tenant</th><th>End</th><th>Rent</th></tr></thead>
        <tbody>
          <?php foreach ($d['activeLeases'] as $lc): ?>
          <tr>
            <td><a href="<?= base_url('contracts/' . $lc['id']) ?>"><?= esc($lc['contract_number'] ?? $lc['id']) ?></a></td>
            <td><?= esc($lc['property_name'] ?? '') ?> <?= ! empty($lc['unit_number']) ? '— Unit ' . esc($lc['unit_number']) : '' ?></td>
            <td><?= esc($lc['tenant_name'] ?? '') ?></td>
            <td><?= esc($lc['end_date'] ?? '') ?></td>
            <td><?= number_format((float) ($lc['rent_amount'] ?? 0), 0) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>

  <div class="tab-pane fade" id="tab-payouts">
    <div class="d-flex justify-content-between mb-2">
      <span class="small text-muted">Recent payouts</span>
      <a href="<?= base_url('landlords/' . $l['id'] . '/payout') ?>" class="btn btn-sm btn-fm-primary">Create Payout</a>
    </div>
    <div class="fm-card p-0">
      <table class="table table-sm mb-0">
        <thead class="table-light"><tr><th>Period</th><th>Property</th><th>Gross</th><th>Net</th><th>Status</th><th></th></tr></thead>
        <tbody>
          <?php if (empty($d['payouts'])): ?>
          <tr><td colspan="6" class="text-muted text-center py-3">No payouts yet.</td></tr>
          <?php else: ?>
          <?php foreach ($d['payouts'] as $po): ?>
          <tr>
            <td class="small"><?= esc($po['period_from'] ?? '') ?> — <?= esc($po['period_to'] ?? '') ?></td>
            <td class="small"><?= esc($po['property_name'] ?? '—') ?></td>
            <td><?= number_format((float) ($po['gross_rent'] ?? 0), 2) ?></td>
            <td><?= number_format((float) ($po['net_amount'] ?? 0), 2) ?></td>
            <td><span class="fm-badge badge-status-<?= esc($po['status']) ?>"><?= ucfirst($po['status']) ?></span></td>
            <td>
              <?php if (($po['status'] ?? '') !== 'paid'): ?>
              <?= form_open(base_url('landlords/payouts/' . $po['id'] . '/mark-paid'), ['class' => 'd-inline', 'onsubmit' => 'return confirm("Mark this payout as paid?");']) ?>
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-outline-success">Confirm Paid</button>
              <?= form_close() ?>
              <?php else: ?>
              <span class="small text-muted"><?= esc($po['paid_date'] ?? '') ?></span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="tab-pane fade" id="tab-documents">
    <div class="fm-form-section mb-3">
      <h6>Add Document</h6>
      <?= form_open_multipart(base_url('landlords/' . $l['id'] . '/documents')) ?>
        <?= csrf_field() ?>
        <div class="row g-2 align-items-end">
          <div class="col-md-4">
            <label class="form-label small">File *</label>
            <input type="file" name="doc_file" class="form-control form-control-sm" required>
          </div>
          <div class="col-md-3">
            <label class="form-label small">Type</label>
            <select name="doc_type" class="form-select form-select-sm">
              <?php foreach ($docTypes as $k => $label): ?>
              <option value="<?= $k ?>"><?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label small">Notes</label>
            <input type="text" name="notes" class="form-control form-control-sm">
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-sm btn-fm-primary w-100">Upload</button>
          </div>
        </div>
      <?= form_close() ?>
    </div>
    <div class="fm-card p-0">
      <table class="table table-sm mb-0">
        <thead class="table-light"><tr><th>File</th><th>Type</th><th>Uploaded</th><th></th></tr></thead>
        <tbody>
          <?php if (empty($d['documents'])): ?>
          <tr><td colspan="4" class="text-muted text-center py-3">No documents.</td></tr>
          <?php else: ?>
          <?php foreach ($d['documents'] as $doc): ?>
          <tr>
            <td class="small"><?= esc($doc['title'] ?? '') ?></td>
            <td class="small"><?= esc($doc['doc_type'] ?? '') ?></td>
            <td class="small"><?= esc($doc['created_at'] ?? '') ?></td>
            <td class="text-end">
              <?php if (! empty($doc['file_path'])): ?>
              <a href="<?= base_url('file/documents/' . basename($doc['file_path'])) ?>" class="btn btn-sm btn-outline-secondary" target="_blank">View</a>
              <?php endif; ?>
              <?= form_open(base_url('landlords/documents/' . $doc['id'] . '/delete'), ['class' => 'd-inline', 'onsubmit' => 'return confirm("Delete document?")']) ?>
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
              <?= form_close() ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
