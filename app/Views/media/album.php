<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<?php if(!empty($album)): ?>
<!-- Single album view -->
<div class="page-header">
  <div>
    <h1><i class="bi bi-images me-2 text-primary"></i><?= esc($album['title']) ?></h1>
    <nav aria-label="breadcrumb"><ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="<?= base_url('media') ?>">Media</a></li>
      <li class="breadcrumb-item active"><?= esc($album['title']) ?></li>
    </ol></nav>
  </div>
  <div class="d-flex gap-2">
    <span class="fm-badge badge-status-<?= $album['album_type'] ?> align-self-center"><?= ucfirst($album['album_type']) ?></span>
    <?php if(!$album['is_locked']): ?>
    <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#lockAlbumModal"><i class="bi bi-lock me-1"></i>Lock Album</button>
    <?php else: ?>
    <span class="fm-badge badge-status-completed align-self-center"><i class="bi bi-lock-fill me-1"></i>Locked</span>
    <?php endif; ?>
    <a href="<?= base_url('media') ?>" class="btn btn-fm-outline btn-sm">← Albums</a>
  </div>
</div>

<?php if($album['is_locked'] && $album['signature_path']): ?>
<div class="alert alert-success d-flex align-items-center gap-2 mb-3">
  <i class="bi bi-shield-check fs-5"></i>
  <div>Album locked on <?= date('d M Y H:i', strtotime($album['locked_at'] ?? $album['created_at'])) ?>.
    <a href="<?= base_url('file/'.$album['signature_path']) ?>" target="_blank" class="ms-2">View Signature</a>
  </div>
</div>
<?php endif; ?>

<?php if(!$album['is_locked']): ?>
<!-- Upload form -->
<div class="fm-card p-3 mb-3">
  <form action="<?= base_url('media/albums/'.$album['id'].'/upload') ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-2 align-items-end">
      <div class="col-sm-8">
        <label class="form-label fw-medium">Add Photos</label>
        <input type="file" name="media_files[]" class="form-control" multiple accept="image/*">
      </div>
      <div class="col-sm-4">
        <button type="submit" class="btn btn-fm-primary w-100"><i class="bi bi-upload me-1"></i>Upload</button>
      </div>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="fm-card">
  <div class="fm-card-body">
    <?php if(empty($items)): ?>
    <div class="text-center py-5 text-muted"><i class="bi bi-images d-block mb-2" style="font-size:2.5rem"></i>No images yet.</div>
    <?php else: ?>
    <?php
    // Before/After compare view for before_after albums
    $isBa = ($album['album_type'] === 'before_after');
    $befores = array_filter($items, fn($i) => $i['condition_tag'] === 'before');
    $afters  = array_filter($items, fn($i) => $i['condition_tag'] === 'after');
    $others  = array_filter($items, fn($i) => !in_array($i['condition_tag'], ['before','after']));
    ?>
    <?php if($isBa && (!empty($befores) || !empty($afters))): ?>
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <h6 class="text-muted fw-semibold mb-2"><i class="bi bi-arrow-left-circle me-1"></i>Before</h6>
        <div class="row g-2">
          <?php foreach($befores as $b): ?>
          <div class="col-6"><a href="<?= base_url($b['file_path']) ?>" target="_blank"><img src="<?= base_url($b['file_path']) ?>" class="img-fluid rounded" style="max-height:160px;object-fit:cover;width:100%"></a><?php if($b['caption']): ?><div class="small text-muted mt-1"><?= esc($b['caption']) ?></div><?php endif; ?></div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="col-md-6">
        <h6 class="text-muted fw-semibold mb-2"><i class="bi bi-arrow-right-circle me-1"></i>After</h6>
        <div class="row g-2">
          <?php foreach($afters as $a): ?>
          <div class="col-6"><a href="<?= base_url($a['file_path']) ?>" target="_blank"><img src="<?= base_url($a['file_path']) ?>" class="img-fluid rounded" style="max-height:160px;object-fit:cover;width:100%"></a><?php if($a['caption']): ?><div class="small text-muted mt-1"><?= esc($a['caption']) ?></div><?php endif; ?></div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <div class="row g-2">
      <?php foreach(($isBa ? $others : $items) as $item): ?>
      <div class="col-6 col-sm-4 col-md-3">
        <div class="position-relative">
          <a href="<?= base_url($item['file_path']) ?>" target="_blank">
            <img src="<?= base_url($item['file_path']) ?>" class="img-fluid rounded" style="height:140px;object-fit:cover;width:100%">
          </a>
          <?php if($item['condition_tag']): ?><span class="position-absolute top-0 start-0 badge bg-dark m-1"><?= esc($item['condition_tag']) ?></span><?php endif; ?>
        </div>
        <?php if($item['caption']): ?><div class="small text-muted mt-1"><?= esc($item['caption']) ?></div><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Lock Album Modal -->
