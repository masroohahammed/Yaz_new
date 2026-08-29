<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-pencil me-2 text-primary"></i>Edit User</h1></div></div>
<?= form_open(base_url('settings/users/update/'.$user['id'])) ?>
<div class="row g-3"><div class="col-lg-6"><div class="fm-form-section"><h6>Edit User Details</h6>
<div class="mb-3"><label class="form-label">Full Name *</label><input type="text" name="name" class="form-control" value="<?= esc($user['name']) ?>" required></div>
<div class="mb-3"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" value="<?= esc($user['email']) ?>" required></div>
<div class="mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control" value="<?= esc($user['phone']) ?>"></div>
<div class="mb-3"><label class="form-label">Role</label><select name="role_id" class="form-select"><?php foreach($roles as $r): ?><option value="<?= $r['id'] ?>" <?= $user['role_id']==$r['id']?'selected':'' ?>><?= esc($r['display_name']) ?></option><?php endforeach; ?></select></div>
<div class="mb-3"><label class="form-label">Link to Tenant <span class="text-muted small">(portal access)</span></label>
  <select name="tenant_id" class="form-select">
    <option value="">— None —</option>
    <?php foreach ($tenants ?? [] as $t): ?>
      <option value="<?= (int)$t['id'] ?>" <?= (int)($user['tenant_id'] ?? 0) === (int)$t['id'] ? 'selected' : '' ?>><?= esc($t['full_name']) ?><?= !empty($t['phone']) ? ' · '.esc($t['phone']) : '' ?></option>
    <?php endforeach; ?>
  </select>
</div>
<div class="mb-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="active" <?= $user['status']==='active'?'selected':'' ?>>Active</option><option value="inactive" <?= $user['status']==='inactive'?'selected':'' ?>>Inactive</option><option value="suspended" <?= $user['status']==='suspended'?'selected':'' ?>>Suspended</option></select></div>
<div class="mb-3"><label class="form-label">New Password <span class="text-muted small">(leave blank to keep current)</span></label><input type="password" name="password" class="form-control"></div>
<button type="submit" class="btn btn-fm-primary w-100">Save Changes</button>
</div></div></div>
<?= form_close() ?>
<?= $this->endSection() ?>
