<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-arrow-left-right me-2 text-primary"></i>Budget Reconciliation</h1></div>
  <a href="<?= base_url('budgets') ?>" class="btn btn-fm-outline btn-sm">Back to Budgets</a>
</div>

<form class="filters-inline form-card mb-3" method="get">
  <select name="facility_id" class="form-select form-select-sm">
    <option value="">— Select Property —</option>
    <?php foreach ($facilities as $f): ?>
      <option value="<?= $f['id'] ?>" <?= ($filters['facility_id']??0)==$f['id']?'selected':'' ?>><?= esc($f['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="year" class="form-select form-select-sm">
    <?php for ($y=date('Y'); $y>=date('Y')-3; $y--): ?>
      <option value="<?= $y ?>" <?= ($filters['year']??date('Y'))==$y?'selected':'' ?>><?= $y ?></option>
    <?php endfor; ?>
  </select>
  <select name="month" class="form-select form-select-sm">
    <?php for ($m=1;$m<=12;$m++): ?>
      <option value="<?= $m ?>" <?= ($filters['month']??date('n'))==$m?'selected':'' ?>><?= date('F', mktime(0,0,0,$m,1)) ?></option>
    <?php endfor; ?>
  </select>
  <button class="btn btn-fm-primary btn-sm" type="submit">Run Reconcile</button>
</form>

<?php if (!empty($unmatched)): ?>
<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="form-card text-center">
      <div class="small text-muted text-uppercase">Lease Payments Total</div>
      <div class="fs-4 fw-bold text-success"><?= number_format($unmatched['lease_total'],2) ?></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-card text-center">
      <div class="small text-muted text-uppercase">Finance Ledger Total</div>
      <div class="fs-4 fw-bold text-info"><?= number_format($unmatched['finance_total'],2) ?></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="form-card text-center">
      <div class="small text-muted text-uppercase">Difference</div>
      <?php $diff = $unmatched['difference']; ?>
      <div class="fs-4 fw-bold <?= abs($diff)<0.01?'text-success':($diff>0?'text-warning':'text-danger') ?>"><?= number_format($diff,2) ?></div>
      <?php if (abs($diff) < 0.01): ?><div class="badge bg-success">Balanced ✓</div><?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="form-card">
      <h6 class="text-muted text-uppercase small mb-3">Lease Payments</h6>
      <?php if (!empty($leasePayments)): ?>
      <table class="table table-sm">
        <thead><tr><th>Ref</th><th>Tenant</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($leasePayments as $lp): ?>
          <tr>
            <td class="small"><?= esc($lp['payment_number']) ?></td>
            <td><?= esc($lp['tenant_name']??'—') ?></td>
            <td class="small"><?= esc($lp['payment_date']) ?></td>
            <td><?= number_format((float)$lp['amount'],2) ?></td>
            <td><span class="badge bg-<?= $lp['status']==='paid'?'success':'warning' ?>"><?= esc($lp['status']) ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p class="text-muted text-center py-3"><?= $filters['facility_id'] ? 'No lease payments for this period.' : 'Select a property to load data.' ?></p>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="form-card">
      <h6 class="text-muted text-uppercase small mb-3">Finance Ledger Entries</h6>
      <?php if (!empty($financeItems)): ?>
      <table class="table table-sm">
        <thead><tr><th>Ref</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead>
        <tbody>
          <?php foreach ($financeItems as $fi): ?>
          <tr>
            <td class="small"><?= esc($fi['ref_number']) ?></td>
            <td class="small"><?= esc($fi['date']) ?></td>
            <td><?= number_format((float)$fi['amount'],2) ?></td>
            <td><span class="badge bg-secondary"><?= esc($fi['status']) ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p class="text-muted text-center py-3"><?= $filters['facility_id'] ? 'No finance entries for this period.' : 'Select a property to load data.' ?></p>
      <?php endif; ?>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
