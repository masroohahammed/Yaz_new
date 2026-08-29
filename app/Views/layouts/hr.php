<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= view('hr/partials/subnav', ['active' => $hrNavActive ?? '']) ?>
<?= $this->renderSection('hr_content') ?>
<?= $this->endSection() ?>
