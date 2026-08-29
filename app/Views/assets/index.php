<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $ast = $assetStats ?? []; ?>
<div class="page-header">
  <div><h1><i class="bi bi-cpu me-2 text-primary"></i>Asset Management</h1>
    <?php if (!empty($ast)): ?><p class="text-muted small mb-0"><?= (int)$ast['total_assets'] ?> assets · <?= (int)$ast['assets_with_qr'] ?> with QR · <?= (int)$ast['scans_today'] ?> scans today</p><?php endif; ?>
  </div>
  <div class="d-flex gap-2">
    <button type="button" class="btn btn-fm-outline btn-sm" id="bulkPrintBtn" disabled><i class="bi bi-printer me-1"></i>Print Labels</button>
    <a href="<?= base_url('asset-register/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Add Asset</a>
  </div>
</div>
<?php if (!empty($ast)): ?>
<div class="row g-2 mb-3">
  <div class="col-6 col-md-2"><div class="kpi-card kpi-blue py-2 px-3"><div class="kpi-label small">Total</div><div class="kpi-value"><?= (int)$ast['total_assets'] ?></div></div></div>
  <div class="col-6 col-md-2"><div class="kpi-card kpi-green py-2 px-3"><div class="kpi-label small">Active</div><div class="kpi-value"><?= (int)$ast['active_assets'] ?></div></div></div>
  <div class="col-6 col-md-2"><div class="kpi-card kpi-red py-2 px-3"><div class="kpi-label small">Faulty</div><div class="kpi-value"><?= (int)$ast['faulty_assets'] ?></div></div></div>
  <div class="col-6 col-md-2"><div class="kpi-card kpi-orange py-2 px-3"><div class="kpi-label small">Open WO</div><div class="kpi-value"><?= (int)$ast['assets_open_wo'] ?></div></div></div>
  <div class="col-6 col-md-2"><div class="kpi-card kpi-teal py-2 px-3"><div class="kpi-label small">No QR</div><div class="kpi-value"><?= (int)$ast['assets_without_qr'] ?></div></div></div>
  <div class="col-6 col-md-2"><div class="kpi-card kpi-purple py-2 px-3"><div class="kpi-label small">Warranty soon</div><div class="kpi-value"><?= (int)$ast['warranty_expiring'] ?></div></div></div>
