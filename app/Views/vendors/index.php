<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-building me-2 text-primary"></i>Vendor Management</h1></div><a href="<?= base_url('vendors/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Vendor</a></div>
<div class="fm-card"><div class="fm-card-body p-0"><div class="table-responsive">
<table class="fm-table"><thead><tr><th>Vendor</th><th>Category</th><th>Contact</th><th>Phone</th><th>Rating</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($vendors as $v): ?>
<tr>
  <td><div class="fw-semibold small"><?= esc($v['name']) ?></div><div class="x-small text-muted"><?= esc($v['email']??'') ?></div></td>
  <td class="small"><?= esc($v['category']) ?></td>
  <td class="small"><?= esc($v['contact']??'—') ?></td>
  <td class="small"><?= esc($v['phone']??'—') ?></td>
  <td><?php for($i=1;$i<=5;$i++): ?><i class="bi bi-star<?= $i<=$v['rating']?'-fill text-warning':' text-muted' ?>" style="font-size:.75rem"></i><?php endfor; ?></td>
  <td><span class="fm-badge badge-status-<?= $v['status']==='active'?'completed':($v['status']==='blacklisted'?'cancelled':'new') ?>"><?= ucfirst($v['status']) ?></span></td>
  <td><a href="<?= base_url('vendors/view/'.$v['id']) ?>" class="btn-action bg-primary bg-opacity-10 text-primary"><i class="bi bi-eye"></i></a></td>
</tr>
<?php endforeach; ?>
<?php if(empty($vendors)): ?><tr><td colspan="7" class="text-center py-4 text-muted">No vendors added yet</td></tr><?php endif; ?>
</tbody></table></div></div></div>
<?= $this->endSection() ?>
