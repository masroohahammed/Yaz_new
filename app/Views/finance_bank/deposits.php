<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= $this->include('finance_bank/_doc_list', ['items' => $deposits, 'type' => 'deposit', 'numberCol' => 'deposit_number', 'dateCol' => 'deposit_date', 'amountCol' => 'amount', 'extraCol' => 'bank_account_name', 'createUrl' => 'finance-bank/deposits/create', 'title' => 'Deposits', 'icon' => 'bi-arrow-down-circle', 'canCreate' => $canCreate]) ?>
<?= $this->endSection() ?>
