<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= view('contracts/_workflow_form', [
  'title' => 'Renew Contract',
  'contract' => $contract,
  'action' => base_url('contracts/' . $contract['id'] . '/renew'),
  'type' => 'renew',
]) ?>
<?= $this->endSection() ?>
