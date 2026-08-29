<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-briefcase me-2 text-primary"></i>Sales Deals</h1></div>
<a href="<?= base_url('sales/create') ?>" class="btn btn-fm-primary btn-sm">New Deal</a></div>
<?php if (!empty($migrationRequired)): ?><div class="alert alert-warning">Run migration for <code>sales_deals</code>.</div><?php else: ?>
<form class="filters-inline form-card mb-3" method="get">
  <input type="text" name="search" class="form-control form-control-sm" value="<?= esc($filters['search']??'') ?>" placeholder="Search deals…">
  <button class="btn btn-fm-outline btn-sm" type="submit">Filter</button>
</form>
<div class="form-card p-0"><table class="table table-registry table-sm mb-0"><thead><tr><th>Deal</th><th>Buyer</th><th>Property</th><th>Value</th><th>Stage</th></tr></thead>
<tbody><?php foreach ($deals as $d): ?><tr>
<td><?= esc($d['deal_number']) ?></td><td><?= esc($d['buyer_name']) ?></td><td><?= esc($d['facility_name']??'—') ?></td>
<td><?= $d['agreed_price'] ? number_format((float)$d['agreed_price'],2) : '—' ?></td><td><?= esc($d['stage']) ?></td>
</tr><?php endforeach; ?><?php if (empty($deals)): ?><tr><td colspan="5" class="text-center text-muted py-4">No deals.</td></tr><?php endif; ?></tbody></table></div>
<?php endif; ?>
<?= $this->endSection() ?>