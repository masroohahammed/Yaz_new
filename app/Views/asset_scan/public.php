<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <title><?= esc($asset['name']) ?> — Asset</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    body { background:#f4f7fb; font-family:system-ui,sans-serif; }
    .scan-hero { background:linear-gradient(135deg,#0a3d6b,#1565c0); color:#fff; padding:1.25rem 1rem 1.5rem; }
    .scan-card { border:0; border-radius:12px; box-shadow:0 4px 20px rgba(0,0,0,.08); }
    .action-btn { border-radius:10px; padding:.75rem; font-weight:600; }
  </style>
</head>
<body>
<div class="scan-hero">
  <div class="small opacity-75 mb-1"><i class="bi bi-qr-code-scan me-1"></i>Asset Scan</div>
  <h1 class="h4 mb-1 fw-bold"><?= esc($asset['name']) ?></h1>
  <div class="small"><?= esc($asset['asset_code']) ?> · <?= esc($asset['facility_name'] ?? '—') ?></div>
</div>

<div class="container px-3 py-3" style="max-width:520px">
  <?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success small"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>

  <div class="card scan-card mb-3">
    <div class="card-body">
      <div class="row g-2 small">
        <div class="col-6"><span class="text-muted">Status</span><br><strong><?= ucfirst(str_replace('_',' ',$asset['status'])) ?></strong></div>
        <div class="col-6"><span class="text-muted">Category</span><br><strong><?= ucfirst(str_replace('_',' ',$asset['category'])) ?></strong></div>
        <div class="col-6"><span class="text-muted">Location</span><br><?= esc($asset['location_in_facility'] ?: $asset['floor_room'] ?: '—') ?></div>
        <div class="col-6"><span class="text-muted">Serial</span><br><?= esc($asset['serial_number'] ?: '—') ?></div>
        <div class="col-6"><span class="text-muted">Last PM</span><br><?= !empty($asset['last_maintenance']) ? date('d M Y', strtotime($asset['last_maintenance'])) : '—' ?></div>
        <div class="col-6"><span class="text-muted">Warranty</span><br>
          <?php if (!empty($asset['warranty_expiry'])): ?>
          <?= date('d M Y', strtotime($asset['warranty_expiry'])) ?>
          <?php if (strtotime($asset['warranty_expiry']) < time()): ?><span class="text-danger">(Expired)</span><?php endif; ?>
          <?php else: ?>—<?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <?php if (!empty($openWos)): ?>
  <div class="card scan-card mb-3">
    <div class="card-body py-2">
      <div class="small fw-bold mb-2 text-warning"><i class="bi bi-exclamation-circle me-1"></i>Open Work Orders</div>
      <?php foreach ($openWos as $wo): ?>
      <div class="d-flex justify-content-between small border-bottom py-1">
        <span><?= esc($wo['wo_number']) ?></span>
        <span class="text-muted"><?= ucfirst($wo['status']) ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="d-grid gap-2 mb-3">
    <?php if ($isLoggedIn): ?>
    <a href="<?= base_url('workorders/create?asset_id='.(int)$asset['id'].'&facility_id='.(int)($asset['facility_id']??0)) ?>" class="btn btn-primary action-btn">
      <i class="bi bi-tools me-2"></i>Create Work Order
    </a>
    <a href="<?= base_url('helpdesk/create?asset_id='.(int)$asset['id'].'&facility_id='.(int)($asset['facility_id']??0)) ?>" class="btn btn-outline-primary action-btn">
      <i class="bi bi-megaphone me-2"></i>Report Complaint
    </a>
    <a href="<?= base_url('asset-register/view/'.(int)$asset['id']) ?>" class="btn btn-outline-secondary action-btn">
      <i class="bi bi-box-arrow-in-right me-2"></i>Full Asset Record
    </a>
    <?php else: ?>
    <button class="btn btn-danger action-btn" type="button" data-bs-toggle="collapse" data-bs-target="#reportForm">
      <i class="bi bi-exclamation-triangle me-2"></i>Report Breakdown
    </button>
    <?php endif; ?>
  </div>

  <?php if (! $isLoggedIn): ?>
  <div class="collapse" id="reportForm">
    <div class="card scan-card mb-3">
      <div class="card-body">
        <h2 class="h6 fw-bold mb-3">Report Issue</h2>
        <?= form_open(base_url('scan/asset/'.esc($asset['qr_token']).'/complaint')) ?>
        <div class="mb-2">
          <label class="form-label small">Your Name</label>
          <input type="text" name="requester_name" class="form-control form-control-sm" required>
        </div>
        <div class="mb-2">
          <label class="form-label small">Phone / Email</label>
          <input type="text" name="requester_phone" class="form-control form-control-sm">
        </div>
        <div class="mb-2">
          <label class="form-label small">Priority</label>
          <select name="priority" class="form-select form-select-sm">
            <option value="high">High</option>
            <option value="medium" selected>Medium</option>
            <option value="low">Low</option>
            <option value="critical">Critical</option>
          </select>
        </div>
        <div class="mb-2">
          <label class="form-label small">Issue Description</label>
          <textarea name="description" class="form-control form-control-sm" rows="3" required placeholder="Describe the problem..."></textarea>
        </div>
        <button type="submit" class="btn btn-danger btn-sm w-100">Submit Complaint</button>
        <?= form_close() ?>
      </div>
    </div>
  </div>
  <a href="<?= base_url('login') ?>" class="btn btn-link btn-sm text-center d-block">Staff login for full access</a>
  <?php endif; ?>

  <div class="text-center small text-muted py-3">
    <img src="<?= esc($qrImageUrl) ?>" alt="QR" width="100" class="mb-2 opacity-50">
    <div><?= esc($settings['company_name'] ?? 'FM ERP') ?></div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
