<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= esc($title ?? 'Sign Contract') ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
  body { background: #f4f6f9; font-family: system-ui, sans-serif; }
  .sign-card { max-width: 640px; margin: 2rem auto; }
  .brand-logo { max-height: 48px; }
</style>
</head>
<body>
<div class="sign-card">
  <div class="card shadow-sm border-0">
    <div class="card-body p-4">
      <div class="d-flex align-items-center gap-3 mb-3">
        <?php if (! empty($companyLogoUrl)): ?>
          <img src="<?= esc($companyLogoUrl) ?>" alt="Logo" class="brand-logo">
        <?php endif; ?>
        <div>
          <h1 class="h5 mb-0">Lease Contract Signature</h1>
          <div class="small text-muted"><?= esc($settings['company_name'] ?? '') ?></div>
        </div>
      </div>

      <?php if ($msg = session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc($msg) ?></div>
      <?php endif; ?>
      <?php if ($msg = session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc($msg) ?></div>
      <?php endif; ?>
      <?php if ($msg = session()->getFlashdata('info')): ?>
        <div class="alert alert-info"><?= esc($msg) ?></div>
      <?php endif; ?>

      <div class="border rounded p-3 mb-3 bg-light">
        <div class="small"><strong>Contract:</strong> <?= esc($contract['contract_number'] ?? '') ?></div>
        <div class="small"><strong>Tenant:</strong> <?= esc($contract['tenant_name'] ?? '') ?></div>
        <?php if ($tenantQid !== ''): ?>
          <div class="small"><strong>QID / Passport:</strong> <?= esc($tenantQid) ?></div>
        <?php endif; ?>
        <div class="small"><strong>Property:</strong> <?= esc($contract['facility_name'] ?? '') ?> · Unit <?= esc($contract['unit_number'] ?? '—') ?></div>
        <div class="small"><strong>Period:</strong> <?= esc($contract['start_date'] ?? '') ?> – <?= esc($contract['end_date'] ?? '') ?></div>
      </div>

      <?php if (! empty($alreadySigned) && ! empty($signaturePreview)): ?>
        <div class="text-center mb-3">
          <div class="small text-muted mb-2">Your signature<?= ! empty($signedAt) ? ' · ' . esc(date('d M Y H:i', strtotime((string) $signedAt))) : '' ?></div>
          <img src="<?= esc($signaturePreview) ?>" alt="Signature" style="max-width:100%;max-height:120px;border:1px solid #dee2e6;border-radius:8px;background:#fff;padding:8px">
        </div>
        <p class="small text-success text-center mb-0"><i class="bi bi-check-circle-fill me-1"></i>This contract is signed. You may close this page.</p>
      <?php else: ?>
        <p class="small text-muted">Please sign below next to your name. Use your finger on mobile or mouse on desktop.</p>
        <?= form_open(base_url('contract/sign/' . rawurlencode((string) $token))) ?>
          <?= csrf_field() ?>
          <?= view('partials/_signature_pad', ['fieldName' => 'tenant_signature', 'label' => 'Tenant signature']) ?>
          <button type="submit" class="btn btn-primary w-100"><i class="bi bi-pen me-1"></i>Submit Signature</button>
        <?= form_close() ?>
      <?php endif; ?>
    </div>
  </div>
</div>
<script src="<?= base_url('assets/js/signature-pad.js') ?>"></script>
</body>
</html>
