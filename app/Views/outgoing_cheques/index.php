<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1>Outgoing Cheques</h1></div><a href="<?= base_url('outgoing-cheques/create') ?>" class="btn btn-fm-primary btn-sm">Issue Cheque</a></div>
<?php if (!empty($migrationRequired)): ?><div class="alert alert-warning">Run migration for <code>outgoing_cheques</code>.</div><?php else: ?>
<form class="filters-inline form-card mb-3" method="get">
  <input type="text" name="search" class="form-control form-control-sm" value="<?= esc($search??'') ?>" placeholder="Search…">
  <button class="btn btn-fm-outline btn-sm" type="submit">Filter</button>
</form>
<div class="form-card p-0"><table class="table table-registry table-sm mb-0"><thead><tr><th>Cheque</th><th>Payee</th><th>Bank</th><th>Date</th><th>Amount</th><th>Status</th><th></th></tr></thead>
<tbody><?php foreach ($cheques as $c): ?><tr>
<td><?= esc($c['cheque_no']) ?></td><td><?= esc($c['payee_name']) ?></td><td><?= esc($c['bank_name']) ?></td>
<td><?= esc($c['cheque_date']) ?></td><td><?= number_format((float)$c['amount'],2) ?></td><td><?= esc($c['status']) ?></td>
<td><a href="<?= base_url('outgoing-cheques/'.$c['id'].'/edit') ?>" class="btn btn-sm btn-fm-outline">Edit</a></td>
</tr><?php endforeach; ?></tbody></table></div>
<?php endif; ?>
<?= $this->endSection() ?>