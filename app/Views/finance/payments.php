<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $canPay = !empty($canManagePayments); ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-cash-coin me-2"></i>Payments</h1>
    <div class="small text-muted">Record collections — includes work order draft invoices ready to collect</div>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= base_url('finance/invoices') ?>" class="btn btn-fm-outline btn-sm">All Invoices</a>
    <a href="<?= base_url('finance/cash-flow') ?>" class="btn btn-fm-outline btn-sm">Cash Flow</a>
    <a href="<?= base_url('finance/ledger') ?>" class="btn btn-fm-outline btn-sm">Ledger</a>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="kpi-card kpi-orange">
      <div class="kpi-label">Outstanding (balance)</div>
      <div class="kpi-value" style="font-size:1.1rem"><?= $currency ?> <?= number_format((float)($stats['outstanding']??0),2) ?></div>
      <div class="kpi-sub"><?= (int)($stats['pending_count']??0) ?> invoice(s)</div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="kpi-card kpi-green">
      <div class="kpi-label">Collected (YTD)</div>
      <div class="kpi-value" style="font-size:1.1rem"><?= $currency ?> <?= number_format((float)($stats['collected_ytd']??0),2) ?></div>
    </div>
  </div>
</div>

<div class="fm-card mb-3">
  <div class="fm-card-body py-2">
    <?= form_open(base_url('finance/payments'), ['method' => 'get', 'class' => 'row g-2 align-items-end', 'data-no-loader' => '']) ?>
    <div class="col-auto">
      <label class="form-label small mb-0">Facility</label>
      <select name="facility" class="form-select form-select-sm">
        <option value="">All</option>
        <?php foreach ($facilities ?? [] as $f): ?>
        <option value="<?= (int)$f['id'] ?>" <?= (int)($filterFacility??0) === (int)$f['id'] ? 'selected' : '' ?>><?= esc($f['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-auto">
      <label class="form-label small mb-0">Invoice type</label>
      <select name="type" class="form-select form-select-sm">
        <option value="">All</option>
        <option value="work_order" <?= ($filterType??'') === 'work_order' ? 'selected' : '' ?>>Work order</option>
        <option value="contract" <?= ($filterType??'') === 'contract' ? 'selected' : '' ?>>Contract</option>
        <option value="adhoc" <?= ($filterType??'') === 'adhoc' ? 'selected' : '' ?>>Ad hoc</option>
      </select>
    </div>
    <div class="col-auto"><button type="submit" class="btn btn-fm-primary btn-sm">Filter</button></div>
    <?= form_close() ?>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="fm-card">
      <div class="card-header-fm"><h5><i class="bi bi-hourglass-split me-2"></i>Collect Payment</h5></div>
      <div class="fm-card-body p-0">
        <table class="fm-table">
          <thead>
            <tr>
              <th>Invoice</th><th>Facility</th><th>Due</th><th>Status</th>
              <th class="text-end">Total</th><th class="text-end">Balance</th><th></th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($pending as $inv): ?>
          <tr>
            <td>
              <a href="<?= base_url('finance/invoices/view/'.$inv['id']) ?>" class="fw-semibold"><?= esc($inv['invoice_number']) ?></a>
              <?php if (!empty($inv['wo_number'])): ?>
              <div class="x-small text-muted">WO <?= esc($inv['wo_number']) ?></div>
              <?php endif; ?>
            </td>
            <td class="small"><?= esc($inv['facility_name']??'') ?></td>
            <td class="small"><?= $inv['due_date'] ? date('d M Y', strtotime($inv['due_date'])) : '—' ?></td>
            <td><span class="fm-badge badge-status-<?= esc($inv['status']) ?>"><?= ucfirst($inv['status']) ?></span></td>
            <td class="text-end"><?= $currency ?> <?= number_format((float)$inv['total'],2) ?></td>
            <td class="text-end fw-bold text-danger"><?= $currency ?> <?= number_format((float)($inv['balance_due']??$inv['total']),2) ?></td>
            <td class="text-end" style="min-width:200px">
              <?php if ($canPay): ?>
              <button type="button" class="btn btn-success btn-sm" data-bs-toggle="collapse" data-bs-target="#payForm<?= (int)$inv['id'] ?>">
                Record payment
              </button>
              <?php endif; ?>
            </td>
          </tr>
          <?php if ($canPay): ?>
          <tr class="collapse" id="payForm<?= (int)$inv['id'] ?>">
            <td colspan="7" class="bg-light">
              <div class="p-3" style="max-width:420px">
                <?= view('finance/_record_payment_form', [
                  'invoiceId'   => (int) $inv['id'],
                  'balanceDue'  => (float) ($inv['balance_due'] ?? $inv['total']),
                  'currency'    => $currency ?? 'QAR',
                  'redirectTo'  => current_url(),
                ]) ?>
              </div>
            </td>
          </tr>
          <?php endif; ?>
          <?php endforeach; ?>
          <?php if (empty($pending)): ?>
          <tr><td colspan="7" class="text-center py-4 text-muted">No invoices with balance due. Work order invoices appear here even in draft status.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="fm-card">
      <div class="card-header-fm"><h5><i class="bi bi-check-circle me-2"></i>Recently Paid</h5></div>
      <div class="fm-card-body p-0">
        <?php foreach ($paidRecent as $inv): ?>
        <a href="<?= base_url('finance/invoices/view/'.$inv['id']) ?>" class="d-flex justify-content-between px-3 py-2 border-bottom border-light text-decoration-none text-reset">
          <div>
            <div class="small fw-semibold"><?= esc($inv['invoice_number']) ?></div>
            <div class="x-small text-muted"><?= esc($inv['facility_name']??'') ?></div>
          </div>
          <div class="text-end">
            <div class="small fw-bold text-success"><?= $currency ?> <?= number_format((float)$inv['total'],2) ?></div>
            <div class="x-small text-muted"><?= $inv['paid_at'] ? date('d M Y', strtotime($inv['paid_at'])) : '' ?></div>
          </div>
        </a>
        <?php endforeach; ?>
        <?php if (empty($paidRecent)): ?><p class="text-center py-4 text-muted small">No recent payments</p><?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
