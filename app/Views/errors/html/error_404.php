<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>404 — Page Not Found</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
body{background:linear-gradient(135deg,#76002b 0%,#1a1a2e 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:'Segoe UI',sans-serif}
.card{background:#fff;border-radius:20px;padding:48px 40px;text-align:center;max-width:480px;box-shadow:0 24px 60px rgba(0,0,0,.3)}
.num{font-size:6rem;font-weight:800;color:#76002b;line-height:1}
.title{font-size:1.4rem;font-weight:700;margin:12px 0 8px;color:#1a2332}
p{color:#6b7a8d;margin-bottom:24px}
a.btn-home{background:#76002b;color:#fff;border-radius:10px;padding:10px 28px;text-decoration:none;font-weight:700;display:inline-block;transition:.2s}
a.btn-home:hover{opacity:.9;color:#fff}
</style>
</head>
<body>
<div class="card">
  <div class="num">404</div>
  <div class="title">Page Not Found</div>
  <p>The page you're looking for doesn't exist or has been moved.</p>
  <a href="<?= base_url('dashboard') ?>" class="btn-home"><i class="bi bi-house me-2"></i>Back to Dashboard</a>
  <div class="mt-4" style="font-size:.75rem;color:#9ca3af">FM ERP Platform</div>
</div>
</body>
</html>
