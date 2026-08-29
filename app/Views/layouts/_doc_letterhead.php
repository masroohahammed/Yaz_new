<?php
/**
 * Logo + company block for printable documents.
 *
 * @var array       $settings
 * @var string|null $companyLogoUrl
 * @var string|null $companyLogoB64
 * @var bool        $usePdf
 */
$usePdf    = ! empty($usePdf);
$logoSrc   = $usePdf ? ($companyLogoB64 ?? null) : ($companyLogoUrl ?? null);
$name      = trim((string) ($settings['company_name'] ?? 'FM ERP'));
$tagline   = trim((string) ($settings['company_tagline'] ?? ''));
?>
<div class="doc-letterhead" style="display:flex;justify-content:space-between;align-items:flex-start;border-bottom:2px solid #333;padding-bottom:10px;margin-bottom:14px">
  <div>
    <?php if ($logoSrc): ?>
      <img src="<?= esc($logoSrc) ?>" alt="<?= esc($name) ?>" style="max-height:56px;max-width:180px;object-fit:contain;display:block;margin-bottom:4px">
    <?php endif; ?>
    <div style="font-size:13px;font-weight:700"><?= esc($name) ?></div>
    <?php if ($tagline !== ''): ?>
      <div style="font-size:9px;color:#666;text-transform:uppercase;letter-spacing:.6px;margin-top:2px"><?= esc($tagline) ?></div>
    <?php endif; ?>
    <?= $this->include('layouts/_company_contact', ['settings' => $settings, 'plain' => true]) ?>
  </div>
</div>
