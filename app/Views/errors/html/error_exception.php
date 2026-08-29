<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>500 — Server Error</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
body{background:linear-gradient(135deg,#76002b 0%,#1a1a2e 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:'Segoe UI',sans-serif;margin:0}
.card{background:#fff;border-radius:20px;padding:48px 40px;text-align:center;max-width:480px;box-shadow:0 24px 60px rgba(0,0,0,.3)}
.icon{font-size:4rem;color:#c62828;margin-bottom:16px}
.title{font-size:1.4rem;font-weight:700;margin:0 0 8px;color:#1a2332}
p{color:#6b7a8d;margin-bottom:24px;line-height:1.6}
a.btn-home{background:#76002b;color:#fff;border-radius:10px;padding:10px 28px;text-decoration:none;font-weight:700;display:inline-block}
a.btn-home:hover{opacity:.9;color:#fff}
</style>
</head>
<body>
<div class="card">
  <div class="icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
  <div class="title">Server Error</div>
  <p>Something went wrong on our end. Our team has been notified.<br>Please try again or return to the dashboard.</p>
  <a href="<?= base_url('dashboard') ?>" class="btn-home"><i class="bi bi-house me-2"></i>Back to Dashboard</a>
  <?php if(ENVIRONMENT==='development' && isset($message)): ?>
  <div style="margin-top:24px;text-align:left;background:#f8f9fa;border-radius:8px;padding:12px;font-size:.75rem;color:#495057;white-space:pre-wrap;max-height:200px;overflow:auto"><?= esc($message) ?></div>
  <?php endif; ?>
</div>
</body>
</html>
