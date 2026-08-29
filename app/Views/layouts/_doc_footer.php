<?php
/**
 * Shared letterhead footer for contracts, invoices, estimations, summaries.
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
$vat     = trim((string) ($settings['company_vat'] ?? ''));
$poBox   = trim((string) ($settings['company_po_box'] ?? ''));
$addr    = trim((string) ($settings['company_address'] ?? ''));
$email   = trim((string) ($settings['company_email'] ?? ''));
$website = trim((string) ($settings['company_website'] ?? ''));
$name    = trim((string) ($settings['company_name'] ?? ''));

if ($phone === '' && $cr === '' && $vat === '' && $poBox === '' && $addr === '' && $email === '' && $website === '' && $name === '') {
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
if ($vat !== '') {
    $parts[] = 'VAT:' . $vat;
}
if ($poBox !== '') {
    $parts[] = 'PO.BOX:' . $poBox;
}
if ($addr !== '') {
    $parts[] = str_replace(["\r\n", "\n"], ', ', $addr);
}
if ($email !== '') {
    $parts[] = 'Email: ' . $email;
}
if ($website !== '') {
    $parts[] = $website;
}
?>
<div class="doc-footer-line" style="text-align:center;font-size:9px;color:#555;margin-top:18px;padding-top:8px;border-top:1px solid #ccc;line-height:1.5">
  <?= esc(implode(', ', $parts)) ?>
</div>
