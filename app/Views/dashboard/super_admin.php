<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$primaryColor   = $settings['primary_color']   ?? '#76002b';
$secondaryColor = $settings['secondary_color'] ?? '#c7ba9a';
$totalRevenue   = $revenue  ?? 0;
$totalExpenses  = $expenses ?? 0;
$netProfit      = $totalRevenue - $totalExpenses;
$profitPct      = $totalRevenue > 0 ? round($netProfit/$totalRevenue*100,1) : 0;
?>

<!-- PAGE HEADER -->
<div class="page-header">
  <div>
    <h1 class="mb-0">CEO Dashboard</h1>
    <div class="small text-muted"><?= date('l, d F Y') ?> · <?= esc($settings['company_name']??'FM ERP') ?></div>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('reports') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-bar-chart-line me-1"></i>Reports</a>
  </div>
</div>

<!-- SLA ALERTS -->
<?php if(!empty($slaAlerts)): ?>
<div class="alert alert-danger d-flex align-items-center gap-2 mb-3 py-2">
  <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0"></i>
  <div class="flex-grow-1 small">
    <strong><?= count($slaAlerts ?? []) ?> SLA breach<?= count($slaAlerts ?? [])>1?'es':'' ?> imminent:</strong>
    <?= implode(' · ', array_map(fn($a)=>'<a href="'.base_url('workorders/view/'.$a['id']).'" class="text-danger fw-bold">'.esc($a['wo_number']).'</a>', $slaAlerts)) ?>
  </div>
</div>
<?php endif; ?>

<!-- KPI ROW 1 — Operational -->
<div class="row g-3 mb-3">
  <div class="col-6 col-sm-4 col-lg-3 col-xl-2">
    <div class="kpi-card kpi-primary">
      <div class="kpi-icon mb-2"><i class="bi bi-building"></i></div>
      <div class="kpi-label">Facilities</div>
      <div class="kpi-value"><?= $totalFacilities??0 ?></div>
    </div>
  </div>
  <div class="col-6 col-sm-4 col-lg-3 col-xl-2">
    <div class="kpi-card kpi-orange">
      <div class="kpi-icon mb-2"><i class="bi bi-tools"></i></div>
      <div class="kpi-label">Open WOs</div>
      <div class="kpi-value"><?= $openWO??0 ?></div>
      <div class="kpi-sub"><?= $criticalWO??0 ?> critical</div>
    </div>
  </div>
  <div class="col-6 col-sm-4 col-lg-3 col-xl-2">
    <div class="kpi-card <?= ($slaCompliance??100)>=90?'kpi-green':($slaCompliance>=70?'kpi-secondary':'kpi-red') ?>">
      <div class="kpi-icon mb-2"><i class="bi bi-shield-check"></i></div>
      <div class="kpi-label">SLA Rate</div>
      <div class="kpi-value"><?= $slaCompliance??100 ?>%</div>
      <div class="kpi-sub"><?= $breachedWO??0 ?> breached</div>
    </div>
  </div>
  <div class="col-6 col-sm-4 col-lg-3 col-xl-2">
    <div class="kpi-card kpi-green">
      <div class="kpi-icon mb-2"><i class="bi bi-cash-stack"></i></div>
      <div class="kpi-label">Revenue (YTD)</div>
      <div class="kpi-value" style="font-size:1.1rem"><?= $currency ?> <?= number_format($totalRevenue/1000,1) ?>K</div>
      <div class="kpi-sub"><?= $profitPct >= 0 ? '+' : '' ?><?= $profitPct ?>% margin</div>
    </div>
  </div>
  <div class="col-6 col-sm-4 col-lg-3 col-xl-2">
    <div class="kpi-card kpi-teal">
      <div class="kpi-icon mb-2"><i class="bi bi-file-earmark-text"></i></div>
      <div class="kpi-label">Active Contracts</div>
      <div class="kpi-value"><?= $activeContracts??0 ?></div>
      <?php if(!empty($expiringContracts)): ?><div class="kpi-sub text-warning"><?= count($expiringContracts ?? []) ?> expiring</div><?php endif; ?>
    </div>
  </div>
  <div class="col-6 col-sm-4 col-lg-3 col-xl-2">
    <div class="kpi-card <?= ($pendingInvoices??0)>0?'kpi-secondary':'kpi-green' ?>">
      <div class="kpi-icon mb-2"><i class="bi bi-receipt"></i></div>
      <div class="kpi-label">Pending Inv.</div>
      <div class="kpi-value"><?= $pendingInvoices??0 ?></div>
      <div class="kpi-sub"><?= $currency ?> <?= number_format(($overdueAmount??0)/1000,1) ?>K overdue</div>
    </div>
  </div>
