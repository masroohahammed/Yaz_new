<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-file-earmark-text me-2"></i>Contract Expiry Report</h1></div>
  <a href="<?= base_url('reports/export/contracts/csv') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-filetype-csv me-1"></i>Export CSV</a>
</div>
<div class="fm-card mb-3"><div class="fm-card-body py-2">
  <form method="get" class="row g-2 align-items-end">
    <div class="col-md-3"><label class="form-label small">Facility</label>
      <select name="facility" class="form-select form-select-sm"><option value="">All Facilities</option><?php foreach($facilities as $f): ?><option value="<?= $f['id'] ?>" <?= $filterFacility==$f['id']?'selected':'' ?>><?= esc($f['name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><label class="form-label small">Status</label>
      <select name="status" class="form-select form-select-sm"><option value="">All</option><?php foreach(['active','expired','renewed','cancelled'] as $s): ?><option value="<?= $s ?>" <?= $filterStatus===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><label class="form-label small">&nbsp;</label>
      <div class="form-check"><input type="checkbox" name="expiring" value="1" class="form-check-input" id="expiringCheck" <?= $filterExpiring?'checked':'' ?>><label class="form-check-label small" for="expiringCheck">Expiring in 60 days only</label></div></div>
    <div class="col-md-2"><button type="submit" class="btn btn-fm-primary btn-sm w-100">Filter</button></div>
  </form>
</div></div>
<div class="fm-card"><div class="fm-card-body p-0">
  <?php if(empty($contracts)): ?><p class="text-center py-4 text-muted small">No contracts found.</p><?php else: ?>
  <table class="fm-table">
    <thead><tr><th>Contract #</th><th>Client / Tenant</th><th>Facility</th><th>Unit</th><th>Contact</th><th>Period</th><th>Value</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach($contracts as $c): $days=(int)ceil((strtotime($c['end_date'])-time())/86400); $expiring=$days>0&&$days<=60&&$c['status']==='active'; ?>
    <tr class="<?= $expiring&&$days<=7?'sla-warn':'' ?>">
      <td class="fw-semibold small"><?= esc($c['contract_number']) ?></td>
      <td><div class="small fw-semibold"><?= esc($c['client_name']) ?></div><?php if(!empty($c['client_mobile'])): ?><div class="x-small text-muted"><?= esc($c['client_mobile']) ?></div><?php endif; ?></td>
      <td class="small"><?= esc($c['facility_name']??'—') ?></td>
      <td class="small text-muted"><?= $c['unit_number']?'Unit '.esc($c['unit_number']):'—' ?></td>
      <td class="small"><?php if(!empty($c['client_email'])): ?><a href="mailto:<?= esc($c['client_email']) ?>" class="x-small"><?= esc($c['client_email']) ?></a><?php else: ?>—<?php endif; ?></td>
      <td class="small"><?= date('d M Y',strtotime($c['start_date'])) ?> → <span class="<?= $expiring?'text-danger fw-bold':'' ?>"><?= date('d M Y',strtotime($c['end_date'])) ?></span><?php if($expiring): ?><br><span class="x-small text-danger"><?= $days ?>d left</span><?php endif; ?></td>
      <td class="small"><?= $currency ?> <?= number_format($c['value'],0) ?></td>
      <td><span class="fm-badge badge-status-<?= esc($c['status']) ?>"><?= ucfirst($c['status']) ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div></div>
<?= $this->endSection() ?>
