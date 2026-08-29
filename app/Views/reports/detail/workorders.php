<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$qs = $queryString ?? '';
$summaryUrl = base_url('reports/workorders') . $qs;
?>
<div class="page-header"><div><h1><i class="bi bi-tools me-2"></i>Work Orders — Detailed</h1></div></div>
<?= view('reports/_report_toolbar', [
  'summaryUrl'  => $summaryUrl,
  'exportCsv'   => base_url('reports/export/workorders/csv'),
  'isDetailPage'=> true,
]) ?>
<div class="alert alert-light border small mb-3 no-print">
  <strong>Customer / location:</strong> Facility name when the job is on-site; otherwise requester name from the work order (non-facility jobs).
</div>
<div class="report-print-area fm-card"><div class="fm-card-body p-0 overflow-auto">
<table class="fm-table table-sm">
<thead><tr>
  <th>WO #</th><th>Title</th><th>Customer / Facility</th><th>Requester</th><th>Status</th><th>Priority</th><th>Assigned</th><th>Supervisor</th><th>Created</th><th>Completed</th><th>SLA Due</th><th>Cost</th><th></th>
</tr></thead>
<tbody>
<?php foreach ($wos as $w): ?>
<tr>
  <td class="small fw-semibold"><?= esc($w['wo_number']) ?></td>
  <td class="small"><?= esc($w['title']) ?></td>
  <td class="small"><?= $w['facility_name'] ? esc($w['facility_name']) : '<span class="text-muted">Non-facility</span>' ?></td>
  <td class="small"><?= esc($w['requester_name'] ?? '—') ?><?= !empty($w['requester_phone']) ? '<br><span class="text-muted">'.esc($w['requester_phone']).'</span>' : '' ?></td>
  <td><span class="fm-badge badge-status-<?= esc($w['status']) ?>"><?= ucfirst(str_replace('_',' ',$w['status'])) ?></span></td>
  <td><span class="fm-badge badge-priority-<?= esc($w['priority']) ?>"><?= ucfirst($w['priority']) ?></span></td>
  <td class="small"><?= esc($w['assigned_name'] ?? '—') ?></td>
  <td class="small"><?= esc($w['supervisor_name'] ?? '—') ?></td>
  <td class="small text-muted"><?= date('d M Y H:i', strtotime($w['created_at'])) ?></td>
  <td class="small text-muted"><?= $w['completed_at'] ? date('d M Y H:i', strtotime($w['completed_at'])) : '—' ?></td>
  <td class="small <?= !empty($w['sla_breached']) ? 'text-danger fw-bold' : '' ?>"><?= $w['sla_due'] ? date('d M Y H:i', strtotime($w['sla_due'])) : '—' ?></td>
  <td class="small"><?= number_format((float)($w['actual_cost'] ?? 0), 2) ?></td>
  <td><a href="<?= base_url('workorders/view/'.$w['id']) ?>" class="btn btn-sm btn-fm-outline py-0 no-print">Open</a></td>
</tr>
<?php endforeach; ?>
<?php if (empty($wos)): ?><tr><td colspan="13" class="text-center py-4 text-muted">No work orders in range.</td></tr><?php endif; ?>
</tbody>
</table>
</div></div>
<?= $this->endSection() ?>
