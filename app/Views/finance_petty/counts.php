<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header d-flex justify-content-between"><h1>Physical Cash Counts</h1>
<a href="<?= base_url('finance-petty/counts/create') ?>" class="btn btn-fm-primary btn-sm">New Count</a></div>
<div class="fm-card"><div class="fm-card-body p-0">
<table class="fm-table"><thead><tr><th>Number</th><th>Date</th><th>Account</th><th>System</th><th>Physical</th><th>Diff</th><th>Shortage</th><th>Excess</th><th>Status</th><th></th></tr></thead><tbody>
<?php foreach ($counts as $c): ?>
<tr>
  <td class="small font-monospace"><?= esc($c['count_number']) ?></td>
  <td class="small"><?= date('d M Y', strtotime($c['count_date'])) ?></td>
  <td class="small"><?= esc($c['account_name'] ?? '') ?></td>
  <td class="small"><?= number_format((float)$c['system_balance'],2) ?></td>
  <td class="small"><?= number_format((float)$c['physical_total'],2) ?></td>
  <td class="small fw-bold"><?= number_format((float)$c['difference'],2) ?></td>
  <td class="small text-danger"><?= number_format((float)$c['shortage'],2) ?></td>
  <td class="small text-success"><?= number_format((float)$c['excess'],2) ?></td>
  <td><span class="fm-badge badge-status-<?= esc(str_replace('_','-',$c['status'])) ?>"><?= ucfirst($c['status']) ?></span></td>
  <td><?php if ($c['status']==='submitted'): ?><form method="post" action="<?= base_url('finance-petty/counts/approve/'.$c['id']) ?>" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-success" onclick="return confirm('Post adjustment?')">Approve Adj.</button></form><?php endif; ?></td>
</tr>
<?php endforeach; ?>
</tbody></table>
</div></div>
<?= $this->endSection() ?>
