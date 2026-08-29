<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= $this->include('finance_petty/_expense_list', ['items' => $advances, 'title' => 'Petty Cash Advances', 'icon' => 'bi-person-lines-fill', 'createUrl' => 'finance-petty/advances/create', 'canCreate' => $canCreate]) ?>
<?= $this->endSection() ?>
