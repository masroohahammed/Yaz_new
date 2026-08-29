<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-lightning-charge me-2 text-primary"></i>Utility Accounts</h1></div>
  <a href="<?= base_url('utilities/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>New Account</a>
</div>

<?php if (!empty($migrationRequired)): ?>
  <div class="alert alert-warning">Run database migration to enable utility billing module.</div>
<?php else: ?>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<form class="filters-inline form-card mb-3" method="get">
  <select name="facility_id" class="form-select form-select-sm">
    <option value="">All properties</option>
    <?php foreach ($facilities??[] as $f): ?>
      <option value="<?= $f['id'] ?>" <?= ($filters['facility_id']??0)==$f['id']?'selected':'' ?>><?= esc($f['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="billing_mode" class="form-select form-select-sm">
    <option value="">All modes</option>
    <?php foreach (['included','billed_separately','tenant_pays_direct','complimentary'] as $m): ?>
      <option value="<?= $m ?>" <?= ($filters['billing_mode']??'')===$m?'selected':'' ?>><?= str_replace('_',' ',ucfirst($m)) ?></option>
    <?php endforeach; ?>
  </select>
  <select name="status" class="form-select form-select-sm">
    <option value="">All statuses</option>
    <option value="active" <?= ($filters['status']??'')==='active'?'selected':'' ?>>Active</option>
    <option value="inactive" <?= ($filters['status']??'')==='inactive'?'selected':'' ?>>Inactive</option>
  </select>
  <button class="btn btn-fm-outline btn-sm" type="submit">Filter</button>
</form>

<div class="form-card p-0">
  <table class="table table-registry table-sm mb-0">
    <thead><tr><th>Utility</th><th>Property</th><th>Unit</th><th>Provider</th><th>Account #</th><th>Billing Mode</th><th>Monthly</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($accounts as $a): ?>
      <tr>
        <td><strong><?= esc($a['utility_name']) ?></strong><?php if(!empty($a['meter_number'])): ?><br><small class="text-muted">Meter: <?= esc($a['meter_number']) ?></small><?php endif; ?></td>
        <td><?= esc($a['facility_name']??'—') ?></td>
        <td><?= esc($a['unit_number']??'—') ?></td>
        <td><?= esc($a['provider_name']??'—') ?></td>
        <td class="small"><?= esc($a['account_number']??'—') ?></td>
        <td><span class="badge bg-<?= ['included'=>'info','billed_separately'=>'primary','tenant_pays_direct'=>'warning','complimentary'=>'success'][$a['billing_mode']]??'secondary' ?>"><?= str_replace('_',' ',esc($a['billing_mode'])) ?></span></td>
        <td><?= $a['monthly_charge'] ? number_format((float)$a['monthly_charge'],2) : '—' ?></td>
        <td><span class="badge bg-<?= $a['status']==='active'?'success':'secondary' ?>"><?= esc($a['status']) ?></span></td>
        <td>
          <div class="d-flex gap-1">
            <a href="<?= base_url('utilities/'.$a['id'].'/bills') ?>" class="btn btn-sm btn-fm-primary">Bills</a>
            <a href="<?= base_url('utilities/'.$a['id'].'/edit') ?>" class="btn btn-sm btn-fm-outline">Edit</a>
            <form method="post" action="<?= base_url('utilities/'.$a['id'].'/delete') ?>" onsubmit="return confirm('Remove this account?')"><?= csrf_field() ?>
              <button class="btn btn-sm btn-outline-danger">Del</button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($accounts)): ?>
        <tr><td colspan="9" class="text-center text-muted py-4">No utility accounts found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php if ($total > $perPage): ?>
<div class="mt-2 text-muted small">Showing <?= count($accounts) ?> of <?= $total ?></div>
<?php endif; ?>
<?php endif; ?>
<?= $this->endSection() ?>