</div>

<!-- CHARTS ROW -->
<div class="row g-3 mb-3">

  <!-- Revenue vs Expenses 12m -->
  <div class="col-lg-8">
    <div class="fm-card h-100">
      <div class="card-header-fm">
        <h5><i class="bi bi-bar-chart me-2"></i>Revenue vs Expenses (12 months)</h5>
        <a href="<?= base_url('reports/finance') ?>" class="small text-primary">Full Report</a>
      </div>
      <div class="fm-card-body"><canvas id="revExpChart" height="100"></canvas></div>
    </div>
  </div>

  <!-- WO Status Donut -->
  <div class="col-lg-4">
    <div class="fm-card h-100">
      <div class="card-header-fm"><h5><i class="bi bi-pie-chart me-2"></i>WO by Priority</h5></div>
      <div class="fm-card-body d-flex flex-column align-items-center">
        <canvas id="woChart" height="160" style="max-width:180px"></canvas>
        <div class="mt-3 w-100">
          <?php foreach(['critical'=>'danger','high'=>'orange','medium'=>'warning','low'=>'success'] as $p=>$c): ?>
          <?php $cnt = 0; foreach($woPriority??[] as $r) if($r['priority']===$p) $cnt=$r['cnt']; ?>
          <div class="d-flex justify-content-between small py-1 border-bottom border-light">
            <span><i class="bi bi-circle-fill text-<?= $c ?> me-1" style="font-size:.5rem"></i><?= ucfirst($p) ?></span>
            <strong><?= $cnt ?></strong>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">

  <!-- Recent WOs live feed -->
  <div class="col-lg-5">
    <div class="fm-card h-100">
      <div class="card-header-fm">
        <h5><i class="bi bi-activity me-2"></i>Live Work Orders</h5>
        <a href="<?= base_url('workorders') ?>" class="small text-primary">View all</a>
      </div>
      <div class="fm-card-body p-0" style="max-height:320px;overflow-y:auto">
        <?php foreach($recentWOs??[] as $w): ?>
        <div class="d-flex align-items-start gap-3 px-3 py-2 border-bottom border-light <?= $w['sla_breached']?'sla-warn':'' ?>">
          <span class="fm-badge badge-priority-<?= esc($w['priority']) ?> flex-shrink-0 mt-1"><?= ucfirst($w['priority']) ?></span>
          <div class="flex-grow-1 min-w-0">
            <div class="small fw-semibold text-truncate"><a href="<?= base_url('workorders/view/'.$w['id']) ?>" class="text-primary text-decoration-none"><?= esc($w['wo_number']) ?></a> — <?= esc(substr($w['title'],0,28)) ?></div>
            <div class="x-small text-muted"><?= esc($w['facility_name']??'') ?> <?= $w['assigned_name']?'· '.esc($w['assigned_name']):'' ?></div>
          </div>
          <span class="fm-badge badge-status-<?= esc($w['status']) ?> flex-shrink-0"><?= ucfirst(str_replace('_',' ',$w['status'])) ?></span>
        </div>
        <?php endforeach; ?>
        <?php if(empty($recentWOs)): ?><p class="text-center text-muted py-4 small">No open work orders</p><?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Technician Performance -->
  <div class="col-lg-4">
    <div class="fm-card h-100">
      <div class="card-header-fm">
        <h5><i class="bi bi-person-gear me-2"></i>Top Technicians (30d)</h5>
        <a href="<?= base_url('reports/technician') ?>" class="small text-primary">Report</a>
      </div>
      <div class="fm-card-body p-0">
        <?php foreach($techPerf??[] as $t): ?>
        <div class="px-3 py-2 border-bottom border-light">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="small fw-semibold"><?= esc($t['name']) ?></span>
            <span class="small text-muted"><?= $t['completed']??0 ?>/<?= $t['total']??0 ?></span>
          </div>
          <?php $pct = ($t['total']??0)>0?round($t['completed']/$t['total']*100):0; ?>
          <div class="progress" style="height:5px;border-radius:3px">
            <div class="progress-bar <?= $pct>=80?'':'bg-warning' ?>" style="width:<?= $pct ?>%;background:<?= $pct>=80?$primaryColor:'#f59e0b' ?>"></div>
          </div>
          <?php if(($t['breached']??0)>0): ?><div class="x-small text-danger mt-1"><?= $t['breached'] ?> SLA breach<?= $t['breached']>1?'es':'' ?></div><?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php if(empty($techPerf)): ?><p class="text-center text-muted py-4 small">No technician data</p><?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Alerts & Actions -->
  <div class="col-lg-3">
    <div class="fm-card mb-3">
      <div class="card-header-fm"><h5><i class="bi bi-bell me-2 text-warning"></i>Alerts</h5></div>
      <div class="fm-card-body p-0">
        <?php
        $alerts = [
          ['SLA Breaches',    $breachedWO??0,      'workorders?status=in_progress',   'exclamation-triangle','red'],
          ['Expiring Contracts', count($expiringContracts??[]), 'finance/contracts',  'file-earmark-text',   'warning'],
          ['Low Stock',       $lowStock??0,         'inventory?filter=low',            'box-seam',            'orange'],
          ['Pending Approvals', count($pendingApprovals??[]), 'workorders',            'hand-thumbs-up',      'blue'],
          ['Overdue Invoices', $overdueCount??0,    'finance/invoices?status=overdue', 'receipt',             'red'],
          ['Open Incidents',  $openIncidents??0,   'compliance',                       'shield-exclamation',  'orange'],
        ];
        ?>
        <?php foreach($alerts as [$label,$count,$url,$icon,$color]): ?>
        <a href="<?= base_url($url) ?>" class="d-flex justify-content-between align-items-center px-3 py-2 text-decoration-none border-bottom border-light">
          <span class="small text-dark"><i class="bi bi-<?= $icon ?> me-2 text-<?= $color ?>"></i><?= $label ?></span>
          <span class="fw-bold <?= $count>0?'text-'.$color:'text-success' ?>"><?= $count ?></span>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Pending Approvals quick action -->
    <?php if(!empty($pendingApprovals)): ?>
    <div class="fm-card">
      <div class="card-header-fm"><h5><i class="bi bi-hand-thumbs-up me-2"></i>Needs Approval</h5></div>
      <div class="fm-card-body p-0">
        <?php foreach(array_slice($pendingApprovals,0,3) as $w): ?>
        <div class="px-3 py-2 border-bottom border-light">
          <div class="small fw-semibold"><a href="<?= base_url('workorders/view/'.$w['id']) ?>"><?= esc($w['wo_number']) ?></a></div>
          <div class="x-small text-muted"><?= esc(substr($w['title'],0,30)) ?></div>
          <div class="d-flex gap-1 mt-1">
            <?= form_open(base_url('workorders/approve/'.$w['id'])) ?>
            <button class="btn btn-success btn-sm py-0 px-2 x-small" onclick="return confirm('Approve?')">Approve</button>
            <?= form_close() ?>
            <?= form_open(base_url('workorders/reject/'.$w['id'])) ?>
            <button class="btn btn-outline-danger btn-sm py-0 px-2 x-small" onclick="return confirm('Reject?')">Reject</button>
            <?= form_close() ?>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if(count($pendingApprovals ?? [])>3): ?><div class="text-center py-2 x-small text-muted"><?= count($pendingApprovals ?? [])-3 ?> more pending</div><?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Facility Occupancy + Finance Row -->
