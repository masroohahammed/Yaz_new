<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $i = $inspection; $defaultAreas = ['Kitchen','Bathroom','Living Room','Bedroom']; ?>
<div class="page-header"><h1>Checklist — Unit <?= esc($i['unit_number']) ?></h1></div>
<?= form_open(base_url('pm-inspections/checklist/'.$i['id'])) ?>
<?= csrf_field() ?>
<?php foreach ($defaultAreas as $idx => $area): ?>
<div class="fm-form-section mb-2">
  <h6><?= esc($area) ?></h6>
  <input type="hidden" name="areas[]" value="<?= esc($area) ?>">
  <div class="row g-2">
    <div class="col-md-4"><select name="condition_rating[]" class="form-select form-select-sm">
      <?php foreach (['excellent','good','fair','poor','damaged'] as $c): ?><option value="<?= $c ?>"><?= ucfirst($c) ?></option><?php endforeach; ?>
    </select></div>
    <div class="col-md-8"><input type="text" name="item_notes[]" class="form-control form-control-sm" placeholder="Notes"></div>
  </div>
</div>
<?php endforeach; ?>
<div class="row g-2 mt-2">
  <div class="col-md-4"><label class="form-label small">Overall condition</label>
    <select name="overall_condition" class="form-select form-select-sm">
      <?php foreach (['excellent','good','fair','poor'] as $c): ?><option value="<?= $c ?>"><?= ucfirst($c) ?></option><?php endforeach; ?>
    </select></div>
  <div class="col-12"><textarea name="overall_notes" class="form-control form-control-sm" rows="2" placeholder="Overall notes"></textarea></div>
  <div class="col-12">
    <button type="submit" name="submit_action" value="save" class="btn btn-sm btn-secondary">Save</button>
    <button type="submit" name="submit_action" value="complete" class="btn btn-sm btn-fm-primary">Save Checklist</button>
  </div>
</div>
<?= form_close() ?>
<?= $this->endSection() ?>
