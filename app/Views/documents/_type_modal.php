<?php
/** Document type manager modal */
$redirect = $redirect ?? current_url();
?>
<div class="modal fade" id="dmsTypeModal" tabindex="-1" aria-labelledby="dmsTypeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="<?= base_url('documents/add-type') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="redirect" value="<?= esc($redirect) ?>">
        <div class="modal-header">
          <h5 class="modal-title" id="dmsTypeModalLabel"><i class="bi bi-tags me-2"></i>Document types</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Type name</label>
            <input type="text" name="type_label" class="form-control" required placeholder="e.g. Lease agreement">
          </div>
          <div class="mb-3">
            <label class="form-label">Key <span class="text-muted">(optional)</span></label>
            <input type="text" name="type_key" class="form-control" placeholder="lease_agreement">
          </div>
          <?php if (! empty($docTypes)): ?>
          <div class="small text-muted">Current types: <?= esc(implode(', ', array_values($docTypes))) ?></div>
          <?php endif; ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-fm-outline" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-fm-primary">Add type</button>
        </div>
      </form>
    </div>
  </div>
</div>