<div class="row g-3 mb-3">
  <div class="col-lg-7">
    <div class="fm-card">
      <div class="card-header-fm">
        <h5><i class="bi bi-grid me-2"></i>Facility Occupancy &amp; Performance</h5>
        <a href="<?= base_url('reports/occupancy') ?>" class="small text-primary">Report</a>
      </div>
      <div class="fm-card-body p-0">
        <table class="fm-table">
          <thead><tr><th>Facility</th><th>Units</th><th>Occupancy</th><th>Open WOs</th><th>Revenue</th></tr></thead>
          <tbody>
          <?php foreach($facilityStats??[] as $f):
            $occ = ($f['total_units']??0)>0 ? round(($f['occupied_units']??0)/($f['total_units']??1)*100) : 0;
          ?>
          <tr>
            <td class="small fw-semibold"><a href="<?= base_url('facilities/'.$f['id'].'/units') ?>" class="text-primary"><?= esc($f['name']) ?></a></td>
            <td class="small text-center"><?= $f['occupied_units']??0 ?>/<?= $f['total_units']??0 ?></td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <div class="progress flex-grow-1" style="height:6px"><div class="progress-bar" style="width:<?= $occ ?>%;background:<?= $primaryColor ?>"></div></div>
                <span class="small fw-bold"><?= $occ ?>%</span>
              </div>
            </td>
            <td class="small text-center <?= ($f['open_wo']??0)>0?'fw-bold text-warning':'' ?>"><?= $f['open_wo']??0 ?></td>
            <td class="small"><?= $currency ?> <?= number_format(($f['revenue']??0)/1000,1) ?>K</td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($facilityStats)): ?><tr><td colspan="5" class="text-center py-3 text-muted">No facility data</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Expiring Contracts -->
  <div class="col-lg-5">
    <div class="fm-card">
      <div class="card-header-fm">
        <h5><i class="bi bi-clock-history me-2 text-warning"></i>Expiring Contracts (30d)</h5>
        <a href="<?= base_url('reports/contracts?expiring=1') ?>" class="small text-primary">View all</a>
      </div>
      <div class="fm-card-body p-0">
        <?php if(empty($expiringContracts)): ?>
        <p class="text-center py-3 text-success small"><i class="bi bi-check-circle me-1"></i>No contracts expiring in 30 days</p>
        <?php else: ?>
        <?php foreach($expiringContracts as $c): $days=(int)ceil((strtotime($c['end_date'])-time())/86400); ?>
        <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom border-light">
          <div class="flex-grow-1">
            <div class="small fw-semibold"><a href="<?= base_url('finance/contracts/view/'.$c['id']) ?>" class="text-primary"><?= esc($c['contract_number']) ?></a></div>
            <div class="x-small text-muted"><?= esc($c['client_name']) ?> · <?= esc($c['facility_name']??'') ?></div>
          </div>
          <span class="fm-badge badge-status-<?= $days<=7?'overdue':($days<=30?'pending':'active') ?>"><?= $days ?>d left</span>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script>
