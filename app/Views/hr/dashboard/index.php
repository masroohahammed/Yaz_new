<?= $this->extend('layouts/hr') ?>
<?= $this->section('hr_content') ?>
<div class="page-header mb-3">
  <div>
    <h1><i class="bi bi-people-fill me-2 text-primary"></i>HR Dashboard</h1>
    <p class="text-muted small mb-0">Workforce, attendance, leave, payroll, and lifecycle — one module.</p>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-4 col-lg-2">
    <div class="fm-card"><div class="fm-card-body text-center py-3">
      <div class="fs-4 fw-bold text-primary"><?= (int)($stats['employees_active'] ?? 0) ?></div>
      <div class="small text-muted">Active Staff</div>
    </div></div>
  </div>
  <div class="col-6 col-md-4 col-lg-2">
    <div class="fm-card"><div class="fm-card-body text-center py-3">
      <div class="fs-4 fw-bold"><?= (int)($stats['leave_pending'] ?? 0) ?></div>
      <div class="small text-muted">Leave Pending</div>
    </div></div>
  </div>
  <div class="col-6 col-md-4 col-lg-2">
    <div class="fm-card"><div class="fm-card-body text-center py-3">
      <div class="fs-4 fw-bold"><?= (int)($stats['approvals_pending'] ?? 0) ?></div>
      <div class="small text-muted">Approvals</div>
    </div></div>
  </div>
  <div class="col-6 col-md-4 col-lg-2">
    <div class="fm-card"><div class="fm-card-body text-center py-3">
      <div class="fs-4 fw-bold"><?= (int)($stats['doc_expiring'] ?? 0) ?></div>
      <div class="small text-muted">Docs (30d)</div>
    </div></div>
  </div>
  <div class="col-6 col-md-4 col-lg-2">
    <div class="fm-card"><div class="fm-card-body text-center py-3">
      <div class="fs-4 fw-bold"><?= (int)($stats['contracts_expiring'] ?? 0) ?></div>
      <div class="small text-muted">Contracts (60d)</div>
    </div></div>
  </div>
  <div class="col-6 col-md-4 col-lg-2">
    <div class="fm-card"><div class="fm-card-body text-center py-3">
      <div class="fs-4 fw-bold"><?= (int)($stats['payroll_draft'] ?? 0) ?></div>
      <div class="small text-muted">Payroll Draft</div>
    </div></div>
  </div>
</div>

<div class="row g-3">
  <?php
  $rbac = new \App\Services\RbacService(\Config\Database::connect());
  $role = session()->get('user_role') ?? 'client';
  foreach ($quickLinks as $link):
    if (! $rbac->can($role, $link['perm'])) {
        continue;
    }
  ?>
  <div class="col-md-6 col-lg-4">
    <a href="<?= base_url($link['href']) ?>" class="text-decoration-none">
      <div class="fm-card h-100">
        <div class="fm-card-body d-flex align-items-start gap-3">
          <i class="bi <?= esc($link['icon']) ?> fs-3 text-primary"></i>
          <div>
            <strong class="text-dark d-block"><?= esc($link['label']) ?></strong>
            <span class="small text-muted"><?= esc($link['desc']) ?></span>
          </div>
        </div>
      </div>
    </a>
  </div>
  <?php endforeach; ?>
</div>
<?= $this->endSection() ?>
