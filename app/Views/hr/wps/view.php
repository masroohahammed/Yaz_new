<?= $this->extend('layouts/hr') ?>
<?= $this->section('hr_content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-bank2 me-2 text-primary"></i><?= esc($batch['batch_number'] ?? 'WPS Batch') ?></h1>
    <p class="text-muted small mb-0"><?= date('d M Y', strtotime($batch['period_start'])) ?> – <?= date('d M Y', strtotime($batch['period_end'])) ?></p>
  </div>
  <div class="d-flex gap-2">
    <?php if (!empty($batch['file_content'])): ?>
    <a href="<?= base_url('hr/wps/download/'.$batch['id']) ?>" class="btn btn-fm-primary btn-sm">Download CSV</a>
    <?php endif; ?>
    <a href="<?= base_url('hr/wps') ?>" class="btn btn-fm-outline btn-sm">Back</a>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-3"><div class="fm-card"><div class="fm-card-body small"><div class="text-muted">Records</div><strong><?= (int)$batch['record_count'] ?></strong></div></div></div>
  <div class="col-md-3"><div class="fm-card"><div class="fm-card-body small"><div class="text-muted">Total</div><strong><?= number_format((float)$batch['total_amount'], 2) ?></strong></div></div></div>
  <div class="col-md-3"><div class="fm-card"><div class="fm-card-body small"><div class="text-muted">Status</div><span class="fm-badge badge-status-<?= esc($batch['status']) ?>"><?= esc(ucfirst($batch['status'])) ?></span></div></div></div>
  <div class="col-md-3"><div class="fm-card"><div class="fm-card-body small"><div class="text-muted">Payroll</div><?= esc($batch['run_number'] ?? '—') ?></div></div></div>
</div>

<div class="fm-card"><div class="card-header-fm"><h5>WPS Records</h5></div>
<div class="fm-card-body p-0"><table class="fm-table table-sm"><thead><tr><th>Employee</th><th>QID</th><th>IBAN</th><th>Amount</th><th>Status</th></tr></thead><tbody>
<?php foreach ($records as $r): ?>
<tr>
  <td class="small"><?= esc($r['employee_name']) ?> <span class="text-muted">(<?= esc($r['emp_code']) ?>)</span></td>
  <td class="small"><?= esc($r['qid_number'] ?: '—') ?></td>
  <td class="small"><?= esc($r['iban'] ?: '—') ?></td>
  <td><?= number_format((float)$r['amount'], 2) ?></td>
  <td><span class="fm-badge badge-status-<?= esc($r['status']) ?>"><?= esc(ucfirst($r['status'])) ?></span><?php if (!empty($r['validation_message'])): ?><div class="text-danger small"><?= esc($r['validation_message']) ?></div><?php endif; ?></td>
</tr>
<?php endforeach; ?>
<?php if (empty($records)): ?><tr><td colspan="5" class="text-center py-3 text-muted">No records.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?= $this->endSection() ?>
