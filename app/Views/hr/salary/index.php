<?= $this->extend('layouts/hr') ?>
<?= $this->section('hr_content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-cash-stack me-2 text-primary"></i>Salary Structures</h1>
    <p class="text-muted small mb-0">Component-based compensation — revisions are archived, never overwritten.</p>
  </div>
  <a href="<?= base_url('hr/compensation/advances') ?>" class="btn btn-fm-outline btn-sm">Advances</a>
</div>

<?php if (!empty($migrationRequired)): ?>
<div class="alert alert-warning">Run <code>database/patch_hr_upgrade_complete.sql</code> to enable salary structures.</div>
<?php else: ?>

<div class="row g-3">
  <div class="col-lg-4">
    <?= form_open(base_url('hr/salary'), ['method' => 'get', 'class' => 'fm-card']) ?>
    <div class="card-header-fm"><h5>Employee</h5></div>
    <div class="fm-card-body">
      <select name="employee_id" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">Select…</option>
        <?php foreach ($employees as $e): ?>
        <option value="<?= (int)$e['id'] ?>" <?= ($selectedEmp ?? '') == $e['id'] ? 'selected' : '' ?>><?= esc($e['name']) ?> (<?= esc($e['emp_code']) ?>)</option>
        <?php endforeach; ?>
      </select>
    </div>
    <?= form_close() ?>

    <?php if (!empty($structure)): ?>
    <div class="fm-card mt-3">
      <div class="card-header-fm"><h5>Current Structure</h5></div>
      <div class="fm-card-body small">
        <div class="mb-1">Effective: <?= date('d M Y', strtotime($structure['effective_from'])) ?></div>
        <div class="mb-1">Gross: <strong><?= number_format((float)$structure['gross_salary'], 2) ?> <?= esc($structure['currency']) ?></strong></div>
        <div>Net: <strong><?= number_format((float)$structure['net_salary'], 2) ?> <?= esc($structure['currency']) ?></strong></div>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <div class="col-lg-8">
    <?php if (($canEdit ?? false) && !empty($selectedEmp)): ?>
    <div class="fm-card mb-3">
      <div class="card-header-fm"><h5><?= empty($structure) ? 'Create' : 'Revise' ?> Salary Structure</h5></div>
      <div class="fm-card-body">
        <?= form_open(base_url('hr/salary/store')) ?>
        <?= csrf_field() ?>
        <input type="hidden" name="employee_id" value="<?= (int)$selectedEmp ?>">
        <div class="row g-2 mb-2">
          <div class="col-md-4"><label class="small">Effective From</label><input type="date" name="effective_from" class="form-control form-control-sm" value="<?= esc(date('Y-m-d')) ?>" required></div>
          <div class="col-md-8"><label class="small">Remarks</label><input name="remarks" class="form-control form-control-sm"></div>
        </div>
        <table class="fm-table table-sm">
          <thead><tr><th>Component</th><th>Type</th><th>Amount</th></tr></thead>
          <tbody>
          <?php foreach ($components as $c): ?>
          <?php $existing = null; foreach (($structure['lines'] ?? []) as $l) { if ((int)$l['component_id'] === (int)$c['id']) { $existing = $l; break; } } ?>
          <tr>
            <td class="small"><?= esc($c['name']) ?><input type="hidden" name="component_id[]" value="<?= (int)$c['id'] ?>"></td>
            <td class="small"><?= esc(ucfirst($c['component_type'])) ?></td>
            <td><input type="number" step="0.01" name="amount[]" class="form-control form-control-sm" value="<?= $existing ? number_format((float)$existing['amount'], 2, '.', '') : '0' ?>"></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <button type="submit" class="btn btn-fm-primary btn-sm mt-2">Save Structure</button>
        <?= form_close() ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($structure['lines'])): ?>
    <div class="fm-card mb-3"><div class="card-header-fm"><h5>Current Breakdown</h5></div>
    <div class="fm-card-body p-0"><table class="fm-table table-sm"><thead><tr><th>Component</th><th>Type</th><th>Amount</th></tr></thead><tbody>
    <?php foreach ($structure['lines'] as $l): ?>
    <tr><td class="small"><?= esc($l['name']) ?></td><td class="small"><?= esc($l['component_type']) ?></td><td><?= number_format((float)$l['amount'], 2) ?></td></tr>
    <?php endforeach; ?>
    </tbody></table></div></div>
    <?php endif; ?>

    <?php if (!empty($revisions)): ?>
    <div class="fm-card"><div class="card-header-fm"><h5>Revision History</h5></div>
    <div class="fm-card-body p-0"><table class="fm-table table-sm"><thead><tr><th>Date</th><th>Type</th><th>Old Gross</th><th>New Gross</th></tr></thead><tbody>
    <?php foreach ($revisions as $r): ?>
    <tr><td class="small"><?= !empty($r['effective_date']) ? date('d M Y', strtotime($r['effective_date'])) : '—' ?></td><td><?= esc($r['revision_type']) ?></td><td><?= $r['old_gross'] !== null ? number_format((float)$r['old_gross'],2) : '—' ?></td><td><?= number_format((float)$r['new_gross'],2) ?></td></tr>
    <?php endforeach; ?>
    </tbody></table></div></div>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>
