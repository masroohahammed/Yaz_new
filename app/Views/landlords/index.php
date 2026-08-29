<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-person-badge me-2 text-primary"></i>Landlords</h1></div>
  <?php if (empty($migrationRequired)): ?><a href="<?= base_url('landlords/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Landlord</a><?php endif; ?>
</div>
<?php if (!empty($migrationRequired)): ?>
<div class="alert alert-warning">The <code><?= esc($missingTable ?? 'landlords') ?></code> table is missing. Run migration <strong>2026-07-23-120000_PmErpModules</strong>.</div>
<?php else: ?>
<form class="filters-inline form-card mb-3" method="get">
  <input type="text" name="search" class="form-control form-control-sm" placeholder="Search…" value="<?= esc($search ?? '') ?>">
  <select name="status" class="form-select form-select-sm"><option value="">All</option><?php foreach (['active','inactive'] as $s): ?><option value="<?= $s ?>" <?= ($status ?? '')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select>
  <button class="btn btn-fm-outline btn-sm" type="submit">Filter</button>
</form>
<div class="form-card p-0"><div class="table-responsive"><table class="table table-registry table-sm mb-0">
<thead><tr><th>Code</th><th>Name</th><th>Phone</th><th>Email</th><th>ID</th><th>Status</th><th></th></tr></thead>
<tbody><?php foreach ($landlords as $l): ?><tr>
<td class="small fw-semibold"><?= esc($l['short_code'] ?? '—') ?></td>
<td class="fw-semibold"><?= esc($l['full_name']) ?></td><td><?= esc($l['phone'] ?? '—') ?></td><td><?= esc($l['email'] ?? '—') ?></td>
<td class="small"><?= esc($l['id_number'] ?? '—') ?></td><td><span class="badge bg-secondary"><?= esc($l['status']) ?></span></td>
<td class="text-end">
  <a href="<?= base_url('landlords/'.$l['id']) ?>" class="btn btn-sm btn-fm-outline">View</a>
  <a href="<?= base_url('reports/pm/landlord?landlord='.$l['id']) ?>" class="btn btn-sm btn-fm-outline">Reports</a>
  <a href="<?= base_url('landlords/'.$l['id'].'/revenue') ?>" class="btn btn-sm btn-fm-outline"><i class="bi bi-graph-up"></i></a>
  <a href="<?= base_url('landlords/'.$l['id'].'/edit') ?>" class="btn btn-sm btn-fm-outline">Edit</a>
</td>
</tr><?php endforeach; ?><?php if (empty($landlords)): ?><tr><td colspan="7" class="text-center text-muted py-4">No landlords.</td></tr><?php endif; ?></tbody>
</table></div></div>
<?php endif; ?>
<?= $this->endSection() ?>
