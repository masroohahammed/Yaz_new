<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $qs = $queryString ?? ''; ?>
<div class="page-header"><div><h1><i class="bi bi-receipt me-2"></i>Finance — Detailed</h1></div></div>
<?= view('reports/_report_toolbar', [
  'summaryUrl'   => base_url('reports/finance') . $qs,
  'exportCsv'    => base_url('reports/export/finance/csv'),
  'isDetailPage' => true,
]) ?>
<div class="alert alert-light border small mb-3 no-print">
  <strong>Customer on invoice:</strong> <code>bill_to_name</code> from the work order requester or service customer when there is no facility; otherwise the facility name.
</div>
<div class="report-print-area">
<div class="fm-card mb-3"><div class="card-header-fm"><h5>All invoices</h5></div><div class="fm-card-body p-0 overflow-auto">
<table class="fm-table table-sm"><thead><tr>
  <th>Invoice #</th><th>Bill to / Customer</th><th>Facility</th><th>WO #</th><th>Issue</th><th>Due</th><th>Amount</th><th>Status</th><th></th>
</tr></thead><tbody>
<?php foreach ($invoices as $i):
  $billTo = $i['bill_to_name'] ?? $i['facility_name'] ?? 'Customer';
?>
<tr>
  <td class="fw-semibold small"><?= esc($i['invoice_number']) ?></td>
  <td class="small"><?= esc($billTo) ?><?= !empty($i['bill_to_address']) ? '<br><span class="text-muted">'.esc($i['bill_to_address']).'</span>' : '' ?></td>
  <td class="small text-muted"><?= esc($i['facility_name'] ?? '—') ?></td>
  <td class="small"><?= esc($i['wo_number'] ?? '—') ?></td>
  <td class="small"><?= date('d M Y', strtotime($i['issue_date'])) ?></td>
  <td class="small"><?= date('d M Y', strtotime($i['due_date'])) ?></td>
  <td class="small fw-bold"><?= esc($currency) ?> <?= number_format((float)$i['total'], 2) ?></td>
  <td><span class="fm-badge badge-status-<?= esc($i['status']) ?>"><?= ucfirst($i['status']) ?></span></td>
  <td class="no-print"><a href="<?= base_url('finance/invoices/view/'.$i['id']) ?>" class="btn btn-sm btn-fm-outline py-0">Open</a></td>
</tr>
<?php endforeach; ?>
<?php if (empty($invoices)): ?><tr><td colspan="9" class="text-center py-3 text-muted">No invoices.</td></tr><?php endif; ?>
</tbody></table>
</div></div>
<div class="fm-card"><div class="card-header-fm"><h5>All expenses</h5></div><div class="fm-card-body p-0 overflow-auto">
<table class="fm-table table-sm"><thead><tr><th>Date</th><th>Description</th><th>Category</th><th>Facility</th><th>By</th><th>Amount</th><th>Status</th></tr></thead><tbody>
<?php foreach ($expenses as $e): ?>
<tr>
  <td class="small"><?= date('d M Y', strtotime($e['expense_date'])) ?></td>
  <td class="small"><?= esc($e['description'] ?? $e['title'] ?? '—') ?></td>
  <td class="small"><?= esc($e['category'] ?? '—') ?></td>
  <td class="small"><?= esc($e['facility_name'] ?? '—') ?></td>
  <td class="small"><?= esc($e['created_by_name'] ?? '—') ?></td>
  <td class="small fw-bold"><?= esc($currency) ?> <?= number_format((float)($e['amount'] ?? 0), 2) ?></td>
  <td class="small"><?= esc($e['status'] ?? '—') ?></td>
</tr>
<?php endforeach; ?>
<?php if (empty($expenses)): ?><tr><td colspan="7" class="text-center py-3 text-muted">No expenses.</td></tr><?php endif; ?>
</tbody></table>
</div></div>
</div>
<?= $this->endSection() ?>
