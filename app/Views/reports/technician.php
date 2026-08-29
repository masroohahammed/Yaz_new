<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-person-gear me-2"></i>Technician Performance</h1></div>
  <a href="<?= base_url('reports/export/technician/csv') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-filetype-csv me-1"></i>Export CSV</a>
</div>
<form method="get" class="fm-card mb-3"><div class="fm-card-body py-2"><div class="row g-2 align-items-end">
  <div class="col-md-3"><label class="form-label small">From</label><input type="date" name="from" class="form-control form-control-sm" value="<?= $from ?>"></div>
  <div class="col-md-3"><label class="form-label small">To</label><input type="date" name="to" class="form-control form-control-sm" value="<?= $to ?>"></div>
  <div class="col-auto"><button type="submit" class="btn btn-fm-primary btn-sm">Apply</button></div>
</div></div></form>
<div class="fm-card"><div class="fm-card-body p-0">
  <table class="fm-table">
    <thead><tr><th>Technician</th><th>Assigned</th><th>Completed</th><th>Completion %</th><th>Avg Resolution</th><th>SLA Breached</th><th>Labor Cost</th><th>Rating</th></tr></thead>
    <tbody>
    <?php foreach($techs as $t):
      $compPct = $t['completion_rate']??0;
      $rating  = $compPct>=90?'Excellent':($compPct>=75?'Good':($compPct>=50?'Average':'Needs Improvement'));
      $ratingClass = $compPct>=90?'success':($compPct>=75?'primary':($compPct>=50?'warning':'danger'));
    ?>
    <tr>
      <td><div class="small fw-bold"><?= esc($t['name']) ?></div><div class="x-small text-muted"><?= esc($t['email']??'') ?></div></td>
      <td class="small text-center"><?= $t['total_assigned']??0 ?></td>
      <td class="small text-center fw-bold text-success"><?= $t['completed']??0 ?></td>
      <td>
        <div class="d-flex align-items-center gap-2">
          <div class="progress flex-grow-1" style="height:8px;border-radius:4px"><div class="progress-bar bg-<?= $ratingClass ?>" style="width:<?= min(100,$compPct) ?>%"></div></div>
          <span class="small fw-bold"><?= $compPct ?>%</span>
        </div>
      </td>
      <td class="small text-center"><?= $t['avg_resolution_hours']??'—' ?>h</td>
      <td class="small text-center <?= ($t['sla_breached']??0)>0?'text-danger fw-bold':'' ?>"><?= $t['sla_breached']??0 ?></td>
      <td class="small"><?= $currency ?> <?= number_format($t['total_labor_cost']??0,2) ?></td>
      <td><span class="fm-badge badge-status-<?= $compPct>=75?'active':'pending' ?>"><?= $rating ?></span></td>
    </tr>
    <?php endforeach; ?>
    <?php if(empty($techs)): ?><tr><td colspan="8" class="text-center py-3 text-muted">No technician data found.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div></div>
<?= $this->endSection() ?>
