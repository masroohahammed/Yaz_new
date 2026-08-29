<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $qs = '?' . http_build_query(array_filter(['from'=>$filters['from']??'','to'=>$filters['to']??'','facility'=>$filters['facility']??''])); ?>
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div><h1><i class="bi bi-people me-2"></i>Customer Invoices</h1></div>
</div>
<?= view('reports/_report_toolbar', [
  'summaryUrl'   => base_url('reports/customer-invoices') . $qs,
  'exportCsv'    => base_url('reports/export/customer/csv'),
  'isDetailPage' => true,
]) ?>
<div class="alert alert-info small mb-3 no-print">
  <strong>Who is the “customer”?</strong> For each invoice we use, in order: the <em>service customer</em> on the linked work order,
  the work order <em>requester name</em>, or the <em>facility name</em> for on-site jobs. Non-facility jobs bill to the requester/customer name, not a facility.
</div>
<div class="report-print-area">
<form method="get" class="fm-card mb-3"><div class="fm-card-body py-2">
  <div class="row g-2 align-items-end">
    <div class="col-md-3"><label class="form-label small">From</label><input type="date" name="from" class="form-control form-control-sm" value="<?= esc($filters['from'] ?? date('Y-m-01')) ?>"></div>
    <div class="col-md-3"><label class="form-label small">To</label><input type="date" name="to" class="form-control form-control-sm" value="<?= esc($filters['to'] ?? date('Y-m-d')) ?>"></div>
    <div class="col-md-3"><label class="form-label small">Facility</label>
      <select name="facility" class="form-select form-select-sm">
        <option value="">All</option>
        <?php foreach ($facilities ?? [] as $f): ?>
        <option value="<?= $f['id'] ?>" <?= (string)($filters['facility'] ?? '') === (string)$f['id'] ? 'selected' : '' ?>><?= esc($f['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-auto"><button type="submit" class="btn btn-fm-primary btn-sm">Run</button></div>
  </div>
</div></form>
<div class="fm-card"><div class="fm-card-body p-0 overflow-auto">
<table class="fm-table"><thead><tr>
  <?php foreach ($headers as $h): ?><th><?= esc($h) ?></th><?php endforeach; ?>
</tr></thead><tbody>
<?php foreach ($rows as $row): ?><tr>
  <?php foreach ($row as $cell): ?><td class="small"><?= esc((string) $cell) ?></td><?php endforeach; ?>
</tr><?php endforeach; ?>
<?php if (empty($rows)): ?><tr><td colspan="<?= max(1, count($headers)) ?>" class="text-center py-4 text-muted">No invoices in range.</td></tr><?php endif; ?>
</tbody></table>
</div></div>
</div>
<?= $this->endSection() ?>
