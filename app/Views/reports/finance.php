<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-receipt me-2"></i>Finance Report</h1></div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('reports/export/finance/csv') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-filetype-csv me-1"></i>CSV</a>
    <a href="<?= base_url('reports/export/finance/excel') ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a>
  </div>
</div>
<form method="get" class="fm-card mb-3"><div class="fm-card-body py-2"><div class="row g-2 align-items-end">
  <div class="col-md-2"><label class="form-label small">From</label><input type="date" name="from" class="form-control form-control-sm" value="<?= $from ?>"></div>
  <div class="col-md-2"><label class="form-label small">To</label><input type="date" name="to" class="form-control form-control-sm" value="<?= $to ?>"></div>
  <div class="col-md-4"><label class="form-label small">Facility</label><select name="facility" class="form-select form-select-sm"><option value="">All Facilities</option><?php foreach($facilities as $f): ?><option value="<?= $f['id'] ?>" <?= $filterFacility==$f['id']?'selected':'' ?>><?= esc($f['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-auto"><button type="submit" class="btn btn-fm-primary btn-sm">Filter</button></div>
</div></div></form>
<div class="row g-3 mb-3">
  <div class="col-6 col-md-3"><div class="kpi-card kpi-green"><div class="d-flex gap-3 align-items-center"><div class="kpi-icon"><i class="bi bi-cash-stack"></i></div><div><div class="kpi-label">Revenue (Paid)</div><div class="kpi-value"><?= $currency ?> <?= number_format($revenue/1000,1) ?>K</div></div></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-red"><div class="d-flex gap-3 align-items-center"><div class="kpi-icon"><i class="bi bi-credit-card"></i></div><div><div class="kpi-label">Expenses</div><div class="kpi-value"><?= $currency ?> <?= number_format($totalExpenses/1000,1) ?>K</div></div></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card <?= $profit>=0?'kpi-teal':'kpi-orange' ?>"><div class="d-flex gap-3 align-items-center"><div class="kpi-icon"><i class="bi bi-graph-up"></i></div><div><div class="kpi-label">Net Profit</div><div class="kpi-value"><?= $currency ?> <?= number_format($profit/1000,1) ?>K</div></div></div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-card kpi-orange"><div class="d-flex gap-3 align-items-center"><div class="kpi-icon"><i class="bi bi-hourglass"></i></div><div><div class="kpi-label">Outstanding</div><div class="kpi-value"><?= $currency ?> <?= number_format($outstanding/1000,1) ?>K</div></div></div></div></div>
</div>
<div class="row g-3">
  <div class="col-lg-7">
    <div class="fm-card"><div class="card-header-fm"><h5>Invoices</h5></div><div class="fm-card-body p-0">
      <table class="fm-table"><thead><tr><th>Invoice #</th><th>Facility</th><th>Issue Date</th><th>Amount</th><th>Status</th></tr></thead><tbody>
      <?php foreach($invoices as $i): ?><tr>
        <td class="small fw-semibold"><a href="<?= base_url('finance/invoices/view/'.$i['id']) ?>" class="text-primary"><?= esc($i['invoice_number']) ?></a></td>
        <td class="small text-muted"><?= esc($i['facility_name']??'—') ?></td>
        <td class="small text-muted"><?= date('d M Y',strtotime($i['issue_date'])) ?></td>
        <td class="small fw-bold"><?= $currency ?> <?= number_format($i['total'],2) ?></td>
        <td><span class="fm-badge badge-status-<?= esc($i['status']) ?>"><?= ucfirst($i['status']) ?></span></td>
      </tr><?php endforeach; ?>
      <?php if(empty($invoices)): ?><tr><td colspan="5" class="text-center py-3 text-muted">No invoices.</td></tr><?php endif; ?>
      </tbody></table>
    </div></div>
  </div>
  <div class="col-lg-5">
    <div class="fm-card"><div class="card-header-fm"><h5>Expenses</h5></div><div class="fm-card-body p-0">
      <table class="fm-table"><thead><tr><th>Description</th><th>Category</th><th>Amount</th><th>Status</th></tr></thead><tbody>
      <?php foreach($expenses as $e): ?><tr>
        <td class="small"><?= esc(substr($e['description'],0,30)) ?></td>
        <td class="small text-muted"><?= ucfirst(str_replace('_',' ',$e['category']??'')) ?></td>
        <td class="small fw-bold"><?= $currency ?> <?= number_format($e['amount'],2) ?></td>
        <td><span class="fm-badge badge-status-<?= esc($e['status']) ?>"><?= ucfirst($e['status']) ?></span></td>
      </tr><?php endforeach; ?>
      <?php if(empty($expenses)): ?><tr><td colspan="4" class="text-center py-3 text-muted">No expenses.</td></tr><?php endif; ?>
      </tbody></table>
    </div></div>
  </div>
</div>
<?= $this->endSection() ?>
