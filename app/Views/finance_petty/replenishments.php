<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between"><h1>Replenishments</h1>
<?php if ($canReplenish): ?><a href="<?= base_url('finance-petty/replenishments/create') ?>" class="btn btn-fm-primary btn-sm">New Replenishment</a><?php endif; ?></div>
<?= $this->include('finance_petty/_expense_list', ['items' => $replenishments, 'title' => '', 'canCreate' => false]) ?>
<?php foreach ($replenishments as $r): if ($r['status']==='approved'): ?>
<form method="post" action="<?= base_url('finance-petty/replenishments/post/'.$r['id']) ?>" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-fm-primary mb-2">Post <?= esc($r['replenishment_number']) ?></button></form>
<?php endif; endforeach; ?>
<?= $this->endSection() ?>
