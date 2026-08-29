<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= $this->include('hr/_subnav', ['hrActive' => 'performance']) ?>
<div class="page-header mb-3"><h1 class="h4">Goal / KPI</h1></div>
<div class="hr-page-card">
  <?= form_open(base_url('hr/performance/goal/store')) ?>
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-md-6"><label class="form-label">Employee</label><select name="user_id" class="form-select" required><?php foreach ($employees as $e): ?><option value="<?= $e['user_id'] ?>"><?= esc($e['name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-6"><label class="form-label">Due date</label><input type="date" name="due_date" class="form-control"></div>
    <div class="col-12"><label class="form-label">Goal title</label><input name="title" class="form-control" required></div>
    <div class="col-12"><label class="form-label">KPI target</label><input name="kpi_target" class="form-control" placeholder="e.g. 95% occupancy"></div>
    <div class="col-md-3"><label class="form-label">Progress %</label><input type="number" name="progress_pct" class="form-control" min="0" max="100" value="0"></div>
    <div class="col-12"><button class="btn btn-fm-primary">Create goal</button></div>
  </div>
  <?= form_close() ?>
</div>
<?= $this->endSection() ?>
