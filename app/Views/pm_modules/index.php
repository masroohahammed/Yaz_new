<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div>
    <h1><i class="bi <?= esc($module['icon'] ?? 'bi-table') ?> me-2"></i><?= esc($module['title']) ?></h1>
    <p class="text-muted small mb-0"><?= (int) $total ?> records</p>
  </div>
  <a href="<?= base_url('pm/' . $slug . '/create') ?>" class="btn btn-fm-primary btn-sm">Add</a>
</div>
<form method="get" class="fm-card mb-3"><div class="fm-card-body py-2 d-flex gap-2">
  <input type="search" name="search" class="form-control form-control-sm" value="<?= esc($search) ?>" placeholder="Search">
  <button class="btn btn-sm btn-fm-outline" type="submit">Filter</button>
</div></form>
<div class="fm-card table-responsive">
  <table class="fm-table table-sm mb-0">
    <thead><tr>
      <?php foreach ($module['columns'] as $col): ?><th><?= esc($col['label']) ?></th><?php endforeach; ?>
      <th></th>
    </tr></thead>
    <tbody>
    <?php foreach ($rows as $row): ?>
      <tr>
        <?php foreach ($module['columns'] as $col): ?>
        <td><?= esc($row[$col['key']] ?? '') ?></td>
        <?php endforeach; ?>
        <td class="text-end text-nowrap">
          <a href="<?= base_url('pm/' . $slug . '/' . (int) $row['id']) ?>" class="btn btn-sm btn-fm-outline">View</a>
          <a href="<?= base_url('pm/' . $slug . '/' . (int) $row['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($rows)): ?><tr><td colspan="<?= count($module['columns']) + 1 ?>" class="text-muted text-center py-4">No records.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?= $this->endSection() ?>
