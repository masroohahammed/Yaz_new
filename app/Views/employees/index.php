<?= $this->extend('layouts/hr') ?>
<?= $this->section('hr_content') ?>
<?php $masters = $masters ?? []; $perms = $perms ?? []; ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-people-fill me-2 text-primary"></i>Employees</h1>
    <?php if (empty($hrReady)): ?>
    <p class="text-warning small mb-0"><i class="bi bi-exclamation-triangle me-1"></i>Run <code>database/patch_hr_upgrade_complete.sql</code> for full HR master integration.</p>
    <?php endif; ?>
  </div>
  <div class="d-flex gap-2">
    <?php if (!empty($perms['hr.settings'])): ?>
    <a href="<?= base_url('hr/settings') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-gear me-1"></i>HR Settings</a>
    <?php endif; ?>
    <?php if (!empty($perms['employee.create'])): ?>
    <a href="<?= base_url('employees/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Employee</a>
    <?php endif; ?>
  </div>
</div>

<div class="fm-card mb-3">
  <div class="fm-card-body">
    <?= form_open(base_url('employees'), ['method' => 'get', 'class' => 'row g-2 align-items-end']) ?>
    <div class="col-md-3">
      <label class="form-label small mb-1">Search</label>
      <input type="text" name="q" class="form-control form-control-sm" value="<?= esc($filters['search'] ?? '') ?>" placeholder="Code, name, QID, mobile…">
    </div>
    <?php if (!empty($masters['departments'])): ?>
    <div class="col-md-2">
      <label class="form-label small mb-1">Department</label>
      <select name="department_id" class="form-select form-select-sm">
        <option value="">All</option>
        <?php foreach ($masters['departments'] as $d): ?>
        <option value="<?= (int)$d['id'] ?>" <?= ($filters['department_id'] ?? '') == $d['id'] ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>
    <?php if (!empty($masters['employeeTypes'])): ?>
    <div class="col-md-2">
      <label class="form-label small mb-1">Type</label>
      <select name="employee_type_id" class="form-select form-select-sm">
        <option value="">All</option>
        <?php foreach ($masters['employeeTypes'] as $t): ?>
        <option value="<?= (int)$t['id'] ?>" <?= ($filters['employee_type_id'] ?? '') == $t['id'] ? 'selected' : '' ?>><?= esc($t['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>
    <div class="col-md-2">
      <button type="submit" class="btn btn-fm-primary btn-sm w-100">Filter</button>
    </div>
    <?= form_close() ?>
  </div>
</div>

<div class="fm-card">
  <div class="card-header-fm"><h5><i class="bi bi-people"></i>Employees (<?= (int)($totalCount ?? count($employees)) ?>)</h5></div>
  <div class="fm-card-body p-0"><div class="table-responsive">
    <table class="fm-table">
      <thead><tr><th>Code</th><th>Name</th><th>Type</th><th>Designation</th><th>Department</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
      <?php foreach ($employees as $e): ?>
      <tr>
        <td class="fw-semibold small"><?= esc($e['emp_code']) ?></td>
        <td>
          <div class="d-flex align-items-center gap-2">
            <div class="user-avatar"><?= strtoupper(substr($e['name'] ?? '?', 0, 2)) ?></div>
            <div>
              <div class="fw-semibold small"><?= esc($e['name'] ?? 'N/A') ?></div>
              <div class="x-small text-muted"><?= esc($e['email'] ?? '') ?></div>
            </div>
          </div>
        </td>
        <td class="small"><?= esc($e['employee_type_name'] ?? '—') ?></td>
        <td class="small"><?= esc($e['designation_master_name'] ?? $e['designation']) ?></td>
        <td class="small"><?= esc($e['department_master_name'] ?? $e['department']) ?></td>
        <td><span class="fm-badge badge-status-<?= esc($e['status']) ?>"><?= esc($e['status_name'] ?? ucfirst(str_replace('_', ' ', $e['status']))) ?></span></td>
        <td>
          <div class="d-flex gap-1">
            <a href="<?= base_url('employees/view/'.$e['id']) ?>" class="btn-action bg-primary bg-opacity-10 text-primary"><i class="bi bi-eye"></i></a>
            <?php if (!empty($perms['employee.edit'])): ?>
            <a href="<?= base_url('employees/edit/'.$e['id']) ?>" class="btn-action bg-warning bg-opacity-10 text-warning"><i class="bi bi-pencil"></i></a>
            <?php endif; ?>
            <?php if (!empty($perms['employee.delete'])): ?>
            <?= form_open(base_url('employees/delete/'.$e['id']), ['class' => 'd-inline']) ?>
            <?= csrf_field() ?>
            <button type="submit" class="btn-action bg-danger bg-opacity-10 text-danger border-0" onclick="return confirm('Deactivate this employee?')"><i class="bi bi-person-x"></i></button>
            <?= form_close() ?>
            <?php endif; ?>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($employees)): ?><tr><td colspan="7" class="text-center py-4 text-muted">No employees found</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div></div>
</div>
<?= $this->endSection() ?>
