<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-diagram-3 me-2"></i>Finance Module Hub</h1>
    <div class="small text-muted">Integrated with Maintenance, AMC, WO, Procurement, Inventory, HR &amp; Assets</div>
  </div>
  <?php if (!empty($glEnabled)): ?>
  <span class="badge bg-success">GL Engine Active</span>
  <?php else: ?>
  <a href="<?= base_url('settings') ?>" class="btn btn-fm-outline btn-sm">Run migration: Finance ERP Foundation</a>
  <?php endif; ?>
</div>

<div class="row g-3 mb-4">
  <?php foreach ($modules as $m): ?>
  <?php
    $badge = match ($m['status']) {
      'active'  => 'success',
      'partial' => 'warning',
      default   => 'secondary',
    };
    $href = !empty($m['route']) ? base_url($m['route']) : '#';
  ?>
  <div class="col-md-6 col-lg-4">
    <a href="<?= esc($href) ?>" class="text-decoration-none">
      <div class="fm-card h-100">
        <div class="fm-card-body">
          <div class="d-flex justify-content-between align-items-start">
            <strong class="text-dark"><?= esc($m['name']) ?></strong>
            <span class="badge bg-<?= $badge ?>"><?= esc(ucfirst($m['status'])) ?></span>
          </div>
          <div class="small text-muted mt-2">#<?= (int)$m['id'] ?> · <?= esc(implode(', ', $m['integrates'])) ?></div>
        </div>
      </div>
    </a>
  </div>
  <?php endforeach; ?>
</div>

<div class="fm-card">
  <div class="card-header-fm"><h5><i class="bi bi-arrow-left-right me-2"></i>Cross-Module Flows</h5></div>
  <div class="fm-card-body p-0">
    <table class="table table-sm mb-0">
      <thead><tr><th>From</th><th>To</th><th>Flow</th></tr></thead>
      <tbody>
        <?php foreach ($crossLinks as $l): ?>
        <tr>
          <td><?= esc($l['from']) ?></td>
          <td><?= esc($l['to']) ?></td>
          <td class="small text-muted"><?= esc($l['flow']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<p class="small text-muted mt-3">
  <a href="<?= base_url('finance/integration-log') ?>">Integration log</a> ·
  <a href="<?= base_url('docs') ?>">Documentation</a> (see repo <code>docs/FINANCE_MODULE.md</code>)
</p>
<?= $this->endSection() ?>
