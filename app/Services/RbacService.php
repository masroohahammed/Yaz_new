<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Role-based access control — permissions by role name (no hardcoded role IDs).
 * Optional overrides stored in system_settings as rbac_overrides JSON.
 */
class RbacService
{
    /** @var array<string, list<string>> */
    private const PERMISSIONS = [
        'super_admin' => ['*'],
        'facility_manager' => [
            'dashboard', 'dashboard.kpi', 'helpdesk', 'workorders', 'job-cards', 'facilities', 'facilities.create', 'facilities.edit',
            'units.view', 'units.create', 'units.edit',
            'assets',
            'employees', 'employee.create', 'employee.edit', 'employee.delete',
            'attendance', 'attendance.view', 'attendance.adjust', 'attendance.approve',
            'hr.settings', 'hr.document_expiry', 'hr.contract_expiry', 'hr.dashboard',
            'employee.identification.view', 'employee.salary.view',
            'employee.documents.view', 'employee.documents.upload',
            'employee.contract.view', 'employee.contract.view_rate', 'employee.contract.edit', 'employee.contract.edit_rate',
            'employee.assignment.view', 'employee.assignment.edit',
            'manpower.view', 'manpower.manage',
            'leave.view', 'leave.apply', 'leave.approve',
            'employee.salary.edit', 'advance.approve', 'loan.approve',
            'payroll.process', 'payroll.approve', 'payroll.unlock', 'wps.generate',
            'transfer.approve', 'settlement.approve',
            'inventory', 'procurement', 'vendors', 'quotations', 'compliance', 'utility', 'reports',
            'reports.finance', 'reports.kpi', 'finance', 'finance.invoices', 'finance.contracts',
            'finance.petty_cash', 'finance.reimbursements', 'finance.amc', 'finance.expenses',
            'finance.payments', 'finance.ledger', 'media', 'ai',
            'estimations', 'costing', 'settings.users', 'settings.activity', 'notifications', 'profile',
        ],
        'property_manager' => [
            'dashboard', 'dashboard.kpi', 'helpdesk', 'facilities', 'facilities.create', 'facilities.edit',
            'units.view', 'units.create', 'units.edit',
            'assets',
            'tenants', 'landlords', 'leases', 'cheques', 'crm', 'sales',
            'utilities', 'budgets', 'cost-management', 'offers', 'media', 'ai',
            'compliance', 'utility', 'reports', 'reports.kpi',
            'finance', 'finance.invoices', 'finance.contracts', 'finance.payments',
            'finance.expenses', 'finance.ledger', 'finance.petty_cash', 'finance.reimbursements',
            'estimations', 'notifications', 'profile',
        ],
        'real_estate_manager' => [
            'dashboard', 'dashboard.kpi', 'helpdesk', 'facilities', 'facilities.create', 'facilities.edit',
            'units.view', 'units.create', 'units.edit',
            'assets',
            'tenants', 'landlords', 'leases', 'cheques', 'crm', 'sales',
            'utilities', 'budgets', 'offers', 'media', 'ai',
            'compliance', 'utility', 'reports', 'reports.kpi',
            'finance', 'finance.invoices', 'finance.contracts', 'finance.payments',
            'finance.expenses', 'notifications', 'profile',
        ],
        'salesman' => [
            'dashboard', 'helpdesk', 'facilities', 'units.view', 'tenants', 'crm', 'sales', 'media',
            'estimations', 'notifications', 'profile',
        ],
        'supervisor' => [
            'dashboard', 'helpdesk', 'workorders', 'job-cards', 'employees', 'attendance',
            'attendance.view', 'attendance.approve', 'hr.dashboard',
            'leave.view', 'leave.apply', 'leave.approve',
            'employee.documents.view', 'employee.contract.view', 'employee.assignment.view',
            'reports', 'profile', 'notifications',
        ],
        'qa_inspector' => [
            'dashboard', 'workorders', 'job-cards', 'reports', 'profile', 'notifications',
        ],
        'technician' => [
            'dashboard', 'workorders', 'job-cards', 'inventory', 'profile', 'notifications', 'media',
            'attendance', 'hr.dashboard', 'finance.reimbursements', 'finance.petty_cash',
            'leave.view', 'leave.apply',
        ],
        'finance_manager' => [
            'dashboard', 'dashboard.kpi', 'finance', 'finance.invoices', 'finance.expenses',
            'finance.petty_cash', 'finance.reimbursements', 'finance.contracts',
            'finance.ledger', 'finance.payments', 'finance.coa', 'finance.gl', 'finance.ap',
            'finance.amc', 'finance.budgets', 'finance.reports',
            'employees', 'employee.salary.view', 'employee.salary.edit', 'advance.approve', 'loan.approve',
            'hr.dashboard',
            'payroll.process', 'payroll.approve', 'payroll.unlock', 'wps.generate',
            'transfer.approve', 'settlement.approve',
            'employee.contract.view', 'employee.contract.view_rate', 'hr.contract_expiry',
            'manpower.view',
            'tenants', 'landlords', 'leases', 'cheques', 'budgets', 'utilities', 'cost-management', 'ai',
            'facilities', 'units.view',
            'reports', 'reports.finance', 'reports.kpi', 'profile', 'notifications',
        ],
        'finance_user' => [
            'dashboard', 'finance', 'finance.invoices', 'finance.expenses',
            'finance.petty_cash', 'finance.reimbursements', 'finance.ap', 'finance.amc',
            'leases', 'cheques', 'profile', 'notifications',
        ],
        'procurement_officer' => [
            'dashboard', 'procurement', 'vendors', 'quotations', 'inventory', 'reports', 'reports.procurement',
            'employee.contract.view', 'employee.assignment.view', 'profile', 'notifications',
        ],
        'client' => [
            'portal', 'dashboard', 'helpdesk', 'profile', 'notifications', 'finance.invoices',
        ],
        'tenant' => [
            'portal', 'dashboard', 'helpdesk', 'profile', 'notifications', 'finance.invoices',
        ],

        'cash_collector' => [
            'collector', 'dashboard', 'profile', 'notifications',
        ],
        'landlord' => [
            'dashboard', 'facilities', 'units.view', 'tenants', 'leases', 'finance.invoices', 'profile', 'notifications',
        ],
        'leasing_agent' => [
            'dashboard', 'facilities', 'units.view', 'units.create', 'tenants', 'leases', 'crm', 'sales', 'profile', 'notifications',
        ],
        'crm_agent' => [
            'dashboard', 'facilities', 'units.view', 'tenants', 'crm', 'sales', 'profile', 'notifications',
        ],
        'accountant' => [
            'dashboard', 'finance', 'finance.invoices', 'finance.expenses', 'finance.ledger', 'finance.payments',
            'leases', 'tenants', 'facilities', 'units.view', 'profile', 'notifications',
        ],
    ];

