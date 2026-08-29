<?php
/**
 * Logo + company name + email for printable documents (from companies table).
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
$logoSrc = $usePdf ? ($companyLogoB64 ?? null) : ($companyLogoUrl ?? null);
$name    = trim((string) ($settings['company_name'] ?? 'FM ERP'));
$email   = trim((string) ($settings['company_email'] ?? ''));
?>
<div class="doc-letterhead" style="text-align:center;border-bottom:2px solid #333;padding-bottom:12px;margin-bottom:16px">
  <?php if ($logoSrc): ?>
    <img src="<?= esc($logoSrc) ?>" alt="<?= esc($name) ?>" style="max-height:64px;max-width:200px;object-fit:contain;display:block;margin:0 auto 8px">
  <?php endif; ?>
  <div style="font-size:14px;font-weight:700;line-height:1.3"><?= esc($name) ?></div>
  <?php if ($email !== ''): ?>
    <div style="font-size:11px;color:#444;margin-top:4px"><?= esc($email) ?></div>
  <?php endif; ?>
</div>
