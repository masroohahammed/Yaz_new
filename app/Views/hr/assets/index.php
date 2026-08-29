<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= $this->include('hr/_subnav', ['hrActive' => 'assets']) ?>

<div class="page-header d-flex justify-content-between mb-3">
  <div><h1 class="h4 mb-0"><i class="bi bi-laptop me-2"></i>Employee Assets</h1></div>
  <a href="<?= base_url('hr/assets/create') ?>" class="btn btn-fm-primary btn-sm">Assign asset</a>
</div>

<div class="hr-page-card">
  <table class="fm-table table-sm mb-0">
    <thead><tr><th>Employee</th><th>Type</th><th>Description</th><th>Tag</th><th>Assigned</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($assets as $a): ?>
    <tr>
      <td><?= esc($a['employee_name']) ?></td>
      <td class="small"><?= esc(ucfirst(str_replace('_', ' ', $a['asset_type']))) ?></td>
      <td><?= esc($a['description']) ?></td>
      <td class="small"><?= esc($a['asset_tag'] ?? '—') ?></td>
      <td class="small"><?= esc($a['assigned_date']) ?></td>
      <td><span class="fm-badge badge-status-<?= esc($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
      <td>
        <?php if ($a['status'] === 'assigned'): ?>
        <?= form_open(base_url('hr/assets/return/' . $a['id']), ['class' => 'd-inline']) ?><?= csrf_field() ?><button class="btn btn-sm btn-fm-outline">Return</button><?= form_close() ?>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($assets)): ?><tr><td colspan="7" class="text-muted text-center py-4">No assets assigned.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?= $this->endSection() ?>
