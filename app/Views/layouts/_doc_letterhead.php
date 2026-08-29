<?php
/**
 * Logo + company name + phone + email for printable documents.
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
$logoSrc = $usePdf
    ? ($companyLogoB64 ?: $companyLogoUrl ?: null)
    : ($companyLogoUrl ?: $companyLogoB64 ?: null);
$name    = trim((string) ($settings['company_name'] ?? 'FM ERP'));
$email   = trim((string) ($settings['company_email'] ?? ''));
$phone   = trim((string) ($settings['company_phone'] ?? ''));
$primary = trim((string) ($settings['primary_color'] ?? '#76002b'));
$phoneFmt = $phone;
if ($phone !== '') {
    $digits = preg_replace('/\D/', '', $phone);
    if (str_starts_with($digits, '974')) {
        $phoneFmt = '(974)' . substr($digits, 3);
    } elseif ($digits !== '') {
        $phoneFmt = '(974)' . $digits;
    }
}
?>
<div class="doc-letterhead" style="text-align:center;border-bottom:2px solid <?= esc($primary) ?>;padding-bottom:14px;margin-bottom:16px">
  <?php if ($logoSrc): ?>
    <img src="<?= esc($logoSrc) ?>" alt="<?= esc($name) ?>" style="max-height:68px;max-width:220px;object-fit:contain;display:block;margin:0 auto 10px">
  <?php endif; ?>
  <div style="font-size:15px;font-weight:700;line-height:1.35;color:#1a1a1a"><?= esc($name) ?></div>
  <?php if ($phoneFmt !== ''): ?>
    <div style="font-size:11px;color:#444;margin-top:5px">Tel: <?= esc($phoneFmt) ?></div>
  <?php endif; ?>
  <?php if ($email !== ''): ?>
    <div style="font-size:11px;color:#444;margin-top:3px"><?= esc($email) ?></div>
  <?php endif; ?>
</div>
