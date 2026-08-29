<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div><h1><?= esc($title) ?></h1></div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= base_url('reports/export/'.$type.'/csv') ?>" class="btn btn-fm-outline btn-sm">CSV</a>
    <a href="<?= base_url('reports/export/'.$type.'/xls') ?>" class="btn btn-fm-outline btn-sm">Excel</a>
    <a href="<?= base_url('reports/export/'.$type.'/pdf') ?>" class="btn btn-fm-outline btn-sm">PDF</a>
    <a href="<?= base_url('reports/builder') ?>" class="btn btn-fm-outline btn-sm">Back</a>
  </div>
</div>
<div class="fm-card"><div class="fm-card-body p-0 overflow-auto">
<table class="fm-table"><thead><tr>
  <?php foreach ($headers as $h): ?><th><?= esc($h) ?></th><?php endforeach; ?>
</tr></thead><tbody>
<?php foreach ($rows as $row): ?><tr>
  <?php foreach ($row as $cell): ?><td class="small"><?= esc((string) $cell) ?></td><?php endforeach; ?>
</tr><?php endforeach; ?>
<?php if (empty($rows)): ?><tr><td colspan="<?= max(1, count($headers)) ?>" class="text-center py-4 text-muted">No rows</td></tr><?php endif; ?>
</tbody></table>
</div></div>
<?= $this->endSection() ?>
