<?php
/** @var array<string, mixed> $emp */
/** @var list<array<string, mixed>> $leaveRequests */
/** @var list<array<string, mixed>> $leaveBalances */
/** @var array<string, bool> $perms */
$perms = $perms ?? [];
?>
<div class="fm-form-section mb-3">
  <h6><i class="bi bi-calendar2-week"></i> Leave Balances (<?= date('Y') ?>)</h6>
  <?php if (empty($leaveBalances)): ?>
  <p class="text-muted small mb-0">No leave balances initialized.</p>
  <?php else: ?>
  <div class="table-responsive">
    <table class="fm-table table-sm">
      <thead><tr><th>Type</th><th>Available</th><th>Used</th><th>Pending</th></tr></thead>
      <tbody>
      <?php foreach ($leaveBalances as $b):
        $avail = (float)$b['opening_balance'] + (float)$b['accrued'] + (float)$b['adjusted'] - (float)$b['used'] - (float)$b['pending'];
      ?>
      <tr>
        <td class="small"><?= esc($b['type_name']) ?><?= empty($b['is_paid']) ? ' <span class="text-muted">(unpaid)</span>' : '' ?></td>
        <td class="small fw-semibold"><?= number_format(max(0, $avail), 1) ?></td>
        <td class="small"><?= number_format((float)$b['used'], 1) ?></td>
        <td class="small"><?= number_format((float)$b['pending'], 1) ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<div class="fm-form-section">
  <h6>Recent Requests</h6>
  <?php if (empty($leaveRequests)): ?>
  <p class="text-muted small mb-0">No leave requests on record.</p>
  <?php else: ?>
  <div class="table-responsive">
    <table class="fm-table table-sm">
      <thead><tr><th>Type</th><th>Period</th><th>Days</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach (array_slice($leaveRequests, 0, 10) as $r): ?>
      <tr>
        <td class="small"><?= esc($r['leave_type_name'] ?? '') ?></td>
        <td class="small"><?= date('d M Y', strtotime($r['start_date'])) ?><?= $r['end_date'] !== $r['start_date'] ? ' – '.date('d M Y', strtotime($r['end_date'])) : '' ?></td>
        <td class="small"><?= number_format((float)$r['days_requested'], 1) ?></td>
        <td><span class="fm-badge badge-status-<?= esc($r['status']) ?>"><?= esc(ucfirst($r['status'])) ?></span></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php if (!empty($perms['leave.apply']) && !empty($emp['leave_applicable'])): ?>
  <a href="<?= base_url('hr/leave?employee_id='.(int)$emp['id']) ?>" class="btn btn-sm btn-fm-outline mt-2">Apply for Leave</a>
  <?php endif; ?>
  <?php endif; ?>
</div>
