<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-shield-check me-2"></i>SLA Performance Report</h1></div>
  <a href="<?= base_url('reports/export/workorders/csv') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-filetype-csv me-1"></i>Export</a>
</div>
<form method="get" class="fm-card mb-3"><div class="fm-card-body py-2"><div class="row g-2 align-items-end">
  <div class="col-md-2"><label class="form-label small">From</label><input type="date" name="from" class="form-control form-control-sm" value="<?= $from ?>"></div>
  <div class="col-md-2"><label class="form-label small">To</label><input type="date" name="to" class="form-control form-control-sm" value="<?= $to ?>"></div>
  <div class="col-md-4"><label class="form-label small">Facility</label><select name="facility" class="form-select form-select-sm"><option value="">All</option><?php foreach($facilities as $f): ?><option value="<?= $f['id'] ?>" <?= $filterFacility==$f['id']?'selected':'' ?>><?= esc($f['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-auto"><button type="submit" class="btn btn-fm-primary btn-sm">Apply</button></div>
</div></div></form>
<div class="row g-3 mb-3">
  <div class="col-md-3"><div class="kpi-card kpi-primary"><div class="d-flex gap-3 align-items-center"><div class="kpi-icon"><i class="bi bi-list"></i></div><div><div class="kpi-label">Total WOs</div><div class="kpi-value"><?= $total ?></div></div></div></div></div>
  <div class="col-md-3"><div class="kpi-card <?= $compliance>=90?'kpi-green':($compliance>=70?'kpi-orange':'kpi-red') ?>"><div class="d-flex gap-3 align-items-center"><div class="kpi-icon"><i class="bi bi-shield-check"></i></div><div><div class="kpi-label">SLA Compliance</div><div class="kpi-value"><?= $compliance ?>%</div></div></div></div></div>
  <div class="col-md-3"><div class="kpi-card <?= $breached>0?'kpi-red':'kpi-green' ?>"><div class="d-flex gap-3 align-items-center"><div class="kpi-icon"><i class="bi bi-exclamation-triangle"></i></div><div><div class="kpi-label">Breached</div><div class="kpi-value"><?= $breached ?></div></div></div></div></div>
  <div class="col-md-3"><div class="kpi-card kpi-green"><div class="d-flex gap-3 align-items-center"><div class="kpi-icon"><i class="bi bi-check-circle"></i></div><div><div class="kpi-label">Met SLA</div><div class="kpi-value"><?= $total-$breached ?></div></div></div></div></div>
</div>
<div class="row g-3 mb-3">
  <?php foreach(['critical'=>'red','high'=>'orange','medium'=>'blue','low'=>'green'] as $p=>$c): if(!isset($byPriority[$p])) continue; $pr=$byPriority[$p]; $bRate=$pr['total']>0?round($pr['breached']/$pr['total']*100):0; ?>
  <div class="col-md-3">
    <div class="fm-card text-center"><div class="fm-card-body">
      <div class="fw-bold mb-1 text-<?= $c ?>"><?= ucfirst($p) ?></div>
      <div class="kpi-value"><?= $pr['total'] ?></div>
      <div class="small text-danger"><?= $pr['breached'] ?> breached (<?= $bRate ?>%)</div>
      <div class="progress mt-2" style="height:6px"><div class="progress-bar bg-danger" style="width:<?= $bRate ?>%"></div></div>
    </div></div>
  </div>
  <?php endforeach; ?>
</div>
<div class="fm-card"><div class="card-header-fm"><h5>Breached Work Orders</h5></div><div class="fm-card-body p-0">
  <table class="fm-table"><thead><tr><th>WO #</th><th>Title</th><th>Facility</th><th>Priority</th><th>Assigned</th><th>SLA Due</th><th>Status</th></tr></thead><tbody>
  <?php $breachedWOs=array_filter($wos,fn($w)=>$w['sla_breached']==1); foreach($breachedWOs as $w): ?>
  <tr><td class="fw-bold small"><a href="<?= base_url('workorders/view/'.$w['id']) ?>" class="text-primary"><?= esc($w['wo_number']) ?></a></td>
  <td class="small"><?= esc(substr($w['title'],0,30)) ?></td><td class="small text-muted"><?= esc($w['facility_name']??'—') ?></td>
  <td><span class="fm-badge badge-priority-<?= esc($w['priority']) ?>"><?= ucfirst($w['priority']) ?></span></td>
  <td class="small text-muted"><?= esc($w['assigned_name']??'—') ?></td>
  <td class="small text-danger"><?= $w['sla_due']?date('d M Y H:i',strtotime($w['sla_due'])):'—' ?></td>
  <td><span class="fm-badge badge-status-<?= esc($w['status']) ?>"><?= ucfirst(str_replace('_',' ',$w['status'])) ?></span></td>
  </tr>
  <?php endforeach; ?>
  <?php if(empty($breachedWOs)): ?><tr><td colspan="7" class="text-center py-3 text-success fw-semibold">✓ No SLA breaches in this period!</td></tr><?php endif; ?>
  </tbody></table>
</div></div>
<?= $this->endSection() ?>
