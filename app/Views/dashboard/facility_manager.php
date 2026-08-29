<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-kanban me-2 text-primary"></i>Facility Manager Dashboard</h1><div class="small text-muted"><?= date('l, d F Y') ?></div></div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('workorders/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New Work Order</a>
    <a href="<?= base_url('helpdesk') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-headset me-1"></i>Help Desk <?= $pendingReq>0?"<span class='badge bg-danger'>$pendingReq</span>":'' ?></a>
  </div>
</div>

<?= view('partials/ai_alert_banner', ['aiFlags' => $aiFlags ?? []]) ?>


<!-- KPIs -->
<div class="row g-3 mb-3">
  <div class="col-6 col-sm-6 col-md-3 col-lg-3"><div class="kpi-card kpi-orange"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-tools"></i></div><div><div class="kpi-label">Open WO</div><div class="kpi-value"><?= $openWO ?></div></div></div></div></div>
  <div class="col-6 col-sm-6 col-md-3 col-lg-3"><div class="kpi-card kpi-blue"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-calendar-check"></i></div><div><div class="kpi-label">Pending PM</div><div class="kpi-value"><?= $pendingPM ?></div></div></div></div></div>
  <div class="col-6 col-sm-6 col-md-3 col-lg-3"><div class="kpi-card kpi-red"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-exclamation-triangle"></i></div><div><div class="kpi-label">SLA Breaches</div><div class="kpi-value"><?= $slaBreaches ?></div></div></div></div></div>
  <div class="col-6 col-sm-6 col-md-3 col-lg-3"><div class="kpi-card kpi-purple"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-inbox"></i></div><div><div class="kpi-label">New Requests</div><div class="kpi-value"><?= $pendingReq ?></div></div></div></div></div>
</div>

<?php $ast = $assetStats ?? []; if (!empty($ast)): ?>
<div class="row g-3 mb-3">
  <div class="col-6 col-md-3"><div class="kpi-card kpi-teal py-2 px-3"><div class="kpi-label small">Total Assets</div><div class="kpi-value"><?= (int)($ast['total_assets'] ?? 0) ?></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-green py-2 px-3"><div class="kpi-label small">Active</div><div class="kpi-value"><?= (int)($ast['active_assets'] ?? 0) ?></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-red py-2 px-3"><div class="kpi-label small">Faulty / WO Open</div><div class="kpi-value"><?= (int)($ast['faulty_assets'] ?? 0) ?> / <?= (int)($ast['assets_open_wo'] ?? 0) ?></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-orange py-2 px-3"><div class="kpi-label small">Warranty ≤60d · Scans today</div><div class="kpi-value" style="font-size:1.1rem"><?= (int)($ast['warranty_expiring'] ?? 0) ?> · <?= (int)($ast['scans_today'] ?? 0) ?></div></div></div>
</div>
<?php endif; ?>

