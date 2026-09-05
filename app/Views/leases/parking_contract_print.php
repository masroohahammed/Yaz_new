<?php
/**
 * Bilingual parking lease — English left, Arabic right.
 *
 * @var array<string,mixed> $d
 * @var bool                $forDompdf
 */
$forDompdf = ! empty($forDompdf);
$unitNo    = esc($d['parking_unit_no'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Parking Contract — <?= esc($d['contract_number'] ?? $unitNo) ?></title>
<?php if (! $forDompdf): ?>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="<?= base_url('assets/css/contract-signature.css') ?>" rel="stylesheet">
<?php endif; ?>
<style>
  * { box-sizing: border-box; }
  body { margin: 0; background: #fff; color: #111; line-height: 1.55; font-family: <?= $forDompdf ? 'DejaVu Sans, sans-serif' : "'DM Sans', Arial, sans-serif" ?>; }
  .no-print { position: fixed; top: 12px; right: 12px; z-index: 99; display: flex; gap: 8px; }
  .no-print button, .no-print a { background: #76002b; color: #fff; border: none; padding: 8px 14px; border-radius: 6px; font-size: 12px; text-decoration: none; cursor: pointer; font-family: 'DM Sans', sans-serif; }
  .page { max-width: 210mm; margin: 0 auto; padding: 10mm 12mm 14mm; }
  .bilingual { display: table; width: 100%; table-layout: fixed; border-collapse: collapse; }
  .bilingual-row { display: table-row; }
  .col-en { display: table-cell; width: 50%; vertical-align: top; padding: 0 7px 8px 0; font-family: <?= $forDompdf ? 'DejaVu Sans, sans-serif' : "'DM Sans', Arial, sans-serif" ?>; font-size: 10.5px; direction: ltr; text-align: left; }
  .col-ar { display: table-cell; width: 50%; vertical-align: top; padding: 0 0 8px 7px; font-family: <?= $forDompdf ? 'DejaVu Sans, sans-serif' : "'Cairo', 'Traditional Arabic', sans-serif" ?>; font-size: 10.5px; direction: rtl; text-align: right; }
  .doc-title-en { font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: .3px; margin: 8px 0 4px; text-align: center; }
  .doc-title-ar { font-weight: 700; font-size: 12px; margin: 8px 0 4px; text-align: center; }
  .block { margin-bottom: 8px; }
  .clause-title { font-weight: 700; margin: 10px 0 4px; display: block; }
  .highlight { font-weight: 700; }
  ol.en { margin: 4px 0 0; padding-left: 14px; }
  ol.ar { margin: 4px 0 0; padding-right: 14px; }
  ol.en li, ol.ar li { margin-bottom: 3px; }
  .signatures { display: table; width: 100%; table-layout: fixed; border-collapse: collapse; margin-top: 20px; }
  .sig-row { display: table-row; }
  .signatures .col-en, .signatures .col-ar { font-size: 10px; padding-top: 8px; }
  .sig-line { border-top: 1px solid #333; margin-top: 36px; padding-top: 4px; font-size: 9.5px; }
  .landlord-line { text-align: center; font-size: 11px; font-weight: 600; margin: 6px 0 10px; color: #333; }
  .contract-photos-wrap { text-align: center; padding: 8px 0 12px; }
  .contract-photos-wrap img { max-height: 140px; max-width: 31%; object-fit: contain; border: 1px solid #ccc; margin: 0 4px; padding: 4px; background: #fff; }
  .parking-tenant-sign-box { margin-top: 10px; width: 100%; }
  .parking-tenant-sign-inner { height: 68px; text-align: center; vertical-align: bottom; line-height: 68px; }
  .parking-tenant-sign-img { max-height: 62px; max-width: 96%; vertical-align: bottom; display: inline-block; object-fit: contain; object-position: bottom center; }
  .parking-tenant-sign-line { border-top: 1px solid #333; width: 100%; height: 0; margin-top: 0; }
  .tenant-signature-anchor { position: relative; min-height: 120px; margin: 16px 0 8px; width: 100%; }
  .tenant-signature-anchor .tenant-signature-line { border-top: 1px solid #333; width: 100%; position: absolute; left: 0; right: 0; bottom: 0; }
  .tenant-signature-anchor .tenant-signature-image { position: absolute; left: 50%; bottom: 10px; transform: translateX(-50%); max-height: 84px; max-width: 96%; display: block; object-fit: contain; object-position: bottom center; }
  .tenant-signature-anchor.is-signing { border: 1.5px dashed #888; background: #fff; border-radius: 8px; padding: 12px 12px 4px; min-height: 148px; }
  .tenant-signature-anchor.is-signing .fm-signature-canvas { width: 100% !important; height: 120px !important; background: #fff; display: block; }
  @media print {
    .no-print { display: none !important; }
    .page { padding: 8mm 10mm; }
    @page { size: A4 portrait; margin: 8mm; }
  }
</style>
</head>
<body>
<?php if (! $forDompdf): ?>
<div class="no-print">
  <button type="button" onclick="window.print()">Print</button>
  <?php if (! empty($pdfUrl)): ?>
  <a href="<?= esc($pdfUrl) ?>" target="_blank">Download PDF</a>
  <?php endif; ?>
</div>
<?php endif; ?>

<div class="page">
  <?= $this->include('leases/partials/parking_contract_document') ?>
</div>
</body>
</html>
