<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= view('contracts/_workflow_form', [
  'title' => 'Terminate Contract',
  'contract' => $contract,
  'action' => base_url('contracts/' . $contract['id'] . '/terminate'),
  'type' => 'terminate',
]) ?>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?= $this->include('partials/tinymce') ?>
<?= $this->endSection() ?>
