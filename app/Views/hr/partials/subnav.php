<?php
/** @var string $active */
$role = session()->get('user_role') ?? 'client';
$rbac = new \App\Services\RbacService(\Config\Database::connect());
$show = fn (string $perm) => $rbac->can($role, $perm);

$path = trim((string) service('uri')->getPath(), '/');
if ($active === '') {
    $active = match (true) {
        $path === 'hr' || str_starts_with($path, 'hr/dashboard')     => 'dashboard',
        str_starts_with($path, 'employees')                            => 'employees',
        str_starts_with($path, 'hr/attendance')                        => 'attendance',
        str_starts_with($path, 'hr/shifts')                            => 'shifts',
        str_starts_with($path, 'hr/leave')                             => 'leave',
        str_starts_with($path, 'hr/manpower')                          => 'manpower',
        str_starts_with($path, 'hr/salary')                            => 'salary',
        str_starts_with($path, 'hr/compensation/advances')             => 'advances',
        str_starts_with($path, 'hr/compensation/loans')                => 'loans',
        str_starts_with($path, 'hr/payroll')                            => 'payroll',
        str_starts_with($path, 'hr/wps')                               => 'wps',
        str_starts_with($path, 'hr/onboarding')                        => 'onboarding',
        str_starts_with($path, 'hr/offboarding')                       => 'offboarding',
        str_starts_with($path, 'hr/transfers')                         => 'transfers',
        str_starts_with($path, 'hr/requests')                          => 'requests',
        str_starts_with($path, 'hr/approvals')                         => 'approvals',
        str_starts_with($path, 'hr/documents')                         => 'documents',
        str_starts_with($path, 'hr/contracts/expiry')                  => 'contracts',
        str_starts_with($path, 'hr/settings')                          => 'settings',
        default                                                        => '',
    };
}

$items = [];
$add = function (string $key, string $href, string $label, string $perm, string $icon) use (&$items, $show) {
    if ($show($perm)) {
        $items[] = ['key' => $key, 'href' => $href, 'label' => $label, 'perm' => $perm, 'icon' => $icon];
    }
};

$add('dashboard', 'hr/dashboard', 'Dashboard', 'hr.dashboard', 'bi-speedometer2');
$add('employees', 'employees', 'Employees', 'employees', 'bi-people-fill');
$add('attendance', 'hr/attendance', 'Attendance', 'attendance.view', 'bi-clock-history');
$add('shifts', 'hr/shifts', 'Shifts', 'attendance.adjust', 'bi-calendar3');
$add('leave', 'hr/leave', 'Leave', 'leave.view', 'bi-calendar2-week');
$add('manpower', 'hr/manpower', 'Manpower', 'manpower.view', 'bi-diagram-3');
$add('salary', 'hr/salary', 'Salary', 'employee.salary.view', 'bi-cash-stack');
$add('advances', 'hr/compensation/advances', 'Advances', 'employee.salary.view', 'bi-wallet2');
$add('loans', 'hr/compensation/loans', 'Loans', 'employee.salary.view', 'bi-bank');
$add('payroll', 'hr/payroll', 'Payroll', 'payroll.process', 'bi-calculator');
$add('wps', 'hr/wps', 'WPS', 'wps.generate', 'bi-file-earmark-spreadsheet');
$add('onboarding', 'hr/onboarding', 'Onboarding', 'employee.edit', 'bi-person-check');
$add('transfers', 'hr/transfers', 'Transfers', 'employee.edit', 'bi-arrow-left-right');
$add('requests', 'hr/requests', 'Requests', 'employees', 'bi-inbox');
$add('documents', 'hr/documents/expiry', 'Doc Expiry', 'hr.document_expiry', 'bi-file-earmark-text');
$add('contracts', 'hr/contracts/expiry', 'Contracts', 'hr.contract_expiry', 'bi-file-earmark-medical');
$add('settings', 'hr/settings', 'Settings', 'hr.settings', 'bi-gear');

if ($show('transfer.approve') || $show('settlement.approve') || $show('leave.approve')) {
    $items[] = [
        'key'   => 'approvals',
        'href'  => 'hr/approvals',
        'label' => 'Approvals',
        'perm'  => 'transfer.approve',
        'icon'  => 'bi-check2-circle',
    ];
}

if ($items === []) {
    return;
}
?>
<nav class="hr-module-nav fm-card mb-3" aria-label="HR module navigation">
  <div class="fm-card-body py-2 px-2">
    <ul class="nav nav-pills hr-module-nav-list flex-nowrap gap-1 mb-0">
      <?php foreach ($items as $item): ?>
      <li class="nav-item">
        <a class="nav-link d-flex align-items-center gap-1 <?= $active === $item['key'] ? 'active' : '' ?>"
           href="<?= base_url($item['href']) ?>">
          <i class="bi <?= esc($item['icon']) ?>" aria-hidden="true"></i>
          <span><?= esc($item['label']) ?></span>
        </a>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
</nav>
