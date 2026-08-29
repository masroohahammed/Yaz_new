<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-receipt me-2 text-primary"></i><?= esc($account['utility_name']) ?> — Bills</h1>
    <div class="small text-muted">
      <?= esc($account['provider_name']??'') ?><?= $account['account_number'] ? ' · Account: '.esc($account['account_number']) : '' ?>
      · Mode: <strong><?= str_replace('_',' ',esc($account['billing_mode'])) ?></strong>
    </div>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-fm-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addBillModal"><i class="bi bi-plus me-1"></i>Add Bill</button>
    <a href="<?= base_url('utilities') ?>" class="btn btn-fm-outline btn-sm">Back</a>
  </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<?php if ($totalPending > 0): ?>
  <div class="alert alert-warning d-flex align-items-center gap-2">
    <i class="bi bi-exclamation-circle fs-5"></i>
    <span>Pending balance: <strong><?= number_format($totalPending, 2) ?> <?= esc($currency??'QAR') ?></strong></span>
  </div>
<?php endif; ?>

<div class="form-card p-0">
  <table class="table table-registry table-sm mb-0">
    <thead><tr><th>Bill #</th><th>Date</th><th>Period</th><th>Reading</th><th>Amount</th><th>Charge To</th><th>Due</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($bills as $b): ?>
      <tr>
        <td><?= esc($b['bill_no']??'—') ?></td>
        <td class="small"><?= esc($b['bill_date']??'—') ?></td>
        <td class="small"><?= esc($b['period_from']??'') ?><?= $b['period_from']&&$b['period_to']?' → ':''; ?><?= esc($b['period_to']??'') ?></td>
        <td class="small"><?= $b['reading_prev']!==null ? esc($b['reading_prev']).' → '.esc($b['reading_curr']) : '—' ?></td>
        <td class="fw-semibold"><?= number_format((float)$b['amount'],2) ?></td>
        <td><?= $b['charge_to_tenant'] ? '<span class="badge bg-warning">Tenant</span>' : '<span class="badge bg-info">Owner</span>' ?></td>
        <td class="small"><?= esc($b['due_date']??'—') ?></td>
        <td><span class="badge bg-<?= ['pending'=>'warning','paid'=>'success','transferred'=>'info','cancelled'=>'danger'][$b['status']]??'secondary' ?>"><?= esc($b['status']) ?></span></td>
        <td>
          <?php if ($b['status'] === 'pending'): ?>
          <div class="d-flex gap-1">
            <form method="post" action="<?= base_url('utilities/bills/'.$b['id'].'/transfer-to-tenant') ?>"><?= csrf_field() ?>
              <button class="btn btn-warning btn-sm">→ Tenant</button>
            </form>
            <form method="post" action="<?= base_url('utilities/bills/'.$b['id'].'/transfer-to-owner') ?>"><?= csrf_field() ?>
              <button class="btn btn-info btn-sm text-white">→ Owner</button>
            </form>
            <form method="post" action="<?= base_url('utilities/bills/'.$b['id'].'/paid') ?>"><?= csrf_field() ?>
              <button class="btn btn-success btn-sm">✓ Paid</button>
            </form>
          </div>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($bills)): ?>
        <tr><td colspan="9" class="text-center text-muted py-4">No bills found for this account.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<!-- Add Bill Modal -->
<div class="modal fade" id="addBillModal" tabindex="-1">
  <div class="modal-dialog modal-lg"><div class="modal-content">
    <form method="post" action="<?= base_url('utilities/'.$account['id'].'/bills/add') ?>"><?= csrf_field() ?>
    <div class="modal-header"><h5 class="modal-title">Add Utility Bill</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
      <div class="row g-3">
        <div class="col-md-4">
          <label class="form-label">Bill #</label>
          <input name="bill_no" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Bill Date</label>
          <input type="date" name="bill_date" class="form-control" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label">Due Date</label>
          <input type="date" name="due_date" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Period From</label>
          <input type="date" name="period_from" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Period To</label>
          <input type="date" name="period_to" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Amount <span class="text-danger">*</span></label>
          <input type="number" step="0.01" name="amount" class="form-control" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Prev Reading</label>
          <input type="number" step="0.001" name="reading_prev" class="form-control">
        </div>
        <div class="col-md-3">
          <label class="form-label">Curr Reading</label>
          <input type="number" step="0.001" name="reading_curr" class="form-control">
        </div>
        <div class="col-md-3 d-flex align-items-end pb-1">
          <div class="form-check">
            <input type="checkbox" name="charge_to_tenant" value="1" class="form-check-input" id="chkTenant">
            <label class="form-check-label" for="chkTenant">Charge to Tenant</label>
          </div>
        </div>
        <div class="col-12">
          <label class="form-label">Notes</label>
          <textarea name="notes" class="form-control" rows="2"></textarea>
        </div>
      </div>
    </div>
    <div class="modal-footer"><button type="submit" class="btn btn-fm-primary">Add Bill</button></div>
    </form>
  </div></div>
</div>
<?= $this->endSection() ?>
