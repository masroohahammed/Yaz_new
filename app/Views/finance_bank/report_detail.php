<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between"><h1><?= esc($title) ?></h1><button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer me-1"></i>Print</button></div>
<div class="fm-card"><div class="fm-card-body p-0">
<table class="fm-table"><thead><tr><th>Date</th><th>Number</th><th>Type</th><th>Debit</th><th>Credit</th><th>Property</th></tr></thead><tbody>
<?php foreach ($transactions as $t): ?>
<tr>
  <td class="small"><?= date('d M Y', strtotime($t['transaction_date'])) ?></td>
  <td class="small font-monospace"><?= esc($t['transaction_number']) ?></td>
  <td class="small"><?= esc($t['transaction_type']) ?></td>
  <td class="small"><?= (float)$t['debit'] ? number_format((float)$t['debit'],2) : '—' ?></td>
  <td class="small"><?= (float)$t['credit'] ? number_format((float)$t['credit'],2) : '—' ?></td>
  <td class="small text-muted"><?= esc($t['facility_name'] ?? '—') ?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div></div>
<?= $this->endSection() ?>
