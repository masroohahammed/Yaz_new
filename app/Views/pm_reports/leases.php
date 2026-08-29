<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-file-earmark-text me-2"></i>Lease Expiry Report</h1></div>
  <a href="<?= base_url('reports/pm') ?>" class="btn btn-fm-outline btn-sm">← Reports</a>
</div>
<div class="fm-card mb-3"><div class="fm-card-body py-2">
  <form method="get" class="row g-2 align-items-end">
    <div class="col-md-3"><label class="form-label small">Property</label>
      <select name="facility" class="form-select form-select-sm"><option value="">All Properties</option>
      <?php foreach ($facilities as $f): ?><option value="<?= $f['id'] ?>" <?= $filterFacility==$f['id']?'selected':'' ?>><?= esc($f['name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><label class="form-label small">Status</label>
      <select name="status" class="form-select form-select-sm"><option value="">All</option>
      <?php foreach (['active','expired','terminated','cancelled'] as $s): ?><option value="<?= $s ?>" <?= $filterStatus===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><label class="form-label small">&nbsp;</label>
      <div class="form-check"><input type="checkbox" name="expiring" value="1" class="form-check-input" id="expiringCheck" <?= $filterExpiring?'checked':'' ?>><label class="form-check-label small" for="expiringCheck">Expiring in 60 days only</label></div></div>
    <div class="col-md-2"><button type="submit" class="btn btn-fm-primary btn-sm w-100">Filter</button></div>
  </form>
</div></div>
<div class="fm-card"><div class="fm-card-body p-0">
  <?php if (empty($leases)): ?><p class="text-center py-4 text-muted small">No leases found.</p><?php else: ?>
  <table class="fm-table">
    <thead><tr><th>Contract #</th><th>Tenant</th><th>Property</th><th>Unit</th><th>Period</th><th>Rent</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($leases as $c):
      $days = (int) ceil((strtotime($c['end_date']) - time()) / 86400);
      $expiring = $days <= 60 && ($c['status'] ?? '') === 'active';
      $rent = (float) ($c['monthly_rent'] ?? $c['rent_amount'] ?? $c['value'] ?? 0);
    ?>
    <tr class="<?= $expiring && $days <= 7 ? 'sla-warn' : '' ?>">
      <td class="fw-semibold small"><a href="<?= base_url('contracts/'.$c['id']) ?>"><?= esc($c['contract_number']) ?></a></td>
      <td><div class="small fw-semibold"><?= esc($c['client_name'] ?? '—') ?></div>
        <?php if (!empty($c['client_mobile'])): ?><div class="x-small text-muted"><?= esc($c['client_mobile']) ?></div><?php endif; ?></td>
      <td class="small"><?= esc($c['facility_name'] ?? '—') ?></td>
      <td class="small text-muted"><?= $c['unit_number'] ? 'Unit '.esc($c['unit_number']) : '—' ?></td>
      <td class="small"><?= date('d M Y', strtotime($c['start_date'])) ?> → <span class="<?= $expiring?'text-danger fw-bold':'' ?>"><?= date('d M Y', strtotime($c['end_date'])) ?></span>
        <?php if ($expiring): ?><br><span class="x-small text-danger"><?= $days ?>d left</span><?php endif; ?></td>
      <td class="small"><?= $currency ?> <?= number_format($rent, 0) ?></td>
      <td><span class="fm-badge badge-status-<?= esc($c['status']) ?>"><?= ucfirst($c['status']) ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div></div>
<?= $this->endSection() ?>
