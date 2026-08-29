<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= esc($wo['wo_number'] ?? 'Work Order') ?> — <?= esc($settings['company_name'] ?? 'FM ERP') ?></title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
<?php
$primaryColor   = $settings['primary_color']   ?? '#76002b';
$secondaryColor = $settings['secondary_color'] ?? '#c7ba9a';
$companyName    = $settings['company_name']    ?? 'FM ERP';
$companyTagline = $settings['company_tagline'] ?? 'Facility Management ERP';
$logoSrc        = $companyLogoB64 ?? $companyLogoUrl ?? null;

function _woPrintHexDarken($hex,$pct=15){$hex=ltrim($hex,'#');if(strlen($hex)===3)$hex=$hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];[$r,$g,$b]=[hexdec(substr($hex,0,2)),hexdec(substr($hex,2,2)),hexdec(substr($hex,4,2))];$f=1-$pct/100;return sprintf('#%02x%02x%02x',max(0,(int)($r*$f)),max(0,(int)($g*$f)),max(0,(int)($b*$f)));}
function _woPrintRgb($hex){$hex=ltrim($hex,'#');if(strlen($hex)===3)$hex=$hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];return hexdec(substr($hex,0,2)).','.hexdec(substr($hex,2,2)).','.hexdec(substr($hex,4,2));}
$primaryRgb = _woPrintRgb($primaryColor);
?>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',Arial,sans-serif;color:#1a2332;font-size:13px;line-height:1.5;background:#fff}
.page{max-width:800px;margin:0 auto;min-height:100vh;display:flex;flex-direction:column}
/* Header */
.doc-header{display:flex;justify-content:space-between;align-items:flex-start;padding:24px 32px 16px;border-bottom:3px solid <?= $primaryColor ?>}
.doc-logo{max-height:56px;max-width:170px;object-fit:contain;display:block;margin-bottom:4px}
.doc-brand-text{font-size:1.3rem;font-weight:700;color:<?= $primaryColor ?>}
.doc-tagline{font-size:.62rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.8px;margin-top:3px}
.doc-title-area{text-align:right}
.doc-type-label{font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:<?= $secondaryColor ?>;margin-bottom:4px}
.doc-number{font-size:1.2rem;font-weight:700;color:<?= $primaryColor ?>}
.doc-status-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:.65rem;font-weight:700;margin-top:4px;text-transform:uppercase}
/* Meta grid */
.meta-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:0;border-bottom:1px solid #e8edf3}
.meta-cell{padding:14px 16px;border-right:1px solid #f0f4f8}
.meta-cell:last-child{border-right:none}
.meta-label{font-size:.62rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#9ca3af;margin-bottom:3px}
.meta-value{font-size:.82rem;font-weight:600;color:#1a2332}
/* Priority badges */
.pri-critical{background:#fee2e2;color:#991b1b}.pri-high{background:#ffedd5;color:#9a3412}
.pri-medium{background:#fef9c3;color:#854d0e}.pri-low{background:#dcfce7;color:#166534}
.status-badge{display:inline-block;padding:2px 8px;border-radius:12px;font-size:.65rem;font-weight:700}
/* Description */
.section{padding:16px 32px}
.section-title{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:<?= $primaryColor ?>;margin-bottom:10px;padding-bottom:5px;border-bottom:2px solid rgba(<?= $primaryRgb ?>,.15)}
.description-box{background:#fafafa;border-radius:8px;padding:14px;font-size:.83rem;line-height:1.6;border-left:3px solid <?= $secondaryColor ?>}
/* Table */
.items-table{width:100%;border-collapse:collapse;margin-top:10px}
.items-table th{background:<?= $primaryColor ?>;color:#fff;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;padding:8px 14px;text-align:left}
.items-table td{padding:8px 14px;border-bottom:1px solid #f0f4f8;font-size:.8rem}
.items-table tr:nth-child(even) td{background:#fdf8f6}
/* Signature */
.sig-row{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;padding:20px 32px}
.sig-block{border-top:1.5px solid #ccc;padding-top:6px}
.sig-label{font-size:.62rem;text-transform:uppercase;letter-spacing:.5px;color:#9ca3af}
/* Footer */
.doc-footer{margin-top:auto;padding:10px 32px;border-top:2px solid <?= $primaryColor ?>;background:rgba(<?= $primaryRgb ?>,.06);display:flex;justify-content:space-between;align-items:center}
.footer-brand{font-weight:700;color:<?= $primaryColor ?>;font-size:.78rem}
.footer-info{font-size:.68rem;color:#9ca3af}
/* Print */
@media print{body{-webkit-print-color-adjust:exact;print-color-adjust:exact}.no-print{display:none!important}}
</style>
</head>
<body>
<div class="page">

  <!-- Document Header -->
  <div class="doc-header">
    <div>
      <?php if ($logoSrc): ?>
      <img src="<?= esc($logoSrc) ?>" alt="<?= esc($companyName) ?>" class="doc-logo">
      <?php else: ?>
      <div class="doc-brand-text"><?= esc($companyName) ?></div>
      <?php endif; ?>
      <div class="doc-tagline"><?= esc($companyTagline) ?></div>
    </div>
    <div class="doc-title-area">
      <div class="doc-type-label">WORK ORDER</div>
      <div class="doc-number"><?= esc($wo['wo_number'] ?? '—') ?></div>
      <span class="doc-status-badge <?= 'pri-'.esc($wo['priority'] ?? 'low') ?>"><?= ucfirst($wo['priority'] ?? '—') ?> Priority</span>
    </div>
  </div>

  <!-- Meta Grid -->
  <div class="meta-grid">
    <div class="meta-cell">
      <div class="meta-label">Facility</div>
      <div class="meta-value"><?= esc($wo['facility_name'] ?? '—') ?></div>
    </div>
    <div class="meta-cell">
      <div class="meta-label">Category</div>
      <div class="meta-value"><?= esc(ucfirst(str_replace('_',' ',$wo['category'] ?? ''))) ?></div>
    </div>
    <div class="meta-cell">
      <div class="meta-label">Status</div>
      <div class="meta-value"><?= esc(ucfirst(str_replace('_',' ',$wo['status'] ?? ''))) ?></div>
    </div>
    <div class="meta-cell">
      <div class="meta-label">Assigned To</div>
      <div class="meta-value"><?= esc($wo['technician_name'] ?? 'Unassigned') ?></div>
    </div>
    <div class="meta-cell">
      <div class="meta-label">Created Date</div>
      <div class="meta-value"><?= isset($wo['created_at']) ? date('d M Y', strtotime($wo['created_at'])) : '—' ?></div>
    </div>
    <div class="meta-cell">
      <div class="meta-label">Due Date</div>
      <div class="meta-value"><?= isset($wo['due_date']) ? date('d M Y', strtotime($wo['due_date'])) : '—' ?></div>
    </div>
  </div>

  <!-- Description -->
  <div class="section">
    <div class="section-title">Work Description</div>
    <div class="description-box"><?= nl2br(esc($wo['description'] ?? 'No description provided.')) ?></div>
  </div>

  <!-- Materials Used (if any) -->
  <?php if (!empty($materials)): ?>
  <div class="section" style="padding-top:0">
    <div class="section-title">Materials Used</div>
    <table class="items-table">
      <thead>
        <tr>
          <th>Item</th>
          <th>Qty</th>
          <th>Unit</th>
          <th>Notes</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($materials as $m): ?>
        <tr>
          <td><?= esc($m['item_name'] ?? '') ?></td>
          <td><?= esc($m['quantity'] ?? '') ?></td>
          <td><?= esc($m['unit'] ?? '') ?></td>
          <td><?= esc($m['notes'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>

  <!-- Completion Notes -->
  <?php if (!empty($wo['completion_notes'])): ?>
  <div class="section" style="padding-top:0">
    <div class="section-title">Completion Notes</div>
    <div class="description-box"><?= nl2br(esc($wo['completion_notes'])) ?></div>
  </div>
  <?php endif; ?>

  <!-- Signature Block -->
  <div class="sig-row">
    <div class="sig-block">
      <div style="height:40px"></div>
      <div class="sig-label">Technician Signature</div>
    </div>
    <div class="sig-block">
      <div style="height:40px"></div>
      <div class="sig-label">Supervisor Approval</div>
    </div>
    <div class="sig-block">
      <div style="height:40px"></div>
      <div class="sig-label">Client / FM Signature</div>
    </div>
  </div>

  <!-- Footer -->
  <div class="doc-footer">
    <div class="footer-brand"><?= esc($companyName) ?></div>
    <div class="footer-info">Printed: <?= date('d M Y, H:i') ?> | <?= esc($wo['wo_number'] ?? '') ?></div>
  </div>

</div><!-- /.page -->

<script>window.onload=()=>window.print()</script>
</body>
</html>
