<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <h4 class="mb-0 fw-semibold"><i class="bi bi-person-badge me-2 text-danger"></i>Collector Dashboard</h4>
        <div class="text-muted small"><?= date('l, d F Y') ?></div>
    </div>
    <?php if ($openSession): ?>
        <span class="badge bg-success fs-6 px-3 py-2">
            <i class="bi bi-record-circle me-1"></i>Session: <?= esc($openSession['session_code']) ?>
        </span>
    <?php else: ?>
        <a href="<?= base_url('collector/session') ?>" class="btn btn-warning btn-lg px-4">
            <i class="bi bi-play-circle me-2"></i>Start Session
        </a>
    <?php endif; ?>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3 px-2 h-100" style="border-left:4px solid var(--primary)!important;border-radius:12px">
            <div class="fs-1 fw-bold text-danger"><?= $todayCount ?></div>
            <div class="text-muted small">Collections Today</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3 px-2 h-100" style="border-left:4px solid #198754!important;border-radius:12px">
            <div class="fs-1 fw-bold text-success"><?= esc($currency) ?> <?= number_format($todaySum, 2) ?></div>
            <div class="text-muted small">Cash Collected Today</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3 px-2 h-100" style="border-left:4px solid #ffc107!important;border-radius:12px">
            <div class="fs-1 fw-bold text-warning"><?= $pendingCount ?></div>
            <div class="text-muted small">Pending Assignments</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3 px-2 h-100" style="border-left:4px solid #0dcaf0!important;border-radius:12px">
            <div class="fs-1 fw-bold text-info"><?= $pendingHandoffs ?></div>
            <div class="text-muted small">Pending Handoffs</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card border-0 shadow-sm mb-4" style="border-radius:12px">
    <div class="card-header bg-transparent fw-semibold border-bottom py-3">
        <i class="bi bi-lightning-charge me-2 text-warning"></i>Quick Actions
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <a href="<?= base_url('collector/search') ?>" class="btn btn-outline-danger w-100 py-3 d-flex flex-column align-items-center gap-2" style="border-radius:12px;font-size:0.9rem">
                    <i class="bi bi-cash-coin fs-2"></i>
                    Collect Payment
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="<?= base_url('collector/assignments') ?>" class="btn btn-outline-warning w-100 py-3 d-flex flex-column align-items-center gap-2" style="border-radius:12px;font-size:0.9rem">
                    <i class="bi bi-list-task fs-2"></i>
                    My Assignments
                    <?php if ($pendingCount > 0): ?>
                        <span class="badge bg-warning text-dark"><?= $pendingCount ?></span>
                    <?php endif; ?>
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="<?= base_url('collector/history') ?>" class="btn btn-outline-secondary w-100 py-3 d-flex flex-column align-items-center gap-2" style="border-radius:12px;font-size:0.9rem">
                    <i class="bi bi-clock-history fs-2"></i>
                    History
                </a>
            </div>
            <div class="col-6 col-md-3">
                <a href="<?= base_url('collector/report') ?>" class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center gap-2" style="border-radius:12px;font-size:0.9rem">
                    <i class="bi bi-printer fs-2"></i>
                    Daily Report
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Session Status -->
<div class="card border-0 shadow-sm" style="border-radius:12px">
    <div class="card-header bg-transparent fw-semibold border-bottom py-3">
        <i class="bi bi-calendar-check me-2 text-success"></i>Session Status
    </div>
    <div class="card-body">
        <?php if ($openSession): ?>
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <div class="fw-bold fs-5 text-success"><?= esc($openSession['session_code']) ?> &mdash; Open</div>
                    <div class="text-muted small">Started: <?= date('d M Y H:i', strtotime($openSession['started_at'])) ?></div>
                    <div class="text-muted small">Opening Float: <?= esc($currency) ?> <?= number_format((float)$openSession['opening_float'], 2) ?></div>
                </div>
                <a href="<?= base_url('collector/session') ?>" class="btn btn-outline-danger px-4 py-2">
                    <i class="bi bi-stop-circle me-2"></i>Close Session
                </a>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <i class="bi bi-calendar-x fs-1 text-muted mb-2 d-block"></i>
                <div class="text-muted mb-3">No active session. Start a session to begin collecting.</div>
                <a href="<?= base_url('collector/session') ?>" class="btn btn-success btn-lg px-5">
                    <i class="bi bi-play-circle me-2"></i>Start Session
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
