<?php
/**
 * Logo + company block for printable documents.
 *
 * @var array       $settings
 * @var string|null $companyLogoUrl
 * @var string|null $companyLogoB64
 * @var bool        $usePdf
 * @var array|null  $companyBranding
 */
$usePdf    = ! empty($usePdf);
$branding  = $companyBranding ?? null;
if (is_array($branding)) {
    $settings       = $branding['settings'] ?? $settings;
    $companyLogoUrl = $branding['logoUrl'] ?? $companyLogoUrl ?? null;
    $companyLogoB64 = $branding['logoB64'] ?? $companyLogoB64 ?? null;
}
$logoSrc   = $usePdf ? ($companyLogoB64 ?? null) : ($companyLogoUrl ?? null);
$name      = trim((string) ($settings['company_name'] ?? 'FM ERP'));
$tagline   = trim((string) ($settings['company_tagline'] ?? ''));
$code      = trim((string) ($settings['company_code'] ?? ''));
$contact   = trim((string) ($settings['company_contact'] ?? ''));
?>
<div class="doc-letterhead" style="display:flex;justify-content:space-between;align-items:flex-start;border-bottom:2px solid #333;padding-bottom:10px;margin-bottom:14px">
  <div style="flex:1;text-align:center">
    <?php if ($logoSrc): ?>
      <img src="<?= esc($logoSrc) ?>" alt="<?= esc($name) ?>" style="max-height:52px;max-width:160px;object-fit:contain;display:block;margin:0 auto 6px">
    <?php endif; ?>
    <div style="font-size:12px;font-weight:700"><?= esc($name) ?><?php if ($code !== ''): ?> <span style="font-weight:600;color:#555">(<?= esc($code) ?>)</span><?php endif; ?></div>
    <?php if ($tagline !== ''): ?>
      <div style="font-size:8px;color:#666;text-transform:uppercase;letter-spacing:.6px;margin-top:2px"><?= esc($tagline) ?></div>
    <?php endif; ?>
    <?php if ($contact !== ''): ?>
      <div style="font-size:8px;color:#555;margin-top:2px"><?= esc($contact) ?></div>
    <?php endif; ?>
    <?= $this->include('layouts/_company_contact', ['settings' => $settings, 'plain' => true, 'style' => 'font-size:8px;color:#555;line-height:1.45;margin-top:4px;text-align:center']) ?>
  </div>
</div>
