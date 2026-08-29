<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center mt-5">
  <div class="col-sm-10 col-md-6 col-lg-5">
    <div class="fm-card p-4">
      <div class="text-center mb-4">
        <i class="bi bi-shield-lock text-primary" style="font-size:2.5rem"></i>
        <h4 class="mt-2 fw-bold">Two-Factor Authentication</h4>
        <p class="text-muted small">Enter the 6-digit code from your authenticator app.</p>
      </div>

      <?php if(session()->getFlashdata('error')): ?>
      <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
      <?php endif; ?>

      <form action="<?= base_url('auth/mfa') ?>" method="post">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label fw-medium">Verification Code</label>
          <input type="text" name="totp_code" class="form-control form-control-lg text-center" maxlength="6" minlength="6" inputmode="numeric" pattern="[0-9]{6}" autocomplete="one-time-code" autofocus required placeholder="000000">
        </div>
        <button type="submit" class="btn btn-primary-brand w-100">Verify</button>
      </form>
      <div class="mt-3 text-center">
        <a href="<?= base_url('login') ?>" class="small text-muted">← Back to login</a>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
