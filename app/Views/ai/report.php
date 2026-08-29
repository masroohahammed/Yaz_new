<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi <?= esc($icon ?? 'bi-robot') ?> me-2"></i><?= esc($title) ?></h1>
    <div class="small text-muted"><?= esc($subtitle ?? '') ?></div>
  </div>
  <a href="<?= base_url('ai') ?>" class="btn btn-fm-outline btn-sm">← AI Dashboard</a>
</div>
<div class="fm-card"><div class="fm-card-body">
  <pre class="mb-0" style="white-space:pre-wrap;font-family:inherit;font-size:.875rem;line-height:1.6"><?= esc($content) ?></pre>
</div></div>
<?= $this->endSection() ?>
