<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-credit-card me-2"></i>Accounts Payable</h1>
    <p class="text-muted small mb-0">Outstanding supplier invoices and payment tracking</p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('reports/procurement') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-bar-chart-line me-1"></i>Procurement Report</a>
    <a href="<?= base_url('procurement') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-bag-check me-1"></i>Procurement</a>
  </div>
</div>

<!-- KPI row -->
<div class="row g-3 mb-3">
  <div class="col-sm-4">
    <div class="fm-card fm-card-body text-center">
      <div class="kpi-label">Total Due</div>
      <div class="kpi-value"><?= esc($currency) ?> <?= number_format($totalDue, 2) ?></div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="fm-card fm-card-body text-center">
      <div class="kpi-label">Total Paid</div>
      <div class="kpi-value text-success"><?= esc($currency) ?> <?= number_format($totalPaid, 2) ?></div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="fm-card fm-card-body text-center">
      <div class="kpi-label">Outstanding</div>
      <div class="kpi-value text-danger"><?= esc($currency) ?> <?= number_format($outstanding, 2) ?></div>
    </div>
  </div>
</div>

<!-- Filters -->
<form class="fm-card fm-card-body mb-3 d-flex flex-wrap gap-2 align-items-end" method="get">
  <div>
    <label class="form-label small fw-semibold">From</label>
    <input type="date" name="from" class="form-control form-control-sm" value="<?= esc($from) ?>">
  </div>
  <div>
    <label class="form-label small fw-semibold">To</label>
    <input type="date" name="to" class="form-control form-control-sm" value="<?= esc($to) ?>">
  </div>
  <div>
    <label class="form-label small fw-semibold">Status</label>
    <select name="status" class="form-select form-select-sm">
      <option value="">All</option>
      <option value="pending"  <?= $status==='pending'  ?'selected':'' ?>>Pending</option>
      <option value="partial"  <?= $status==='partial'  ?'selected':'' ?>>Partial</option>
      <option value="paid"     <?= $status==='paid'     ?'selected':'' ?>>Paid</option>
    </select>
  </div>
  <button type="submit" class="btn btn-fm-primary btn-sm">Filter</button>
</form>

<!-- Payables table -->
<div class="fm-card">
  <div class="fm-card-body p-0">
    <div class="table-responsive">
      <table class="fm-table">
        <thead>
          <tr>
            <th>PO #</th><th>Vendor</th><th>Total</th><th>Paid</th><th>Balance</th>
            <th>Due Date</th><th>Status</th><th></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($payables)): ?>
          <tr><td colspan="8" class="text-center text-muted py-4">No payables found for the selected period.</td></tr>
          <?php else: ?>
          <?php foreach ($payables as $p):
            $balance = (float)$p['total_amount'] - (float)$p['paid_amount'];
            $overdue = !empty($p['due_date']) && $p['due_date'] < date('Y-m-d') && $p['payment_status'] !== 'paid';
          ?>
          <tr class="<?= $overdue ? 'table-danger' : '' ?>">
            <td class="fw-semibold"><?= esc($p['po_number']) ?></td>
            <td><?= esc($p['vendor_name'] ?? '—') ?></td>
            <td><?= esc($currency) ?> <?= number_format((float)$p['total_amount'], 2) ?></td>
            <td class="text-success"><?= esc($currency) ?> <?= number_format((float)$p['paid_amount'], 2) ?></td>
            <td class="fw-semibold <?= $balance > 0 ? 'text-danger' : 'text-success' ?>">
              <?= esc($currency) ?> <?= number_format($balance, 2) ?>
            </td>
            <td class="small <?= $overdue ? 'text-danger fw-semibold' : '' ?>">
              <?= !empty($p['due_date']) ? date('d M Y', strtotime($p['due_date'])) : '—' ?>
              <?= $overdue ? '<i class="bi bi-exclamation-triangle ms-1"></i>' : '' ?>
            </td>
            <td>
              <span class="fm-badge fm-badge-<?= $p['payment_status'] ?>">
                <?= ucfirst($p['payment_status'] ?? 'pending') ?>
              </span>
            </td>
            <td class="text-end">
              <?php if ($p['payment_status'] !== 'paid'): ?>
              <button type="button" class="btn btn-sm btn-success"
                      data-bs-toggle="modal"
                      data-bs-target="#payModal"
                      data-po-id="<?= $p['id'] ?>"
                      data-po-num="<?= esc($p['po_number']) ?>"
                      data-balance="<?= $balance ?>">
                <i class="bi bi-cash me-1"></i>Record Payment
              </button>
              <?php endif; ?>
              <a href="<?= base_url('procurement/order/view/' . $p['id']) ?>" class="btn btn-sm btn-fm-outline ms-1">View PO</a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Payment modal -->
<div class="modal fade" id="payModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-cash me-2"></i>Record Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="payForm" method="post">
        <?= csrf_field() ?>
        <div class="modal-body">
          <p class="small text-muted mb-3">PO: <strong id="payPoNum"></strong> — Balance: <strong id="payBalance"></strong></p>
          <div class="mb-3">
            <label class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
            <input type="number" name="amount" id="payAmount" class="form-control" step="0.01" min="0.01" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Payment Method</label>
            <select name="method" class="form-select">
              <option value="bank_transfer">Bank Transfer</option>
              <option value="cheque">Cheque</option>
              <option value="cash">Cash</option>
              <option value="online">Online Payment</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Reference / Cheque No.</label>
            <input type="text" name="reference" class="form-control" placeholder="Optional reference">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-fm-primary"><i class="bi bi-check-lg me-1"></i>Save Payment</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
document.getElementById('payModal')?.addEventListener('show.bs.modal', function(e) {
  const btn = e.relatedTarget;
  document.getElementById('payPoNum').textContent   = btn.dataset.poNum;
  document.getElementById('payBalance').textContent = '<?= esc($currency) ?> ' + parseFloat(btn.dataset.balance).toFixed(2);
  document.getElementById('payAmount').value        = parseFloat(btn.dataset.balance).toFixed(2);
  document.getElementById('payForm').action         = '<?= base_url('finance/accounts-payable/pay/') ?>' + btn.dataset.poId;
});
</script>
<?= $this->endSection() ?>
