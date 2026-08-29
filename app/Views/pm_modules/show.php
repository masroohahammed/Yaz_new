<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between">
  <h1><?= esc($module['title']) ?></h1>
  <div>
    <a href="<?= base_url('pm/' . $slug . '/' . (int) $row['id'] . '/edit') ?>" class="btn btn-sm btn-fm-primary">Edit</a>
    <a href="<?= base_url('pm/' . $slug) ?>" class="btn btn-sm btn-fm-outline">Back</a>
  </div>
</div>
<div class="fm-form-section">
  <div class="row g-3 small">
    <?php foreach ($module['columns'] as $col): ?>
    <div class="col-md-4">
      <span class="text-muted d-block"><?= esc($col['label']) ?></span>
      <div class="fw-semibold"><?= esc($row[$col['key']] ?? '—') ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?= $this->endSection() ?>
