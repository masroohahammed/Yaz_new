<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-shield-check me-2"></i>Security Settings</h1>
    <div class="small text-muted">Login protection, passwords, sessions, and audit logging.</div>
  </div>
  <a href="<?= base_url('settings') ?>" class="btn btn-fm-outline btn-sm">← Settings</a>
</div>

<?= form_open(base_url('settings/security/save')) ?>
<div class="fm-card"><div class="fm-card-body">
  <?php
  $toggles = ['sec_public_registration','sec_require_mfa_admins','sec_audit_sensitive_actions','sec_force_https'];
  $numbers = ['sec_login_max_attempts','sec_login_lockout_minutes','sec_password_min_length','sec_password_expire_days','sec_session_idle_minutes'];
  foreach ($labels as $key => $label):
    $isToggle = in_array($key, $toggles, true);
    $isNumber = in_array($key, $numbers, true);
    $val = $security[$key] ?? '';
  ?>
  <div class="d-flex align-items-center justify-content-between py-3 border-bottom gap-3">
    <div class="flex-grow-1">
      <div class="fw-semibold"><?= esc($label) ?></div>
      <?php if ($key === 'sec_public_registration'): ?>
      <div class="small text-muted">When off, the public register page is disabled (recommended for production).</div>
      <?php elseif ($key === 'sec_require_mfa_admins'): ?>
      <div class="small text-muted">Super admin accounts must enable 2FA before accessing the dashboard.</div>
      <?php elseif ($key === 'sec_password_expire_days'): ?>
      <div class="small text-muted">Set to 0 to disable forced password rotation.</div>
      <?php endif; ?>
    </div>
    <?php if ($isToggle): ?>
    <div class="form-check form-switch">
      <input type="checkbox" name="<?= esc($key) ?>" value="1" class="form-check-input" role="switch" style="width:48px;height:24px" <?= $val === '1' ? 'checked' : '' ?>>
    </div>
    <?php elseif ($isNumber): ?>
    <input type="number" name="<?= esc($key) ?>" class="form-control form-control-sm" style="max-width:100px" min="0" value="<?= esc($val) ?>">
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
  <button type="submit" class="btn btn-fm-primary w-100 mt-3"><i class="bi bi-check-lg me-1"></i>Save Security Settings</button>
</div></div>
<?= form_close() ?>

<div class="alert alert-info mt-3 small">
  <strong>Already protected:</strong> CSRF tokens on all POST forms, bcrypt password hashing, login brute-force lockout,
  optional MFA (Profile → 2FA), company/facility data scoping, and activity audit log.
</div>
<?= $this->endSection() ?>
