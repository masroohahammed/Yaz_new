<?= $this->extend('layouts/hr') ?>
<?= $this->section('hr_content') ?>
<?php
$activeTab = $activeTab ?? 'overview';
$statusLabel = $emp['status_name'] ?? ucfirst(str_replace('_', ' ', $emp['status']));
?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-person-badge me-2 text-primary"></i><?= esc($displayName ?? $emp['name'] ?? 'Employee') ?></h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb mb-0">
      <li class="breadcrumb-item"><a href="<?= base_url('employees') ?>">Employees</a></li>
      <li class="breadcrumb-item active"><?= esc($emp['emp_code']) ?></li>
    </ol></nav>
  </div>
  <?php if (!empty($perms['employee.edit'])): ?>
  <a href="<?= base_url('employees/edit/'.$emp['id']) ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
  <?php endif; ?>
</div>

<div class="row g-3 mb-3">
  <div class="col-lg-4">
    <div class="fm-form-section text-center">
      <div class="user-avatar mx-auto mb-3" style="width:64px;height:64px;font-size:1.5rem"><?= strtoupper(substr($displayName ?? $emp['name'] ?? '?', 0, 2)) ?></div>
      <h5 class="fw-bold"><?= esc($displayName ?? $emp['name'] ?? 'N/A') ?></h5>
      <?php if (!empty($emp['name_ar'])): ?><div class="small text-muted mb-1" dir="rtl"><?= esc($emp['name_ar']) ?></div><?php endif; ?>
      <div class="small text-muted mb-1"><?= esc($emp['designation_master_name'] ?? $emp['designation']) ?></div>
      <div class="small text-muted mb-3"><?= esc($emp['department_master_name'] ?? $emp['department']) ?></div>
      <span class="fm-badge badge-status-<?= esc($emp['status']) ?>"><?= esc($statusLabel) ?></span>
    </div>
  </div>
  <div class="col-lg-8">
    <div class="fm-form-section">
      <div class="row g-3 small">
        <div class="col-md-4"><div class="text-muted">Employee Code</div><div class="fw-semibold"><?= esc($emp['emp_code']) ?></div></div>
        <div class="col-md-4"><div class="text-muted">Company</div><div><?= esc($emp['company_name'] ?? '—') ?></div></div>
        <div class="col-md-4"><div class="text-muted">Operating Company</div><div><?= esc($emp['operating_company_name'] ?? '—') ?></div></div>
        <div class="col-md-4"><div class="text-muted">Employee Type</div><div><?= esc($emp['employee_type_name'] ?? '—') ?></div></div>
        <div class="col-md-4"><div class="text-muted">Employment Source</div><div><?= esc($emp['employment_source_name'] ?? '—') ?></div></div>
        <div class="col-md-4"><div class="text-muted">Reporting Manager</div><div><?= esc($emp['reporting_manager_name'] ?? '—') ?></div></div>
        <div class="col-md-4"><div class="text-muted">Joining Date</div><div><?= !empty($emp['joining_date']) ? date('d M Y', strtotime($emp['joining_date'])) : (!empty($emp['hire_date']) ? date('d M Y', strtotime($emp['hire_date'])) : '—') ?></div></div>
        <div class="col-md-4"><div class="text-muted">Shift</div><div><?= substr($emp['shift_start'], 0, 5) ?> – <?= substr($emp['shift_end'], 0, 5) ?></div></div>
      </div>
    </div>
  </div>
</div>

