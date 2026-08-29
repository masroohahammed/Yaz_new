<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-wallet2 me-2"></i>Expense Report</h1></div>
  <a href="<?= base_url('reports/pm') ?>" class="btn btn-fm-outline btn-sm">← Reports</a>
</div>
<div class="row g-3 mb-3">
  <div class="col-md-6"><div class="kpi-card kpi-primary"><div class="kpi-label">Expense Lines</div><div class="kpi-value"><?= count($expenses) ?></div></div></div>
  <div class="col-md-6"><div class="kpi-card kpi-red"><div class="kpi-label">Approved Total</div><div class="kpi-value" style="font-size:1rem"><?= $currency ?> <?= number_format($approvedTotal, 0) ?></div></div></div>
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
      <?php foreach (['pending','approved','rejected'] as $s): ?><option value="<?= $s ?>" <?= $filterStatus===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><button type="submit" class="btn btn-fm-primary btn-sm w-100">Filter</button></div>
  </form>
</div></div>
<div class="fm-card"><div class="fm-card-body p-0">
  <?php if (empty($expenses)): ?><p class="text-center py-4 text-muted small">No expenses in this period.</p><?php else: ?>
  <table class="fm-table">
    <thead><tr><th>Date</th><th>Property</th><th>Category</th><th>Description</th><th>Amount</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($expenses as $e): ?>
    <tr>
      <td class="small"><?= date('d M Y', strtotime($e['expense_date'])) ?></td>
      <td class="small"><?= esc($e['facility_name'] ?? '—') ?></td>
      <td class="small"><?= esc($e['category'] ?? '—') ?></td>
      <td class="small"><?= esc($e['description'] ?? '—') ?></td>
      <td class="small"><?= $currency ?> <?= number_format((float)($e['amount'] ?? 0), 0) ?></td>
      <td><span class="fm-badge badge-status-<?= esc($e['status']) ?>"><?= ucfirst($e['status']) ?></span></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div></div>
<?= $this->endSection() ?>
