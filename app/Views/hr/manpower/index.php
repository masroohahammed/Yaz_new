<?= $this->extend('layouts/hr') ?>
<?= $this->section('hr_content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-diagram-3 me-2 text-primary"></i>Manpower Planning</h1>
    <p class="text-muted small mb-0">Required headcount vs active site assignments — uses existing facilities master.</p>
  </div>
  <a href="<?= base_url('employees') ?>" class="btn btn-fm-outline btn-sm">Workforce</a>
</div>

<?php if (!empty($migrationRequired)): ?>
<div class="alert alert-warning">Run <code>database/patch_hr_upgrade_complete.sql</code> to enable manpower planning.</div>
<?php else: ?>

<?php if ($canManage ?? false): ?>
<div class="fm-card mb-3">
  <div class="card-header-fm"><h5>Add Requirement</h5></div>
  <div class="fm-card-body">
    <?= form_open(base_url('hr/manpower/store')) ?>
    <?= csrf_field() ?>
    <div class="row g-2 align-items-end">
      <div class="col-md-3"><label class="form-label small">Facility</label>
        <select name="facility_id" class="form-select form-select-sm" required>
          <option value="">Select…</option>
          <?php foreach ($facilities as $f): ?><option value="<?= (int)$f['id'] ?>"><?= esc($f['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <?php if (!empty($masters['designations'])): ?>
      <div class="col-md-3"><label class="form-label small">Designation</label>
        <select name="designation_id" class="form-select form-select-sm"><option value="">Any</option>
          <?php foreach ($masters['designations'] as $d): ?><option value="<?= (int)$d['id'] ?>"><?= esc($d['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
      <div class="col-md-2"><label class="form-label small">Required</label><input type="number" min="1" name="required_headcount" class="form-control form-control-sm" value="1"></div>
      <div class="col-md-2"><label class="form-label small">Start</label><input type="date" name="start_date" class="form-control form-control-sm"></div>
      <div class="col-md-2"><button type="submit" class="btn btn-fm-primary btn-sm w-100">Add</button></div>
    </div>
    <?= form_close() ?>
  </div>
</div>
<?php endif; ?>

<div class="fm-card">
  <div class="card-header-fm"><h5>Requirements vs Assignments</h5></div>
  <div class="fm-card-body p-0"><div class="table-responsive">
    <table class="fm-table">
      <thead><tr><th>Facility</th><th>Designation</th><th>Required</th><th>Assigned</th><th>Gap</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($rows as $row): ?>
      <tr>
        <td><?= esc($row['facility_name'] ?? '—') ?></td>
        <td class="small"><?= esc($row['designation_name'] ?? 'Any') ?></td>
        <td><?= (int)$row['required_headcount'] ?></td>
        <td><?= (int)($row['assigned_headcount'] ?? 0) ?></td>
        <td>
          <?php $gap = (int)($row['gap'] ?? 0); ?>
          <span class="badge <?= $gap > 0 ? 'bg-danger-subtle text-danger' : ($gap < 0 ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success') ?>">
            <?= $gap > 0 ? '+'.$gap.' short' : ($gap < 0 ? abs($gap).' surplus' : 'Balanced') ?>
          </span>
        </td>
        <td><span class="fm-badge badge-status-<?= esc($row['status']) ?>"><?= esc(ucfirst($row['status'])) ?></span></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($rows)): ?><tr><td colspan="6" class="text-center py-4 text-muted">No manpower requirements defined yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div></div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>
