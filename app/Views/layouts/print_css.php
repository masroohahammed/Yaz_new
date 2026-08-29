<?php
/**
 * Shared print/PDF stylesheet partial.
 * Include with:  <?= $this->include('layouts/print_css') ?>
 * Or echo directly inside a <style> block in standalone PDF views.
 *
 * Expects: $settings['primary_color'], $settings['secondary_color']
 */
$primaryColor   = $settings['primary_color']   ?? '#76002b';
$secondaryColor = $settings['secondary_color'] ?? '#c7ba9a';
function _printHexDarken(string $hex, int $pct = 15): string {
    $hex = ltrim($hex,'#');
    if(strlen($hex)===3)$hex=$hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    [$r,$g,$b]=[hexdec(substr($hex,0,2)),hexdec(substr($hex,2,2)),hexdec(substr($hex,4,2))];
    $f=1-$pct/100;
    return sprintf('#%02x%02x%02x',max(0,(int)($r*$f)),max(0,(int)($g*$f)),max(0,(int)($b*$f)));
}
function _printHexToRgb(string $hex): string {
    $hex=ltrim($hex,'#');
    if(strlen($hex)===3)$hex=$hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    return hexdec(substr($hex,0,2)).','.hexdec(substr($hex,2,2)).','.hexdec(substr($hex,4,2));
}
$primaryRgb   = _printHexToRgb($primaryColor);
$secondaryRgb = _printHexToRgb($secondaryColor);
?>
/* ── DOCUMENT / PDF SHARED STYLES ───────────────────────── */
:root{
  --doc-primary:<?= $primaryColor ?>;
  --doc-secondary:<?= $secondaryColor ?>;
  --doc-primary-light:rgba(<?= $primaryRgb ?>,.08);
  --doc-secondary-light:rgba(<?= $secondaryRgb ?>,.15);
}
body{font-family:'DM Sans',Arial,sans-serif;color:#1a2332;font-size:13px;line-height:1.5;margin:0;padding:0}
/* Document header */
.doc-header{display:flex;justify-content:space-between;align-items:flex-start;padding:24px 28px 16px;background:#fff;border-bottom:3px solid <?= $primaryColor ?>}
.doc-brand{}
.doc-logo{max-height:60px;max-width:180px;object-fit:contain;display:block;margin-bottom:4px}
.doc-brand-text{font-size:1.3rem;font-weight:700;color:<?= $primaryColor ?>;line-height:1.1}
.doc-tagline{font-size:.68rem;color:#6b7a8d;text-transform:uppercase;letter-spacing:.8px;margin-top:3px}
.doc-title-block{text-align:right}
.doc-type{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:<?= $secondaryColor ?>;margin-bottom:4px}
.doc-number{font-size:1.25rem;font-weight:700;color:<?= $primaryColor ?>}
.doc-status{display:inline-block;padding:3px 12px;border-radius:20px;font-size:.68rem;font-weight:700;margin-top:4px;text-transform:uppercase;letter-spacing:.5px}
.doc-divider{height:1px;background:linear-gradient(90deg,<?= $primaryColor ?>,<?= $secondaryColor ?>,transparent);margin:0 28px}
/* Meta info grid */
.doc-meta{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;padding:20px 28px;background:#fafafa;border-bottom:1px solid #e8edf3}
.doc-meta-item .meta-label{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#9ca3af;margin-bottom:3px}
.doc-meta-item .meta-value{font-size:.85rem;font-weight:600;color:#1a2332}
/* Table */
.doc-table{width:100%;border-collapse:collapse;margin:0}
.doc-table th{background:<?= $primaryColor ?>;color:#fff;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;padding:9px 14px;text-align:left}
.doc-table th:last-child{text-align:right}
.doc-table td{padding:9px 14px;border-bottom:1px solid #f0f4f8;font-size:.82rem;vertical-align:top}
.doc-table td:last-child{text-align:right;font-weight:600}
.doc-table tr:nth-child(even) td{background:#fdf8f6}
.doc-table tfoot td{font-weight:700;border-top:2px solid <?= $primaryColor ?>;background:var(--doc-primary-light)}
.doc-table tfoot tr.total-row td{font-size:1rem;color:<?= $primaryColor ?>}
/* Sections */
.doc-section{padding:20px 28px}
.doc-section-title{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:<?= $primaryColor ?>;margin-bottom:12px;padding-bottom:6px;border-bottom:2px solid var(--doc-secondary-light)}
/* Checklist */
.checklist-item{display:flex;align-items:center;gap:12px;padding:8px 0;border-bottom:1px solid #f5f5f5}
.checklist-item:last-child{border-bottom:none}
.check-box{width:18px;height:18px;border:2px solid <?= $primaryColor ?>;border-radius:4px;flex-shrink:0;display:flex;align-items:center;justify-content:center}
.check-box.checked{background:<?= $primaryColor ?>;color:#fff;font-size:.7rem}
.check-label{font-size:.82rem;flex:1}
.check-status{font-size:.72rem;font-weight:600;padding:2px 8px;border-radius:10px}
/* Signature block */
.sig-block{border-top:1px dashed #ccc;margin-top:8px;padding-top:6px;text-align:center}
.sig-label{font-size:.68rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.5px;margin-top:4px}
/* Footer */
.doc-footer{padding:12px 28px;border-top:2px solid <?= $primaryColor ?>;background:var(--doc-primary-light);display:flex;justify-content:space-between;align-items:center;margin-top:auto}
.doc-footer-brand{font-weight:700;color:<?= $primaryColor ?>;font-size:.8rem}
.doc-footer-page{font-size:.72rem;color:#9ca3af}
/* Print rules */
@media print{
  .no-print{display:none!important}
  body{-webkit-print-color-adjust:exact;print-color-adjust:exact}
  .doc-header{break-inside:avoid}
  .doc-table{break-inside:auto}
  .doc-table tr{break-inside:avoid}
}
