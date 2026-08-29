<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1>Petty Cash Reports</h1></div>
<div class="row g-3">
<?php foreach (['Statement','Expenses by Branch','Outstanding Advances','Replenishment Report','Cash Count Report','Audit Report'] as $r): ?>
<div class="col-md-4"><div class="fm-card p-3"><div class="fw-semibold"><?= esc($r) ?></div><div class="small text-muted">Filter by date, branch, property, custodian — export via print</div><button onclick="window.print()" class="btn btn-sm btn-outline-secondary mt-2">Print</button></div></div>
<?php endforeach; ?>
</div>
<?= $this->endSection() ?>
