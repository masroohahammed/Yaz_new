<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Access Denied</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
html{font-size:95%}
body{min-height:100vh;display:flex;align-items:center;justify-content:center;background:#f4f5f7;font-family:system-ui,sans-serif}
.card{max-width:420px;border:0;border-radius:16px;box-shadow:0 12px 40px rgba(0,0,0,.08)}
</style>
</head>
<body>
<div class="card p-4 text-center">
  <div class="text-danger mb-3" style="font-size:2.5rem"><i class="bi bi-shield-x"></i>🔒</div>
  <h1 class="h4 mb-2">Access Denied</h1>
  <p class="text-muted small mb-4"><?= esc($message ?? 'You do not have permission to view this page.') ?></p>
  <a href="<?= esc($homeUrl ?? base_url('dashboard')) ?>" class="btn btn-primary btn-sm">Go to Home</a>
</div>
</body>
</html>
