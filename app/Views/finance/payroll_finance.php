<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= $this->include('finance/_page_header', ['title' => 'Payroll & HR Finance', 'subtitle' => 'GL account 5300 — link to Employees for HR records', 'backUrl' => 'finance/reports']) ?>
<div class="alert alert-info">Payroll journal export and WPS integration can be added per company policy. Operational staff are listed below; post payroll journals to GL when payroll is processed.</div>
<a href="<?= base_url('employees') ?>" class="btn btn-fm-outline btn-sm mb-3"><i class="bi bi-people me-1"></i>Employees module</a>
<div class="fm-card"><div class="fm-card-body p-0">
<table class="fm-table"><thead><tr><th>Name</th><th>Email</th><th>Role</th></tr></thead>
<tbody>
<?php foreach ($employees as $e): ?>
<tr><td><?= esc($e['name']) ?></td><td class="small"><?= esc($e['email'] ?? '') ?></td><td><?= esc($e['role_name'] ?? '') ?></td></tr>
<?php endforeach; ?>
</tbody></table>
</div></div>
<?= $this->endSection() ?>
