<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= $this->include('finance_bank/_doc_list', ['items' => $transfers, 'type' => 'transfer', 'numberCol' => 'transfer_number', 'dateCol' => 'transfer_date', 'amountCol' => 'amount', 'createUrl' => 'finance-bank/transfers/create', 'title' => 'Bank Transfers', 'icon' => 'bi-arrow-left-right', 'canCreate' => $canCreate]) ?>
<?= $this->endSection() ?>
