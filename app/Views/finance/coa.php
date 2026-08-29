<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= $this->include('finance/_page_header', ['title' => 'Chart of Accounts', 'subtitle' => 'Assets, liabilities, income & expense accounts', 'backUrl' => 'finance']) ?>
<?php if (empty($accounts)): ?>
<div class="alert alert-warning">Run database migration <code>2026-05-24-140000_FinanceErpFoundation</code> or SQL patch <code>patch_finance_erp_foundation.sql</code>.</div>
<?php else: ?>
<div class="fm-card"><div class="fm-card-body p-0">
<table class="table table-sm mb-0">
  <thead><tr><th>Code</th><th>Account</th><th>Group</th><th>Type</th><th class="text-end">Opening</th></tr></thead>
  <tbody>
  <?php foreach ($accounts as $a): ?>
  <tr>
    <td><code><?= esc($a['code']) ?></code></td>
    <td><?= esc($a['name']) ?></td>
    <td class="small"><?= esc($a['group_name'] ?? '') ?></td>
    <td><span class="badge bg-light text-dark"><?= esc($a['account_type'] ?? '') ?></span></td>
    <td class="text-end"><?= number_format((float)($a['opening_balance']??0), 2) ?></td>
  </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div></div>
<?php endif; ?>
<?= $this->endSection() ?>
