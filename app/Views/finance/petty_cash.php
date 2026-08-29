<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-wallet me-2"></i>Petty Cash</h1>
    <?php if (!empty($filterStatus)): ?><span class="fm-badge badge-status-<?= esc($filterStatus) ?> ms-2"><?= ucfirst($filterStatus) ?></span><?php endif; ?>
  </div>
  <a href="<?= base_url('finance/petty-cash/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>New Request</a>
</div>
<div class="row g-3 mb-3">
  <?php foreach (['pending'=>'hourglass','approved'=>'check-circle','issued'=>'cash','reconciliation'=>'clipboard-check','closed'=>'lock'] as $st => $icon): ?>
  <div class="col-6 col-md-4 col-lg">
    <div class="kpi-card kpi-blue"><div class="d-flex gap-2 align-items-center">
      <div class="kpi-icon"><i class="bi bi-<?= $icon ?>"></i></div>
      <div><div class="kpi-label"><?= ucfirst($st) ?></div>
      <div class="kpi-value"><?= count(array_filter($records, fn ($r) => $r['status'] === $st)) ?></div></div>
    </div></div>
  </div>
  <?php endforeach; ?>
</div>
<div class="fm-card"><div class="fm-card-body p-0">
  <?php if(empty($records)): ?>
  <p class="text-center py-5 text-muted">No petty cash requests. <a href="<?= base_url('finance/petty-cash/create') ?>">Create one</a>.</p>
  <?php else: ?>
  <table class="fm-table">
    <thead><tr><th>PC #</th><th>Requested By</th><th>Amount</th><th>Purpose</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach($records as $pc):
      $role = session()->get('user_role');
    ?>
    <tr>
      <td><a href="<?= base_url('finance/petty-cash/view/'.$pc['id']) ?>" class="fw-bold small text-primary"><?= esc($pc['pc_number']) ?></a></td>
      <td class="small"><?= esc($pc['requested_by_name']??'—') ?></td>
      <td class="small fw-bold"><?= $currency ?> <?= number_format($pc['amount'],2) ?></td>
      <td class="small text-muted"><?= esc(substr($pc['purpose'],0,40)) ?></td>
      <td><span class="fm-badge badge-status-<?= esc($pc['status']) ?>"><?= ucfirst($pc['status']) ?></span></td>
      <td class="text-nowrap">
        <?php if($pc['status']==='pending' && in_array($role,['super_admin','finance_manager','facility_manager','property_manager'],true)): ?>
        <form method="post" action="<?= base_url('finance/petty-cash/approve/'.$pc['id']) ?>" class="d-inline"><?= csrf_field() ?>
          <button class="btn-action bg-success text-white" title="Approve"><i class="bi bi-check-lg"></i></button>
        </form>
        <form method="post" action="<?= base_url('finance/petty-cash/reject/'.$pc['id']) ?>" class="d-inline"><?= csrf_field() ?>
          <button class="btn-action bg-danger text-white" onclick="return confirm('Reject?')" title="Reject"><i class="bi bi-x-lg"></i></button>
        </form>
        <?php endif; ?>
        <?php if($pc['status']==='approved' && in_array($role,['super_admin','finance_manager','finance_user'],true)): ?>
        <form method="post" action="<?= base_url('finance/petty-cash/issue/'.$pc['id']) ?>" class="d-inline"><?= csrf_field() ?>
          <button class="btn-action bg-primary text-white" title="Issue funds"><i class="bi bi-cash"></i></button>
        </form>
        <?php endif; ?>
        <?php if($pc['status']==='issued' && in_array($role,['super_admin','finance_manager','finance_user'],true)): ?>
        <a href="<?= base_url('finance/petty-cash/view/'.$pc['id']) ?>" class="btn-action bg-warning text-dark" title="Reconcile"><i class="bi bi-clipboard-check"></i></a>
        <?php endif; ?>
        <?php if($pc['status']==='reconciliation' && in_array($role,['super_admin','finance_manager','finance_user'],true)): ?>
        <form method="post" action="<?= base_url('finance/petty-cash/close/'.$pc['id']) ?>" class="d-inline"><?= csrf_field() ?>
          <button class="btn-action bg-secondary text-white" onclick="return confirm('Close and post expense?')" title="Close"><i class="bi bi-lock"></i></button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div></div>
<?= $this->endSection() ?>
