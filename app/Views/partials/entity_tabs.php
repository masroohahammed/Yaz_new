<?php
/** @var list<array{id: string, label: string, badge?: int|string, icon?: string, show?: bool}> $tabs */
/** @var string $activeId */
$tabs    = $tabs ?? [];
$activeId = $activeId ?? ($tabs[0]['id'] ?? '');
?>
<ul class="nav fm-entity-tabs" role="tablist">
  <?php foreach ($tabs as $tab):
    if (isset($tab['show']) && ! $tab['show']) continue;
    $id = $tab['id'];
  ?>
  <li class="nav-item" role="presentation">
    <a class="nav-link <?= $id === $activeId ? 'active' : '' ?>" id="tab-<?= esc($id) ?>-tab"
       data-bs-toggle="tab" href="#tab-<?= esc($id) ?>" role="tab" aria-controls="tab-<?= esc($id) ?>">
      <?php if (! empty($tab['icon'])): ?><i class="bi <?= esc($tab['icon']) ?> me-1"></i><?php endif; ?>
      <?= esc($tab['label']) ?>
      <?php if (isset($tab['badge'])): ?><span class="badge bg-secondary ms-1"><?= esc($tab['badge']) ?></span><?php endif; ?>
    </a>
  </li>
  <?php endforeach; ?>
</ul>
