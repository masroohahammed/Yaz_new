<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
helper('fm');
$t = $tenant;
$d = $data360 ?? [];
$active = $d['activeLease'] ?? null;
?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-person me-2"></i><?= esc($t['full_name']) ?></h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= base_url('tenants') ?>">Tenants</a></li>
      <li class="breadcrumb-item active"><?= esc($t['full_name']) ?></li>
    </ol></nav>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <span class="fm-badge badge-status-<?= esc($t['status']) ?> align-self-center"><?= ucfirst($t['status']) ?></span>
    <?php if (empty($t['is_blacklisted']) && ($t['status'] ?? '') !== 'blacklisted'): ?>
    <?= form_open(base_url('tenants/' . $t['id'] . '/blacklist'), ['class' => 'd-inline fm-submit-form', 'onsubmit' => 'return confirm("Blacklist this tenant? They will be blocked from new leases.");']) ?>
      <?= csrf_field() ?>
      <input type="hidden" name="reason" value="Blacklisted from tenant profile">
      <button type="submit" class="btn btn-sm btn-warning">Blacklist Tenant</button>
    <?= form_close() ?>
    <?php else: ?>
    <?= form_open(base_url('tenants/' . $t['id'] . '/unblacklist'), ['class' => 'd-inline', 'onsubmit' => 'return confirm("Remove this tenant from the blacklist?");']) ?>
      <?= csrf_field() ?>
      <input type="hidden" name="reason" value="Removed from blacklist">
      <button type="submit" class="btn btn-sm btn-outline-warning">Remove Blacklist</button>
    <?= form_close() ?>
    <?php endif; ?>
    <a href="<?= base_url('contracts/create?tenant_id=' . $t['id']) ?>" class="btn btn-sm btn-fm-primary">New Contract</a>
    <a href="<?= base_url('payments/create?tenant_id=' . $t['id']) ?>" class="btn btn-sm btn-outline-primary">Record Payment</a>
    <a href="<?= base_url('helpdesk/create?requester_phone=' . urlencode($t['phone'] ?? '')) ?>" class="btn btn-sm btn-outline-secondary">Add Complaint</a>
    <a href="<?= base_url('tenants/' . $t['id'] . '/edit') ?>" class="btn btn-sm btn-fm-outline"><i class="bi bi-pencil"></i> Edit</a>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-6 col-md-3"><div class="kpi-card kpi-primary"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-file-earmark-text"></i></div><div><div class="kpi-label">Leases</div><div class="kpi-value"><?= count($d['leaseHistory'] ?? []) ?></div></div></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-orange"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-exclamation-circle"></i></div><div><div class="kpi-label">Outstanding</div><div class="kpi-value"><?= number_format((float) ($d['outstanding']['total'] ?? 0), 0) ?></div><div class="kpi-sub"><?= (int) ($d['outstanding']['count'] ?? 0) ?> invoice(s)</div></div></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-teal"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-bank"></i></div><div><div class="kpi-label">Cheques</div><div class="kpi-value"><?= count($d['cheques'] ?? []) ?></div></div></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-blue"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-tools"></i></div><div><div class="kpi-label">Work Orders</div><div class="kpi-value"><?= count($d['workOrders'] ?? []) ?></div></div></div></div></div>
</div>

<ul class="nav fm-entity-tabs">
  <li class="nav-item"><a class="nav-link active" href="#tab-profile" data-bs-toggle="tab">Profile</a></li>
  <li class="nav-item"><a class="nav-link" href="#tab-leases" data-bs-toggle="tab">Leases</a></li>
  <li class="nav-item"><a class="nav-link" href="#tab-payments" data-bs-toggle="tab">Payments</a></li>
  <li class="nav-item"><a class="nav-link" href="#tab-cheques" data-bs-toggle="tab">Cheques</a></li>
  <li class="nav-item"><a class="nav-link" href="#tab-service" data-bs-toggle="tab">Service</a></li>
  <li class="nav-item"><a class="nav-link" href="#tab-documents" data-bs-toggle="tab">Documents <span class="badge bg-secondary ms-1"><?= count($d['documents'] ?? []) ?></span></a></li>
