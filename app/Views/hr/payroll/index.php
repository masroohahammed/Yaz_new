<?= $this->extend('layouts/hr') ?>
<?= $this->section('hr_content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-calculator me-2 text-primary"></i>Payroll Runs</h1>
    <p class="text-muted small mb-0">Monthly payroll — calculate, approve, lock, and post to finance GL.</p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('hr/wps') ?>" class="btn btn-fm-outline btn-sm">WPS</a>
  </div>
</div>

<?php if (!empty($migrationRequired)): ?>
<div class="alert alert-warning">Run <code>database/patch_hr_upgrade_complete.sql</code> to enable payroll.</div>
<?php else: ?>

<div class="fm-card mb-3">
  <div class="card-header-fm"><h5>New Payroll Run</h5></div>
  <div class="fm-card-body">
    <?= form_open(base_url('hr/payroll/create')) ?>
    <?= csrf_field() ?>
    <div class="row g-2 align-items-end">
      <div class="col-md-3"><label class="small">Period (month)</label><input type="month" name="period_month" class="form-control form-control-sm" value="<?= esc(date('Y-m')) ?>" required></div>
      <div class="col-md-3"><label class="small">Operating Branch</label>
        <select name="branch_id" class="form-select form-select-sm">
          <option value="">All branches</option>
          <?php foreach ($branches as $b): ?>
          <option value="<?= (int)$b['id'] ?>"><?= esc($b['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3"><label class="small">Pay Date</label><input type="date" name="pay_date" class="form-control form-control-sm" value="<?= esc(date('Y-m-t')) ?>"></div>
      <div class="col-md-3"><button type="submit" class="btn btn-fm-primary btn-sm">Create Run</button></div>
    </div>
    <?= form_close() ?>
  </div>
</div>

<div class="fm-card"><div class="card-header-fm"><h5>Recent Runs</h5></div>
<div class="fm-card-body p-0"><table class="fm-table"><thead><tr><th>Run #</th><th>Period</th><th>Branch</th><th>Employees</th><th>Net</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($runs as $r): ?>
<tr>
  <td class="small fw-semibold"><?= esc($r['run_number']) ?></td>
  <td class="small"><?= date('d M Y', strtotime($r['period_start'])) ?> – <?= date('d M Y', strtotime($r['period_end'])) ?></td>
  <td class="small"><?= esc($r['branch_name'] ?? '—') ?></td>
  <td><?= (int)$r['employee_count'] ?></td>
  <td><?= number_format((float)$r['total_net'], 2) ?></td>
  <td><span class="fm-badge badge-status-<?= esc($r['status']) ?>"><?= esc($statuses[$r['status']] ?? $r['status']) ?></span></td>
  <td><a href="<?= base_url('hr/payroll/view/'.$r['id']) ?>" class="btn btn-sm btn-fm-outline">Open</a></td>
</tr>
<?php endforeach; ?>
<?php if (empty($runs)): ?><tr><td colspan="7" class="text-center py-3 text-muted">No payroll runs yet.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php endif; ?>
<?= $this->endSection() ?>
