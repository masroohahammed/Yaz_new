<?php
$companyName    = $settings['company_name']    ?? 'FM ERP';
$primaryColor   = $settings['primary_color']   ?? '#76002b';
$secondaryColor = $settings['secondary_color'] ?? '#c7ba9a';
helper('fm');
$logoUrl = fm_logo_url($settings['company_logo'] ?? '');

function _pubHexDarken(string $hex, int $pct = 15): string {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    [$r,$g,$b] = [hexdec(substr($hex,0,2)),hexdec(substr($hex,2,2)),hexdec(substr($hex,4,2))];
    $f = 1 - $pct / 100;
    return sprintf('#%02x%02x%02x', max(0,(int)($r*$f)), max(0,(int)($g*$f)), max(0,(int)($b*$f)));
}
function _pubHexToRgb(string $hex): string {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
    return hexdec(substr($hex,0,2)).','.hexdec(substr($hex,2,2)).','.hexdec(substr($hex,4,2));
}
$primaryHover  = _pubHexDarken($primaryColor, 15);
$primaryDark   = _pubHexDarken($primaryColor, 30);
$primaryRgb    = _pubHexToRgb($primaryColor);
$secondaryRgb  = _pubHexToRgb($secondaryColor);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Maintenance Request — <?= esc($companyName) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{
  --fm-primary:<?= $primaryColor ?>;
  --fm-primary-hover:<?= $primaryHover ?>;
  --fm-secondary:<?= $secondaryColor ?>;
}
*{box-sizing:border-box}
body{
  font-family:'DM Sans',system-ui,sans-serif;
  min-height:100vh;
  background:linear-gradient(135deg, <?= $primaryColor ?> 0%, <?= $primaryDark ?> 50%, #1a1a2e 100%);
  padding:2rem 1rem;
  position:relative;
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
  box-shadow:0 24px 60px rgba(0,0,0,.35);margin-bottom:1rem;
}
.auth-logo{
  width:72px;height:72px;border-radius:18px;
  background:linear-gradient(135deg,<?= $primaryColor ?>,<?= $secondaryColor ?>);
  color:#fff;font-size:2rem;
  display:inline-flex;align-items:center;justify-content:center;
  box-shadow:0 8px 20px rgba(<?= $primaryRgb ?>,.4);
}
.form-label{font-size:.82rem;font-weight:600;color:#374151;margin-bottom:5px}
.form-control,.form-select{
  font-size:.85rem;border-radius:10px;border:1.5px solid #e5e7eb;padding:10px 14px;transition:.2s;
}
.form-control:focus,.form-select:focus{
  border-color:<?= $primaryColor ?>;
  box-shadow:0 0 0 3px rgba(<?= $primaryRgb ?>,.15);outline:none;
}
.btn-fm-primary{
  background:<?= $primaryColor ?>;color:#fff;border:none;border-radius:10px;
  padding:11px 20px;font-size:.88rem;font-weight:700;transition:.2s;
}
.btn-fm-primary:hover{background:<?= $primaryHover ?>;color:#fff;transform:translateY(-1px);box-shadow:0 6px 16px rgba(<?= $primaryRgb ?>,.35)}
.btn-fm-outline{
  background:transparent;color:<?= $primaryColor ?>;border:1.5px solid <?= $primaryColor ?>;border-radius:8px;
  padding:6px 16px;font-size:.82rem;font-weight:600;transition:.2s;
}
.btn-fm-outline:hover{background:<?= $primaryColor ?>;color:#fff}
</style>
</head>
<body>
<div style="max-width:680px;margin:0 auto;padding:0 1rem;position:relative;z-index:1">
  <div class="text-center text-white mb-4">
    <?php if (! empty($logoUrl)): ?>
    <img src="<?= esc($logoUrl) ?>" alt="<?= esc($companyName) ?>" class="mb-3" style="max-height:72px;max-width:220px;object-fit:contain">
    <?php else: ?>
    <div class="auth-logo mx-auto mb-3"><i class="bi bi-buildings-fill"></i></div>
    <?php endif; ?>
    <h2 class="fw-bold"><?= esc($companyName) ?></h2>
    <p class="opacity-75">Submit a Maintenance Request</p>
  </div>

  <?php if(session()->getFlashdata('success')): ?>
  <div class="alert alert-success border-0 rounded-3 mb-3"><i class="bi bi-check-circle-fill me-2"></i><?= session()->getFlashdata('success') ?></div>
  <?php endif; ?>
  <?php if(session()->getFlashdata('errors')): ?>
  <div class="alert alert-danger border-0 rounded-3 mb-3"><?php foreach((array)session()->getFlashdata('errors') as $e): ?><div><?= esc($e) ?></div><?php endforeach; ?></div>
  <?php endif; ?>

  <div class="public-card">
    <h4 class="fw-bold mb-1" style="color:#0a3d6b">Submit Maintenance Request</h4>
    <p class="text-muted small mb-4">Fill in the form below. You will receive a ticket number to track your request.</p>
    <?= form_open_multipart(base_url('request')) ?>
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Your Name *</label><input type="text" name="requester_name" class="form-control" value="<?= old('requester_name') ?>" required></div>
      <div class="col-md-6"><label class="form-label">Phone Number</label><input type="tel" name="requester_phone" class="form-control" value="<?= old('requester_phone') ?>" placeholder="+974 XXXX XXXX"></div>
      <div class="col-12"><label class="form-label">Email Address</label><input type="email" name="requester_email" class="form-control" value="<?= old('requester_email') ?>"></div>
      <div class="col-md-6"><label class="form-label">Facility / Location</label><select name="facility_id" id="facility_id" class="form-select"><option value="">— Select if known —</option><?php foreach($facilities as $f): ?><option value="<?= $f['id'] ?>" <?= (string)old('facility_id') === (string)$f['id'] ? 'selected' : '' ?>><?= esc($f['name']) ?></option><?php endforeach; ?></select></div>
      <div class="col-md-6"><label class="form-label">Unit (optional)</label><select name="unit_id" id="unit_id" class="form-select"><option value="">— Select facility first —</option></select></div>
      <div class="col-md-6"><label class="form-label">Issue Category</label><select name="category" class="form-select"><option value="">— Select —</option><?php foreach(['HVAC','Electrical','Plumbing','Fire Safety','Security','Civil / Structural','IT / Telecom','Cleaning','Other'] as $c): ?><option value="<?= $c ?>"><?= $c ?></option><?php endforeach; ?></select></div>
      <div class="col-md-6"><label class="form-label">Priority</label><select name="priority" class="form-select"><option value="low">Low — Minor inconvenience</option><option value="medium" selected>Medium — Needs attention</option><option value="high">High — Affecting operations</option><option value="critical">Critical — Emergency</option></select></div>
      <div class="col-12"><label class="form-label">Description *</label><textarea name="description" class="form-control" rows="4" placeholder="Please describe the issue in detail. Include location, time it started, and any relevant details..." required><?= old('description') ?></textarea></div>
      <div class="col-12"><label class="form-label">Attach Image (optional)</label><input type="file" name="image" class="form-control" accept="image/*"></div>
      <div class="col-12"><button type="submit" class="btn btn-fm-primary w-100 py-3 fw-bold"><i class="bi bi-send-fill me-2"></i>Submit Request</button></div>
    </div>
    <?= form_close() ?>
    <hr class="my-4">
    <div class="text-center"><p class="small text-muted mb-2">Already have a ticket number?</p>
    <form class="d-flex gap-2 justify-content-center" action="" onsubmit="var t=this.ticket.value.trim();if(t){window.location='<?= base_url('track/') ?>'+t;}return false;">
      <input type="text" name="ticket" class="form-control form-control-sm" placeholder="e.g. MR-2024-0001" style="max-width:200px">
      <button type="submit" class="btn btn-fm-outline btn-sm">Track</button>
    </form>
    </div>
    <div class="text-center mt-3"><a href="<?= base_url('login') ?>" class="small text-muted">Staff Login →</a></div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function(){
  const fac = document.getElementById('facility_id');
  const unit = document.getElementById('unit_id');
  async function loadUnits(fid) {
    unit.innerHTML = '<option value="">— None —</option>';
    if (!fid) return;
    try {
      const r = await fetch('<?= base_url('request/units/') ?>' + fid);
      const data = await r.json();
      data.forEach(u => {
        const o = document.createElement('option');
        o.value = u.id;
        o.textContent = (u.unit_number || 'Unit') + (u.floor ? ' · Floor ' + u.floor : '');
        unit.appendChild(o);
      });
    } catch (e) {}
  }
  fac.addEventListener('change', () => loadUnits(fac.value));
  if (fac.value) loadUnits(fac.value);
})();
</script>
</body></html>
