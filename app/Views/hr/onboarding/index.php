<?= $this->extend('layouts/hr') ?>
<?= $this->section('hr_content') ?>
<div class="page-header"><div><h1><i class="bi bi-person-plus me-2 text-primary"></i>Employee Onboarding</h1></div></div>
<?php if (!empty($migrationRequired)): ?>
<div class="alert alert-warning">Run <code>database/patch_hr_upgrade_complete.sql</code> to enable onboarding.</div>
<?php else: ?>
<div class="row g-3">
  <div class="col-lg-4">
    <?= form_open(base_url('hr/onboarding'), ['method'=>'get','class'=>'fm-card']) ?>
    <div class="card-header-fm"><h5>Select Employee</h5></div>
    <div class="fm-card-body">
      <select name="employee_id" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">Choose…</option>
        <?php foreach ($employees as $e): ?>
        <option value="<?= (int)$e['id'] ?>" <?= ($selectedEmp ?? '') == $e['id'] ? 'selected' : '' ?>><?= esc($e['name']) ?> (<?= esc($e['emp_code']) ?>)</option>
        <?php endforeach; ?>
      </select>
    </div>
    <?= form_close() ?>
    <?php if ($selectedEmp && empty($instance)): ?>
    <?= form_open(base_url('hr/onboarding/start/'.$selectedEmp)) ?><?= csrf_field() ?>
    <button class="btn btn-fm-primary btn-sm mt-2">Start Onboarding</button><?= form_close() ?>
    <?php endif; ?>
  </div>
  <div class="col-lg-8">
    <?php if (!empty($instance)): ?>
    <div class="fm-card"><div class="card-header-fm"><h5>Checklist — <?= esc(ucfirst($instance['status'])) ?></h5></div>
    <div class="fm-card-body p-0"><table class="fm-table table-sm"><thead><tr><th>Task</th><th>Role</th><th>Status</th><th></th></tr></thead><tbody>
    <?php foreach ($tasks as $t): ?>
    <tr><td class="small"><?= esc($t['name']) ?></td><td class="small"><?= esc($t['assignee_role']) ?></td>
    <td><span class="fm-badge badge-status-<?= esc($t['status']) ?>"><?= esc(ucfirst($t['status'])) ?></span></td>
    <td><?php if ($t['status']==='pending'): ?><?= form_open(base_url('hr/onboarding/task/'.$t['id'].'/complete'),['class'=>'d-inline']) ?><?= csrf_field() ?><button class="btn btn-sm btn-success">Done</button><?= form_close() ?><?php endif; ?></td></tr>
    <?php endforeach; ?>
    </tbody></table></div></div>
    <?php elseif ($selectedEmp): ?><p class="text-muted">No onboarding instance — click Start Onboarding.</p><?php endif; ?>
  </div>
</div>
<?php endif; ?>
<?= $this->endSection() ?>
