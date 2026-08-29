<?php $companyName = $settings['company_name'] ?? 'FM ERP'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Register — <?= esc($companyName) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&display=swap" rel="stylesheet">
<link href="<?= base_url('assets/css/custom.css') ?>" rel="stylesheet">
<style>body{padding-top:0!important}</style>
</head>
<body>
<div class="auth-bg">
<div class="auth-card">
<div class="auth-logo"><i class="bi bi-person-plus-fill"></i></div>
<h4 class="text-center fw-bold mb-1" style="color:#0a3d6b">Create Account</h4>
<p class="text-center text-muted small mb-4"><?= esc($companyName) ?> — Client Portal Access</p>
<?php if(session()->getFlashdata('errors')): ?><div class="alert alert-danger border-0 rounded-3 small"><?php foreach((array)session()->getFlashdata('errors') as $e): ?><div><?= esc($e) ?></div><?php endforeach; ?></div><?php endif; ?>
<?= form_open('register') ?>
<div class="mb-3"><label class="form-label">Full Name</label><input type="text" name="name" class="form-control" value="<?= old('name') ?>" required></div>
<div class="mb-3"><label class="form-label">Email Address</label><input type="email" name="email" class="form-control" value="<?= old('email') ?>" required></div>
<div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
<div class="mb-4"><label class="form-label">Confirm Password</label><input type="password" name="confirm_password" class="form-control" required></div>
<button type="submit" class="btn btn-fm-primary w-100 mb-3">Create Account</button>
<?= form_close() ?>
<div class="text-center small"><a href="<?= base_url('login') ?>" class="text-muted">Already have an account? Login →</a></div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
