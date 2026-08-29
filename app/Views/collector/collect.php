<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0 fw-semibold"><i class="bi bi-cash-coin me-2 text-danger"></i>Collect Payment</h4>
    <a href="<?= base_url('collector/tenant/' . ($payment['tenant_id'] ?? '')) ?>" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to Tenant
    </a>
</div>

<!-- Payment Summary Card -->
<div class="card border-0 shadow-sm mb-4" style="border-radius:12px;border-top:4px solid var(--primary)!important">
    <div class="card-header bg-transparent py-3 fw-semibold border-bottom">
        <i class="bi bi-receipt me-2 text-primary"></i>Invoice Details
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-3 col-6">
                <div class="text-muted small mb-1">Payment #</div>
                <div class="fw-bold"><?= esc($payment['payment_number']) ?></div>
            </div>
            <div class="col-md-3 col-6">
                <div class="text-muted small mb-1">Tenant</div>
                <div class="fw-bold"><?= esc($payment['tenant_name'] ?? '—') ?></div>
                <?php if ($payment['tenant_phone'] ?? ''): ?>
                <a href="tel:<?= esc($payment['tenant_phone']) ?>" class="text-muted small text-decoration-none">
                    <i class="bi bi-phone me-1"></i><?= esc($payment['tenant_phone']) ?>
                </a>
                <?php endif; ?>
            </div>
            <div class="col-md-3 col-6">
                <div class="text-muted small mb-1">Property</div>
                <div><?= esc($payment['facility_name'] ?? '—') ?></div>
            </div>
            <div class="col-md-3 col-6">
                <div class="text-muted small mb-1">Due Date</div>
                <div><?= $payment['due_date'] ? date('d M Y', strtotime($payment['due_date'])) : '—' ?></div>
            </div>
            <div class="col-md-3 col-6">
                <div class="text-muted small mb-1">Invoice Amount</div>
                <div class="fw-bold fs-5 text-danger"><?= esc($currency) ?> <?= number_format((float)$payment['amount'], 2) ?></div>
            </div>
            <div class="col-md-3 col-6">
                <div class="text-muted small mb-1">Type</div>
                <div><?= esc(ucfirst($payment['payment_type'] ?? 'rent')) ?></div>
            </div>
            <div class="col-md-3 col-6">
                <div class="text-muted small mb-1">Status</div>
                <?php $statusColors = ['pending' => 'secondary', 'partial' => 'warning', 'overdue' => 'danger']; ?>
                <span class="badge bg-<?= $statusColors[$payment['status']] ?? 'secondary' ?> fs-6">
                    <?= ucfirst($payment['status']) ?>
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Session Warning -->
<?php if (!$openSession): ?>
<div class="alert alert-warning d-flex align-items-center mb-4">
    <i class="bi bi-exclamation-triangle me-3 fs-4"></i>
    <div>
        <strong>No active session.</strong> You can still collect payments, but it's recommended to
        <a href="<?= base_url('collector/session') ?>" class="alert-link">start a session</a> first.
    </div>
</div>
<?php endif; ?>

<!-- Collection Form -->
<div class="card border-0 shadow-sm" style="border-radius:12px">
    <div class="card-header bg-transparent py-3 fw-semibold border-bottom">
        <i class="bi bi-pencil-square me-2 text-success"></i>Record Collection
    </div>
    <div class="card-body">
        <form method="POST" action="<?= base_url('collector/collect/' . $payment['id']) ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Amount Collected <span class="text-danger">*</span></label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text fw-bold"><?= esc($currency) ?></span>
                        <input type="number" name="amount" class="form-control"
                               step="0.01" min="0.01"
                               max="<?= (float)$payment['amount'] ?>"
                               value="<?= (float)$payment['amount'] ?>"
                               required>
                    </div>
                    <div class="form-text">Enter less than the full amount for partial payment.</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                    <div class="d-flex gap-2 flex-wrap mt-1">
                        <input type="radio" class="btn-check" name="payment_method" id="pm_cash" value="cash" checked required>
                        <label class="btn btn-outline-success btn-lg flex-fill" for="pm_cash">
                            <i class="bi bi-cash me-1"></i>Cash
                        </label>
                        <input type="radio" class="btn-check" name="payment_method" id="pm_cheque" value="cheque">
                        <label class="btn btn-outline-primary btn-lg flex-fill" for="pm_cheque">
                            <i class="bi bi-bank me-1"></i>Cheque
                        </label>
                        <input type="radio" class="btn-check" name="payment_method" id="pm_transfer" value="transfer">
                        <label class="btn btn-outline-info btn-lg flex-fill" for="pm_transfer">
                            <i class="bi bi-arrow-left-right me-1"></i>Transfer
                        </label>
                    </div>
                </div>

                <div class="col-md-4" id="chequeNoWrap" style="display:none">
                    <label class="form-label fw-semibold">Cheque No</label>
                    <input type="text" name="cheque_no" class="form-control form-control-lg" placeholder="Cheque number...">
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Notes / Remarks</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Collection remarks…"></textarea>
                </div>
            </div>

            <div class="mt-4 d-flex gap-3 flex-wrap">
                <button type="submit" class="btn btn-success btn-lg px-5">
                    <i class="bi bi-check-circle me-2"></i>Confirm Collection
                </button>
                <a href="<?= base_url('collector/tenant/' . ($payment['tenant_id'] ?? '')) ?>"
                   class="btn btn-outline-secondary btn-lg px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.querySelectorAll('input[name="payment_method"]').forEach(el => {
    el.addEventListener('change', () => {
        const wrap = document.getElementById('chequeNoWrap');
        if (wrap) wrap.style.display = el.value === 'cheque' ? '' : 'none';
    });
});
</script>
<?= $this->endSection() ?>
