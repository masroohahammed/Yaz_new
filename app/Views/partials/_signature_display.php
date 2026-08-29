<?php
/** @var string|null $path Stored path e.g. uploads/signatures/foo.png */
/** @var string $label */
$path  = $path ?? null;
$label = $label ?? 'Signature';
if (empty($path)) {
    return;
}
$url = function_exists('fm_logo_url') ? fm_logo_url($path) : '';
if ($url === '') {
    return;
}
?>
<div class="fm-signature-preview mb-2">
  <div class="small text-muted mb-1"><?= esc($label) ?></div>
  <img src="<?= esc($url) ?>" alt="<?= esc($label) ?>" style="max-width:100%;height:72px;border:1px solid #e2e8f0;border-radius:6px;background:#fff">
</div>
