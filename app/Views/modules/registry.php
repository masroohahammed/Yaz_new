<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div>
    <h1><i class="bi <?= esc($icon ?? 'bi-table') ?> me-2 text-primary"></i><?= esc($title) ?></h1>
  </div>
  <?php if (empty($tableMissing)): ?>
  <a href="<?= base_url($slug . '/create') ?>" class="btn btn-fm-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i>Add New
  </a>
  <?php endif; ?>
</div>

<?php if (! empty($tableMissing)): ?>
<div class="alert alert-warning">
  Database table not installed. Run <code>database/pm_upgrade_existing_fm.sql</code> or full clean install.
</div>
<?php else: ?>

<div class="fm-card mb-4 p-3">
  <form method="get" class="row g-2 align-items-end">
    <div class="col-sm-8 col-md-6">
      <input type="search" name="search" class="form-control form-control-sm"
             placeholder="Search…" value="<?= esc($filters['search'] ?? '') ?>">
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
      <a href="<?= base_url($slug) ?>" class="btn btn-sm btn-outline-secondary ms-1">Reset</a>
    </div>
  </form>
</div>

<div class="fm-card p-0">
  <div class="table-responsive">
    <table class="table table-registry table-hover align-middle mb-0">
      <thead class="table-light">
        <tr>
          <?php foreach ($columns as $col): ?>
            <th><?= esc($col['label']) ?></th>
          <?php endforeach; ?>
          <th class="text-end">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="<?= count($columns) + 1 ?>" class="text-center text-muted py-4">No records yet.</td></tr>
        <?php else: ?>
          <?php foreach ($rows as $row): ?>
            <tr>
              <?php foreach ($columns as $col): ?>
                <td><?= esc($row[$col['key']] ?? '') ?></td>
              <?php endforeach; ?>
              <td class="text-end">
                <a href="<?= base_url($slug . '/view/' . $row['id']) ?>" class="btn btn-sm btn-outline-primary">View</a>
                <a href="<?= base_url($slug . '/edit/' . $row['id']) ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php if (! empty($filters['total']) && ($filters['total'] ?? 0) > ($filters['perPage'] ?? 25)): ?>
<p class="text-muted small mt-2">
  Page <?= (int) ($filters['page'] ?? 1) ?> —
  <?= (int) $filters['total'] ?> total records
</p>
<?php endif; ?>

<?php endif; ?>

<?= $this->endSection() ?>
