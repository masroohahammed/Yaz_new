<?php
/**
 * Bilingual parking lease — English left, Arabic right (single page).
 *
 * @var array<string,mixed> $d
 */
$rentFmt   = number_format((float) ($d['rent_amount'] ?? 0), 0);
$unitNo    = esc($d['parking_unit_no'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Parking Contract — <?= esc($d['contract_number'] ?? $unitNo) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  * { box-sizing: border-box; }
  body { margin: 0; background: #fff; color: #111; line-height: 1.55; }
  .no-print { position: fixed; top: 12px; right: 12px; z-index: 99; display: flex; gap: 8px; }
  .no-print button, .no-print a { background: #76002b; color: #fff; border: none; padding: 8px 14px; border-radius: 6px; font-size: 12px; text-decoration: none; cursor: pointer; font-family: 'DM Sans', sans-serif; }
  .page { max-width: 210mm; margin: 0 auto; padding: 10mm 12mm 14mm; }
  .bilingual { display: grid; grid-template-columns: 1fr 1fr; gap: 0 14px; align-items: start; }
  .col-en { font-family: 'DM Sans', Arial, sans-serif; font-size: 10.5px; direction: ltr; text-align: left; }
  .col-ar { font-family: 'Cairo', 'Traditional Arabic', sans-serif; font-size: 10.5px; direction: rtl; text-align: right; }
  .doc-title-en { font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: .3px; margin: 8px 0 4px; text-align: center; }
  .doc-title-ar { font-weight: 700; font-size: 12px; margin: 8px 0 4px; text-align: center; }
  .row-pair { display: contents; }
  .block { margin-bottom: 8px; }
  .clause-title { font-weight: 700; margin: 10px 0 4px; display: block; }
  .highlight { font-weight: 700; }
  ol.en { margin: 4px 0 0; padding-left: 14px; }
  ol.ar { margin: 4px 0 0; padding-right: 14px; }
  ol.en li, ol.ar li { margin-bottom: 3px; }
  .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 20px; }
  .sig-line { border-top: 1px solid #333; margin-top: 36px; padding-top: 4px; font-size: 9.5px; }
  .sig-img { max-height: 56px; max-width: 100%; display: block; margin: 0 auto 4px; }
  .tenant-signature-anchor { position: relative; min-height: 68px; margin: 12px 0 0; }
  .tenant-signature-anchor .tenant-signature-line { border-top: 1px solid #333; width: 100%; position: absolute; left: 0; right: 0; bottom: 0; }
  .tenant-signature-anchor .tenant-signature-image { position: absolute; left: 50%; bottom: 6px; transform: translateX(-50%); max-height: 50px; max-width: 92%; display: block; object-fit: contain; object-position: bottom center; }
  .tenant-signature-anchor.is-signing {
    border: 1.5px dashed #888;
    background: #fff;
    border-radius: 6px;
    padding: 8px 10px 0;
    box-sizing: border-box;
  }
  .tenant-signature-anchor.is-signing .sign-pad-wrap {
    position: relative;
    left: auto; right: auto; bottom: auto;
    border: none;
    background: transparent;
    margin: 0 0 4px;
  }
  .tenant-signature-anchor.is-signing .fm-signature-canvas { height: 54px !important; background: #fff; }
  .tenant-signature-anchor.is-signing .tenant-signature-line { position: relative; margin-top: 4px; }
  .tenant-signature-anchor.is-signing .sign-pad-clear { position: absolute; top: 6px; right: 8px; font-size: 9px; background: rgba(255,255,255,.9); border: none; color: #666; cursor: pointer; text-decoration: underline; padding: 2px 4px; }
  .landlord-line { text-align: center; font-family: 'DM Sans', sans-serif; font-size: 11px; font-weight: 600; margin: 6px 0 10px; color: #333; }
  @media print {
    .no-print { display: none !important; }
    .page { padding: 8mm 10mm; }
    @page { size: A4 portrait; margin: 8mm; }
  }
</style>
</head>
<body>
<div class="no-print">
  <button type="button" onclick="window.print()">Print</button>
  <?php if (! empty($pdfUrl)): ?>
  <a href="<?= esc($pdfUrl) ?>" target="_blank">Download PDF</a>
  <?php endif; ?>
</div>

<div class="page">
  <?= $this->include('leases/partials/parking_contract_document') ?>
</div>
</body>
</html>
