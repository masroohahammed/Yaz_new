<?= $this->extend('layouts/hr') ?>
<?= $this->section('hr_content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-bank2 me-2 text-primary"></i>WPS Batches</h1>
    <p class="text-muted small mb-0">Generate bank salary files from locked payroll runs.</p>
  </div>
  <a href="<?= base_url('hr/payroll') ?>" class="btn btn-fm-outline btn-sm">Payroll</a>
</div>

<?php if (!empty($migrationRequired)): ?>
<div class="alert alert-warning">Run <code>database/patch_hr_upgrade_complete.sql</code> to enable WPS.</div>
<?php else: ?>

<div class="fm-card mb-3">
  <div class="card-header-fm"><h5>Generate WPS Batch</h5></div>
  <div class="fm-card-body">
    <?= form_open(base_url('hr/wps/generate')) ?>
    <?= csrf_field() ?>
    <div class="row g-2 align-items-end">
      <div class="col-md-6">
        <label class="small">Locked Payroll Run</label>
        <select name="payroll_run_id" class="form-select form-select-sm" required>
          <option value="">Select…</option>
          <?php foreach ($eligibleRuns as $r): ?>
          <option value="<?= (int)$r['id'] ?>" <?= ($selectedRunId ?? null) === (int)$r['id'] ? 'selected' : '' ?>><?= esc($r['run_number']) ?> — <?= date('M Y', strtotime($r['period_start'])) ?> (<?= number_format((float)$r['total_net'],2) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3"><button type="submit" class="btn btn-fm-primary btn-sm">Generate</button></div>
    </div>
    <?= form_close() ?>
  </div>
</div>

<div class="fm-card"><div class="card-header-fm"><h5>Recent Batches</h5></div>
<div class="fm-card-body p-0"><table class="fm-table"><thead><tr><th>Batch #</th><th>Payroll</th><th>Branch</th><th>Records</th><th>Amount</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($batches as $b): ?>
<tr>
  <td class="small fw-semibold"><?= esc($b['batch_number']) ?></td>
  <td class="small"><?= esc($b['run_number'] ?? '—') ?></td>
  <td class="small"><?= esc($b['branch_name'] ?? '—') ?></td>
  <td><?= (int)$b['record_count'] ?></td>
  <td><?= number_format((float)$b['total_amount'], 2) ?></td>
  <td><span class="fm-badge badge-status-<?= esc($b['status']) ?>"><?= esc(ucfirst($b['status'])) ?></span></td>
  <td class="text-nowrap">
    <a href="<?= base_url('hr/wps/view/'.$b['id']) ?>" class="btn btn-sm btn-fm-outline">View</a>
    <?php if (!empty($b['file_content'])): ?>
    <a href="<?= base_url('hr/wps/download/'.$b['id']) ?>" class="btn btn-sm btn-fm-outline">Download</a>
    <?php endif; ?>
  </td>
</tr>
<?php endforeach; ?>
<?php if (empty($batches)): ?><tr><td colspan="7" class="text-center py-3 text-muted">No WPS batches yet.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php endif; ?>
<?= $this->endSection() ?>
