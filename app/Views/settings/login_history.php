<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="page-header"><div><h1><i class="bi bi-clock-history me-2"></i>Login History</h1></div></div>
<?php $sessions = $sessions ?? []; ?>
<div class="fm-card"><div class="fm-card-body p-0">
  <table class="fm-table">
    <thead><tr><th>User</th><th>Email</th><th>Login Time</th><th>Logout Time</th><th>IP Address</th><th>Duration</th></tr></thead>
    <tbody>
    <?php foreach($sessions as $s):
      $loginTs  = $s['logged_in_at']  ? strtotime($s['logged_in_at'])  : null;
      $logoutTs = $s['logged_out_at'] ? strtotime($s['logged_out_at']) : null;
      $duration = ($loginTs&&$logoutTs) ? gmdate('H:i:s',$logoutTs-$loginTs) : ($loginTs?'Active':'—');
    ?>
    <tr>
      <td class="small fw-semibold"><?= esc($s['user_name']??'—') ?></td>
      <td class="small text-muted"><?= esc($s['email']??'') ?></td>
      <td class="small"><?= $loginTs?date('d M Y H:i',$loginTs):'—' ?></td>
      <td class="small text-muted"><?= $logoutTs?date('d M Y H:i',$logoutTs):'<span class="fm-badge badge-status-active">Active</span>' ?></td>
      <td class="x-small text-muted"><?= esc($s['ip_address']??'') ?></td>
      <td class="small text-muted"><?= $duration ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if(empty($sessions)): ?><tr><td colspan="6" class="text-center py-4 text-muted">No login history.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div></div>
<?= $this->endSection() ?>
