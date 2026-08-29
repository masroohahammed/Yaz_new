<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div><h1><i class="bi bi-currency-exchange me-2"></i>Profit / Costing</h1></div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('reports/export/profit/csv') ?>" class="btn btn-fm-outline btn-sm">CSV</a>
    <a href="<?= base_url('reports/export/profit/pdf') ?>" class="btn btn-fm-outline btn-sm">PDF</a>
    <a href="<?= base_url('reports/export/profit/xls') ?>" class="btn btn-fm-outline btn-sm">Excel</a>
  </div>
</div>
<div class="fm-card"><div class="fm-card-body p-0">
<table class="fm-table"><thead><tr><th>WO #</th><th>Facility</th><th>Cost</th><th>Revenue</th><th>Profit</th></tr></thead>
<tbody>
<?php foreach ($rows as $r): ?>
<tr>
  <td class="fw-semibold"><?= esc($r['wo_number'] ?? '—') ?></td>
  <td><?= esc($r['facility_name'] ?? '—') ?></td>
  <td><?= number_format((float)($r['total_cost'] ?? 0), 2) ?></td>
  <td><?= number_format((float)($r['revenue'] ?? 0), 2) ?></td>
  <td class="<?= ((float)($r['profit'] ?? 0) >= 0) ? 'text-success' : 'text-danger' ?>"><?= number_format((float)($r['profit'] ?? 0), 2) ?></td>
</tr>
<?php endforeach; ?>
<?php if (empty($rows)): ?><tr><td colspan="5" class="text-center py-4 text-muted">No costing records — add via Maintenance Costing.</td></tr><?php endif; ?>
</tbody></table>
</div></div>
<?= $this->endSection() ?>
