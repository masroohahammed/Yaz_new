<?php
$role = session()->get('user_role') ?? 'client';
$rbac = $rbac ?? new \App\Services\RbacService(\Config\Database::connect());

$path = trim((string) service('uri')->getPath(), '/');
$url  = $path === '' ? '/' : '/' . $path;

$show = fn(string $perm) => $rbac->can($role, $perm);

$link = function (string $href, string $icon, string $label, string $perm, ?string $match = null) use ($url, $show) {
    if (! $show($perm)) {
        return '';
    }
    $match  = $match ?? $href;
    $needle = str_starts_with($match, '/') ? $match : '/' . ltrim($match, '/');
    $active = str_contains($url, ltrim($needle, '/')) ? ' active' : '';

    return '<a href="' . base_url($href) . '" class="sidebar-item d-flex align-items-center gap-2 px-3 py-2 rounded text-decoration-none mb-1' . $active . '">'
         . '<i class="bi ' . esc($icon) . ' fs-5 flex-shrink-0"></i><span class="sidebar-label">' . esc($label) . '</span></a>';
};

$section = fn (string $label) => '<div class="nav-section-label px-3 pt-2 pb-1 small text-uppercase" style="opacity:.55;letter-spacing:.06em">' . esc($label) . '</div>';
?>

<?= $section('Dashboard') ?>
<?= $link('dashboard',     'bi-speedometer2',   'Overview',      'dashboard') ?>
<?= $link('dashboard/kpi', 'bi-graph-up-arrow', 'KPI Analytics', 'dashboard.kpi', 'dashboard/kpi') ?>

<?php if ($show('helpdesk')): ?>
<?= $section('Help Desk') ?>
<?= $link('helpdesk',                    'bi-headset',         'All Tickets',   'helpdesk') ?>
<?= $link('helpdesk?status=pending',     'bi-inbox',           'New / Pending', 'helpdesk', 'status=pending') ?>
<?= $link('helpdesk?status=reviewed',    'bi-check2-square',   'Verified',      'helpdesk', 'status=reviewed') ?>
<?= $link('helpdesk?status=rejected',    'bi-x-circle',        'Rejected',      'helpdesk', 'status=rejected') ?>
<?= $link('helpdesk?status=converted',   'bi-arrow-right-circle','Converted',   'helpdesk', 'status=converted') ?>
<?php endif; ?>

<?php if ($show('workorders')): ?>
<?= $section('Work Orders') ?>
<?= $link('workorders',                  'bi-tools',           'Active',        'workorders', 'workorders') ?>
<?= $link('workorders?status=assigned',  'bi-person-check',    'Assigned',      'workorders', 'status=assigned') ?>
<?= $link('workorders?status=completed', 'bi-check-circle',    'Completed',     'workorders', 'status=completed') ?>
<?= $link('workorders/schedule',         'bi-calendar-check',  'PM Schedule',   'workorders', 'schedule') ?>
<?php endif; ?>

<?php if ($show('job-cards')): ?>
<?= $section('Job Cards') ?>
<?= $link('job-cards',                   'bi-card-checklist',  'Active Jobs',   'job-cards') ?>
<?= $link('job-cards?status=completed',  'bi-check2-all',      'Completed',     'job-cards', 'status=completed') ?>
<?php endif; ?>

<?php if ($show('estimations')): ?>
<?= $section('Estimations') ?>
<?= $link('estimations', 'bi-calculator', 'All Estimations', 'estimations') ?>
<?php endif; ?>

<?php if ($show('inventory')): ?>
<?= $section('Inventory') ?>
<?= $link('inventory',          'bi-box-seam',        'Stock',     'inventory') ?>
<?= $link('inventory/movement', 'bi-arrow-left-right','Movements', 'inventory', 'movement') ?>
<?php endif; ?>

<?php if ($show('procurement') || $show('vendors')): ?>
<?= $section('Procurement') ?>
<?php if ($show('procurement')): ?>
<?= $link('procurement', 'bi-cart',  'Procurement', 'procurement') ?>
<?php endif; ?>
<?php if ($show('vendors')): ?>
<?= $link('vendors',     'bi-truck', 'Vendors',     'vendors') ?>
<?php endif; ?>
<?php endif; ?>

