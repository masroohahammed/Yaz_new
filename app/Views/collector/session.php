<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0 fw-semibold"><i class="bi bi-calendar-check me-2 text-success"></i>My Session</h4>
    <a href="<?= base_url('collector') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Dashboard</a>
</div>

<?php if ($openSession): ?>
<!-- Active Session -->
<div class="card border-0 shadow-sm mb-4" style="border-radius:12px;border-top:4px solid #198754!important">
    <div class="card-header bg-transparent py-3">
        <span class="fw-bold text-success fs-5"><i class="bi bi-record-circle me-2"></i><?= esc($openSession['session_code']) ?> — Active</span>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-4">
                <div class="text-muted small mb-1">Started At</div>
                <div class="fw-semibold"><?= date('d M Y H:i', strtotime($openSession['started_at'])) ?></div>
            </div>
            <div class="col-md-4">
                <div class="text-muted small mb-1">Opening Float</div>
                <div class="fw-semibold"><?= number_format((float)$openSession['opening_float'], 2) ?></div>
            </div>
        </div>

        <hr>
        <h6 class="fw-semibold mb-3 text-danger"><i class="bi bi-stop-circle me-2"></i>Close This Session</h6>
        <form method="POST" action="<?= base_url('collector/session/close') ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-medium">Closing Cash <span class="text-danger">*</span></label>
                    <input type="number" name="closing_cash" class="form-control form-control-lg" step="0.01" min="0" required placeholder="0.00">
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-medium">Notes</label>
                    <input type="text" name="notes" class="form-control form-control-lg" placeholder="Any remarks for this session...">
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-danger btn-lg px-5"
                    onclick="return confirm('Close session and create handoff?')">
                    <i class="bi bi-stop-circle me-2"></i>Close Session & Handoff Cash
                </button>
            </div>
        </form>
    </div>
</div>

<?php else: ?>
<!-- Start Session Form -->
<div class="card border-0 shadow-sm mb-4" style="border-radius:12px;border-top:4px solid #198754!important">
    <div class="card-header bg-transparent py-3 fw-semibold">
        <i class="bi bi-play-circle me-2 text-success"></i>Start a New Session
    </div>
    <div class="card-body">
        <form method="POST" action="<?= base_url('collector/session/start') ?>">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-medium">Opening Float (optional)</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text"><i class="bi bi-currency-dollar"></i></span>
                        <input type="number" name="opening_float" class="form-control" step="0.01" min="0" value="0" placeholder="0.00">
                    </div>
                    <div class="form-text">Cash on hand at start of session</div>
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-success btn-lg px-5">
                    <i class="bi bi-play-circle me-2"></i>Start Session
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Recent Sessions -->
<?php if (!empty($recentSessions)): ?>
<div class="card border-0 shadow-sm" style="border-radius:12px">
    <div class="card-header bg-transparent py-3 fw-semibold">
        <i class="bi bi-clock-history me-2"></i>Recent Sessions
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Code</th>
                    <th>Date</th>
                    <th>Opening Float</th>
                    <th>Closing Cash</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentSessions as $s): ?>
                <tr>
                    <td class="fw-medium"><?= esc($s['session_code']) ?></td>
                    <td><?= $s['started_at'] ? date('d M Y H:i', strtotime($s['started_at'])) : '—' ?></td>
                    <td><?= number_format((float)$s['opening_float'], 2) ?></td>
                    <td><?= $s['closing_cash'] !== null ? number_format((float)$s['closing_cash'], 2) : '—' ?></td>
                    <td>
                        <?php if ($s['status'] === 'open'): ?>
                            <span class="badge bg-success">Open</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Closed</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
