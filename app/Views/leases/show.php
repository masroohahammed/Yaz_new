<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$statusColors = ['draft'=>'secondary','active'=>'success','expired'=>'warning','terminated'=>'danger','renewed'=>'info'];
$statusColor  = $statusColors[$contract['status']] ?? 'secondary';
$isParkingLease = strtolower((string)($contract['unit_type'] ?? '')) === 'parking' || ($contract['contract_kind'] ?? '') === 'parking';
?>
<div class="page-header">
  <div>
    <h1>Contract <?= esc($contract['contract_number']) ?></h1>
    <div class="small text-muted"><?= esc($contract['tenant_name'] ?? '') ?> &middot; <?= esc($contract['facility_name'] ?? '') ?> &middot; Unit <?= esc($contract['unit_number'] ?? '—') ?></div>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <?php if ($isParkingLease): ?>
    <a href="<?= base_url('contracts/'.$contract['id'].'/parking-print') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-file-earmark-pdf me-1"></i>Parking Agreement</a>
    <?php else: ?>
    <a href="<?= base_url('contracts/'.$contract['id'].'/print') ?>" class="btn btn-fm-outline btn-sm" target="_blank"><i class="bi bi-printer me-1"></i>Print</a>
    <?php endif; ?>
    <a href="<?= base_url('contracts/'.$contract['id'].'/edit') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
    <?php if (in_array($contract['status'], ['active','draft'])): ?>
    <?php if ($isParkingLease): ?>
    <a href="<?= base_url('contracts/'.$contract['id'].'/parking-print') ?>" class="btn btn-sm btn-success"><i class="bi bi-arrow-repeat me-1"></i>Renew</a>
    <?php else: ?>
    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#renewModal"><i class="bi bi-arrow-repeat me-1"></i>Renew</button>
    <?php endif; ?>
    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#amendModal"><i class="bi bi-pencil-square me-1"></i>Amend</button>
    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#terminateModal"><i class="bi bi-x-circle me-1"></i>Terminate</button>
    <?php endif; ?>
    <?php if ($contract['status'] === 'active'): ?>
    <form method="post" action="<?= base_url('contracts/'.$contract['id'].'/penalties') ?>" class="d-inline fm-submit-form" onsubmit="return confirm('Apply late penalties to overdue invoices using this contract\'s configured rate?');"><?= csrf_field() ?>
      <button class="btn btn-sm btn-warning"><i class="bi bi-exclamation-triangle me-1"></i>Apply Penalties</button>
    </form>
    <form method="post" action="<?= base_url('contracts/'.$contract['id'].'/generate-invoices') ?>" class="d-inline fm-submit-form"><?= csrf_field() ?>
      <button class="btn btn-sm btn-primary" data-confirm="Generate invoices for the full contract period? Existing periods will be skipped."><i class="bi bi-receipt me-1"></i>Generate Invoices</button>
    </form>
    <?php endif; ?>
    <?php if (($signatureReady ?? true) && empty(trim($contract['tenant_signature_path'] ?? ''))): ?>
    <form method="post" action="<?= base_url('contracts/'.$contract['id'].'/generate-sign-link') ?>" class="d-inline"><?= csrf_field() ?>
      <button type="submit" class="btn btn-sm btn-fm-primary"><i class="bi bi-pen me-1"></i>Send for signature</button>
    </form>
    <?php elseif (! empty(trim($contract['tenant_signature_path'] ?? ''))): ?>
    <span class="badge bg-success align-self-center"><i class="bi bi-check-circle me-1"></i>Signed</span>
    <?php endif; ?>
  </div>
</div>

<?php if ($signSql = session()->getFlashdata('sign_sql')): ?>
<div class="alert alert-warning">
  <div class="small fw-semibold mb-1"><?= esc(session()->getFlashdata('error') ?? 'Run this SQL in phpMyAdmin:') ?></div>
  <pre class="small mb-0" style="white-space:pre-wrap"><?= esc($signSql) ?></pre>
</div>
<?php endif; ?>

<?= view('partials/_lease_signature_panel', [
    'lease' => $contract,
    'signLink' => session()->getFlashdata('sign_link'),
    'signatureReady' => $signatureReady ?? null,
]) ?>

