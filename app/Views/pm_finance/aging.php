<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1>Aging Report</h1></div>
<table class="table"><thead><tr><th>Bucket</th><th class="text-end">Outstanding</th></tr></thead>
<tbody><?php foreach ($buckets as $label => $amt): ?><tr><td><?= esc($label) ?> days</td><td class="text-end"><?= number_format($amt,2) ?></td></tr><?php endforeach; ?></tbody></table>
<?= $this->endSection() ?>
