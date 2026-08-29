<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Sidebar menu for the Tenant / Client portal workspace.
 */
class PortalMenu extends BaseConfig
{
    /** @var list<array<string, mixed>> */
    public array $items = [
        ['type' => 'heading', 'label' => 'My Portal'],
        ['key' => 'portal',          'label' => 'Dashboard',        'icon' => 'bi-speedometer2',      'url' => 'portal'],
        ['key' => 'portal_leases',   'label' => 'My Leases',        'icon' => 'bi-file-earmark-text', 'url' => 'portal/leases'],
        ['key' => 'portal_payments', 'label' => 'Payments / Invoices', 'icon' => 'bi-credit-card',   'url' => 'portal/payments'],
        ['key' => 'portal_tickets',  'label' => 'Maintenance',      'icon' => 'bi-tools',             'url' => 'portal/tickets'],
        ['key' => 'portal_new_ticket', 'label' => 'New Ticket',     'icon' => 'bi-plus-circle',       'url' => 'portal/tickets/create'],

        ['type' => 'heading', 'label' => 'Account'],
        ['key' => 'profile',         'label' => 'Profile',          'icon' => 'bi-person-circle',     'url' => 'profile'],
        ['key' => 'notifications',   'label' => 'Notifications',    'icon' => 'bi-bell',              'url' => 'notifications'],
    ];
}