<ul class="nav nav-tabs fm-tabs mb-3">
  <li class="nav-item"><a class="nav-link <?= $activeTab === 'overview' ? 'active' : '' ?>" href="#tab-overview" data-bs-toggle="tab">Overview</a></li>
  <li class="nav-item"><a class="nav-link <?= $activeTab === 'personal' ? 'active' : '' ?>" href="#tab-personal" data-bs-toggle="tab">Personal</a></li>
  <li class="nav-item"><a class="nav-link <?= $activeTab === 'employment' ? 'active' : '' ?>" href="#tab-employment" data-bs-toggle="tab">Employment</a></li>
  <li class="nav-item"><a class="nav-link <?= $activeTab === 'attendance' ? 'active' : '' ?>" href="#tab-attendance" data-bs-toggle="tab">Attendance</a></li>
  <?php if ($canViewDocuments ?? false): ?>
  <li class="nav-item"><a class="nav-link <?= $activeTab === 'documents' ? 'active' : '' ?>" href="#tab-documents" data-bs-toggle="tab">Documents (<?= count($employeeDocuments ?? []) ?>)</a></li>
  <?php endif; ?>
  <?php if ($canViewContracts ?? false): ?>
  <li class="nav-item"><a class="nav-link <?= $activeTab === 'contract' ? 'active' : '' ?>" href="#tab-contract" data-bs-toggle="tab">Contracts (<?= count($contracts ?? []) ?>)</a></li>
  <?php endif; ?>
  <?php if ($canViewAssignments ?? false): ?>
  <li class="nav-item"><a class="nav-link <?= $activeTab === 'assignment' ? 'active' : '' ?>" href="#tab-assignment" data-bs-toggle="tab">Assignments (<?= count($assignments ?? []) ?>)</a></li>
  <?php endif; ?>
  <?php if ($canViewLeave ?? false): ?>
  <li class="nav-item"><a class="nav-link <?= $activeTab === 'leave' ? 'active' : '' ?>" href="#tab-leave" data-bs-toggle="tab">Leave</a></li>
  <?php endif; ?>
  <?php if ($canViewSalaryTab ?? false): ?>
  <li class="nav-item"><a class="nav-link <?= $activeTab === 'salary' ? 'active' : '' ?>" href="#tab-salary" data-bs-toggle="tab">Salary</a></li>
  <?php endif; ?>
  <li class="nav-item"><a class="nav-link <?= $activeTab === 'timeline' ? 'active' : '' ?>" href="#tab-timeline" data-bs-toggle="tab">Timeline</a></li>
</ul>

