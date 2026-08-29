<?php
/** Shared public-page theme (maintenance request, QR scan, entity pages). */
helper('fm');
$companyName    = $settings['company_name'] ?? 'FM ERP';
$primaryColor   = $settings['primary_color'] ?? '#76002b';
$secondaryColor = $settings['secondary_color'] ?? '#c7ba9a';
$logoUrl        = fm_logo_url($settings['company_logo'] ?? '');

if (! function_exists('_scan_hex_darken')) {
    function _scan_hex_darken(string $hex, int $pct = 15): string {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        [$r, $g, $b] = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
        $f = 1 - $pct / 100;

        return sprintf('#%02x%02x%02x', max(0, (int) ($r * $f)), max(0, (int) ($g * $f)), max(0, (int) ($b * $f)));
    }
}
if (! function_exists('_scan_hex_rgb')) {
    function _scan_hex_rgb(string $hex): string {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        return hexdec(substr($hex, 0, 2)).','.hexdec(substr($hex, 2, 2)).','.hexdec(substr($hex, 4, 2));
    }
}
$primaryHover = _scan_hex_darken($primaryColor, 15);
$primaryDark  = _scan_hex_darken($primaryColor, 30);
$primaryRgb   = _scan_hex_rgb($primaryColor);
$secondaryRgb = _scan_hex_rgb($secondaryColor);
?>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--fm-primary:<?= $primaryColor ?>;--fm-primary-hover:<?= $primaryHover ?>;--fm-secondary:<?= $secondaryColor ?>}
*{box-sizing:border-box}
body.scan-public{
  font-family:'DM Sans',system-ui,sans-serif;min-height:100vh;margin:0;
  background:linear-gradient(135deg, <?= $primaryColor ?> 0%, <?= $primaryDark ?> 55%, #1a1a2e 100%);
  padding:1.25rem 1rem 2rem;position:relative;
}
body.scan-public::before{content:'';position:fixed;top:-120px;right:-120px;width:400px;height:400px;border-radius:50%;background:rgba(<?= $secondaryRgb ?>,.15);pointer-events:none}
.scan-wrap{max-width:680px;margin:0 auto;position:relative;z-index:1}
.scan-brand{text-align:center;color:#fff;margin-bottom:1.25rem}
.scan-brand img{max-height:64px;max-width:200px;object-fit:contain;margin-bottom:.75rem}
.scan-brand .auth-logo{width:64px;height:64px;border-radius:16px;background:linear-gradient(135deg,<?= $primaryColor ?>,<?= $secondaryColor ?>);color:#fff;font-size:1.75rem;display:inline-flex;align-items:center;justify-content:center;margin-bottom:.75rem;box-shadow:0 8px 20px rgba(<?= $primaryRgb ?>,.35)}
.public-card{background:#fff;border-radius:18px;padding:1.35rem 1.25rem;box-shadow:0 20px 50px rgba(0,0,0,.28);margin-bottom:1rem}
.btn-fm-primary{background:<?= $primaryColor ?>;color:#fff;border:none;border-radius:10px;padding:.65rem 1rem;font-size:.85rem;font-weight:700}
.btn-fm-primary:hover{background:<?= $primaryHover ?>;color:#fff}
.btn-fm-outline{background:transparent;color:<?= $primaryColor ?>;border:1.5px solid <?= $primaryColor ?>;border-radius:10px;padding:.55rem .9rem;font-size:.82rem;font-weight:600}
.btn-fm-outline:hover{background:<?= $primaryColor ?>;color:#fff}
.form-label{font-size:.78rem;font-weight:600;color:#374151}
.form-control,.form-select{font-size:.84rem;border-radius:10px;border:1.5px solid #e5e7eb}
.form-control:focus,.form-select:focus{border-color:<?= $primaryColor ?>;box-shadow:0 0 0 3px rgba(<?= $primaryRgb ?>,.15)}
.table-registry{font-size:.82rem}
.table-registry thead th{background:color-mix(in srgb, <?= $primaryColor ?> 8%, white);color:#6b5a62;font-size:.72rem;text-transform:uppercase}
.action-btn{border-radius:10px;padding:.7rem;font-weight:600}
</style>
