<?php
/** @var string $fieldName input name for base64 data URL */
/** @var string $label */
$fieldName = $fieldName ?? 'signature';
$label     = $label ?? 'Signature';
$id        = 'sig_' . preg_replace('/[^a-z0-9]/', '_', $fieldName);
?>
<div class="fm-signature-block mb-3">
  <label class="form-label small fw-medium"><?= esc($label) ?></label>
  <div class="border rounded bg-white position-relative" style="touch-action:none">
    <canvas id="<?= esc($id) ?>_canvas" class="fm-signature-canvas w-100" height="120" style="cursor:crosshair;display:block"></canvas>
    <input type="hidden" name="<?= esc($fieldName) ?>" id="<?= esc($id) ?>_input">
  </div>
  <button type="button" class="btn btn-sm btn-outline-secondary mt-1 fm-sig-clear" data-canvas="<?= esc($id) ?>_canvas" data-input="<?= esc($id) ?>_input">Clear</button>
</div>
