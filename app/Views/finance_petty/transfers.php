<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between"><h1>Petty Cash Transfers</h1></div>
<?php if ($canTransfer): ?>
<form method="post" action="<?= base_url('finance-petty/transfers/store') ?>" class="fm-card mb-3"><div class="fm-card-body row g-2"><?= csrf_field() ?>
  <div class="col-md-3"><label class="form-label small">From</label><select name="from_petty_account_id" class="form-select" required><?php foreach ($accounts as $a): ?><option value="<?= $a['id'] ?>"><?= esc($a['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-3"><label class="form-label small">To</label><select name="to_petty_account_id" class="form-select" required><?php foreach ($accounts as $a): ?><option value="<?= $a['id'] ?>"><?= esc($a['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-2"><label class="form-label small">Amount</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
  <div class="col-md-3"><label class="form-label small">Purpose</label><input name="purpose" class="form-control"></div>
  <div class="col-md-1 d-flex align-items-end"><button class="btn btn-fm-primary w-100">Go</button></div>
</div></form>
<?php endif; ?>
<div class="fm-card"><div class="fm-card-body p-0">
<table class="fm-table"><thead><tr><th>Number</th><th>Date</th><th>From</th><th>To</th><th>Amount</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($transfers as $t): ?>
<tr>
  <td class="small font-monospace"><?= esc($t['transfer_number']) ?></td>
  <td class="small"><?= date('d M Y', strtotime($t['transfer_date'])) ?></td>
  <td class="small">#<?= (int)$t['from_petty_account_id'] ?></td>
  <td class="small">#<?= (int)$t['to_petty_account_id'] ?></td>
  <td class="small fw-bold"><?= number_format((float)$t['amount'],2) ?></td>
  <td><?= esc($t['status']) ?></td>
  <td><?php if ($t['status']==='pending_approval'): ?><form method="post" action="<?= base_url('finance-petty/transfers/approve/'.$t['id']) ?>" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-success">Approve</button></form><?php endif; ?>
  <?php if ($t['status']==='approved'): ?><form method="post" action="<?= base_url('finance-petty/transfers/post/'.$t['id']) ?>" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-fm-primary">Post</button></form><?php endif; ?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div></div>
<?= $this->endSection() ?>
