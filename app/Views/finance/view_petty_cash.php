<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-wallet me-2"></i><?= esc($pc['pc_number']) ?></h1>
    <span class="fm-badge badge-status-<?= esc($pc['status']) ?>"><?= ucfirst($pc['status']) ?></span>
  </div>
  <a href="<?= base_url('finance/petty-cash') ?>" class="btn btn-fm-outline btn-sm">← Petty Cash</a>
</div>
<div class="row g-3">
  <div class="col-lg-6">
    <div class="fm-card"><div class="fm-card-body">
      <p><strong>Amount:</strong> <?= $currency ?> <?= number_format($pc['amount'], 2) ?></p>
      <p><strong>Requested by:</strong> <?= esc($pc['requested_by_name'] ?? '—') ?></p>
      <p><strong>Facility:</strong> <?= esc($pc['facility_name'] ?? '—') ?></p>
      <p><strong>Purpose:</strong> <?= esc($pc['purpose']) ?></p>
      <?php if (!empty($pc['notes'])): ?><p class="text-muted small"><?= esc($pc['notes']) ?></p><?php endif; ?>
    </div></div>
  </div>
  <div class="col-lg-6">
    <div class="fm-card"><div class="fm-card-body">
      <?php $role = session()->get('user_role'); ?>
      <?php if ($pc['status'] === 'issued' && in_array($role, ['super_admin','finance_manager','finance_user'], true)): ?>
      <?= form_open_multipart(base_url('finance/petty-cash/reconcile/'.$pc['id'])) ?>
        <label class="form-label">Reconciliation notes</label>
        <textarea name="notes" class="form-control mb-2" rows="2"></textarea>
        <label class="form-label">Receipt (optional)</label>
        <input type="file" name="receipt" class="form-control mb-2" accept="image/*,.pdf">
        <button type="submit" class="btn btn-fm-primary w-100">Submit Reconciliation</button>
      <?= form_close() ?>
      <?php elseif ($pc['status'] === 'reconciliation' && in_array($role, ['super_admin','finance_manager','finance_user'], true)): ?>
      <?= form_open(base_url('finance/petty-cash/close/'.$pc['id'])) ?>
        <p class="small text-muted">Receipt on file. Close to post expense to ledger.</p>
        <?php if (!empty($pc['receipt_path'])): ?>
        <p><a href="<?= base_url('file/petty_cash/'.basename($pc['receipt_path'])) ?>" target="_blank">View receipt</a></p>
        <?php endif; ?>
        <button type="submit" class="btn btn-fm-primary w-100" onclick="return confirm('Close petty cash?')">Close & Post Expense</button>
      <?= form_close() ?>
      <?php else: ?>
      <p class="text-muted small mb-0">No actions for current status.</p>
      <?php endif; ?>
    </div></div>
  </div>
</div>
<?= $this->endSection() ?>
