<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center mt-4">
  <div class="col-sm-10 col-md-6 col-lg-5">
    <div class="fm-card p-4">
      <div class="text-center mb-4">
        <i class="bi bi-key text-warning" style="font-size:2.5rem"></i>
        <h4 class="mt-2 fw-bold">Password Change Required</h4>
        <p class="text-muted small">Your password has expired (90-day policy). Please set a new password to continue.</p>
      </div>

      <?php if(session()->getFlashdata('error')): ?>
      <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
      <?php endif; ?>

      <form action="<?= base_url('profile/force-password-change') ?>" method="post">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label fw-medium">New Password <span class="text-danger">*</span></label>
          <input type="password" name="new_password" class="form-control" required minlength="8" autocomplete="new-password">
          <div class="form-text">Min 8 chars, 1 uppercase, 1 number.</div>
        </div>
        <div class="mb-3">
          <label class="form-label fw-medium">Confirm Password <span class="text-danger">*</span></label>
          <input type="password" name="confirm_password" class="form-control" required minlength="8" autocomplete="new-password">
        </div>
        <button type="submit" class="btn btn-primary-brand w-100"><i class="bi bi-key me-1"></i>Update Password</button>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
