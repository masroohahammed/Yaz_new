<?php
/** @var string $summaryUrl Summary report URL */
/** @var string|null $detailUrl Full detail report URL (optional) */
/** @var string|null $exportCsv Export CSV URL */
/** @var string|null $exportExcel Export Excel URL */
/** @var bool $isDetailPage Current page is the detail view */
$isDetailPage = $isDetailPage ?? false;
$summaryUrl   = $summaryUrl ?? base_url('reports');
$detailUrl    = $detailUrl ?? null;
?>
<div class="d-flex flex-wrap gap-2 align-items-center mb-3 no-print report-toolbar">
  <?php if ($isDetailPage): ?>
  <a href="<?= esc($summaryUrl) ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-arrow-left me-1"></i>Summary</a>
  <?php elseif ($detailUrl): ?>
  <a href="<?= esc($detailUrl) ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-list-ul me-1"></i>Detailed report</a>
  <?php endif; ?>
  <?php if ($exportCsv): ?>
  <a href="<?= esc($exportCsv) ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-filetype-csv me-1"></i>Export CSV</a>
  <?php endif; ?>
  <?php if (!empty($exportExcel)): ?>
  <a href="<?= esc($exportExcel) ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
  <?php endif; ?>
  <button type="button" class="btn btn-fm-outline btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print <?= $isDetailPage ? 'detail' : 'summary' ?></button>
</div>