<div class="tab-content">
  <div class="tab-pane fade <?= $activeTab === 'overview' ? 'show active' : '' ?>" id="tab-overview">
    <div class="row g-3">
      <div class="col-lg-6">
        <div class="fm-card"><div class="card-header-fm"><h5><i class="bi bi-envelope"></i> Contact</h5></div>
        <div class="fm-card-body small">
          <div class="mb-2"><span class="text-muted">System Email:</span> <?= esc($emp['email'] ?? '—') ?></div>
          <div class="mb-2"><span class="text-muted">Personal Email:</span> <?= esc($emp['personal_email'] ?? '—') ?></div>
          <div class="mb-2"><span class="text-muted">Personal Mobile:</span> <?= esc($emp['personal_mobile'] ?? $emp['phone'] ?? '—') ?></div>
          <div><span class="text-muted">Emergency:</span> <?= esc($emp['emergency_contact_name'] ?? '—') ?> <?= !empty($emp['emergency_contact_phone']) ? '(' . esc($emp['emergency_contact_phone']) . ')' : '' ?></div>
        </div></div>
      </div>
      <div class="col-lg-6">
        <div class="fm-card"><div class="card-header-fm"><h5><i class="bi bi-sliders"></i> Applicability Flags</h5></div>
        <div class="fm-card-body small d-flex flex-wrap gap-2">
          <?php foreach (['wps_applicable'=>'WPS','payroll_applicable'=>'Payroll','leave_applicable'=>'Leave','attendance_applicable'=>'Attendance','overtime_applicable'=>'OT'] as $f=>$label): ?>
          <span class="badge <?= !empty($emp[$f]) ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?>"><?= $label ?>: <?= !empty($emp[$f]) ? 'Yes' : 'No' ?></span>
          <?php endforeach; ?>
        </div></div>
      </div>
    </div>
    <div class="fm-card mt-3"><div class="card-header-fm"><h5><i class="bi bi-tools"></i> Assigned Work Orders</h5></div>
    <div class="fm-card-body p-0"><table class="fm-table"><thead><tr><th>WO #</th><th>Title</th><th>Priority</th><th>Status</th></tr></thead><tbody>
    <?php foreach ($assignedWO as $w): ?><tr>
      <td><a href="<?= base_url('workorders/view/'.$w['id']) ?>" class="text-primary"><?= esc($w['wo_number']) ?></a></td>
      <td class="small"><?= esc(substr($w['title'], 0, 35)) ?></td>
      <td><span class="fm-badge badge-priority-<?= esc($w['priority']) ?>"><?= ucfirst($w['priority']) ?></span></td>
      <td><span class="fm-badge badge-status-<?= esc($w['status']) ?>"><?= ucfirst(str_replace('_', ' ', $w['status'])) ?></span></td>
    </tr><?php endforeach; ?>
    <?php if (empty($assignedWO)): ?><tr><td colspan="4" class="text-center py-3 text-muted">No work orders assigned</td></tr><?php endif; ?>
    </tbody></table></div></div>
  </div>

  <div class="tab-pane fade <?= $activeTab === 'personal' ? 'show active' : '' ?>" id="tab-personal">
    <div class="fm-form-section"><div class="row g-3 small">
      <div class="col-md-4"><div class="text-muted">Gender</div><div><?= esc(ucfirst($emp['gender'] ?? '—')) ?></div></div>
      <div class="col-md-4"><div class="text-muted">Date of Birth</div><div><?= !empty($emp['date_of_birth']) ? date('d M Y', strtotime($emp['date_of_birth'])) : '—' ?></div></div>
      <div class="col-md-4"><div class="text-muted">Nationality</div><div><?= esc($emp['nationality'] ?? '—') ?></div></div>
      <div class="col-md-4"><div class="text-muted">Marital Status</div><div><?= esc($emp['marital_status'] ?? '—') ?></div></div>
      <div class="col-12"><div class="text-muted">Current Address</div><div><?= nl2br(esc($emp['current_address'] ?? '—')) ?></div></div>
      <div class="col-12"><div class="text-muted">Permanent Address</div><div><?= nl2br(esc($emp['permanent_address'] ?? '—')) ?></div></div>
      <?php if ($canViewSensitive ?? false): ?>
      <div class="col-md-4"><div class="text-muted">QID</div><div><?= esc($emp['qid_number'] ?? '—') ?></div></div>
      <div class="col-md-4"><div class="text-muted">QID Expiry</div><div><?= !empty($emp['qid_expiry']) ? date('d M Y', strtotime($emp['qid_expiry'])) : '—' ?></div></div>
      <div class="col-md-4"><div class="text-muted">Passport</div><div><?= esc($emp['passport_number'] ?? '—') ?></div></div>
      <?php else: ?>
      <div class="col-12 text-muted"><i class="bi bi-lock me-1"></i>Sensitive identification fields are hidden.</div>
      <?php endif; ?>
    </div></div>
  </div>

  <div class="tab-pane fade <?= $activeTab === 'employment' ? 'show active' : '' ?>" id="tab-employment">
    <div class="fm-form-section"><div class="row g-3 small">
      <div class="col-md-4"><div class="text-muted">Grade</div><div><?= esc($emp['grade_name'] ?? '—') ?></div></div>
      <div class="col-md-4"><div class="text-muted">Cost Center</div><div><?= esc($emp['cost_center_name'] ?? '—') ?></div></div>
      <div class="col-md-4"><div class="text-muted">Payroll Responsibility</div><div><?= esc(ucwords(str_replace('_', ' ', $emp['payroll_responsibility'] ?? 'our_company'))) ?></div></div>
      <div class="col-md-4"><div class="text-muted">Confirmation Date</div><div><?= !empty($emp['confirmation_date']) ? date('d M Y', strtotime($emp['confirmation_date'])) : '—' ?></div></div>
      <div class="col-md-4"><div class="text-muted">Probation End</div><div><?= !empty($emp['probation_end_date']) ? date('d M Y', strtotime($emp['probation_end_date'])) : '—' ?></div></div>
      <?php if ($canViewSalary ?? false): ?>
      <div class="col-md-4"><div class="text-muted">Hourly Rate</div><div><?= $currency ?> <?= number_format((float)($emp['hourly_rate'] ?? 0), 2) ?></div></div>
      <?php endif; ?>
    </div></div>
  </div>

  <div class="tab-pane fade <?= $activeTab === 'attendance' ? 'show active' : '' ?>" id="tab-attendance">
    <div class="fm-card"><div class="card-header-fm"><h5><i class="bi bi-calendar-check"></i>Recent Attendance</h5>
    <a href="<?= base_url('employees/attendance?emp_id='.$emp['id']) ?>" class="btn btn-sm btn-fm-outline">Full Report</a></div>
    <div class="fm-card-body p-0"><table class="fm-table"><thead><tr><th>Date</th><th>Check In</th><th>Check Out</th><th>Hours</th><th>Status</th></tr></thead><tbody>
    <?php foreach (array_slice($attendance, 0, 15) as $a): ?><tr>
      <td class="small"><?= date('d M Y', strtotime($a['date'])) ?></td>
      <td class="small"><?= $a['check_in'] ? date('H:i', strtotime($a['check_in'])) : '—' ?></td>
      <td class="small"><?= $a['check_out'] ? date('H:i', strtotime($a['check_out'])) : '—' ?></td>
      <td class="small"><?= $a['hours_worked'] ?? '—' ?></td>
      <td><span class="fm-badge badge-status-<?= esc($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
    </tr><?php endforeach; ?>
    <?php if (empty($attendance)): ?><tr><td colspan="5" class="text-center py-3 text-muted">No attendance records</td></tr><?php endif; ?>
    </tbody></table></div></div>
  </div>

  <?php if ($canViewDocuments ?? false): ?>
  <div class="tab-pane fade <?= $activeTab === 'documents' ? 'show active' : '' ?>" id="tab-documents">
    <?= view('documents/panel', [
      'module'      => 'employee',
      'refId'       => (int)$emp['id'],
      'embed'       => true,
      'documents'   => $employeeDocuments ?? [],
      'categories'  => $docCategories ?? [],
      'facilityId'  => $emp['facility_id'] ?? null,
      'canUpload'   => $canUploadDocuments ?? false,
      'canDelete'   => $canUploadDocuments ?? false,
      'hrDocs'      => $hrDocService ?? null,
    ]) ?>
  </div>
  <?php endif; ?>

  <?php if ($canViewContracts ?? false): ?>
  <div class="tab-pane fade <?= $activeTab === 'contract' ? 'show active' : '' ?>" id="tab-contract">
    <?= view('employees/partials/tab_contracts', [
      'emp'              => $emp,
      'contracts'        => $contracts ?? [],
      'suppliers'        => $suppliers ?? [],
      'contractStatuses' => $contractStatuses ?? [],
      'perms'            => $perms ?? [],
    ]) ?>
  </div>
  <?php endif; ?>

  <?php if ($canViewAssignments ?? false): ?>
  <div class="tab-pane fade <?= $activeTab === 'assignment' ? 'show active' : '' ?>" id="tab-assignment">
    <?= view('employees/partials/tab_assignments', [
      'emp'                => $emp,
      'assignments'        => $assignments ?? [],
      'facilities'         => $facilities ?? [],
      'units'              => $units ?? [],
      'contracts'          => $contracts ?? [],
      'assignmentTypes'    => $assignmentTypes ?? [],
      'assignmentStatuses' => $assignmentStatuses ?? [],
      'perms'              => $perms ?? [],
    ]) ?>
  </div>
  <?php endif; ?>

  <?php if ($canViewLeave ?? false): ?>
  <div class="tab-pane fade <?= $activeTab === 'leave' ? 'show active' : '' ?>" id="tab-leave">
    <?= view('employees/partials/tab_leave', [
      'emp'            => $emp,
      'leaveRequests'  => $leaveRequests ?? [],
      'leaveBalances'  => $leaveBalances ?? [],
      'perms'          => $perms ?? [],
    ]) ?>
  </div>
  <?php endif; ?>

  <?php if ($canViewSalaryTab ?? false): ?>
  <div class="tab-pane fade <?= $activeTab === 'salary' ? 'show active' : '' ?>" id="tab-salary">
    <?= view('employees/partials/tab_salary', [
      'emp'             => $emp,
      'salaryStructure' => $salaryStructure ?? null,
      'salaryRevisions' => $salaryRevisions ?? [],
      'advances'        => $advances ?? [],
      'loans'           => $loans ?? [],
      'perms'           => $perms ?? [],
    ]) ?>
  </div>
  <?php endif; ?>

  <div class="tab-pane fade <?= $activeTab === 'timeline' ? 'show active' : '' ?>" id="tab-timeline">
    <?= view('employees/partials/tab_timeline', [
      'emp'            => $emp,
      'timelineEvents' => $timelineEvents ?? [],
    ]) ?>
  </div>
</div>
<?= $this->endSection() ?>
