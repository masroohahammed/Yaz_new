<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-card-checklist me-2"></i>Edit <?= esc($jc['jc_number']) ?></h1></div></div>
<?= form_open_multipart(base_url('job-cards/' . $jc['id'] . '/update'), ['class' => 'fm-submit-form']) ?>
<div class="row g-3">
  <div class="col-lg-8">
    <div class="fm-form-section">
      <h6><i class="bi bi-card-text"></i>Work Description</h6>
      <textarea name="description" class="form-control <?= session('errors.description') ? 'is-invalid' : '' ?>" rows="6" required><?= esc(old('description', $jc['description'])) ?></textarea>
      <?php if (session('errors.description')): ?><div class="invalid-feedback d-block"><?= esc(session('errors.description')) ?></div><?php endif; ?>
    </div>
    <div class="fm-form-section">
      <h6><i class="bi bi-check-square"></i>Completion Notes</h6>
      <textarea name="completion_notes" class="form-control" rows="4" placeholder="Notes on what was done..."><?= esc(old('completion_notes', $jc['completion_notes'] ?? '')) ?></textarea>
    </div>
    <div class="fm-form-section">
      <h6><i class="bi bi-camera"></i>Before / After Photos</h6>
      <div class="row g-2">
        <div class="col-md-6"><label class="form-label small">Before Image</label><input type="file" name="before_image" class="form-control" accept="image/*"><?php if($jc['before_image']): ?><a href="<?= base_url('file/job_cards/'.basename($jc['before_image'])) ?>" target="_blank" class="small text-primary">View current</a><?php endif; ?></div>
        <div class="col-md-6"><label class="form-label small">After Image</label><input type="file" name="after_image" class="form-control" accept="image/*"><?php if($jc['after_image']): ?><a href="<?= base_url('file/job_cards/'.basename($jc['after_image'])) ?>" target="_blank" class="small text-primary">View current</a><?php endif; ?></div>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="fm-form-section">
      <h6><i class="bi bi-sliders"></i>Status &amp; Hours</h6>
      <div class="mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
          <?php foreach(['draft'=>'Draft','in_progress'=>'In Progress','completed'=>'Completed'] as $v=>$l): ?>
          <option value="<?= $v ?>" <?= old('status', $jc['status'])===$v?'selected':'' ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mb-0">
        <label class="form-label">Labor Hours</label>
        <input type="number" name="labor_hours" class="form-control <?= session('errors.labor_hours') ? 'is-invalid' : '' ?>" step="0.5" min="0" value="<?= esc(old('labor_hours', $jc['labor_hours'])) ?>">
      </div>
    </div>
  </div>
</div>
<div class="d-flex gap-2 mt-2">
  <button type="submit" class="btn btn-fm-primary fm-submit-btn"><i class="bi bi-check-lg me-2"></i>Save Changes</button>
  <a href="<?= base_url('job-cards/view/'.$jc['id']) ?>" class="btn btn-fm-outline">Cancel</a>
</div>
<?= form_close() ?>
<?= $this->endSection() ?>