<div class="row g-3 mb-3">
  <div class="col-lg-6">
    <div class="form-card h-100">
      <h6 class="text-muted text-uppercase small mb-3">Contract Terms</h6>
      <p class="mb-1"><strong>Rent:</strong> <?= number_format((float)$contract['rent_amount'],2) ?> <?= esc($currency) ?> / <?= esc($contract['payment_frequency']) ?></p>
      <p class="mb-1"><strong>Period:</strong> <?= esc($contract['start_date']) ?> – <?= esc($contract['end_date']) ?></p>
      <p class="mb-1"><strong>Status:</strong> <span class="badge bg-<?= $statusColor ?>"><?= esc($contract['status']) ?></span></p>
      <p class="mb-1"><strong>Payment method:</strong> <?= esc($contract['payment_type'] ?? '—') ?></p>
      <?php if (!empty($contract['security_deposit'])): ?>
      <p class="mb-1"><strong>Security deposit:</strong> <?= number_format((float)$contract['security_deposit'],2) ?> <?= esc($currency) ?></p>
      <?php endif; ?>
      <?php if ($isParkingLease && !empty($contract['plate_number'])): ?>
      <p class="mb-1"><strong>Plate:</strong> <?= esc($contract['plate_number']) ?></p>
      <?php endif; ?>
      <?php if (!empty($contract['vat_applicable'])): ?>
      <p class="mb-0"><strong>VAT:</strong> <?= esc($contract['vat_rate'] ?? '') ?>%</p>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="form-card h-100">
      <h6 class="text-muted text-uppercase small mb-3">Tenant</h6>
      <p class="mb-1"><strong><?= esc($contract['tenant_name'] ?? '—') ?></strong></p>
      <?php
      $displayQid = trim((string)($contract['tenant_qid'] ?? $contract['qid_no'] ?? $contract['passport_no'] ?? ''));
      if ($displayQid !== ''): ?>
      <p class="mb-1 text-muted small">QID / Passport: <?= esc($displayQid) ?></p>
      <?php endif; ?>
      <?php if (!empty($contract['tenant_phone'])): ?><p class="mb-0 text-muted"><?= esc($contract['tenant_phone']) ?></p><?php endif; ?>
    </div>
  </div>
</div>

<?php if (!empty($offers)): ?>
<div class="form-card mb-3">
  <h6 class="text-muted text-uppercase small mb-2">Complimentary Offers</h6>
  <table class="table table-sm table-registry mb-0">
    <thead><tr><th>Type</th><th>Period</th><th>Discount</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($offers as $o): ?>
      <tr><td><?= esc($o['offer_type']) ?></td><td><?= esc($o['start_date']??'—') ?> – <?= esc($o['end_date']??'—') ?></td><td><?= esc($o['discount_percent']??'—') ?>%</td><td><?= esc($o['status']) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<?php if (!empty($amendments)): ?>
<div class="form-card mb-3">
  <h6 class="text-muted text-uppercase small mb-2">Amendments</h6>
  <table class="table table-sm table-registry mb-0">
    <thead><tr><th>Effective</th><th>New Rent</th><th>New End Date</th><th>Description</th></tr></thead>
    <tbody>
    <?php foreach ($amendments as $a): ?>
      <tr><td><?= esc($a['effective_date']) ?></td><td><?= $a['new_rent'] ? number_format((float)$a['new_rent'],2).' '.esc($currency) : '—' ?></td><td><?= esc($a['new_end_date']??'—') ?></td><td><?= esc($a['description']) ?></td></tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<div class="form-card mb-3">
  <h6 class="text-muted text-uppercase small mb-2">Payments</h6>
  <table class="table table-registry table-sm mb-0">
    <thead><tr><th>#</th><th>Due</th><th>Period</th><th>Amount</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($payments as $p): ?>
      <tr>
        <td><?= esc($p['payment_number']) ?></td>
        <td><?= esc($p['due_date']??'—') ?></td>
        <td class="small text-muted"><?= esc($p['period_from']??'') ?><?= !empty($p['period_from'])?' – ':'' ?><?= esc($p['period_to']??'') ?></td>
        <td><?= number_format((float)$p['amount'],2) ?></td>
        <td><span class="badge bg-secondary"><?= esc($p['status']) ?></span></td>
        <td><a href="<?= base_url('payments/'.$p['id'].'/edit') ?>" class="btn btn-sm btn-fm-outline">Edit</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($payments)): ?><tr><td colspan="6" class="text-muted text-center py-3">No payments yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<?php if (!empty($documents)): ?>
