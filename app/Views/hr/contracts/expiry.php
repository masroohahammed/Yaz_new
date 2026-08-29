<?= $this->extend('layouts/hr') ?>
<?= $this->section('hr_content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-file-earmark-ruled me-2 text-primary"></i>Employment Contract Expiry</h1>
    <p class="text-muted small mb-0">Direct and outsourced employee contracts — history is never overwritten on renewal.</p>
  </div>
  <a href="<?= base_url('employees') ?>" class="btn btn-fm-outline btn-sm">Workforce</a>
</div>

<?php if (!empty($migrationRequired)): ?>
<div class="alert alert-warning">Run <code>database/patch_hr_upgrade_complete.sql</code> to enable employment contracts.</div>
<?php else: ?>

<ul class="nav nav-pills mb-3 flex-wrap gap-1">
  <?php foreach ($buckets as $key => $meta): ?>
  <li class="nav-item">
    <a class="nav-link <?= ($activeBucket ?? '') === $key ? 'active' : '' ?>" href="<?= base_url('hr/contracts/expiry?bucket='.$key) ?>">
      <?= esc($meta['label']) ?> <span class="badge bg-secondary ms-1"><?= count($bucketData[$key] ?? []) ?></span>
    </a>
  </li>
  <?php endforeach; ?>
</ul>

<div class="fm-card">
  <div class="card-header-fm"><h5><?= esc($buckets[$activeBucket]['label'] ?? 'Contracts') ?></h5></div>
  <div class="fm-card-body p-0"><div class="table-responsive">
    <table class="fm-table">
      <thead><tr><th>Employee</th><th>Contract #</th><th>End Date</th><th>Status</th><th>Supplier</th><?php if ($canViewRates ?? false): ?><th>Cost</th><?php endif; ?><th></th></tr></thead>
      <tbody>
      <?php foreach ($activeRows as $row): ?>
      <tr>
        <td><?= esc($row['employee_name'] ?? '—') ?> <span class="text-muted small">(<?= esc($row['emp_code'] ?? '') ?>)</span></td>
        <td><?= esc($row['contract_number'] ?: '#'.$row['id']) ?></td>
        <td><?= !empty($row['contract_end_date']) ? date('d M Y', strtotime($row['contract_end_date'])) : '—' ?></td>
        <td><span class="fm-badge badge-status-<?= esc($row['contract_status']) ?>"><?= esc(ucfirst(str_replace('_', ' ', $row['contract_status']))) ?></span></td>
        <td class="small"><?= esc($row['supplier_name'] ?? '—') ?></td>
        <?php if ($canViewRates ?? false): ?><td class="small"><?= $row['cost_rate'] !== null ? number_format((float)$row['cost_rate'], 2) : '—' ?></td><?php endif; ?>
        <td><a href="<?= base_url('employees/view/'.$row['employee_id'].'?tab=contract') ?>" class="btn btn-sm btn-fm-outline">Profile</a></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($activeRows)): ?><tr><td colspan="7" class="text-center py-4 text-muted">No contracts in this bucket.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div></div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>
