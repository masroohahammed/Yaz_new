<?php
/** @var array<string, mixed> $emp */
/** @var list<array<string, mixed>> $assignments */
/** @var list<array<string, mixed>> $facilities */
/** @var list<array<string, mixed>> $units */
/** @var list<array<string, mixed>> $contracts */
/** @var array<string, bool> $perms */
$perms = $perms ?? [];
$canEdit = !empty($perms['employee.assignment.edit']);
?>
<div class="fm-form-section mb-3">
  <h6><i class="bi bi-geo-alt"></i> Site / Property Assignments</h6>
  <?php if (empty($assignments)): ?>
  <p class="text-muted small mb-0">No assignment history recorded.</p>
  <?php else: ?>
  <div class="table-responsive">
    <table class="fm-table table-sm">
      <thead><tr><th>Facility</th><th>Unit</th><th>Type</th><th>Period</th><th>Allocation</th><th>Status</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($assignments as $a): ?>
      <tr class="<?= !empty($a['is_current']) ? 'table-light' : '' ?>">
        <td class="small fw-semibold"><?= esc($a['facility_name'] ?? '—') ?><?= !empty($a['is_current']) ? ' <span class="badge bg-primary-subtle text-primary">Current</span>' : '' ?></td>
        <td class="small"><?= esc($a['unit_name'] ?? '—') ?></td>
        <td class="small"><?= esc($assignmentTypes[$a['assignment_type']] ?? ucfirst($a['assignment_type'])) ?></td>
        <td class="small"><?= $a['start_date'] ? date('d M Y', strtotime($a['start_date'])) : '—' ?> – <?= $a['end_date'] ? date('d M Y', strtotime($a['end_date'])) : 'Open' ?></td>
        <td class="small"><?= number_format((float)($a['allocation_pct'] ?? 100), 0) ?>%</td>
        <td><span class="fm-badge badge-status-<?= esc($a['assignment_status']) ?>"><?= esc($assignmentStatuses[$a['assignment_status']] ?? ucfirst($a['assignment_status'])) ?></span></td>
        <td class="text-nowrap">
          <?php if ($canEdit && !empty($a['is_current']) && $a['assignment_status'] === 'active'): ?>
          <?= form_open(base_url('hr/assignments/end/'.$a['id']), ['class' => 'd-inline']) ?>
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-sm btn-fm-outline" onclick="return confirm('End this assignment? History will be preserved.')">End</button>
          <?= form_close() ?>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php if ($canEdit): ?>
<div class="fm-form-section">
  <h6>Add Assignment</h6>
  <?= form_open(base_url('hr/assignments/store')) ?>
  <?= csrf_field() ?>
  <input type="hidden" name="employee_id" value="<?= (int)$emp['id'] ?>">
  <div class="row g-2">
    <div class="col-md-3"><label class="form-label small">Facility / Site</label>
      <select name="facility_id" class="form-select form-select-sm" required>
        <option value="">Select…</option>
        <?php foreach ($facilities as $f): ?>
        <option value="<?= (int)$f['id'] ?>"><?= esc($f['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php if (!empty($units)): ?>
    <div class="col-md-3"><label class="form-label small">Unit (optional)</label>
      <select name="unit_id" class="form-select form-select-sm"><option value="">—</option>
        <?php foreach ($units as $u): ?><option value="<?= (int)$u['id'] ?>"><?= esc($u['unit_number'] ?? '#'.$u['id']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>
    <div class="col-md-2"><label class="form-label small">Type</label>
      <select name="assignment_type" class="form-select form-select-sm">
        <?php foreach ($assignmentTypes as $code => $label): ?><option value="<?= esc($code) ?>"><?= esc($label) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-2"><label class="form-label small">Start</label><input type="date" name="start_date" class="form-control form-control-sm" value="<?= esc(date('Y-m-d')) ?>"></div>
    <div class="col-md-2"><label class="form-label small">End</label><input type="date" name="end_date" class="form-control form-control-sm"></div>
    <div class="col-md-2"><label class="form-label small">Allocation %</label><input type="number" step="1" min="1" max="100" name="allocation_pct" class="form-control form-control-sm" value="100"></div>
    <div class="col-md-3"><label class="form-label small">Role on Site</label><input name="role_on_site" class="form-control form-control-sm" placeholder="e.g. Lead Technician"></div>
    <?php if (!empty($contracts)): ?>
    <div class="col-md-3"><label class="form-label small">Linked Contract</label>
      <select name="contract_id" class="form-select form-select-sm"><option value="">—</option>
        <?php foreach ($contracts as $c): ?><option value="<?= (int)$c['id'] ?>"><?= esc($c['contract_number'] ?: '#'.$c['id']) ?></option><?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>
    <div class="col-md-4"><label class="form-label small">Remarks</label><input name="remarks" class="form-control form-control-sm"></div>
    <div class="col-md-2 d-flex align-items-end"><button type="submit" class="btn btn-fm-primary btn-sm w-100">Save</button></div>
  </div>
  <?= form_close() ?>
</div>
<?php elseif (!empty($perms['employee.assignment.view'])): ?>
<p class="text-muted small"><i class="bi bi-lock me-1"></i>Assignment editing requires <code>employee.assignment.edit</code> permission.</p>
<?php endif; ?>
