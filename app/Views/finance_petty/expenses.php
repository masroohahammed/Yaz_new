<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= $this->include('finance_petty/_expense_list', [
  'items' => $expenses,
  'title' => 'Petty Cash Expenses',
  'icon' => 'bi-receipt',
  'createUrl' => 'finance-petty/expenses/create',
  'canCreate' => $canCreate,
]) ?>
<?= $this->endSection() ?>
