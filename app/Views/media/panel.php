<?php
/**
 * Embeddable media panel — include in any module view.
 * Required vars: $albums (array), $module (string), $refId (int), $albumTypes (array)
 */
?>
<div class="fm-card mt-3">
  <div class="card-header-fm">
    <h5><i class="bi bi-images me-2"></i>Media Albums <span class="badge bg-secondary ms-1"><?= count($albums) ?></span></h5>
    <button class="btn btn-fm-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newMediaAlbumModal-<?= $refId ?>">
      <i class="bi bi-plus me-1"></i>New Album
    </button>
  </div>
  <div class="fm-card-body">
    <?php if(empty($albums)): ?>
    <p class="text-muted small text-center py-3">No media albums. Create one to store photos.</p>
    <?php else: ?>
    <div class="row g-2">
      <?php foreach($albums as $a): ?>
      <div class="col-sm-6 col-md-4">
        <div class="border rounded p-2 d-flex align-items-center gap-2">
          <?php if($a['is_locked']): ?><i class="bi bi-lock-fill text-warning"></i><?php else: ?><i class="bi bi-folder2-open text-primary"></i><?php endif; ?>
          <div class="flex-grow-1 min-w-0">
            <div class="fw-semibold small text-truncate"><?= esc($a['title']) ?></div>
            <div class="text-muted" style="font-size:.75rem"><?= $a['item_count'] ?> photo<?= $a['item_count'] != 1 ? 's' : '' ?> &bull; <?= ucfirst($a['album_type']) ?></div>
          </div>
          <a href="<?= base_url('media/albums/'.$a['id']) ?>" class="btn btn-sm btn-outline-secondary flex-shrink-0"><i class="bi bi-eye"></i></a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- New Media Album Modal (unique by refId) -->
<div class="modal fade" id="newMediaAlbumModal-<?= $refId ?>" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?= base_url('media/albums') ?>" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="module" value="<?= esc($module) ?>">
        <input type="hidden" name="ref_id" value="<?= (int)$refId ?>">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-images me-2"></i>New Album</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-medium">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-medium">Album Type</label>
            <select name="album_type" class="form-select">
              <?php foreach($albumTypes as $t): ?>
              <option value="<?= $t ?>"><?= ucfirst(str_replace('_', ' ', $t)) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary-brand">Create</button>
        </div>
      </form>
    </div>
  </div>
</div>