    /** @var array<string, string> */
    public const PERMISSION_LABELS = [
        'dashboard'              => 'Dashboard',
        'dashboard.kpi'          => 'KPI Analytics',
        'helpdesk'               => 'Help Desk',
        'workorders'             => 'Work Orders',
        'job-cards'              => 'Job Cards',
        'facilities'             => 'Facilities / Properties (view)',
        'facilities.create'      => 'Create Properties / Facilities',
        'facilities.edit'        => 'Edit Properties / Facilities',
        'units.create'           => 'Create Units',
        'units.view'             => 'View Units',
        'units.edit'             => 'Edit Units',
        'assets'                 => 'Assets',
        'employees'              => 'Workforce / Employee List',
        'employee.create'        => 'Create Employee',
        'employee.edit'          => 'Edit Employee',
        'employee.delete'        => 'Deactivate Employee',
        'attendance'             => 'Attendance (Check-in/out)',
        'attendance.view'        => 'View Attendance Reports',
        'attendance.adjust'      => 'Manual Attendance Adjustments',
        'attendance.approve'     => 'Approve Attendance Regularizations',
        'hr.settings'            => 'HR Settings',
        'hr.dashboard'           => 'HR Module Dashboard',
        'hr.document_expiry'     => 'HR Document Expiry Dashboard',
        'hr.contract_expiry'     => 'HR Contract Expiry Dashboard',
        'employee.documents.view'   => 'View Employee Documents',
        'employee.documents.upload' => 'Upload Employee Documents',
        'employee.identification.view' => 'View Employee ID Documents',
        'employee.salary.view'   => 'View Employee Salary/Bank',
        'employee.salary.edit'   => 'Edit Salary Structures',
        'advance.approve'        => 'Approve Salary Advances',
        'loan.approve'           => 'Approve Employee Loans',
        'payroll.process'        => 'Run Payroll',
        'payroll.approve'        => 'Approve & Lock Payroll',
        'payroll.unlock'         => 'Unlock Payroll (Audited)',
        'wps.generate'           => 'Generate WPS Files',
        'transfer.approve'       => 'Approve Employee Transfers',
        'settlement.approve'     => 'Approve Final Settlements',
        'employee.contract.view' => 'View Employment Contracts',
        'employee.contract.view_rate' => 'View Contract Commercial Rates',
        'employee.contract.edit' => 'Create/Edit Employment Contracts',
        'employee.contract.edit_rate' => 'Edit Contract Commercial Rates',
        'employee.assignment.view' => 'View Site Assignments',
        'employee.assignment.edit' => 'Create/Edit Site Assignments',
        'manpower.view'          => 'View Manpower Planning',
        'manpower.manage'        => 'Manage Manpower Requirements',
        'leave.view'             => 'View Leave',
        'leave.apply'            => 'Apply for Leave',
        'leave.approve'          => 'Approve Leave Requests',
        'inventory'              => 'Inventory',
        'procurement'            => 'Procurement',
        'vendors'                => 'Vendors',
        'finance'                => 'Finance Overview',
        'finance.invoices'       => 'Invoices',
        'finance.expenses'       => 'Expenses',
        'finance.petty_cash'     => 'Petty Cash',
        'finance.reimbursements' => 'Reimbursements',
        'finance.contracts'      => 'Contracts',
        'finance.ledger'         => 'Ledger',
        'finance.payments'       => 'Payments',
        'finance.coa'            => 'Chart of Accounts',
        'finance.gl'             => 'General Ledger',
        'finance.ap'             => 'Accounts Payable',
        'finance.amc'            => 'AMC Billing',
        'finance.budgets'        => 'Budgets',
        'finance.reports'        => 'Financial Reports Hub',
        'reports'                => 'Reports',
        'reports.finance'        => 'Finance Reports',
        'reports.kpi'            => 'KPI Reports',
        'reports.procurement'    => 'Procurement Reports',
        'compliance'             => 'Compliance',
        'utility'                => 'Utility & Energy',
        'estimations'            => 'Estimations',
        'costing'                => 'Maintenance Costing',
        'tenants'                => 'Tenants',
        'landlords'              => 'Landlords',
        'leases'                 => 'Leases / Contracts',
        'cheques'                => 'Cheques',
        'crm'                    => 'CRM',
        'sales'                  => 'Sales',
        'utilities'              => 'Utility Billing',
        'budgets'                => 'Property Budgeting',
        'cost-management'        => 'Cost Management',
        'offers'                 => 'Complimentary Offers',
        'media'                  => 'Media Albums',
        'ai'                     => 'AI Intelligence',
        'quotations'             => 'Vendor Quotations',
        'settings.users'         => 'Users & Settings',
        'settings.companies'     => 'Companies',
        'settings.roles'         => 'Roles & Permissions',
        'settings.workflow'      => 'Workflow Config',
        'settings.activity'      => 'Activity Log',
        'settings.login_history' => 'Login History',
        'notifications'          => 'Notifications',
        'profile'                => 'Profile',
        'portal'                 => 'Tenant Portal',
        'collector'              => 'Cash Collector',
    ];

