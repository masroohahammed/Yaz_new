<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= esc($title ?? 'Inspection Report') ?> — <?= esc($settings['company_name'] ?? 'FM ERP') ?></title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
<?php
$primaryColor   = $settings['primary_color']   ?? '#76002b';
$secondaryColor = $settings['secondary_color'] ?? '#c7ba9a';
$companyName    = $settings['company_name']    ?? 'FM ERP';
$companyTagline = $settings['company_tagline'] ?? 'Facility Management ERP';
$logoSrc        = $companyLogoB64 ?? $companyLogoUrl ?? null;

function _inspHexRgb($hex) {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    return hexdec(substr($hex, 0, 2)) . ',' . hexdec(substr($hex, 2, 2)) . ',' . hexdec(substr($hex, 4, 2));
}
$primaryRgb = _inspHexRgb($primaryColor);

$i = $inspection;
$scopeType = (string) ($i['scope_type'] ?? 'unit');
$scopeLabels = ['property' => 'Property', 'unit' => 'Unit', 'asset' => 'Asset'];
$data = $items ?? [];
$areas = $data['areas'] ?? [];
$ratings = $data['ratings'] ?? [];
$notes = $data['notes'] ?? [];
$photos = $data['photos'] ?? [];
$priorities = $data['priorities'] ?? [];
$statuses = $data['statuses'] ?? [];
$typeLabel = ucfirst(str_replace('_', ' ', (string) ($i['type'] ?? 'routine')));
$dateVal = $i['inspection_date'] ?? $i['created_at'] ?? '';
$dateFmt = $dateVal ? date('d M Y', strtotime((string) $dateVal)) : date('d M Y');
$subjectLabel = match ($scopeType) {
    'property' => (string) ($i['property_name'] ?? 'Property'),
    'asset'    => (string) ($i['asset_name'] ?? 'Asset'),
    default    => 'Unit ' . ($i['unit_number'] ?? '—'),
};
?>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'DM Sans',Arial,sans-serif;color:#1a2332;font-size:13px;background:#fff}
.page{max-width:820px;margin:0 auto;min-height:100vh;display:flex;flex-direction:column}
.doc-header{display:flex;justify-content:space-between;align-items:flex-start;padding:20px 28px 14px;border-bottom:3px solid <?= $primaryColor ?>}
.doc-logo{max-height:52px;max-width:160px;object-fit:contain;display:block;margin-bottom:3px}
.doc-brand-text{font-size:1.25rem;font-weight:700;color:<?= $primaryColor ?>}
.doc-tagline{font-size:.6rem;color:#9ca3af;text-transform:uppercase;letter-spacing:.8px;margin-top:2px}
.doc-title-area{text-align:right}
.doc-type-label{font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:2px;color:<?= $secondaryColor ?>;margin-bottom:3px}
.doc-title{font-size:1.1rem;font-weight:700;color:<?= $primaryColor ?>}
.doc-ref{font-size:.72rem;color:#6b7a8d;margin-top:3px}
.info-bar{display:grid;grid-template-columns:repeat(4,1fr);padding:12px 28px;background:#fafafa;border-bottom:1px solid #e8edf3}
.info-item{padding:8px 12px;border-right:1px solid #f0f4f8}
.info-item:last-child{border-right:none}
.info-label{font-size:.6rem;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#9ca3af;margin-bottom:2px}
.info-value{font-size:.8rem;font-weight:600}
.type-banner{padding:8px 28px;font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;background:rgba(<?= $primaryRgb ?>,.08);color:<?= $primaryColor ?>;border-bottom:1px solid rgba(<?= $primaryRgb ?>,.12)}
.cl-section{padding:16px 28px}
.cl-section-title{font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:<?= $primaryColor ?>;margin-bottom:10px;padding-bottom:5px;border-bottom:2px solid rgba(<?= $primaryRgb ?>,.15)}
.cl-item{display:flex;align-items:flex-start;gap:14px;padding:12px 0;border-bottom:1px solid #f0f4f8}
.cl-item:last-child{border-bottom:none}
.cl-num{font-size:.75rem;color:#9ca3af;min-width:22px;padding-top:2px}
.cl-content{flex:1}
.cl-label{font-size:.85rem;font-weight:600;margin-bottom:4px}
.cl-notes{font-size:.75rem;color:#6b7a8d;font-style:italic;margin-top:4px}
.cl-status{font-size:.65rem;font-weight:700;padding:3px 10px;border-radius:999px;display:inline-block;text-transform:uppercase}
.cl-status-excellent,.cl-status-good{background:#dcfce7;color:#166534}
.cl-status-fair{background:#fef9c3;color:#a16207}
.cl-status-poor{background:#ffedd5;color:#c2410c}
.cl-status-damaged{background:#fee2e2;color:#991b1b}
.cl-photo{margin-top:8px}
.cl-photo img{max-height:110px;max-width:180px;border-radius:8px;border:1px solid #e5e7eb;object-fit:cover}
.cl-priority-critical{background:#fee2e2;color:#991b1b;font-weight:700}
.cl-meta{font-size:.68rem;color:#6b7280;margin-top:4px}
.summary-box{margin:0 28px 16px;padding:12px 16px;background:rgba(<?= $primaryRgb ?>,.05);border:1px solid rgba(<?= $primaryRgb ?>,.12);border-radius:8px;font-size:.82rem}
.summary-box strong{display:block;font-size:.65rem;text-transform:uppercase;letter-spacing:.8px;color:#9ca3af;margin-bottom:4px}
.doc-footer{margin-top:auto;padding:10px 28px;border-top:2px solid <?= $primaryColor ?>;background:rgba(<?= $primaryRgb ?>,.06);display:flex;justify-content:space-between}
.footer-brand{font-weight:700;color:<?= $primaryColor ?>;font-size:.75rem}
.footer-info{font-size:.65rem;color:#9ca3af}
.no-print{padding:12px 28px}
@media print{body{-webkit-print-color-adjust:exact;print-color-adjust:exact}.no-print{display:none!important}}
</style>
</head>
<body>
<div class="page">
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
      <div class="doc-type-label"><?= esc($scopeLabels[$scopeType] ?? 'Unit') ?> Inspection</div>
      <div class="doc-title"><?= esc($typeLabel) ?> Report</div>
      <div class="doc-ref">Report #<?= (int) ($i['id'] ?? 0) ?> · Generated <?= date('d M Y H:i') ?></div>
    </div>
  </div>

  <div class="info-bar">
    <div class="info-item"><div class="info-label">Property</div><div class="info-value"><?= esc($i['property_name'] ?? '—') ?></div></div>
    <div class="info-item"><div class="info-label"><?= $scopeType === 'asset' ? 'Asset' : ($scopeType === 'property' ? 'Scope' : 'Unit') ?></div><div class="info-value"><?= esc($subjectLabel) ?><?php if ($scopeType === 'property' && ! empty($i['floor_label'])): ?> · <?= esc($i['floor_label']) ?><?php endif; ?></div></div>
    <div class="info-item"><div class="info-label">Inspection Date</div><div class="info-value"><?= esc($dateFmt) ?></div></div>
    <div class="info-item"><div class="info-label">Inspector</div><div class="info-value"><?= esc($i['inspector_name'] ?: '—') ?></div></div>
  </div>

  <div class="type-banner">
    Status: <?= esc(ucfirst((string) ($i['status'] ?? 'draft'))) ?>
    <?php if (! empty($i['overall_condition'])): ?>
    · Overall condition: <?= esc(ucfirst((string) $i['overall_condition'])) ?>
    <?php endif; ?>
  </div>

  <div class="cl-section">
    <div class="cl-section-title">Area Breakdown</div>
    <?php if (empty($areas)): ?>
    <p style="color:#9ca3af;font-size:.82rem">No checklist data recorded.</p>
    <?php else: ?>
    <?php foreach ($areas as $idx => $area): ?>
    <?php
      $rating = $ratings[$idx] ?? '';
      $note = $notes[$idx] ?? '';
      $photo = $photos[$idx] ?? '';
      $priority = $priorities[$idx] ?? '';
      $itemStatus = $statuses[$idx] ?? '';
      $statusClass = $rating ? 'cl-status cl-status-' . preg_replace('/[^a-z]/', '', $rating) : '';
    ?>
    <div class="cl-item">
      <div class="cl-num"><?= $idx + 1 ?>.</div>
      <div class="cl-content">
        <div class="cl-label"><?= esc($area) ?><?php if ($priority === 'critical'): ?> ⚠<?php endif; ?></div>
        <?php if ($rating): ?>
        <span class="<?= esc($statusClass) ?>"><?= esc(ucfirst($rating)) ?></span>
        <?php else: ?>
        <span style="font-size:.75rem;color:#9ca3af">Not rated</span>
        <?php endif; ?>
        <?php if ($priority || $itemStatus): ?>
        <div class="cl-meta">
          <?php if ($priority): ?><span class="<?= $priority === 'critical' ? 'cl-priority-critical' : '' ?>">Priority: <?= esc(ucfirst($priority)) ?></span><?php endif; ?>
          <?php if ($priority && $itemStatus): ?> · <?php endif; ?>
          <?php if ($itemStatus): ?>Status: <?= esc(ucfirst(str_replace('_', ' ', $itemStatus))) ?><?php endif; ?>
        </div>
        <?php endif; ?>
        <?php if ($note): ?>
        <div class="cl-notes"><?= esc($note) ?></div>
        <?php endif; ?>
        <?php if ($photo): ?>
        <div class="cl-photo">
          <img src="<?= esc(base_url($photo)) ?>" alt="Photo for <?= esc($area) ?>">
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <?php if (! empty($i['notes'])): ?>
  <div class="summary-box">
    <strong>Summary Notes</strong>
    <?= nl2br(esc($i['notes'])) ?>
  </div>
  <?php endif; ?>

  <div class="doc-footer">
    <div class="footer-brand"><?= esc($companyName) ?></div>
    <div class="footer-info">Inspection report · <?= esc($dateFmt) ?></div>
  </div>
</div>

<div class="no-print">
  <button type="button" onclick="window.print()" style="padding:8px 16px;background:<?= $primaryColor ?>;color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600">Print / Save PDF</button>
</div>
</body>
</html>
