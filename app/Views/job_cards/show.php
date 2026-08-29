<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php $role = session('user_role'); $uid = session('user_id'); ?>

<div class="d-flex flex-wrap align-items-center gap-2 mb-4">
    <a href="/work-orders/<?= $jc['wo_id'] ?>" class="text-muted text-decoration-none small"><i class="bi bi-arrow-left"></i> <?= esc($jc['wo_number']) ?></a>
    <span class="text-muted">/</span>
    <h2 class="fw-semibold mb-0">Job Card <?= esc($jc['jc_number']) ?></h2>
    <span class="badge-status badge-<?= $jc['status'] ?> ms-2"><?= ucfirst($jc['status']) ?></span>
    <div class="ms-auto d-flex gap-2">
        <a href="/job-cards/<?= $jc['id'] ?>/print"
           target="_blank"
           class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-printer me-1"></i> Print
        </a>
        <a href="/job-cards/<?= $jc['id'] ?>/print?pdf=1"
           class="btn btn-sm btn-outline-danger">
            <i class="bi bi-file-earmark-pdf me-1"></i> PDF
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">

        <!-- Details -->
        <div class="fm-card mb-4">
            <div class="fm-card-header"><span class="fw-semibold">Job Card Details</span></div>
            <div class="p-3">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted small">Work Order</dt>
                    <dd class="col-sm-8"><a href="/work-orders/<?= $jc['wo_id'] ?>"><?= esc($jc['wo_number']) ?> — <?= esc($jc['wo_title']) ?></a></dd>
                    <dt class="col-sm-4 text-muted small">Facility</dt>
                    <dd class="col-sm-8"><?= esc($jc['facility_name']) ?></dd>
                    <dt class="col-sm-4 text-muted small">Supervisor</dt>
                    <dd class="col-sm-8"><?= esc($jc['supervisor_name'] ?? '—') ?></dd>
                    <dt class="col-sm-4 text-muted small">Technician</dt>
                    <dd class="col-sm-8"><?= esc($jc['technician_name'] ?? '—') ?></dd>
                    <dt class="col-sm-4 text-muted small">Scheduled</dt>
                    <dd class="col-sm-8">
                        <?= $jc['scheduled_date'] ? date('d M Y', strtotime($jc['scheduled_date'])) : '—' ?>
                        <?= $jc['scheduled_hours'] ? ' (' . $jc['scheduled_hours'] . ' hrs)' : '' ?>
                    </dd>
                    <dt class="col-sm-4 text-muted small">Description</dt>
                    <dd class="col-sm-8"><?= nl2br(esc($jc['description'])) ?></dd>
                    <?php if ($jc['technician_notes']): ?>
                        <dt class="col-sm-4 text-muted small">Technician Notes</dt>
                        <dd class="col-sm-8"><?= nl2br(esc($jc['technician_notes'])) ?></dd>
                    <?php endif; ?>
                    <?php if ($jc['completion_notes']): ?>
                        <dt class="col-sm-4 text-muted small">Completion Notes</dt>
                        <dd class="col-sm-8"><?= nl2br(esc($jc['completion_notes'])) ?></dd>
                    <?php endif; ?>
                    <?php if ($jc['labor_hours']): ?>
                        <dt class="col-sm-4 text-muted small">Actual Hours</dt>
                        <dd class="col-sm-8"><?= $jc['labor_hours'] ?> hrs</dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <!-- Before/After Images -->
        <?php if ($jc['before_image'] || $jc['after_image']): ?>
            <div class="fm-card mb-4">
                <div class="fm-card-header"><span class="fw-semibold">Before / After</span></div>
                <div class="p-3 row g-3">
                    <?php if ($jc['before_image']): ?>
                        <div class="col-sm-6">
                            <p class="small text-muted mb-1">Before</p>
                            <img src="<?= base_url($jc['before_image']) ?>" class="img-fluid rounded border" alt="Before">
                        </div>
                    <?php endif; ?>
                    <?php if ($jc['after_image']): ?>
                        <div class="col-sm-6">
                            <p class="small text-muted mb-1">After</p>
                            <img src="<?= base_url($jc['after_image']) ?>" class="img-fluid rounded border" alt="After">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Materials Used -->
        <?php if (! empty($materials)): ?>
            <div class="fm-card mb-4">
                <div class="fm-card-header"><span class="fw-semibold">Materials Used</span></div>
                <div class="table-responsive">
                    <table class="table table-fm mb-0">
                        <thead><tr><th>Item</th><th>Qty</th><th>Unit Cost</th><th>Total</th></tr></thead>
                        <tbody>
                            <?php foreach ($materials as $m): ?>
                                <tr>
                                    <td><?= esc($m['item_name']) ?></td>
                                    <td><?= $m['quantity'] ?></td>
                                    <td><?= number_format($m['unit_cost'], 2) ?> QAR</td>
                                    <td><?= number_format($m['total_cost'], 2) ?> QAR</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <div class="col-lg-4">
        <div class="fm-card mb-4">
            <div class="fm-card-header"><span class="fw-semibold">Actions</span></div>
            <div class="p-3 d-grid gap-3">

                <!-- Start Work (Stage 9) -->
                <?php if ($jc['status'] === 'draft' && (in_array($role,['super_admin','facility_manager','supervisor']) || (int)$uid === (int)$jc['assigned_to'])): ?>
                    <form action="/job-cards/<?= $jc['id'] ?>/start" method="post">
                        <?= csrf_field() ?>
                        <button class="btn btn-sm btn-warning w-100"><i class="bi bi-play-fill me-1"></i> Start Work Execution</button>
                    </form>
                <?php endif; ?>

                <!-- Complete Job Card (Stage 11) -->
                <?php if ($jc['status'] === 'in_progress' && (in_array($role,['super_admin','facility_manager','supervisor']) || (int)$uid === (int)$jc['assigned_to'])): ?>
                    <button class="btn btn-sm btn-success w-100" data-bs-toggle="modal" data-bs-target="#completeModal">
                        <i class="bi bi-check2-all me-1"></i> Complete Job Card
                    </button>
                <?php endif; ?>

                <!-- Supervisor Approval -->
                <?php if ($jc['status'] === 'completed' && in_array($role,['super_admin','facility_manager','supervisor']) && (int)$uid === (int)$jc['supervisor_id']): ?>
                    <form action="/job-cards/<?= $jc['id'] ?>/approve" method="post">
                        <?= csrf_field() ?>
                        <button class="btn btn-sm btn-primary-brand w-100">
                            <i class="bi bi-patch-check me-1"></i> Approve Job Card
                        </button>
                    </form>
                <?php endif; ?>

                <?php if ($jc['status'] === 'approved'): ?>
                    <div class="text-center py-2">
                        <i class="bi bi-check-circle-fill text-success fs-3"></i>
                        <p class="small text-muted mt-1 mb-0">Approved by Supervisor</p>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<!-- Complete Modal -->
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Complete Job Card</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= base_url('job-cards/' . $jc['id'] . '/complete') ?>" method="post" enctype="multipart/form-data" class="fm-submit-form" data-loader-msg="Completing job card…">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <label class="form-label small fw-medium">Actual Hours <span class="text-danger">*</span></label>
                            <input type="number" name="labor_hours" class="form-control form-control-sm" step="0.5" min="0.5" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-medium">Completion Notes <span class="text-danger">*</span></label>
                            <textarea name="completion_notes" class="form-control form-control-sm" rows="3" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-medium">Technician Notes</label>
                            <textarea name="technician_notes" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-medium">Before Image</label>
                            <input type="file" name="before_image" class="form-control form-control-sm" accept="image/*">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-medium">After Image</label>
                            <input type="file" name="after_image" class="form-control form-control-sm" accept="image/*">
                        </div>
                        <!-- Materials -->
                        <div class="col-12">
                            <label class="form-label small fw-medium">Materials Used</label>
                            <div id="materialsContainer">
                                <div class="row g-2 mb-2 material-row">
                                    <div class="col-5"><input type="text"   name="materials[0][item_name]" class="form-control form-control-sm" placeholder="Item name"></div>
                                    <div class="col-2"><input type="number" name="materials[0][quantity]"  class="form-control form-control-sm" placeholder="Qty" min="1" step="0.1"></div>
                                    <div class="col-3"><input type="number" name="materials[0][unit_cost]" class="form-control form-control-sm" placeholder="Unit cost" min="0" step="0.01"></div>
                                    <div class="col-2"><button type="button" class="btn btn-sm btn-outline-danger w-100 remove-row">✕</button></div>
                                </div>
                            </div>
                            <button type="button" id="addMaterial" class="btn btn-sm btn-outline-secondary mt-1">+ Add Material</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fm-submit-btn"><i class="bi bi-check2-circle me-1"></i>Submit Completion</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
let matIdx = 1;
document.getElementById('addMaterial')?.addEventListener('click', () => {
    const tmpl = `<div class="row g-2 mb-2 material-row">
        <div class="col-5"><input type="text"   name="materials[${matIdx}][item_name]" class="form-control form-control-sm" placeholder="Item name"></div>
        <div class="col-2"><input type="number" name="materials[${matIdx}][quantity]"  class="form-control form-control-sm" placeholder="Qty" min="1" step="0.1"></div>
        <div class="col-3"><input type="number" name="materials[${matIdx}][unit_cost]" class="form-control form-control-sm" placeholder="Unit cost" min="0" step="0.01"></div>
        <div class="col-2"><button type="button" class="btn btn-sm btn-outline-danger w-100 remove-row">✕</button></div>
    </div>`;
    document.getElementById('materialsContainer').insertAdjacentHTML('beforeend', tmpl);
    matIdx++;
});
document.getElementById('materialsContainer')?.addEventListener('click', e => {
    if (e.target.classList.contains('remove-row')) e.target.closest('.material-row').remove();
});
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
