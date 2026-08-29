<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-person-badge me-2 text-primary"></i><?= esc($landlord['full_name']) ?></h1>
    <?php if (!empty($landlord['full_name_ar'])): ?><div class="small text-muted text-end" dir="rtl"><?= esc($landlord['full_name_ar']) ?></div><?php endif; ?>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('reports/pm/landlord?landlord='.(int)$landlord['id']) ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-bar-chart-line me-1"></i>Reports</a>
    <a href="<?= base_url('landlords/'.$landlord['id'].'/revenue') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-graph-up me-1"></i>Revenue</a>
    <button class="btn btn-fm-outline btn-sm" data-bs-toggle="modal" data-bs-target="#payoutModal"><i class="bi bi-cash-coin me-1"></i>New Payout</button>
    <a href="<?= base_url('landlords/'.$landlord['id'].'/edit') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-6">
    <div class="form-card h-100">
      <h6 class="text-muted text-uppercase small mb-3">Personal Details</h6>
      <p class="mb-1"><strong>Phone:</strong> <?= esc($landlord['phone'] ?? '—') ?></p>
      <p class="mb-1"><strong>Phone 2:</strong> <?= esc($landlord['phone2'] ?? '—') ?></p>
      <p class="mb-1"><strong>Email:</strong> <?= esc($landlord['email'] ?? '—') ?></p>
      <p class="mb-1"><strong>Nationality:</strong> <?= esc($landlord['nationality'] ?? '—') ?></p>
      <p class="mb-1"><strong>ID:</strong> <?= esc($landlord['id_type'] ?? '') ?> <?= esc($landlord['id_number'] ?? '—') ?> <?= !empty($landlord['id_expiry']) ? '(exp. '.esc($landlord['id_expiry']).')' : '' ?></p>
      <p class="mb-0"><strong>Status:</strong> <span class="badge bg-<?= $landlord['status']==='active'?'success':'secondary' ?>"><?= esc($landlord['status']) ?></span></p>
    </div>
  </div>
  <div class="col-md-6">
    <div class="form-card h-100">
      <h6 class="text-muted text-uppercase small mb-3">Banking</h6>
      <p class="mb-1"><strong>Bank:</strong> <?= esc($landlord['bank_name'] ?? '—') ?></p>
      <p class="mb-1"><strong>Account:</strong> <?= esc($landlord['bank_account'] ?? '—') ?></p>
      <p class="mb-1"><strong>IBAN:</strong> <?= esc($landlord['bank_iban'] ?? '—') ?></p>
      <p class="mb-0"><strong>Commission:</strong> <?= esc($landlord['commission_pct'] ?? '—') ?>%</p>
    </div>
  </div>
</div>

<?php if (!empty($payouts)): ?>
<div class="form-card mb-3">
  <h6 class="text-muted text-uppercase small mb-2">Payouts</h6>
  <div class="table-responsive">
  <table class="table table-sm table-registry mb-0">
    <thead><tr><th>Period</th><th>Gross</th><th>Commission</th><th>Net</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($payouts as $po): ?>
    <tr>
      <td class="small"><?= esc($po['period_from']??'—') ?> – <?= esc($po['period_to']??'—') ?></td>
      <td><?= number_format((float)($po['gross_rent']??0),2) ?></td>
      <td><?= number_format((float)($po['commission']??0),2) ?></td>
      <td><strong><?= number_format((float)($po['net_amount']??0),2) ?> <?= esc($currency) ?></strong></td>
      <td><span class="badge bg-<?= $po['status']==='paid'?'success':'warning' ?>"><?= esc($po['status']) ?></span></td>
      <td>
        <?php if ($po['status'] === 'pending'): ?>
        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#markPaidModal<?= $po['id'] ?>">Mark Paid</button>
        <!-- Mark Paid Modal -->
        <div class="modal fade" id="markPaidModal<?= $po['id'] ?>" tabindex="-1"><div class="modal-dialog modal-sm"><div class="modal-content">
          <div class="modal-header"><h6 class="modal-title">Mark Payout Paid</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
          <form method="post" action="<?= base_url('landlords/payouts/'.$po['id'].'/mark-paid') ?>"><?= csrf_field() ?>
          <div class="modal-body">
            <label class="form-label">Paid Date</label><input type="date" name="paid_date" class="form-control mb-2" value="<?= date('Y-m-d') ?>">
            <label class="form-label">Reference</label><input type="text" name="reference_no" class="form-control">
          </div>
          <div class="modal-footer"><button type="submit" class="btn btn-success btn-sm">Confirm</button></div>
          </form>
        </div></div></div>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<?php else: ?>
<div class="form-card mb-3 text-muted">No payouts recorded yet. <button class="btn btn-sm btn-fm-outline ms-2" data-bs-toggle="modal" data-bs-target="#payoutModal">Create first payout</button></div>
<?php endif; ?>

<!-- New Payout Modal -->
<div class="modal fade" id="payoutModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title">New Payout — <?= esc($landlord['full_name']) ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <form method="post" action="<?= base_url('landlords/'.$landlord['id'].'/payout') ?>"><?= csrf_field() ?>
  <div class="modal-body row g-2">
    <div class="col-md-6"><label class="form-label">Period From</label><input type="date" name="period_from" class="form-control"></div>
    <div class="col-md-6"><label class="form-label">Period To</label><input type="date" name="period_to" class="form-control"></div>
    <div class="col-md-4"><label class="form-label">Gross Rent</label><input type="number" step="0.01" name="gross_rent" class="form-control"></div>
    <div class="col-md-4"><label class="form-label">Commission</label><input type="number" step="0.01" name="commission" class="form-control" placeholder="<?= esc($landlord['commission_pct']??'') ?>%"></div>
    <div class="col-md-4"><label class="form-label">Deductions</label><input type="number" step="0.01" name="deductions" class="form-control" value="0"></div>
    <div class="col-md-6"><label class="form-label">Net Amount (auto-calc)</label><input type="number" step="0.01" name="net_amount" class="form-control" placeholder="Leave blank to auto-calc"></div>
    <div class="col-md-6"><label class="form-label">Payment Method</label><input type="text" name="payment_method" class="form-control" placeholder="bank transfer…"></div>
    <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
  </div>
  <div class="modal-footer"><button type="button" class="btn btn-fm-outline" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-fm-primary">Create Payout</button></div>
  </form>
</div></div></div>

<?= $this->endSection() ?>
