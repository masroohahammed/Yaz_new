<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-clock-history me-2"></i>Asset History — <?= esc($asset['asset_code']) ?></h1></div><a href="<?= base_url('asset-register/view/'.$asset['id']) ?>" class="btn btn-fm-outline btn-sm">← Back</a></div>
<div class="row g-3">
<div class="col-md-4">
  <div class="fm-card"><div class="fm-card-body">
    <h6 class="fw-semibold mb-3" style="color:var(--fm-navy)">Depreciation</h6>
    <?php $rows=[['Purchase Cost',$asset['purchase_cost']],['Years Owned',$yearsOwned.' yrs'],['Annual Depreciation',$depreciationPerYear],['Current Book Value',$currentValue]]; ?>
    <?php foreach($rows as [$l,$v]): ?>
    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted small"><?= $l ?></span><span class="small fw-semibold"><?= is_float($v)||is_int($v)?$currency.' '.number_format($v,2):esc($v) ?></span></div>
    <?php endforeach; ?>
  </div></div>
</div>
<div class="col-md-8">
  <div class="fm-card"><div class="card-header-fm"><h5>Maintenance History</h5></div>
  <div class="fm-card-body p-0"><div class="table-responsive"><table class="fm-table"><thead><tr><th>WO Number</th><th>Title</th><th>Type</th><th>Status</th><th>Technician</th><th>Date</th></tr></thead><tbody>
  <?php foreach($history as $h): ?>
  <tr>
    <td class="fw-semibold small"><a href="<?= base_url('workorders/view/'.$h['id']) ?>" class="text-primary"><?= esc($h['wo_number']) ?></a></td>
    <td class="small"><?= esc($h['title']) ?></td>
    <td class="small"><?= ucfirst($h['type']) ?></td>
    <td><span class="fm-badge badge-status-<?= $h['status'] ?>"><?= ucfirst(str_replace('_',' ',$h['status'])) ?></span></td>
    <td class="small"><?= esc($h['technician_name']??'Unassigned') ?></td>
    <td class="small"><?= date('d M Y',strtotime($h['created_at'])) ?></td>
  </tr>
  <?php endforeach; ?>
  <?php if(empty($history)): ?><tr><td colspan="6" class="text-center py-4 text-muted">No maintenance history</td></tr><?php endif; ?>
  </tbody></table></div></div></div>
</div>
</div>
<?= $this->endSection() ?>
