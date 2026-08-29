<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= $this->include('hr/_subnav', ['hrActive' => 'performance']) ?>
<div class="page-header mb-3"><h1 class="h4">Performance Review</h1></div>
<div class="hr-page-card">
  <?= form_open(base_url('hr/performance/review/store')) ?>
  <?= csrf_field() ?>
  <div class="row g-3">
    <div class="col-md-6"><label class="form-label">Employee</label><select name="user_id" class="form-select" required><?php foreach ($employees as $e): ?><option value="<?= $e['user_id'] ?>"><?= esc($e['name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-6"><label class="form-label">Period</label><input name="period_label" class="form-control" placeholder="Q1 2026" required></div>
    <div class="col-md-3"><label class="form-label">Rating (1-5)</label><input type="number" step="0.1" min="1" max="5" name="rating" class="form-control"></div>
    <div class="col-md-3"><label class="form-label">Review date</label><input type="date" name="review_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
    <div class="col-12"><label class="form-label">Strengths</label><textarea name="strengths" class="form-control" rows="2"></textarea></div>
    <div class="col-12"><label class="form-label">Improvements</label><textarea name="improvements" class="form-control" rows="2"></textarea></div>
    <div class="col-12"><label class="form-label">Comments</label><textarea name="comments" class="form-control" rows="2"></textarea></div>
    <div class="col-12"><button class="btn btn-fm-primary">Save review</button></div>
  </div>
  <?= form_close() ?>
</div>
<?= $this->endSection() ?>
