<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div>
    <h1><i class="bi bi-plus-circle me-2 text-primary"></i>Submit Maintenance Ticket</h1>
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb mb-0 small">
        <li class="breadcrumb-item"><a href="<?= base_url('portal') ?>">Portal</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('portal/tickets') ?>">My Tickets</a></li>
        <li class="breadcrumb-item active">New Ticket</li>
      </ol>
    </nav>
  </div>
  <a href="<?= base_url('portal/tickets') ?>" class="btn btn-outline-secondary btn-sm">
    <i class="bi bi-arrow-left me-1"></i>Back
  </a>
</div>

<?php if (session()->getFlashdata('errors') || ! empty($errors)): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>Please fix the following:</strong>
    <ul class="mb-0 mt-1">
      <?php foreach ((array)(session()->getFlashdata('errors') ?? $errors ?? []) as $err): ?>
        <li><?= esc($err) ?></li>
      <?php endforeach; ?>
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<div class="row justify-content-center">
  <div class="col-lg-8 col-xl-7">
    <div class="fm-card form-card">
      <div class="card-header-fm">
        <h5><i class="bi bi-wrench-adjustable me-2"></i>Ticket Details</h5>
      </div>
      <div class="fm-card-body">
        <form method="post" action="<?= base_url('portal/tickets') ?>" enctype="multipart/form-data">
          <?= csrf_field() ?>

          <!-- Title -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
            <input type="text" name="title" class="form-control"
                   value="<?= esc(old('title')) ?>"
                   placeholder="Brief description of the issue"
                   required maxlength="200">
          </div>

          <div class="row g-3 mb-3">
            <!-- Category -->
            <div class="col-sm-6">
              <label class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
              <select name="category" class="form-select" required>
                <option value="">— Select —</option>
                <?php
                  $cats = ['Plumbing', 'Electrical', 'HVAC / Air Conditioning', 'Structural', 'Pest Control',
                           'Cleaning', 'Painting', 'Appliances', 'Security / Locks', 'Common Area', 'Other'];
                  foreach ($cats as $c):
                    $sel = old('category') === $c ? ' selected' : '';
                ?>
                <option value="<?= esc($c) ?>"<?= $sel ?>><?= esc($c) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Priority -->
            <div class="col-sm-6">
              <label class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
              <select name="priority" class="form-select" required>
                <option value="">— Select —</option>
                <option value="low"      <?= old('priority') === 'low'      ? 'selected' : '' ?>>Low</option>
                <option value="medium"   <?= old('priority') === 'medium'   ? 'selected' : '' ?>>Medium</option>
                <option value="high"     <?= old('priority') === 'high'     ? 'selected' : '' ?>>High</option>
                <option value="critical" <?= old('priority') === 'critical' ? 'selected' : '' ?>>Critical — Urgent</option>
              </select>
            </div>
          </div>

          <!-- Unit (optional) -->
          <?php if (! empty($units)): ?>
          <div class="mb-3">
            <label class="form-label fw-semibold">Unit / Location</label>
            <select name="unit_id" class="form-select">
              <option value="">— Not specified —</option>
              <?php foreach ($units as $u): ?>
              <option value="<?= (int)$u['id'] ?>" <?= old('unit_id') == $u['id'] ? 'selected' : '' ?>>
                <?= esc($u['facility_name'] ?? '') ?> — Unit <?= esc($u['unit_number']) ?>
              </option>
              <?php endforeach; ?>
            </select>
            <div class="form-text">Select the unit where the issue is located.</div>
          </div>
          <?php endif; ?>

          <!-- Description -->
          <div class="mb-3">
            <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
            <textarea name="description" class="form-control" rows="5"
                      placeholder="Please describe the issue in detail — when it started, how severe it is, any safety concerns…"
                      required minlength="10"><?= esc(old('description')) ?></textarea>
          </div>

          <!-- Photo (optional) -->
          <div class="mb-4">
            <label class="form-label fw-semibold">Photo <span class="text-muted small">(optional)</span></label>
            <input type="file" name="photo" class="form-control" accept="image/*,.pdf">
            <div class="form-text">Upload a photo or document that helps describe the issue (max 10 MB).</div>
          </div>

          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-fm-primary">
              <i class="bi bi-send me-1"></i>Submit Ticket
            </button>
            <a href="<?= base_url('portal/tickets') ?>" class="btn btn-outline-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
