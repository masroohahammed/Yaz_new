<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php
$role           = session('user_role');
$currentUserId  = session('user_id');
$stageKeys      = array_keys($stageFlow);
$currentStageIdx= array_search($wo['workflow_stage'], $stageKeys);
?>

<!-- Header -->
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <a href="/work-orders" class="text-muted text-decoration-none small"><i class="bi bi-arrow-left"></i> Work Orders</a>
        </div>
        <h2 class="fw-semibold mb-0"><?= esc($wo['wo_number']) ?></h2>
        <p class="text-muted mb-0"><?= esc($wo['title']) ?></p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <span class="badge-status badge-<?= $wo['priority'] ?> fs-6"><?= ucfirst($wo['priority']) ?></span>
        <span class="badge-status badge-<?= $wo['status'] ?> fs-6"><?= ucwords(str_replace('_',' ',$wo['status'])) ?></span>
    </div>
</div>

<!-- Workflow Stepper -->
<div class="fm-card mb-4 p-3">
    <div class="workflow-stepper">
        <?php foreach ($stageFlow as $key => $step): ?>
            <?php
            $idx   = array_search($key, $stageKeys);
            $state = $idx < $currentStageIdx ? 'done' : ($idx === $currentStageIdx ? 'active' : 'pending');
            ?>
            <div class="workflow-step <?= $state ?>">
                <i class="bi <?= $step['icon'] ?>"></i>
                <span><?= $step['label'] ?></span>
                <?php if ($state === 'done'): ?><i class="bi bi-check-lg ms-1"></i><?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="row g-4">
    <!-- Left column: details + actions -->
    <div class="col-lg-8">

        <!-- Details Card -->
        <div class="fm-card mb-4">
            <div class="fm-card-header d-flex justify-content-between">
                <span class="fw-semibold">Work Order Details</span>
                <?php if (in_array($role,['super_admin','facility_manager'])): ?>
                    <a href="/work-orders/<?= $wo['id'] ?>/edit" class="btn btn-sm btn-outline-secondary">Edit</a>
                <?php endif; ?>
            </div>
            <div class="p-3">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted small">Facility</dt>
                    <dd class="col-sm-8"><?= esc($wo['facility_name']) ?> <span class="text-muted small">(<?= esc($wo['facility_code']) ?>)</span></dd>

                    <dt class="col-sm-4 text-muted small">Type</dt>
                    <dd class="col-sm-8"><?= ucfirst($wo['type']) ?></dd>

                    <dt class="col-sm-4 text-muted small">Category</dt>
                    <dd class="col-sm-8"><?= ucfirst($wo['category'] ?? '—') ?></dd>

                    <dt class="col-sm-4 text-muted small">Asset</dt>
                    <dd class="col-sm-8"><?= $wo['asset_name'] ? esc($wo['asset_name']) . ' (' . esc($wo['asset_code']) . ')' : '—' ?></dd>

                    <dt class="col-sm-4 text-muted small">Supervisor</dt>
                    <dd class="col-sm-8"><?= esc($wo['supervisor_name'] ?? '—') ?></dd>

                    <dt class="col-sm-4 text-muted small">Technician</dt>
                    <dd class="col-sm-8"><?= esc($wo['assigned_name'] ?? '—') ?></dd>

                    <dt class="col-sm-4 text-muted small">SLA Due</dt>
                    <dd class="col-sm-8 <?= ($wo['sla_breached'] ?? false) ? 'text-danger fw-semibold' : '' ?>">
                        <?= $wo['sla_due'] ? date('d M Y H:i', strtotime($wo['sla_due'])) : '—' ?>
                        <?php if ($wo['sla_breached']): ?><span class="badge bg-danger ms-1">Breached</span><?php endif; ?>
                    </dd>

                    <dt class="col-sm-4 text-muted small">Planned Start</dt>
                    <dd class="col-sm-8"><?= $wo['planned_start'] ? date('d M Y H:i', strtotime($wo['planned_start'])) : '—' ?></dd>

                    <dt class="col-sm-4 text-muted small">Planned End</dt>
                    <dd class="col-sm-8"><?= $wo['planned_end'] ? date('d M Y H:i', strtotime($wo['planned_end'])) : '—' ?></dd>

                    <dt class="col-sm-4 text-muted small">Est. Cost</dt>
                    <dd class="col-sm-8"><?= $wo['estimated_cost'] ? number_format($wo['estimated_cost'],2) . ' QAR' : '—' ?></dd>

                    <dt class="col-sm-4 text-muted small">Description</dt>
                    <dd class="col-sm-8"><?= nl2br(esc($wo['description'])) ?></dd>

                    <?php if ($wo['completion_notes']): ?>
                        <dt class="col-sm-4 text-muted small">Completion Notes</dt>
                        <dd class="col-sm-8"><?= nl2br(esc($wo['completion_notes'])) ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <!-- Job Cards -->
        <div class="fm-card mb-4">
            <div class="fm-card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Job Cards</span>
                <?php if (in_array($role, ['super_admin','facility_manager','supervisor'])
                       && in_array($wo['workflow_stage'], ['assigned_to_supervisor','job_card_created','technician_assigned','planning_scheduling'])): ?>
                    <a href="/job-cards/create/<?= $wo['id'] ?>" class="btn btn-sm btn-primary-brand">
                        <i class="bi bi-plus-lg"></i> Create Job Card
                    </a>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table table-fm mb-0">
                    <thead><tr><th>JC #</th><th>Technician</th><th>Status</th><th>Scheduled</th><th>Hours</th><th></th></tr></thead>
                    <tbody>
                        <?php if (empty($jobCards)): ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">No job cards yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($jobCards as $jc): ?>
                                <tr>
                                    <td><a href="/job-cards/<?= $jc['id'] ?>" class="text-decoration-none"><?= esc($jc['jc_number']) ?></a></td>
                                    <td><?= esc($jc['technician_name'] ?? '—') ?></td>
                                    <td><span class="badge-status badge-<?= $jc['status'] ?>"><?= ucfirst($jc['status']) ?></span></td>
                                    <td><?= $jc['scheduled_date'] ? date('d M Y', strtotime($jc['scheduled_date'])) : '—' ?></td>
                                    <td><?= $jc['labor_hours'] ?? '—' ?></td>
                                    <td><a href="/job-cards/<?= $jc['id'] ?>" class="btn btn-sm btn-outline-secondary py-0 px-2">View</a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Comments -->
        <div class="fm-card mb-4" id="comments">
            <div class="fm-card-header"><span class="fw-semibold">Comments</span></div>
            <div class="p-3">
                <?php foreach ($comments as $c): ?>
                    <div class="d-flex gap-2 mb-3">
                        <span class="avatar-circle flex-shrink-0"><?= strtoupper(substr($c['user_name'],0,1)) ?></span>
                        <div>
                            <div class="small fw-semibold"><?= esc($c['user_name']) ?> <span class="text-muted fw-normal"><?= date('d M Y H:i', strtotime($c['created_at'])) ?></span></div>
                            <p class="mb-0 small"><?= nl2br(esc($c['comment'])) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
                <form action="/work-orders/<?= $wo['id'] ?>/comment" method="post" class="d-flex gap-2 mt-3">
                    <?= csrf_field() ?>
                    <input type="text" name="comment" class="form-control form-control-sm" placeholder="Add a comment…" required>
                    <button class="btn btn-sm btn-secondary flex-shrink-0">Post</button>
                </form>
            </div>
        </div>

        <!-- Activity Log -->
        <div class="fm-card">
            <div class="fm-card-header"><span class="fw-semibold">Activity Log</span></div>
            <ul class="list-group list-group-flush">
                <?php if (empty($logs)): ?>
                    <li class="list-group-item text-muted small py-3 text-center">No activity recorded yet.</li>
                <?php else: ?>
                    <?php foreach (array_reverse($logs) as $log): ?>
                        <li class="list-group-item py-2">
                            <div class="small"><?= esc($log['description'] ?: ucfirst(str_replace('_',' ',$log['action']))) ?></div>
                            <div class="text-muted" style="font-size:.72rem"><?= date('d M Y H:i', strtotime($log['created_at'])) ?></div>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>

    </div>

    <!-- Right column: actions panel -->
    <div class="col-lg-4">

        <!-- Stage Actions -->
        <div class="fm-card mb-4">
            <div class="fm-card-header"><span class="fw-semibold">Actions</span></div>
            <div class="p-3 d-grid gap-2">

                <!-- Stage 5: Assign Supervisor -->
                <?php if ($wo['workflow_stage'] === 'converted_to_wo' && in_array($role,['super_admin','facility_manager'])): ?>
                    <form action="/work-orders/<?= $wo['id'] ?>/assign-supervisor" method="post">
                        <?= csrf_field() ?>
                        <select name="supervisor_id" class="form-select form-select-sm mb-2" required>
                            <option value="">Select Supervisor…</option>
                            <?php foreach ($supervisors as $s): ?>
                                <option value="<?= $s['id'] ?>"><?= esc($s['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button class="btn btn-sm btn-primary-brand w-100">Assign to Supervisor</button>
                    </form>
                <?php endif; ?>

                <!-- Stage 8: Schedule -->
                <?php if ($wo['workflow_stage'] === 'technician_assigned' && in_array($role,['super_admin','facility_manager','supervisor'])): ?>
                    <form action="/work-orders/<?= $wo['id'] ?>/schedule" method="post">
                        <?= csrf_field() ?>
                        <div class="mb-2">
                            <label class="form-label small mb-1">Planned Start</label>
                            <input type="datetime-local" name="planned_start" class="form-control form-control-sm" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small mb-1">Planned End</label>
                            <input type="datetime-local" name="planned_end" class="form-control form-control-sm" required>
                        </div>
                        <button class="btn btn-sm btn-primary-brand w-100">Set Schedule</button>
                    </form>
                <?php endif; ?>

                <!-- Stage 10: QC Approve/Reject -->
                <?php if ($wo['workflow_stage'] === 'inspection_qc' && in_array($role,['super_admin','facility_manager'])): ?>
                    <form action="/work-orders/<?= $wo['id'] ?>/qc" method="post">
                        <?= csrf_field() ?>
                        <textarea name="qa_notes" class="form-control form-control-sm mb-2" rows="2" placeholder="QC notes…"></textarea>
                        <div class="d-flex gap-2">
                            <button name="action" value="approve" class="btn btn-sm btn-success flex-fill">✓ Approve QC</button>
                            <button name="action" value="reject"  class="btn btn-sm btn-danger  flex-fill">✗ Reject</button>
                        </div>
                    </form>
                <?php endif; ?>

                <!-- Stage 12: Close WO -->
                <?php if ($wo['workflow_stage'] === 'job_completed' && in_array($role,['super_admin','facility_manager'])): ?>
                    <form action="/work-orders/<?= $wo['id'] ?>/close" method="post"
                          onsubmit="return confirm('Close this work order permanently?')">
                        <?= csrf_field() ?>
                        <button class="btn btn-sm btn-dark w-100"><i class="bi bi-lock me-1"></i> Close Work Order</button>
                    </form>
                <?php endif; ?>

                <?php if ($wo['workflow_stage'] === 'wo_closed'): ?>
                    <div class="text-center py-2">
                        <i class="bi bi-check-circle-fill text-success fs-3"></i>
                        <p class="small text-muted mt-1 mb-0">Work Order Closed</p>
                    </div>
                <?php endif; ?>

            </div>
        </div>

        <!-- Requester Info -->
        <?php if ($wo['requester_name']): ?>
            <div class="fm-card mb-4">
                <div class="fm-card-header"><span class="fw-semibold">Requester</span></div>
                <div class="p-3">
                    <p class="mb-1"><i class="bi bi-person me-2 text-muted"></i><?= esc($wo['requester_name']) ?></p>
                    <?php if ($wo['requester_phone']): ?>
                        <p class="mb-1"><i class="bi bi-telephone me-2 text-muted"></i><?= esc($wo['requester_phone']) ?></p>
                    <?php endif; ?>
                    <?php if ($wo['requester_email']): ?>
                        <p class="mb-0"><i class="bi bi-envelope me-2 text-muted"></i><?= esc($wo['requester_email']) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<?= $this->endSection() ?>
