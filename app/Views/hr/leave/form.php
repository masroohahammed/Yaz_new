<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= $this->include('hr/_subnav', ['hrActive' => 'leave']) ?>

<div class="page-header mb-3"><h1 class="h4">Request Leave</h1></div>

<div class="hr-page-card">
  <?= form_open(base_url('hr/leave/store')) ?>
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Employee</label>
      <select name="user_id" class="form-select" required>
        <?php foreach ($employees as $e): ?>
        <option value="<?= $e['user_id'] ?>"><?= esc($e['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Leave type</label>
      <select name="leave_type_id" class="form-select" required>
        <?php foreach ($types as $t): ?>
        <option value="<?= $t['id'] ?>"><?= esc($t['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3"><label class="form-label">Start</label><input type="date" name="start_date" class="form-control" required></div>
    <div class="col-md-3"><label class="form-label">End</label><input type="date" name="end_date" class="form-control" required></div>
    <div class="col-12"><label class="form-label">Reason</label><textarea name="reason" class="form-control" rows="3"></textarea></div>
    <div class="col-12"><button class="btn btn-fm-primary">Submit request</button> <a href="<?= base_url('hr/leave') ?>" class="btn btn-fm-outline">Cancel</a></div>
  </div>
  <?= form_close() ?>
</div>
<?= $this->endSection() ?>
