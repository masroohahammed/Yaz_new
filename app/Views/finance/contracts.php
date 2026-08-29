<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-file-earmark-text me-2"></i>Contracts</h1></div><a href="<?= base_url('finance/contracts/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>New Contract</a></div>
<div class="fm-card"><div class="fm-card-body p-0">
  <?php if(empty($contracts)): ?><p class="text-center py-4 text-muted">No contracts found.</p><?php else: ?>
  <table class="fm-table">
    <thead><tr><th>Contract #</th><th>Client</th><th>Facility</th><th>Period</th><th>Value</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach($contracts as $c): $expiring=strtotime($c['end_date'])<strtotime('+60 days')&&$c['status']==='active'; ?>
    <tr class="<?= $expiring?'sla-warn':'' ?>">
      <td class="fw-bold small"><a href="<?= base_url('finance/contracts/view/'.$c['id']) ?>" class="text-primary"><?= esc($c['contract_number']) ?></a></td>
      <td><div class="small fw-semibold"><?= esc($c['client_name']) ?></div><?php if(!empty($c['client_mobile'])): ?><div class="x-small text-muted"><?= esc($c['client_mobile']) ?></div><?php endif; ?></td>
      <td class="small text-muted"><?= esc($c['facility_name']??'—') ?></td>
      <td class="small"><?= date('d M Y',strtotime($c['start_date'])) ?> → <span class="<?= $expiring?'text-danger fw-bold':'' ?>"><?= date('d M Y',strtotime($c['end_date'])) ?></span><?= $expiring?' ⚠️':'' ?></td>
      <td class="small fw-bold"><?= $currency ?> <?= number_format($c['value'],0) ?></td>
      <td><span class="fm-badge badge-status-<?= esc($c['status']) ?>"><?= ucfirst($c['status']) ?></span></td>
      <td><a href="<?= base_url('finance/contracts/view/'.$c['id']) ?>" class="btn-action bg-primary text-white"><i class="bi bi-eye"></i></a></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div></div>
<?= $this->endSection() ?>