<div class="row g-3 mb-3">
  <!-- Kanban Board -->
  <div class="col-lg-9">
    <div class="fm-card" style="background:linear-gradient(135deg,var(--fm-navy),#0d3a5c)">
      <div class="card-header-fm" style="border-color:rgba(255,255,255,.1)"><h5 class="text-white"><i class="bi bi-kanban"></i>Work Order Kanban</h5></div>
      <div class="fm-card-body">
        <div class="row g-2">
          <?php
          $grouped = ['new'=>[],'assigned'=>[],'in_progress'=>[],'on_hold'=>[]];
          foreach($workOrders as $w) { if(isset($grouped[$w['status']])) $grouped[$w['status']][] = $w; }
          $colColors = ['new'=>'#6c757d','assigned'=>'#0d6efd','in_progress'=>'#6f42c1','on_hold'=>'#fd7e14'];
          $colLabels = ['new'=>'New','assigned'=>'Assigned','in_progress'=>'In Progress','on_hold'=>'On Hold'];
          ?>
          <?php foreach($grouped as $status => $wos): ?>
          <div class="col-md-3">
            <div class="kanban-col">
              <div class="kanban-col-header d-flex justify-content-between align-items-center" style="background:rgba(255,255,255,.08);color:#fff">
                <span><?= $colLabels[$status] ?></span>
                <span class="badge" style="background:<?= $colColors[$status] ?>;font-size:.65rem"><?= count($wos ?? []) ?></span>
              </div>
              <?php foreach(array_slice($wos,0,4) as $w): ?>
              <a href="<?= base_url('workorders/view/'.$w['id']) ?>" class="text-decoration-none">
                <div class="kanban-card">
                  <div class="d-flex justify-content-between align-items-start mb-1">
                    <span class="text-white x-small fw-semibold"><?= esc($w['wo_number']) ?></span>
                    <span class="fm-badge badge-priority-<?= esc($w['priority']) ?>" style="font-size:.6rem"><?= ucfirst($w['priority']) ?></span>
                  </div>
                  <div class="text-secondary x-small"><?= esc(substr($w['title'],0,45)) ?></div>
                  <?php if($w['assigned_name']): ?><div class="text-white-50 x-small mt-1"><i class="bi bi-person-fill me-1"></i><?= esc($w['assigned_name']) ?></div><?php endif; ?>
                </div>
              </a>
              <?php endforeach; ?>
              <?php if(count($wos ?? [])>4): ?><div class="text-center x-small text-secondary mt-1">+<?= count($wos ?? [])-4 ?> more</div><?php endif; ?>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Technician Grid -->
  <div class="col-lg-3">
    <div class="fm-card h-100" style="background:linear-gradient(135deg,#1a3a5c,#0d3a5c)">
      <div class="card-header-fm" style="border-color:rgba(255,255,255,.1)"><h5 class="text-white"><i class="bi bi-people"></i>Technicians (<?= count($technicians ?? []) ?>)</h5></div>
      <div class="fm-card-body p-2">
        <?php foreach(array_slice($technicians,0,8) as $t): ?>
        <div class="d-flex align-items-center gap-2 p-2 rounded mb-1" style="background:rgba(255,255,255,.06)">
          <div class="user-avatar"><?= strtoupper(substr($t['name']??'?',0,2)) ?></div>
          <div class="flex-grow-1 overflow-hidden">
            <div class="text-white x-small fw-semibold text-truncate"><?= esc(explode(' ',$t['name']??'')[0]) ?></div>
            <div class="text-secondary" style="font-size:.65rem"><?= esc($t['designation']??'Technician') ?></div>
          </div>
          <div class="flex-shrink-0"><span style="width:8px;height:8px;border-radius:50%;display:block;background:<?= ($t['status']??'inactive')==='active'?'#27ae60':'#7f8c8d' ?>"></span></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<!-- Pending Requests + Live Feed -->
<div class="row g-3">
  <div class="col-lg-6">
    <div class="fm-card"><div class="card-header-fm"><h5><i class="bi bi-inbox text-warning"></i>Pending Requests</h5><a href="<?= base_url('helpdesk') ?>" class="small text-primary">View all</a></div>
    <div class="fm-card-body p-0">
    <table class="fm-table"><thead><tr><th>Ticket</th><th>From</th><th>Category</th><th>Priority</th><th>Actions</th></tr></thead><tbody>
    <?php foreach($recentRequests as $r): ?><tr>
      <td class="fw-semibold small"><a href="<?= base_url('helpdesk/view/'.$r['id']) ?>" class="text-primary"><?= esc($r['ticket_number']) ?></a></td>
      <td class="small"><?= esc(substr($r['requester_name'],0,15)) ?></td>
      <td class="small text-muted"><?= esc($r['category']??'—') ?></td>
      <td><span class="fm-badge badge-priority-<?= esc($r['priority']) ?>"><?= ucfirst($r['priority']) ?></span></td>
      <td><a href="<?= base_url('helpdesk/view/'.$r['id']) ?>" class="btn-action bg-primary bg-opacity-10 text-primary"><i class="bi bi-eye"></i></a></td>
    </tr><?php endforeach; ?>
    <?php if(empty($recentRequests)): ?><tr><td colspan="5" class="text-center py-4 text-muted"><i class="bi bi-check-circle text-success d-block mb-2 fs-4"></i>No pending requests</td></tr><?php endif; ?>
    </tbody></table></div></div>
  </div>
  <div class="col-lg-6">
    <div class="fm-card" style="background:linear-gradient(135deg,#1a3a5c,#0a2744)">
      <div class="card-header-fm" style="border-color:rgba(255,255,255,.1)"><h5 class="text-white"><i class="bi bi-activity"></i>Live Feed</h5><button class="btn x-small text-info-light" onclick="loadLiveFeed()"><i class="bi bi-arrow-clockwise me-1"></i>Refresh</button></div>
      <ul class="list-unstyled mb-0" id="liveFeed" style="max-height:250px;overflow-y:auto"><li class="px-3 py-3 text-secondary small text-center">Loading...</li></ul>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>loadLiveFeed(); setInterval(loadLiveFeed, 30000);</script>
<?= $this->endSection() ?>
