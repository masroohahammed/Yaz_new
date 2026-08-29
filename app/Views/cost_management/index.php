<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header">
  <div><h1><i class="bi bi-wallet2 me-2 text-primary"></i>Cost Management</h1></div>
  <div class="d-flex gap-2">
    <?php if ($section === 'expenses'): ?>
      <a href="<?= base_url('cost-management/expense/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>Add Expense</a>
    <?php else: ?>
      <a href="<?= base_url('cost-management/reminders/create') ?>" class="btn btn-fm-primary btn-sm"><i class="bi bi-plus me-1"></i>Add Reminder</a>
    <?php endif; ?>
  </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<!-- Tabs -->
<ul class="nav nav-tabs mb-3">
  <li class="nav-item"><a class="nav-link <?= $section==='expenses'?'active':'' ?>" href="<?= base_url('cost-management?section=expenses&facility_id='.$facilityId) ?>">Expenses</a></li>
  <li class="nav-item"><a class="nav-link <?= $section==='reminders'?'active':'' ?>" href="<?= base_url('cost-management?section=reminders&facility_id='.$facilityId) ?>">Reminders</a></li>
</ul>

<form class="filters-inline mb-3" method="get">
  <input type="hidden" name="section" value="<?= esc($section) ?>">
  <select name="facility_id" class="form-select form-select-sm">
    <option value="">All properties</option>
    <?php foreach ($facilities as $f): ?>
      <option value="<?= $f['id'] ?>" <?= $facilityId==$f['id']?'selected':'' ?>><?= esc($f['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn btn-fm-outline btn-sm" type="submit">Filter</button>
</form>

<?php if ($section === 'expenses'): ?>
<!-- Expenses -->
<div class="form-card p-0">
  <table class="table table-registry table-sm mb-0">
    <thead><tr><th>Title</th><th>Category</th><th>Property</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach ($expenses as $e): ?>
      <tr>
        <td><?= esc($e['title']??$e['description']??'—') ?></td>
        <td><?= esc($e['category']??'—') ?></td>
        <td><?= esc($e['facility_name']??'—') ?></td>
        <td class="small"><?= esc($e['expense_date']??$e['created_at']??'—') ?></td>
        <td><?= number_format((float)($e['amount']??0),2) ?></td>
        <td><span class="badge bg-<?= ($e['status']??'pending')==='approved'?'success':'warning' ?>"><?= esc($e['status']??'pending') ?></span></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($expenses)): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">No expenses found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php else: ?>
<!-- Reminders -->
<div class="form-card p-0">
  <table class="table table-registry table-sm mb-0">
    <thead><tr><th>Title</th><th>Type</th><th>Property</th><th>Due</th><th>Recurrence</th><th>Amount</th><th>Status</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($reminders as $r): ?>
      <?php
        $isOverdue = !empty($r['due_date']) && $r['status'] === 'pending' && $r['due_date'] < date('Y-m-d');
      ?>
      <tr class="<?= $isOverdue ? 'table-danger' : '' ?>">
        <td><?= esc($r['title']) ?></td>
        <td><?= esc($r['type']??'general') ?></td>
        <td><?= esc($r['facility_name']??'—') ?></td>
        <td class="small <?= $isOverdue?'text-danger fw-bold':'' ?>"><?= esc($r['due_date']??'—') ?></td>
        <td><?= esc($r['recurrence']??'—') ?></td>
        <td><?= $r['amount'] ? number_format((float)$r['amount'],2) : '—' ?></td>
        <td><span class="badge bg-<?= ['pending'=>'warning','done'=>'success','snoozed'=>'secondary'][$r['status']]??'secondary' ?>"><?= esc($r['status']) ?></span></td>
        <td>
          <div class="d-flex gap-1">
            <a href="<?= base_url('cost-management/reminders/'.$r['id'].'/edit') ?>" class="btn btn-sm btn-fm-outline">Edit</a>
            <?php if ($r['status'] === 'pending'): ?>
              <form method="post" action="<?= base_url('cost-management/reminders/'.$r['id'].'/done') ?>"><?= csrf_field() ?>
                <button class="btn btn-success btn-sm">✓ Done</button>
              </form>
            <?php endif; ?>
            <form method="post" action="<?= base_url('cost-management/reminders/'.$r['id'].'/delete') ?>" onsubmit="return confirm('Delete?')"><?= csrf_field() ?>
              <button class="btn btn-outline-danger btn-sm">Del</button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($reminders)): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">No reminders found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
<?= $this->endSection() ?>
