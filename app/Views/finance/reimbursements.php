<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-arrow-return-left me-2"></i>Reimbursements</h1></div>
  <a href="<?= base_url('finance/reimbursements/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>New Request</a>
</div>
<div class="fm-card"><div class="fm-card-body p-0">
  <?php if(empty($records)): ?>
  <p class="text-center py-5 text-muted">No reimbursement requests. <a href="<?= base_url('finance/reimbursements/create') ?>">Submit one</a>.</p>
  <?php else: ?>
  <table class="fm-table">
    <thead><tr><th>RMB #</th><th>Employee</th><th>Amount</th><th>Category</th><th>Date</th><th>Receipt</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach($records as $r): ?>
    <tr>
      <td class="fw-bold small"><?= esc($r['rmb_number']) ?></td>
      <td class="small"><?= esc($r['requested_by_name']??'—') ?></td>
      <td class="small fw-bold"><?= $currency ?> <?= number_format($r['amount'],2) ?></td>
      <td class="small"><?= esc(ucfirst($r['category'])) ?></td>
      <td class="small text-muted"><?= date('d M Y',strtotime($r['expense_date'])) ?></td>
      <td class="small"><?= $r['receipt_path']?'<a href="'.base_url('file/receipts/'.basename($r['receipt_path'])).'" target="_blank" class="text-primary"><i class="bi bi-paperclip"></i></a>':'—' ?></td>
      <td><span class="fm-badge badge-status-<?= esc($r['status']) ?>"><?= ucfirst($r['status']) ?></span></td>
      <td>
        <?php if($r['status']==='pending' && in_array(session()->get('user_role'),['super_admin','facility_manager','finance_manager'], true)): ?>
        <form method="post" action="<?= base_url('finance/reimbursements/approve/'.$r['id']) ?>" class="d-inline"><?= csrf_field() ?>
          <button class="btn-action bg-success text-white" title="Approve" onclick="return confirm('Approve reimbursement?')"><i class="bi bi-check-lg"></i></button>
        </form>
        <?php endif; ?>
        <?php if($r['status']==='approved' && in_array(session()->get('user_role'),['super_admin','finance_manager'], true)): ?>
        <form method="post" action="<?= base_url('finance/reimbursements/pay/'.$r['id']) ?>" class="d-inline"><?= csrf_field() ?>
          <button class="btn-action bg-primary text-white" title="Mark paid" onclick="return confirm('Record payment?')"><i class="bi bi-cash"></i></button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div></div>
<?= $this->endSection() ?>
