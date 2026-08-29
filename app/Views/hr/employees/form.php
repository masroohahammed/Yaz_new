<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= $this->include('hr/_subnav', ['hrActive' => 'employees']) ?>
<?php $e = $employee; $isEdit = ! empty($e['id']); ?>

<div class="page-header mb-3">
  <h1 class="h4 mb-0"><?= $isEdit ? 'Edit Employee' : 'Add Employee Profile' ?></h1>
  <p class="text-muted small mb-0">Link user account, employment details, and payroll salary</p>
</div>

<?= form_open(base_url('hr/employees/store')) ?>
<?= csrf_field() ?>
<div class="row g-3">
  <div class="col-lg-8">
    <div class="hr-page-card mb-3">
      <h6 class="text-muted text-uppercase small mb-3">Account & employment</h6>
      <div class="row g-2">
        <div class="col-md-6">
          <label class="form-label">Link to user *</label>
          <select name="user_id" class="form-select" required>
            <option value="">— Select user —</option>
            <?php foreach ($users as $u): ?>
            <option value="<?= $u['id'] ?>"><?= esc($u['name']) ?> (<?= esc($u['email']) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Employee code</label>
          <input type="text" name="employee_code" class="form-control" placeholder="Auto-generated if empty">
        </div>
        <div class="col-md-4">
          <label class="form-label">Department</label>
          <select name="department_id" class="form-select">
            <option value="">—</option>
            <?php foreach ($departments as $d): ?>
            <option value="<?= $d['id'] ?>"><?= esc($d['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Designation</label>
          <select name="designation_id" class="form-select">
            <option value="">—</option>
            <?php foreach ($designations as $d): ?>
            <option value="<?= $d['id'] ?>"><?= esc($d['title']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label">Reporting manager</label>
          <select name="manager_user_id" class="form-select">
            <option value="">—</option>
            <?php foreach ($managers as $m): ?>
            <option value="<?= $m['id'] ?>"><?= esc($m['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4"><label class="form-label">Hire date</label><input type="date" name="hire_date" class="form-control"></div>
        <div class="col-md-4">
          <label class="form-label">Employment type</label>
          <select name="employment_type" class="form-select">
            <?php foreach (['full_time','part_time','contract','intern'] as $t): ?>
            <option value="<?= $t ?>"><?= ucfirst(str_replace('_',' ',$t)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
        <div class="col-md-4"><label class="form-label">Basic salary</label><input type="number" step="0.01" name="basic_salary" class="form-control" value="0"></div>
        <div class="col-md-4"><label class="form-label">Allowances</label><input type="number" step="0.01" name="allowances" class="form-control" value="0"></div>
      </div>
    </div>
    <div class="hr-page-card">
      <h6 class="text-muted text-uppercase small mb-3">Identity & compliance</h6>
      <div class="row g-2">
        <div class="col-md-4"><label class="form-label">National ID</label><input type="text" name="national_id" class="form-control"></div>
        <div class="col-md-4"><label class="form-label">Passport no</label><input type="text" name="passport_no" class="form-control"></div>
        <div class="col-md-4"><label class="form-label">Passport expiry</label><input type="date" name="passport_expiry" class="form-control"></div>
        <div class="col-md-4"><label class="form-label">Visa expiry</label><input type="date" name="visa_expiry" class="form-control"></div>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="hr-page-card">
      <button type="submit" class="btn btn-fm-primary w-100 mb-2">Save Employee</button>
      <a href="<?= base_url('hr/employees') ?>" class="btn btn-fm-outline w-100">Cancel</a>
    </div>
  </div>
</div>
<?= form_close() ?>
<?= $this->endSection() ?>
