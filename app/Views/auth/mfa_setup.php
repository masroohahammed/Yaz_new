<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
  <div class="col-md-8 col-lg-6">
    <div class="page-header">
      <div>
        <h1><i class="bi bi-shield-lock me-2 text-primary"></i>Two-Factor Authentication Setup</h1>
        <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= base_url('profile') ?>">Profile</a></li><li class="breadcrumb-item active">MFA Setup</li></ol></nav>
      </div>
    </div>

    <?php if($user['mfa_enabled']): ?>
    <div class="alert alert-success">
      <i class="bi bi-shield-check me-2"></i><strong>2FA is currently enabled</strong> on your account.
    </div>
    <div class="fm-card p-4 mb-3">
      <h6 class="fw-semibold mb-3">Disable Two-Factor Authentication</h6>
      <form action="<?= base_url('auth/mfa-setup') ?>" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="disable">
        <button type="submit" class="btn btn-danger" onclick="return confirm('Disable 2FA on your account?')">
          <i class="bi bi-shield-x me-1"></i>Disable 2FA
        </button>
      </form>
    </div>
    <?php else: ?>

    <?php if($mfa_secret): ?>
    <div class="fm-card p-4 mb-3">
      <h6 class="fw-semibold mb-3">Step 1: Scan QR or enter secret manually</h6>
      <div class="alert alert-info">
        <strong>OTP URL (copy to authenticator):</strong><br>
        <code class="small"><?= esc($otp_url) ?></code>
      </div>
      <div class="mb-2">
        <label class="form-label fw-medium">Manual entry secret:</label>
        <div class="input-group">
          <input type="text" class="form-control font-monospace" value="<?= esc($mfa_secret) ?>" id="mfaSecret" readonly>
          <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('mfaSecret').value)"><i class="bi bi-clipboard"></i></button>
        </div>
      </div>
      <h6 class="fw-semibold mb-3 mt-4">Step 2: Verify code to activate</h6>
      <form action="<?= base_url('auth/mfa-setup') ?>" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="enable">
        <div class="row g-2 align-items-end">
          <div class="col">
            <input type="text" name="totp_code" class="form-control" maxlength="6" minlength="6" inputmode="numeric" pattern="[0-9]{6}" placeholder="6-digit code" required>
          </div>
          <div class="col-auto">
            <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Enable 2FA</button>
          </div>
        </div>
      </form>
    </div>
    <?php endif; ?>

    <div class="fm-card p-4">
      <h6 class="fw-semibold mb-3">Generate New Secret</h6>
      <p class="small text-muted">Use Google Authenticator, Authy, or any TOTP-compatible app.</p>
      <form action="<?= base_url('auth/mfa-setup') ?>" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="generate">
        <button type="submit" class="btn btn-primary-brand"><i class="bi bi-qr-code me-1"></i>Generate Secret</button>
      </form>
    </div>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>
