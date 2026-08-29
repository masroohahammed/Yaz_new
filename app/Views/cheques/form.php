<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $ro = !empty($readOnly); ?>
<div class="page-header">
  <div><h1><?= esc($title ?? 'Cheque') ?></h1></div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('cheques') ?>" class="btn btn-fm-outline btn-sm">Back</a>
    <?php if ($ro && !empty($cheque)): ?>
      <?php if ($cheque['status'] === 'pending'): ?>
        <form method="post" action="<?= base_url('cheques/'.$cheque['id'].'/deposit') ?>" class="d-inline"><?= csrf_field() ?>
          <button class="btn btn-sm btn-info" data-confirm="Mark as deposited?">Deposit</button>
        </form>
        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#bounceModal">Bounce</button>
      <?php elseif ($cheque['status'] === 'deposited'): ?>
        <form method="post" action="<?= base_url('cheques/'.$cheque['id'].'/clear') ?>" class="d-inline"><?= csrf_field() ?>
          <button class="btn btn-sm btn-success" data-confirm="Mark as cleared?">Clear</button>
        </form>
        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#bounceModal">Bounce</button>
        <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#cashModal">→ Cash</button>
      <?php elseif ($cheque['status'] === 'bounced'): ?>
        <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#cashModal">→ Cash</button>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<div class="form-card">
<?php if ($ro): ?>
  <div class="row g-3">
    <div class="col-md-4"><strong>Cheque No:</strong> <?= esc($cheque['cheque_no']) ?></div>
    <div class="col-md-4"><strong>Amount:</strong> <?= number_format((float)$cheque['amount'],2) ?> <?= esc($currency) ?></div>
    <div class="col-md-4"><strong>Status:</strong> <span class="badge bg-secondary"><?= esc($cheque['status']) ?></span></div>
    <div class="col-md-4"><strong>Bank:</strong> <?= esc($cheque['bank_name']??'—') ?></div>
    <div class="col-md-4"><strong>Date:</strong> <?= esc($cheque['cheque_date']??'—') ?></div>
    <div class="col-md-4"><strong>Received:</strong> <?= esc($cheque['received_date']??'—') ?></div>
    <div class="col-md-4"><strong>Deposited:</strong> <?= esc($cheque['deposit_date']??'—') ?></div>
    <div class="col-md-4"><strong>Cleared:</strong> <?= esc($cheque['clearance_date']??'—') ?></div>
    <div class="col-md-4"><strong>Tenant:</strong> <?= esc($cheque['tenant_name']??'—') ?></div>
    <div class="col-md-4"><strong>Contract:</strong> <?= esc($cheque['contract_number']??'—') ?></div>
    <div class="col-md-4"><strong>Property:</strong> <?= esc($cheque['facility_name']??'—') ?></div>
    <?php if (!empty($cheque['bounce_reason'])): ?>
    <div class="col-12 mt-2"><div class="alert alert-danger mb-0"><strong>Bounce reason:</strong> <?= esc($cheque['bounce_reason']) ?></div></div>
    <?php endif; ?>
    <?php if (!empty($cheque['case_no'])): ?>
    <div class="col-12"><div class="alert alert-warning mb-0"><strong>Legal case:</strong> <?= esc($cheque['case_no']) ?> — Filed: <?= esc($cheque['filed_date']??'—') ?><br><?= esc($cheque['case_notes']??'') ?></div></div>
    <?php endif; ?>
    <?php if (!empty($cheque['notes'])): ?>
    <div class="col-12"><p class="text-muted"><?= nl2br(esc($cheque['notes'])) ?></p></div>
    <?php endif; ?>
  </div>

