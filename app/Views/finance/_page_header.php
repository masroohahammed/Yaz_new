<?php /** @var string $title @var string|null $subtitle @var string|null $backUrl */ ?>
<div class="page-header mb-3">
  <div>
    <h1 class="h4 mb-1"><?= esc($title ?? '') ?></h1>
    <?php if (!empty($subtitle)): ?><div class="small text-muted"><?= esc($subtitle) ?></div><?php endif; ?>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="<?= base_url('settings/finance-module') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-gear me-1"></i>Finance Setup</a>
    <a href="<?= base_url($backUrl ?? 'finance') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
  </div>
</div>
