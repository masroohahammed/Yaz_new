<?php

namespace App\Services\Finance;

/**
 * Maps the 19 finance sub-modules to routes, integrations, and implementation status.
 */
class FinanceModuleRegistry
{
    /** @return list<array<string, mixed>> */
    public static function modules(): array
    {
        return [
            ['id' => 1,  'key' => 'coa',        'name' => 'Chart of Accounts',     'status' => 'active',   'route' => 'finance/coa',           'integrates' => []],
            ['id' => 2,  'key' => 'gl',         'name' => 'General Ledger',        'status' => 'active',   'route' => 'finance/gl',            'integrates' => ['all']],
            ['id' => 3,  'key' => 'ar',         'name' => 'Accounts Receivable',   'status' => 'active',   'route' => 'finance/invoices',      'integrates' => ['workorders', 'contracts', 'amc']],
            ['id' => 4,  'key' => 'ap',         'name' => 'Accounts Payable',      'status' => 'active',   'route' => 'finance/vendor-bills',  'integrates' => ['procurement', 'vendors']],
            ['id' => 5,  'key' => 'petty',      'name' => 'Petty Cash',            'status' => 'active',   'route' => 'finance/petty-cash',    'integrates' => ['gl', 'expenses']],
            ['id' => 6,  'key' => 'bank',       'name' => 'Cash & Bank',           'status' => 'active',   'route' => 'finance/payments',      'integrates' => ['payments', 'gl']],
            ['id' => 7,  'key' => 'budget',     'name' => 'Budget Management',     'status' => 'active',   'route' => 'finance/budgets',       'integrates' => ['procurement', 'expenses']],
            ['id' => 8,  'key' => 'expense',    'name' => 'Expense Management',    'status' => 'active',   'route' => 'finance/expenses',      'integrates' => ['workorders', 'gl']],
            ['id' => 9,  'key' => 'revenue',    'name' => 'Revenue Management',    'status' => 'active',   'route' => 'finance/invoices',      'integrates' => ['contracts', 'workorders']],
            ['id' => 10, 'key' => 'procurement','name' => 'Procurement Finance',   'status' => 'active',   'route' => 'procurement',           'integrates' => ['ap', 'inventory']],
            ['id' => 11, 'key' => 'wo',         'name' => 'Work Order Costing',    'status' => 'active',   'route' => 'workorders',            'integrates' => ['ar', 'costing', 'job-cards']],
            ['id' => 12, 'key' => 'amc',        'name' => 'AMC Contract Billing',  'status' => 'active',   'route' => 'finance/amc-billing',   'integrates' => ['contracts', 'ar']],
            ['id' => 13, 'key' => 'payroll',    'name' => 'Payroll & HR Finance',  'status' => 'active',   'route' => 'finance/payroll-finance', 'integrates' => ['gl', 'employees']],
            ['id' => 14, 'key' => 'assets',     'name' => 'Fixed Assets',          'status' => 'active',   'route' => 'asset-register',        'integrates' => ['gl', 'maintenance']],
            ['id' => 15, 'key' => 'inventory',  'name' => 'Inventory Finance',     'status' => 'active',   'route' => 'inventory',             'integrates' => ['procurement', 'wo']],
            ['id' => 16, 'key' => 'branch',     'name' => 'Multi-Branch / Cost Center', 'status' => 'active', 'route' => 'finance/coa',      'integrates' => ['gl', 'reports']],
            ['id' => 17, 'key' => 'vat',        'name' => 'VAT / Tax',             'status' => 'active',   'route' => 'settings',              'integrates' => ['ar', 'ap']],
            ['id' => 18, 'key' => 'workflow',   'name' => 'Approval Workflow',     'status' => 'active',   'route' => 'settings/workflow',     'integrates' => ['expense', 'invoice', 'po']],
            ['id' => 19, 'key' => 'reports',    'name' => 'Financial Reports',     'status' => 'active',   'route' => 'finance/reports',       'integrates' => ['gl', 'ar', 'ap']],
        ];
    }

    /** @return list<array<string, string>> */
    public static function crossModuleLinks(): array
    {
        return [
            ['from' => 'Work Orders', 'to' => 'AR Invoice', 'flow' => 'WO complete → QA → Client approve → Draft invoice → Payment → GL'],
            ['from' => 'Job Cards', 'to' => 'WO Costing', 'flow' => 'JC labor/materials → wo_labor / wo_materials → Invoice subtotal'],
            ['from' => 'AMC Contracts', 'to' => 'Recurring Invoice', 'flow' => 'Contract schedule → Auto invoice → Collection'],
            ['from' => 'Purchase Order', 'to' => 'Vendor Bill (AP)', 'flow' => 'PO approved → 3-way match → Vendor bill pay → GL'],
            ['from' => 'Petty Cash', 'to' => 'Expense + GL', 'flow' => 'Request → Approve → Issue → Reconcile → Expense → GL'],
            ['from' => 'Maintenance Costing', 'to' => 'WO Profit', 'flow' => 'Labor + parts + vendor → actual_cost → Profit analysis'],
            ['from' => 'Inventory', 'to' => 'Material Cost', 'flow' => 'Stock issue → WO materials → Invoice / GL expense'],
            ['from' => 'Tenant / Units', 'to' => 'Rental Billing', 'flow' => 'Unit contract → Invoice (adhoc/contract)'],
        ];
    }
}