</div>
<?php endif; ?>
<div class="fm-card mb-3">
  <?= form_open('assets',['method'=>'get','class'=>'fm-card-body']) ?>
  <div class="row g-2 align-items-end">
    <div class="col-md-4"><input type="text" name="search" class="form-control form-control-sm" placeholder="Search name or code..." value="<?= esc($filters['search']) ?>"></div>
    <div class="col-md-2"><select name="category" class="form-select form-select-sm"><option value="">All Categories</option><?php foreach(['hvac','elevator','electrical','plumbing','fire_safety','security','it','civil','other'] as $c): ?><option <?= $filters['category']==$c?'selected':'' ?> value="<?= $c ?>"><?= ucfirst(str_replace('_',' ',$c)) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><select name="status" class="form-select form-select-sm"><option value="">All Statuses</option><?php foreach(['active','under_maintenance','retired','disposed'] as $s): ?><option <?= $filters['status']==$s?'selected':'' ?> value="<?= $s ?>"><?= ucfirst(str_replace('_',' ',$s)) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><select name="facility" class="form-select form-select-sm"><option value="">All Facilities</option><?php foreach($facilities as $f): ?><option <?= $filters['facility']==$f['id']?'selected':'' ?> value="<?= $f['id'] ?>"><?= esc($f['name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><button type="submit" class="btn btn-fm-primary btn-sm w-100"><i class="bi bi-search me-1"></i>Filter</button></div>
  </div>
  <?= form_close() ?>
</div>
<div class="fm-card"><div class="fm-card-body p-0"><div class="table-responsive">
<table class="fm-table"><thead><tr><th><input type="checkbox" id="selectAllAssets" title="Select all"></th><th>Code</th><th>Name</th><th>Category</th><th>Facility</th><th>Brand/Model</th><th>Status</th><th>Health</th><th>Next PM</th><th>Warranty</th><th>Actions</th></tr></thead><tbody>
<?php foreach($assets as $a): ?><tr>
  <td><input type="checkbox" class="asset-select" value="<?= (int)$a['id'] ?>"></td>
  <td class="fw-semibold"><a href="<?= base_url('asset-register/view/'.$a['id']) ?>" class="text-primary"><?= esc($a['asset_code']) ?></a></td>
  <td class="small"><?= esc($a['name']) ?></td>
  <td><span class="fm-badge badge-status-active" style="font-size:.65rem"><?= ucfirst(str_replace('_',' ',$a['category'])) ?></span></td>
  <td class="small text-muted"><?= esc($a['facility_name']) ?></td>
  <td class="small text-muted"><?= esc(($a['brand']??'').' '.($a['model']??'')) ?></td>
  <td><span class="fm-badge badge-status-<?= esc($a['status']) ?>"><?= ucfirst(str_replace('_',' ',$a['status'])) ?></span></td>
  <td style="min-width:100px"><?php $h=(int)$a['health_score']; ?><div class="d-flex align-items-center gap-1"><div class="health-bar flex-grow-1"><div class="health-bar-fill <?= $h>=80?'good':($h>=50?'warn':'bad') ?>" style="width:<?= $h ?>%"></div></div><span class="x-small"><?= $h ?>%</span></div></td>
  <td class="small <?= (!empty($a['next_maintenance']) && strtotime($a['next_maintenance']) < time())?'text-danger fw-bold':'' ?>"><?= !empty($a['next_maintenance']) ? date('d M Y', strtotime($a['next_maintenance'])) : '—' ?></td>
  <td class="small <?= ($a['warranty_expiry']&&strtotime($a['warranty_expiry'])<time())?'text-danger':'' ?>"><?= $a['warranty_expiry']?date('d M Y',strtotime($a['warranty_expiry'])):'—' ?></td>
  <td><div class="d-flex gap-1">
    <a href="<?= base_url('asset-register/view/'.$a['id']) ?>" class="btn-action bg-primary bg-opacity-10 text-primary"><i class="bi bi-eye"></i></a>
    <a href="<?= base_url('asset-register/qrcode/'.$a['id']) ?>" class="btn-action bg-info bg-opacity-10 text-info" title="QR"><i class="bi bi-qr-code"></i></a>
    <a href="<?= base_url('asset-register/edit/'.$a['id']) ?>" class="btn-action bg-warning bg-opacity-10 text-warning"><i class="bi bi-pencil"></i></a>
  </div></td>
</tr><?php endforeach; ?>
<?php if(empty($assets)): ?><tr><td colspan="11" class="text-center py-5 text-muted"><i class="bi bi-cpu d-block mb-2 fs-2"></i>No assets found</td></tr><?php endif; ?>
</tbody></table></div></div></div>
<?= $this->section('scripts') ?>
<script>
document.getElementById('selectAllAssets')?.addEventListener('change', function () {
  document.querySelectorAll('.asset-select').forEach(cb => { cb.checked = this.checked; });
  updateBulkPrint();
});
document.querySelectorAll('.asset-select').forEach(cb => cb.addEventListener('change', updateBulkPrint));
function updateBulkPrint() {
  const ids = [...document.querySelectorAll('.asset-select:checked')].map(cb => cb.value);
  const btn = document.getElementById('bulkPrintBtn');
  if (!btn) return;
  btn.disabled = ids.length === 0;
  btn.onclick = () => { window.open('<?= base_url('asset-register/print-labels') ?>?ids=' + ids.join(','), '_blank'); };
}
</script>
<?= $this->endSection() ?>
<?= $this->endSection() ?>
