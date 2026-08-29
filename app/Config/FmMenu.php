<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class FmMenu extends BaseConfig
{
    /** @var list<array<string, mixed>> */
    public array $items = [
        ['type' => 'heading', 'label' => 'Facility Management'],
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'url' => 'dashboard'],
        ['key' => 'dashboard_kpi', 'label' => 'KPI Analytics', 'icon' => 'bi-graph-up-arrow', 'url' => 'dashboard/kpi'],
        ['key' => 'properties', 'label' => 'Properties', 'icon' => 'bi-building', 'url' => 'properties'],
        ['key' => 'units', 'label' => 'Units', 'icon' => 'bi-door-closed', 'url' => 'units'],

        ['type' => 'heading', 'label' => 'Operations'],
        ['key' => 'maintenance', 'label' => 'Maintenance', 'icon' => 'bi-tools', 'url' => 'maintenance/list'],
        ['key' => 'scan', 'label' => 'QR Scanner', 'icon' => 'bi-qr-code-scan', 'url' => 'scan'],
        ['key' => 'helpdesk', 'label' => 'Complaints', 'icon' => 'bi-headset', 'url' => 'helpdesk'],
        ['key' => 'workorders', 'label' => 'Work Orders', 'icon' => 'bi-clipboard2-check', 'url' => 'workorders'],
        ['key' => 'jobcards', 'label' => 'Job Cards', 'icon' => 'bi-card-checklist', 'url' => 'job-cards'],
        ['key' => 'estimations', 'label' => 'Estimations', 'icon' => 'bi-calculator', 'url' => 'estimations'],

        ['type' => 'heading', 'label' => 'Assets & Compliance'],
        ['key' => 'assets', 'label' => 'Assets', 'icon' => 'bi-box-seam', 'url' => 'assets'],
        ['key' => 'inspections', 'label' => 'Inspections', 'icon' => 'bi-clipboard2-pulse', 'url' => 'compliance/inspections'],
        ['key' => 'unit_inspections', 'label' => 'Move In / Out', 'icon' => 'bi-door-open', 'url' => 'compliance/unit-inspections'],
        ['key' => 'compliance', 'label' => 'Compliance', 'icon' => 'bi-shield-check', 'url' => 'compliance'],
        ['key' => 'media', 'label' => 'Media Albums', 'icon' => 'bi-images', 'url' => 'media'],

        ['type' => 'heading', 'label' => 'Supply Chain'],
        ['key' => 'vendors', 'label' => 'Vendors', 'icon' => 'bi-shop', 'url' => 'vendors'],
        ['key' => 'inventory', 'label' => 'Inventory', 'icon' => 'bi-boxes', 'url' => 'inventory'],
        ['key' => 'procurement', 'label' => 'Procurement', 'icon' => 'bi-bag-check', 'url' => 'procurement'],
        ['key' => 'costing', 'label' => 'Costing', 'icon' => 'bi-currency-exchange', 'url' => 'costing'],
        ['key' => 'utility', 'label' => 'Utility & Energy', 'icon' => 'bi-lightning-charge', 'url' => 'utility'],

        ['type' => 'heading', 'label' => 'Reports & Analytics'],
        ['key' => 'reports', 'label' => 'Reports', 'icon' => 'bi-bar-chart-line', 'url' => 'reports'],
        ['key' => 'ai_reports', 'label' => 'AI Reports', 'icon' => 'bi-robot', 'url' => 'ai/reports'],
    ];
}
