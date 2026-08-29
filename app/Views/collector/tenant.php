<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h4 class="mb-0 fw-semibold">
            <span class="avatar-circle me-2"><?= strtoupper(substr($tenant['full_name'] ?? 'T', 0, 1)) ?></span>
            <?= esc($tenant['full_name']) ?>
        </h4>
        <?php if ($tenant['company_name'] ?? ''): ?>
            <div class="text-muted small ms-5"><?= esc($tenant['company_name']) ?></div>
        <?php endif; ?>
    </div>
    <a href="<?= base_url('collector/search') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back to Search</a>
</div>

<div class="row g-3 mb-4">
    <!-- Tenant Info -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px">
            <div class="card-header bg-transparent fw-semibold py-3 border-bottom">
                <i class="bi bi-person me-2 text-danger"></i>Contact Info
            </div>
            <div class="card-body">
                <?php if ($tenant['phone'] ?? ''): ?>
                <div class="mb-2">
                    <div class="text-muted small">Phone</div>
                    <a href="tel:<?= esc($tenant['phone']) ?>" class="fw-medium text-decoration-none">
                        <i class="bi bi-phone me-1"></i><?= esc($tenant['phone']) ?>
                    </a>
                </div>
                <?php endif; ?>
                <?php if ($tenant['email'] ?? ''): ?>
                <div class="mb-2">
                    <div class="text-muted small">Email</div>
                    <div><?= esc($tenant['email']) ?></div>
                </div>
                <?php endif; ?>
                <?php if ($tenant['qid_number'] ?? ''): ?>
                <div class="mb-2">
                    <div class="text-muted small">QID</div>
                    <div><?= esc($tenant['qid_number']) ?></div>
                </div>
                <?php endif; ?>
                <?php if ($tenant['nationality'] ?? ''): ?>
                <div>
                    <div class="text-muted small">Nationality</div>
                    <div><?= esc($tenant['nationality']) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Active Lease -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px">
            <div class="card-header bg-transparent fw-semibold py-3 border-bottom">
                <i class="bi bi-file-earmark-text me-2 text-primary"></i>Active Lease
            </div>
            <div class="card-body">
                <?php if ($activeLease): ?>
                <div class="row g-2">
                    <div class="col-6">
                        <div class="text-muted small">Contract #</div>
                        <div class="fw-medium"><?= esc($activeLease['contract_number'] ?? '—') ?></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Property</div>
                        <div class="fw-medium"><?= esc($activeLease['facility_name'] ?? '—') ?></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Unit</div>
                        <div class="fw-medium"><?= esc($activeLease['unit_number'] ?? '—') ?></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Expires</div>
                        <div class="fw-medium"><?= $activeLease['end_date'] ? date('d M Y', strtotime($activeLease['end_date'])) : '—' ?></div>
                    </div>
                    <div class="col-6">
                        <div class="text-muted small">Monthly Rent</div>
                        <div class="fw-medium"><?= esc($currency) ?> <?= number_format((float)($activeLease['monthly_rent'] ?? 0), 2) ?></div>
                    </div>
                </div>
                <?php else: ?>
                <div class="text-muted py-3"><i class="bi bi-info-circle me-2"></i>No active lease contract found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Open Payments -->
<div class="card border-0 shadow-sm" style="border-radius:12px">
    <div class="card-header bg-transparent fw-semibold py-3 border-bottom d-flex align-items-center justify-content-between">
        <span><i class="bi bi-cash-stack me-2 text-warning"></i>Open Invoices / Payments</span>
        <span class="badge bg-danger rounded-pill"><?= count($openPayments) ?></span>
    </div>

    <?php if (empty($openPayments)): ?>
    <div class="card-body text-center py-5 text-success">
        <i class="bi bi-check-circle fs-1 d-block mb-2"></i>
        No outstanding payments. Account is clear!
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Payment #</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Due Date</th>
                    <th>Status</th>
                    <th>Property</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($openPayments as $p): ?>
                <tr>
                    <td class="fw-medium"><?= esc($p['payment_number']) ?></td>
                    <td><?= esc(ucfirst($p['payment_type'] ?? 'rent')) ?></td>
                    <td class="fw-semibold"><?= esc($currency) ?> <?= number_format((float)$p['amount'], 2) ?></td>
                    <td>
                        <?php if ($p['due_date']): ?>
                            <?php $daysOverdue = (int) floor((time() - strtotime($p['due_date'])) / 86400); ?>
                            <?= date('d M Y', strtotime($p['due_date'])) ?>
                            <?php if ($daysOverdue > 0): ?>
                                <span class="badge bg-danger ms-1"><?= $daysOverdue ?>d overdue</span>
                            <?php endif; ?>
                        <?php else: ?>—<?php endif; ?>
                    </td>
                    <td>
                        <?php $statusColors = ['pending' => 'secondary', 'partial' => 'warning', 'overdue' => 'danger']; ?>
                        <span class="badge bg-<?= $statusColors[$p['status']] ?? 'secondary' ?>">
                            <?= ucfirst($p['status']) ?>
                        </span>
                    </td>
                    <td><?= esc($p['facility_name'] ?? '—') ?></td>
                    <td>
                        <a href="<?= base_url('collector/collect/' . $p['id']) ?>"
                           class="btn btn-danger btn-sm px-3 py-2">
                            <i class="bi bi-cash-coin me-1"></i>Collect
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
