<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div><h1><i class="bi bi-patch-check me-2"></i>QC / QA Report</h1></div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('reports/export/qc/csv') ?>" class="btn btn-fm-outline btn-sm">CSV</a>
    <a href="<?= base_url('reports/export/qc/pdf') ?>" class="btn btn-fm-outline btn-sm">PDF</a>
  </div>
</div>
<div class="fm-card mb-3"><div class="fm-card-body py-2">
  <?= form_open(base_url('reports/qc'), ['method' => 'get', 'class' => 'row g-2 align-items-end']) ?>
  <div class="col-auto"><label class="small">From</label><input type="date" name="from" class="form-control form-control-sm" value="<?= esc($from) ?>"></div>
  <div class="col-auto"><label class="small">To</label><input type="date" name="to" class="form-control form-control-sm" value="<?= esc($to) ?>"></div>
  <div class="col-auto"><button class="btn btn-fm-primary btn-sm">Filter</button></div>
  <?= form_close() ?>
</div></div>
<div class="fm-card"><div class="fm-card-body p-0">
<table class="fm-table"><thead><tr><th>WO #</th><th>QA</th><th>QA date</th><th>Client</th><th>Stage</th><th>Facility</th></tr></thead>
<tbody>
<?php foreach ($rows as $r): ?>
<tr>
  <td class="fw-semibold"><?= esc($r['wo_number']) ?></td>
  <td><span class="fm-badge"><?= esc($r['qa_status']) ?></span></td>
  <td class="small"><?= $r['qa_approved_at'] ? date('d M Y', strtotime($r['qa_approved_at'])) : '—' ?></td>
  <td><?= esc($r['client_approval_status'] ?? '—') ?></td>
  <td class="small text-muted"><?= esc($r['workflow_stage'] ?? '') ?></td>
  <td><?= esc($r['facility_name'] ?? '—') ?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div></div>
<?= $this->endSection() ?>
