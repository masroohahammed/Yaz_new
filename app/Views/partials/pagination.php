<?php if (!empty($totalCount) && !empty($perPage) && $totalCount > $perPage): ?>
<?php
  $currentPage = (int) ($currentPage ?? 1);
  $totalPages  = (int) ceil($totalCount / $perPage);
  $query       = $_GET ?? [];
  unset($query['page']);
  $baseQs = http_build_query($query);
  $sep    = $baseQs ? '&' : '';
?>
<nav class="d-flex justify-content-between align-items-center px-3 py-2 border-top" aria-label="Pagination">
  <span class="small text-muted">Page <?= $currentPage ?> of <?= $totalPages ?> (<?= (int) $totalCount ?> records)</span>
  <ul class="pagination pagination-sm mb-0">
    <?php if ($currentPage > 1): ?>
    <li class="page-item"><a class="page-link" href="?<?= esc($baseQs . $sep . 'page=' . ($currentPage - 1)) ?>">Previous</a></li>
    <?php endif; ?>
    <?php if ($currentPage < $totalPages): ?>
    <li class="page-item"><a class="page-link" href="?<?= esc($baseQs . $sep . 'page=' . ($currentPage + 1)) ?>">Next</a></li>
    <?php endif; ?>
  </ul>
</nav>
<?php endif; ?>
