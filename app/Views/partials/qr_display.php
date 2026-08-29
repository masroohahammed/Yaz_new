<?php
/** @var string $scanUrl */
/** @var string $qrImageUrl */
/** @var string $entityLabel */
/** @var string|null $printUrl */
$entityLabel = $entityLabel ?? 'Entity';
?>
<div class="fm-card">
  <div class="card-header-fm">
    <h5 class="mb-0"><i class="bi bi-qr-code me-2"></i>QR Code</h5>
    <?php if (! empty($printUrl)): ?>
    <a href="<?= esc($printUrl) ?>" class="btn btn-fm-outline btn-sm" target="_blank"><i class="bi bi-printer me-1"></i>Print</a>
    <?php endif; ?>
  </div>
  <div class="fm-card-body text-center">
    <img src="<?= esc($qrImageUrl) ?>" alt="QR Code" class="img-fluid mb-3" style="max-width:220px">
    <div class="small text-muted mb-2">Scan to open <?= esc(strtolower($entityLabel)) ?> actions</div>
    <div class="input-group input-group-sm mb-2">
      <input type="text" class="form-control" value="<?= esc($scanUrl) ?>" readonly id="qr-url-copy">
      <button type="button" class="btn btn-fm-outline" onclick="navigator.clipboard.writeText(document.getElementById('qr-url-copy').value)">Copy</button>
    </div>
    <a href="<?= esc($scanUrl) ?>" class="btn btn-fm-primary btn-sm" target="_blank"><i class="bi bi-box-arrow-up-right me-1"></i>Open scan page</a>
  </div>
</div>
