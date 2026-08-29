<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$role = session('user_role');

// Stage progress
$stage = 1;
if ($complaint['verified_at'])                                   $stage = 2;
if (($complaint['approval_status'] ?? 'pending') === 'approved')                $stage = 3;
if ($complaint['status'] === 'converted')                        $stage = 4;
?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-headset me-2 text-primary"></i><?= esc($complaint['ticket_number']) ?></h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= base_url('helpdesk') ?>">Helpdesk</a></li><li class="breadcrumb-item active"><?= esc($complaint['ticket_number']) ?></li></ol></nav>
  </div>
</div>

<!-- Mini Stepper (stages 1–4) -->
<div class="fm-card mb-4 p-3">
    <div class="workflow-stepper">
        <?php $steps = ['Complaint Received','Complaint Verification','Approval Process','Convert to Work Order']; ?>
        <?php foreach ($steps as $i => $lbl): ?>
            <?php $state = ($i+1) < $stage ? 'done' : (($i+1) === $stage ? 'active' : 'pending'); ?>
            <div class="workflow-step <?= $state ?>">
                <?= $i+1 ?>. <?= $lbl ?>
                <?php if ($state === 'done'): ?><i class="bi bi-check-lg ms-1"></i><?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="fm-card mb-4">
            <div class="fm-card-header"><span class="fw-semibold">Complaint Details</span></div>
            <div class="p-3">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted small">Requester</dt>
                    <dd class="col-sm-8"><?= esc($complaint['requester_name']) ?></dd>

                    <dt class="col-sm-4 text-muted small">Contact</dt>
                    <dd class="col-sm-8">
                        <?= esc($complaint['requester_phone'] ?? '—') ?>
                        <?php if ($complaint['requester_email']): ?> / <?= esc($complaint['requester_email']) ?><?php endif; ?>
                    </dd>

                    <dt class="col-sm-4 text-muted small">Facility</dt>
                    <dd class="col-sm-8"><?= esc($complaint['facility_name']) ?></dd>

                    <dt class="col-sm-4 text-muted small">Category</dt>
                    <dd class="col-sm-8"><?= esc($complaint['category']) ?></dd>

                    <dt class="col-sm-4 text-muted small">Priority</dt>
                    <dd class="col-sm-8"><span class="badge-status badge-<?= $complaint['priority'] ?>"><?= ucfirst($complaint['priority']) ?></span></dd>

                    <dt class="col-sm-4 text-muted small">Description</dt>
                    <dd class="col-sm-8"><?= nl2br(esc($complaint['description'])) ?></dd>

                    <?php if ($complaint['image_path']): ?>
                        <dt class="col-sm-4 text-muted small">Attachment</dt>
                        <dd class="col-sm-8">
                            <a href="<?= base_url($complaint['image_path']) ?>" target="_blank">
                                <img src="<?= base_url($complaint['image_path']) ?>" class="img-thumbnail" style="max-width:200px">
                            </a>
                        </dd>
                    <?php endif; ?>

                    <?php if ($complaint['verification_notes']): ?>
                        <dt class="col-sm-4 text-muted small">Verification Notes</dt>
                        <dd class="col-sm-8"><?= nl2br(esc($complaint['verification_notes'])) ?></dd>
                    <?php endif; ?>

                    <?php if ($complaint['rejection_reason']): ?>
                        <dt class="col-sm-4 text-muted small">Rejection Reason</dt>
                        <dd class="col-sm-8 text-danger"><?= nl2br(esc($complaint['rejection_reason'])) ?></dd>
                    <?php endif; ?>

                    <?php if ($complaint['converted_wo_number']): ?>
                        <dt class="col-sm-4 text-muted small">Work Order</dt>
                        <dd class="col-sm-8">
                            <a href="/work-orders/<?= $complaint['converted_to_wo'] ?>" class="fw-medium">
                                <?= esc($complaint['converted_wo_number']) ?> <i class="bi bi-arrow-up-right-circle"></i>
                            </a>
                        </dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="fm-card mb-4">
            <div class="fm-card-header"><span class="fw-semibold">Actions</span></div>
            <div class="p-3 d-grid gap-3">

                <!-- Stage 2: Verify -->
                <?php if (! $complaint['verified_at'] && $complaint['status'] === 'pending' && in_array($role, ['super_admin','facility_manager','supervisor'])): ?>
                    <form action="/helpdesk/<?= $complaint['id'] ?>/verify" method="post" class="fm-submit-form" data-loader-msg="Verifying…">
                        <?= csrf_field() ?>
                        <textarea name="verification_notes" class="form-control form-control-sm mb-2" rows="2" placeholder="Verification notes…"></textarea>
                        <button class="btn btn-sm btn-warning w-100"><i class="bi bi-search me-1"></i> Verify Complaint</button>
                    </form>
                <?php endif; ?>

                <!-- Stage 3: Approve -->
                <?php if ($complaint['verified_at'] && ($complaint['approval_status'] ?? 'pending') === 'pending' && in_array($role, ['super_admin','facility_manager'])): ?>
                    <form action="/helpdesk/<?= $complaint['id'] ?>/approve" method="post">
                        <?= csrf_field() ?>
                        <textarea name="rejection_reason" class="form-control form-control-sm mb-2" rows="2" placeholder="Rejection reason (if rejecting)…"></textarea>
                        <div class="d-flex gap-2">
                            <button name="action" value="approve" class="btn btn-sm btn-success flex-fill">✓ Approve</button>
                            <button name="action" value="reject"  class="btn btn-sm btn-danger  flex-fill">✗ Reject</button>
                        </div>
                    </form>
                <?php endif; ?>

                <!-- Stage 4: Convert to WO -->
                <?php if (($complaint['approval_status'] ?? 'pending') === 'approved' && $complaint['status'] !== 'converted' && in_array($role, ['super_admin','facility_manager','supervisor'])): ?>
                    <form action="/helpdesk/<?= $complaint['id'] ?>/convert" method="post">
                        <?= csrf_field() ?>
                        <div class="mb-2">
                            <label class="form-label small fw-medium">WO Title</label>
                            <input type="text" name="title" class="form-control form-control-sm"
                                   value="<?= esc($complaint['category']) ?> — <?= esc(substr($complaint['description'], 0, 60)) ?>" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-medium">WO Type</label>
                            <select name="type" class="form-select form-select-sm">
                                <option value="corrective" selected>Corrective</option>
                                <option value="emergency">Emergency</option>
                                <option value="preventive">Preventive</option>
                                <option value="breakdown">Breakdown</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-medium">Assign Supervisor</label>
                            <select name="supervisor_id" class="form-select form-select-sm">
                                <option value="">Later…</option>
                                <?php foreach ($supervisors as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= esc($s['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button class="btn btn-sm btn-primary-brand w-100 mt-1">
                            <i class="bi bi-file-earmark-plus me-1"></i> Convert to Work Order
                        </button>
                    </form>
                <?php endif; ?>

                <?php if ($complaint['status'] === 'converted'): ?>
                    <div class="text-center py-2">
                        <i class="bi bi-check-circle-fill text-success fs-3"></i>
                        <p class="small text-muted mt-1 mb-0">Converted to Work Order</p>
                    </div>
                <?php endif; ?>

                <?php if ($complaint['status'] === 'rejected'): ?>
                    <div class="text-center py-2">
                        <i class="bi bi-x-circle-fill text-danger fs-3"></i>
                        <p class="small text-muted mt-1 mb-0">Complaint Rejected</p>
                    </div>
                <?php endif; ?>

            </div>
        </div>

        <!-- Timeline -->
        <div class="fm-card">
            <div class="fm-card-header"><span class="fw-semibold small">Timeline</span></div>
            <ul class="list-group list-group-flush small">
                <li class="list-group-item py-2">
                    <div class="fw-medium">Submitted</div>
                    <div class="text-muted"><?= date('d M Y H:i', strtotime($complaint['created_at'])) ?></div>
                </li>
                <?php if ($complaint['verified_at']): ?>
                    <li class="list-group-item py-2">
                        <div class="fw-medium">Verified by <?= esc($complaint['verified_by_name']) ?></div>
                        <div class="text-muted"><?= date('d M Y H:i', strtotime($complaint['verified_at'])) ?></div>
                    </li>
                <?php endif; ?>
                <?php if ($complaint['approved_at']): ?>
                    <li class="list-group-item py-2">
                        <div class="fw-medium"><?= ucfirst($complaint['approval_status'] ?? 'pending') ?> by <?= esc($complaint['approved_by_name']) ?></div>
                        <div class="text-muted"><?= date('d M Y H:i', strtotime($complaint['approved_at'])) ?></div>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
