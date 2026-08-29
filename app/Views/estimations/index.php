<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-calculator me-2"></i>Estimations</h1></div>
  <a href="<?= base_url('estimations/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>New Estimation</a>
</div>
<!-- KPIs -->
<div class="row g-3 mb-3">
  <?php foreach(['draft'=>['kpi-secondary','bi-file-earmark','Draft'],'pending_approval'=>['kpi-orange','bi-hourglass','Pending'],'approved'=>['kpi-green','bi-check-circle','Approved'],'converted'=>['kpi-blue','bi-arrow-right-circle','Converted']] as $s=>[$c,$i,$l]): ?>
  <div class="col-6 col-md-3"><div class="kpi-card <?= $c ?>"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi <?= $i ?>"></i></div><div><div class="kpi-label"><?= $l ?></div><div class="kpi-value"><?= $kpi[$s]??0 ?></div></div></div></div></div>
  <?php endforeach; ?>
</div>
<!-- Filter -->
<div class="fm-card mb-3"><div class="fm-card-body py-2 d-flex gap-2 flex-wrap">
  <span class="small text-muted align-self-center fw-semibold">Status:</span>
  <?php foreach([''=> 'All','draft'=>'Draft','pending_approval'=>'Pending','approved'=>'Approved','rejected'=>'Rejected','converted'=>'Converted'] as $v=>$l): ?>
  <a href="?status=<?= $v ?>" class="btn btn-sm <?= $filterStatus===$v?'btn-fm-primary':'btn-outline-secondary' ?>"><?= $l ?></a>
  <?php endforeach; ?>
</div></div>
<!-- Table -->
<div class="fm-card"><div class="fm-card-body p-0">
  <?php if(empty($ests)): ?>
  <p class="text-center text-muted py-5">No estimations yet. <a href="<?= base_url('estimations/create') ?>">Create one</a>.</p>
  <?php else: ?>
  <table class="fm-table">
    <thead><tr><th>EST #</th><th>Title</th><th>Facility</th><th>Labor</th><th>Materials</th><th>Total</th><th>Rev</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach($ests as $e): ?>
    <tr>
      <td><a href="<?= base_url('estimations/view/'.$e['id']) ?>" class="fw-bold text-primary"><?= esc($e['est_number']) ?></a></td>
      <td class="small"><?= esc(substr($e['title'],0,40)) ?></td>
      <td class="small text-muted"><?= esc($e['facility_name']??'—') ?></td>
      <td class="small"><?= $currency ?> <?= number_format($e['labor_cost'],0) ?></td>
      <td class="small"><?= $currency ?> <?= number_format($e['material_cost'],0) ?></td>
      <td class="fw-bold"><?= $currency ?> <?= number_format($e['total'],2) ?></td>
      <td class="small text-center text-muted">v<?= $e['revision'] ?></td>
      <td><span class="fm-badge badge-status-<?= esc($e['status']) ?>"><?= ucfirst(str_replace('_',' ',$e['status'])) ?></span></td>
      <td>
        <a href="<?= base_url('estimations/view/'.$e['id']) ?>" class="btn-action bg-primary text-white me-1"><i class="bi bi-eye"></i></a>
        <a href="<?= base_url('estimations/print/'.$e['id']) ?>" class="btn-action bg-secondary text-white" target="_blank"><i class="bi bi-printer"></i></a>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div></div>
<?= $this->endSection() ?>
