<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= $this->include('hr/_subnav', ['hrActive' => 'assets']) ?>
<div class="page-header mb-3"><h1 class="h4">Assign Asset</h1></div>
<div class="hr-page-card">
  <?= form_open(base_url('hr/assets/store')) ?>
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-md-6"><label class="form-label">Employee</label><select name="user_id" class="form-select" required><?php foreach ($employees as $e): ?><option value="<?= $e['user_id'] ?>"><?= esc($e['name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-6"><label class="form-label">Type</label><select name="asset_type" class="form-select"><?php foreach (['laptop','mobile','sim','access_card','uniform','keys','equipment','other'] as $t): ?><option value="<?= $t ?>"><?= ucfirst(str_replace('_',' ',$t)) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-6"><label class="form-label">Description</label><input name="description" class="form-control" required></div>
    <div class="col-md-3"><label class="form-label">Asset tag</label><input name="asset_tag" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">Serial</label><input name="serial_number" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">Assigned date</label><input type="date" name="assigned_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
    <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
    <div class="col-12"><button class="btn btn-fm-primary">Assign</button></div>
  </div>
  <?= form_close() ?>
</div>
<?= $this->endSection() ?>
