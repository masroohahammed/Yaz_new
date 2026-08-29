<?= $this->extend('layouts/main') ?>
<?= $this->section('head') ?>
<style>
@media print {
    .sidebar, .topbar, .no-print, nav, .main-wrapper > .topbar { display: none !important; }
    .main-wrapper { margin: 0 !important; }
    .main-content { padding: 0 !important; }
    .card { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
    .btn { display: none !important; }
}
.report-header { background: var(--primary); color: white; border-radius: 12px 12px 0 0; padding: 1.5rem 2rem; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-3 no-print">
    <h4 class="mb-0 fw-semibold"><i class="bi bi-printer me-2 text-primary"></i>Daily Collection Report</h4>
    <div class="d-flex gap-2">
        <form method="GET" class="d-flex gap-2">
            <input type="date" name="date" class="form-control" value="<?= esc($date) ?>">
            <button type="submit" class="btn btn-primary"><i class="bi bi-filter me-1"></i>Load</button>
        </form>
        <button onclick="window.print()" class="btn btn-outline-secondary">
            <i class="bi bi-printer me-1"></i>Print
        </button>
        <a href="<?= base_url('collector') ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Dashboard</a>
    </div>
</div>

<!-- Report Document -->
<div class="card border-0 shadow-sm" style="border-radius:12px">
    <!-- Header -->
    <div class="report-header">
        <div class="row align-items-center">
            <div class="col">
                <h5 class="mb-1 fw-bold">Daily Collection Report</h5>
                <div class="opacity-75"><?= date('l, d F Y', strtotime($date)) ?></div>
            </div>
            <div class="col-auto text-end">
                <?php if (!empty($companyLogoUrl)): ?>
                    <img src="<?= esc($companyLogoUrl) ?>" alt="Logo" style="max-height:50px">
                <?php else: ?>
                    <div class="fw-bold fs-5"><?= esc($settings['company_name'] ?? 'FM ERP') ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card-body px-4 py-3">
        <!-- Collector Info -->
        <div class="row mb-4">
            <div class="col-md-6">
                <table class="table table-borderless table-sm mb-0">
                    <tr><td class="text-muted fw-medium" width="140">Collector:</td><td class="fw-semibold"><?= esc($collector['name'] ?? '—') ?></td></tr>
                    <tr><td class="text-muted fw-medium">Date:</td><td><?= date('d/m/Y', strtotime($date)) ?></td></tr>
                    <?php if ($session): ?>
                    <tr><td class="text-muted fw-medium">Session:</td><td><?= esc($session['session_code'] ?? '—') ?></td></tr>
                    <tr><td class="text-muted fw-medium">Opening Float:</td><td><?= esc($currency) ?> <?= number_format((float)($session['opening_float'] ?? 0), 2) ?></td></tr>
                    <?php endif; ?>
                </table>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="display-6 fw-bold text-danger"><?= esc($currency) ?> <?= number_format($total, 2) ?></div>
                <div class="text-muted">Total Collected</div>
                <div class="mt-1 fw-medium"><?= count($payments) ?> transaction(s)</div>
            </div>
        </div>

        <hr>

        <?php if (empty($payments)): ?>
        <div class="text-center py-4 text-muted">
            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
            No collections recorded for this date.
        </div>
        <?php else: ?>
        <table class="table table-bordered table-sm align-middle">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Payment #</th>
                    <th>Tenant</th>
                    <th>Phone</th>
                    <th>Property</th>
                    <th>Method</th>
                    <th class="text-end">Amount</th>
                    <th>Status</th>
                    <th>Cheque #</th>
                </tr>
            </thead>
            <tbody>
                <?php $seq = 1; foreach ($payments as $p): ?>
                <tr>
                    <td><?= $seq++ ?></td>
                    <td><?= esc($p['payment_number']) ?></td>
                    <td><?= esc($p['tenant_name'] ?? '—') ?></td>
                    <td><?= esc($p['tenant_phone'] ?? '—') ?></td>
                    <td><?= esc($p['facility_name'] ?? '—') ?></td>
                    <td><?= esc(ucfirst($p['payment_method'] ?? '—')) ?></td>
                    <td class="text-end fw-semibold"><?= esc($currency) ?> <?= number_format((float)$p['amount'], 2) ?></td>
                    <td><?= ucfirst($p['status']) ?></td>
                    <td><?= esc($p['cheque_no'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="fw-bold">
                <tr class="table-warning">
                    <td colspan="6" class="text-end">TOTAL</td>
                    <td class="text-end"><?= esc($currency) ?> <?= number_format($total, 2) ?></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
        <?php endif; ?>

        <!-- Signatures -->
        <div class="row mt-5 pt-4">
            <div class="col-md-5">
                <div style="border-top:1px solid #333;padding-top:8px">
                    <div class="fw-semibold">Collector Signature</div>
                    <div class="text-muted small"><?= esc($collector['name'] ?? '') ?></div>
                </div>
            </div>
            <div class="col-md-5 offset-md-2">
                <div style="border-top:1px solid #333;padding-top:8px">
                    <div class="fw-semibold">Finance / Supervisor Signature</div>
                    <div class="text-muted small">Acknowledged By</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
