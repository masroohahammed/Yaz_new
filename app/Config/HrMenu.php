<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Human Resources navigation (merged into PM/FM/admin sidebars).
 */
class HrMenu extends BaseConfig
{
    /** @var list<array<string, mixed>> */
    public array $items = [
        ['type' => 'heading', 'label' => 'Human Resources'],
        ['key' => 'hr_dashboard', 'label' => 'HR Dashboard', 'icon' => 'bi-speedometer2', 'url' => 'hr/dashboard'],
        ['key' => 'employees', 'label' => 'Employees', 'icon' => 'bi-people-fill', 'url' => 'employees'],
        ['key' => 'hr_employees_mod', 'label' => 'HR Employee 360', 'icon' => 'bi-person-vcard', 'url' => 'hr/employees'],
        ['key' => 'hr_expenses', 'label' => 'HR Expenses', 'icon' => 'bi-receipt', 'url' => 'hr/expenses'],
        ['key' => 'hr_performance', 'label' => 'Performance', 'icon' => 'bi-graph-up-arrow', 'url' => 'hr/performance'],
        ['key' => 'hr_assets', 'label' => 'HR Assets', 'icon' => 'bi-laptop', 'url' => 'hr/assets'],
        ['key' => 'hr_attendance', 'label' => 'Attendance', 'icon' => 'bi-clock-history', 'url' => 'hr/attendance'],
        ['key' => 'hr_leave', 'label' => 'Leave', 'icon' => 'bi-calendar2-week', 'url' => 'hr/leave'],
        ['key' => 'hr_payroll', 'label' => 'Payroll', 'icon' => 'bi-calculator', 'url' => 'hr/payroll'],
        ['key' => 'hr_manpower', 'label' => 'Manpower', 'icon' => 'bi-diagram-3', 'url' => 'hr/manpower'],
        ['key' => 'hr_onboarding', 'label' => 'Onboarding', 'icon' => 'bi-person-plus', 'url' => 'hr/onboarding'],
        ['key' => 'hr_settings', 'label' => 'HR Settings', 'icon' => 'bi-gear', 'url' => 'hr/settings'],
    ];
}
