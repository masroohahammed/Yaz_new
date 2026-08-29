<?php
/** @var array<string, mixed> $emp */
/** @var array<string, list<array<string, mixed>>> $options */
$options = $options ?? [];
$isEdit  = ! empty($emp);
?>
<div class="row g-3">
  <div class="col-md-4">
    <label class="form-label">First Name</label>
    <input type="text" name="first_name" class="form-control" value="<?= esc(old('first_name', $emp['first_name'] ?? '')) ?>">
  </div>
  <div class="col-md-4">
    <label class="form-label">Middle Name</label>
    <input type="text" name="middle_name" class="form-control" value="<?= esc(old('middle_name', $emp['middle_name'] ?? '')) ?>">
  </div>
  <div class="col-md-4">
    <label class="form-label">Last Name</label>
    <input type="text" name="last_name" class="form-control" value="<?= esc(old('last_name', $emp['last_name'] ?? '')) ?>">
  </div>
  <div class="col-md-6">
    <label class="form-label">Arabic Name</label>
    <input type="text" name="name_ar" class="form-control" dir="rtl" value="<?= esc(old('name_ar', $emp['name_ar'] ?? '')) ?>">
  </div>
  <div class="col-md-3">
    <label class="form-label">Gender</label>
    <select name="gender" class="form-select">
      <option value="">—</option>
      <?php foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $val => $label): ?>
      <option value="<?= $val ?>" <?= old('gender', $emp['gender'] ?? '') === $val ? 'selected' : '' ?>><?= $label ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-3">
    <label class="form-label">Date of Birth</label>
    <input type="date" name="date_of_birth" class="form-control" value="<?= esc(old('date_of_birth', $emp['date_of_birth'] ?? '')) ?>">
  </div>
  <div class="col-md-4">
    <label class="form-label">Nationality</label>
    <input type="text" name="nationality" class="form-control" value="<?= esc(old('nationality', $emp['nationality'] ?? '')) ?>">
  </div>
  <div class="col-md-4">
    <label class="form-label">Marital Status</label>
    <input type="text" name="marital_status" class="form-control" value="<?= esc(old('marital_status', $emp['marital_status'] ?? '')) ?>">
  </div>
  <div class="col-md-4">
    <label class="form-label">Personal Mobile</label>
    <input type="text" name="personal_mobile" class="form-control" value="<?= esc(old('personal_mobile', $emp['personal_mobile'] ?? '')) ?>">
  </div>
  <div class="col-md-6">
    <label class="form-label">Personal Email</label>
    <input type="email" name="personal_email" class="form-control" value="<?= esc(old('personal_email', $emp['personal_email'] ?? '')) ?>">
  </div>
  <div class="col-md-6">
    <label class="form-label">Link to User Account</label>
    <select name="user_id" class="form-select">
      <option value="">— No linked account —</option>
      <?php foreach ($users ?? [] as $u): ?>
      <option value="<?= (int)$u['id'] ?>" <?= (string)old('user_id', $emp['user_id'] ?? '') === (string)$u['id'] ? 'selected' : '' ?>><?= esc($u['name']) ?> (<?= esc($u['email']) ?>)</option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-12">
    <label class="form-label">Current Address</label>
    <textarea name="current_address" class="form-control" rows="2"><?= esc(old('current_address', $emp['current_address'] ?? '')) ?></textarea>
  </div>
  <div class="col-12">
    <label class="form-label">Permanent Address</label>
    <textarea name="permanent_address" class="form-control" rows="2"><?= esc(old('permanent_address', $emp['permanent_address'] ?? '')) ?></textarea>
  </div>
  <div class="col-md-4">
    <label class="form-label">Emergency Contact</label>
    <input type="text" name="emergency_contact_name" class="form-control" value="<?= esc(old('emergency_contact_name', $emp['emergency_contact_name'] ?? '')) ?>">
  </div>
  <div class="col-md-4">
    <label class="form-label">Relationship</label>
    <input type="text" name="emergency_contact_relationship" class="form-control" value="<?= esc(old('emergency_contact_relationship', $emp['emergency_contact_relationship'] ?? '')) ?>">
  </div>
  <div class="col-md-4">
    <label class="form-label">Emergency Phone</label>
    <input type="text" name="emergency_contact_phone" class="form-control" value="<?= esc(old('emergency_contact_phone', $emp['emergency_contact_phone'] ?? '')) ?>">
  </div>
</div>
