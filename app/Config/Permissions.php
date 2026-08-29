<?php

namespace Config;

/**
 * Role-based permission map.
 *
 * Each role lists the controller::method pairs it may access.
 * Use '*' as a wildcard for "all methods on that controller".
 * A missing entry = access denied.
 */
class Permissions
{
    /**
     * Returns the flat list of allowed routes for a given role slug.
     * Routes are expressed as 'Controller::method' (case-insensitive on method).
     */
    public static array $map = [

        'super_admin' => ['*'],   // unrestricted

        'facility_manager' => [
            'Dashboard::index',
            'WorkOrders::*',
            'JobCards::*',
            'Helpdesk::*',
            'Facilities::index',
            'Facilities::show',
            'Assets::*',
            'Employees::*',
            'HrEmployees::*',
            'HrExpenses::*',
            'HrPerformance::*',
            'HrAssets::*',
            'Finance::*',
            'Finance::*',
            'Finance::*',
            'Finance::*',
            'Finance::*',
            'FinanceBank::*',
            'FinancePettyCash::*',
            'Vendors::*',
            'Inventory::*',
            'Reports::*',
            'Compliance::*',
            'Incidents::*',
            'Compliance::*',
            'Estimations::*',
            'Notifications::*',
        ],

        'supervisor' => [
            'Dashboard::index',
            'WorkOrders::index',
            'WorkOrders::show',
            'WorkOrders::assignedToMe',
            'WorkOrders::createJobCard',
            'WorkOrders::updateStage',
            'WorkOrders::scheduleWork',
            'JobCards::*',
            'Helpdesk::index',
            'Helpdesk::show',
            'Notifications::*',
        ],

        'technician' => [
            'Dashboard::index',
            'WorkOrders::index',
            'WorkOrders::show',
            'WorkOrders::myWork',
            'WorkOrders::updateExecution',
            'JobCards::index',
            'JobCards::show',
            'JobCards::updateProgress',
            'JobCards::complete',
            'Notifications::index',
            'Notifications::markRead',
        ],

        'client' => [
            'Dashboard::index',
            'Helpdesk::index',
            'Helpdesk::create',
            'Helpdesk::store',
            'Helpdesk::show',
            'WorkOrders::clientView',
            'Notifications::index',
            'Notifications::markRead',
        ],

        'finance_manager' => [
            'Dashboard::index',
            'Finance::*',
            'Finance::*',
            'Finance::*',
            'Finance::*',
            'Finance::*',
            'FinanceBank::*',
            'FinancePettyCash::*',
            'Leases::*',
            'Landlords::*',
            'Tenants::*',
            'PmModules::*',
            'WorkOrders::index',
            'WorkOrders::show',
            'Reports::*',
            'PmReports::*',
            'Notifications::*',
        ],

        'finance_user' => [
            'Dashboard::index',
            'Finance::invoices',
            'Finance::viewInvoice',
            'Finance::expenses',
            'Finance::expenses',
            'Finance::pettyCash',
            'Finance::viewPettyCash',
            'FinanceBank::index',
            'FinancePettyCash::*',
            'Finance::reimbursements',
            'Finance::reimbursements',
            'Notifications::index',
            'Notifications::markRead',
        ],

        'procurement_officer' => [
            'Dashboard::index',
            'Vendors::*',
            'Inventory::*',
            'Procurement::*',
            'PurchaseRequests::*',
            'Rfq::*',
            'Notifications::*',
        ],

        'property_manager' => [
            'Dashboard::index',
            'Facilities::*',
            'Units::*',
            'Landlords::*',
            'Tenants::*',
            'Leases::*',
            'Payments::*',
            'Cheques::*',
            'OutgoingCheques::*',
            'Crm::*',
            'Sales::*',
            'ComplimentaryOffers::*',
            'UtilityBilling::*',
            'Budgeting::*',
            'CostManagement::*',
            'Finance::*',
            'FinanceBank::*',
            'FinancePettyCash::*',
            'PmReports::*',
            'PmModules::*',
            'Reports::*',
            'Documents::*',
            'Helpdesk::*',
            'Notifications::*',
            'Collector::assign',
            'Collector::assignBulk',
            'Collector::handoff',
            'Collector::acknowledgeHandoff',
        ],

        'cash_collector' => [
            'Dashboard::index',
            'Collector::*',
            'Profile::*',
            'Notifications::*',
        ],

        'landlord' => [
            'Dashboard::index',
            'PmReports::landlord',
            'PmReports::landlordExport',
            'Facilities::index',
            'Facilities::show',
            'Units::index',
            'Units::view',
            'Tenants::index',
            'Tenants::show',
            'Leases::index',
            'Leases::show',
            'Profile::*',
            'Notifications::*',
        ],
    ];

    /**
     * Check if a role is allowed to access a controller/method pair.
     */
    public static function can(string $role, string $controller, string $method): bool
    {
        if (! isset(self::$map[$role])) {
            return false;
        }

        $allowed = self::$map[$role];

        // Super-admin wildcard
        if (in_array('*', $allowed, true)) {
            return true;
        }

        $controllerWild = $controller . '::*';
        $exact          = $controller . '::' . $method;

        return in_array($controllerWild, $allowed, true)
            || in_array($exact, $allowed, true);
    }
}
