<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-start flex-wrap gap-2">
  <div>
    <h1><i class="bi bi-arrow-left-right me-2 text-warning"></i>Compare Inspections</h1>
    <div class="small text-muted">
      <?= esc(ucfirst(str_replace('_', ' ', (string) ($left['type'] ?? '')))) ?>
      (<?= esc($left['inspection_date'] ?? $left['created_at'] ?? '') ?>)
      vs
      <?= esc(ucfirst(str_replace('_', ' ', (string) ($right['type'] ?? '')))) ?>
      (<?= esc($right['inspection_date'] ?? $right['created_at'] ?? '') ?>)
    </div>
  </div>
  <a href="<?= base_url('pm-inspections') ?>" class="btn btn-sm btn-fm-outline"><i class="bi bi-arrow-left me-1"></i>Back</a>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-6">
    <div class="fm-card h-100">
      <div class="card-header-fm"><h6 class="mb-0">Before — Unit <?= esc($left['unit_number']) ?></h6></div>
      <div class="fm-card-body small">
        <div><?= esc($left['property_name']) ?></div>
        <a href="<?= base_url('pm-inspections/view/' . $left['id']) ?>" class="btn btn-sm btn-fm-outline mt-2">View report</a>
      </div>
    </div>
  </div>
  <div class="col-md-6">
    <div class="fm-card h-100">
      <div class="card-header-fm"><h6 class="mb-0">After — Unit <?= esc($right['unit_number']) ?></h6></div>
      <div class="fm-card-body small">
        <div><?= esc($right['property_name']) ?></div>
        <a href="<?= base_url('pm-inspections/view/' . $right['id']) ?>" class="btn btn-sm btn-fm-outline mt-2">View report</a>
      </div>
    </div>
  </div>
</div>

<div class="fm-card">
  <div class="card-header-fm">
    <h5 class="mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Condition Changes</h5>
    <?php if (! empty($diff)): ?>
    <span class="fm-badge badge-status-cancelled"><?= count($diff) ?> flagged</span>
    <?php endif; ?>
  </div>
  <div class="fm-card-body p-0">
    <?php if (empty($diff)): ?>
    <div class="text-center py-5 text-muted">
      <i class="bi bi-check-circle d-block mb-2 text-success" style="font-size:2rem"></i>
      No significant discrepancies flagged between these inspections.
    </div>
    <?php else: ?>
    <div class="table-responsive">
      <table class="table table-registry table-sm mb-0">
        <thead><tr><th>Area</th><th>Before</th><th>After</th></tr></thead>
        <tbody>
        <?php foreach ($diff as $d): ?>
        <tr class="table-warning">
          <td class="fw-semibold"><?= esc($d['area']) ?></td>
          <td><span class="fm-badge condition-badge-<?= esc($d['before']) ?>"><?= ucfirst(esc($d['before'])) ?></span></td>
          <td><span class="fm-badge condition-badge-<?= esc($d['after']) ?>"><?= ucfirst(esc($d['after'])) ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
<?= $this->endSection() ?>
