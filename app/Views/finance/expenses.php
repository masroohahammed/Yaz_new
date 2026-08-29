<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-credit-card me-2"></i>Expenses</h1></div><a href="<?= base_url('finance/expenses/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>Log Expense</a></div>
<div class="fm-card"><div class="fm-card-body p-0">
  <?php if(empty($expenses)): ?><p class="text-center py-4 text-muted">No expenses logged yet.</p><?php else: ?>
  <table class="fm-table">
    <thead><tr><th>Description</th><th>Facility</th><th>Category</th><th>Amount</th><th>Date</th><th>By</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach($expenses as $e): ?>
    <tr>
      <td class="small fw-semibold"><?= esc(substr($e['description'],0,35)) ?></td>
      <td class="small text-muted"><?= esc($e['facility_name']??'—') ?></td>
      <td class="small"><?= esc(ucfirst(str_replace('_',' ',$e['category']??''))) ?></td>
      <td class="small fw-bold"><?= $currency ?> <?= number_format($e['amount'],2) ?></td>
      <td class="small text-muted"><?= date('d M Y',strtotime($e['expense_date'])) ?></td>
      <td class="small text-muted"><?= esc($e['created_by_name']??'—') ?></td>
      <td><span class="fm-badge badge-status-<?= esc($e['status']) ?>"><?= ucfirst($e['status']) ?></span></td>
      <td>
        <?php if($e['status']==='pending' && in_array(session()->get('user_role'),['super_admin','facility_manager','finance_manager'])): ?>
        <form method="post" action="<?= base_url('finance/expenses/approve/'.$e['id']) ?>" class="d-inline"><?= csrf_field() ?><button class="btn-action bg-success text-white me-1" onclick="return confirm('Approve?')"><i class="bi bi-check-lg"></i></button></form>
        <form method="post" action="<?= base_url('finance/expenses/reject/'.$e['id']) ?>" class="d-inline"><?= csrf_field() ?><button class="btn-action bg-danger text-white" onclick="return confirm('Reject?')"><i class="bi bi-x-lg"></i></button></form>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php endif; ?>
</div></div>
<?= $this->endSection() ?>
