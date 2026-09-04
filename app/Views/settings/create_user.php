<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-person-plus me-2 text-primary"></i>Add User</h1></div></div>
<?= form_open(base_url('settings/users/store')) ?>
<div class="row g-3"><div class="col-lg-6"><div class="fm-form-section"><h6>User Details</h6>
<div class="mb-3"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
<div class="mb-3"><label class="form-label">Company *</label>
  <select name="company_id" class="form-select" required>
    <option value="">— Select company —</option>
    <?php foreach ($companies ?? [] as $c): ?>
      <option value="<?= (int) $c['id'] ?>" <?= (int) old('company_id') === (int) $c['id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
    <?php endforeach; ?>
  </select>
</div>
<div class="mb-3"><label class="form-label">Role *</label><select name="role_id" class="form-select" required><?php foreach($roles as $r): ?><option value="<?= $r['id'] ?>"><?= esc($r['display_name']) ?></option><?php endforeach; ?></select></div>
<div class="mb-3"><label class="form-label">Link to Tenant <span class="text-muted small">(portal access)</span></label>
  <select name="tenant_id" class="form-select">
    <option value="">— None —</option>
    <?php foreach ($tenants ?? [] as $t): ?>
      <option value="<?= (int)$t['id'] ?>"><?= esc($t['full_name']) ?><?= !empty($t['phone']) ? ' · '.esc($t['phone']) : '' ?></option>
    <?php endforeach; ?>
  </select>
</div>
<div class="mb-3"><label class="form-label">Password *</label><input type="password" name="password" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Confirm Password *</label><input type="password" name="confirm_password" class="form-control" required></div>
<?= view('settings/partials/user_access_fields') ?>
<button type="submit" class="btn btn-fm-primary w-100">Create User</button>
</div></div></div>
<?= form_close() ?>
<?= $this->endSection() ?>
