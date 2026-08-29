<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-building me-2 text-primary"></i><?= esc($facility['name']) ?></h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= base_url('facilities') ?>">Facilities</a></li><li class="breadcrumb-item active"><?= esc($facility['name']) ?></li></ol></nav>
  </div>
  <?php $sc = ['active'=>'success','inactive'=>'secondary','under_maintenance'=>'warning']; ?>
  <span class="fm-badge badge-status-<?= $facility['status'] ?? 'inactive' ?>"><?= ucwords(str_replace('_',' ',$facility['status'])) ?></span>
</div>

<div class="row g-4">
    <div class="col-lg-8">

        <!-- Details -->
        <div class="fm-card mb-4">
            <div class="fm-card-header d-flex justify-content-between">
                <span class="fw-semibold">Facility Details</span>
                <?php if (in_array(session('user_role'), ['super_admin','facility_manager'])): ?>
                    <a href="/facilities/<?= $facility['id'] ?>/edit" class="btn btn-sm btn-outline-secondary">Edit</a>
                <?php endif; ?>
            </div>
            <div class="p-3">
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted small">Code</dt>
                    <dd class="col-sm-8"><?= esc($facility['code']) ?></dd>
                    <dt class="col-sm-4 text-muted small">Company</dt>
                    <dd class="col-sm-8"><?= esc($facility['company_name'] ?? '—') ?></dd>
                    <dt class="col-sm-4 text-muted small">Address</dt>
                    <dd class="col-sm-8"><?= esc($facility['address'] ?? '') ?>, <?= esc($facility['city']) ?>, <?= esc($facility['country']) ?></dd>
                    <dt class="col-sm-4 text-muted small">Manager</dt>
                    <dd class="col-sm-8"><?= esc($facility['manager_name'] ?? '—') ?></dd>
                    <dt class="col-sm-4 text-muted small">Area</dt>
                    <dd class="col-sm-8"><?= $facility['area_sqm'] ? number_format($facility['area_sqm']) . ' sqm' : '—' ?></dd>
                    <dt class="col-sm-4 text-muted small">Floors</dt>
                    <dd class="col-sm-8"><?= $facility['floors'] ?? '—' ?></dd>
                </dl>
            </div>
        </div>

        <!-- Assets -->
        <div class="fm-card mb-4">
            <div class="fm-card-header d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Assets (<?= count($assets) ?>)</span>
                <?php if (in_array(session('user_role'), ['super_admin','facility_manager'])): ?>
                    <a href="/assets/create?facility_id=<?= $facility['id'] ?>" class="btn btn-sm btn-primary-brand">+ Add Asset</a>
                <?php endif; ?>
            </div>
            <div class="table-responsive">
                <table class="table table-fm mb-0">
                    <thead><tr><th>Code</th><th>Name</th><th>Category</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        <?php if (empty($assets)): ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No assets registered.</td></tr>
                        <?php else: ?>
                            <?php foreach ($assets as $a): ?>
                                <tr>
                                    <td class="small text-muted"><?= esc($a['asset_code']) ?></td>
                                    <td><a href="/assets/<?= $a['id'] ?>" class="text-decoration-none"><?= esc($a['name']) ?></a></td>
                                    <td><?= esc($a['category'] ?? '—') ?></td>
                                    <td><span class="badge bg-light text-dark border small"><?= ucfirst($a['status'] ?? 'active') ?></span></td>
                                    <td><a href="/assets/<?= $a['id'] ?>" class="btn btn-sm btn-outline-secondary py-0 px-2">View</a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <div class="col-lg-4">
        <!-- Stats -->
        <div class="fm-card mb-4 p-3">
            <div class="fw-semibold mb-3">Quick Stats</div>
            <div class="d-flex flex-column gap-2">
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Active Work Orders</span>
                    <span class="fw-semibold"><?= $activeWoCount ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Total Assets</span>
                    <span class="fw-semibold"><?= count($assets) ?></span>
                </div>
            </div>
            <div class="mt-3">
                <a href="/work-orders?facility_id=<?= $facility['id'] ?>" class="btn btn-sm btn-outline-secondary w-100">
                    View Work Orders <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <?php if (in_array(session('user_role'), ['super_admin'])): ?>
            <div class="fm-card p-3">
                <div class="fw-semibold mb-3 text-danger">Danger Zone</div>
                <form action="/facilities/<?= $facility['id'] ?>/delete" method="post"
                      onsubmit="return confirm('Remove this facility permanently?')">
                    <?= csrf_field() ?>
                    <button class="btn btn-sm btn-outline-danger w-100">Remove Facility</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
