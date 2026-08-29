<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-bank me-2"></i>Cheques (PDC) Report</h1></div>
  <a href="<?= base_url('reports/pm') ?>" class="btn btn-fm-outline btn-sm">← Reports</a>
</div>
<div class="row g-3 mb-3">
  <div class="col-md-6"><div class="kpi-card kpi-primary"><div class="kpi-label">Cheques</div><div class="kpi-value"><?= count($cheques) ?></div></div></div>
  <div class="col-md-6"><div class="kpi-card kpi-green"><div class="kpi-label">Total Amount</div><div class="kpi-value" style="font-size:1rem"><?= $currency ?> <?= number_format($totalAmount, 0) ?></div></div></div>
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
      <?php foreach (['pending','deposited','cleared','bounced','cancelled'] as $s): ?><option value="<?= $s ?>" <?= $filterStatus===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><button type="submit" class="btn btn-fm-primary btn-sm w-100">Filter</button></div>
  </form>
</div></div>
<div class="fm-card"><div class="fm-card-body p-0">
  <?php if (empty($cheques)): ?><p class="text-center py-4 text-muted small">No cheques in this period.</p><?php else: ?>
  <table class="fm-table">
    <thead><tr><th>Cheque #</th><th>Tenant</th><th>Contract</th><th>Property</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($cheques as $c): ?>
    <tr>
      <td class="small fw-semibold"><?= esc($c['cheque_no'] ?? '—') ?></td>
      <td class="small"><?= esc($c['tenant_name'] ?? '—') ?></td>
      <td class="small"><?= esc($c['contract_number'] ?? '—') ?></td>
      <td class="small"><?= esc($c['facility_name'] ?? '—') ?></td>
      <td class="small"><?= !empty($c['cheque_date']) ? date('d M Y', strtotime($c['cheque_date'])) : '—' ?></td>
      <td class="small"><?= $currency ?> <?= number_format((float)($c['amount'] ?? 0), 0) ?></td>
      <td><span class="fm-badge badge-status-<?= esc($c['status']) ?>"><?= ucfirst($c['status']) ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div></div>
<?= $this->endSection() ?>
