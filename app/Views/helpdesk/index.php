<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$listUrl    = $listUrl ?? base_url('helpdesk');
$resetUrl   = $resetUrl ?? base_url('helpdesk');
$detailPath = $detailPath ?? 'helpdesk/view/';
$headerIcon = ! empty($readOnly) ? 'bi-tools' : 'bi-headset';
?>

<div class="page-header">
  <div>
    <h1><i class="bi <?= esc($headerIcon) ?> me-2 text-primary"></i><?= esc($pageTitle ?? 'Helpdesk — Complaints') ?></h1>
    <p class="text-muted small mb-0">Total: <?= number_format($total) ?><?php if (!empty($readOnly)): ?> · <span class="badge bg-secondary">Read-only in PM workspace</span><?php endif; ?></p>
  </div>
  <?php if (empty($readOnly)): ?>
  <a href="<?= base_url('helpdesk/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Submit Complaint</a>
  <?php endif; ?>
</div>

<!-- Filters -->
<div class="fm-card mb-4 p-3">
    <form method="get" action="<?= esc($listUrl) ?>" class="row g-2 align-items-end">
        <div class="col-sm-6 col-md-4">
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="Search ticket #, requester…" value="<?= esc($filters['search'] ?? '') ?>">
        </div>
        <div class="col-sm-6 col-md-3">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <?php foreach (['pending'=>'Pending','reviewed'=>'Reviewed','converted'=>'Converted','rejected'=>'Rejected'] as $v=>$l): ?>
                    <option value="<?= $v ?>" <?= ($filters['status'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-6 col-md-2">
            <select name="priority" class="form-select form-select-sm">
                <option value="">All Priorities</option>
                <?php foreach (['critical','high','medium','low'] as $p): ?>
                    <option value="<?= $p ?>" <?= ($filters['priority'] ?? '') === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
            <a href="<?= esc($resetUrl) ?>" class="btn btn-sm btn-outline-secondary ms-1">Reset</a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="fm-card">
    <div class="table-responsive">
        <table class="table table-fm mb-0">
            <thead>
                <tr>
                    <th>Ticket #</th>
                    <th>Requester</th>
                    <th>Category</th>
                    <th>Priority</th>
                    <th>Stage</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                    <tr><td colspan="8" class="text-center py-5 text-muted">No maintenance requests found.</td></tr>
                <?php else: ?>
                    <?php foreach ($requests as $r): ?>
                        <?php $detailUrl = base_url($detailPath . (int) $r['id']); ?>
                        <tr>
                            <td><a href="<?= esc($detailUrl) ?>" class="fw-medium text-decoration-none"><?= esc($r['ticket_number']) ?></a></td>
                            <td><?= esc($r['requester_name']) ?><br><small class="text-muted"><?= esc($r['requester_phone'] ?? '') ?></small></td>
                            <td><?= esc($r['category']) ?></td>
                            <td><span class="badge-status badge-<?= $r['priority'] ?>"><?= ucfirst($r['priority']) ?></span></td>
                            <td>
                                <?php
                                $stage = '';
                                if ($r['status'] === 'pending')   $stage = '1. Received';
                                elseif ($r['verified_at'])        $stage = '2. Verified';
                                elseif (($r['approval_status'] ?? 'pending') === 'approved') $stage = '3. Approved';
                                elseif ($r['status'] === 'converted') $stage = '4. Converted to WO';
                                else $stage = '1. Received';
                                ?>
                                <span class="badge rounded-pill bg-light text-dark border small"><?= $stage ?></span>
                            </td>
                            <td>
                                <?php $sc = ['pending'=>'warning','reviewed'=>'info','converted'=>'success','rejected'=>'danger']; ?>
                                <span class="badge bg-<?= $sc[$r['status']] ?? 'secondary' ?>"><?= ucfirst($r['status']) ?></span>
                            </td>
                            <td class="text-muted small"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                            <td><a href="<?= esc($detailUrl) ?>" class="btn btn-sm btn-outline-secondary py-0 px-2">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
