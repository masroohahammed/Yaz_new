<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="page-header">
  <h1>
    <i class="bi <?= esc($icon) ?> me-2 text-primary"></i>
    <?= $isEdit ? 'Edit' : 'Add' ?> <?= esc($title) ?>
  </h1>
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
      <li class="breadcrumb-item"><a href="<?= base_url($slug) ?>"><?= esc($title) ?></a></li>
      <li class="breadcrumb-item active"><?= $isEdit ? 'Edit' : 'Create' ?></li>
    </ol>
  </nav>
</div>

<?= form_open($isEdit ? base_url($slug . '/update/' . $row['id']) : base_url($slug . '/store')) ?>

<?php foreach ($sections as $section): ?>
<div class="fm-card mb-4 p-4">
  <h6 class="fm-form-section mb-3"><i class="bi bi-pencil-square me-2"></i><?= esc($section['label']) ?></h6>
  <div class="row g-3">
    <?php foreach ($section['fields'] as $field):
      $name  = $field['name'];
      $type  = $field['type'] ?? 'text';
      $value = old($name, $row[$name] ?? '');
      $col   = ($type === 'textarea') ? 'col-12' : 'col-md-6';
    ?>
    <div class="<?= $col ?>">
      <label class="form-label small fw-medium" for="f-<?= esc($name) ?>">
        <?= esc($field['label']) ?><?= ! empty($field['required']) ? ' *' : '' ?>
      </label>

      <?php if ($type === 'textarea'): ?>
        <textarea name="<?= esc($name) ?>" id="f-<?= esc($name) ?>" rows="3"
                  class="form-control form-control-sm"><?= esc($value) ?></textarea>

      <?php elseif ($type === 'select'): ?>
        <select name="<?= esc($name) ?>" id="f-<?= esc($name) ?>" class="form-select form-select-sm">
          <option value="">— Select —</option>
          <?php foreach ($field['options'] ?? [] as $optVal => $optLabel): ?>
            <option value="<?= esc($optVal) ?>" <?= (string) $value === (string) $optVal ? 'selected' : '' ?>>
              <?= esc($optLabel) ?>
            </option>
          <?php endforeach; ?>
        </select>

      <?php elseif ($type === 'checkbox'): ?>
        <div class="form-check mt-1">
          <input type="checkbox" name="<?= esc($name) ?>" id="f-<?= esc($name) ?>" value="1"
                 class="form-check-input" <?= ! empty($value) ? 'checked' : '' ?>>
          <label class="form-check-label small" for="f-<?= esc($name) ?>">Yes</label>
        </div>

      <?php elseif ($type === 'fk' || $type === 'fk_user'): ?>
        <select name="<?= esc($name) ?>" id="f-<?= esc($name) ?>"
                class="form-select form-select-sm pm-fk-select"
                data-field="<?= esc($name) ?>"
                <?= ($field['table'] ?? '') === 'units' ? 'data-unit-select' : '' ?>
                <?= ($field['table'] ?? '') === 'facilities' ? 'data-facility-select' : '' ?>>
          <option value="">— Select —</option>
          <?php foreach ($fkData[$name] ?? [] as $optId => $optLabel): ?>
            <option value="<?= esc($optId) ?>" <?= (string) $value === (string) $optId ? 'selected' : '' ?>>
              <?= esc($optLabel) ?>
            </option>
          <?php endforeach; ?>
        </select>

      <?php else: ?>
        <input type="<?= esc($type === 'number' ? 'number' : ($type === 'date' ? 'date' : 'text')) ?>"
               name="<?= esc($name) ?>" id="f-<?= esc($name) ?>"
               value="<?= esc($value) ?>" class="form-control form-control-sm"
               <?= ! empty($field['required']) && ! $isEdit ? 'required' : '' ?>>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endforeach; ?>

<div class="d-flex gap-2">
  <button type="submit" class="btn btn-fm-primary">
    <i class="bi bi-check-lg me-1"></i><?= $isEdit ? 'Save Changes' : 'Create' ?>
  </button>
  <a href="<?= base_url($slug) ?>" class="btn btn-outline-secondary">Cancel</a>
</div>

<?= form_close() ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const facilitySelect = document.querySelector('[data-facility-select]');
  const unitSelect = document.querySelector('[data-unit-select]');
  if (!facilitySelect || !unitSelect) return;

  facilitySelect.addEventListener('change', function () {
    const fid = this.value;
    unitSelect.innerHTML = '<option value="">— Select —</option>';
    if (!fid) return;
    fetch('<?= base_url('pm/ajax/units') ?>/' + fid, { headers: { 'Accept': 'application/json' } })
      .then(r => r.json())
      .then(rows => {
        rows.forEach(u => {
          const o = document.createElement('option');
          o.value = u.id;
          o.textContent = u.unit_number || u.id;
          unitSelect.appendChild(o);
        });
      });
  });
});
</script>
<?= $this->endSection() ?>
