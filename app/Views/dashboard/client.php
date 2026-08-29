<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-person-circle me-2 text-primary"></i>Client Portal</h1></div>
  <a href="<?= base_url('request') ?>" class="btn btn-fm-primary btn-sm" target="_blank"><i class="bi bi-plus-lg me-1"></i>New Request</a>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-4"><div class="kpi-card kpi-blue"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-ticket"></i></div><div><div class="kpi-label">My Tickets</div><div class="kpi-value"><?= count($myTickets ?? []) ?></div></div></div></div></div>
  <div class="col-md-4"><div class="kpi-card kpi-orange"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-clock"></i></div><div><div class="kpi-label">Pending</div><div class="kpi-value"><?= count(array_filter($myTickets, fn($t)=>$t['status']==='pending')) ?></div></div></div></div></div>
  <div class="col-md-4"><div class="kpi-card kpi-green"><div class="d-flex align-items-center gap-3"><div class="kpi-icon"><i class="bi bi-check-circle"></i></div><div><div class="kpi-label">Resolved</div><div class="kpi-value"><?= count(array_filter($myTickets, fn($t)=>in_array($t['status'],['converted','reviewed']))) ?></div></div></div></div></div>
</div>

<div class="row g-3">
  <!-- My Tickets -->
  <div class="col-lg-7">
    <div class="fm-card">
      <div class="card-header-fm"><h5><i class="bi bi-headset me-2"></i>My Service Requests</h5></div>
      <div class="fm-card-body p-0"><div class="table-responsive">
      <table class="fm-table"><thead><tr><th>Ticket #</th><th>Category</th><th>Priority</th><th>Status</th><th>Date</th><th>Feedback</th></tr></thead><tbody>
      <?php foreach($myTickets as $t): ?>
      <tr>
        <td class="fw-semibold small"><a href="<?= base_url('track/'.esc($t['ticket_number'])) ?>" target="_blank"><?= esc($t['ticket_number']) ?></a></td>
        <td class="small"><?= esc($t['category']??'General') ?></td>
        <td><span class="fm-badge badge-priority-<?= $t['priority'] ?>"><?= ucfirst($t['priority']) ?></span></td>
        <td><span class="fm-badge badge-status-<?= $t['status'] ?>"><?= ucfirst($t['status']) ?></span></td>
        <td class="small"><?= date('d M Y',strtotime($t['created_at'])) ?></td>
        <td>
          <?php if(in_array($t['status'],['reviewed','converted'])): ?>
          <?php if($t['rating']): ?>
          <span class="text-warning"><?= str_repeat('★',$t['rating']) ?><?= str_repeat('☆',5-$t['rating']) ?></span>
          <?php else: ?>
          <a href="<?= base_url('track/'.esc($t['ticket_number'])) ?>#feedback" class="btn-action bg-success bg-opacity-10 text-success" title="Leave feedback on track page"><i class="bi bi-star"></i></a>
          <?php endif; ?>
          <?php else: ?><span class="text-muted small">—</span><?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($myTickets)): ?><tr><td colspan="6" class="text-center py-4 text-muted">No requests yet — <a href="<?= base_url('request') ?>" target="_blank">submit one here</a></td></tr><?php endif; ?>
      </tbody></table></div></div>
    </div>
  </div>
  <!-- Invoice History + SLA -->
  <div class="col-lg-5">
    <div class="fm-card mb-3">
      <div class="card-header-fm"><h5><i class="bi bi-receipt me-2"></i>Invoice History</h5><a href="<?= base_url('finance/invoices') ?>" class="small text-primary">View all</a></div>
      <div class="fm-card-body p-0">
        <?php foreach($myInvoices as $inv): ?>
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom border-light">
          <div><div class="small fw-semibold"><?= esc($inv['invoice_number']) ?></div><div class="x-small text-muted"><?= date('d M Y',strtotime($inv['issue_date'])) ?></div></div>
          <div class="text-end"><div class="small fw-bold"><?= $currency ?> <?= number_format($inv['total'],2) ?></div><span class="fm-badge badge-status-<?= $inv['status'] ?>"><?= ucfirst($inv['status']) ?></span></div>
        </div>
        <?php endforeach; ?>
        <?php if(empty($myInvoices)): ?><div class="p-3 text-center text-muted small">No invoices yet</div><?php endif; ?>
      </div>
    </div>
    <div class="fm-card">
      <div class="card-header-fm"><h5><i class="bi bi-speedometer me-2 text-success"></i>SLA Performance</h5></div>
      <div class="fm-card-body text-center">
        <?php
        $total   = count($myTickets ?? []);
        $onTime  = count(array_filter($myTickets, fn($t)=>$t['status']==='converted'));
        $slaPct  = $total > 0 ? round($onTime/$total*100) : 100;
        ?>
        <div style="font-size:3rem;font-weight:800;color:<?= $slaPct>=90?'var(--fm-green)':($slaPct>=70?'var(--fm-orange)':'var(--fm-red)') ?>"><?= $slaPct ?>%</div>
        <div class="small text-muted">Requests resolved on time</div>
        <div class="progress mt-3" style="height:8px"><div class="progress-bar <?= $slaPct>=90?'bg-success':($slaPct>=70?'bg-warning':'bg-danger') ?>" style="width:<?= $slaPct ?>%"></div></div>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
