<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $isEdit = ! empty($row['id']); ?>
<div class="page-header"><h1><?= esc($title) ?></h1></div>
<?= form_open($action, ['class' => 'fm-submit-form']) ?>
<?= csrf_field() ?>
<?php foreach (\Config\PmFormFields::sections($slug) as $section): ?>
<div class="fm-form-section mb-3">
  <h6><?= esc($section['label']) ?></h6>
  <div class="row g-2">
    <?php foreach ($section['fields'] as $field):
      $name = $field['name'];
      $val  = old($name, $row[$name] ?? '');
      $type = $field['type'] ?? 'text';
    ?>
    <div class="col-md-6">
      <label class="form-label"><?= esc($field['label']) ?><?= ! empty($field['required']) ? ' *' : '' ?></label>
      <?php if (in_array($type, ['fk', 'fk_user'], true)): ?>
        <select name="<?= esc($name) ?>" class="form-select" <?= ! empty($field['required']) ? 'required' : '' ?>>
          <option value="">—</option>
          <?php foreach ($options[$name] ?? [] as $optId => $label): ?>
          <option value="<?= esc($optId) ?>" <?= (string) $val === (string) $optId ? 'selected' : '' ?>><?= esc($label) ?></option>
          <?php endforeach; ?>
        </select>
      <?php elseif ($type === 'select'): ?>
        <select name="<?= esc($name) ?>" class="form-select">
          <?php foreach ($field['options'] ?? [] as $optVal => $label): ?>
          <option value="<?= esc($optVal) ?>" <?= (string) $val === (string) $optVal ? 'selected' : '' ?>><?= esc($label) ?></option>
          <?php endforeach; ?>
        </select>
      <?php elseif ($type === 'textarea'): ?>
        <textarea name="<?= esc($name) ?>" class="form-control" rows="3"><?= esc($val) ?></textarea>
      <?php elseif ($type === 'checkbox'): ?>
        <div class="form-check"><input type="checkbox" name="<?= esc($name) ?>" value="1" class="form-check-input" <?= $val ? 'checked' : '' ?>></div>
      <?php else: ?>
        <input type="<?= $type === 'number' ? 'number' : ($type === 'date' ? 'date' : 'text') ?>" name="<?= esc($name) ?>" class="form-control" value="<?= esc($val) ?>" <?= $type === 'number' ? 'step="0.01"' : '' ?>>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endforeach; ?>
<button type="submit" class="btn btn-fm-primary fm-submit-btn"><?= $isEdit ? 'Update' : 'Save' ?></button>
<a href="<?= base_url('pm/' . $slug) ?>" class="btn btn-fm-outline">Cancel</a>
<?= form_close() ?>
<?= $this->endSection() ?>
