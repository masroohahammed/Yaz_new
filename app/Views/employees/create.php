<?= $this->extend('layouts/hr') ?>
<?= $this->section('hr_content') ?>
<div class="page-header"><div><h1><i class="bi bi-person-plus me-2 text-primary"></i>Add Employee</h1></div></div>
<?= form_open(base_url('employees/store')) ?>
<div class="row g-3">
  <div class="col-lg-8">
    <div class="fm-form-section mb-3">
      <h6><i class="bi bi-person"></i> Personal Information</h6>
      <?= view('employees/partials/form_personal', ['emp' => [], 'users' => $users, 'options' => $options]) ?>
    </div>
    <div class="fm-form-section">
      <h6><i class="bi bi-briefcase"></i> Employment Information</h6>
      <?= view('employees/partials/form_employment', ['emp' => [], 'options' => $options]) ?>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="fm-form-section">
      <h6>Actions</h6>
      <button type="submit" class="btn btn-fm-primary w-100 mb-2">Add Employee</button>
      <a href="<?= base_url('employees') ?>" class="btn btn-fm-outline w-100">Cancel</a>
    </div>
  </div>
</div>
<?= form_close() ?>
<?= $this->endSection() ?>
