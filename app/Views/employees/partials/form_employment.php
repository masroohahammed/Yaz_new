<?php
/** @var array<string, mixed> $emp */
/** @var array<string, list<array<string, mixed>>> $options */
$options = $options ?? [];
?>
<div class="row g-3">
  <?php if (!empty($options['companies']) && count($options['companies']) > 1): ?>
  <div class="col-md-6">
    <label class="form-label">Company</label>
    <select name="company_id" class="form-select">
      <?php foreach ($options['companies'] as $c): ?>
      <option value="<?= (int)$c['id'] ?>" <?= (int)old('company_id', $emp['company_id'] ?? 0) === (int)$c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php endif; ?>
  <?php if (!empty($options['operatingCompanies'])): ?>
  <div class="col-md-6">
    <label class="form-label">Operating Company</label>
    <select name="operating_company_id" class="form-select">
      <option value="">— Select —</option>
      <?php foreach ($options['operatingCompanies'] as $oc): ?>
      <option value="<?= (int)$oc['id'] ?>" <?= (int)old('operating_company_id', $emp['operating_company_id'] ?? 0) === (int)$oc['id'] ? 'selected' : '' ?>><?= esc($oc['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php endif; ?>
  <div class="col-md-6">
    <label class="form-label">Cost Center</label>
    <select name="cost_center_id" class="form-select">
      <option value="">— None —</option>
      <?php foreach ($options['costCenters'] ?? [] as $cc): ?>
      <option value="<?= (int)$cc['id'] ?>" <?= (int)old('cost_center_id', $emp['cost_center_id'] ?? 0) === (int)$cc['id'] ? 'selected' : '' ?>><?= esc($cc['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-6">
    <label class="form-label">Department <?= empty($options['departments']) ? '*' : '' ?></label>
    <?php if (!empty($options['departments'])): ?>
    <select name="department_id" class="form-select">
      <option value="">— Select —</option>
      <?php foreach ($options['departments'] as $d): ?>
      <option value="<?= (int)$d['id'] ?>" <?= (int)old('department_id', $emp['department_id'] ?? 0) === (int)$d['id'] ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <?php else: ?>
    <input type="text" name="department" class="form-control" value="<?= esc(old('department', $emp['department'] ?? '')) ?>" required>
    <?php endif; ?>
  </div>
  <div class="col-md-6">
    <label class="form-label">Designation <?= empty($options['designations']) ? '*' : '' ?></label>
    <?php if (!empty($options['designations'])): ?>
    <select name="designation_id" class="form-select">
      <option value="">— Select —</option>
      <?php foreach ($options['designations'] as $d): ?>
      <option value="<?= (int)$d['id'] ?>" <?= (int)old('designation_id', $emp['designation_id'] ?? 0) === (int)$d['id'] ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <?php else: ?>
    <input type="text" name="designation" class="form-control" value="<?= esc(old('designation', $emp['designation'] ?? '')) ?>" required>
    <?php endif; ?>
  </div>
  <?php if (!empty($options['grades'])): ?>
  <div class="col-md-4">
    <label class="form-label">Grade</label>
    <select name="grade_id" class="form-select">
      <option value="">— None —</option>
      <?php foreach ($options['grades'] as $g): ?>
      <option value="<?= (int)$g['id'] ?>" <?= (int)old('grade_id', $emp['grade_id'] ?? 0) === (int)$g['id'] ? 'selected' : '' ?>><?= esc($g['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php endif; ?>
  <?php if (!empty($options['employeeTypes'])): ?>
  <div class="col-md-4">
    <label class="form-label">Employee Type</label>
    <select name="employee_type_id" class="form-select">
      <?php foreach ($options['employeeTypes'] as $t): ?>
      <option value="<?= (int)$t['id'] ?>" <?= (int)old('employee_type_id', $emp['employee_type_id'] ?? 0) === (int)$t['id'] ? 'selected' : '' ?>><?= esc($t['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php endif; ?>
  <?php if (!empty($options['employmentSources'])): ?>
  <div class="col-md-4">
    <label class="form-label">Employment Source</label>
    <select name="employment_source_id" class="form-select">
      <?php foreach ($options['employmentSources'] as $s): ?>
      <option value="<?= (int)$s['id'] ?>" <?= (int)old('employment_source_id', $emp['employment_source_id'] ?? 0) === (int)$s['id'] ? 'selected' : '' ?>><?= esc($s['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php endif; ?>
  <?php if (!empty($options['employeeStatuses'])): ?>
  <div class="col-md-4">
    <label class="form-label">Employee Status</label>
    <select name="status_id" class="form-select">
      <?php foreach ($options['employeeStatuses'] as $s): ?>
      <option value="<?= (int)$s['id'] ?>" <?= (int)old('status_id', $emp['status_id'] ?? 0) === (int)$s['id'] ? 'selected' : '' ?>><?= esc($s['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php else: ?>
  <div class="col-md-4">
    <label class="form-label">Status</label>
    <select name="status" class="form-select">
      <?php foreach (['active','on_leave','inactive'] as $s): ?>
      <option value="<?= $s ?>" <?= old('status', $emp['status'] ?? 'active') === $s ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php endif; ?>
  <?php if (!empty($options['managers'])): ?>
  <div class="col-md-4">
    <label class="form-label">Reporting Manager</label>
    <select name="reporting_manager_id" class="form-select">
      <option value="">— None —</option>
      <?php foreach ($options['managers'] as $m): ?>
      <option value="<?= (int)$m['id'] ?>" <?= (int)old('reporting_manager_id', $emp['reporting_manager_id'] ?? 0) === (int)$m['id'] ? 'selected' : '' ?>><?= esc($m['name'] ?? $m['emp_code']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <?php endif; ?>
  <div class="col-md-3">
    <label class="form-label">Joining Date</label>
    <input type="date" name="joining_date" class="form-control" value="<?= esc(old('joining_date', $emp['joining_date'] ?? $emp['hire_date'] ?? '')) ?>">
  </div>
  <div class="col-md-3">
    <label class="form-label">Confirmation Date</label>
    <input type="date" name="confirmation_date" class="form-control" value="<?= esc(old('confirmation_date', $emp['confirmation_date'] ?? '')) ?>">
  </div>
  <div class="col-md-3">
    <label class="form-label">Probation End</label>
    <input type="date" name="probation_end_date" class="form-control" value="<?= esc(old('probation_end_date', $emp['probation_end_date'] ?? '')) ?>">
  </div>
  <div class="col-md-3">
    <label class="form-label">Hire Date (legacy)</label>
    <input type="date" name="hire_date" class="form-control" value="<?= esc(old('hire_date', $emp['hire_date'] ?? '')) ?>">
  </div>
  <div class="col-md-3">
    <label class="form-label">Shift Start</label>
    <input type="time" name="shift_start" class="form-control" value="<?= esc(old('shift_start', substr($emp['shift_start'] ?? '08:00:00', 0, 5))) ?>">
  </div>
  <div class="col-md-3">
    <label class="form-label">Shift End</label>
    <input type="time" name="shift_end" class="form-control" value="<?= esc(old('shift_end', substr($emp['shift_end'] ?? '17:00:00', 0, 5))) ?>">
  </div>
  <div class="col-md-3">
    <label class="form-label">Hourly Rate (<?= $currency ?>)</label>
    <input type="number" name="hourly_rate" step="0.01" class="form-control" value="<?= esc(old('hourly_rate', $emp['hourly_rate'] ?? 0)) ?>">
  </div>
  <div class="col-md-3">
    <label class="form-label">Payroll Responsibility</label>
    <select name="payroll_responsibility" class="form-select">
      <?php foreach (['our_company'=>'Our Company Payroll','supplier'=>'Supplier Payroll','external'=>'External Payroll','consultant'=>'Consultant Payment','none'=>'Non-Payroll'] as $val=>$label): ?>
      <option value="<?= $val ?>" <?= old('payroll_responsibility', $emp['payroll_responsibility'] ?? 'our_company') === $val ? 'selected' : '' ?>><?= $label ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-12">
    <div class="row g-2">
      <?php
      $flags = [
        'wps_applicable' => 'WPS Applicable',
        'payroll_applicable' => 'Payroll Applicable',
        'leave_applicable' => 'Leave Applicable',
        'attendance_applicable' => 'Attendance Applicable',
        'overtime_applicable' => 'Overtime Applicable',
      ];
      foreach ($flags as $field => $label):
        $checked = old($field, $emp[$field] ?? 1);
      ?>
      <div class="col-md-4 col-lg-2">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="<?= $field ?>" value="1" id="<?= $field ?>" <?= $checked ? 'checked' : '' ?>>
          <label class="form-check-label small" for="<?= $field ?>"><?= $label ?></label>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