<div class="modal fade" id="lockAlbumModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?= base_url('media/albums/'.$album['id'].'/lock') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-lock me-2"></i>Lock Album</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted small">Locking will prevent further changes. Attach a signature image if required.</p>
          <div class="mb-3">
            <label class="form-label fw-medium">Signature Image (optional)</label>
            <input type="file" name="signature_file" class="form-control" accept="image/*">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning"><i class="bi bi-lock me-1"></i>Lock Album</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php else: ?>
<!-- Album list view -->
<div class="page-header">
  <div>
    <h1><i class="bi bi-images me-2 text-primary"></i>Media Albums</h1>
  </div>
  <button class="btn btn-fm-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newAlbumModal"><i class="bi bi-plus me-1"></i>New Album</button>
</div>

<?php if(!empty($migrationRequired)): ?>
<div class="alert alert-warning">Media tables are not available. Run database migration first.</div>
<?php elseif(empty($albums)): ?>
<div class="fm-card"><div class="fm-card-body text-center py-5 text-muted"><i class="bi bi-images d-block mb-2" style="font-size:2.5rem"></i>No albums found.</div></div>
<?php else: ?>
<div class="row g-3">
<?php foreach($albums as $a): ?>
<div class="col-md-4 col-lg-3">
  <div class="fm-card h-100">
    <div class="fm-card-body p-3">
      <div class="d-flex justify-content-between align-items-start mb-2">
        <span class="badge bg-secondary"><?= ucfirst($a['album_type']) ?></span>
        <?php if($a['is_locked']): ?><i class="bi bi-lock-fill text-warning" title="Locked"></i><?php endif; ?>
      </div>
      <h6 class="fw-semibold mb-1"><?= esc($a['title']) ?></h6>
      <div class="small text-muted mb-2"><?= $a['item_count'] ?> photo<?= $a['item_count'] != 1 ? 's' : '' ?></div>
      <div class="small text-muted mb-3"><?= esc($a['module']) ?> #<?= $a['ref_id'] ?></div>
      <a href="<?= base_url('media/albums/'.$a['id']) ?>" class="btn btn-sm btn-fm-outline w-100">View Album</a>
    </div>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<!-- New Album Modal -->
<div class="modal fade" id="newAlbumModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?= base_url('media/albums') ?>" method="post">
        <?= csrf_field() ?>
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-plus me-2"></i>New Album</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-medium">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control" required>
          </div>
          <div class="row g-2">
            <div class="col-sm-6">
              <label class="form-label fw-medium">Module</label>
              <input type="text" name="module" class="form-control" placeholder="facility / unit / work_order">
            </div>
            <div class="col-sm-6">
              <label class="form-label fw-medium">Record ID</label>
              <input type="number" name="ref_id" class="form-control" min="1">
            </div>
          </div>
          <div class="mb-3 mt-3">
            <label class="form-label fw-medium">Album Type</label>
            <select name="album_type" class="form-select">
              <?php foreach(['handover'=>'Handover','return'=>'Return','condition'=>'Condition','before_after'=>'Before / After','general'=>'General'] as $v=>$l): ?>
              <option value="<?= $v ?>"><?= $l ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary-brand">Create Album</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
