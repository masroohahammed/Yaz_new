<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1>Trial Balance</h1><small class="text-muted">As of <?= esc($asOf) ?></small></div>
  <form method="get" class="d-inline"><?= form_open(base_url('finance/pm/trial-balance'), ['method'=>'get']) ?>
    <input type="date" name="as_of" value="<?= esc($asOf) ?>" class="form-control form-control-sm d-inline-block w-auto">
    <button class="btn btn-sm btn-secondary">Generate</button>
  <?= form_close() ?></form>
</div>
<table class="table table-sm"><thead><tr><th>Code</th><th>Account</th><th class="text-end">Income</th><th class="text-end">Expense</th></tr></thead>
<tbody><?php foreach ($rows as $r): ?><tr>
  <td><?= esc($r['code'] ?? '') ?></td><td><?= esc($r['name']) ?></td>
  <td class="text-end"><?= number_format((float)($r['income']??0),2) ?></td>
  <td class="text-end"><?= number_format((float)($r['expense']??0),2) ?></td>
</tr><?php endforeach; ?></tbody></table>
<?= $this->endSection() ?>
