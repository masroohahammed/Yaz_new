<?php
/** @var array $jc Job card row */
/** @var array $wo Work order */
?>
<div class="modal fade" id="completeJobCardModal" tabindex="-1" aria-labelledby="completeJobCardModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="completeJobCardModalLabel"><i class="bi bi-check2-circle me-2"></i>Complete Job Card — <?= esc($jc['jc_number']) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <?= form_open(base_url('job-cards/' . $jc['id'] . '/complete'), ['enctype' => 'multipart/form-data', 'class' => 'fm-submit-form', 'data-loader-msg' => 'Completing…']) ?>
      <div class="modal-body">
        <div class="alert alert-light border small mb-3">
          Data syncs to this work order (Labor, Materials, Costing). Next step: <strong>QA / Closure</strong> tab for finance invoice.
        </div>
        <div class="row g-3">
          <div class="col-sm-4">
            <label class="form-label small fw-medium">Actual Hours <span class="text-danger">*</span></label>
            <input type="number" name="labor_hours" class="form-control form-control-sm" step="0.5" min="0.5"
                   value="<?= esc($jc['scheduled_hours'] ?? $jc['labor_hours'] ?? '2') ?>" required>
          </div>
          <div class="col-12">
            <label class="form-label small fw-medium">Completion Notes <span class="text-danger">*</span></label>
            <textarea name="completion_notes" class="form-control form-control-sm" rows="3" required
                      placeholder="Work performed, findings, resolution…"><?= esc($jc['description'] ?? '') ?></textarea>
          </div>
          <div class="col-12">
            <label class="form-label small fw-medium">Technician Notes</label>
            <textarea name="technician_notes" class="form-control form-control-sm" rows="2"></textarea>
          </div>
          <div class="col-sm-6">
            <label class="form-label small fw-medium">Before Image</label>
            <input type="file" name="before_image" class="form-control form-control-sm" accept="image/*">
          </div>
          <div class="col-sm-6">
            <label class="form-label small fw-medium">After Image</label>
            <input type="file" name="after_image" class="form-control form-control-sm" accept="image/*">
          </div>
          <div class="col-12">
            <?= view('partials/_signature_pad', ['fieldName' => 'customer_signature', 'label' => 'Customer signature (optional)']) ?>
          </div>
          <div class="col-12">
            <label class="form-label small fw-medium">Materials Used (optional)</label>
            <div id="woMaterialsContainer">
              <div class="row g-2 mb-2 material-row">
                <div class="col-5"><input type="text" name="materials[0][item_name]" class="form-control form-control-sm" placeholder="Item name"></div>
                <div class="col-2"><input type="number" name="materials[0][quantity]" class="form-control form-control-sm" placeholder="Qty" min="0" step="0.1" value="1"></div>
                <div class="col-3"><input type="number" name="materials[0][unit_cost]" class="form-control form-control-sm" placeholder="Unit cost" min="0" step="0.01"></div>
                <div class="col-2"><button type="button" class="btn btn-sm btn-outline-danger w-100 wo-remove-mat" tabindex="-1">✕</button></div>
              </div>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary mt-1" id="woAddMaterial">+ Add Material</button>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-success fm-submit-btn"><i class="bi bi-check2-circle me-1"></i>Complete &amp; Go to QA</button>
      </div>
      <?= form_close() ?>
    </div>
  </div>
</div>
