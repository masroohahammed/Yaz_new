<?php
/**
 * Digital signature actions for a lease contract row.
 * @var array<string,mixed>|null $lease
 * @var string|null $signLink Flash copy of signing URL
 */
$lease = $lease ?? null;
if (empty($lease['id'])) {
    return;
}
$leaseId = (int) $lease['id'];
$hasSignature = trim((string) ($lease['tenant_signature_path'] ?? '')) !== '';
$signedAt = $lease['tenant_signed_at'] ?? null;
?>
<div class="form-card mb-3">
  <h6 class="text-muted text-uppercase small mb-3">Digital signature</h6>
  <?php if (! empty($signLink)): ?>
<div class="alert alert-info py-2 mb-3">
    <div class="small fw-semibold mb-1">Tenant signing link</div>
    <div class="input-group input-group-sm">
      <input type="text" class="form-control" id="tenantSignLink_<?= $leaseId ?>" readonly value="<?= esc($signLink) ?>">
      <button type="button" class="btn btn-fm-outline" onclick="navigator.clipboard.writeText(document.getElementById('tenantSignLink_<?= $leaseId ?>').value)">Copy</button>
      <a href="<?= esc($signLink) ?>" target="_blank" rel="noopener" class="btn btn-fm-primary">Open</a>
    </div>
</div>
<?php endif; ?>
<?php if ($signSql = session()->getFlashdata('sign_sql')): ?>
<div class="alert alert-warning py-2 mb-3">
  <div class="small fw-semibold mb-1">Run this SQL in phpMyAdmin to enable digital signatures:</div>
  <pre class="small mb-0" style="white-space:pre-wrap"><?= esc($signSql) ?></pre>
</div>
<?php endif; ?>
  <?php if ($hasSignature): ?>
    <?= view('partials/_signature_display', [
        'path'  => $lease['tenant_signature_path'],
        'label' => 'Tenant signature' . ($signedAt ? ' · ' . date('d M Y H:i', strtotime((string) $signedAt)) : ''),
    ]) ?>
    <div class="d-flex gap-2 flex-wrap">
      <a href="<?= base_url('contracts/' . $leaseId . '/signed-pdf') ?>" class="btn btn-sm btn-fm-primary"><i class="bi bi-file-earmark-pdf me-1"></i>Download signed PDF</a>
      <a href="<?= base_url('contracts/' . $leaseId . '/whatsapp-share') ?>" class="btn btn-sm btn-success" target="_blank" rel="noopener"><i class="bi bi-whatsapp me-1"></i>Share via WhatsApp</a>
      <a href="<?= base_url('contracts/' . $leaseId) ?>" class="btn btn-sm btn-fm-outline">Contract detail</a>
    </div>
  <?php else: ?>
    <p class="small text-muted mb-2">Generate a link and send it to the tenant. They can sign on any device without logging in.</p>
    <form method="post" action="<?= base_url('contracts/' . $leaseId . '/generate-sign-link') ?>" class="d-inline"><?= csrf_field() ?>
      <button type="submit" class="btn btn-sm btn-fm-primary"><i class="bi bi-link-45deg me-1"></i>Generate signing link</button>
    </form>
  <?php endif; ?>
</div>
