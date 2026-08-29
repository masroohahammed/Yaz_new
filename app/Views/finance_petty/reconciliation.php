<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><h1>Petty Cash Reconciliation</h1></div>
<div class="row g-3">
<div class="col-lg-5"><div class="fm-card"><div class="fm-card-body">
<form method="post" action="<?= base_url('finance-petty/reconciliation/store') ?>"><?= csrf_field() ?>
  <div class="mb-3"><label class="form-label">Account</label><select name="petty_account_id" class="form-select" required><?php foreach ($accounts as $a): ?><option value="<?= $a['id'] ?>"><?= esc($a['name']) ?></option><?php endforeach; ?></select></div>
  <div class="mb-3"><label class="form-label">Date</label><input type="date" name="reconciliation_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
  <div class="mb-3"><label class="form-label">Physical Cash Counted</label><input type="number" step="0.01" name="physical_cash" class="form-control" required></div>
  <div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
  <button class="btn btn-fm-primary">Save Reconciliation</button>
</form>
</div></div></div>
<div class="col-lg-7"><div class="fm-card"><div class="fm-card-body p-0">
<table class="fm-table"><thead><tr><th>Date</th><th>Account</th><th>System</th><th>Physical</th><th>Diff</th><th>Status</th></tr></thead><tbody>
<?php foreach ($recs as $r): ?>
<tr>
  <td class="small"><?= date('d M Y', strtotime($r['reconciliation_date'])) ?></td>
  <td class="small"><?= esc($r['account_name'] ?? '') ?></td>
  <td class="small"><?= number_format((float)$r['system_balance'],2) ?></td>
  <td class="small"><?= number_format((float)$r['physical_cash'],2) ?></td>
  <td class="small fw-bold"><?= number_format((float)$r['final_difference'],2) ?></td>
  <td><span class="fm-badge badge-status-<?= esc(str_replace('_','-',$r['status'])) ?>"><?= ucwords(str_replace('_',' ',$r['status'])) ?></span></td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div></div></div>
</div>
<?= $this->endSection() ?>
