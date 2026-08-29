<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= $this->include('finance_bank/_doc_list', ['items' => $withdrawals, 'type' => 'withdrawal', 'numberCol' => 'withdrawal_number', 'dateCol' => 'withdrawal_date', 'amountCol' => 'amount', 'extraCol' => null, 'createUrl' => 'finance-bank/withdrawals/create', 'title' => 'Withdrawals', 'icon' => 'bi-arrow-up-circle', 'canCreate' => $canCreate]) ?>
<?= $this->endSection() ?>
