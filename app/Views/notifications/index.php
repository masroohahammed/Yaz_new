<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-bell me-2 text-primary"></i>Notifications</h1></div></div>
<div class="fm-card"><div class="fm-card-body p-0">
<?php if(empty($notifications)): ?>
<div class="text-center py-5 text-muted"><i class="bi bi-bell-slash fs-2 d-block mb-3"></i>No notifications</div>
<?php else: ?>
<?php foreach($notifications as $n): ?>
<div class="d-flex gap-3 px-4 py-3 border-bottom <?= !$n['is_read']?'bg-primary bg-opacity-5':'' ?>">
<div class="flex-shrink-0 mt-1"><i class="bi bi-<?= $n['type']==='sla_breach'?'exclamation-triangle text-danger':($n['type']==='invoice'?'receipt text-success':'info-circle text-primary') ?> fs-5"></i></div>
<div class="flex-grow-1"><div class="fw-semibold"><?= esc($n['title']) ?><?= !$n['is_read']?' <span class="badge bg-primary ms-2" style="font-size:.6rem">New</span>':'' ?></div>
<div class="small text-muted"><?= esc($n['message']??'') ?></div>
<div class="x-small text-muted mt-1"><?= date('d M Y H:i',strtotime($n['created_at'])) ?></div></div>
</div><?php endforeach; ?>
<?php endif; ?>
</div></div>
<?= $this->endSection() ?>
