<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0 fw-semibold"><i class="bi bi-clock-history me-2 text-secondary"></i>Collection History</h4>
    <a href="<?= base_url('collector') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Dashboard</a>
</div>

<!-- Date filter -->
<div class="card border-0 shadow-sm mb-4" style="border-radius:12px">
    <div class="card-body py-3">
        <form method="GET" action="<?= base_url('collector/history') ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-medium mb-1">From</label>
                <input type="date" name="from" class="form-control" value="<?= esc($from) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-medium mb-1">To</label>
                <input type="date" name="to" class="form-control" value="<?= esc($to) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter me-1"></i>Filter</button>
            </div>
            <div class="col-md-2">
                <a href="<?= base_url('collector/report?date=' . date('Y-m-d')) ?>" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-printer me-1"></i>Print Today
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Summary Bar -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3" style="border-radius:12px">
            <div class="fs-2 fw-bold text-danger"><?= count($payments) ?></div>
            <div class="text-muted small">Transactions</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3" style="border-radius:12px">
            <div class="fs-2 fw-bold text-success"><?= esc($currency) ?> <?= number_format($total, 2) ?></div>
            <div class="text-muted small">Total Collected</div>
        </div>
    </div>
</div>

<!-- Payments table -->
<?php if (empty($payments)): ?>
<div class="card border-0 shadow-sm" style="border-radius:12px">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-inbox fs-1 d-block mb-2"></i>No collections found for the selected period.
    </div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm" style="border-radius:12px">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Payment #</th>
                    <th>Tenant</th>
                    <th>Property</th>
                    <th>Method</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $p): ?>
                <tr>
                    <td><?= $p['payment_date'] ? date('d M Y', strtotime($p['payment_date'])) : '—' ?></td>
                    <td class="fw-medium"><?= esc($p['payment_number']) ?></td>
                    <td><?= esc($p['tenant_name'] ?? '—') ?></td>
                    <td><?= esc($p['facility_name'] ?? '—') ?></td>
                    <td>
                        <?php $micons = ['cash' => 'bi-cash', 'cheque' => 'bi-bank', 'transfer' => 'bi-arrow-left-right']; ?>
                        <i class="bi <?= $micons[$p['payment_method']] ?? 'bi-credit-card' ?> me-1"></i>
                        <?= esc(ucfirst($p['payment_method'] ?? '—')) ?>
                    </td>
                    <td class="fw-semibold"><?= esc($currency) ?> <?= number_format((float)$p['amount'], 2) ?></td>
                    <td>
                        <?php $colors = ['paid' => 'success', 'partial' => 'warning']; ?>
                        <span class="badge bg-<?= $colors[$p['status']] ?? 'secondary' ?>"><?= ucfirst($p['status']) ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="table-light fw-semibold">
                <tr>
                    <td colspan="5" class="text-end">Total:</td>
                    <td><?= esc($currency) ?> <?= number_format($total, 2) ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
