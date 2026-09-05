<?php
/** @var bool $isParking */
/** @var string $token */
/** @var bool $alreadySigned */
/** @var array<string,mixed> $contract */
$primary      = esc($settings['primary_color'] ?? '#76002b');
$signUrl      = base_url('contract/sign/' . rawurlencode((string) $token));
$loginUrl     = base_url('login?redirect=' . rawurlencode($signUrl));
$loggedIn     = (bool) session()->get('logged_in');
$companyName  = trim((string) ($settings['company_name'] ?? 'FM ERP'));
$contractNo   = ! empty($isParking)
    ? trim((string) ($d['contract_number'] ?? $d['parking_unit_no'] ?? $contract['contract_number'] ?? ''))
    : trim((string) ($contract['contract_number'] ?? ''));
$tenantName   = trim((string) (! empty($isParking)
    ? ($d['tenant_name'] ?? $contract['tenant_name'] ?? '')
    : ($contract['tenant_name'] ?? '')));
$propertyName = trim((string) ($contract['facility_name'] ?? ''));
$unitNo       = trim((string) (! empty($isParking)
    ? ($d['parking_unit_no'] ?? $contract['unit_number'] ?? '')
    : ($contract['unit_number'] ?? '')));
$startDate    = trim((string) (! empty($isParking)
    ? ($d['start_date'] ?? $contract['start_date'] ?? '')
    : ($contract['start_date'] ?? '')));
$endDate      = trim((string) (! empty($isParking)
    ? ($d['end_date'] ?? $contract['end_date'] ?? '')
    : ($contract['end_date'] ?? '')));
