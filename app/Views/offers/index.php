<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-gift me-2 text-primary"></i>Complimentary Offers</h1></div>
  <a href="<?= base_url('complimentary-offers/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>New Offer</a>
</div>

<?php if (!empty($migrationRequired)): ?>
  <div class="alert alert-warning">Run database migration to enable this module.</div>
<?php else: ?>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<form class="filters-inline form-card mb-3" method="get">
  <select name="status" class="form-select form-select-sm">
    <option value="">All statuses</option>
    <?php foreach (['active','expired','cancelled'] as $s): ?>
      <option value="<?= $s ?>" <?= ($filters['status']??'')===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn btn-fm-outline btn-sm" type="submit">Filter</button>
</form>

<div class="form-card p-0">
  <table class="table table-registry table-sm mb-0">
    <thead><tr><th>Contract</th><th>Tenant</th><th>Property</th><th>Type</th><th>Period / Discount</th><th>Dates</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($offers as $o): ?>
      <tr>
        <td><?= esc($o['contract_number']??'—') ?></td>
        <td><?= esc($o['tenant_name']??'—') ?></td>
        <td class="small"><?= esc($o['facility_name']??'—') ?><?php if(!empty($o['unit_number'])): ?> / <?= esc($o['unit_number']) ?><?php endif; ?></td>
        <td><?= esc($o['offer_type']) ?></td>
        <td class="small">
          <?php if ($o['free_period_value']): ?><?= $o['free_period_value'] ?> months free<?php endif; ?>
          <?php if ($o['discount_percent']): ?><?= $o['discount_percent'] ?>% off<?php endif; ?>
        </td>
        <td class="small"><?= esc($o['start_date']??'—') ?> → <?= esc($o['end_date']??'—') ?></td>
        <td><span class="badge bg-<?= $o['status']==='active'?'success':($o['status']==='expired'?'secondary':'danger') ?>"><?= esc($o['status']) ?></span></td>
        <td>
          <div class="d-flex gap-1">
            <a href="<?= base_url('complimentary-offers/'.$o['id'].'/edit') ?>" class="btn btn-sm btn-fm-outline">Edit</a>
            <?php if ($o['status'] === 'active'): ?>
              <form method="post" action="<?= base_url('complimentary-offers/'.$o['id'].'/expire') ?>"><?= csrf_field() ?>
                <button class="btn btn-sm btn-warning" onclick="return confirm('Mark as expired?')">Expire</button>
              </form>
            <?php endif; ?>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($offers)): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">No complimentary offers found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php if ($total > $perPage): ?>
<div class="mt-3 text-muted small">Showing <?= count($offers) ?> of <?= $total ?></div>
<?php endif; ?>
<?php endif; ?>
<?= $this->endSection() ?>