(function(){
  const PRIMARY   = '<?= $primaryColor ?>';
  const SECONDARY = '<?= $secondaryColor ?>';
  const CURRENCY  = '<?= $currency ?>';

  // Revenue vs Expenses chart
  const trendData = <?= json_encode($revTrend??[]) ?>;
  if(trendData.length && document.getElementById('revExpChart')){
    new Chart(document.getElementById('revExpChart'),{
      type:'bar',
      data:{
        labels: trendData.map(d=>d.mon),
        datasets:[
          {label:'Revenue',data:trendData.map(d=>parseFloat(d.revenue||0)),backgroundColor:PRIMARY+'99',borderColor:PRIMARY,borderWidth:2,borderRadius:4},
          {label:'Expenses',data:trendData.map(d=>parseFloat(d.expenses||0)),backgroundColor:SECONDARY+'99',borderColor:SECONDARY,borderWidth:2,borderRadius:4},
        ]
      },
      options:{
        responsive:true,plugins:{legend:{position:'top',labels:{boxWidth:12,font:{size:11}}},
          tooltip:{callbacks:{label:ctx=>CURRENCY+' '+ctx.raw.toLocaleString()}}},
        scales:{y:{ticks:{callback:v=>CURRENCY+' '+(v>=1000?Math.round(v/1000)+'K':v)},grid:{color:'#f0f4f8'}},x:{grid:{display:false}}}
      }
    });
  }

  // WO Priority donut
  const woPri = <?= json_encode($woPriority??[]) ?>;
  if(woPri.length && document.getElementById('woChart')){
    const labels = woPri.map(d=>d.priority.charAt(0).toUpperCase()+d.priority.slice(1));
    const vals   = woPri.map(d=>parseInt(d.cnt));
    const colors = {'critical':'#c62828','high':'#e65100','medium':'#f59e0b','low':'#2e7d32'};
    new Chart(document.getElementById('woChart'),{
      type:'doughnut',
      data:{labels,datasets:[{data:vals,backgroundColor:woPri.map(d=>colors[d.priority]||'#9ca3af'),borderWidth:2,borderColor:'#fff'}]},
      options:{responsive:true,plugins:{legend:{display:false}},cutout:'70%'}
    });
  }
})();
</script>
<?= $this->endSection() ?>
