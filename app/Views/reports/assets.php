<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-cpu me-2"></i>Asset Report</h1></div>
  <a href="<?= base_url('reports/export/assets/csv') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-filetype-csv me-1"></i>Export</a>
</div>
<form method="get" class="fm-card mb-3"><div class="fm-card-body py-2"><div class="row g-2 align-items-end">
  <div class="col-md-4"><label class="form-label small">Facility</label><select name="facility" class="form-select form-select-sm"><option value="">All</option><?php foreach($facilities as $f): ?><option value="<?= $f['id'] ?>" <?= $filterFacility==$f['id']?'selected':'' ?>><?= esc($f['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-3"><label class="form-label small">Status</label><select name="status" class="form-select form-select-sm"><option value="">All</option><option value="active" <?= $filterStatus==='active'?'selected':'' ?>>Active</option><option value="inactive" <?= $filterStatus==='inactive'?'selected':'' ?>>Inactive</option><option value="maintenance" <?= $filterStatus==='maintenance'?'selected':'' ?>>Maintenance</option></select></div>
  <div class="col-auto"><button type="submit" class="btn btn-fm-primary btn-sm">Filter</button></div>
</div></div></form>
<div class="fm-card"><div class="fm-card-body p-0">
  <table class="fm-table">
    <thead><tr><th>Code</th><th>Name</th><th>Category</th><th>Facility</th><th>Health</th><th>Status</th><th>Warranty</th><th>Next Maint.</th></tr></thead>
    <tbody>
    <?php foreach($assets as $a): $h=(int)($a['health_score']??100); ?>
    <tr>
      <td class="small fw-semibold text-muted"><?= esc($a['asset_code']) ?></td>
      <td class="small"><a href="<?= base_url('asset-register/view/'.$a['id']) ?>" class="text-primary"><?= esc($a['name']) ?></a></td>
      <td class="small"><?= ucfirst(str_replace('_',' ',$a['category']??'')) ?></td>
      <td class="small text-muted"><?= esc($a['facility_name']??'—') ?></td>
      <td>
        <div class="d-flex align-items-center gap-2">
          <div class="progress" style="width:50px;height:6px"><div class="progress-bar <?= $h>=80?'bg-success':($h>=50?'bg-warning':'bg-danger') ?>" style="width:<?= $h ?>%"></div></div>
          <span class="small"><?= $h ?>%</span>
        </div>
      </td>
      <td><span class="fm-badge badge-status-<?= esc($a['status']) ?>"><?= ucfirst($a['status']) ?></span></td>
      <td class="small <?= !empty($a['warranty_expiry'])&&strtotime($a['warranty_expiry'])<time()?'text-danger':'' ?>"><?= !empty($a['warranty_expiry'])?date('d M Y',strtotime($a['warranty_expiry'])):'—' ?></td>
      <td class="small <?= !empty($a['next_maintenance'])&&strtotime($a['next_maintenance'])<time()?'text-warning fw-bold':'' ?>"><?= !empty($a['next_maintenance'])?date('d M Y',strtotime($a['next_maintenance'])):'—' ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if(empty($assets)): ?><tr><td colspan="8" class="text-center py-3 text-muted">No assets found.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div></div>
<?= $this->endSection() ?>
