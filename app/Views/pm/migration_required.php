<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1><?= esc($title ?? 'Module') ?></h1></div>
<div class="alert alert-warning">Database migration required. Run <code>database/pm_modules_patch.sql</code>.</div>
<?= $this->endSection() ?>
