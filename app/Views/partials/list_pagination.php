<?php if (! empty($total) && ! empty($perPage) && $total > $perPage): ?>
<?php
  $currentPage = (int) ($currentPage ?? 1);
  $from        = ($currentPage - 1) * $perPage + 1;
  $to          = min($currentPage * $perPage, (int) $total);
?>
<div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
  <small class="text-muted">Showing <?= $from ?>–<?= $to ?> of <?= (int) $total ?></small>
  <nav><?= paginate((int) $total, (int) $perPage, $currentPage) ?></nav>
</div>
<?php endif; ?>
