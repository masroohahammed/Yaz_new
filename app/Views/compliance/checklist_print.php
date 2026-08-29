<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= esc($checklistTitle ?? 'Checklist') ?> — <?= esc($settings['company_name'] ?? 'FM ERP') ?></title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
<?php
$primaryColor   = $settings['primary_color']   ?? '#76002b';
$secondaryColor = $settings['secondary_color'] ?? '#c7ba9a';
$companyName    = $settings['company_name']    ?? 'FM ERP';
$companyTagline = $settings['company_tagline'] ?? 'Facility Management ERP';
$logoSrc        = $companyLogoB64 ?? $companyLogoUrl ?? null;

function _clHexRgb($hex){$hex=ltrim($hex,'#');if(strlen($hex)===3)$hex=$hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];return hexdec(substr($hex,0,2)).','.hexdec(substr($hex,2,2)).','.hexdec(substr($hex,4,2));}
$primaryRgb=$_clHexRgb=$primaryRgb=_clHexRgb($primaryColor);
?>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',Arial,sans-serif;color:#1a2332;font-size:13px;background:#fff}
.page{max-width:800px;margin:0 auto;min-height:100vh;display:flex;flex-direction:column}
/* Header */
.doc-header{display:flex;justify-content:space-between;align-items:flex-start;padding:20px 28px 14px;border-bottom:3px solid <?= $primaryColor ?>}
.doc-logo{max-height:52px;max-width:160px;object-fit:contain;display:block;margin-bottom:3px}
.doc-brand-text{font-size:1.25rem;font-weight:700;color:<?= $primaryColor ?>}
.doc-tagline{font-size:.6rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.8px;margin-top:2px}
.doc-title-area{text-align:right}
.doc-type-label{font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:<?= $secondaryColor ?>;margin-bottom:3px}
.doc-title{font-size:1.1rem;font-weight:700;color:<?= $primaryColor ?>}
.doc-ref{font-size:.72rem;color:#6b7a8d;margin-top:3px}
/* Info bar */
.info-bar{display:grid;grid-template-columns:repeat(4,1fr);padding:12px 28px;background:#fafafa;border-bottom:1px solid #e8edf3;gap:0}
.info-item{padding:8px 12px;border-right:1px solid #f0f4f8}
.info-item:last-child{border-right:none}
.info-label{font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#9ca3af;margin-bottom:2px}
.info-value{font-size:.8rem;font-weight:600}
/* Type banner */
.type-banner{padding:8px 28px;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;background:rgba(<?= $primaryRgb ?>,.08);color:<?= $primaryColor ?>;border-bottom:1px solid rgba(<?= $primaryRgb ?>,.12)}
/* Section */
.cl-section{padding:16px 28px}
.cl-section-title{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:<?= $primaryColor ?>;margin-bottom:10px;padding-bottom:5px;border-bottom:2px solid rgba(<?= $primaryRgb ?>,.15)}
/* Checklist items */
.cl-item{display:flex;align-items:flex-start;gap:14px;padding:10px 0;border-bottom:1px solid #f5f5f5}
.cl-item:last-child{border-bottom:none}
.cl-box{width:20px;height:20px;border:2px solid <?= $primaryColor ?>;border-radius:4px;flex-shrink:0;margin-top:1px;display:flex;align-items:center;justify-content:center}
.cl-box.checked{background:<?= $primaryColor ?>;color:#fff}
.cl-check-icon{font-size:.7rem;font-weight:900}
.cl-content{flex:1}
.cl-label{font-size:.83rem;font-weight:600;margin-bottom:3px}
.cl-notes-line{font-size:.75rem;color:#6b7a8d;font-style:italic}
.cl-status{font-size:.65rem;font-weight:700;padding:2px 8px;border-radius:12px;margin-left:auto;flex-shrink:0;text-transform:uppercase}
.cl-status-ok{background:#dcfce7;color:#166534}
.cl-status-issue{background:#fee2e2;color:#991b1b}
.cl-status-na{background:#f1f5f9;color:#475569}
/* Notes line */
.notes-line{border-bottom:1px dashed #ccc;margin:6px 0;height:20px}
/* Photo box */
.photo-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:10px}
.photo-placeholder{border:1.5px dashed #ccc;border-radius:8px;height:100px;display:flex;align-items:center;justify-content:center;color:#ccc;font-size:.72rem;text-align:center}
/* Signatures */
.sig-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;padding:20px 28px}
.sig-block{padding-top:50px;border-top:1.5px solid #bbb;text-align:center}
.sig-name{font-size:.75rem;font-weight:600;margin-top:4px}
.sig-label{font-size:.62rem;text-transform:uppercase;letter-spacing:.5px;color:#9ca3af}
.sig-date{font-size:.65rem;color:#9ca3af;margin-top:2px}
/* Footer */
.doc-footer{margin-top:auto;padding:10px 28px;border-top:2px solid <?= $primaryColor ?>;background:rgba(<?= $primaryRgb ?>,.06);display:flex;justify-content:space-between}
.footer-brand{font-weight:700;color:<?= $primaryColor ?>;font-size:.75rem}
.footer-info{font-size:.65rem;color:#9ca3af}
@media print{body{-webkit-print-color-adjust:exact;print-color-adjust:exact}.no-print{display:none!important}}
</style>
</head>
<body>
<div class="page">

  <!-- Header -->
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
      <div class="doc-type-label">INSPECTION CHECKLIST</div>
      <div class="doc-title"><?= esc($checklistTitle ?? 'Checklist') ?></div>
      <?php if(!empty($refNumber)): ?>
      <div class="doc-ref">Ref: <?= esc($refNumber) ?></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Type banner -->
  <div class="type-banner">
    <?php
    $typeLabel = [
      'move_in'    => '📋 Move-In Inspection',
      'move_out'   => '📋 Move-Out Inspection',
      'routine'    => '🔍 Routine Inspection',
      'safety'     => '⚠️ Safety Inspection',
      'handover'   => '🔑 Handover Checklist',
    ][$checklistType ?? ''] ?? ucfirst(str_replace('_',' ',$checklistType ?? 'General Checklist'));
    echo esc($typeLabel);
    ?>
  </div>

  <!-- Info bar -->
  <div class="info-bar">
    <div class="info-item">
      <div class="info-label">Unit / Location</div>
      <div class="info-value"><?= esc($unitRef ?? '—') ?></div>
    </div>
    <div class="info-item">
      <div class="info-label">Facility</div>
      <div class="info-value"><?= esc($facilityName ?? '—') ?></div>
    </div>
    <div class="info-item">
      <div class="info-label">Inspection Date</div>
      <div class="info-value"><?= esc($inspectionDate ?? date('d M Y')) ?></div>
    </div>
    <div class="info-item">
      <div class="info-label">Inspector</div>
      <div class="info-value"><?= esc($inspectorName ?? '—') ?></div>
    </div>
  </div>

  <!-- Checklist Items -->
  <?php if (!empty($sections)): ?>
    <?php foreach($sections as $section): ?>
    <div class="cl-section">
      <div class="cl-section-title"><?= esc($section['title']) ?></div>
      <?php foreach($section['items'] as $item): ?>
      <div class="cl-item">
        <div class="cl-box <?= !empty($item['checked']) ? 'checked' : '' ?>">
          <?php if(!empty($item['checked'])): ?><span class="cl-check-icon">✓</span><?php endif; ?>
        </div>
        <div class="cl-content">
          <div class="cl-label"><?= esc($item['label']) ?></div>
          <?php if(!empty($item['notes'])): ?>
          <div class="cl-notes-line"><?= esc($item['notes']) ?></div>
          <?php else: ?>
          <div class="notes-line"></div><!-- blank notes line for filling -->
          <?php endif; ?>
        </div>
        <?php if(!empty($item['status'])): ?>
        <span class="cl-status cl-status-<?= esc($item['status']) ?>">
          <?= esc(ucfirst($item['status'])) ?>
        </span>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>

  <?php else: ?>
  <!-- Default blank checklist items when no data provided -->
  <div class="cl-section">
    <div class="cl-section-title">General Inspection Items</div>
    <?php
    $defaultItems = [
      'Cleanliness & General Condition', 'Walls, Ceilings & Floors',
      'Doors & Windows', 'Electrical Fixtures', 'Plumbing & Water Fittings',
      'Air Conditioning / HVAC', 'Key Handover', 'Utilities (Electricity / Water)',
      'Fire Safety Equipment', 'Assets & Inventory Condition',
    ];
    foreach($defaultItems as $it):
    ?>
    <div class="cl-item">
      <div class="cl-box"></div>
      <div class="cl-content">
        <div class="cl-label"><?= esc($it) ?></div>
        <div class="notes-line"></div>
      </div>
      <span class="cl-status cl-status-na" style="opacity:.4">N/A</span>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <!-- Photo attachments placeholder -->
  <div class="cl-section" style="padding-top:0">
    <div class="cl-section-title">Photo Documentation</div>
    <div class="photo-grid">
      <div class="photo-placeholder">📷<br>Photo 1</div>
      <div class="photo-placeholder">📷<br>Photo 2</div>
      <div class="photo-placeholder">📷<br>Photo 3</div>
    </div>
  </div>

  <!-- Remarks -->
  <div class="cl-section" style="padding-top:0">
    <div class="cl-section-title">Remarks / Additional Notes</div>
    <div class="notes-line" style="margin:8px 0"></div>
    <div class="notes-line" style="margin:8px 0"></div>
    <div class="notes-line" style="margin:8px 0"></div>
  </div>

  <!-- Signatures -->
  <div class="sig-grid">
    <div class="sig-block">
      <div class="sig-name">Inspector</div>
      <div class="sig-label">Signature &amp; Name</div>
      <div class="sig-date">Date: ____/____/______</div>
    </div>
    <div class="sig-block">
      <div class="sig-name">Tenant / Owner</div>
      <div class="sig-label">Signature &amp; Name</div>
      <div class="sig-date">Date: ____/____/______</div>
    </div>
    <div class="sig-block">
      <div class="sig-name">FM Manager</div>
      <div class="sig-label">Approval Signature</div>
      <div class="sig-date">Date: ____/____/______</div>
    </div>
  </div>

  <!-- Footer -->
  <div class="doc-footer">
    <div class="footer-brand"><?= esc($companyName) ?> — Facility Management</div>
    <div class="footer-info">Printed: <?= date('d M Y, H:i') ?><?php if(!empty($refNumber)): ?> | <?= esc($refNumber) ?><?php endif; ?></div>
  </div>

</div>
<script>window.onload=()=>window.print()</script>
</body>
</html>