    private ?array $overrides = null;

    public function __construct(private ?BaseConnection $db = null)
    {
    }

    /** @return list<string> */
    public static function allPermissionKeys(): array
    {
        return array_keys(self::PERMISSION_LABELS);
    }

    /** @return array<string, list<string>> */
    public function permissionsMap(): array
    {
        $map       = self::PERMISSIONS;
        $overrides = $this->loadOverrides();
        foreach ($overrides as $role => $perms) {
            if (is_array($perms)) {
                $map[$role] = $perms;
            }
        }

        return $map;
    }

    public function can(string $role, string $permission): bool
    {
        $perms = $this->permissionsMap()[$role] ?? $this->permissionsMap()['client'];

        if (in_array('*', $perms, true)) {
            return true;
        }

        // Exact match
        if (in_array($permission, $perms, true)) {
            return true;
        }

        $resolved = $this->resolvePermissionAlias($permission);
        if ($resolved !== $permission && in_array($resolved, $perms, true)) {
            return true;
        }

        // For finance.*, reports.*, settings.* — require exact match only (no parent prefix bleed)
        $strictPrefixes = ['finance.', 'reports.', 'settings.'];
        foreach ($strictPrefixes as $prefix) {
            if (str_starts_with($resolved, $prefix)) {
                return $this->canFromRolePermissions($role, $resolved);
            }
        }

        // For all other permissions, allow parent prefix match
        foreach ($perms as $p) {
            if (str_starts_with($resolved, $p . '.')) {
                return true;
            }
        }

        return $this->canFromRolePermissions($role, $resolved);
    }

