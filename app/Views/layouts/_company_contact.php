<?php
/**
 * Company address / phone / email from system settings (invoices, PDFs, prints).
 *
 * @var array  $settings
 * @var string $class      Optional extra CSS classes
 * @var string $style      Optional inline style for print views
 * @var bool   $plain      true = no icons/links (print/PDF)
 */
$addr  = trim((string) ($settings['company_address'] ?? ''));
$phone = trim((string) ($settings['company_phone'] ?? ''));
$email = trim((string) ($settings['company_email'] ?? ''));
if ($addr === '' && $phone === '' && $email === '') {
    return;
}
$plain     = ! empty($plain);
$wrapClass = trim('fm-company-contact ' . ($class ?? ''));
$wrapStyle = $style ?? '';
?>
<div class="<?= esc($wrapClass) ?>"<?= $wrapStyle !== '' ? ' style="' . esc($wrapStyle) . '"' : '' ?>>
  <?php if ($addr !== ''): ?>
  <div class="fm-company-contact-line"><?= nl2br(esc($addr)) ?></div>
  <?php endif; ?>
  <?php if ($phone !== ''): ?>
  <div class="fm-company-contact-line"><?= $plain ? 'Tel: ' : '<i class="bi bi-telephone me-1" aria-hidden="true"></i>' ?><?= esc($phone) ?></div>
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
</div>
