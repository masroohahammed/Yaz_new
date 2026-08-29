<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-credit-card me-2"></i>Lease Payments Report</h1></div>
  <a href="<?= base_url('reports/pm') ?>" class="btn btn-fm-outline btn-sm">← Reports</a>
</div>
<div class="row g-3 mb-3">
  <div class="col-md-4"><div class="kpi-card kpi-primary"><div class="kpi-label">Payments</div><div class="kpi-value"><?= (int)$stats['count'] ?></div></div></div>
  <div class="col-md-4"><div class="kpi-card kpi-green"><div class="kpi-label">Collected</div><div class="kpi-value" style="font-size:1rem"><?= $currency ?> <?= number_format($stats['collected'], 0) ?></div></div></div>
  <div class="col-md-4"><div class="kpi-card kpi-orange"><div class="kpi-label">Due / Overdue</div><div class="kpi-value" style="font-size:1rem"><?= $currency ?> <?= number_format($stats['due'], 0) ?></div></div></div>
</div>
<div class="fm-card mb-3"><div class="fm-card-body py-2">
  <form method="get" class="row g-2 align-items-end">
    <div class="col-md-2"><label class="form-label small">From</label><input type="date" name="from" class="form-control form-control-sm" value="<?= esc($from) ?>"></div>
    <div class="col-md-2"><label class="form-label small">To</label><input type="date" name="to" class="form-control form-control-sm" value="<?= esc($to) ?>"></div>
    <div class="col-md-3"><label class="form-label small">Property</label>
      <select name="facility" class="form-select form-select-sm"><option value="">All</option>
      <?php foreach ($facilities as $f): ?><option value="<?= $f['id'] ?>" <?= $filterFacility==$f['id']?'selected':'' ?>><?= esc($f['name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><label class="form-label small">Status</label>
      <select name="status" class="form-select form-select-sm"><option value="">All</option>
      <?php foreach (['pending','paid','overdue','cancelled'] as $s): ?><option value="<?= $s ?>" <?= $filterStatus===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><button type="submit" class="btn btn-fm-primary btn-sm w-100">Filter</button></div>
  </form>
</div></div>
<div class="fm-card"><div class="fm-card-body p-0">
  <?php if (empty($payments)): ?><p class="text-center py-4 text-muted small">No payments in this period.</p><?php else: ?>
  <table class="fm-table">
    <thead><tr><th>Payment #</th><th>Contract</th><th>Tenant</th><th>Property</th><th>Due</th><th>Amount</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($payments as $p): ?>
    <tr>
      <td class="small fw-semibold"><?= esc($p['payment_number'] ?? $p['id']) ?></td>
      <td class="small"><?= esc($p['contract_number'] ?? '—') ?></td>
      <td class="small"><?= esc($p['tenant_name'] ?? '—') ?></td>
      <td class="small"><?= esc($p['facility_name'] ?? '—') ?></td>
      <td class="small"><?= !empty($p['due_date']) ? date('d M Y', strtotime($p['due_date'])) : '—' ?></td>
      <td class="small"><?= $currency ?> <?= number_format((float)($p['amount'] ?? 0), 0) ?></td>
      <td><span class="fm-badge badge-status-<?= esc($p['status']) ?>"><?= ucfirst($p['status']) ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div></div>
<?= $this->endSection() ?>
