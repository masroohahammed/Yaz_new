<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $qs = $queryString ?? ''; ?>
<div class="page-header"><div><h1><i class="bi bi-shield-check me-2"></i>SLA — Detailed (all work orders)</h1></div></div>
<?= view('reports/_report_toolbar', [
  'summaryUrl'   => base_url('reports/sla') . $qs,
  'exportCsv'    => base_url('reports/export/workorders/csv'),
  'isDetailPage' => true,
]) ?>
<div class="report-print-area fm-card"><div class="fm-card-body p-0 overflow-auto">
<table class="fm-table table-sm"><thead><tr>
  <th>WO #</th><th>Title</th><th>Facility</th><th>Priority</th><th>Assigned</th><th>Created</th><th>SLA Due</th><th>Completed</th><th>SLA</th><th>Status</th><th></th>
</tr></thead><tbody>
<?php foreach ($wos as $w): ?>
<tr class="<?= !empty($w['sla_breached']) ? 'table-danger' : '' ?>">
  <td class="fw-semibold small"><?= esc($w['wo_number']) ?></td>
  <td class="small"><?= esc(mb_strimwidth($w['title'] ?? '', 0, 40, '…')) ?></td>
  <td class="small"><?= $w['facility_name'] ? esc($w['facility_name']) : 'Non-facility' ?></td>
  <td><span class="fm-badge badge-priority-<?= esc($w['priority']) ?>"><?= ucfirst($w['priority']) ?></span></td>
  <td class="small"><?= esc($w['assigned_name'] ?? '—') ?></td>
  <td class="small text-muted"><?= date('d M Y H:i', strtotime($w['created_at'])) ?></td>
  <td class="small"><?= $w['sla_due'] ? date('d M Y H:i', strtotime($w['sla_due'])) : '—' ?></td>
  <td class="small"><?= $w['completed_at'] ? date('d M Y H:i', strtotime($w['completed_at'])) : '—' ?></td>
  <td class="small"><?= !empty($w['sla_breached']) ? '<span class="text-danger fw-bold">Breached</span>' : 'Met' ?></td>
  <td><span class="fm-badge badge-status-<?= esc($w['status']) ?>"><?= ucfirst(str_replace('_',' ',$w['status'])) ?></span></td>
  <td class="no-print"><a href="<?= base_url('workorders/view/'.$w['id']) ?>" class="btn btn-sm btn-fm-outline py-0">Open</a></td>
</tr>
<?php endforeach; ?>
<?php if (empty($wos)): ?><tr><td colspan="11" class="text-center py-4 text-muted">No data.</td></tr><?php endif; ?>
</tbody></table>
</div></div>
<?= $this->endSection() ?>