    /**
     * Map granular FinanceBank / PettyCash keys onto existing role permissions.
     */
    private function resolvePermissionAlias(string $permission): string
    {
        $exact = [
            'finance.dashboard'      => 'finance',
            'finance.reconciliation' => 'finance.payments',
            'finance.audit_log'      => 'finance',
            'finance.settings'       => 'finance',
        ];
        if (isset($exact[$permission])) {
            return $exact[$permission];
        }

        $prefixes = [
            'finance.bank.'         => 'finance.payments',
            'finance.deposit.'      => 'finance.payments',
            'finance.withdrawal.'   => 'finance.payments',
            'finance.transfer.'     => 'finance.payments',
            'finance.income.'       => 'finance.payments',
            'finance.expense.'      => 'finance.payments',
            'finance.transaction.'  => 'finance.payments',
            'petty_cash.'           => 'finance.petty_cash',
        ];
        foreach ($prefixes as $prefix => $target) {
            if (str_starts_with($permission, $prefix) || $permission === rtrim($prefix, '.')) {
                return $target;
            }
        }

        return $permission;
    }

    /**
     * Permissions matrix (role_permissions table) — module-level view/create/edit/delete.
     */
    private function canFromRolePermissions(string $role, string $permission): bool
    {
        if ($this->db === null || ! $this->db->tableExists('role_permissions') || ! $this->db->tableExists('roles')) {
            return false;
        }

        $roleRow = $this->db->table('roles')->select('id')->where('name', $role)->get()->getRowArray();
        if (! $roleRow) {
            return false;
        }

        $mapped = $this->permissionToModuleAction($permission);
        if ($mapped === null) {
            return false;
        }

        $row = $this->db->table('role_permissions')
            ->where('role_id', (int) $roleRow['id'])
            ->where('module', $mapped['module'])
            ->get()->getRowArray();

        if (! $row) {
            return false;
        }

        $col = 'can_' . $mapped['action'];

        return isset($row[$col]) && (int) $row[$col] === 1;
    }