<?php else: ?>
<form method="post" action="<?= isset($cheque['id']) ? base_url('cheques/'.$cheque['id'].'/update') : base_url('cheques/store') ?>"><?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-md-4"><label class="form-label">Contract</label><select name="contract_id" class="form-select"><option value="">—</option><?php foreach ($contracts??[] as $c): ?><option value="<?= $c['id'] ?>"><?= esc($c['contract_number']) ?> — <?= esc($c['tenant_name']??'') ?></option><?php endforeach; ?></select></div>
    <div class="col-md-4"><label class="form-label">Cheque no <span class="text-danger">*</span></label><input name="cheque_no" class="form-control" required value="<?= esc($cheque['cheque_no']??'') ?>"></div>
    <div class="col-md-4"><label class="form-label">Amount <span class="text-danger">*</span></label><input type="number" step="0.01" name="amount" class="form-control" required value="<?= esc($cheque['amount']??'') ?>"></div>
    <div class="col-md-4"><label class="form-label">Bank</label><input name="bank_name" class="form-control" value="<?= esc($cheque['bank_name']??'') ?>"></div>
    <div class="col-md-4"><label class="form-label">Cheque date</label><input type="date" name="cheque_date" class="form-control" value="<?= esc($cheque['cheque_date']??'') ?>"></div>
    <div class="col-md-4"><label class="form-label">Status</label><select name="status" class="form-select"><?php foreach (['pending','deposited','cleared','bounced','cancelled','replaced'] as $s): ?><option value="<?= $s ?>" <?= ($cheque['status']??'pending')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-4"><label class="form-label">Received date</label><input type="date" name="received_date" class="form-control" value="<?= esc($cheque['received_date']??'') ?>"></div>
    <div class="col-md-4"><label class="form-label">Account name</label><input name="account_name" class="form-control" value="<?= esc($cheque['account_name']??'') ?>"></div>
    <div class="col-md-4"><label class="form-label">Account no</label><input name="account_no" class="form-control" value="<?= esc($cheque['account_no']??'') ?>"></div>
    <div class="col-md-4"><label class="form-label">Period from</label><input type="date" name="period_from" class="form-control" value="<?= esc($cheque['period_from']??'') ?>"></div>
    <div class="col-md-4"><label class="form-label">Period to</label><input type="date" name="period_to" class="form-control" value="<?= esc($cheque['period_to']??'') ?>"></div>
    <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"><?= esc($cheque['notes']??'') ?></textarea></div>
  </div>
  <div class="mt-3"><button class="btn btn-fm-primary">Save</button></div>
</form>
<?php endif; ?>
</div>

<?php if ($ro && !empty($cheque) && in_array($cheque['status'], ['pending','deposited','bounced'])): ?>
<!-- Bounce Modal -->
<div class="modal fade" id="bounceModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <div class="modal-header"><h6 class="modal-title text-danger">Mark Bounced</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <form method="post" action="<?= base_url('cheques/'.$cheque['id'].'/bounce') ?>"><?= csrf_field() ?>
  <div class="modal-body row g-2">
    <div class="col-12"><label class="form-label">Bounce Reason <span class="text-danger">*</span></label><textarea name="bounce_reason" class="form-control" rows="2" required></textarea></div>
    <div class="col-12"><div class="form-check"><input type="checkbox" name="file_legal" value="1" class="form-check-input" id="flMain"><label class="form-check-label" for="flMain">File Legal Case</label></div></div>
    <div class="col-md-6"><label class="form-label">Case No</label><input type="text" name="case_no" class="form-control"></div>
    <div class="col-md-6"><label class="form-label">Filed Date</label><input type="date" name="filed_date" class="form-control"></div>
    <div class="col-12"><label class="form-label">Case Notes</label><textarea name="case_notes" class="form-control" rows="2"></textarea></div>
  </div>
  <div class="modal-footer"><button type="submit" class="btn btn-danger btn-sm">Mark Bounced</button></div>
  </form>
</div></div></div>

<!-- Cash Conversion Modal -->
<div class="modal fade" id="cashModal" tabindex="-1"><div class="modal-dialog modal-sm"><div class="modal-content">
  <div class="modal-header"><h6 class="modal-title">Convert to Cash</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <form method="post" action="<?= base_url('cheques/'.$cheque['id'].'/convert-to-cash') ?>"><?= csrf_field() ?>
  <div class="modal-body">
    <label class="form-label">Conversion Date <span class="text-danger">*</span></label>
    <input type="date" name="cash_conversion_date" class="form-control mb-2" required value="<?= date('Y-m-d') ?>">
    <label class="form-label">Conversion Amount</label>
    <input type="number" step="0.01" name="conversion_amount" class="form-control mb-2" placeholder="<?= esc($cheque['amount']) ?>">
    <label class="form-label">Notes</label>
    <input type="text" name="notes" class="form-control">
  </div>
  <div class="modal-footer"><button type="submit" class="btn btn-secondary btn-sm">Convert</button></div>
  </form>
</div></div></div>
<?php endif; ?>

<?= $this->endSection() ?>
