<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= $this->include('hr/_subnav', ['hrActive' => 'employees']) ?>
<?php
$e = $employee;
$isEdit = ! empty($e['id']);
$action = $formAction ?? base_url('hr/employees/store');
?>

<div class="page-header mb-3">
  <h1 class="h4 mb-0"><?= $isEdit ? 'Edit Employee' : 'Add Employee Profile' ?></h1>
  <p class="text-muted small mb-0">Link user account, employment details, and payroll salary</p>
</div>

<?= form_open($action, ['class' => 'fm-submit-form']) ?>
<?= csrf_field() ?>
<div class="row g-3">
  <div class="col-lg-8">
    <div class="hr-page-card mb-3">
      <h6 class="text-muted text-uppercase small mb-3">Account & employment</h6>
      <div class="row g-2">
        <div class="col-md-6">
          <label class="form-label">Link to user *</label>
          <select name="user_id" class="form-select" <?= $isEdit ? 'disabled' : 'required' ?>>
            <option value="">— Select user —</option>
            <?php foreach ($users as $u): ?>
            <option value="<?= (int) $u['id'] ?>" <?= (int) old('user_id', $e['user_id'] ?? 0) === (int) $u['id'] ? 'selected' : '' ?>><?= esc($u['name']) ?> (<?= esc($u['email']) ?>)</option>
            <?php endforeach; ?>
          </select>
          <?php if ($isEdit): ?><input type="hidden" name="user_id" value="<?= (int) ($e['user_id'] ?? 0) ?>"><?php endif; ?>
        </div>
        <div class="col-md-6">
          <label class="form-label">Employee code</label>
          <input type="text" name="employee_code" class="form-control" value="<?= esc(old('employee_code', $e['employee_code'] ?? '')) ?>" placeholder="Auto-generated if empty">
        </div>
        <div class="col-md-4">
          <label class="form-label">Department</label>
          <select name="department_id" class="form-select">
            <option value="">—</option>
            <?php foreach ($departments as $d): ?>
            <option value="<?= (int) $d['id'] ?>" <?= (int) old('department_id', $e['department_id'] ?? 0) === (int) $d['id'] ? 'selected' : '' ?>><?= esc($d['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Designation</label>
          <select name="designation_id" class="form-select">
            <option value="">—</option>
            <?php foreach ($designations as $d): ?>
            <option value="<?= (int) $d['id'] ?>" <?= (int) old('designation_id', $e['designation_id'] ?? 0) === (int) $d['id'] ? 'selected' : '' ?>><?= esc($d['title']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Reporting manager</label>
          <select name="manager_user_id" class="form-select">
            <option value="">—</option>
            <?php foreach ($managers as $m): ?>
            <option value="<?= (int) $m['id'] ?>" <?= (int) old('manager_user_id', $e['manager_user_id'] ?? 0) === (int) $m['id'] ? 'selected' : '' ?>><?= esc($m['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4"><label class="form-label">Hire date</label><input type="date" name="hire_date" class="form-control" value="<?= esc(old('hire_date', $e['hire_date'] ?? '')) ?>"></div>
        <div class="col-md-4">
          <label class="form-label">Employment type</label>
          <select name="employment_type" class="form-select">
            <?php foreach (['full_time','part_time','contract','intern'] as $t): ?>
            <option value="<?= $t ?>" <?= old('employment_type', $e['employment_type'] ?? 'full_time') === $t ? 'selected' : '' ?>><?= ucfirst(str_replace('_',' ',$t)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="<?= esc(old('phone', $e['phone'] ?? '')) ?>"></div>
        <div class="col-md-4"><label class="form-label">Basic salary</label><input type="number" step="0.01" name="basic_salary" class="form-control" value="<?= esc(old('basic_salary', $e['basic_salary'] ?? 0)) ?>"></div>
        <div class="col-md-4"><label class="form-label">Allowances</label><input type="number" step="0.01" name="allowances" class="form-control" value="<?= esc(old('allowances', $e['allowances'] ?? 0)) ?>"></div>
        <?php if ($isEdit): ?>
        <div class="col-md-4">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <?php foreach (['active','inactive','terminated'] as $s): ?>
            <option value="<?= $s ?>" <?= old('status', $e['status'] ?? 'active') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <div class="hr-page-card">
      <h6 class="text-muted text-uppercase small mb-3">Identity & compliance</h6>
      <div class="row g-2">
        <div class="col-md-4"><label class="form-label">National ID</label><input type="text" name="national_id" class="form-control" value="<?= esc(old('national_id', $e['national_id'] ?? '')) ?>"></div>
        <div class="col-md-4"><label class="form-label">Passport no</label><input type="text" name="passport_no" class="form-control" value="<?= esc(old('passport_no', $e['passport_no'] ?? '')) ?>"></div>
        <div class="col-md-4"><label class="form-label">Passport expiry</label><input type="date" name="passport_expiry" class="form-control" value="<?= esc(old('passport_expiry', $e['passport_expiry'] ?? '')) ?>"></div>
        <div class="col-md-4"><label class="form-label">Visa expiry</label><input type="date" name="visa_expiry" class="form-control" value="<?= esc(old('visa_expiry', $e['visa_expiry'] ?? '')) ?>"></div>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="hr-page-card">
      <button type="submit" class="btn btn-fm-primary w-100 mb-2 fm-submit-btn"><?= $isEdit ? 'Update Employee' : 'Save Employee' ?></button>
      <a href="<?= $isEdit ? base_url('hr/employees/view/' . (int) $e['id']) : base_url('hr/employees') ?>" class="btn btn-fm-outline w-100">Cancel</a>
    </div>
  </div>
</div>
<?= form_close() ?>
<?= $this->endSection() ?>
