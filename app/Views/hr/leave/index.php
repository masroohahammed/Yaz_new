<?= $this->extend('layouts/hr') ?>
<?= $this->section('hr_content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-calendar2-week me-2 text-primary"></i>Leave Management</h1>
    <p class="text-muted small mb-0">Balances, applications, and history — approved leave marks attendance automatically.</p>
  </div>
  <div class="d-flex gap-2">
    <?php if ($canApprove ?? false): ?>
    <a href="<?= base_url('hr/leave/approvals') ?>" class="btn btn-fm-outline btn-sm">Approvals</a>
    <?php endif; ?>
  </div>
</div>

<?php if (!empty($migrationRequired)): ?>
<div class="alert alert-warning">Run <code>database/patch_hr_upgrade_complete.sql</code> to enable leave management.</div>
<?php else: ?>

<div class="row g-3 mb-3">
  <div class="col-lg-4">
    <?= form_open(base_url('hr/leave'), ['method' => 'get', 'class' => 'fm-card']) ?>
    <div class="card-header-fm"><h5>Select Employee</h5></div>
    <div class="fm-card-body">
      <select name="employee_id" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">— Choose —</option>
        <?php foreach ($employees as $e): ?>
        <option value="<?= (int)$e['id'] ?>" <?= ($selectedEmp ?? '') == $e['id'] ? 'selected' : '' ?>><?= esc($e['name']) ?> (<?= esc($e['emp_code']) ?>)</option>
        <?php endforeach; ?>
      </select>
      <?php if ($canApprove && !empty($selectedEmp)): ?>
      <?= form_open(base_url('hr/leave/init-balances/'.$selectedEmp), ['class' => 'mt-2']) ?>
      <?= csrf_field() ?>
      <button type="submit" class="btn btn-sm btn-fm-outline w-100">Initialize Balances</button>
      <?= form_close() ?>
      <?php endif; ?>
    </div>
    <?= form_close() ?>

    <?php if (!empty($selectedEmp) && !empty($balances)): ?>
    <div class="fm-card mt-3">
      <div class="card-header-fm"><h5>Balances (<?= date('Y') ?>)</h5></div>
      <div class="fm-card-body p-0"><table class="fm-table table-sm">
        <thead><tr><th>Type</th><th>Available</th><th>Used</th><th>Pending</th></tr></thead>
        <tbody>
        <?php foreach ($balances as $b):
          $avail = (float)$b['opening_balance'] + (float)$b['accrued'] + (float)$b['adjusted'] - (float)$b['used'] - (float)$b['pending'];
        ?>
        <tr>
          <td class="small"><?= esc($b['type_name']) ?></td>
          <td class="small fw-semibold"><?= number_format(max(0,$avail), 1) ?></td>
          <td class="small"><?= number_format((float)$b['used'], 1) ?></td>
          <td class="small"><?= number_format((float)$b['pending'], 1) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>
    </div>
    <?php endif; ?>
  </div>

  <div class="col-lg-8">
    <?php if (($canApply ?? false) && !empty($selectedEmp)): ?>
    <div class="fm-card mb-3">
      <div class="card-header-fm"><h5>Apply for Leave</h5></div>
      <div class="fm-card-body">
        <?= form_open(base_url('hr/leave/apply')) ?>
        <?= csrf_field() ?>
        <input type="hidden" name="employee_id" value="<?= (int)$selectedEmp ?>">
        <div class="row g-2">
          <div class="col-md-4"><label class="small">Leave Type</label><select name="leave_type_id" class="form-select form-select-sm" required><?php foreach ($leaveTypes as $t): ?><option value="<?= (int)$t['id'] ?>"><?= esc($t['name']) ?></option><?php endforeach; ?></select></div>
          <div class="col-md-3"><label class="small">From</label><input type="date" name="start_date" class="form-control form-control-sm" required></div>
          <div class="col-md-3"><label class="small">To</label><input type="date" name="end_date" class="form-control form-control-sm"></div>
          <div class="col-md-2"><label class="small">Half Day</label><select name="half_day" class="form-select form-select-sm"><option value="none">Full</option><option value="first_half">1st Half</option><option value="second_half">2nd Half</option></select></div>
          <div class="col-12"><label class="small">Reason</label><textarea name="reason" class="form-control form-control-sm" rows="2" required></textarea></div>
          <div class="col-12"><button type="submit" class="btn btn-fm-primary btn-sm">Submit Request</button></div>
        </div>
        <?= form_close() ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="fm-card">
      <div class="card-header-fm"><h5>Leave History</h5></div>
      <div class="fm-card-body p-0"><div class="table-responsive">
        <table class="fm-table">
          <thead><tr><th>Employee</th><th>Type</th><th>Period</th><th>Days</th><th>Status</th></tr></thead>
          <tbody>
          <?php foreach ($requests as $r): ?>
          <tr>
            <td class="small"><?= esc($r['employee_name'] ?? '—') ?></td>
            <td class="small"><?= esc($r['leave_type_name'] ?? '') ?></td>
            <td class="small"><?= date('d M Y', strtotime($r['start_date'])) ?><?= $r['end_date'] !== $r['start_date'] ? ' – '.date('d M Y', strtotime($r['end_date'])) : '' ?></td>
            <td><?= number_format((float)$r['days_requested'], 1) ?></td>
            <td><span class="fm-badge badge-status-<?= esc($r['status']) ?>"><?= esc($statuses[$r['status']] ?? ucfirst($r['status'])) ?></span></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($requests)): ?><tr><td colspan="5" class="text-center py-4 text-muted">No leave requests yet.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div></div>
    </div>
  </div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>
