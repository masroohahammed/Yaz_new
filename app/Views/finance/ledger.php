<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-journal-text me-2"></i>Finance Ledger</h1>
    <div class="small text-muted">Income, expenses, and net profit</div>
  </div>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-4"><div class="kpi-card kpi-green"><div class="kpi-label">Income</div><div class="kpi-value" style="font-size:1.2rem"><?= $currency ?> <?= number_format($totalIncome,2) ?></div></div></div>
  <div class="col-md-4"><div class="kpi-card kpi-red"><div class="kpi-label">Expenses</div><div class="kpi-value" style="font-size:1.2rem"><?= $currency ?> <?= number_format($totalExpense,2) ?></div></div></div>
  <div class="col-md-4"><div class="kpi-card kpi-primary"><div class="kpi-label">Net</div><div class="kpi-value" style="font-size:1.2rem"><?= $currency ?> <?= number_format($netProfit,2) ?></div></div></div>
</div>

<div class="fm-card mb-3">
  <div class="fm-card-body">
    <?= form_open(base_url('finance/ledger'), ['method'=>'get','class'=>'row g-2 align-items-end','data-no-loader'=>'']) ?>
    <div class="col-auto"><label class="form-label">From</label><input type="date" name="from" class="form-control" value="<?= esc($from) ?>"></div>
    <div class="col-auto"><label class="form-label">To</label><input type="date" name="to" class="form-control" value="<?= esc($to) ?>"></div>
    <div class="col-auto"><button class="btn btn-fm-primary btn-sm">Filter</button></div>
    <?= form_close() ?>
  </div>
</div>

<div class="fm-card">
  <div class="card-header-fm"><h5><i class="bi bi-list-ul me-2"></i>Ledger Entries</h5></div>
  <div class="fm-card-body p-0">
    <table class="fm-table">
      <thead><tr><th>Date</th><th>Type</th><th>Ref</th><th>Category</th><th>Facility</th><th class="text-end">Amount</th></tr></thead>
      <tbody>
      <?php foreach($entries as $e): ?>
      <tr>
        <td class="small"><?= date('d M Y', strtotime($e['entry_date'])) ?></td>
        <td><span class="fm-badge <?= $e['entry_type']==='income'?'badge-status-paid':'badge-status-pending' ?>"><?= ucfirst($e['entry_type']) ?></span></td>
        <td class="small fw-semibold"><?= esc($e['ref_no']) ?></td>
        <td class="small"><?= esc($e['category']) ?></td>
        <td class="small"><?= esc($e['facility_name']??'—') ?></td>
        <td class="text-end fw-semibold <?= $e['entry_type']==='income'?'text-success':'text-danger' ?>">
          <?= $e['entry_type']==='income'?'+':'-' ?> <?= $currency ?> <?= number_format((float)$e['amount'],2) ?>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($entries)): ?><tr><td colspan="6" class="text-center py-4 text-muted">No ledger entries in this period</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?= $this->endSection() ?>
