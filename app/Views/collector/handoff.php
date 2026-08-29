<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="d-flex align-items-center justify-content-between mb-3">
    <h4 class="mb-0 fw-semibold"><i class="bi bi-bag-check me-2 text-info"></i>Cash Handoff</h4>
    <a href="<?= base_url('collector') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Dashboard</a>
</div>

<?php $userRole = session()->get('user_role'); ?>
<?php $isManager = in_array($userRole, ['super_admin', 'property_manager', 'finance_manager']); ?>

<?php if (empty($handoffs)): ?>
<div class="card border-0 shadow-sm" style="border-radius:12px">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-bag-x fs-1 d-block mb-2"></i>
        No handoff records found.
    </div>
</div>
<?php else: ?>
<div class="card border-0 shadow-sm" style="border-radius:12px">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Session</th>
                    <th>Collector</th>
                    <th>Amount</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Acknowledged By</th>
                    <th>Notes</th>
                    <?php if ($isManager): ?><th>Action</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($handoffs as $h): ?>
                <tr>
                    <td class="fw-medium"><?= esc($h['session_code'] ?? '—') ?></td>
                    <td><?= esc($h['collector_name'] ?? '—') ?></td>
                    <td class="fw-semibold"><?= esc($currency) ?> <?= number_format((float)$h['amount'], 2) ?></td>
                    <td><?= $h['created_at'] ? date('d M Y H:i', strtotime($h['created_at'])) : '—' ?></td>
                    <td>
                        <?php if ($h['status'] === 'acknowledged'): ?>
                            <span class="badge bg-success"><i class="bi bi-check2 me-1"></i>Acknowledged</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Pending</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($h['acknowledged_by_name'] ?? ''): ?>
                            <?= esc($h['acknowledged_by_name']) ?>
                            <?php if ($h['acknowledged_at']): ?>
                                <div class="text-muted" style="font-size:.72rem"><?= date('d M Y H:i', strtotime($h['acknowledged_at'])) ?></div>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-muted small"><?= esc($h['notes'] ?? '') ?></td>
                    <?php if ($isManager): ?>
                    <td>
                        <?php if ($h['status'] === 'pending'): ?>
                        <form method="POST" action="<?= base_url('collector/handoff/' . $h['id'] . '/ack') ?>"
                              onsubmit="return confirm('Acknowledge this cash handoff of <?= esc($currency) ?> <?= number_format((float)$h['amount'], 2) ?>?')">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-success btn-sm px-3">
                                <i class="bi bi-check-circle me-1"></i>Acknowledge
                            </button>
                        </form>
                        <?php else: ?>
                            <span class="text-muted small">Done</span>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Bulk acknowledgment note for managers -->
<?php if ($isManager): ?>
<div class="alert alert-info mt-4 d-flex align-items-start gap-2">
    <i class="bi bi-info-circle-fill mt-1"></i>
    <div>
        <strong>Finance Manager Note:</strong> Acknowledging a handoff confirms you have received the cash from the collector.
        This updates the handoff status and timestamps the confirmation.
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
