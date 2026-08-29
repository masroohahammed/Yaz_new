<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php helper('fm'); ?>
<?= view('contracts/_workflow_form', [
  'title' => 'Contract Amendment',
  'contract' => $contract,
  'action' => base_url('contracts/' . $contract['id'] . '/amendment'),
  'type' => 'amendment',
]) ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?= $this->include('partials/tinymce') ?>
<?= $this->endSection() ?>