<?php if ($show('finance') || $show('finance.invoices') || $show('finance.expenses') || $show('finance.petty_cash') || $show('finance.reimbursements') || $show('finance.contracts') || $show('finance.coa') || $show('finance.gl') || $show('finance.ap') || $show('finance.amc') || $show('finance.budgets') || $show('finance.reports') || $show('finance.ledger') || $show('finance.payments')): ?>
<?= $section('Finance') ?>
<?php if ($show('finance')): ?>
<?= $link('finance',                'bi-graph-up',          'Overview',       'finance',               'finance') ?>
<?= $link('finance/hub',            'bi-diagram-3',         'Module Hub',     'finance',               'finance/hub') ?>
<?php endif; ?>
<?php if ($show('finance.coa')): ?>
<?= $link('finance/coa',            'bi-list-columns',      'Chart of Accounts','finance.coa',         'finance/coa') ?>
<?php endif; ?>
<?php if ($show('finance.gl')): ?>
<?= $link('finance/gl',             'bi-journal-bookmark',  'General Ledger', 'finance.gl',            'finance/gl') ?>
<?php endif; ?>
<?php if ($show('finance.invoices')): ?>
<?= $link('finance/invoices',       'bi-receipt',           'Invoices',       'finance.invoices',      'finance/invoices') ?>
<?= $link('finance/payments',       'bi-cash-coin',         'Payments',       'finance.payments',      'finance/payments') ?>
<?= $link('finance/ledger',         'bi-journal-text',      'Ledger',         'finance.ledger',        'finance/ledger') ?>
<?php endif; ?>
<?php if ($show('finance.expenses')): ?>
<?= $link('finance/expenses',       'bi-credit-card',       'Expenses',       'finance.expenses',      'finance/expenses') ?>
<?php endif; ?>
<?php if ($show('finance.petty_cash')): ?>
<?= $link('finance/petty-cash',                        'bi-wallet',       'Petty Cash',      'finance.petty_cash', 'finance/petty-cash') ?>
<?= $link('finance/petty-cash?status=pending',         'bi-hourglass',    'PC — Pending',    'finance.petty_cash', 'status=pending') ?>
<?= $link('finance/petty-cash?status=issued',          'bi-cash',         'PC — Issued',     'finance.petty_cash', 'status=issued') ?>
<?= $link('finance/petty-cash?status=reconciliation',  'bi-clipboard-check','PC — Reconciliation','finance.petty_cash','status=reconciliation') ?>
<?php endif; ?>
<?php if ($show('finance.reimbursements')): ?>
<?= $link('finance/reimbursements', 'bi-arrow-return-left', 'Reimbursements', 'finance.reimbursements','finance/reimbursements') ?>
<?php endif; ?>
<?php if ($show('finance.contracts')): ?>
<?= $link('finance/contracts',      'bi-file-earmark-text', 'Contracts',      'finance.contracts',     'finance/contracts') ?>
<?php endif; ?>
<?php if ($show('finance.amc')): ?>
<?= $link('finance/amc-billing',    'bi-calendar-check',    'AMC Billing',    'finance.amc',           'finance/amc-billing') ?>
<?php endif; ?>
<?php if ($show('finance.ap')): ?>
<?= $link('finance/vendor-bills',    'bi-truck',             'Vendor Bills (AP)','finance.ap',          'finance/vendor-bills') ?>
<?php endif; ?>
<?php if ($show('finance.budgets')): ?>
<?= $link('finance/budgets',         'bi-piggy-bank',        'Budgets',        'finance.budgets',       'finance/budgets') ?>
<?php endif; ?>
<?php if ($show('finance.reports')): ?>
<?= $link('finance/reports',         'bi-file-earmark-bar-graph','Fin. Reports','finance.reports',    'finance/reports') ?>
<?php endif; ?>
<?php endif; ?>

