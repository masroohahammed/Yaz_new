<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * URI prefixes gated by workspace (PM vs FM).
 * Shared prefixes are accessible from both workspaces.
 */
class Workspace extends BaseConfig
{
    /** @var list<string> */
    public array $sharedPrefixes = [
        'dashboard',
        'properties',
        'facilities',
        'units',
        'maintenance',
        'helpdesk',
        'notifications',
        'profile',
        'ajax',
        'file',
        'documents',
        'media',
        'scan',
        'ai',
        'finance-bank',
        'finance-petty',
        'settings',
        'companies',
        'users',
        'cron',
        'collector',
        'portal',
    ];

    /** @var list<string> */
    public array $pmOnlyPrefixes = [
        'finance',
        'crm',
        'sales',
        'landlords',
        'tenants',
        'contracts',
        'leases',
        'lease-contracts',
        'payments',
        'rent-payments',
        'landlord-payouts',
        'pm',
        'cheques',
        'outgoing-cheques',
        'cost-management',
        'reports/pm',
        'utilities',
        'budgets',
        'complimentary-offers',
        'settings/contract-templates',
    ];

    /** @var list<string> */
    public array $fmOnlyPrefixes = [
        'workorders',
        'work-orders',
        'job-cards',
        'assets',
        'asset-register',
        'compliance',
        'inspections',
        'employees',
        'inventory',
        'procurement',
        'purchase-orders',
        'purchase-requests',
        'rfq',
        'vendors',
        'quotations',
        'estimations',
        'costing',
        'utility',
        'site-visits',
    ];
}
