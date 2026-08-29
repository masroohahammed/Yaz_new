<?= $this->extend('layouts/hr') ?>
<?= $this->section('hr_content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-check2-square me-2 text-primary"></i>Leave Approvals</h1>
    <p class="text-muted small mb-0">Pending requests — approval deducts balance and marks attendance as leave.</p>
  </div>
  <a href="<?= base_url('hr/leave') ?>" class="btn btn-fm-outline btn-sm">Leave Dashboard</a>
</div>

<?php if (!empty($migrationRequired)): ?>
<div class="alert alert-warning">Run <code>database/patch_hr_upgrade_complete.sql</code> to enable leave approvals.</div>
<?php else: ?>

<div class="fm-card">
  <div class="card-header-fm"><h5>Pending (<?= count($pending) ?>)</h5></div>
  <div class="fm-card-body p-0"><div class="table-responsive">
    <table class="fm-table">
      <thead><tr><th>Employee</th><th>Type</th><th>Period</th><th>Days</th><th>Reason</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($pending as $p): ?>
      <tr>
        <td><?= esc($p['employee_name'] ?? '—') ?> <span class="text-muted small">(<?= esc($p['emp_code'] ?? '') ?>)</span></td>
        <td class="small"><?= esc($p['leave_type_name'] ?? '') ?></td>
        <td class="small"><?= date('d M Y', strtotime($p['start_date'])) ?> – <?= date('d M Y', strtotime($p['end_date'])) ?></td>
        <td><?= number_format((float)$p['days_requested'], 1) ?></td>
        <td class="small"><?= esc($p['reason'] ?? '') ?></td>
        <td class="text-nowrap">
          <?= form_open(base_url('hr/leave/approve/'.$p['id']), ['class' => 'd-inline']) ?>
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-sm btn-success">Approve</button>
          <?= form_close() ?>
          <?= form_open(base_url('hr/leave/reject/'.$p['id']), ['class' => 'd-inline']) ?>
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-sm btn-fm-outline">Reject</button>
          <?= form_close() ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($pending)): ?><tr><td colspan="6" class="text-center py-4 text-muted">No pending leave requests.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div></div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>
