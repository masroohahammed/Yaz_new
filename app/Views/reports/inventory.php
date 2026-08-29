<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-box-seam me-2"></i>Inventory Report</h1></div>
  <a href="<?= base_url('reports/export/inventory/csv') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-filetype-csv me-1"></i>Export CSV</a>
</div>
<div class="row g-3 mb-3">
  <div class="col-md-3"><div class="kpi-card kpi-primary"><div class="d-flex gap-3 align-items-center"><div class="kpi-icon"><i class="bi bi-box-seam"></i></div><div><div class="kpi-label">Total Items</div><div class="kpi-value"><?= count($items) ?></div></div></div></div></div>
  <div class="col-md-3"><div class="kpi-card kpi-green"><div class="d-flex gap-3 align-items-center"><div class="kpi-icon"><i class="bi bi-currency-dollar"></i></div><div><div class="kpi-label">Stock Value</div><div class="kpi-value"><?= $currency ?> <?= number_format($totalValue/1000,1) ?>K</div></div></div></div></div>
  <div class="col-md-3"><div class="kpi-card kpi-red"><div class="d-flex gap-3 align-items-center"><div class="kpi-icon"><i class="bi bi-exclamation-triangle"></i></div><div><div class="kpi-label">Low Stock</div><div class="kpi-value"><?= count(array_filter($items,fn($i)=>$i['quantity']<=$i['min_quantity'])) ?></div></div></div></div></div>
  <div class="col-md-3"><div class="kpi-card kpi-orange"><div class="d-flex gap-3 align-items-center"><div class="kpi-icon"><i class="bi bi-x-circle"></i></div><div><div class="kpi-label">Zero Stock</div><div class="kpi-value"><?= count(array_filter($items,fn($i)=>$i['quantity']==0)) ?></div></div></div></div></div>
</div>
<div class="row g-3">
  <div class="col-lg-8">
    <div class="fm-card"><div class="card-header-fm"><h5><i class="bi bi-list me-2"></i>All Inventory Items</h5></div><div class="fm-card-body p-0">
      <table class="fm-table"><thead><tr><th>Code</th><th>Name</th><th>Category</th><th>Qty</th><th>Min</th><th>Unit Cost</th><th>Value</th><th>Status</th></tr></thead><tbody>
      <?php foreach($items as $i): $isLow=$i['quantity']<=$i['min_quantity']; ?>
      <tr class="<?= $isLow&&$i['quantity']>0?'sla-warn':'' ?>">
        <td class="small fw-semibold text-muted"><?= esc($i['item_code']) ?></td>
        <td class="small fw-semibold"><?= esc($i['name']) ?></td>
        <td class="small"><?= esc(ucfirst($i['category']??'')) ?></td>
        <td class="small fw-bold <?= $i['quantity']==0?'text-danger':($isLow?'text-warning':'') ?>"><?= $i['quantity'] ?> <?= esc($i['unit']) ?></td>
        <td class="small text-muted"><?= $i['min_quantity'] ?></td>
        <td class="small"><?= $currency ?> <?= number_format($i['unit_cost'],2) ?></td>
        <td class="small"><?= $currency ?> <?= number_format($i['quantity']*$i['unit_cost'],2) ?></td>
        <td><?php if($i['quantity']==0): ?><span class="fm-badge badge-status-overdue">Out</span><?php elseif($isLow): ?><span class="fm-badge badge-status-pending">Low</span><?php else: ?><span class="fm-badge badge-status-active">OK</span><?php endif; ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody></table>
    </div></div>
  </div>
  <div class="col-lg-4">
    <div class="fm-card"><div class="card-header-fm"><h5><i class="bi bi-arrow-left-right me-2"></i>Top 20 Movement (30 days)</h5></div><div class="fm-card-body p-0">
      <table class="fm-table"><thead><tr><th>Item</th><th>In</th><th>Out</th></tr></thead><tbody>
      <?php foreach($movements as $m): ?><tr><td class="small fw-semibold"><?= esc($m['item_name']) ?></td><td class="small text-success"><?= $m['total_in']?:0 ?></td><td class="small text-danger"><?= $m['total_out']?:0 ?></td></tr><?php endforeach; ?>
      </tbody></table>
    </div></div>
  </div>
</div>
<?= $this->endSection() ?>
