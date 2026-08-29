<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= $this->include('finance/_page_header', ['title' => 'AR Aging', 'backUrl' => 'finance/reports']) ?>
<div class="row g-2 mb-3">
  <?php foreach ($buckets as $label => $amt): ?>
  <div class="col"><div class="fm-card p-2 text-center"><div class="small text-muted"><?= esc($label) ?></div><strong><?= $currency ?> <?= number_format($amt, 2) ?></strong></div></div>
  <?php endforeach; ?>
</div>
<div class="fm-card"><div class="fm-card-body p-0">
<table class="fm-table"><thead><tr><th>Invoice</th><th>Facility</th><th>Due</th><th>Days</th><th>Bucket</th><th class="text-end">Amount</th></tr></thead>
<tbody>
<?php foreach ($rows as $r): ?>
<tr>
  <td><a href="<?= base_url('finance/invoices/view/'.(int)($r['id'] ?? 0)) ?>"><?= esc($r['invoice_number']) ?></a></td>
  <td><?= esc($r['facility_name'] ?? '—') ?></td>
  <td class="small"><?= esc($r['due_date']) ?></td>
  <td><?= (int)($r['days_overdue'] ?? 0) ?></td>
  <td><span class="fm-badge"><?= esc($r['bucket']) ?></span></td>
  <td class="text-end"><?= number_format((float)$r['total'], 2) ?></td>
</tr>
<?php endforeach; ?>
<?php if (empty($rows)): ?><tr><td colspan="6" class="text-center py-4 text-muted">No outstanding receivables</td></tr><?php endif; ?>
</tbody></table>
</div></div>
<?= $this->endSection() ?>
