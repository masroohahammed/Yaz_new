<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Sidebar navigation items for the cash_collector workspace.
 */
class CollectorMenu extends BaseConfig
{
    /** @var list<array<string, mixed>> */
    public array $items = [
        ['type' => 'heading', 'label' => 'Field Collection'],
        ['key' => 'collector',      'label' => 'Dashboard',       'icon' => 'bi-speedometer2',       'url' => 'collector'],
        ['key' => 'session',        'label' => 'My Session',       'icon' => 'bi-calendar-check',     'url' => 'collector/session'],
        ['key' => 'search',         'label' => 'Collect Payment',  'icon' => 'bi-cash-coin',          'url' => 'collector/search'],
        ['key' => 'assignments',    'label' => 'Assignments',      'icon' => 'bi-list-task',          'url' => 'collector/assignments'],

        ['type' => 'heading', 'label' => 'Records'],
        ['key' => 'history',        'label' => 'History',          'icon' => 'bi-clock-history',      'url' => 'collector/history'],
        ['key' => 'handoff',        'label' => 'Cash Handoff',     'icon' => 'bi-bag-check',          'url' => 'collector/handoff'],
        ['key' => 'report',         'label' => 'Daily Report',     'icon' => 'bi-printer',            'url' => 'collector/report'],

        ['type' => 'heading', 'label' => 'Account'],
        ['key' => 'profile',        'label' => 'Profile',          'icon' => 'bi-person-circle',      'url' => 'profile'],
    ];
}
