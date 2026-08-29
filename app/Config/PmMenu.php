<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class PmMenu extends BaseConfig
{
    /** @var list<array<string, mixed>> */
    public array $items = [
        ['type' => 'heading', 'label' => 'Property Management'],
        ['key' => 'dashboard',    'label' => 'Dashboard',   'icon' => 'bi-speedometer2',     'url' => 'dashboard'],
        ['key' => 'properties',   'label' => 'Properties',  'icon' => 'bi-building',          'url' => 'properties'],
        ['key' => 'landlords',    'label' => 'Landlords',    'icon' => 'bi-person-badge',      'url' => 'landlords'],
        ['key' => 'tenants',      'label' => 'Tenants',      'icon' => 'bi-people',            'url' => 'tenants'],
        ['key' => 'maintenance',  'label' => 'Maintenance',  'icon' => 'bi-tools',             'url' => 'maintenance/list', 'badge' => 'view'],
        ['key' => 'scan',         'label' => 'QR Scanner',   'icon' => 'bi-qr-code-scan',      'url' => 'scan'],

        ['type' => 'heading', 'label' => 'Leasing & Finance'],
        ['key' => 'contracts',    'label' => 'Leases',        'icon' => 'bi-file-earmark-text', 'url' => 'contracts'],
        ['key' => 'offers',       'label' => 'Compl. Offers', 'icon' => 'bi-gift',              'url' => 'complimentary-offers'],
        ['key' => 'cheques',      'label' => 'Cheques',       'icon' => 'bi-bank',              'url' => 'cheques'],
        ['key' => 'invoices',     'label' => 'Invoices',      'icon' => 'bi-receipt',           'url' => 'finance/invoices'],
        ['key' => 'payments',     'label' => 'Payments',      'icon' => 'bi-credit-card',       'url' => 'payments'],
        ['key' => 'expenses',     'label' => 'Expenses',      'icon' => 'bi-wallet2',           'url' => 'finance/expenses'],
        ['key' => 'ledger',       'label' => 'Finance Ledger','icon' => 'bi-journal-text',      'url' => 'finance/pm'],
        ['key' => 'reports',      'label' => 'Reports',       'icon' => 'bi-bar-chart-line',    'url' => 'reports'],
        ['key' => 'pm_reports',   'label' => 'PM Reports Hub', 'icon' => 'bi-clipboard-data',   'url' => 'reports/pm'],
        ['key' => 'landlord_reports', 'label' => 'Landlord Reports', 'icon' => 'bi-person-badge', 'url' => 'reports/pm/landlord'],

        ['type' => 'heading', 'label' => 'CRM & Sales'],
        ['key' => 'crm',      'label' => 'CRM',        'icon' => 'bi-person-lines-fill', 'url' => 'crm'],
        ['key' => 'sales',    'label' => 'Sales',       'icon' => 'bi-bag-check',         'url' => 'sales'],
        ['key' => 'helpdesk', 'label' => 'Complaints',  'icon' => 'bi-headset',           'url' => 'helpdesk'],

        ['type' => 'heading', 'label' => 'Operations'],
        ['key' => 'utilities',       'label' => 'Utilities',       'icon' => 'bi-lightning-charge', 'url' => 'utilities'],
        ['key' => 'inspections',     'label' => 'Inspections',     'icon' => 'bi-clipboard2-pulse', 'url' => 'pm-inspections'],
        ['key' => 'budgets',         'label' => 'Budgeting',       'icon' => 'bi-bar-chart',         'url' => 'budgets'],
        ['key' => 'cost-management', 'label' => 'Cost Management', 'icon' => 'bi-coin',              'url' => 'cost-management'],
        ['key' => 'media',           'label' => 'Media Albums',     'icon' => 'bi-images',            'url' => 'media'],
        ['key' => 'ai_reports',      'label' => 'AI Reports',       'icon' => 'bi-robot',             'url' => 'ai/reports'],
        ['key' => 'outgoing',        'label' => 'Outgoing Cheques', 'icon' => 'bi-cash',              'url' => 'outgoing-cheques'],
        ['key' => 'collector_assign','label' => 'Assign Collectors','icon' => 'bi-person-badge',       'url' => 'collector/assign'],
        ['key' => 'collector_handoff','label' => 'Cash Handoffs',   'icon' => 'bi-bag-check',          'url' => 'collector/handoff'],
        ['key' => 'commission_rules', 'label' => 'Commission Rules', 'icon' => 'bi-percent',           'url' => 'sales/commission-rules'],
        ['key' => 'landlord_payouts', 'label' => 'All Payouts',      'icon' => 'bi-cash-coin',         'url' => 'pm/landlord-payouts'],
    ];
}
