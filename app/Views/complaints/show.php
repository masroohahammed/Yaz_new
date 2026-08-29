<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header d-flex justify-content-between align-items-center">
  <h1><i class="bi bi-exclamation-circle me-2"></i><?= esc($title) ?></h1>
  <div>
    <a href="<?= base_url('complaints/edit/' . $row['id']) ?>" class="btn btn-sm btn-fm-primary">Edit</a>
    <a href="<?= base_url('complaints') ?>" class="btn btn-sm btn-outline-secondary">Back</a>
  </div>
</div>

<div class="fm-card p-4">
  <dl class="row small">
    <dt class="col-sm-3 text-muted">Ticket</dt><dd class="col-sm-9"><?= esc($row['ticket_number'] ?? '') ?></dd>
    <dt class="col-sm-3 text-muted">Priority</dt><dd class="col-sm-9"><?= esc($row['priority'] ?? '') ?></dd>
    <dt class="col-sm-3 text-muted">Status</dt><dd class="col-sm-9"><?= esc($row['status'] ?? '') ?></dd>
    <dt class="col-sm-3 text-muted">Subject</dt><dd class="col-sm-9"><?= esc($row['category'] ?? '') ?></dd>
    <dt class="col-sm-3 text-muted">Description</dt><dd class="col-sm-9"><?= esc($row['description'] ?? '') ?></dd>
    <dt class="col-sm-3 text-muted">Created</dt><dd class="col-sm-9"><?= esc($row['created_at'] ?? '') ?></dd>
  </dl>
</div>

<?= $this->endSection() ?>
