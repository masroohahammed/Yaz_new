<?php
$companyName    = $settings['company_name']    ?? 'FM ERP';
$primaryColor   = $settings['primary_color']   ?? '#76002b';
$secondaryColor = $settings['secondary_color'] ?? '#c7ba9a';
helper('fm');

if (! function_exists('_trackHexDarken')) {
    function _trackHexDarken(string $hex, int $pct = 15): string {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        [$r,$g,$b] = [hexdec(substr($hex,0,2)),hexdec(substr($hex,2,2)),hexdec(substr($hex,4,2))];
        $f = 1 - $pct / 100;
        return sprintf('#%02x%02x%02x', max(0,(int)($r*$f)), max(0,(int)($g*$f)), max(0,(int)($b*$f)));
    }
    function _trackHexToRgb(string $hex): string {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        return hexdec(substr($hex,0,2)).','.hexdec(substr($hex,2,2)).','.hexdec(substr($hex,4,2));
    }
}
$primaryDark  = _trackHexDarken($primaryColor, 30);
$primaryRgb   = _trackHexToRgb($primaryColor);
$secondaryRgb = _trackHexToRgb($secondaryColor);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Track Request — <?= esc($companyName) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--fm-primary:<?= $primaryColor ?>;--fm-secondary:<?= $secondaryColor ?>}
*{box-sizing:border-box}
body{
  font-family:'DM Sans',system-ui,sans-serif;
  min-height:100vh;
  background:linear-gradient(135deg, <?= $primaryColor ?> 0%, <?= $primaryDark ?> 50%, #1a1a2e 100%);
  padding:2rem 1rem;position:relative;
}
body::before{
  content:'';position:fixed;top:-120px;right:-120px;width:400px;height:400px;
  border-radius:50%;background:rgba(<?= $secondaryRgb ?>,.15);pointer-events:none;
}
body::after{
  content:'';position:fixed;bottom:-100px;left:-100px;width:300px;height:300px;
  border-radius:50%;background:rgba(255,255,255,.05);pointer-events:none;
}
.public-card{
  background:#fff;border-radius:20px;padding:32px 28px;
  box-shadow:0 24px 60px rgba(0,0,0,.35);
}
.fm-badge{display:inline-block;padding:4px 12px;border-radius:20px;font-size:.78rem;font-weight:600}
.badge-status-pending{background:#fff3cd;color:#856404}
.badge-status-reviewed{background:#cfe2ff;color:#084298}
.badge-status-converted{background:#d1e7dd;color:#0a3622}
.badge-status-rejected{background:#f8d7da;color:#842029}
.badge-priority-low{background:#d1fae5;color:#065f46}
.badge-priority-medium{background:#fef3c7;color:#92400e}
.badge-priority-high{background:#fee2e2;color:#991b1b}
.badge-priority-critical{background:#7f1d1d;color:#fff}
</style>
</head>
<body>
<div style="max-width:680px;margin:0 auto;padding:0 1rem;position:relative;z-index:1">
  <?php if (! empty($logoUrl)): ?>
  <div class="text-center mb-3"><img src="<?= esc($logoUrl) ?>" alt="<?= esc($companyName) ?>" style="max-height:64px;max-width:200px;object-fit:contain"></div>
  <?php endif; ?>
<div class="public-card">
  <a href="<?= base_url('request') ?>" class="small text-primary"><i class="bi bi-arrow-left me-1"></i>New Request</a>
  <h4 class="fw-bold mt-3 mb-4" style="color:#0a3d6b"><i class="bi bi-search me-2"></i>Track Request: <?= esc($ticket) ?></h4>
  <?php if($req): ?>
  <div class="row g-3">
    <div class="col-md-6"><div class="small text-muted">Ticket Number</div><div class="fw-bold fs-5"><?= esc($req['ticket_number']) ?></div></div>
    <div class="col-md-6"><div class="small text-muted">Status</div><span class="fm-badge badge-status-<?= esc($req['status']) ?> fs-6"><?= ucfirst($req['status']) ?></span></div>
    <div class="col-md-6"><div class="small text-muted">Priority</div><span class="fm-badge badge-priority-<?= esc($req['priority']) ?>"><?= ucfirst($req['priority']) ?></span></div>
    <div class="col-md-6"><div class="small text-muted">Submitted</div><div><?= date('d M Y H:i',strtotime($req['created_at'])) ?></div></div>
    <?php if (! empty($req['facility_name'])): ?>
    <div class="col-md-6"><div class="small text-muted">Facility</div><div><?= esc($req['facility_name']) ?></div></div>
    <?php endif; ?>
    <?php if (! empty($req['unit_number'])): ?>
    <div class="col-md-6"><div class="small text-muted">Unit</div><div><?= esc($req['unit_number']) ?></div></div>
    <?php endif; ?>
    <div class="col-12"><div class="small text-muted">Description</div><div class="p-3 rounded" style="background:#f7f9fc"><?= nl2br(esc($req['description'])) ?></div></div>
    <?php if($req['status']==='converted'): ?>
    <div class="col-12"><div class="alert alert-success border-0 rounded-3"><i class="bi bi-check-circle-fill me-2"></i>Your request has been reviewed and converted to a work order. Our team is working on it.</div></div>
    <?php elseif($req['status']==='rejected'): ?>
    <div class="col-12"><div class="alert alert-warning border-0 rounded-3"><i class="bi bi-info-circle me-2"></i>Your request was reviewed but could not be processed at this time. Please contact us directly.</div></div>
    <?php elseif($req['status']==='reviewed'): ?>
    <div class="col-12"><div class="alert alert-info border-0 rounded-3"><i class="bi bi-eye me-2"></i>Your request has been reviewed and is pending assignment.</div></div>
    <?php else: ?>
    <div class="col-12"><div class="alert alert-secondary border-0 rounded-3"><i class="bi bi-clock me-2"></i>Your request is pending review. We will process it shortly.</div></div>
    <?php endif; ?>
  </div>
  <?php else: ?>
  <div class="alert alert-warning border-0 rounded-3"><i class="bi bi-search me-2"></i>No request found with ticket number <strong><?= esc($ticket) ?></strong>. Please check your ticket number and try again.</div>
  <?php endif; ?>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body></html>
