<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-arrow-left-right me-2"></i>Cash Flow</h1>
    <div class="small text-muted">Money in and out — filter by date, facility, source, and method</div>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('finance/payments') ?>" class="btn btn-fm-outline btn-sm">Payments</a>
    <a href="<?= base_url('finance/ledger') ?>" class="btn btn-fm-outline btn-sm">Ledger</a>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-4">
    <div class="kpi-card kpi-green">
      <div class="kpi-label">Cash In</div>
      <div class="kpi-value" style="font-size:1.1rem"><?= $currency ?> <?= number_format((float)$totalIn, 2) ?></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="kpi-card kpi-red">
      <div class="kpi-label">Cash Out</div>
      <div class="kpi-value" style="font-size:1.1rem"><?= $currency ?> <?= number_format((float)$totalOut, 2) ?></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="kpi-card kpi-primary">
      <div class="kpi-label">Net Cash Flow</div>
      <div class="kpi-value" style="font-size:1.1rem"><?= $currency ?> <?= number_format((float)$netCash, 2) ?></div>
    </div>
  </div>
</div>

<div class="fm-card mb-3">
  <div class="fm-card-body">
    <?= form_open(base_url('finance/cash-flow'), ['method' => 'get', 'class' => 'row g-2 align-items-end', 'data-no-loader' => '']) ?>
    <div class="col-md-2">
      <label class="form-label small">From</label>
      <input type="date" name="from" class="form-control form-control-sm" value="<?= esc($from) ?>">
    </div>
    <div class="col-md-2">
      <label class="form-label small">To</label>
      <input type="date" name="to" class="form-control form-control-sm" value="<?= esc($to) ?>">
    </div>
    <div class="col-md-2">
      <label class="form-label small">Flow</label>
      <select name="flow" class="form-select form-select-sm">
        <option value="all" <?= $flow === 'all' ? 'selected' : '' ?>>All</option>
        <option value="in" <?= $flow === 'in' ? 'selected' : '' ?>>In only</option>
        <option value="out" <?= $flow === 'out' ? 'selected' : '' ?>>Out only</option>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label small">Source</label>
      <select name="source" class="form-select form-select-sm">
        <option value="all" <?= $sourceType === 'all' ? 'selected' : '' ?>>All sources</option>
        <option value="invoice_payment" <?= $sourceType === 'invoice_payment' ? 'selected' : '' ?>>Customer payments</option>
        <option value="expense" <?= $sourceType === 'expense' ? 'selected' : '' ?>>Expenses</option>
        <option value="reimbursement" <?= $sourceType === 'reimbursement' ? 'selected' : '' ?>>Reimbursements</option>
        <option value="petty_cash" <?= $sourceType === 'petty_cash' ? 'selected' : '' ?>>Petty cash</option>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label small">Facility</label>
      <select name="facility" class="form-select form-select-sm">
        <option value="">All</option>
        <?php foreach ($facilities as $f): ?>
        <option value="<?= (int)$f['id'] ?>" <?= (int)$filterFacility === (int)$f['id'] ? 'selected' : '' ?>><?= esc($f['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label small">Payment method</label>
      <select name="method" class="form-select form-select-sm">
        <option value="">Any</option>
        <?php foreach (['cash','bank','card','cheque','online'] as $m): ?>
        <option value="<?= $m ?>" <?= $payMethod === $m ? 'selected' : '' ?>><?= ucfirst($m) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2">
      <label class="form-label small">Invoice type</label>
      <select name="invoice_type" class="form-select form-select-sm">
        <option value="">Any</option>
        <option value="work_order" <?= $invoiceType === 'work_order' ? 'selected' : '' ?>>Work order</option>
        <option value="contract" <?= $invoiceType === 'contract' ? 'selected' : '' ?>>Contract</option>
        <option value="adhoc" <?= $invoiceType === 'adhoc' ? 'selected' : '' ?>>Ad hoc</option>
      </select>
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-fm-primary btn-sm">Apply</button>
    </div>
    <?= form_close() ?>
  </div>
</div>

<div class="fm-card">
  <div class="card-header-fm"><h5><i class="bi bi-list-ul me-2"></i>Transactions</h5></div>
  <div class="fm-card-body p-0">
    <table class="fm-table">
      <thead>
        <tr>
          <th>Date</th><th>Flow</th><th>Source</th><th>Reference</th>
          <th>Facility</th><th>Method</th><th class="text-end">Amount</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td class="small"><?= !empty($r['entry_date']) ? date('d M Y', strtotime($r['entry_date'])) : '—' ?></td>
        <td>
          <span class="fm-badge <?= $r['flow'] === 'in' ? 'badge-status-paid' : 'badge-status-pending' ?>">
            <?= $r['flow'] === 'in' ? 'In' : 'Out' ?>
          </span>
        </td>
        <td class="small"><?= esc($r['source_label']) ?></td>
        <td class="small fw-semibold"><?= esc($r['ref_no']) ?></td>
        <td class="small"><?= esc($r['facility_name'] ?: '—') ?></td>
        <td class="small"><?= esc($r['payment_method'] ?: '—') ?></td>
        <td class="text-end fw-semibold <?= $r['flow'] === 'in' ? 'text-success' : 'text-danger' ?>">
          <?= $r['flow'] === 'in' ? '+' : '-' ?> <?= $currency ?> <?= number_format((float)$r['amount'], 2) ?>
        </td>
      </tr>
      <?php if (!empty($r['detail'])): ?>
      <tr><td colspan="7" class="small text-muted py-0 ps-4 border-0"><?= esc($r['detail']) ?></td></tr>
      <?php endif; ?>
      <?php endforeach; ?>
      <?php if (empty($rows)): ?>
      <tr><td colspan="7" class="text-center py-4 text-muted">No cash movements for these filters</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?= $this->endSection() ?>
