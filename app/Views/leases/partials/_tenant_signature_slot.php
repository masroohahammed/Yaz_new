<?php
/**
 * Tenant signature area for print and public sign views.
 *
 * @var bool   $signMode
 * @var bool   $alreadySigned
 * @var string $tenantSignatureB64
 * @var string $lineClass
 * @var string $imgClass
 * @var string $fieldName
 */
$signMode      = ! empty($signMode);
$alreadySigned = ! empty($alreadySigned);
$tenantSig     = trim((string) ($tenantSignatureB64 ?? ''));
$fieldName     = $fieldName ?? 'tenant_signature';
$padId         = 'sig_' . preg_replace('/[^a-z0-9]/', '_', $fieldName);
$showPad       = $signMode && ! $alreadySigned;
$showImage     = $tenantSig !== '';
?>
<div class="tenant-signature-anchor<?= $showPad ? ' is-signing' : '' ?><?= $showImage ? ' is-signed' : '' ?>">
  <?php if ($showPad): ?>
    <div class="sign-pad-wrap">
      <canvas id="<?= esc($padId) ?>_canvas" class="fm-signature-canvas" style="width:100%;height:72px;display:block"></canvas>
      <input type="hidden" name="<?= esc($fieldName) ?>" id="<?= esc($padId) ?>_input">
    </div>
    <button type="button" class="sign-pad-clear fm-sig-clear" data-canvas="<?= esc($padId) ?>_canvas" data-input="<?= esc($padId) ?>_input">Clear</button>
  <?php elseif ($showImage): ?>
    <img src="<?= esc($tenantSig) ?>" alt="Tenant signature" class="tenant-signature-image">
  <?php endif; ?>
  <div class="tenant-signature-line" aria-hidden="true"></div>
</div>
