<?php

namespace Config;

/**
 * Sidebar menu — workspace-tagged items filtered by RbacService at render time.
 *
 * workspace: shared | pm | fm | both (shown when session workspace matches or is both)
 */
class Menu
{
  /** @return list<array<string, mixed>> */
  public static function sections(): array
  {
    return [
      [
        'label'     => 'Dashboard',
        'workspace' => 'shared',
        'items'     => [
          ['href' => 'dashboard', 'icon' => 'bi-speedometer2', 'label' => 'Overview', 'perm' => 'dashboard'],
          ['href' => 'dashboard/kpi', 'icon' => 'bi-graph-up-arrow', 'label' => 'KPI Analytics', 'perm' => 'dashboard.kpi', 'match' => 'dashboard/kpi'],
        ],
      ],
      [
        'label'     => 'Property Management',
        'workspace' => 'pm',
        'items'     => [
          ['href' => 'landlords', 'icon' => 'bi-person-badge', 'label' => 'Landlords', 'perm' => 'landlords'],
          ['href' => 'tenants', 'icon' => 'bi-people', 'label' => 'Tenants', 'perm' => 'tenants'],
          ['href' => 'contracts', 'icon' => 'bi-file-earmark-text', 'label' => 'Lease Contracts', 'perm' => 'leases'],
          ['href' => 'payments', 'icon' => 'bi-cash-stack', 'label' => 'Rent Payments', 'perm' => 'leases'],
          ['href' => 'cheques', 'icon' => 'bi-bank', 'label' => 'Incoming Cheques', 'perm' => 'cheques'],
          ['href' => 'outgoing-cheques', 'icon' => 'bi-bank2', 'label' => 'Outgoing Cheques', 'perm' => 'outgoing_cheques'],
          ['href' => 'landlords', 'icon' => 'bi-cash-coin', 'label' => 'Landlord Payouts', 'perm' => 'landlords'],
          ['href' => 'crm', 'icon' => 'bi-person-lines-fill', 'label' => 'CRM', 'perm' => 'crm'],
          ['href' => 'sales', 'icon' => 'bi-briefcase', 'label' => 'Sales & Deals', 'perm' => 'sales'],
          ['href' => 'complimentary-offers', 'icon' => 'bi-gift', 'label' => 'Complimentary Offers', 'perm' => 'complimentary_offers'],
          ['href' => 'utilities', 'icon' => 'bi-lightning', 'label' => 'Utility Billing', 'perm' => 'utilities'],
          ['href' => 'budgets', 'icon' => 'bi-piggy-bank', 'label' => 'Budgeting', 'perm' => 'budgets'],
        ],
      ],
      [
        'label'     => 'Help Desk',
        'workspace' => 'shared',
        'items'     => [
          ['href' => 'helpdesk', 'icon' => 'bi-headset', 'label' => 'Tickets', 'perm' => 'helpdesk'],
        ],
      ],
      [
        'label'     => 'Operations (FM)',
        'workspace' => 'fm',
        'items'     => [
          ['href' => 'workorders', 'icon' => 'bi-tools', 'label' => 'Work Orders', 'perm' => 'workorders'],
          ['href' => 'job-cards', 'icon' => 'bi-card-checklist', 'label' => 'Job Cards', 'perm' => 'job-cards'],
          ['href' => 'site-visits', 'icon' => 'bi-geo-alt', 'label' => 'Site Visits', 'perm' => 'workorders', 'match' => 'site-visits'],
          ['href' => 'estimations', 'icon' => 'bi-calculator', 'label' => 'Estimations', 'perm' => 'estimations'],
          ['href' => 'costing', 'icon' => 'bi-currency-exchange', 'label' => 'Costing', 'perm' => 'costing'],
          ['href' => 'helpdesk', 'icon' => 'bi-exclamation-circle', 'label' => 'Complaints', 'perm' => 'helpdesk'],
        ],
      ],
      [
        'label'     => 'Properties',
        'workspace' => 'shared',
        'items'     => [
          ['href' => 'facilities', 'icon' => 'bi-building', 'label' => 'Properties / Facilities', 'perm' => 'facilities'],
          ['href' => 'units', 'icon' => 'bi-grid-3x3-gap', 'label' => 'Units', 'perm' => 'facilities', 'match' => 'units'],
          ['href' => 'asset-register', 'icon' => 'bi-cpu', 'label' => 'Assets', 'perm' => 'assets', 'match' => 'asset-register'],
        ],
      ],
      [
        'label'     => 'Workforce',
        'workspace' => 'fm',
        'items'     => [
          ['href' => 'employees', 'icon' => 'bi-people', 'label' => 'Employees', 'perm' => 'employees'],
        ],
      ],
      [
        'label'     => 'Procurement',
        'workspace' => 'fm',
        'items'     => [
          ['href' => 'inventory', 'icon' => 'bi-box-seam', 'label' => 'Inventory', 'perm' => 'inventory'],
          ['href' => 'procurement', 'icon' => 'bi-cart', 'label' => 'Procurement', 'perm' => 'procurement'],
          ['href' => 'vendors', 'icon' => 'bi-truck', 'label' => 'Vendors', 'perm' => 'vendors'],
        ],
      ],
      [
        'label'     => 'Finance',
        'workspace' => 'shared',
        'items'     => [
          ['href' => 'finance', 'icon' => 'bi-graph-up', 'label' => 'Overview', 'perm' => 'finance'],
          ['href' => 'finance/invoices', 'icon' => 'bi-receipt', 'label' => 'Invoices', 'perm' => 'finance.invoices'],
          ['href' => 'finance/expenses', 'icon' => 'bi-credit-card', 'label' => 'Expenses', 'perm' => 'finance.expenses'],
          ['href' => 'finance-bank', 'icon' => 'bi-bank2', 'label' => 'Bank & Cash', 'perm' => 'finance.payments'],
          ['href' => 'finance-petty', 'icon' => 'bi-cash-stack', 'label' => 'Petty Cash Module', 'perm' => 'finance.petty_cash'],
          ['href' => 'finance/petty-cash', 'icon' => 'bi-wallet', 'label' => 'Petty Cash', 'perm' => 'finance.petty_cash'],
          ['href' => 'finance/reimbursements', 'icon' => 'bi-arrow-return-left', 'label' => 'Reimbursements', 'perm' => 'finance.reimbursements'],
          ['href' => 'finance/contracts', 'icon' => 'bi-file-earmark-text', 'label' => 'Service Contracts', 'perm' => 'finance.contracts'],
          ['href' => 'finance/ledger', 'icon' => 'bi-journal-text', 'label' => 'Ledger', 'perm' => 'finance.ledger'],
          ['href' => 'finance/coa', 'icon' => 'bi-list-columns', 'label' => 'Chart of Accounts', 'perm' => 'finance.coa'],
          ['href' => 'finance/gl', 'icon' => 'bi-journal-bookmark', 'label' => 'General Ledger', 'perm' => 'finance.gl'],
          ['href' => 'finance/vendor-bills', 'icon' => 'bi-truck', 'label' => 'Vendor Bills', 'perm' => 'finance.ap'],
          ['href' => 'finance/amc-billing', 'icon' => 'bi-calendar-check', 'label' => 'AMC Billing', 'perm' => 'finance.amc'],
          ['href' => 'finance/budgets', 'icon' => 'bi-piggy-bank', 'label' => 'Budgets', 'perm' => 'finance.budgets'],
          ['href' => 'finance/reports', 'icon' => 'bi-file-earmark-bar-graph', 'label' => 'Fin. Reports', 'perm' => 'finance.reports'],
        ],
      ],
      [
        'label'     => 'Monitoring',
        'workspace' => 'fm',
        'items'     => [
          ['href' => 'compliance', 'icon' => 'bi-shield-check', 'label' => 'Compliance', 'perm' => 'compliance'],
          ['href' => 'utility', 'icon' => 'bi-lightning-charge', 'label' => 'Utility & Energy', 'perm' => 'utility'],
        ],
      ],
      [
        'label'     => 'Reports',
        'workspace' => 'shared',
        'items'     => [
          ['href' => 'reports', 'icon' => 'bi-bar-chart-line', 'label' => 'Reports Hub', 'perm' => 'reports'],
          ['href' => 'reports/kpi', 'icon' => 'bi-speedometer', 'label' => 'KPI Reports', 'perm' => 'reports.kpi', 'match' => 'reports/kpi'],
          ['href' => 'reports/finance', 'icon' => 'bi-cash-stack', 'label' => 'Finance Reports', 'perm' => 'reports.finance', 'match' => 'reports/finance'],
          ['href' => 'reports/pm', 'icon' => 'bi-clipboard-data', 'label' => 'PM Reports Hub', 'perm' => 'reports', 'match' => 'reports/pm'],
          ['href' => 'reports/pm/landlord', 'icon' => 'bi-person-badge', 'label' => 'Landlord Reports', 'perm' => 'reports', 'match' => 'reports/pm/landlord'],
        ],
      ],
      [
        'label'     => 'System',
        'workspace' => 'both',
        'items'     => [
          ['href' => 'settings', 'icon' => 'bi-gear', 'label' => 'Settings', 'perm' => 'settings.users'],
          ['href' => 'settings/users', 'icon' => 'bi-person-badge', 'label' => 'Users', 'perm' => 'settings.users', 'match' => 'settings/users'],
          ['href' => 'settings/companies', 'icon' => 'bi-buildings', 'label' => 'Companies', 'perm' => 'settings.companies', 'match' => 'settings/companies'],
          ['href' => 'settings/roles', 'icon' => 'bi-shield-lock', 'label' => 'Roles & Permissions', 'perm' => 'settings.roles', 'match' => 'settings/roles'],
        ],
      ],
      [
        'label'     => 'Account',
        'workspace' => 'shared',
        'items'     => [
          ['href' => 'profile', 'icon' => 'bi-person-circle', 'label' => 'Profile', 'perm' => 'profile'],
          ['href' => 'notifications', 'icon' => 'bi-bell', 'label' => 'Notifications', 'perm' => 'notifications'],
        ],
      ],
    ];
  }
}
