<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-receipt me-2"></i>Invoices</h1></div>
  <a href="<?= base_url('finance/invoices/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>New Invoice</a>
</div>
<form method="get" class="fm-card mb-3" data-no-loader><div class="fm-card-body py-2"><div class="row g-2 align-items-end">
  <div class="col-md-3"><select name="status" class="form-select form-select-sm"><option value="">All Status</option><?php foreach(['draft','sent','paid','overdue','cancelled'] as $s): ?><option value="<?= $s ?>" <?= $filterStatus===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-3"><select name="facility" class="form-select form-select-sm"><option value="">All Facilities</option><?php foreach($facilities as $f): ?><option value="<?= $f['id'] ?>" <?= $filterFacility==$f['id']?'selected':'' ?>><?= esc($f['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-2"><input type="date" name="from" class="form-control form-control-sm" value="<?= $from ?>"></div>
  <div class="col-md-2"><input type="date" name="to" class="form-control form-control-sm" value="<?= $to ?>"></div>
  <div class="col-auto"><button type="submit" class="btn btn-fm-primary btn-sm">Filter</button></div>
</div></div></form>
<div class="fm-card"><div class="fm-card-body p-0">
  <?php if(empty($invoices)): ?><p class="text-center py-4 text-muted">No invoices found.</p><?php else: ?>
  <table class="fm-table">
    <thead><tr><th>Invoice #</th><th>Facility</th><th>Issue Date</th><th>Due Date</th><th>Amount</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach($invoices as $i): ?>
    <tr>
      <td class="fw-bold small"><a href="<?= base_url('finance/invoices/view/'.$i['id']) ?>" class="text-primary"><?= esc($i['invoice_number']) ?></a></td>
      <td class="small"><?= esc($i['facility_name']??'—') ?></td>
      <td class="small text-muted"><?= date('d M Y',strtotime($i['issue_date'])) ?></td>
      <td class="small <?= $i['status']==='overdue'?'text-danger fw-bold':'' ?>"><?= date('d M Y',strtotime($i['due_date'])) ?></td>
      <td class="fw-bold"><?= $currency ?> <?= number_format($i['total'],2) ?></td>
      <td><span class="fm-badge badge-status-<?= esc($i['status']) ?>"><?= ucfirst($i['status']) ?></span></td>
      <td>
        <a href="<?= base_url('finance/invoices/view/'.$i['id']) ?>" class="btn-action bg-primary text-white me-1"><i class="bi bi-eye"></i></a>
        <a href="<?= base_url('finance/invoices/print/'.$i['id']) ?>" class="btn-action bg-secondary text-white" target="_blank"><i class="bi bi-printer"></i></a>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div></div>
<?= $this->endSection() ?>
