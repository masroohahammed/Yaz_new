<?= $this->extend('layouts/hr') ?>
<?= $this->section('hr_content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-gear me-2 text-primary"></i>HR Settings</h1>
    <p class="text-muted small mb-0">Configurable employee types, employment sources, statuses, departments, and designations.</p>
  </div>
  <a href="<?= base_url('employees') ?>" class="btn btn-fm-outline btn-sm">Back to Workforce</a>
</div>

<?php if (!empty($migrationRequired)): ?>
<div class="alert alert-warning"><i class="bi bi-database-exclamation me-2"></i>Run <code>database/patch_hr_upgrade_complete.sql</code> or <code>php spark hr:schema</code> then <code>php spark migrate</code>.</div>
<?php else: ?>

<ul class="nav nav-tabs fm-tabs mb-3">
  <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-types" type="button">Employee Types</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-sources" type="button">Employment Sources</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-statuses" type="button">Employee Statuses</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-depts" type="button">Departments</button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-desigs" type="button">Designations</button></li>
</ul>

<div class="tab-content">
  <?php
  $sections = [
    'tab-types'    => ['table' => 'hr_employee_types', 'rows' => $employeeTypes ?? [], 'label' => 'Employee Type'],
    'tab-sources'  => ['table' => 'hr_employment_sources', 'rows' => $employmentSources ?? [], 'label' => 'Employment Source'],
    'tab-statuses' => ['table' => 'hr_employee_statuses', 'rows' => $employeeStatuses ?? [], 'label' => 'Employee Status'],
    'tab-depts'    => ['table' => 'hr_departments', 'rows' => $departments ?? [], 'label' => 'Department'],
    'tab-desigs'   => ['table' => 'hr_designations', 'rows' => $designations ?? [], 'label' => 'Designation'],
  ];
  $first = true;
  foreach ($sections as $tabId => $sec):
  ?>
  <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" id="<?= $tabId ?>">
    <?php $first = false; ?>
    <div class="row g-3">
      <div class="col-lg-8">
        <div class="fm-card"><div class="card-header-fm"><h5><?= esc($sec['label']) ?>s</h5></div>
        <div class="fm-card-body p-0"><table class="fm-table"><thead><tr><th>Code</th><th>Name</th></tr></thead><tbody>
        <?php foreach ($sec['rows'] as $row): ?><tr><td class="small"><code><?= esc($row['code']) ?></code></td><td><?= esc($row['name']) ?></td></tr><?php endforeach; ?>
        <?php if (empty($sec['rows'])): ?><tr><td colspan="2" class="text-center py-3 text-muted">No records</td></tr><?php endif; ?>
        </tbody></table></div></div>
      </div>
      <div class="col-lg-4">
        <div class="fm-form-section">
          <h6>Add <?= esc($sec['label']) ?></h6>
          <?= form_open(base_url('hr/settings/store')) ?>
          <?= csrf_field() ?>
          <input type="hidden" name="table" value="<?= esc($sec['table']) ?>">
          <div class="mb-2"><label class="form-label small">Code</label><input type="text" name="code" class="form-control form-control-sm" required placeholder="e.g. permanent"></div>
          <div class="mb-2"><label class="form-label small">Name</label><input type="text" name="name" class="form-control form-control-sm" required></div>
          <?php if ($sec['table'] === 'hr_employment_sources'): ?>
          <div class="mb-2"><label class="form-label small">Payroll Responsibility</label>
            <select name="payroll_responsibility" class="form-select form-select-sm">
              <option value="our_company">Our Company Payroll</option>
              <option value="supplier">Supplier Payroll</option>
              <option value="consultant">Consultant Payment</option>
              <option value="none">Non-Payroll</option>
            </select>
          </div>
          <?php endif; ?>
          <button type="submit" class="btn btn-fm-primary btn-sm w-100">Add</button>
          <?= form_close() ?>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<?= $this->endSection() ?>