</ul>

<div class="tab-content fm-tab-pane">
  <div class="tab-pane fade show active" id="tab-profile">
    <div class="row g-3">
      <div class="col-md-6">
        <div class="fm-form-section">
          <h6>Contact</h6>
          <div class="small mb-1"><span class="text-muted">Phone:</span> <?= esc($t['phone']) ?></div>
          <?php if (! empty($t['whatsapp'])): ?><div class="small mb-1"><span class="text-muted">WhatsApp:</span> <?= esc($t['whatsapp']) ?></div><?php endif; ?>
          <?php if (! empty($t['email'])): ?><div class="small mb-1"><span class="text-muted">Email:</span> <?= esc($t['email']) ?></div><?php endif; ?>
          <div class="small mb-1"><span class="text-muted">Type:</span> <?= esc($t['tenant_type'] ?? '') ?></div>
          <?php if (! empty($t['portal_user_name'])): ?>
          <div class="small"><span class="text-muted">Portal user:</span> <?= esc($t['portal_user_name']) ?></div>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-md-6">
        <div class="fm-form-section">
          <h6>Current residence</h6>
          <?php if ($active || ! empty($t['current_unit_number'])): ?>
          <div class="small mb-1"><span class="text-muted">Property:</span>
            <?php if (! empty($t['current_facility_id'])): ?>
            <a href="<?= base_url('properties/view/' . $t['current_facility_id']) ?>"><?= esc($t['current_property_name'] ?? $active['property_name'] ?? '') ?></a>
            <?php else: ?><?= esc($active['property_name'] ?? '') ?><?php endif; ?>
          </div>
          <div class="small mb-1"><span class="text-muted">Unit:</span>
            <?php if (! empty($t['current_unit_id'])): ?>
            <a href="<?= base_url('units/view/' . $t['current_unit_id']) ?>">Unit <?= esc($t['current_unit_number'] ?? $active['unit_number'] ?? '') ?></a>
            <?php else: ?>Unit <?= esc($active['unit_number'] ?? '') ?><?php endif; ?>
          </div>
          <?php if ($active): ?>
          <div class="small mb-1"><span class="text-muted">Rent:</span> <?= $currency ?> <?= number_format((float) $active['rent_amount'], 0) ?></div>
          <div class="small"><span class="text-muted">Contract end:</span> <?= esc($active['end_date'] ?? '') ?></div>
          <?php endif; ?>
          <?php else: ?>
          <p class="small text-muted mb-0">No active lease or assigned unit.</p>
          <?php endif; ?>
        </div>
        <?php if (! empty($t['qid_no']) || ! empty($t['passport_no'])): ?>
        <div class="fm-form-section mt-3">
          <h6>Documents (ID)</h6>
          <?php if (! empty($t['qid_no'])): ?><div class="small">QID: <?= esc($t['qid_no']) ?> <?= ! empty($t['qid_expiry']) ? '(exp ' . esc($t['qid_expiry']) . ')' : '' ?></div><?php endif; ?>
          <?php if (! empty($t['passport_no'])): ?><div class="small">Passport: <?= esc($t['passport_no']) ?></div><?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="tab-pane fade" id="tab-leases">
    <div class="fm-card p-0">
      <table class="fm-table">
        <thead><tr><th>Contract</th><th>Property</th><th>Unit</th><th>Period</th><th>Rent</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($d['leaseHistory'] ?? [] as $lc): ?>
        <tr>
          <td class="small fw-semibold"><?= esc($lc['contract_number'] ?? $lc['id']) ?></td>
          <td class="small"><?= esc($lc['property_name'] ?? '') ?></td>
          <td class="small"><?= esc($lc['unit_number'] ?? '') ?></td>
          <td class="small"><?= esc($lc['start_date'] ?? '') ?> → <?= esc($lc['end_date'] ?? '') ?></td>
          <td class="small"><?= number_format((float) ($lc['rent_amount'] ?? 0), 0) ?></td>
          <td><span class="fm-badge badge-status-<?= esc($lc['status'] ?? '') ?>"><?= ucfirst($lc['status'] ?? '') ?></span></td>
          <td><a href="<?= base_url('contracts/' . $lc['id']) ?>" class="btn btn-sm btn-link">View</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($d['leaseHistory'])): ?><tr><td colspan="7" class="text-center py-3 text-muted">No leases</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="tab-pane fade" id="tab-payments">
    <div class="fm-card p-0">
      <table class="fm-table">
        <thead><tr><th>Payment #</th><th>Type</th><th>Amount</th><th>Due</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($d['payments'] ?? [] as $p): ?>
        <tr>
          <td class="small"><?= esc($p['payment_number'] ?? $p['id']) ?></td>
          <td class="small"><?= esc($p['payment_type'] ?? '') ?></td>
          <td class="small fw-semibold"><?= number_format((float) ($p['amount'] ?? 0), 2) ?></td>
          <td class="small"><?= esc($p['due_date'] ?? '') ?></td>
          <td><span class="fm-badge badge-status-<?= esc($p['status'] ?? '') ?>"><?= ucfirst($p['status'] ?? '') ?></span></td>
          <td><a href="<?= base_url('payments/' . $p['id']) ?>" class="btn btn-sm btn-link">View</a></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($d['payments'])): ?><tr><td colspan="6" class="text-center py-3 text-muted">No payments</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="tab-pane fade" id="tab-cheques">
    <div class="fm-card p-0">
      <table class="fm-table">
        <thead><tr><th>Cheque #</th><th>Bank</th><th>Amount</th><th>Date</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($d['cheques'] ?? [] as $ch): ?>
        <tr>
          <td class="small"><?= esc($ch['cheque_no'] ?? '') ?></td>
          <td class="small"><?= esc($ch['bank_name'] ?? '') ?></td>
          <td class="small"><?= number_format((float) ($ch['amount'] ?? 0), 2) ?></td>
          <td class="small"><?= esc($ch['cheque_date'] ?? '') ?></td>
          <td><?= ucfirst($ch['status'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($d['cheques'])): ?><tr><td colspan="5" class="text-center py-3 text-muted">No cheques</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="tab-pane fade" id="tab-service">
    <div class="row g-3">
      <div class="col-md-6">
        <div class="fm-card p-3">
          <h6 class="mb-2">Complaints / requests</h6>
          <?php if (empty($d['complaints'])): ?><p class="small text-muted">None logged.</p><?php else: ?>
          <ul class="list-unstyled small mb-0">
            <?php foreach ($d['complaints'] as $c): ?>
            <li class="border-bottom py-1"><?= esc($c['ticket_number'] ?? '') ?> — <?= esc($c['category'] ?? '') ?> <span class="text-muted">(<?= esc($c['status'] ?? '') ?>)</span></li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-md-6">
        <div class="fm-card p-3">
          <h6 class="mb-2">Work orders (unit-linked)</h6>
          <?php if (empty($d['workOrders'])): ?><p class="small text-muted">None.</p><?php else: ?>
          <ul class="list-unstyled small mb-0">
            <?php foreach ($d['workOrders'] as $w): ?>
            <li class="border-bottom py-1">
              <a href="<?= base_url('workorders/view/' . $w['id']) ?>"><?= esc($w['wo_number'] ?? '') ?></a>
              — <?= esc(substr($w['title'] ?? '', 0, 40)) ?>
            </li>
            <?php endforeach; ?>
          </ul>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
  <div class="tab-pane fade" id="tab-documents">
    <?= $this->include('documents/_tab', [
      'module' => 'tenants',
      'refId' => (int) $t['id'],
      'embed' => true,
      'documents' => $d['documents'] ?? [],
      'docTypes' => fm_document_types(),
      'tenantId' => (int) $t['id'],
    ]) ?>
  </div>
</div>

<?= view('documents/_embed_modals', [
  'module'   => 'tenants',
  'refId'    => (int) $t['id'],
  'docTypes' => fm_document_types(),
  'tenantId' => (int) $t['id'],
]) ?>
<?= $this->endSection() ?>
