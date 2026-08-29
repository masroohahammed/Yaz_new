<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-people me-2 text-primary"></i>User Management</h1></div>
<div class="d-flex gap-2"><a href="<?= base_url('settings/users/create') ?>" class="btn btn-fm-primary btn-sm">Add User</a><a href="<?= base_url('settings') ?>" class="btn btn-fm-outline btn-sm">Settings</a></div></div>
<div class="fm-card"><div class="fm-card-body p-0"><div class="table-responsive">
<table class="fm-table"><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Phone</th><th>Last Login</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($users as $u): ?><tr>
<td><div class="d-flex align-items-center gap-2"><div class="user-avatar"><?= strtoupper(substr($u['name'],0,2)) ?></div><span class="fw-semibold"><?= esc($u['name']) ?></span></div></td>
<td class="small"><?= esc($u['email']) ?></td>
<td><span class="fm-badge" style="background:#e8f4fd;color:#1565C0;border:1px solid #90CAF9"><?= esc($u['role_display']) ?></span></td>
<td class="small text-muted"><?= esc($u['phone']??'—') ?></td>
<td class="small text-muted"><?= $u['last_login'] ? date('d M Y H:i',strtotime($u['last_login'])) : 'Never' ?></td>
<td><span class="fm-badge badge-status-<?= esc($u['status']) ?>"><?= ucfirst($u['status']) ?></span></td>
<td><div class="d-flex gap-1">
<a href="<?= base_url('settings/users/edit/'.$u['id']) ?>" class="btn-action bg-warning bg-opacity-10 text-warning"><i class="bi bi-pencil"></i></a>
<?php if($u['id'] != session()->get('user_id')): ?>
<a href="<?= base_url('settings/users/delete/'.$u['id']) ?>" class="btn-action bg-danger bg-opacity-10 text-danger" data-confirm="Deactivate this user?"><i class="bi bi-person-x"></i></a>
<?php endif; ?>
</div></td></tr><?php endforeach; ?>
</tbody></table></div></div></div>
<?= $this->endSection() ?>