<?php if ($show('facilities') || $show('assets')): ?>
<?= $section('Properties') ?>
<?php if ($show('facilities')): ?>
<?= $link('facilities',    'bi-building', 'Facilities', 'facilities') ?>
<?php endif; ?>
<?php if ($show('assets')): ?>
<?= $link('asset-register','bi-cpu',      'Assets',     'assets', 'asset-register') ?>
<?php endif; ?>
<?php endif; ?>

<?php if ($show('hr.dashboard')): ?>
<?php
$hrSidebarActive = (str_contains($url, 'hr/') || str_contains($url, 'hr') || str_contains($url, 'employees')) ? ' active' : '';
?>
<?= $section('Human Resources') ?>
<a href="<?= base_url('hr/dashboard') ?>" class="sidebar-item d-flex align-items-center gap-2 px-3 py-2 rounded text-decoration-none mb-1<?= $hrSidebarActive ?>">
  <i class="bi bi-people-fill fs-5 flex-shrink-0"></i><span class="sidebar-label">HR Module</span>
</a>
<?php endif; ?>

<?php if ($show('costing')): ?>
<?= $section('Costing') ?>
<?= $link('costing', 'bi-currency-exchange', 'Maintenance Costing', 'costing') ?>
<?php endif; ?>

<?php if ($show('utility') || $show('compliance')): ?>
<?= $section('Monitoring') ?>
<?php if ($show('utility')): ?>
<?= $link('utility',    'bi-lightning-charge', 'Utility & Energy', 'utility') ?>
<?php endif; ?>
<?php if ($show('compliance')): ?>
<?= $link('compliance', 'bi-shield-check',     'Compliance',       'compliance') ?>
<?php endif; ?>
<?php endif; ?>

<?php if ($show('reports')): ?>
<?= $section('Reports') ?>
<?= $link('reports',            'bi-bar-chart-line', 'Reports Hub',     'reports',        'reports') ?>
<?php if ($show('reports.kpi')): ?>
<?= $link('reports/kpi',        'bi-speedometer',    'KPI Analytics',   'reports.kpi',    'reports/kpi') ?>
<?php endif; ?>
<?php if ($show('reports.finance')): ?>
<?= $link('reports/finance',    'bi-cash-stack',     'Finance Reports', 'reports.finance','reports/finance') ?>
<?php endif; ?>
<?= $link('reports/workorders', 'bi-tools',          'Work Orders',     'reports',        'reports/workorders') ?>
<?= $link('reports/sla',        'bi-clock',          'SLA Report',      'reports',        'reports/sla') ?>
<?php if ($show('settings.activity')): ?>
<?= $link('reports/activity-log', 'bi-journal-text', 'Activity Log',    'settings.activity', 'reports/activity-log') ?>
<?php endif; ?>
<?php endif; ?>

<?php if ($show('settings.users') || $show('settings.companies') || $show('settings.roles')): ?>
<?= $section('System') ?>
<?= $link('settings',              'bi-gear',        'Settings',            'settings.users',        'settings') ?>
<?= $link('settings/users',        'bi-person-badge','Users',               'settings.users',        'settings/users') ?>
<?php if ($show('settings.companies')): ?>
<?= $link('settings/companies',    'bi-buildings',   'Companies',           'settings.companies',    'settings/companies') ?>
<?php endif; ?>
<?php if ($show('settings.roles')): ?>
<?= $link('settings/roles',        'bi-shield-lock', 'Roles & Permissions', 'settings.roles',        'settings/roles') ?>
<?php endif; ?>
<?php if ($show('settings.workflow')): ?>
<?= $link('settings/workflow',     'bi-diagram-3',   'Workflow Config',     'settings.workflow',     'settings/workflow') ?>
<?php endif; ?>
<?php if ($show('settings.login_history')): ?>
<?= $link('settings/login-history','bi-door-open',   'Login History',       'settings.login_history','login-history') ?>
<?php endif; ?>
<?php endif; ?>

<?= $section('Account') ?>
<?= $link('profile',       'bi-person-circle', 'My Profile',    'profile') ?>
<?= $link('notifications', 'bi-bell',          'Notifications', 'notifications') ?>