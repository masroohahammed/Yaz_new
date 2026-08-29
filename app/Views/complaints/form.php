<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <h1><?= empty($row['id']) ? 'New Complaint' : 'Edit Complaint' ?></h1>
</div>

<?= form_open(empty($row['id']) ? base_url('complaints/store') : base_url('complaints/update/' . $row['id'])) ?>
<div class="fm-card p-4">
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Property *</label>
      <select name="facility_id" class="form-select form-select-sm" required>
        <option value="">— Select —</option>
        <?php foreach ($facilities as $f): ?>
          <option value="<?= $f['id'] ?>" <?= (old('facility_id', $row['facility_id'] ?? '') == $f['id']) ? 'selected' : '' ?>>
            <?= esc($f['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Priority *</label>
      <select name="priority" class="form-select form-select-sm" required>
        <?php foreach (['low', 'medium', 'high', 'critical'] as $p): ?>
          <option value="<?= $p ?>" <?= old('priority', $row['priority'] ?? 'medium') === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php if (! empty($row['id'])): ?>
    <div class="col-md-6">
      <label class="form-label">Status *</label>
      <select name="status" class="form-select form-select-sm" required>
        <?php foreach (['pending', 'reviewed', 'converted', 'rejected'] as $s): ?>
          <option value="<?= $s ?>" <?= old('status', $row['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>
    <div class="col-12">
      <label class="form-label">Subject / Category *</label>
      <input type="text" name="category" class="form-control form-control-sm" required
             value="<?= esc(old('category', $row['category'] ?? '')) ?>">
    </div>
    <div class="col-12">
      <label class="form-label">Description *</label>
      <textarea name="description" rows="4" class="form-control form-control-sm" required><?= esc(old('description', $row['description'] ?? '')) ?></textarea>
    </div>
  </div>
</div>
<div class="mt-3">
  <button type="submit" class="btn btn-fm-primary">Save</button>
  <a href="<?= base_url('complaints') ?>" class="btn btn-outline-secondary">Cancel</a>
</div>
<?= form_close() ?>

<?= $this->endSection() ?>