$contractKind = ! empty($isParking) ? 'Parking Lease Contract' : 'Lease Contract';
$docTitle     = $contractKind . ($contractNo !== '' ? ' — ' . esc($contractNo) : '');
$pageTitle    = ($title ?? 'Sign Contract') . ' — ' . $docTitle;
$metaStatus   = $alreadySigned ? 'Signed' : 'Awaiting tenant signature';
$metaDesc     = implode(' · ', array_filter([
    $companyName,
    $contractNo !== '' ? 'Contract ' . $contractNo : '',
    $tenantName !== '' ? 'Tenant: ' . $tenantName : '',
    $propertyName !== '' ? $propertyName : '',
    $unitNo !== '' ? 'Unit ' . $unitNo : '',
    ($startDate !== '' && $endDate !== '') ? $startDate . ' – ' . $endDate : '',
    $metaStatus,
]));
$ogTitle      = $docTitle;
$ogImage      = '';
if (! empty($companyLogoUrl)) {
    $ogImage = str_starts_with((string) $companyLogoUrl, 'http')
        ? (string) $companyLogoUrl
        : base_url(ltrim((string) $companyLogoUrl, '/'));
}
$pdfFilename = preg_replace('/[^A-Za-z0-9_-]/', '_', $contractNo !== '' ? $contractNo : ('Contract_' . (int) ($contract['id'] ?? 0))) . '.pdf';
?>
<!DOCTYPE html>
<html lang="en" prefix="og: https://ogp.me/ns#">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc($pageTitle) ?></title>
<meta name="description" content="<?= esc($metaDesc) ?>">
<meta name="robots" content="noindex, nofollow">
<meta property="og:locale" content="en_US">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= esc($companyName) ?>">
<meta property="og:title" content="<?= esc($ogTitle) ?>">
<meta property="og:description" content="<?= esc($metaDesc) ?>">
<meta property="og:url" content="<?= esc($signUrl) ?>">
<?php if ($ogImage !== ''): ?>
<meta property="og:image" content="<?= esc($ogImage) ?>">
<meta property="og:image:alt" content="<?= esc($companyName) ?> logo">
<?php endif; ?>
<meta name="twitter:card" content="<?= $ogImage !== '' ? 'summary' : 'summary' ?>">
<meta name="twitter:title" content="<?= esc($ogTitle) ?>">
<meta name="twitter:description" content="<?= esc($metaDesc) ?>">
<?php if ($ogImage !== ''): ?>
<meta name="twitter:image" content="<?= esc($ogImage) ?>">
<?php endif; ?>
<?php if ($contractNo !== ''): ?>
<meta name="contract:number" content="<?= esc($contractNo) ?>">
<?php endif; ?>
<?php if ($tenantName !== ''): ?>
<meta name="contract:tenant" content="<?= esc($tenantName) ?>">
<?php endif; ?>
<?php if ($propertyName !== ''): ?>
<meta name="contract:property" content="<?= esc($propertyName) ?>">
<?php endif; ?>
<?php if ($unitNo !== ''): ?>
<meta name="contract:unit" content="<?= esc($unitNo) ?>">
<?php endif; ?>
<?php if ($startDate !== ''): ?>
<meta name="contract:start_date" content="<?= esc($startDate) ?>">
<?php endif; ?>
<?php if ($endDate !== ''): ?>
<meta name="contract:end_date" content="<?= esc($endDate) ?>">
<?php endif; ?>
<meta name="contract:status" content="<?= esc($metaStatus) ?>">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= base_url('assets/css/contract-signature.css') ?>" rel="stylesheet">
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
  .sign-body { padding: 72px 12px 24px; }
  .sign-body.has-submit-bar { padding-bottom: calc(88px + env(safe-area-inset-bottom, 0px)); }
  .sign-submit-bar {
    position: fixed; bottom: 0; left: 0; right: 0; z-index: 100;
    padding: 12px 16px; padding-bottom: max(12px, env(safe-area-inset-bottom));
    background: rgba(255,255,255,.98); border-top: 1px solid #dde3ea;
    box-shadow: 0 -4px 20px rgba(0,0,0,.08);
  }
  .sign-submit-inner { max-width: 210mm; margin: 0 auto; }
  .sign-submit-bar .submit-btn-main {
    width: 100%; background: #198754; color: #fff; border: none;
    padding: 14px 20px; border-radius: 10px; font-size: 15px; font-weight: 600;
    cursor: pointer; font-family: 'DM Sans', sans-serif;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    box-shadow: 0 4px 14px rgba(25, 135, 84, .35);
  }
  .sign-submit-bar .submit-btn-main:active { transform: translateY(1px); }
  .sign-submit-hint { text-align: center; font-size: 11px; color: #666; margin-top: 6px; }
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
  .pdf-status {
    position: fixed; inset: 0; z-index: 200; background: rgba(255,255,255,.92);
    display: none; align-items: center; justify-content: center; flex-direction: column;
  }
  .pdf-status.is-active { display: flex; }
  h1.contract-title { font-size: 16px; text-align: center; margin: 14px 0 4px; text-transform: uppercase; letter-spacing: .5px; font-weight: 700; }
  .contract-sub { text-align: center; font-size: 11px; color: #666; margin-bottom: 18px; }
  .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 4px 20px; margin-bottom: 18px; }
  .info-row { display: flex; gap: 6px; font-size: 11px; padding: 4px 0; border-bottom: 1px dotted #ddd; }
  .info-row label { font-weight: 700; min-width: 120px; color: #555; }
  .section-title { font-size: 12px; font-weight: 700; background: #faf7f8; padding: 6px 10px; margin: 16px 0 8px; border-left: 3px solid <?= $primary ?>; }
  .content-en { margin-bottom: 0; line-height: 1.75; font-size: 11px; }
  .content-ar { direction: rtl; text-align: right; line-height: 1.75; font-size: 11px; font-family: 'Cairo', Arial, sans-serif; margin-bottom: 0; }
  .signature-line { border-top: 1px solid #333; margin: 36px 0 6px; }
  .signature-party { text-align: center; font-size: 11px; }
  .signature-img { max-height: 64px; max-width: 100%; margin: 0 auto 6px; display: block; }
  .signature-label { font-size: 10px; color: #666; }
  .signed-banner {
    max-width: 210mm; margin: 0 auto 12px; padding: 10px 14px; border-radius: 8px;
    background: #d1e7dd; color: #0f5132; font-size: 12px; text-align: center;
  }
  @media print {
    body { background: #fff; }
    .sign-toolbar, .sign-submit-bar, .flash-wrap, .signed-banner, .sign-pad-clear, .pdf-status { display: none !important; }
    .sign-body, .sign-body.has-submit-bar { padding: 0; }
    .page { box-shadow: none; border-radius: 0; padding: 10mm; max-width: none; }
    @page { size: A4 portrait; margin: 8mm; }
  }
  @media (max-width: 768px) {
    .info-grid { grid-template-columns: 1fr; }
    .sign-toolbar { position: static; }
    .sign-body { padding: 12px 12px 24px; }
    .sign-body.has-submit-bar { padding-bottom: calc(96px + env(safe-area-inset-bottom, 0px)); }
    .page { padding: 8mm 6mm; }
    .bilingual .col-en, .bilingual .col-ar,
    .signatures .col-en, .signatures .col-ar {
      font-size: 9px;
      padding-left: 4px;
      padding-right: 4px;
    }
  }
</style>
</head>
<body>

<div id="pdfStatus" class="pdf-status">
  <div style="font-size:16px;font-weight:600;margin-bottom:8px">Creating PDF…</div>
  <div style="font-size:13px;color:#666">English left · Arabic right</div>
</div>

<div class="sign-toolbar no-print">
  <div class="sign-toolbar-title"><i class="bi bi-file-earmark-text me-1"></i><?= $docTitle ?></div>
  <div class="sign-toolbar-actions">
    <?php if (! $loggedIn): ?>
      <a href="<?= esc($loginUrl) ?>" class="secondary"><i class="bi bi-box-arrow-in-right"></i>Sign in</a>
    <?php endif; ?>
    <button type="button" class="secondary" id="downloadContractPdf"><i class="bi bi-file-earmark-pdf"></i>PDF</button>
    <button type="button" class="secondary" onclick="window.print()"><i class="bi bi-printer"></i>Print</button>
  </div>
</div>

<div class="sign-body<?= ! $alreadySigned ? ' has-submit-bar' : '' ?>">
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
      You may print, download PDF, or close this page.
    </div>
  <?php endif; ?>

  <?php if (! $alreadySigned): ?>
    <?= form_open($signUrl, ['id' => 'tenant-sign-form']) ?>
      <?= csrf_field() ?>
  <?php endif; ?>

  <div class="page" id="contractSignSnapshot">
    <?php if (! empty($isParking)): ?>
      <?= $this->include('leases/partials/parking_contract_document', [
          'signMode' => ! empty($signMode),
          'alreadySigned' => ! empty($alreadySigned),
      ]) ?>
    <?php else: ?>
      <?= $this->include('leases/partials/standard_contract_document', [
          'signMode' => ! empty($signMode),
          'alreadySigned' => ! empty($alreadySigned),
      ]) ?>
    <?php endif; ?>
  </div>

  <?php if (! $alreadySigned): ?>
    <?= form_close() ?>
  <?php endif; ?>
</div>

<?php if (! $alreadySigned): ?>
<div class="sign-submit-bar no-print">
  <div class="sign-submit-inner">
    <button type="submit" form="tenant-sign-form" class="submit-btn-main">
      <i class="bi bi-pen-fill"></i>Submit Signature
    </button>
    <div class="sign-submit-hint">Sign on the tenant line (English / Arabic columns), then submit</div>
  </div>
</div>
<?php endif; ?>

<script src="<?= base_url('assets/js/signature-pad.js') ?>" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSRenI4km2k5z4WboRn0cNq0Y8WoW902Z1aNX/Y+0P3jFtkj9l7j8ig==" crossorigin="anonymous"></script>
<script>
(function () {
  var pageEl = document.getElementById('contractSignSnapshot');
  var statusEl = document.getElementById('pdfStatus');
  var filename = <?= json_encode($pdfFilename, JSON_UNESCAPED_UNICODE) ?>;

  function runPdf() {
    if (!pageEl || typeof html2pdf !== 'function') {
      if (statusEl) statusEl.classList.remove('is-active');
      return Promise.reject(new Error('PDF unavailable'));
    }
    if (statusEl) statusEl.classList.add('is-active');
    var toolbar = document.querySelector('.sign-toolbar');
    var submitBar = document.querySelector('.sign-submit-bar');
    if (toolbar) toolbar.style.display = 'none';
    if (submitBar) submitBar.style.display = 'none';

    var opt = {
      margin: [8, 10, 14, 10],
      filename: filename,
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: {
        scale: 2,
        useCORS: true,
        allowTaint: true,
        letterRendering: true,
        scrollX: 0,
        scrollY: 0,
        backgroundColor: '#ffffff'
      },
      jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
      pagebreak: { mode: ['css', 'legacy'] }
    };

    return html2pdf().set(opt).from(pageEl).save().finally(function () {
      if (toolbar) toolbar.style.display = '';
      if (submitBar) submitBar.style.display = '';
      if (statusEl) statusEl.classList.remove('is-active');
    });
  }

  var btn = document.getElementById('downloadContractPdf');
  if (btn) {
    btn.addEventListener('click', function () {
      var start = function () { runPdf().catch(function () { alert('Could not create PDF. Try Print instead.'); }); };
      if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(function () { setTimeout(start, 300); });
      } else {
        setTimeout(start, 600);
      }
    });
  }
})();
</script>
</body>
</html>
