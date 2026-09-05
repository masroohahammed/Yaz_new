<?php
/**
 * Bilingual parking lease — English left, Arabic right.
 *
 * @var array<string,mixed> $d
 * @var bool                $snapshotPdf
 * @var bool                $autoSnapshotPdf
 */
$snapshotPdf     = ! empty($snapshotPdf);
$autoSnapshotPdf = ! empty($autoSnapshotPdf);
$unitNo          = esc($d['parking_unit_no'] ?? '');
$pdfFilename     = $pdfFilename ?? ('Parking_Contract_' . preg_replace('/[^A-Za-z0-9_-]/', '_', (string) ($d['parking_unit_no'] ?? 'unit')) . '.pdf');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Parking Contract — <?= esc($d['contract_number'] ?? $unitNo) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="<?= base_url('assets/css/contract-signature.css') ?>" rel="stylesheet">
<style>
  * { box-sizing: border-box; }
  body { margin: 0; background: #fff; color: #111; line-height: 1.55; font-family: 'DM Sans', Arial, sans-serif; }
  .no-print { position: fixed; top: 12px; right: 12px; z-index: 99; display: flex; gap: 8px; align-items: center; }
  .no-print button, .no-print a { background: #76002b; color: #fff; border: none; padding: 8px 14px; border-radius: 6px; font-size: 12px; text-decoration: none; cursor: pointer; font-family: 'DM Sans', sans-serif; }
  .pdf-status { position: fixed; inset: 0; z-index: 200; background: rgba(255,255,255,.92); display: none; align-items: center; justify-content: center; flex-direction: column; font-family: 'DM Sans', sans-serif; }
  .pdf-status.is-active { display: flex; }
  .page { max-width: 210mm; margin: 0 auto; padding: 10mm 12mm 14mm; background: #fff; }
  .bilingual { display: table; width: 100%; table-layout: fixed; border-collapse: collapse; }
  .bilingual-row { display: table-row; }
  .col-en { display: table-cell; width: 50%; vertical-align: top; padding: 0 7px 8px 0; font-family: 'DM Sans', Arial, sans-serif; font-size: 10.5px; direction: ltr; text-align: left; }
  .col-ar { display: table-cell; width: 50%; vertical-align: top; padding: 0 0 8px 7px; font-family: 'Cairo', 'Traditional Arabic', sans-serif; font-size: 10.5px; direction: rtl; text-align: right; }
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
    .no-print, .pdf-status { display: none !important; }
    .page { padding: 8mm 10mm; }
    @page { size: A4 portrait; margin: 8mm; }
  }
</style>
</head>
<body>
<div id="pdfStatus" class="pdf-status<?= $autoSnapshotPdf ? ' is-active' : '' ?>">
  <div style="font-size:16px;font-weight:600;margin-bottom:8px">Creating PDF snapshot…</div>
  <div style="font-size:13px;color:#666">Capturing the contract exactly as shown</div>
</div>

<div class="no-print">
  <button type="button" onclick="window.print()">Print</button>
  <?php if ($snapshotPdf): ?>
  <button type="button" id="downloadPdfSnapshot">Download PDF</button>
  <?php endif; ?>
</div>

<div class="page" id="parkingContractSnapshot">
  <?= $this->include('leases/partials/parking_contract_document') ?>
</div>

<?php if ($snapshotPdf): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSRenI4km2k5z4WboRn0cNq0Y8WoW902Z1aNX/Y+0P3jFtkj9l7j8ig==" crossorigin="anonymous"></script>
<script>
(function () {
  var pageEl = document.getElementById('parkingContractSnapshot');
  var statusEl = document.getElementById('pdfStatus');
  var filename = <?= json_encode($pdfFilename, JSON_UNESCAPED_UNICODE) ?>;
  var autoStart = <?= $autoSnapshotPdf ? 'true' : 'false' ?>;

  function runSnapshot() {
    if (!pageEl || typeof html2pdf !== 'function') {
      if (statusEl) statusEl.classList.remove('is-active');
      return Promise.reject(new Error('PDF snapshot unavailable'));
    }
    if (statusEl) statusEl.classList.add('is-active');
    var toolbar = document.querySelector('.no-print');
    if (toolbar) toolbar.style.display = 'none';

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
      if (statusEl) statusEl.classList.remove('is-active');
    });
  }

  function whenReady(cb) {
    if (document.fonts && document.fonts.ready) {
      document.fonts.ready.then(function () { setTimeout(cb, 350); });
    } else {
      setTimeout(cb, 800);
    }
  }

  var btn = document.getElementById('downloadPdfSnapshot');
  if (btn) {
    btn.addEventListener('click', function () { whenReady(runSnapshot); });
  }

  if (autoStart) {
    whenReady(runSnapshot);
  }
})();
</script>
<?php endif; ?>
</body>
</html>
