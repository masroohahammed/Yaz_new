<?php
/** HR sub-navigation — set $hrActive to current module key */
$hrActive = $hrActive ?? 'dashboard';
$items = [
  ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'url' => base_url('hr')],
  ['key' => 'employees', 'label' => 'Employees', 'icon' => 'bi-person-badge', 'url' => base_url('hr/employees')],
  ['key' => 'leave', 'label' => 'Leave', 'icon' => 'bi-calendar-check', 'url' => base_url('hr/leave')],
  ['key' => 'payroll', 'label' => 'Payroll', 'icon' => 'bi-cash-stack', 'url' => base_url('hr/payroll')],
  ['key' => 'performance', 'label' => 'Performance', 'icon' => 'bi-graph-up-arrow', 'url' => base_url('hr/performance')],
  ['key' => 'expenses', 'label' => 'Expenses', 'icon' => 'bi-receipt', 'url' => base_url('hr/expenses')],
  ['key' => 'assets', 'label' => 'Assets', 'icon' => 'bi-laptop', 'url' => base_url('hr/assets')],
];
?>
<link rel="stylesheet" href="<?= base_url('assets/css/hr-module.css') ?>">
<link rel="stylesheet" href="<?= base_url('assets/css/dms.css') ?>">
<nav class="hr-module-nav">
  <ul class="nav nav-pills flex-wrap gap-1">
    <?php foreach ($items as $item): ?>
    <li class="nav-item">
      <a class="nav-link <?= $hrActive === $item['key'] ? 'active' : '' ?>" href="<?= esc($item['url']) ?>">
        <i class="bi <?= esc($item['icon']) ?> me-1"></i><?= esc($item['label']) ?>
      </a>
    </li>
    <?php endforeach; ?>
  </ul>
</nav>
