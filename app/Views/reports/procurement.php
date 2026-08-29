<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-cart me-2"></i>Procurement Report</h1></div>
  <a href="<?= base_url('reports/export/procurement/csv') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-filetype-csv me-1"></i>Export CSV</a>
</div>
<form method="get" class="fm-card mb-3"><div class="fm-card-body py-2"><div class="row g-2 align-items-end">
  <div class="col-md-3"><label class="form-label small">From</label><input type="date" name="from" class="form-control form-control-sm" value="<?= $from ?>"></div>
  <div class="col-md-3"><label class="form-label small">To</label><input type="date" name="to" class="form-control form-control-sm" value="<?= $to ?>"></div>
  <div class="col-md-2"><button type="submit" class="btn btn-fm-primary btn-sm w-100">Filter</button></div>
</div></div></form>
<div class="row g-3 mb-3">
  <div class="col-md-4"><div class="kpi-card kpi-primary"><div class="d-flex gap-3 align-items-center"><div class="kpi-icon"><i class="bi bi-bag"></i></div><div><div class="kpi-label">Total Spend</div><div class="kpi-value"><?= $currency ?> <?= number_format($totalSpend/1000,1) ?>K</div></div></div></div></div>
  <div class="col-md-4"><div class="kpi-card kpi-blue"><div class="d-flex gap-3 align-items-center"><div class="kpi-icon"><i class="bi bi-file-earmark-text"></i></div><div><div class="kpi-label">Purchase Orders</div><div class="kpi-value"><?= count($orders) ?></div></div></div></div></div>
  <div class="col-md-4"><div class="kpi-card kpi-teal"><div class="d-flex gap-3 align-items-center"><div class="kpi-icon"><i class="bi bi-clipboard"></i></div><div><div class="kpi-label">Purchase Requests</div><div class="kpi-value"><?= count($requests) ?></div></div></div></div></div>
</div>
<?php if(!empty($byVendor)): ?>
<div class="fm-card mb-3"><div class="card-header-fm"><h5><i class="bi bi-truck me-2"></i>Spend by Vendor (Top 10)</h5></div><div class="fm-card-body p-0">
  <table class="fm-table"><thead><tr><th>Vendor</th><th>Orders</th><th>Total Spend</th><th>% of Total</th></tr></thead><tbody>
  <?php $i=0; foreach(array_slice($byVendor,0,10,true) as $vendor=>$d): $pct=$totalSpend>0?round($d['spend']/$totalSpend*100,1):0; ?>
  <tr><td class="fw-semibold small"><?= esc($vendor) ?></td><td class="small text-center"><?= $d['orders'] ?></td><td class="small fw-bold"><?= $currency ?> <?= number_format($d['spend'],2) ?></td>
  <td><div class="d-flex align-items-center gap-2"><div class="progress flex-grow-1" style="height:8px;border-radius:4px"><div class="progress-bar" style="width:<?= $pct ?>%;background:var(--fm-primary)"></div></div><span class="small"><?= $pct ?>%</span></div></td></tr>
  <?php if(++$i>=10) break; endforeach; ?>
  </tbody></table>
</div></div>
<?php endif; ?>
<div class="fm-card"><div class="card-header-fm"><h5>Purchase Orders</h5></div><div class="fm-card-body p-0">
  <table class="fm-table"><thead><tr><th>PO #</th><th>Vendor</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead><tbody>
  <?php foreach($orders as $o): ?><tr><td class="fw-semibold small"><a href="<?= base_url('procurement/order/view/'.$o['id']) ?>" class="text-primary"><?= esc($o['po_number']) ?></a></td><td class="small"><?= esc($o['vendor_name']??'—') ?></td><td class="small fw-bold"><?= $currency ?> <?= number_format($o['total_amount'],2) ?></td><td><span class="fm-badge badge-status-<?= esc($o['status']) ?>"><?= ucfirst($o['status']) ?></span></td><td class="small text-muted"><?= date('d M Y',strtotime($o['created_at'])) ?></td></tr><?php endforeach; ?>
  <?php if(empty($orders)): ?><tr><td colspan="5" class="text-center text-muted py-3">No purchase orders in this period.</td></tr><?php endif; ?>
  </tbody></table>
</div></div>
<?= $this->endSection() ?>
