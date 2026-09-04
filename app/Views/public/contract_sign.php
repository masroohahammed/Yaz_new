<?php
/** @var bool $isParking */
/** @var string $token */
/** @var bool $alreadySigned */
/** @var array<string,mixed> $contract */
$primary   = esc($settings['primary_color'] ?? '#76002b');
$signUrl   = base_url('contract/sign/' . rawurlencode((string) $token));
$loginUrl  = base_url('login?redirect=' . rawurlencode($signUrl));
$loggedIn  = (bool) session()->get('logged_in');
$docTitle  = ! empty($isParking)
    ? 'Parking Contract — ' . esc($d['contract_number'] ?? $d['parking_unit_no'] ?? '')
    : 'Lease Contract — ' . esc($contract['contract_number'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc($title ?? 'Sign Contract') ?> — <?= $docTitle ?></title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
  * { box-sizing: border-box; }
  body { margin: 0; background: #eef1f5; color: #333; line-height: 1.55; font-family: 'DM Sans', Arial, sans-serif; }
  .sign-toolbar {
    position: fixed; top: 0; left: 0; right: 0; z-index: 100;
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    padding: 10px 16px; background: rgba(255,255,255,.97); border-bottom: 1px solid #dde3ea;
    box-shadow: 0 2px 12px rgba(0,0,0,.06); flex-wrap: wrap;
  }
  .sign-toolbar-title { font-size: 13px; font-weight: 600; color: #1a1a1a; }
  .sign-toolbar-actions { display: flex; gap: 8px; flex-wrap: wrap; }
  .sign-toolbar-actions a, .sign-toolbar-actions button {
    background: <?= $primary ?>; color: #fff; border: none; padding: 8px 14px; border-radius: 8px;
    font-size: 12px; text-decoration: none; cursor: pointer; font-family: 'DM Sans', sans-serif;
    display: inline-flex; align-items: center; gap: 6px;
  }
  .sign-toolbar-actions a.secondary, .sign-toolbar-actions button.secondary {
    background: #fff; color: <?= $primary ?>; border: 1px solid #cfd8e3;
  }
  .sign-toolbar-actions button.submit-btn { background: #198754; }
  .sign-body { padding: 72px 12px 24px; }
  .page {
    max-width: 210mm; margin: 0 auto; padding: 12mm 14mm;
    background: #fff; box-shadow: 0 8px 32px rgba(0,0,0,.08); border-radius: 4px;
  }
  .flash-wrap { max-width: 210mm; margin: 0 auto 12px; }
  .flash {
    padding: 10px 14px; border-radius: 8px; font-size: 12px; margin-bottom: 8px;
  }
  .flash-success { background: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; }
  .flash-error { background: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }
  .flash-info { background: #cff4fc; color: #055160; border: 1px solid #b6effb; }
  /* Standard lease styles */
  h1.contract-title { font-size: 16px; text-align: center; margin: 14px 0 4px; text-transform: uppercase; letter-spacing: .5px; font-weight: 700; }
  .contract-sub { text-align: center; font-size: 11px; color: #666; margin-bottom: 18px; }
  .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 20px; margin-bottom: 18px; }
  .info-row { display: flex; gap: 6px; font-size: 11px; padding: 4px 0; border-bottom: 1px dotted #ddd; }
  .info-row label { font-weight: 700; min-width: 120px; color: #555; }
  .section-title { font-size: 12px; font-weight: 700; background: #faf7f8; padding: 6px 10px; margin: 16px 0 8px; border-left: 3px solid <?= $primary ?>; }
  .content-en { margin-bottom: 20px; line-height: 1.75; font-size: 11px; }
  .content-ar { direction: rtl; text-align: right; line-height: 1.75; font-size: 11px; font-family: 'Cairo', Arial, sans-serif; margin-bottom: 20px; }
  .divider { border: none; border-top: 1px dashed #bbb; margin: 18px 0; }
  .signature-block { display: grid; grid-template-columns: 1fr 1fr; gap: 36px; margin-top: 36px; }
  .signature-party { text-align: center; font-size: 11px; }
  .signature-line { border-top: 1px solid #333; margin: 36px 0 6px; }
  .signature-img { max-height: 64px; max-width: 100%; margin: 0 auto 6px; display: block; }
  .signature-label { font-size: 10px; color: #666; }
  .signature-hint { font-size: 9px; color: #666; margin-top: 6px; }
  /* Parking lease styles */
  .bilingual { display: grid; grid-template-columns: 1fr 1fr; gap: 0 14px; align-items: start; }
  .col-en { font-family: 'DM Sans', Arial, sans-serif; font-size: 10.5px; direction: ltr; text-align: left; }
  .col-ar { font-family: 'Cairo', 'Traditional Arabic', sans-serif; font-size: 10.5px; direction: rtl; text-align: right; }
  .doc-title-en { font-weight: 700; font-size: 12px; text-transform: uppercase; letter-spacing: .3px; margin: 8px 0 4px; text-align: center; }
  .doc-title-ar { font-weight: 700; font-size: 12px; margin: 8px 0 4px; text-align: center; }
  .block { margin-bottom: 8px; }
  .clause-title { font-weight: 700; margin: 10px 0 4px; display: block; }
  .highlight { font-weight: 700; }
  ol.en { margin: 4px 0 0; padding-left: 14px; }
  ol.ar { margin: 4px 0 0; padding-right: 14px; }
  ol.en li, ol.ar li { margin-bottom: 3px; }
  .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 20px; }
  .sig-line { border-top: 1px solid #333; margin-top: 36px; padding-top: 4px; font-size: 9.5px; }
  .sig-img { max-height: 56px; max-width: 100%; display: block; margin: 0 auto 4px; }
  .landlord-line { text-align: center; font-family: 'DM Sans', sans-serif; font-size: 11px; font-weight: 600; margin: 6px 0 10px; color: #333; }
  /* Signature pad in document */
  .sign-pad-wrap { border: 1px dashed #999; background: #fff; border-radius: 4px; margin: 8px 0 4px; }
  .sign-pad-clear {
    background: none; border: none; color: #666; font-size: 10px; cursor: pointer;
    text-decoration: underline; padding: 0; margin-top: 2px;
  }
  .signed-banner {
    max-width: 210mm; margin: 0 auto 12px; padding: 10px 14px; border-radius: 8px;
    background: #d1e7dd; color: #0f5132; font-size: 12px; text-align: center;
  }
  @media print {
    body { background: #fff; }
    .sign-toolbar, .flash-wrap, .signed-banner, .sign-pad-clear, .submit-btn { display: none !important; }
    .sign-body { padding: 0; }
    .page { box-shadow: none; border-radius: 0; padding: 10mm; }
    @page { size: A4 portrait; margin: 8mm; }
  }
  @media (max-width: 768px) {
    .info-grid, .signature-block, .bilingual, .signatures { grid-template-columns: 1fr; }
    .sign-toolbar { position: static; }
    .sign-body { padding: 12px; }
  }
</style>
</head>
<body>

<div class="sign-toolbar no-print">
  <div class="sign-toolbar-title"><i class="bi bi-file-earmark-text me-1"></i><?= $docTitle ?></div>
  <div class="sign-toolbar-actions">
    <?php if (! $loggedIn): ?>
      <a href="<?= esc($loginUrl) ?>" class="secondary"><i class="bi bi-box-arrow-in-right"></i>Sign in</a>
    <?php endif; ?>
    <button type="button" class="secondary" onclick="window.print()"><i class="bi bi-printer"></i>Print</button>
    <?php if (! $alreadySigned): ?>
      <button type="submit" form="tenant-sign-form" class="submit-btn"><i class="bi bi-pen"></i>Submit Signature</button>
    <?php endif; ?>
  </div>
</div>

<div class="sign-body">
  <div class="flash-wrap no-print">
    <?php if ($msg = session()->getFlashdata('success')): ?>
      <div class="flash flash-success"><i class="bi bi-check-circle me-1"></i><?= esc($msg) ?></div>
    <?php endif; ?>
    <?php if ($msg = session()->getFlashdata('error')): ?>
      <div class="flash flash-error"><i class="bi bi-exclamation-triangle me-1"></i><?= esc($msg) ?></div>
    <?php endif; ?>
    <?php if ($msg = session()->getFlashdata('info')): ?>
      <div class="flash flash-info"><i class="bi bi-info-circle me-1"></i><?= esc($msg) ?></div>
    <?php endif; ?>
  </div>

  <?php if ($alreadySigned): ?>
    <div class="signed-banner no-print">
      <i class="bi bi-check-circle-fill me-1"></i>
      Contract signed<?= ! empty($signedAt) ? ' · ' . esc(date('d M Y H:i', strtotime((string) $signedAt))) : '' ?>.
      You may print or close this page.
    </div>
  <?php endif; ?>

  <?php if (! $alreadySigned): ?>
    <?= form_open($signUrl, ['id' => 'tenant-sign-form']) ?>
      <?= csrf_field() ?>
  <?php endif; ?>

  <div class="page">
    <?php if (! empty($isParking)): ?>
      <?= $this->include('leases/partials/parking_contract_document', [
          'signMode' => true,
          'alreadySigned' => $alreadySigned,
      ]) ?>
    <?php else: ?>
      <?= $this->include('leases/partials/standard_contract_document', [
          'signMode' => true,
          'alreadySigned' => $alreadySigned,
      ]) ?>
    <?php endif; ?>
  </div>

  <?php if (! $alreadySigned): ?>
    <?= form_close() ?>
  <?php endif; ?>
</div>

<script src="<?= base_url('assets/js/signature-pad.js') ?>"></script>
</body>
</html>
