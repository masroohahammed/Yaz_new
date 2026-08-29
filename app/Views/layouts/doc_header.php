<?php
/**
 * Shared document header partial for all print/PDF views.
 *
 * Variables expected (passed from parent view):
 *   $settings         — array of system_settings key/value
 *   $companyLogoUrl   — public URL to logo (or null)
 *   $companyLogoB64   — base64 data URI for PDF embed (or null)
 *   $docTitle         — e.g. "INVOICE", "JOB CARD", "WORK ORDER"
 *   $docNumber        — e.g. "INV-2026-0042"
 *   $docStatus        — (optional) status string
 *   $docStatusClass   — (optional) CSS class for status badge
 *   $usePdf           — true = use base64 logo (wkhtmltopdf), false = use URL
 */
$primaryColor   = $settings['primary_color']   ?? '#76002b';
$secondaryColor = $settings['secondary_color'] ?? '#c7ba9a';
$companyName    = $settings['company_name']    ?? 'FM ERP';
$companyTagline = $settings['company_tagline'] ?? 'Facility Management ERP';
$usePdf         = $usePdf ?? false;
$logoSrc        = $usePdf ? ($companyLogoB64 ?? null) : ($companyLogoUrl ?? null);
?>
<div class="doc-header">
  <div class="doc-brand">
    <?php if ($logoSrc): ?>
    <img src="<?= esc($logoSrc) ?>" alt="<?= esc($companyName) ?>" class="doc-logo">
    <?php else: ?>
    <div class="doc-brand-text"><?= esc($companyName) ?></div>
    <?php endif; ?>
    <div class="doc-tagline"><?= esc($companyTagline) ?></div>
    <?= $this->include('layouts/_company_contact', ['settings' => $settings, 'plain' => ! empty($usePdf)]) ?>
  </div>
  <div class="doc-title-block">
    <div class="doc-type"><?= esc($docTitle ?? 'DOCUMENT') ?></div>
    <div class="doc-number"><?= esc($docNumber ?? '') ?></div>
    <?php if (!empty($docStatus)): ?>
    <span class="doc-status <?= esc($docStatusClass ?? '') ?>"><?= esc($docStatus) ?></span>
    <?php endif; ?>
  </div>
</div>
<div class="doc-divider"></div>