    /** @return array{module: string, action: string}|null */
    private function permissionToModuleAction(string $permission): ?array
    {
        static $exact = [
            'units.view'        => ['module' => 'units', 'action' => 'view'],
            'units.create'      => ['module' => 'units', 'action' => 'create'],
            'units.edit'        => ['module' => 'units', 'action' => 'edit'],
            'leases'            => ['module' => 'leases', 'action' => 'view'],
            'facilities'        => ['module' => 'facilities', 'action' => 'view'],
            'facilities.create' => ['module' => 'facilities', 'action' => 'create'],
            'facilities.edit'   => ['module' => 'facilities', 'action' => 'edit'],
            'tenants'           => ['module' => 'tenants', 'action' => 'view'],
        ];

        if (isset($exact[$permission])) {
            return $exact[$permission];
        }

        if (str_starts_with($permission, 'units.')) {
            $action = substr($permission, 6);

            return in_array($action, ['view', 'create', 'edit', 'delete'], true)
                ? ['module' => 'units', 'action' => $action]
                : ['module' => 'units', 'action' => 'view'];
        }

        if (str_starts_with($permission, 'leases') || str_starts_with($permission, 'contracts')) {
            return ['module' => 'leases', 'action' => 'view'];
        }

        return null;
    }

    /** @return list<string> */
    public function permissionsFor(string $role): array
    {
        return $this->permissionsMap()[$role] ?? $this->permissionsMap()['client'];
    }

    /** @param array<string, list<string>> $map */
    public function saveOverrides(array $map): void
    {
        if (!$this->db || !$this->db->tableExists('system_settings')) {
            return;
        }
        $json   = json_encode($map);
        $exists = $this->db->table('system_settings')->where('setting_key', 'rbac_overrides')->countAllResults();
        if ($exists) {
            $this->db->table('system_settings')->where('setting_key', 'rbac_overrides')->update(['setting_value' => $json]);
        } else {
            $this->db->table('system_settings')->insert(['setting_key' => 'rbac_overrides', 'setting_value' => $json]);
        }
        $this->overrides = null;
        cache()->delete('system_settings');
    }

