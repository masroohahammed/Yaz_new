<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div>
    <h1><i class="bi bi-file-earmark-text me-2 text-primary"></i>Lease Contracts</h1>
  </div>
  <a href="<?= base_url('contracts/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Contract</a>
</div>

<div class="fm-card mb-4 p-3">
  <form method="get" class="row g-2 align-items-end">
    <div class="col-md-3">
      <input type="search" name="search" class="form-control form-control-sm" placeholder="Contract #, tenant…" value="<?= esc($filters['search'] ?? '') ?>">
    </div>
    <div class="col-md-2">
      <select name="status" class="form-select form-select-sm">
        <option value="">All statuses</option>
        <?php foreach (['draft', 'active', 'expired', 'terminated', 'renewed'] as $st): ?>
        <option value="<?= $st ?>" <?= ($filters['status'] ?? '') === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <select name="property_id" class="form-select form-select-sm">
        <option value="">All properties</option>
        <?php foreach ($properties as $p): ?>
        <option value="<?= $p['id'] ?>" <?= ($filters['property_id'] ?? '') == $p['id'] ? 'selected' : '' ?>><?= esc($p['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-auto">
      <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
      <a href="<?= base_url('contracts') ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
    </div>
  </form>
</div>

<div class="fm-card p-0">
  <table class="table table-registry table-hover align-middle mb-0">
    <thead class="table-light">
      <tr>
        <th>Contract</th>
        <th>Tenant</th>
        <th>Property / Unit</th>
        <th>Rent</th>
        <th>Period</th>
        <th>Status</th>
        <th class="text-end">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($contracts)): ?>
      <tr><td colspan="7" class="text-center text-muted py-4">No contracts found.</td></tr>
      <?php else: ?>
      <?php foreach ($contracts as $c): ?>
      <tr>
        <td class="fw-semibold small"><?= esc($c['contract_number'] ?? $c['id']) ?></td>
        <td class="small"><?= esc($c['tenant_name'] ?? '') ?></td>
        <td class="small"><?= esc($c['property_name'] ?? '') ?> / <?= esc($c['unit_number'] ?? '') ?></td>
        <td class="small"><?= number_format((float) ($c['rent_amount'] ?? 0), 0) ?></td>
        <td class="small"><?= esc($c['start_date'] ?? '') ?> → <?= esc($c['end_date'] ?? '') ?></td>
        <td><span class="fm-badge badge-status-<?= esc($c['status']) ?>"><?= ucfirst($c['status']) ?></span></td>
        <td class="text-end">
          <a href="<?= base_url('contracts/' . $c['id']) ?>" class="btn btn-sm btn-outline-primary">View</a>
          <a href="<?= base_url('contracts/' . $c['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
          <a href="<?= base_url('contracts/' . $c['id'] . '/print') ?>" class="btn btn-sm btn-outline-info" target="_blank">Print</a>
          <?php if ($c['status'] === 'active'): ?>
          <a href="<?= base_url('contracts/' . $c['id'] . '/terminate') ?>" class="btn btn-sm btn-outline-danger">Terminate</a>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php if (($total ?? 0) > ($perPage ?? 20)): ?>
<nav class="mt-3">
  <ul class="pagination pagination-sm justify-content-center">
    <?php
    $pages = (int) ceil($total / $perPage);
    $cur = (int) ($page ?? 1);
    $qs = $filters;
    for ($i = 1; $i <= $pages; $i++):
      $qs['page'] = $i;
    ?>
    <li class="page-item <?= $i === $cur ? 'active' : '' ?>"><a class="page-link" href="?<?= http_build_query($qs) ?>"><?= $i ?></a></li>
    <?php endfor; ?>
  </ul>
</nav>
<?php endif; ?>

<?= $this->endSection() ?>
