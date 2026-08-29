<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-people me-2 text-primary"></i>Tenants</h1></div>
  <?php if (empty($migrationRequired)): ?>
  <a href="<?= base_url('tenants/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Tenant</a>
  <?php endif; ?>
</div>
<?php if (!empty($migrationRequired)): ?>
<div class="alert alert-warning">The <code><?= esc($missingTable ?? 'tenants') ?></code> table is missing. Run migration <strong>2026-07-23-120000_PmErpModules</strong> to enable this module.</div>
<?php else: ?>
<form class="filters-inline form-card mb-3" method="get">
  <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, phone, QID…" value="<?= esc($filters['search'] ?? '') ?>">
  <select name="type" class="form-select form-select-sm">
    <option value="">All types</option>
    <?php foreach (['Personal','Corporate'] as $t): ?>
    <option value="<?= $t ?>" <?= ($filters['type'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
    <?php endforeach; ?>
  </select>
  <select name="status" class="form-select form-select-sm">
    <option value="">All statuses</option>
    <?php foreach (['active','inactive','blacklisted'] as $s): ?>
    <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn btn-fm-outline btn-sm" type="submit">Filter</button>
</form>
<div class="form-card p-0">
  <div class="table-responsive">
    <table class="table table-registry table-sm mb-0">
      <thead><tr><th>Name</th><th>Type</th><th>Phone</th><th>QID / Passport</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($tenants as $t): ?>
        <tr>
          <td><div class="fw-semibold"><?= esc($t['full_name']) ?></div><div class="small text-muted"><?= esc($t['email'] ?? '') ?></div></td>
          <td><?= esc($t['tenant_type']) ?></td>
          <td><?= esc($t['phone']) ?></td>
          <td class="small"><?= esc($t['qid_no'] ?: $t['passport_no'] ?: '—') ?></td>
          <td><span class="badge bg-secondary"><?= esc($t['status']) ?></span></td>
          <td class="text-end">
            <a href="<?= base_url('tenants/'.$t['id']) ?>" class="btn btn-sm btn-fm-outline">View</a>
            <a href="<?= base_url('tenants/'.$t['id'].'/edit') ?>" class="btn btn-sm btn-fm-outline">Edit</a>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (empty($tenants)): ?><tr><td colspan="6" class="text-center text-muted py-4">No tenants found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>
