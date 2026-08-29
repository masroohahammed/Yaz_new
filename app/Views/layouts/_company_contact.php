<?php
/**
 * Company address / phone / email from system settings (invoices, PDFs, prints).
 *
 * @var array  $settings
 * @var string $class      Optional extra CSS classes
 * @var string $style      Optional inline style for print views
 * @var bool   $plain      true = no icons/links (print/PDF)
 */
$addr    = trim((string) ($settings['company_address'] ?? ''));
$phone   = trim((string) ($settings['company_phone'] ?? ''));
$cr      = trim((string) ($settings['company_cr'] ?? ''));
$poBox   = trim((string) ($settings['company_po_box'] ?? ''));
$email   = trim((string) ($settings['company_email'] ?? ''));
$website = trim((string) ($settings['company_website'] ?? ''));
if ($addr === '' && $phone === '' && $cr === '' && $poBox === '' && $email === '' && $website === '') {
    return;
}
$plain     = ! empty($plain);
$wrapClass = trim('fm-company-contact ' . ($class ?? ''));
$wrapStyle = $style ?? '';
$phoneFmt  = $phone;
if ($phone !== '') {
    $digits = preg_replace('/\D/', '', $phone);
    if (str_starts_with($digits, '974')) {
        $phoneFmt = '(974)' . substr($digits, 3);
    } elseif ($digits !== '') {
        $phoneFmt = '(974)' . $digits;
    }
}
?>
<div class="<?= esc($wrapClass) ?>"<?= $wrapStyle !== '' ? ' style="' . esc($wrapStyle) . '"' : '' ?>>
  <?php if ($addr !== ''): ?>
  <div class="fm-company-contact-line"><?= nl2br(esc($addr)) ?></div>
  <?php endif; ?>
  <?php if ($phone !== ''): ?>
  <div class="fm-company-contact-line"><?= $plain ? 'Tel: ' : '<i class="bi bi-telephone me-1" aria-hidden="true"></i>' ?><?= esc($phoneFmt) ?></div>
  <?php endif; ?>
  <?php if ($cr !== ''): ?>
  <div class="fm-company-contact-line">CR: <?= esc($cr) ?></div>
  <?php endif; ?>
  <?php if ($poBox !== ''): ?>
  <div class="fm-company-contact-line">P.O. Box: <?= esc($poBox) ?></div>
  <?php endif; ?>
  <?php if ($email !== ''): ?>
  <div class="fm-company-contact-line">
    <?php if ($plain): ?>
    <?= esc($email) ?>
    <?php else: ?>
    <i class="bi bi-envelope me-1" aria-hidden="true"></i><a href="mailto:<?= esc($email) ?>" class="text-reset"><?= esc($email) ?></a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  <?php if ($website !== ''): ?>
  <div class="fm-company-contact-line"><?= esc($website) ?></div>
  <?php endif; ?>
</div>
