<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lease Contract — <?= esc($contract['contract_number']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="<?= base_url('assets/css/contract-signature.css') ?>" rel="stylesheet">
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'DM Sans', Arial, sans-serif; font-size: 12px; color: #333; background: #fff; }
  .page { max-width: 210mm; margin: 0 auto; padding: 12mm 14mm; }
  h1.contract-title { font-size: 16px; text-align: center; margin: 14px 0 4px; text-transform: uppercase; letter-spacing: .5px; font-weight: 700; }
  .contract-sub { text-align: center; font-size: 11px; color: #666; margin-bottom: 18px; }
  .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 20px; margin-bottom: 18px; }
  .info-row { display: flex; gap: 6px; font-size: 11px; padding: 4px 0; border-bottom: 1px dotted #ddd; }
  .info-row label { font-weight: 700; min-width: 120px; color: #555; }
  .section-title { font-size: 12px; font-weight: 700; background: #faf7f8; padding: 6px 10px; margin: 16px 0 8px; border-left: 3px solid <?= esc($settings['primary_color'] ?? '#76002b') ?>; }
  .content-en { margin-bottom: 20px; line-height: 1.75; font-size: 11px; }
  .content-ar { direction: rtl; text-align: right; line-height: 1.75; font-size: 11px; font-family: 'Cairo', Arial, sans-serif; margin-bottom: 20px; }
  .divider { border: none; border-top: 1px dashed #bbb; margin: 18px 0; }
  .signature-block { display: grid; grid-template-columns: 1fr 1fr; gap: 36px; margin-top: 36px; }
  .signature-party { text-align: center; font-size: 11px; }
  .signature-line { border-top: 1px solid #333; margin: 36px 0 6px; }
  .signature-img { max-height: 64px; max-width: 100%; margin: 0 auto 6px; display: block; }
  .signature-label { font-size: 10px; color: #666; }
  .print-btn { position: fixed; top: 15px; right: 15px; background: <?= esc($settings['primary_color'] ?? '#76002b') ?>; color: #fff; border: none; padding: 8px 16px; cursor: pointer; border-radius: 8px; font-size: 12px; }
  @media print { .print-btn { display: none; } .page { padding: 10mm; } }
</style>
</head>
<body>
<button class="print-btn" onclick="window.print()">Print</button>
<div class="page">
  <?= $this->include('leases/partials/standard_contract_document') ?>
</div>
</body>
</html>
