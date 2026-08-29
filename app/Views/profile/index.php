<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-person-circle me-2 text-primary"></i>My Profile</h1></div></div>
<div class="row g-3">
<div class="col-lg-6">
<div class="fm-form-section"><h6><i class="bi bi-person"></i>Profile Information</h6>
<?= form_open(base_url('profile/update')) ?>
<div class="mb-3"><label class="form-label">Full Name</label><input type="text" name="name" class="form-control" value="<?= esc($currentUser['name']) ?>" required></div>
<div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control" value="<?= esc($currentUser['email']) ?>" disabled></div>
<div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="<?= esc($currentUser['phone']??'') ?>"></div>
<div class="mb-3"><label class="form-label">Role</label><input type="text" class="form-control" value="<?= esc($currentUser['role_display'] ?? $currentUser['role_name'] ?? '') ?>" disabled></div>
<button type="submit" class="btn btn-fm-primary">Update Profile</button>
<?= form_close() ?></div>
</div>
<div class="col-lg-6">
<div class="fm-form-section"><h6><i class="bi bi-lock"></i>Change Password</h6>
<?= form_open(base_url('profile/changePassword')) ?>
<div class="mb-3"><label class="form-label">Current Password</label><input type="password" name="current_password" class="form-control" required></div>
<div class="mb-3"><label class="form-label">New Password</label><input type="password" name="new_password" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Confirm New Password</label><input type="password" name="confirm_password" class="form-control" required></div>
<button type="submit" class="btn btn-fm-primary">Change Password</button>
<?= form_close() ?></div>
</div></div>
<?= $this->endSection() ?>
