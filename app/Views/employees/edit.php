<?= $this->extend('layouts/hr') ?>
<?= $this->section('hr_content') ?>
<div class="page-header"><div><h1><i class="bi bi-pencil me-2 text-primary"></i>Edit Employee</h1><p class="text-muted small mb-0"><?= esc($emp['emp_code']) ?></p></div></div>
<?= form_open(base_url('employees/update/'.$emp['id'])) ?>
<ul class="nav nav-tabs fm-tabs mb-3">
  <li class="nav-item"><button class="nav-link active" type="button" data-bs-toggle="tab" data-bs-target="#tab-personal">Personal</button></li>
  <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#tab-employment">Employment</button></li>
  <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#tab-identification">Identification</button></li>
</ul>
<div class="row g-3">
  <div class="col-lg-8">
    <div class="tab-content">
      <div class="tab-pane fade show active" id="tab-personal">
        <div class="fm-form-section"><?= view('employees/partials/form_personal', ['emp' => $emp, 'users' => [], 'options' => $options]) ?></div>
      </div>
      <div class="tab-pane fade" id="tab-employment">
        <div class="fm-form-section"><?= view('employees/partials/form_employment', ['emp' => $emp, 'options' => $options]) ?></div>
      </div>
      <div class="tab-pane fade" id="tab-identification">
        <div class="fm-form-section">
          <h6>Identification Documents</h6>
          <div class="row g-3">
            <div class="col-md-4"><label class="form-label">QID Number</label><input type="text" name="qid_number" class="form-control" value="<?= esc($emp['qid_number'] ?? '') ?>"></div>
            <div class="col-md-4"><label class="form-label">QID Issue</label><input type="date" name="qid_issue_date" class="form-control" value="<?= esc($emp['qid_issue_date'] ?? '') ?>"></div>
            <div class="col-md-4"><label class="form-label">QID Expiry</label><input type="date" name="qid_expiry" class="form-control" value="<?= esc($emp['qid_expiry'] ?? '') ?>"></div>
            <div class="col-md-4"><label class="form-label">Passport Number</label><input type="text" name="passport_number" class="form-control" value="<?= esc($emp['passport_number'] ?? '') ?>"></div>
            <div class="col-md-4"><label class="form-label">Passport Expiry</label><input type="date" name="passport_expiry" class="form-control" value="<?= esc($emp['passport_expiry'] ?? '') ?>"></div>
            <div class="col-md-4"><label class="form-label">Passport Country</label><input type="text" name="passport_country" class="form-control" value="<?= esc($emp['passport_country'] ?? '') ?>"></div>
            <div class="col-md-4"><label class="form-label">Visa Number</label><input type="text" name="visa_number" class="form-control" value="<?= esc($emp['visa_number'] ?? '') ?>"></div>
            <div class="col-md-4"><label class="form-label">Visa Type</label><input type="text" name="visa_type" class="form-control" value="<?= esc($emp['visa_type'] ?? '') ?>"></div>
            <div class="col-md-4"><label class="form-label">Visa Expiry</label><input type="date" name="visa_expiry" class="form-control" value="<?= esc($emp['visa_expiry'] ?? '') ?>"></div>
          </div>
          <hr>
          <h6>Bank Information</h6>
          <div class="row g-3">
            <div class="col-md-4"><label class="form-label">Bank Name</label><input type="text" name="bank_name" class="form-control" value="<?= esc($emp['bank_name'] ?? '') ?>"></div>
            <div class="col-md-4"><label class="form-label">Account Number</label><input type="text" name="bank_account_number" class="form-control" value="<?= esc($emp['bank_account_number'] ?? '') ?>"></div>
            <div class="col-md-4"><label class="form-label">IBAN</label><input type="text" name="iban" class="form-control" value="<?= esc($emp['iban'] ?? '') ?>"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="fm-form-section">
      <h6>Actions</h6>
      <button type="submit" class="btn btn-fm-primary w-100 mb-2">Save Changes</button>
      <a href="<?= base_url('employees/view/'.$emp['id']) ?>" class="btn btn-fm-outline w-100">Cancel</a>
    </div>
  </div>
</div>
<?= form_close() ?>
<?= $this->endSection() ?>