    public function canAccessRoute(string $role, string $uri): bool
    {
        helper('fm');
        $uri = fm_normalize_route_path($uri);

        $routePermission = match (true) {
            $uri === '' || $uri === 'dashboard'              => 'dashboard',
            str_starts_with($uri, 'dashboard/kpi')          => 'dashboard.kpi',
            str_starts_with($uri, 'helpdesk')
                || str_starts_with($uri, 'complaints') => 'helpdesk',
            str_starts_with($uri, 'site-visits')            => 'workorders',
            str_starts_with($uri, 'workorders')
                || str_starts_with($uri, 'work-orders')       => 'workorders',
            str_starts_with($uri, 'job-cards')              => 'job-cards',
            str_starts_with($uri, 'properties/create')
                || str_starts_with($uri, 'facilities/create') => 'facilities.create',
            str_starts_with($uri, 'properties/edit')
                || str_starts_with($uri, 'facilities/edit')
                || preg_match('#^(properties|facilities)/\d+/update$#', $uri)
                || preg_match('#^properties/update/\d+$#', $uri) => 'facilities.edit',
            str_starts_with($uri, 'units/view')
                || str_starts_with($uri, 'properties/units/view')
                || preg_match('#^units/\d+/parking-contract#', $uri)
                || preg_match('#^properties/units/\d+/parking-contract#', $uri) => 'units.view',
            str_contains($uri, '/units/create')
                || preg_match('#^facilities/\d+/units/store$#', $uri)
                || preg_match('#^properties/\d+/units/store$#', $uri) => 'units.create',
            str_starts_with($uri, 'units/edit')
                || preg_match('#^units/update/\d+$#', $uri) => 'units.edit',
            str_starts_with($uri, 'contracts/sync-units') => 'leases',
            str_starts_with($uri, 'facilities')
                || str_starts_with($uri, 'properties')
                || str_starts_with($uri, 'units')           => 'facilities',
            str_starts_with($uri, 'tenants')                => 'tenants',
            str_starts_with($uri, 'landlords')
                || str_starts_with($uri, 'landlord-payouts') => 'landlords',
            str_starts_with($uri, 'contracts')
                || str_starts_with($uri, 'leases')
                || str_starts_with($uri, 'lease-contracts')
                || str_starts_with($uri, 'rent-payments') => 'leases',
            str_starts_with($uri, 'cheques')
                || str_starts_with($uri, 'outgoing-cheques') => 'cheques',
            str_starts_with($uri, 'crm')                    => 'crm',
            str_starts_with($uri, 'sales')                  => 'sales',
            str_starts_with($uri, 'utilities')              => 'utilities',
            str_starts_with($uri, 'budgets')                => 'budgets',
            str_starts_with($uri, 'cost-management')        => 'cost-management',
            str_starts_with($uri, 'complimentary-offers')   => 'offers',
            str_starts_with($uri, 'media')                  => 'media',
            str_starts_with($uri, 'ai')                     => 'ai',
            str_starts_with($uri, 'quotations')             => 'quotations',
            str_starts_with($uri, 'maintenance')            => 'helpdesk',
            str_starts_with($uri, 'asset-register')         => 'assets',
            str_starts_with($uri, 'hr/transfers/approve')             => 'transfer.approve',
            str_starts_with($uri, 'hr/transfers/reject')              => 'transfer.approve',
            str_starts_with($uri, 'hr/transfers/store')                 => 'employee.edit',
            str_starts_with($uri, 'hr/transfers')                       => 'employee.edit',
            str_starts_with($uri, 'hr/offboarding/settlement') && str_contains($uri, '/approve') => 'settlement.approve',
            str_starts_with($uri, 'hr/offboarding/settlement') || str_starts_with($uri, 'hr/offboarding/') && str_contains($uri, 'settlement/calculate') => 'employee.salary.view',
            str_starts_with($uri, 'hr/offboarding/clearance')    => 'employee.edit',
            str_starts_with($uri, 'hr/offboarding')                 => 'employees',
            str_starts_with($uri, 'hr/onboarding')                      => 'employee.edit',
            str_starts_with($uri, 'hr/approvals')                       => 'transfer.approve',
            str_starts_with($uri, 'hr/requests')                         => 'employees',
            $uri === 'hr' || str_starts_with($uri, 'hr/dashboard')              => 'hr.dashboard',
            str_starts_with($uri, 'hr/wps/download')                  => 'wps.generate',
            str_starts_with($uri, 'hr/wps/generate')                  => 'wps.generate',
            str_starts_with($uri, 'hr/wps')                           => 'wps.generate',
            str_starts_with($uri, 'hr/payroll/unlock')                => 'payroll.unlock',
            str_starts_with($uri, 'hr/payroll/post-gl')               => 'payroll.approve',
            str_starts_with($uri, 'hr/payroll/approve')               => 'payroll.approve',
            str_starts_with($uri, 'hr/payroll/lock')                  => 'payroll.approve',
            str_starts_with($uri, 'hr/payroll/calculate')             => 'payroll.process',
            str_starts_with($uri, 'hr/payroll/create')                => 'payroll.process',
            str_starts_with($uri, 'hr/payroll/cancel')                => 'payroll.process',
            str_starts_with($uri, 'hr/payroll')                       => 'payroll.process',
            str_starts_with($uri, 'hr/compensation/advances/approve') => 'advance.approve',
            str_starts_with($uri, 'hr/compensation/advances/reject')  => 'advance.approve',
            str_starts_with($uri, 'hr/compensation/advances/store')   => 'employee.salary.view',
            str_starts_with($uri, 'hr/compensation/advances')         => 'employee.salary.view',
            str_starts_with($uri, 'hr/compensation/loans/approve')    => 'loan.approve',
            str_starts_with($uri, 'hr/compensation/loans/reject')     => 'loan.approve',
            str_starts_with($uri, 'hr/compensation/loans/store')      => 'employee.salary.view',
            str_starts_with($uri, 'hr/compensation/loans')            => 'employee.salary.view',
            str_starts_with($uri, 'hr/salary/store')                  => 'employee.salary.edit',
            str_starts_with($uri, 'hr/salary')                        => 'employee.salary.view',
            str_starts_with($uri, 'hr/leave/approvals')       => 'leave.approve',
            str_starts_with($uri, 'hr/leave/approve')         => 'leave.approve',
            str_starts_with($uri, 'hr/leave/reject')          => 'leave.approve',
            str_starts_with($uri, 'hr/leave/init-balances')   => 'leave.approve',
            str_starts_with($uri, 'hr/leave/apply')           => 'leave.apply',
            str_starts_with($uri, 'hr/leave')                 => 'leave.view',
            str_starts_with($uri, 'hr/attendance/regularizations/approve') => 'attendance.approve',
            str_starts_with($uri, 'hr/attendance/regularizations/reject')  => 'attendance.approve',
            str_starts_with($uri, 'hr/attendance/regularize')              => 'attendance.adjust',
            str_starts_with($uri, 'hr/attendance/adjust')                  => 'attendance.adjust',
            str_starts_with($uri, 'hr/attendance/regularizations')         => 'attendance.view',
            str_starts_with($uri, 'hr/attendance')                         => 'attendance.view',
            str_starts_with($uri, 'hr/shifts')                             => 'attendance.adjust',
            str_starts_with($uri, 'hr/manpower/store')    => 'manpower.manage',
            str_starts_with($uri, 'hr/manpower')          => 'manpower.view',
            str_starts_with($uri, 'hr/assignments')       => 'employee.assignment.edit',
            str_starts_with($uri, 'hr/contracts/expiry') => 'hr.contract_expiry',
            str_starts_with($uri, 'hr/contracts')         => 'employee.contract.edit',
            str_starts_with($uri, 'hr/documents')         => 'hr.document_expiry',
            str_starts_with($uri, 'hr/employees')            => 'employees',
            str_starts_with($uri, 'hr/expenses')             => 'finance.expenses',
            str_starts_with($uri, 'hr/performance')          => 'employees',
            str_starts_with($uri, 'hr/assets')               => 'employees',
            str_starts_with($uri, 'employees/create')     => 'employee.create',
            str_starts_with($uri, 'employees/store')      => 'employee.create',
            str_starts_with($uri, 'employees/edit')       => 'employee.edit',
            str_starts_with($uri, 'employees/update')     => 'employee.edit',
            str_starts_with($uri, 'employees/delete')     => 'employee.delete',
            str_starts_with($uri, 'employees')              => 'employees',
            str_starts_with($uri, 'attendance')           => 'attendance',
            str_starts_with($uri, 'inventory')              => 'inventory',
            str_starts_with($uri, 'procurement')
                || str_starts_with($uri, 'purchase-orders')
                || str_starts_with($uri, 'purchase-requests')
                || str_starts_with($uri, 'rfq')             => 'procurement',
            str_starts_with($uri, 'vendors')                => 'vendors',
            str_starts_with($uri, 'estimations')            => 'estimations',
            str_starts_with($uri, 'costing')                => 'costing',
            str_starts_with($uri, 'finance-bank')           => 'finance.payments',
            str_starts_with($uri, 'finance-petty')          => 'finance.petty_cash',
            str_starts_with($uri, 'finance/hub')            => 'finance',
            str_starts_with($uri, 'finance/coa')            => 'finance.coa',
            str_starts_with($uri, 'finance/gl')             => 'finance.gl',
            str_starts_with($uri, 'finance/vendor-bills')   => 'finance.ap',
            str_starts_with($uri, 'finance/amc-billing')    => 'finance.amc',
            str_starts_with($uri, 'finance/budgets')        => 'finance.budgets',
            str_starts_with($uri, 'finance/reports')        => 'finance.reports',
            str_starts_with($uri, 'finance/integration-log') => 'finance.gl',
            str_starts_with($uri, 'finance/ledger')         => 'finance.ledger',
            str_starts_with($uri, 'finance/trial-balance')
                || str_starts_with($uri, 'finance/balance-sheet')
                || str_starts_with($uri, 'finance/ar-aging')
                || str_starts_with($uri, 'finance/bank-reconciliation')
                || str_starts_with($uri, 'finance/payroll-finance')
                || str_starts_with($uri, 'finance/budgets') => 'finance.reports',
            str_starts_with($uri, 'finance/cash-flow')      => 'finance.ledger',
            str_starts_with($uri, 'finance/payments')       => 'finance.payments',
            str_starts_with($uri, 'payments')
                || str_starts_with($uri, 'rent-payments') => 'leases',
            str_starts_with($uri, 'finance/petty-cash')     => 'finance.petty_cash',
            str_starts_with($uri, 'finance/reimbursements') => 'finance.reimbursements',
            str_starts_with($uri, 'finance/invoices')       => 'finance.invoices',
            str_starts_with($uri, 'finance/contracts')      => 'finance.contracts',
            str_starts_with($uri, 'finance/expenses')       => 'finance.expenses',
            str_starts_with($uri, 'finance')                => 'finance',
            str_starts_with($uri, 'reports/activity-log')  => 'settings.activity',
            str_starts_with($uri, 'reports/kpi')            => 'reports.kpi',
            str_starts_with($uri, 'reports/finance')        => 'reports.finance',
            str_starts_with($uri, 'reports/procurement')    => 'reports.procurement',
            str_starts_with($uri, 'reports/builder')
                || str_starts_with($uri, 'reports/profit')
                || str_starts_with($uri, 'reports/qc')    => 'reports',
            str_starts_with($uri, 'reports')                => 'reports',
            str_starts_with($uri, 'compliance')             => 'compliance',
            str_starts_with($uri, 'utility')                => 'utility',
            str_starts_with($uri, 'settings/companies')     => 'settings.companies',
            str_starts_with($uri, 'settings/roles')         => 'settings.roles',
            str_starts_with($uri, 'settings/finance-module') => 'settings.users',
            str_starts_with($uri, 'settings/workflow')      => 'settings.workflow',
            str_starts_with($uri, 'settings/workspaces')    => 'settings.roles',
            str_starts_with($uri, 'settings/contract-templates') => 'settings.workflow',
            str_starts_with($uri, 'settings/activity-log')  => 'settings.activity',
            str_starts_with($uri, 'settings/login-history') => 'settings.login_history',
            str_starts_with($uri, 'settings')               => 'settings.users',
            str_starts_with($uri, 'notifications')          => 'notifications',
            str_starts_with($uri, 'profile')                => 'profile',
            str_starts_with($uri, 'ajax')                   => 'dashboard',
            str_starts_with($uri, 'portal')                 => 'portal',
            str_starts_with($uri, 'collector')              => 'collector',
            default                                         => 'dashboard',
        };

        return $this->can($role, $routePermission);
    }

    /** @return array<string, list<string>> */
    private function loadOverrides(): array
    {
        if ($this->overrides !== null) {
            return $this->overrides;
        }
        $this->overrides = [];
        if (!$this->db || !$this->db->tableExists('system_settings')) {
            return $this->overrides;
        }
        $row = $this->db->table('system_settings')->where('setting_key', 'rbac_overrides')->get()->getRowArray();
        if ($row && !empty($row['setting_value'])) {
            $decoded = json_decode($row['setting_value'], true);
            if (is_array($decoded)) {
                $this->overrides = $decoded;
            }
        }

        return $this->overrides;
    }
}