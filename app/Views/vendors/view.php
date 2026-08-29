<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-building me-2"></i><?= esc($vendor['name']) ?></h1></div><a href="<?= base_url('vendors') ?>" class="btn btn-fm-outline btn-sm">← Back</a></div>
<div class="row g-3">
<div class="col-md-4">
  <div class="fm-card"><div class="fm-card-body">
    <h6 class="fw-semibold mb-3" style="color:#0a3d6b">Vendor Details</h6>
    <?php $rows=[['Category',$vendor['category'],'tag'],['Contact',$vendor['contact']??'—','person'],['Phone',$vendor['phone']??'—','telephone'],['Email',$vendor['email']??'—','envelope'],['Status',ucfirst($vendor['status']),'circle'],['Address',$vendor['address']??'—','geo']]; foreach($rows as [$l,$v,$i]): ?>
    <div class="d-flex justify-content-between py-2 border-bottom border-light"><span class="text-muted small"><i class="bi bi-<?= $i ?> me-2"></i><?= $l ?></span><span class="small fw-semibold"><?= esc($v) ?></span></div>
    <?php endforeach; ?>
    <div class="mt-3"><?php for($i=1;$i<=5;$i++): ?><i class="bi bi-star<?= $i<=$vendor['rating']?'-fill text-warning':' text-muted' ?>"></i><?php endfor; ?></div>
  </div></div>
</div>
<div class="col-md-8">
  <div class="fm-card"><div class="card-header-fm"><h5>Related Work Orders</h5></div>
  <div class="fm-card-body p-0"><div class="table-responsive"><table class="fm-table"><thead><tr><th>WO #</th><th>Title</th><th>Status</th><th>Date</th></tr></thead><tbody>
  <?php foreach($workOrders as $wo): ?><tr><td><?= esc($wo['wo_number']) ?></td><td class="small"><?= esc($wo['title']) ?></td><td><span class="fm-badge badge-status-<?= $wo['status'] ?>"><?= ucfirst(str_replace('_',' ',$wo['status'])) ?></span></td><td class="small"><?= date('d M Y',strtotime($wo['created_at'])) ?></td></tr><?php endforeach; ?>
  <?php if(empty($workOrders)): ?><tr><td colspan="4" class="text-center py-3 text-muted">No work orders linked to this vendor</td></tr><?php endif; ?>
  </tbody></table></div></div></div>
</div>
</div>
<?= $this->endSection() ?>
