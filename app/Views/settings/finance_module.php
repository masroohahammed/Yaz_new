<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-diagram-3 me-2"></i>Finance Module Setup</h1>
    <div class="small text-muted">Chart of accounts, GL, AR/AP links — configure advanced finance here (not in main sidebar).</div>
  </div>
  <a href="<?= base_url('settings') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-arrow-left me-1"></i>Settings</a>
</div>

<?php if (empty($glEnabled)): ?>
<div class="alert alert-warning">
  <strong>Database:</strong> Run migration <code>FinanceErpFoundation</code> or SQL <code>patch_finance_erp_foundation.sql</code> to enable GL and COA.
</div>
<?php else: ?>
<div class="alert alert-success py-2 small mb-3"><i class="bi bi-check-circle me-1"></i>General Ledger engine is active. Payments and expenses auto-post journals.</div>
<?php endif; ?>

<div class="row g-3 mb-4">
  <?php foreach ($modules as $m):
    $badge = match ($m['status']) {
        'active'  => 'success',
        'partial' => 'warning',
        default   => 'secondary',
    };
    $href = ! empty($m['route']) ? base_url($m['route']) : null;
  ?>
  <div class="col-md-6 col-xl-4">
    <div class="fm-card h-100 finance-module-card">
      <div class="fm-card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <span class="fw-semibold"><?= esc($m['name']) ?></span>
          <span class="badge bg-<?= $badge ?>"><?= esc(ucfirst($m['status'])) ?></span>
        </div>
        <p class="small text-muted mb-3">Links: <?= esc(implode(', ', $m['integrates'])) ?></p>
        <?php if ($href && $m['status'] !== 'planned'): ?>
        <a href="<?= esc($href) ?>" class="btn btn-fm-primary btn-sm">Open</a>
        <?php else: ?>
        <span class="small text-muted">Coming soon</span>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="fm-card">
  <div class="card-header-fm"><h5 class="mb-0"><i class="bi bi-arrow-left-right me-2"></i>Integration flows</h5></div>
  <div class="fm-card-body p-0">
    <table class="table table-sm mb-0 finance-module-table">
      <thead class="table-light"><tr><th>From</th><th>To</th><th>Flow</th></tr></thead>
      <tbody>
        <?php foreach ($crossLinks as $l): ?>
        <tr>
          <td class="fw-semibold"><?= esc($l['from']) ?></td>
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
  <a href="<?= base_url('finance') ?>">Finance overview</a> ·
  <a href="<?= base_url('finance/payments') ?>">Record payments</a>
</p>
<?= $this->endSection() ?>
