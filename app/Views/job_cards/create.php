<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-card-checklist me-2 text-primary"></i>Create Job Card</h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= base_url('workorders/view/'.$wo['id']) ?>"><?= esc($wo['wo_number']) ?></a></li><li class="breadcrumb-item active">Create Job Card</li></ol></nav>
  </div>
  <a href="<?= base_url('workorders/view/'.$wo['id']) ?>" class="btn btn-fm-outline btn-sm">← Back</a>
</div>

<!-- WO Summary Banner -->
<div class="alert alert-light border d-flex align-items-center gap-3 mb-4">
    <div>
        <span class="badge-status badge-<?= $wo['priority'] ?> me-2"><?= ucfirst($wo['priority']) ?></span>
        <strong><?= esc($wo['wo_number']) ?></strong> — <?= esc($wo['title']) ?>
    </div>
    <div class="ms-auto text-muted small"><?= esc($wo['facility_name']) ?></div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="fm-card p-4">
            <form action="/job-cards/<?= $wo['id'] ?>" method="post">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label fw-medium">Assign Technician <span class="text-danger">*</span></label>
                    <select name="assigned_to" class="form-select" required>
                        <option value="">Select technician…</option>
                        <?php foreach ($technicians as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= esc($t['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-medium">Job Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="5" required><?= old('description') ?></textarea>
                    <div class="form-text">Describe the work to be carried out in detail.</div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label fw-medium">Scheduled Date</label>
                        <input type="date" name="scheduled_date" class="form-control" value="<?= old('scheduled_date') ?>">
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label fw-medium">Estimated Hours</label>
                        <input type="number" name="scheduled_hours" class="form-control" step="0.5" min="0.5" value="<?= old('scheduled_hours') ?>">
                    </div>
                </div>

                <div class="d-flex gap-2 pt-2">
                    <button type="submit" class="btn btn-primary-brand">Create Job Card</button>
                    <a href="/work-orders/<?= $wo['id'] ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
