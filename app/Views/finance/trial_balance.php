<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= $this->include('finance/_page_header', ['title' => 'Trial Balance', 'subtitle' => 'Posted GL as of date', 'backUrl' => 'finance/reports']) ?>
<?= form_open(base_url('finance/trial-balance'), ['method' => 'get', 'class' => 'row g-2 align-items-end mb-3']) ?>
<div class="col-auto"><label class="small">As of</label><input type="date" name="as_of" class="form-control form-control-sm" value="<?= esc($asOf) ?>"></div>
<div class="col-auto"><button class="btn btn-fm-primary btn-sm">Run</button></div>
<?= form_close() ?>
<?php if (empty($glEnabled)): ?>
<div class="alert alert-warning">Run <code>php spark migrate</code> and seed COA to enable GL reports.</div>
<?php else: ?>
<div class="fm-card"><div class="fm-card-body p-0">
<table class="fm-table"><thead><tr><th>Code</th><th>Account</th><th>Type</th><th class="text-end">Debit</th><th class="text-end">Credit</th><th class="text-end">Balance</th></tr></thead>
<tbody>
<?php foreach ($rows as $r): ?>
<tr>
  <td><?= esc($r['code']) ?></td><td><?= esc($r['name']) ?></td><td class="small"><?= esc($r['account_type']) ?></td>
  <td class="text-end"><?= number_format($r['debit'], 2) ?></td>
  <td class="text-end"><?= number_format($r['credit'], 2) ?></td>
  <td class="text-end fw-semibold"><?= number_format($r['balance'], 2) ?></td>
</tr>
<?php endforeach; ?>
</tbody>
<tfoot><tr class="fw-bold"><td colspan="3">Totals</td><td class="text-end"><?= number_format($totalDebit, 2) ?></td><td class="text-end"><?= number_format($totalCredit, 2) ?></td><td></td></tr></tfoot>
</table>
</div></div>
<?php endif; ?>
<?= $this->endSection() ?>
