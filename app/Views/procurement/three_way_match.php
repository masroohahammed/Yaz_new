<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$po = $analysis['po'] ?? [];
$status = $analysis['match_status'] ?? 'pending';
$badge = $status === 'matched' ? 'success' : ($status === 'exception' ? 'danger' : 'secondary');
?>
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2">
  <div><h1><i class="bi bi-intersect me-2"></i>3-Way Match</h1><p class="text-muted mb-0 small"><?= esc($po['po_number'] ?? '') ?> · <?= esc($po['vendor_name'] ?? '') ?></p></div>
  <a href="<?= base_url('procurement/order/view/'.$poId) ?>" class="btn btn-fm-outline btn-sm">Back to PO</a>
</div>
<?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= session()->getFlashdata('success') ?></div><?php endif; ?>
<div class="row g-3 mb-3">
  <div class="col-md-4"><div class="fm-card p-3"><div class="small text-muted">PO amount</div><div class="fs-5 fw-bold"><?= number_format((float)($analysis['po_amount'] ?? 0), 2) ?></div></div></div>
  <div class="col-md-4"><div class="fm-card p-3"><div class="small text-muted">GRN value</div><div class="fs-5 fw-bold"><?= number_format((float)($analysis['grn_amount'] ?? 0), 2) ?></div></div></div>
  <div class="col-md-4"><div class="fm-card p-3"><div class="small text-muted">Vendor bill</div><div class="fs-5 fw-bold"><?= number_format((float)($analysis['bill_amount'] ?? 0), 2) ?></div></div></div>
</div>
<div class="fm-card mb-3"><div class="fm-card-body">
  <p><strong>Status:</strong> <span class="badge bg-<?= $badge ?>"><?= esc(ucfirst($status)) ?></span>
  · <strong>Variance (bill − GRN):</strong> <?= number_format((float)($analysis['variance'] ?? 0), 2) ?></p>
  <?php if (empty($analysis['grn'])): ?><p class="small text-warning mb-0">No GRN recorded for this PO yet.</p><?php endif; ?>
  <?php if (empty($analysis['bill'])): ?><p class="small text-warning mb-0">No vendor bill linked — create in Finance → Vendor bills.</p><?php endif; ?>
</div></div>
<?= form_open(base_url('procurement/order/three-way/'.$poId), ['class' => 'fm-card']) ?>
<?= csrf_field() ?>
<div class="fm-card-body">
  <label class="form-label small">Notes</label>
  <textarea name="notes" class="form-control form-control-sm mb-2" rows="2"><?= esc($history['notes'] ?? '') ?></textarea>
  <button type="submit" class="btn btn-fm-primary btn-sm">Save match record</button>
</div>
<?= form_close() ?>
<?php if ($status === 'exception' && in_array(session()->get('user_role'), ['super_admin', 'finance_manager'], true)): ?>
<div class="fm-card mt-3 border-warning"><div class="fm-card-body">
  <h6 class="fw-bold">Finance override</h6>
  <p class="small text-muted mb-2">Approve this exception to allow vendor bill payment when variance is explained.</p>
  <?= form_open(base_url('procurement/order/three-way-approve/'.$poId)) ?>
  <?= csrf_field() ?>
  <textarea name="notes" class="form-control form-control-sm mb-2" rows="2" placeholder="Approval notes"></textarea>
  <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('Approve exception for payment?')">Approve for payment</button>
  <?= form_close() ?>
</div></div>
<?php endif; ?>
<?= $this->endSection() ?>
