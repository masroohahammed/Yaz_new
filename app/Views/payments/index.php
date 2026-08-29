<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-credit-card me-2 text-primary"></i>Lease Payments</h1></div>
<div class="d-flex gap-2">
<?php if (empty($migrationRequired)): ?>
<a href="<?= base_url('payments/export-csv') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-download me-1"></i>CSV</a>
<a href="<?= base_url('payments/create') ?>" class="btn btn-fm-primary btn-sm">Record Payment</a>
<?php endif; ?>
</div></div>
<?php if (!empty($migrationRequired)): ?><div class="alert alert-warning">Run migration to create <code>lease_payments</code>.</div><?php else: ?>
<form class="filters-inline form-card mb-3" method="get">
  <input type="text" name="search" class="form-control form-control-sm" placeholder="Search…" value="<?= esc($filters['search']??'') ?>">
  <select name="status" class="form-select form-select-sm"><option value="">All statuses</option><?php foreach (['pending','paid','partial','overdue','cancelled','postponed'] as $s): ?><option value="<?= $s ?>" <?= ($filters['status']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select>
  <input type="date" name="from" class="form-control form-control-sm" value="<?= esc($filters['from']??'') ?>">
  <input type="date" name="to" class="form-control form-control-sm" value="<?= esc($filters['to']??'') ?>">
  <button class="btn btn-fm-outline btn-sm" type="submit">Filter</button>
</form>
<div class="form-card p-0"><div class="table-responsive"><table class="table table-registry table-sm mb-0">
<thead><tr><th>Payment</th><th>Contract</th><th>Tenant</th><th>Due</th><th>Amount</th><th>Method</th><th>Status</th><th></th></tr></thead>
<tbody><?php foreach ($payments as $p): ?>
<?php
  $statusBadge = ['pending'=>'warning','paid'=>'success','partial'=>'info','overdue'=>'danger','cancelled'=>'secondary','postponed'=>'secondary'];
  $badgeColor  = $statusBadge[$p['status']] ?? 'secondary';
  $actionable  = in_array($p['status'], ['pending','partial','postponed','overdue']);
?>
<tr>
<td><?= esc($p['payment_number']) ?></td>
<td><?php if (!empty($p['contract_id'])): ?><a href="<?= base_url('contracts/'.$p['contract_id']) ?>"><?= esc($p['contract_number']??'—') ?></a><?php else: ?>—<?php endif; ?></td>
<td><?= esc($p['tenant_name']??'—') ?></td>
<td><?= esc($p['due_date']??'—') ?></td>
<td><?= number_format((float)$p['amount'],2) ?></td>
<td><?= esc($p['payment_method']) ?></td>
<td><span class="badge bg-<?= $badgeColor ?>"><?= esc($p['status']) ?></span></td>
<td class="text-end">
  <div class="d-flex gap-1 justify-content-end flex-wrap">
  <?php if ($actionable): ?>
    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#collectModal<?= $p['id'] ?>">Collect</button>
    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#partialModal<?= $p['id'] ?>">Partial</button>
    <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#postponeModal<?= $p['id'] ?>">Postpone</button>
  <?php endif; ?>
  <?php if ($p['status'] === 'paid'): ?>
    <button class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#refundModal<?= $p['id'] ?>">Refund</button>
  <?php endif; ?>
  <a href="<?= base_url('payments/'.$p['id'].'/edit') ?>" class="btn btn-sm btn-fm-outline">Edit</a>
  </div>
</td>
</tr>

<?php if ($actionable): ?>
<!-- Collect Modal -->
<tr class="d-none"><td colspan="8">
<div class="modal fade" id="collectModal<?= $p['id'] ?>" tabindex="-1"><div class="modal-dialog modal-sm"><div class="modal-content">
  <div class="modal-header"><h6 class="modal-title">Collect Payment</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <form method="post" action="<?= base_url('payments/'.$p['id'].'/collect') ?>"><?= csrf_field() ?>
  <div class="modal-body">
    <label class="form-label">Amount (leave blank for full)</label>
    <input type="number" step="0.01" name="amount" class="form-control mb-2" placeholder="<?= esc($p['amount']) ?>">
    <label class="form-label">Payment Date</label>
    <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>">
  </div>
  <div class="modal-footer"><button type="submit" class="btn btn-success btn-sm">Mark Paid</button></div>
  </form>
</div></div></div>
</td></tr>

<!-- Partial Modal -->
<tr class="d-none"><td colspan="8">
<div class="modal fade" id="partialModal<?= $p['id'] ?>" tabindex="-1"><div class="modal-dialog modal-sm"><div class="modal-content">
  <div class="modal-header"><h6 class="modal-title">Record Partial</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <form method="post" action="<?= base_url('payments/'.$p['id'].'/partial') ?>"><?= csrf_field() ?>
  <div class="modal-body">
    <label class="form-label">Partial Amount <span class="text-danger">*</span></label>
    <input type="number" step="0.01" name="partial_amount" class="form-control mb-2" required>
    <label class="form-label">Date</label>
    <input type="date" name="paid_date" class="form-control mb-2" value="<?= date('Y-m-d') ?>">
    <label class="form-label">Note</label>
    <input type="text" name="notes" class="form-control">
  </div>
  <div class="modal-footer"><button type="submit" class="btn btn-info btn-sm">Record</button></div>
  </form>
</div></div></div>
</td></tr>

<!-- Postpone Modal -->
<tr class="d-none"><td colspan="8">
<div class="modal fade" id="postponeModal<?= $p['id'] ?>" tabindex="-1"><div class="modal-dialog modal-sm"><div class="modal-content">
  <div class="modal-header"><h6 class="modal-title">Postpone Payment</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <form method="post" action="<?= base_url('payments/'.$p['id'].'/postpone') ?>"><?= csrf_field() ?>
  <div class="modal-body">
    <label class="form-label">New Due Date <span class="text-danger">*</span></label>
    <input type="date" name="postponed_to" class="form-control mb-2" required>
    <label class="form-label">Reason <span class="text-danger">*</span></label>
    <textarea name="postpone_note" class="form-control" rows="2" required></textarea>
  </div>
  <div class="modal-footer"><button type="submit" class="btn btn-warning btn-sm">Postpone</button></div>
  </form>
</div></div></div>
</td></tr>
<?php endif; ?>

<?php if ($p['status'] === 'paid'): ?>
<!-- Refund Modal -->
<tr class="d-none"><td colspan="8">
<div class="modal fade" id="refundModal<?= $p['id'] ?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <div class="modal-header"><h6 class="modal-title">Record Refund</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <form method="post" action="<?= base_url('payments/'.$p['id'].'/refund') ?>"><?= csrf_field() ?>
  <div class="modal-body row g-2">
    <div class="col-12"><label class="form-label">Refund Type <span class="text-danger">*</span></label>
      <select name="refund_type" class="form-select" required><option value="">—</option><?php foreach (['full','partial','deposit','overpayment'] as $rt): ?><option value="<?= $rt ?>"><?= ucfirst($rt) ?></option><?php endforeach; ?></select>
    </div>
    <div class="col-md-6"><label class="form-label">Amount</label><input type="number" step="0.01" name="refund_amount" class="form-control"></div>
    <div class="col-md-6"><label class="form-label">Refund Date</label><input type="date" name="refund_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
    <div class="col-12"><label class="form-label">Reference</label><input type="text" name="reference" class="form-control"></div>
    <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
  </div>
  <div class="modal-footer"><button type="submit" class="btn btn-secondary btn-sm">Record Refund</button></div>
  </form>
</div></div></div>
</td></tr>
<?php endif; ?>

<?php endforeach; ?>
<?php if (empty($payments)): ?><tr><td colspan="8" class="text-center text-muted py-4">No payments.</td></tr><?php endif; ?></tbody>
</table></div></div>
<?php endif; ?>
<?= $this->endSection() ?>
