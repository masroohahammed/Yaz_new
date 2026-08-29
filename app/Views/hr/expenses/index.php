<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?= $this->include('hr/_subnav', ['hrActive' => 'expenses']) ?>

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
  <div><h1 class="h4 mb-0"><i class="bi bi-receipt me-2"></i>Expense Claims</h1><p class="text-muted small mb-0">Staff claims — GL 5350 on approval</p></div>
  <a href="<?= base_url('hr/expenses/create') ?>" class="btn btn-fm-primary btn-sm">New claim</a>
</div>

<div class="hr-page-card mb-3">
  <form method="get" class="row g-2 align-items-end">
    <div class="col-md-3">
      <label class="form-label small">Status</label>
      <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
        <option value="">All</option>
        <?php foreach (['pending','approved','rejected'] as $s): ?>
        <option value="<?= $s ?>" <?= ($status ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </form>
</div>

<div class="hr-page-card">
  <table class="fm-table table-sm mb-0">
    <thead><tr><th>Employee</th><th>Title</th><th>Amount</th><th>Date</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($claims as $c): ?>
    <tr>
      <td><?= esc($c['employee_name']) ?></td>
      <td><?= esc($c['title']) ?></td>
      <td class="fw-semibold"><?= number_format((float) $c['amount'], 2) ?></td>
      <td class="small"><?= esc($c['expense_date']) ?></td>
      <td><span class="fm-badge badge-status-<?= esc($c['status']) ?>"><?= ucfirst($c['status']) ?></span></td>
      <td class="text-end">
        <?php if (! empty($c['receipt_path'])): ?><a href="<?= base_url('file/' . $c['receipt_path']) ?>" target="_blank" class="btn btn-sm btn-fm-outline">Receipt</a><?php endif; ?>
        <?php if ($c['status'] === 'pending'): ?>
        <?= form_open(base_url('hr/expenses/approve/' . $c['id']), ['class' => 'd-inline']) ?><?= csrf_field() ?><button class="btn btn-sm btn-success">Approve + GL</button><?= form_close() ?>
        <?= form_open(base_url('hr/expenses/reject/' . $c['id']), ['class' => 'd-inline']) ?><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger">Reject</button><?= form_close() ?>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (empty($claims)): ?><tr><td colspan="6" class="text-muted text-center py-4">No claims.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?= $this->endSection() ?>
