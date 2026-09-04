<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-file-earmark-text me-2 text-primary"></i>Lease Contracts</h1></div>
  <?php if (empty($migrationRequired)): ?>
  <div class="d-flex gap-2 flex-wrap">
    <form method="post" action="<?= base_url('contracts/sync-units') ?>" class="d-inline">
      <?= csrf_field() ?>
      <button type="submit" class="btn btn-fm-outline btn-sm" data-confirm="Import and update all unit contracts (property + parking) into this lease list? Existing leases for the same unit will be updated, not duplicated.">
        <i class="bi bi-arrow-repeat me-1"></i>Sync all from Units
      </button>
    </form>
    <a href="<?= base_url('reports/pm/leases?expiring=1') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-calendar-event me-1"></i>Renew expiring</a>
    <a href="<?= base_url('contracts/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Contract</a>
  </div>
  <?php endif; ?>
</div>
<?php if ($msg = session()->getFlashdata('sync_errors')): ?>
<?php if (is_array($msg) && count($msg)): ?>
<div class="alert alert-warning small">
  <strong>Sync notes:</strong>
  <ul class="mb-0 mt-1"><?php foreach ($msg as $err): ?><li><?= esc($err) ?></li><?php endforeach; ?></ul>
</div>
<?php endif; ?>
<?php endif; ?>
<?php if (!empty($migrationRequired)): ?>
<div class="alert alert-warning">Run migration <strong>2026-07-23-120000_PmErpModules</strong> to create <code>lease_contracts</code>.</div>
<?php else: ?>
<form class="filters-inline form-card mb-3" method="get">
  <input type="text" name="search" class="form-control form-control-sm" placeholder="Contract, tenant, unit…" value="<?= esc($filters['search'] ?? '') ?>">
  <select name="status" class="form-select form-select-sm"><option value="">All statuses</option><?php foreach (['draft','active','expired','terminated','renewed'] as $s): ?><option value="<?= $s ?>" <?= ($filters['status']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select>
  <select name="facility" class="form-select form-select-sm"><option value="0">All properties</option><?php foreach ($facilities as $f): ?><option value="<?= $f['id'] ?>" <?= (int)($filters['facility']??0)===(int)$f['id']?'selected':'' ?>><?= esc($f['name']) ?></option><?php endforeach; ?></select>
  <button class="btn btn-fm-outline btn-sm" type="submit">Filter</button>
</form>
<p class="small text-muted mb-3">
  <i class="bi bi-info-circle me-1"></i>
  <strong>Sync from Units</strong> copies inline unit contracts (property + parking) and legacy FM unit contracts into this lease list, including plate numbers for parking units.
</p>
<div class="form-card p-0"><div class="table-responsive"><table class="table table-registry table-sm mb-0">
<thead><tr><th>Contract</th><th>Tenant</th><th>Property</th><th>Unit</th><th>Type</th><th>Rent</th><th>Period</th><th>Status</th><th></th></tr></thead>
<tbody><?php foreach ($contracts as $c):
  $isParking = strtolower((string)($c['unit_type'] ?? '')) === 'parking' || ($c['contract_kind'] ?? '') === 'parking';
?><tr>
<td><a href="<?= base_url('contracts/'.$c['id']) ?>"><?= esc($c['contract_number']) ?></a></td>
<td><?= esc($c['tenant_name'] ?? '—') ?></td><td><?= esc($c['facility_name'] ?? '—') ?></td><td><?= esc($c['unit_number'] ?? '—') ?></td>
<td class="small"><?= $isParking ? '<span class="badge bg-secondary">Parking</span>' : 'Property' ?><?php $plate = $c['plate_number'] ?? $c['unit_plate_number'] ?? ''; if ($isParking && $plate !== ''): ?><br><span class="text-muted"><?= esc($plate) ?></span><?php endif; ?></td>
<td><?= number_format((float)$c['rent_amount'],2) ?> <?= esc($currency) ?></td>
<td class="small"><?= esc($c['start_date']) ?> – <?= esc($c['end_date']) ?></td>
<td><span class="badge bg-secondary"><?= esc($c['status']) ?></span></td>
<td><a href="<?= base_url('contracts/'.$c['id'].'/edit') ?>" class="btn btn-sm btn-fm-outline">Edit</a>
<?php if ($isParking): ?><a href="<?= base_url('contracts/'.$c['id'].'/parking-print') ?>" class="btn btn-sm btn-success">Renew</a><?php endif; ?></td>
</tr><?php endforeach; ?><?php if (empty($contracts)): ?><tr><td colspan="9" class="text-center text-muted py-4">No contracts.</td></tr><?php endif; ?></tbody>
</table></div></div>
<?php endif; ?>
<?= $this->endSection() ?>
