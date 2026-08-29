<?php
// Pull settings from session/db — Auth controller must pass $settings to this view
$companyName    = $settings['company_name']    ?? 'FM ERP';
$companyTagline = $settings['company_tagline'] ?? 'Facility Management ERP — Staff Login';
$primaryColor   = $settings['primary_color']   ?? '#76002b';
$secondaryColor = $settings['secondary_color'] ?? '#c7ba9a';
helper('fm');
$logoUrl = trim((string) ($companyLogoUrl ?? ''));
if ($logoUrl === '') {
    $logoUrl = fm_logo_url($settings['company_logo'] ?? '');
}

function _loginHexDarken(string $hex, int $pct=15): string {
    $hex=ltrim($hex,'#');
    if(strlen($hex)===3)$hex=$hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    [$r,$g,$b]=[hexdec(substr($hex,0,2)),hexdec(substr($hex,2,2)),hexdec(substr($hex,4,2))];
    $f=1-$pct/100;
    return sprintf('#%02x%02x%02x',max(0,(int)($r*$f)),max(0,(int)($g*$f)),max(0,(int)($b*$f)));
}
function _loginHexToRgb(string $hex): string {
    $hex=ltrim($hex,'#');
    if(strlen($hex)===3)$hex=$hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    return hexdec(substr($hex,0,2)).','.hexdec(substr($hex,2,2)).','.hexdec(substr($hex,4,2));
}
$primaryHover  = _loginHexDarken($primaryColor,15);
$primaryRgb    = _loginHexToRgb($primaryColor);
$secondaryRgb  = _loginHexToRgb($secondaryColor);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Login — <?= esc($companyName) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{
  --fm-primary:<?= $primaryColor ?>;
  --fm-primary-hover:<?= $primaryHover ?>;
  --fm-secondary:<?= $secondaryColor ?>;
}
*{box-sizing:border-box;margin:0;padding:0}
body{
  font-family:'DM Sans',system-ui,sans-serif;
  min-height:100vh;
  display:flex;
  align-items:center;
  justify-content:center;
  background:linear-gradient(135deg, <?= $primaryColor ?> 0%, <?= _loginHexDarken($primaryColor,30) ?> 50%, #1a1a2e 100%);
  padding:20px;
}
/* decorative blobs */
body::before{
  content:'';position:fixed;top:-120px;right:-120px;width:400px;height:400px;
  border-radius:50%;background:rgba(<?= $secondaryRgb ?>,.15);pointer-events:none;
}
body::after{
  content:'';position:fixed;bottom:-100px;left:-100px;width:300px;height:300px;
  border-radius:50%;background:rgba(255,255,255,.05);pointer-events:none;
}
.auth-wrap{
  width:100%;max-width:440px;position:relative;z-index:1;
}
.auth-card{
  background:#fff;border-radius:20px;padding:40px 36px;
  box-shadow:0 24px 60px rgba(0,0,0,.35);
}
.auth-logo-wrap{
  text-align:center;margin-bottom:24px;
}
.auth-logo-img{
  max-height:72px;max-width:220px;object-fit:contain;
}
.auth-icon-fallback{
  width:72px;height:72px;border-radius:18px;
  background:linear-gradient(135deg,<?= $primaryColor ?>,<?= $secondaryColor ?>);
  color:#fff;font-size:2rem;
  display:inline-flex;align-items:center;justify-content:center;
  box-shadow:0 8px 20px rgba(<?= $primaryRgb ?>,.4);
}
.auth-company{
  font-size:1.35rem;font-weight:700;color:<?= $primaryColor ?>;
  text-align:center;margin-bottom:4px;line-height:1.2;
}
.auth-tagline{
  text-align:center;color:#6b7a8d;font-size:.82rem;margin-bottom:28px;
}
.form-label{font-size:.82rem;font-weight:600;color:#374151;margin-bottom:5px}
.form-control{
  font-size:.85rem;border-radius:10px;border:1.5px solid #e5e7eb;
  padding:10px 14px;transition:.2s;
}
.form-control:focus{
  border-color:<?= $primaryColor ?>;
  box-shadow:0 0 0 3px rgba(<?= $primaryRgb ?>,.15);
  outline:none;
}
.input-group-text{
  background:#f9fafb;border:1.5px solid #e5e7eb;border-radius:10px 0 0 10px;
  border-right:none;color:#9ca3af;
}
.input-group .form-control{border-radius:0 10px 10px 0;border-left:none}
.input-group .form-control:focus{border-color:<?= $primaryColor ?>;box-shadow:0 0 0 3px rgba(<?= $primaryRgb ?>,.15)}
.btn-login{
  background:<?= $primaryColor ?>;color:#fff;border:none;border-radius:10px;
  padding:11px 20px;font-size:.88rem;font-weight:700;width:100%;
  transition:.2s;letter-spacing:.3px;
  display:flex;align-items:center;justify-content:center;gap:8px;
}
.btn-login:hover{background:<?= $primaryHover ?>;color:#fff;transform:translateY(-1px);box-shadow:0 6px 16px rgba(<?= $primaryRgb ?>,.35)}
.btn-login:active{transform:none}
.divider{display:flex;align-items:center;gap:12px;margin:20px 0}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:#e5e7eb}
.divider span{font-size:.75rem;color:#9ca3af}
.public-link{
  display:block;text-align:center;font-size:.82rem;color:#6b7a8d;
  text-decoration:none;padding:10px;border-radius:8px;border:1px solid #e5e7eb;
  transition:.2s;
}
.public-link:hover{border-color:<?= $primaryColor ?>;color:<?= $primaryColor ?>;background:#fdf8f6}
</style>
</head>
<body>
<div class="auth-wrap">
  <div class="auth-card">

    <!-- Logo / Icon -->
    <div class="auth-logo-wrap">
      <?php if ($logoUrl): ?>
        <img src="<?= esc($logoUrl) ?>" alt="<?= esc($companyName) ?>" class="auth-logo-img">
      <?php else: ?>
        <div class="auth-icon-fallback"><i class="bi bi-buildings-fill"></i></div>
      <?php endif; ?>
    </div>

    <!-- Company name + tagline -->
    <div class="auth-company"><?= esc($companyName) ?></div>
    <div class="auth-tagline"><?= esc($companyTagline) ?></div>

    <!-- Flash messages -->
    <?php if(session()->getFlashdata('success')): ?>
    <div class="alert alert-success border-0 rounded-3 small mb-3 py-2">
      <i class="bi bi-check-circle me-2"></i><?= session()->getFlashdata('success') ?>
    </div>
    <?php endif; ?>
    <?php if(session()->getFlashdata('error')): ?>
    <div class="alert alert-danger border-0 rounded-3 small mb-3 py-2">
      <i class="bi bi-exclamation-triangle me-2"></i><?= session()->getFlashdata('error') ?>
    </div>
    <?php endif; ?>

    <!-- Login form -->
    <?= form_open('login') ?>
    <div class="mb-3">
      <label class="form-label">Email Address</label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
        <input type="email" name="email" class="form-control" value="<?= old('email') ?>" required autocomplete="email">
      </div>
    </div>
    <div class="mb-4">
      <label class="form-label">Password</label>
      <div class="input-group">
        <span class="input-group-text"><i class="bi bi-lock"></i></span>
        <input type="password" name="password" id="pwdInput" class="form-control" required autocomplete="current-password">
        <button type="button" class="btn btn-outline-secondary border-start-0" style="border-radius:0 10px 10px 0;border:1.5px solid #e5e7eb;border-left:none" onclick="togglePwd()">
          <i class="bi bi-eye" id="pwdIcon"></i>
        </button>
      </div>
    </div>
    <button type="submit" class="btn-login mb-3">
      <i class="bi bi-box-arrow-in-right"></i>Sign In
    </button>
    <?= form_close() ?>

    <div class="divider"><span>or</span></div>

    <a href="<?= base_url('request') ?>" class="public-link">
      <i class="bi bi-send me-2"></i>Submit Maintenance Request →
    </a>

    <!-- Demo credentials removed -->

  </div><!-- /.auth-card -->
</div><!-- /.auth-wrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function togglePwd(){
  const i=document.getElementById('pwdInput');
  const ic=document.getElementById('pwdIcon');
  if(i.type==='password'){i.type='text';ic.className='bi bi-eye-slash';}
  else{i.type='password';ic.className='bi bi-eye';}
}
</script>
</body>
</html>
