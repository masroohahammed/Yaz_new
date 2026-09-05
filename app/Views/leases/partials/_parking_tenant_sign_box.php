<?php
/**
 * Tenant signature box for parking contract (print, PDF, public sign).
 *
 * @var bool   $signMode
 * @var bool   $alreadySigned
 * @var string $tenantSignatureB64
 */
$signMode      = ! empty($signMode);
$alreadySigned = ! empty($alreadySigned);
$tenantSig     = trim((string) ($tenantSignatureB64 ?? ''));
$showPad       = $signMode && ! $alreadySigned;
?>
<?php if ($showPad): ?>
  <?= $this->include('leases/partials/_tenant_signature_slot', [
      'signMode'           => true,
      'alreadySigned'      => false,
      'tenantSignatureB64' => '',
  ]) ?>
<?php else: ?>
<div class="parking-tenant-sign-box">
  <div class="parking-tenant-sign-inner">
    <?php if ($tenantSig !== ''): ?>
    <img src="<?= esc($tenantSig) ?>" alt="Tenant signature" class="parking-tenant-sign-img">
    <?php endif; ?>
  </div>
  <div class="parking-tenant-sign-line"></div>
</div>
<?php endif; ?>