<div class="form-card mb-3">
  <h6 class="text-muted text-uppercase small mb-2">Documents</h6>
  <ul class="list-unstyled mb-0">
  <?php foreach ($documents as $d): ?>
    <li><i class="bi bi-paperclip me-1"></i><?= esc($d['original_name'] ?? $d['file_name'] ?? 'Document') ?></li>
  <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<div class="form-card mb-3">
  <h6 class="text-muted text-uppercase small mb-2">Save &amp; Print content</h6>
  <?= form_open(base_url('contracts/'.$contract['id'].'/save-print')) ?>
    <?= csrf_field() ?>
    <textarea name="custom_content_en" class="form-control form-control-sm mb-2" rows="3" placeholder="English print content"><?= esc($contract['custom_content_en'] ?? '') ?></textarea>
    <textarea name="custom_content_ar" class="form-control form-control-sm mb-2" rows="3" dir="rtl" placeholder="Arabic print content"><?= esc($contract['custom_content_ar'] ?? '') ?></textarea>
    <button type="submit" class="btn btn-sm btn-fm-primary">Save &amp; Print</button>
  <?= form_close() ?>
</div>

<!-- Renew Modal -->
<div class="modal fade" id="renewModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title">Renew Contract</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <form method="post" action="<?= base_url('contracts/'.$contract['id'].'/renew') ?>"><?= csrf_field() ?>
  <div class="modal-body">
    <div class="mb-3"><label class="form-label">New Start Date <span class="text-danger">*</span></label><input type="date" name="new_start_date" class="form-control" required value="<?= esc(date('Y-m-d', strtotime($contract['end_date'].' +1 day'))) ?>"></div>
    <div class="mb-3"><label class="form-label">New End Date <span class="text-danger">*</span></label><input type="date" name="new_end_date" class="form-control" required value="<?= esc(date('Y-m-d', strtotime($contract['end_date'].' +1 year'))) ?>"></div>
    <div class="mb-3"><label class="form-label">New Rent (leave blank to keep current)</label><input type="number" step="0.01" name="new_rent" class="form-control" placeholder="<?= esc($contract['rent_amount']) ?>"></div>
    <div class="mb-3"><label class="form-label">Payment Frequency</label>
      <select name="payment_frequency" class="form-select">
        <?php foreach (['monthly','quarterly','yearly'] as $f): ?>
          <option value="<?= $f ?>" <?= ($contract['payment_frequency']??'monthly')===$f?'selected':'' ?>><?= ucfirst($f) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-fm-outline" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success">Renew</button></div>
  </form>
</div></div></div>

<!-- Terminate Modal -->
<div class="modal fade" id="terminateModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title text-danger">Terminate Contract</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <form method="post" action="<?= base_url('contracts/'.$contract['id'].'/terminate') ?>"><?= csrf_field() ?>
  <div class="modal-body">
    <div class="mb-3"><label class="form-label">Termination Reason <span class="text-danger">*</span></label><textarea name="termination_reason" class="form-control" rows="3" required></textarea></div>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-fm-outline" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Terminate</button></div>
  </form>
</div></div></div>

<!-- Amendment Modal -->
<div class="modal fade" id="amendModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title">Record Amendment</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <form method="post" action="<?= base_url('contracts/'.$contract['id'].'/amendment') ?>"><?= csrf_field() ?>
  <div class="modal-body">
    <div class="mb-3"><label class="form-label">Effective Date <span class="text-danger">*</span></label><input type="date" name="effective_date" class="form-control" required value="<?= date('Y-m-d') ?>"></div>
    <div class="mb-3"><label class="form-label">New Rent (optional)</label><input type="number" step="0.01" name="new_rent" class="form-control" placeholder="Leave blank to keep current"></div>
    <div class="mb-3"><label class="form-label">New End Date (optional)</label><input type="date" name="new_end_date" class="form-control"></div>
    <div class="mb-3"><label class="form-label">Description <span class="text-danger">*</span></label><textarea name="description" class="form-control" rows="3" required></textarea></div>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-fm-outline" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-warning">Save Amendment</button></div>
  </form>
</div></div></div>

<?= $this->endSection() ?>
