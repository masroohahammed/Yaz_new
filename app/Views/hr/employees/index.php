<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= $this->include('hr/_subnav', ['hrActive' => 'employees']) ?>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
  <div>
    <h1 class="h4 mb-0"><i class="bi bi-person-badge me-2"></i>Employees</h1>
    <p class="text-muted small mb-0">Profiles, compliance, and payroll salary setup</p>
  </div>
  <a href="<?= base_url('hr/employees/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>Add Employee</a>
</div>

<?php if ($migration): ?>
<div class="alert alert-warning">Run <code>database/pm_dms_hrms_patch.sql</code> to enable employee profiles.</div>
<?php else: ?>
<div class="hr-page-card">
  <table class="fm-table table-sm mb-0">
    <thead><tr><th>Code</th><th>Name</th><th>Department</th><th>Designation</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($employees as $e): ?>
    <tr>
      <td class="fw-semibold"><?= esc($e['employee_code']) ?></td>
      <td><?= esc($e['user_name'] ?? '') ?></td>
      <td class="small"><?= esc($e['department_name'] ?? '—') ?></td>
      <td class="small"><?= esc($e['designation_title'] ?? '—') ?></td>
      <td><span class="fm-badge badge-status-<?= esc($e['status']) ?>"><?= ucfirst(str_replace('_', ' ', $e['status'])) ?></span></td>
      <td class="text-end text-nowrap">
        <a href="<?= base_url('hr/employees/view/' . $e['id']) ?>" class="btn btn-sm btn-fm-outline">View</a>
        <a href="<?= base_url('hr/employees/' . $e['id'] . '/edit') ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($employees)): ?><tr><td colspan="6" class="text-muted text-center py-4">No employee profiles yet.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
