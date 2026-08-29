<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $currency = $settings['currency'] ?? 'QAR'; ?>

<div class="page-header">
  <div><h1><i class="bi bi-cart me-2"></i>Procurement Dashboard</h1><div class="small text-muted mt-1"><?= date('l, d F Y') ?></div></div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('procurement/request/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>New PR</a>
    <a href="<?= base_url('procurement/order/create') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-file-earmark-text me-1"></i>New PO</a>
  </div>
</div>

<!-- KPI Row -->
<div class="row g-3 mb-3">
  <div class="col-6 col-md-3">
    <div class="kpi-card <?= ($kpi['pending_pr'] ?? 0) > 0 ? 'kpi-orange' : 'kpi-teal' ?>">
      <div class="d-flex align-items-center gap-3">
        <div class="kpi-icon"><i class="bi bi-clipboard-plus"></i></div>
        <div>
          <div class="kpi-label">Pending PRs</div>
          <div class="kpi-value"><?= $kpi['pending_pr'] ?? 0 ?></div>
          <div class="kpi-sub">Awaiting approval</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kpi-card kpi-green">
      <div class="d-flex align-items-center gap-3">
        <div class="kpi-icon"><i class="bi bi-check-circle"></i></div>
        <div>
          <div class="kpi-label">Approved PRs</div>
          <div class="kpi-value"><?= $kpi['approved_pr'] ?? 0 ?></div>
          <div class="kpi-sub">Ready to order</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kpi-card kpi-blue">
      <div class="d-flex align-items-center gap-3">
        <div class="kpi-icon"><i class="bi bi-bag-check"></i></div>
        <div>
          <div class="kpi-label">Open POs</div>
          <div class="kpi-value"><?= $kpi['open_po'] ?? 0 ?></div>
          <div class="kpi-sub">Pending / Approved</div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="kpi-card <?= ($kpi['pending_grn'] ?? 0) > 0 ? 'kpi-primary' : 'kpi-teal' ?>">
      <div class="d-flex align-items-center gap-3">
        <div class="kpi-icon"><i class="bi bi-truck"></i></div>
        <div>
          <div class="kpi-label">Pending GRN</div>
          <div class="kpi-value"><?= $kpi['pending_grn'] ?? 0 ?></div>
          <div class="kpi-sub">Delivered, needs receipt</div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">

  <!-- PENDING PURCHASE REQUESTS -->
  <div class="col-lg-7">
    <div class="fm-card">
      <div class="card-header-fm">
        <h5><i class="bi bi-clipboard-plus me-2 text-warning"></i>Pending Purchase Requests</h5>
        <a href="<?= base_url('procurement') ?>" class="small text-primary">View all</a>
      </div>
      <div class="fm-card-body p-0">
        <?php if(empty($pendingRequests)): ?>
        <p class="text-muted text-center py-4 small">No pending purchase requests</p>
        <?php else: ?>
        <table class="fm-table">
          <thead><tr><th>Item</th><th>Qty</th><th>Priority</th><th>Requested By</th><th>Actions</th></tr></thead>
          <tbody>
          <?php foreach($pendingRequests as $pr): ?>
          <tr>
            <td>
              <div class="small fw-semibold"><?= esc($pr['item_name'] ?? 'Unknown Item') ?></div>
              <?php if(!empty($pr['reason'])): ?><div class="x-small text-muted"><?= esc(substr($pr['reason'],0,40)) ?></div><?php endif; ?>
            </td>
            <td class="small fw-semibold"><?= esc($pr['quantity']) ?></td>
            <td><span class="fm-badge badge-priority-<?= esc($pr['priority']) ?>"><?= ucfirst($pr['priority']) ?></span></td>
            <td class="small text-muted"><?= esc($pr['requested_by_name'] ?? '—') ?></td>
            <td>
              <form method="post" action="<?= base_url('procurement/request/approve/'.$pr['id']) ?>" class="d-inline">
                <?= csrf_field() ?>
                <button class="btn-action bg-success text-white me-1" title="Approve" onclick="return confirm('Approve this request?')"><i class="bi bi-check-lg"></i></button>
              </form>
              <form method="post" action="<?= base_url('procurement/request/reject/'.$pr['id']) ?>" class="d-inline">
                <?= csrf_field() ?>
                <button class="btn-action bg-danger text-white" title="Reject" onclick="return confirm('Reject?')"><i class="bi bi-x-lg"></i></button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- RIGHT COLUMN -->
  <div class="col-lg-5">

    <!-- LOW STOCK ALERTS -->
    <div class="fm-card mb-3">
      <div class="card-header-fm">
        <h5><i class="bi bi-box-seam me-2 text-danger"></i>Low Stock Alerts</h5>
        <a href="<?= base_url('inventory') ?>" class="small text-primary">Inventory</a>
      </div>
      <div class="fm-card-body p-0">
        <?php if(empty($lowStockItems)): ?>
        <p class="text-muted text-center py-3 small">All stock levels OK</p>
        <?php else: ?>
        <?php foreach($lowStockItems as $s): ?>
        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom border-light">
          <div>
            <div class="small fw-semibold"><?= esc($s['name']) ?></div>
            <div class="x-small text-muted"><?= esc($s['item_code'] ?? '') ?></div>
          </div>
          <div class="text-end">
            <div class="small fw-bold text-danger"><?= $s['quantity'] ?> / <?= $s['min_quantity'] ?> <?= esc($s['unit']) ?></div>
            <a href="<?= base_url('procurement/request/create?item_id='.$s['id']) ?>" class="x-small text-primary">Create PR</a>
          </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- RECENT PURCHASE ORDERS -->
    <div class="fm-card">
      <div class="card-header-fm">
        <h5><i class="bi bi-file-earmark-text me-2 text-blue"></i>Recent Purchase Orders</h5>
      </div>
      <div class="fm-card-body p-0">
        <?php if(empty($recentOrders)): ?>
        <p class="text-muted text-center py-3 small">No purchase orders yet</p>
        <?php else: ?>
        <?php foreach($recentOrders as $po): ?>
        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom border-light">
          <div>
            <a href="<?= base_url('procurement/order/view/'.$po['id']) ?>" class="small fw-semibold text-primary text-decoration-none"><?= esc($po['po_number']) ?></a>
            <div class="x-small text-muted"><?= esc($po['vendor_name'] ?? '—') ?></div>
          </div>
          <div class="text-end">
            <div class="small fw-bold"><?= $currency ?> <?= number_format($po['total_amount'],0) ?></div>
            <span class="fm-badge badge-status-<?= esc($po['status']) ?>" style="font-size:.6rem"><?= ucfirst($po['status']) ?></span>
          </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <!-- QUICK ACTIONS -->
  <div class="col-12">
    <div class="fm-card">
      <div class="card-header-fm"><h5><i class="bi bi-lightning-charge me-2"></i>Quick Actions</h5></div>
      <div class="fm-card-body">
        <div class="row g-2">
          <?php
          $links = [
            ['procurement/request/create', 'bi-clipboard-plus', 'New PR',           'orange'],
            ['procurement',                'bi-list-check',     'All Requests',      'blue'],
            ['procurement/rfq/create',     'bi-envelope-plus',  'New RFQ',           'teal'],
            ['procurement/order/create',   'bi-bag-plus',       'New PO',            'primary'],
            ['vendors',                    'bi-truck',          'Vendors',           'green'],
            ['inventory',                  'bi-box-seam',       'Inventory',         'gold'],
            ['reports/procurement',        'bi-bar-chart-line', 'Reports',           'secondary'],
          ];
          foreach($links as [$url,$icon,$label,$color]):
          ?>
          <div class="col-6 col-md-3">
            <a href="<?= base_url($url) ?>" class="text-decoration-none">
              <div class="fm-form-section text-center py-3 h-100" style="cursor:pointer">
                <i class="bi <?= $icon ?> fs-4 mb-2 d-block text-<?= $color ?>"></i>
                <div class="small fw-semibold"><?= $label ?></div>
              </div>
            </a>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

</div>

<?= $this->endSection() ?>
