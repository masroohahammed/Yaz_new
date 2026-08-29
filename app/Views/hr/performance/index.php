<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= $this->include('hr/_subnav', ['hrActive' => 'performance']) ?>

<div class="page-header d-flex justify-content-between flex-wrap gap-2 mb-3">
  <div><h1 class="h4 mb-0"><i class="bi bi-graph-up-arrow me-2"></i>Performance</h1></div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('hr/performance/review/create') ?>" class="btn btn-fm-outline btn-sm">New review</a>
    <a href="<?= base_url('hr/performance/goal/create') ?>" class="btn btn-fm-primary btn-sm">New goal</a>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-6">
    <div class="hr-page-card">
      <h6 class="text-muted text-uppercase small mb-3">Reviews</h6>
      <table class="fm-table table-sm mb-0">
        <thead><tr><th>Employee</th><th>Period</th><th>Rating</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($reviews as $r): ?>
        <tr>
          <td><?= esc($r['employee_name']) ?></td>
          <td class="small"><?= esc($r['period_label']) ?></td>
          <td><?= esc($r['rating'] ?? '—') ?></td>
          <td><?= ucfirst($r['status']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($reviews)): ?><tr><td colspan="4" class="text-muted text-center py-3">No reviews yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="col-lg-6">
    <div class="hr-page-card">
      <h6 class="text-muted text-uppercase small mb-3">Goals & KPIs</h6>
      <table class="fm-table table-sm mb-0">
        <thead><tr><th>Employee</th><th>Goal</th><th>Due</th><th>Progress</th></tr></thead>
        <tbody>
        <?php foreach ($goals as $g): ?>
        <tr>
          <td><?= esc($g['employee_name']) ?></td>
          <td class="small"><?= esc($g['title']) ?></td>
          <td class="small"><?= esc($g['due_date'] ?? '—') ?></td>
          <td><?= (int) $g['progress_pct'] ?>%</td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($goals)): ?><tr><td colspan="4" class="text-muted text-center py-3">No goals yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
