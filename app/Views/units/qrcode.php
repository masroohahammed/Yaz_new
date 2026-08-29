<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-qr-code me-2"></i>QR Code — Unit <?= esc($unit['unit_number']) ?></h1></div>
  <a href="<?= base_url('units/view/' . (int) $unit['id']) ?>" class="btn btn-fm-outline btn-sm">← Back</a>
</div>
<div class="row justify-content-center">
  <div class="col-md-5 text-center">
    <div class="fm-card">
      <div class="fm-card-body">
        <img src="<?= esc($qrImageUrl) ?>" alt="QR Code" class="img-fluid mb-3" style="max-width:280px">
        <div class="fw-bold fs-5">Unit <?= esc($unit['unit_number']) ?></div>
        <div class="text-muted"><?= esc($unit['facility_name'] ?? '') ?></div>
        <div class="small text-break mt-2 text-muted"><?= esc($scanUrl) ?></div>
        <div class="d-flex gap-2 justify-content-center mt-3 flex-wrap">
          <a href="<?= esc($qrImageUrl) ?>" download="unit-<?= (int) $unit['id'] ?>-qr.png" class="btn btn-fm-primary btn-sm"><i class="bi bi-download me-1"></i>Download</a>
          <button type="button" class="btn btn-fm-outline btn-sm" onclick="window.print()"><i class="bi bi-printer me-1"></i>Print QR</button>
        </div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
