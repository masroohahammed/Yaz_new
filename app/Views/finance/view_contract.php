<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-file-earmark-text me-2"></i><?= esc($contract['contract_number']) ?></h1><span class="fm-badge badge-status-<?= esc($contract['status']) ?>"><?= ucfirst($contract['status']) ?></span></div>
  <div class="d-flex gap-2">
    <a href="<?= base_url('finance/invoices/create?contract_id='.$contract['id']) ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>Invoice</a>
    <a href="<?= base_url('finance/contracts/edit/'.$contract['id']) ?>" class="btn btn-fm-outline btn-sm"><i class="bi bi-pencil me-1"></i>Edit</a>
    <a href="<?= base_url('finance/contracts') ?>" class="btn btn-fm-outline btn-sm">Back</a>
  </div>
</div>
<?php
$daysLeft = $contract['end_date'] ? (int)ceil((strtotime($contract['end_date'])-time())/86400) : null;
$expiring = $daysLeft!==null && $daysLeft<=60 && $contract['status']==='active';
$expired  = $daysLeft!==null && $daysLeft<=0;
?>
<?php if($expired): ?><div class="alert alert-danger d-flex gap-2 mb-3"><i class="bi bi-exclamation-triangle-fill fs-5"></i><div>This contract has <strong>expired</strong>. Please renew or update its status.</div></div>
<?php elseif($expiring): ?><div class="alert alert-warning d-flex gap-2 mb-3"><i class="bi bi-clock-history fs-5"></i><div>Contract expires in <strong><?= $daysLeft ?> days</strong> (<?= date('d M Y',strtotime($contract['end_date'])) ?>).</div><a href="<?= base_url('finance/contracts/create?facility_id='.$contract['facility_id'].'&renew='.$contract['id']) ?>" class="btn btn-sm btn-warning ms-auto">Renew</a></div>
<?php endif; ?>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="fm-form-section mb-3">
      <h6><i class="bi bi-person"></i>Client / Tenant</h6>
      <div class="small mb-2 fw-bold"><?= esc($contract['client_name']) ?></div>
      <?php if(!empty($contract['client_email'])): ?><div class="small text-muted mb-1"><i class="bi bi-envelope me-1"></i><a href="mailto:<?= esc($contract['client_email']) ?>"><?= esc($contract['client_email']) ?></a></div><?php endif; ?>
      <?php if(!empty($contract['client_mobile'])): ?><div class="small text-muted"><i class="bi bi-telephone me-1"></i><a href="tel:<?= esc($contract['client_mobile']) ?>"><?= esc($contract['client_mobile']) ?></a></div><?php endif; ?>
    </div>
    <div class="fm-form-section mb-3">
      <h6><i class="bi bi-info-circle"></i>Contract Details</h6>
      <div class="small mb-2"><span class="text-muted">Number:</span> <strong><?= esc($contract['contract_number']) ?></strong></div>
      <div class="small mb-2"><span class="text-muted">Type:</span> <?= ucfirst(str_replace('_',' ',$contract['contract_type'])) ?></div>
      <div class="small mb-2"><span class="text-muted">Facility:</span> <strong><?= esc($contract['facility_name']??'—') ?></strong></div>
      <div class="small mb-2"><span class="text-muted">Start:</span> <?= date('d M Y',strtotime($contract['start_date'])) ?></div>
      <div class="small mb-2 <?= $expired?'text-danger fw-bold':($expiring?'text-warning':'') ?>"><span class="text-muted">End:</span> <?= date('d M Y',strtotime($contract['end_date'])) ?><?= $daysLeft!==null&&$daysLeft>0?" ($daysLeft days)":($expired?' (Expired)':'') ?></div>
      <div class="small mb-2"><span class="text-muted">Value:</span> <strong class="text-primary"><?= $currency ?> <?= number_format($contract['value'],2) ?></strong></div>
      <?php if($contract['payment_terms']): ?><div class="small"><span class="text-muted">Terms:</span> <?= esc($contract['payment_terms']) ?></div><?php endif; ?>
    </div>
    <?php if($contract['notes']): ?><div class="fm-form-section"><h6>Notes</h6><p class="small mb-0"><?= esc($contract['notes']) ?></p></div><?php endif; ?>
  </div>
  <div class="col-lg-8">
    <div class="fm-card">
      <div class="card-header-fm"><h5><i class="bi bi-receipt me-2"></i>Invoices Under This Contract</h5>
        <a href="<?= base_url('finance/invoices/create?contract_id='.$contract['id']) ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>New Invoice</a></div>
      <div class="fm-card-body p-0">
        <?php if(empty($invoices)): ?><p class="text-center py-3 text-muted small">No invoices yet.</p><?php else: ?>
        <table class="fm-table">
          <thead><tr><th>Invoice #</th><th>Issue Date</th><th>Due Date</th><th>Amount</th><th>Status</th></tr></thead>
          <tbody><?php foreach($invoices as $i): ?>
          <tr><td class="fw-semibold small"><a href="<?= base_url('finance/invoices/view/'.$i['id']) ?>" class="text-primary"><?= esc($i['invoice_number']) ?></a></td>
          <td class="small text-muted"><?= date('d M Y',strtotime($i['issue_date'])) ?></td>
          <td class="small <?= $i['status']==='overdue'?'text-danger fw-bold':'' ?>"><?= date('d M Y',strtotime($i['due_date'])) ?></td>
          <td class="small fw-bold"><?= $currency ?> <?= number_format($i['total'],2) ?></td>
          <td><span class="fm-badge badge-status-<?= esc($i['status']) ?>"><?= ucfirst($i['status']) ?></span></td></tr>
          <?php endforeach; ?></tbody>
          <tfoot><tr><td colspan="3" class="text-end small fw-bold px-4 py-2">Total Invoiced</td><td class="small fw-bold px-4 py-2"><?= $currency ?> <?= number_format(array_sum(array_column($invoices,'total')),2) ?></td><td></td></tr></tfoot>
        </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection() ?>
