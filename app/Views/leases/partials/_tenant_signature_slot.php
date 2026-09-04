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
$signMode    = ! empty($signMode);
$alreadySigned = ! empty($alreadySigned);
$tenantSig   = trim((string) ($tenantSignatureB64 ?? ''));
$lineClass   = $lineClass ?? 'signature-line';
$imgClass    = $imgClass ?? 'signature-img';
$fieldName   = $fieldName ?? 'tenant_signature';
$padId       = 'sig_' . preg_replace('/[^a-z0-9]/', '_', $fieldName);
?>
<?php if ($signMode && ! $alreadySigned): ?>
  <div class="sign-pad-wrap" style="touch-action:none">
    <canvas id="<?= esc($padId) ?>_canvas" class="fm-signature-canvas" height="90" style="width:100%;cursor:crosshair;display:block"></canvas>
    <input type="hidden" name="<?= esc($fieldName) ?>" id="<?= esc($padId) ?>_input">
  </div>
  <button type="button" class="sign-pad-clear fm-sig-clear" data-canvas="<?= esc($padId) ?>_canvas" data-input="<?= esc($padId) ?>_input">Clear signature</button>
<?php elseif ($tenantSig !== ''): ?>
  <img src="<?= esc($tenantSig) ?>" alt="Tenant signature" class="<?= esc($imgClass) ?>">
<?php else: ?>
  <div class="<?= esc($lineClass) ?>"></div>
<?php endif; ?>
