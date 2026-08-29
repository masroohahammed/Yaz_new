<?php
/**
 * Shared footer for contracts, invoices, estimations (no VAT).
 *
 * @var array  $settings
 * @var bool   $plain
 * @var array|null $companyBranding
 */
if (is_array($companyBranding ?? null)) {
    $settings = $companyBranding['settings'] ?? $settings;
}
$phone   = trim((string) ($settings['company_phone'] ?? ''));
$cr      = trim((string) ($settings['company_cr'] ?? ''));
$poBox   = trim((string) ($settings['company_po_box'] ?? ''));
$addr    = trim((string) ($settings['company_address'] ?? ''));
$website = trim((string) ($settings['company_website'] ?? ''));

if ($phone === '' && $cr === '' && $poBox === '' && $addr === '' && $website === '') {
    return;
}

$phoneFmt = $phone;
if ($phone !== '') {
    $digits = preg_replace('/\D/', '', $phone);
    if (str_starts_with($digits, '974')) {
        $phoneFmt = '(974)' . substr($digits, 3);
    } elseif ($digits !== '') {
        $phoneFmt = '(974)' . $digits;
    }
}

$parts = [];
if ($phoneFmt !== '') {
    $parts[] = 'Phone:' . $phoneFmt;
}
if ($cr !== '') {
    $parts[] = 'C.R No:' . $cr;
}
if ($poBox !== '') {
    $parts[] = 'PO.BOX:' . $poBox;
}
if ($addr !== '') {
    $parts[] = str_replace(["\r\n", "\n"], ', ', $addr);
}
if ($website !== '') {
    $parts[] = $website;
}
?>
<div class="doc-footer-line" style="text-align:center;font-size:10px;color:#555;margin-top:20px;padding-top:10px;border-top:1px solid #ccc;line-height:1.55">
  <?= esc(implode(', ', $parts)) ?>
</div>
