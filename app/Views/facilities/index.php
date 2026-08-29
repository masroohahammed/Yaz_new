<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
helper('fm');
$rbac = new \App\Services\RbacService(\Config\Database::connect());
$canCreateFacility = $rbac->can((string) (session()->get('user_role') ?? 'client'), 'facilities.create');
$canEditFacility = $rbac->can((string) (session()->get('user_role') ?? 'client'), 'facilities.edit');
$propertyBase = fm_workspace_prefix();
?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-building me-2 text-primary"></i>Properties</h1>
  </div>
  <?php if ($canCreateFacility): ?>
  <a href="<?= base_url($propertyBase.'/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Property</a>
  <?php endif; ?>
</div>

<!-- Filters -->
<div class="fm-card mb-4 p-3">
    <form method="get" class="row g-2 align-items-end">
        <div class="col-sm-6 col-md-4">
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="Search name, code…" value="<?= esc($filters['search'] ?? '') ?>">
        </div>
        <div class="col-sm-6 col-md-3">
            <select name="company_id" class="form-select form-select-sm">
                <option value="">All Companies</option>
                <?php foreach ($companies as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($filters['company_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-sm-6 col-md-2">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                <option value="under_maintenance" <?= ($filters['status'] ?? '') === 'under_maintenance' ? 'selected' : '' ?>>Under Maintenance</option>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-secondary">Filter</button>
            <a href="<?= base_url($propertyBase) ?>" class="btn btn-sm btn-outline-secondary ms-1">Reset</a>
        </div>
    </form>
</div>

<!-- Grid -->
<div class="row g-3">
    <?php if (empty($facilities)): ?>
        <div class="col-12 text-center py-5 text-muted">No facilities found.</div>
    <?php else: ?>
        <?php foreach ($facilities as $f): ?>
            <div class="col-sm-6 col-xl-4">
                <div class="fm-card h-100 p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="fw-semibold mb-0">
                                <a href="<?= base_url($propertyBase.'/'.$f['id']) ?>" class="text-decoration-none"><?= esc($f['name']) ?></a>
                            </h6>
                            <small class="text-muted"><?= esc($f['code']) ?></small>
                        </div>
                        <?php $sc = ['active'=>'success','inactive'=>'secondary','under_maintenance'=>'warning']; ?>
                        <span class="badge bg-<?= $sc[$f['status']] ?? 'secondary' ?>"><?= ucwords(str_replace('_',' ',$f['status'])) ?></span>
                    </div>
                    <div class="text-muted small mb-1"><i class="bi bi-building me-1"></i><?= esc($f['company_name'] ?? '—') ?></div>
                    <div class="text-muted small mb-1"><i class="bi bi-geo-alt me-1"></i><?= esc($f['city']) ?>, <?= esc($f['country']) ?></div>
                    <?php if ($f['manager_name']): ?>
                        <div class="text-muted small mb-1"><i class="bi bi-person me-1"></i><?= esc($f['manager_name']) ?></div>
                    <?php endif; ?>
                    <?php if ($f['area_sqm']): ?>
                        <div class="text-muted small"><i class="bi bi-rulers me-1"></i><?= number_format($f['area_sqm']) ?> sqm &bull; <?= $f['floors'] ?> floors</div>
                    <?php endif; ?>
                    <div class="mt-3">
                        <a href="<?= base_url($propertyBase.'/'.$f['id']) ?>" class="btn btn-sm btn-outline-secondary me-1">View</a>
                        <?php if ($canEditFacility): ?>
                            <a href="<?= base_url($propertyBase.'/edit/'.$f['id']) ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
