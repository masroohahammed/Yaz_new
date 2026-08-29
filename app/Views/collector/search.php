<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0 fw-semibold"><i class="bi bi-search me-2 text-danger"></i>Search Tenant</h4>
    <a href="<?= base_url('collector') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Dashboard</a>
</div>

<!-- Search Bar -->
<div class="card border-0 shadow-sm mb-4" style="border-radius:12px">
    <div class="card-body py-3">
        <form method="GET" action="<?= base_url('collector/search') ?>">
            <div class="input-group input-group-lg">
                <span class="input-group-text bg-danger text-white border-danger"><i class="bi bi-search"></i></span>
                <input type="search" name="q" class="form-control border-danger"
                       value="<?= esc($q) ?>"
                       placeholder="Search by name, phone, QID or email…"
                       autofocus>
                <button type="submit" class="btn btn-danger px-4">Search</button>
            </div>
        </form>
    </div>
</div>

<!-- Results -->
<?php if ($q !== '' && empty($tenants)): ?>
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle me-2"></i>No tenants found for "<strong><?= esc($q) ?></strong>".
</div>
<?php elseif (!empty($tenants)): ?>
<div class="card border-0 shadow-sm" style="border-radius:12px">
    <div class="card-header bg-transparent py-3 fw-semibold border-bottom">
        <i class="bi bi-people me-2"></i><?= count($tenants) ?> tenant(s) found
    </div>
    <div class="list-group list-group-flush">
        <?php foreach ($tenants as $t): ?>
        <a href="<?= base_url('collector/tenant/' . $t['id']) ?>"
           class="list-group-item list-group-item-action d-flex align-items-center justify-content-between py-3 px-4 gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="avatar-circle flex-shrink-0 fs-4"><?= strtoupper(substr($t['full_name'] ?? 'T', 0, 1)) ?></div>
                <div>
                    <div class="fw-semibold"><?= esc($t['full_name']) ?></div>
                    <div class="text-muted small">
                        <?= $t['phone'] ? '<i class="bi bi-phone me-1"></i>' . esc($t['phone']) : '' ?>
                        <?= $t['qid_number'] ? ' &bull; QID: ' . esc($t['qid_number']) : '' ?>
                        <?= $t['company_name'] ? ' &bull; ' . esc($t['company_name']) : '' ?>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <?php if (!empty($t['open_payments']) && $t['open_payments'] > 0): ?>
                    <span class="badge bg-danger rounded-pill"><?= $t['open_payments'] ?> open</span>
                <?php else: ?>
                    <span class="badge bg-success rounded-pill">Clear</span>
                <?php endif; ?>
                <i class="bi bi-chevron-right text-muted"></i>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php elseif ($q === ''): ?>
<div class="text-center py-5 text-muted">
    <i class="bi bi-person-search fs-1 d-block mb-3"></i>
    Enter a name, phone number, QID or email to search for a tenant.
</div>
<?php endif; ?>

<?= $this->endSection() ?>
