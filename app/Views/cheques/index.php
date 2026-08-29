<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-bank me-2 text-primary"></i>Incoming Cheques</h1></div>
<div class="d-flex gap-2">
<?php if (empty($migrationRequired)): ?>
<a href="<?= base_url('cheques/import') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-upload me-1"></i>Import</a>
<a href="<?= base_url('cheques/export-csv') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-download me-1"></i>CSV</a>
<a href="<?= base_url('cheques/create') ?>" class="btn btn-fm-primary btn-sm">Register Cheque</a>
<?php endif; ?>
</div></div>
<?php if (!empty($migrationRequired)): ?><div class="alert alert-warning">Run migration to create <code>cheques</code>.</div><?php else: ?>
<form class="filters-inline form-card mb-3" method="get">
  <input type="text" name="search" class="form-control form-control-sm" value="<?= esc($filters['search']??'') ?>" placeholder="Cheque no, tenant…">
  <select name="status" class="form-select form-select-sm"><option value="">All</option><?php foreach (['pending','deposited','cleared','bounced','cancelled','replaced'] as $s): ?><option value="<?= $s ?>" <?= ($filters['status']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select>
  <button class="btn btn-fm-outline btn-sm" type="submit">Filter</button>
</form>
<div class="form-card p-0"><div class="table-responsive"><table class="table table-registry table-sm mb-0">
<thead><tr><th>Cheque</th><th>Tenant</th><th>Contract</th><th>Date</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php
$statusColors = ['pending'=>'warning','deposited'=>'info','cleared'=>'success','bounced'=>'danger','cancelled'=>'secondary','replaced'=>'secondary'];
foreach ($cheques as $c):
  $statusColor = $statusColors[$c['status']] ?? 'secondary';
?>
<tr>
<td><?= esc($c['cheque_no']) ?></td>
<td><?= esc($c['tenant_name']??'—') ?></td>
<td><?= esc($c['contract_number']??'—') ?></td>
<td><?= esc($c['cheque_date']??'—') ?></td>
<td><?= number_format((float)$c['amount'],2) ?></td>
<td><span class="badge bg-<?= $statusColor ?>"><?= esc($c['status']) ?></span></td>
<td>
  <div class="d-flex gap-1 flex-wrap">
  <a href="<?= base_url('cheques/'.$c['id']) ?>" class="btn btn-sm btn-fm-outline">View</a>
  <?php if ($c['status'] === 'pending'): ?>
    <form method="post" action="<?= base_url('cheques/'.$c['id'].'/deposit') ?>" class="d-inline"><?= csrf_field() ?>
      <button class="btn btn-sm btn-info" data-confirm="Mark as deposited?">Deposit</button>
    </form>
    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#bounceModal<?= $c['id'] ?>">Bounce</button>
  <?php elseif ($c['status'] === 'deposited'): ?>
    <form method="post" action="<?= base_url('cheques/'.$c['id'].'/clear') ?>" class="d-inline"><?= csrf_field() ?>
      <button class="btn btn-sm btn-success" data-confirm="Mark as cleared?">Clear</button>
    </form>
    <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#bounceModal<?= $c['id'] ?>">Bounce</button>
    <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#cashModal<?= $c['id'] ?>">→ Cash</button>
  <?php elseif ($c['status'] === 'bounced'): ?>
    <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#cashModal<?= $c['id'] ?>">→ Cash</button>
  <?php endif; ?>
  </div>
</td>
</tr>

<?php if (in_array($c['status'], ['pending','deposited','bounced'])): ?>
<!-- Bounce Modal -->
<tr class="d-none"><td colspan="7">
<div class="modal fade" id="bounceModal<?= $c['id'] ?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <div class="modal-header"><h6 class="modal-title text-danger">Mark Bounced — #<?= esc($c['cheque_no']) ?></h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <form method="post" action="<?= base_url('cheques/'.$c['id'].'/bounce') ?>"><?= csrf_field() ?>
  <div class="modal-body row g-2">
    <div class="col-12"><label class="form-label">Bounce Reason <span class="text-danger">*</span></label><textarea name="bounce_reason" class="form-control" rows="2" required></textarea></div>
    <div class="col-12"><div class="form-check"><input type="checkbox" name="file_legal" value="1" class="form-check-input" id="fl<?= $c['id'] ?>"><label class="form-check-label" for="fl<?= $c['id'] ?>">File Legal Case</label></div></div>
    <div class="col-md-6"><label class="form-label">Case No</label><input type="text" name="case_no" class="form-control"></div>
    <div class="col-md-6"><label class="form-label">Filed Date</label><input type="date" name="filed_date" class="form-control"></div>
    <div class="col-12"><label class="form-label">Case Notes</label><textarea name="case_notes" class="form-control" rows="2"></textarea></div>
  </div>
  <div class="modal-footer"><button type="submit" class="btn btn-danger btn-sm">Mark Bounced</button></div>
  </form>
</div></div></div>
</td></tr>

<!-- Cash Conversion Modal -->
<tr class="d-none"><td colspan="7">
<div class="modal fade" id="cashModal<?= $c['id'] ?>" tabindex="-1"><div class="modal-dialog modal-sm"><div class="modal-content">
  <div class="modal-header"><h6 class="modal-title">Convert to Cash — #<?= esc($c['cheque_no']) ?></h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <form method="post" action="<?= base_url('cheques/'.$c['id'].'/convert-to-cash') ?>"><?= csrf_field() ?>
  <div class="modal-body">
    <label class="form-label">Conversion Date <span class="text-danger">*</span></label>
    <input type="date" name="cash_conversion_date" class="form-control mb-2" required value="<?= date('Y-m-d') ?>">
    <label class="form-label">Amount</label>
    <input type="number" step="0.01" name="conversion_amount" class="form-control mb-2" placeholder="<?= esc($c['amount']) ?>">
    <label class="form-label">Notes</label>
    <input type="text" name="notes" class="form-control">
  </div>
  <div class="modal-footer"><button type="submit" class="btn btn-secondary btn-sm">Convert</button></div>
  </form>
</div></div></div>
</td></tr>
<?php endif; ?>

<?php endforeach; ?>
<?php if (empty($cheques)): ?><tr><td colspan="7" class="text-center text-muted py-4">No cheques.</td></tr><?php endif; ?></tbody>
</table></div></div>
<?php endif; ?>
<?= $this->endSection() ?>
