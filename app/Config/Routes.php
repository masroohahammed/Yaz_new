<?php

use CodeIgniter\Router\RouteCollection;

/**
 * FM ERP — Application routes
 *
 * URLs match sidebar navigation and base_url() usage in views.
 * Protected routes use auth + rbac filters.
 *
 * @var RouteCollection $routes
 */

// ── Public ───────────────────────────────────────────────────────────────────
$routes->get('/', static function () {
    return redirect()->to(session()->get('user_id') ? '/dashboard' : '/login');
});

$routes->get('login',           'Auth::login');
$routes->post('login',          'Auth::doLogin', ['filter' => 'loginThrottle']);
$routes->get('logout',          'Auth::logout');
$routes->get('register',        'Auth::register');
$routes->post('register',       'Auth::doRegister');
$routes->get('auth/login',      'Auth::login');
$routes->post('auth/login',     'Auth::doLogin', ['filter' => 'loginThrottle']);
$routes->get('auth/logout',     'Auth::logout');
$routes->get('auth/register',   'Auth::register');
$routes->post('auth/register',  'Auth::doRegister');
$routes->get('auth/mfa',        'Auth::mfaVerify');
$routes->post('auth/mfa',       'Auth::mfaVerify');

$routes->get('helpdesk/submit',  'Helpdesk::create');
$routes->post('helpdesk/submit', 'Helpdesk::store');
$routes->get('request',                  'PublicRequest::index');
$routes->post('request',                 'PublicRequest::submit');
$routes->get('request/units/(:num)',     'PublicRequest::unitsForFacility/$1');
$routes->get('track/(:segment)',         'PublicRequest::track/$1');

// Public entity maintenance — URI is "maintenance" when app.baseURL is https://domain/public/
// (production pfms.alyazwa.com). Legacy "public/maintenance" alias for root baseURL dev setups.
$routes->get('maintenance',                 'PublicMaintenance::index');
$routes->get('maintenance/ping',            'PublicMaintenance::ping');
$routes->get('maintenance-ping',            'PublicMaintenance::ping');
$routes->post('maintenance',                'PublicMaintenance::submit');
$routes->get('public/maintenance',          'PublicMaintenance::index');
$routes->get('public/maintenance/ping',     'PublicMaintenance::ping');
$routes->get('public/maintenance-ping',     'PublicMaintenance::ping');
$routes->post('public/maintenance',         'PublicMaintenance::submit');
$routes->get('public/inspections',          'PublicEntity::inspections');

// Asset QR scan (public deep links)
$routes->get('scan/asset/(:segment)',           'AssetScan::byToken/$1');
$routes->get('scan/asset/id/(:num)',            'AssetScan::byId/$1');
$routes->post('scan/asset/(:segment)/complaint','AssetScan::submitComplaint/$1');

// Property & unit QR scan (public deep links)
$routes->get('scan/property/(:segment)',              'EntityScan::propertyByToken/$1');
$routes->get('scan/property/id/(:num)',               'EntityScan::propertyById/$1');
$routes->post('scan/property/(:segment)/complaint',   'EntityScan::propertyComplaint/$1');
$routes->get('scan/unit/(:segment)',                  'EntityScan::unitByToken/$1');
$routes->get('scan/unit/id/(:num)',                   'EntityScan::unitById/$1');
$routes->post('scan/unit/(:segment)/complaint',       'EntityScan::unitComplaint/$1');

$routes->get('file/logo/(:segment)', 'FileServe::logo/$1');
$routes->get('file/logos/(:segment)', 'FileServe::serve/logos/$1');

// ── Authenticated ────────────────────────────────────────────────────────────
$routes->group('', ['filter' => ['auth', 'rbac', 'workspace']], static function ($routes) {

    $routes->get('dashboard',      'Dashboard::index');
    $routes->get('dashboard/kpi',  'Dashboard::kpi');
    $routes->get('scan',           'Scan::index');

    // PM/FM maintenance history (read-only in PM) — not Helpdesk workflow
    $routes->get('maintenance/list', 'MaintenanceList::index');

    // Shared property URLs (facilities alias per ERP spec)
    $routes->get('properties',                    'Facilities::index');
    $routes->get('properties/create',             'Facilities::create');
    $routes->post('properties',                   'Facilities::store');
    $routes->get('properties/view/(:num)',        'Facilities::show/$1');
    $routes->get('properties/(:num)',             'Facilities::show/$1');
    $routes->get('properties/edit/(:num)',        'Facilities::edit/$1');
    $routes->post('properties/update/(:num)',     'Facilities::update/$1');
    $routes->post('properties/(:num)/update',     'Facilities::update/$1');
    $routes->post('properties/delete/(:num)',     'Facilities::delete/$1');
    $routes->get('properties/(:num)/units',       'Units::index/$1');
    $routes->get('properties/(:num)/units/create', 'Units::create/$1');
    $routes->post('properties/(:num)/units/store', 'Units::store/$1');
    $routes->get('properties/units/view/(:num)', 'Units::view/$1');
    $routes->get('properties/units/(:num)/parking-contract', 'Units::parkingContract/$1');
    $routes->get('properties/units/(:num)/parking-contract/print', 'Units::parkingContractPrint/$1');
    $routes->post('properties/units/(:num)/parking-contract/print', 'Units::parkingContractPrint/$1');
    $routes->get('properties/qrcode/(:num)',        'Facilities::qrcode/$1');
    $routes->get('units/qrcode/(:num)',             'Units::qrcode/$1');

    // Maintenance staff routes (list is helpdesk; public entity form is maintenance — no auth)
    $routes->get('maintenance/create',            'Helpdesk::create');
    $routes->get('maintenance/view/(:num)',       'Helpdesk::show/$1');
    $routes->get('maintenance/(:num)',            'Helpdesk::show/$1');

    // Helpdesk
    $routes->get('helpdesk/ajax/units/(:num)',   'Helpdesk::ajaxUnitsForFacility/$1');
    $routes->get('helpdesk',                    'Helpdesk::index');
    $routes->get('helpdesk/create',             'Helpdesk::create');
    $routes->post('helpdesk',                   'Helpdesk::store');
    $routes->get('helpdesk/view/(:num)',        'Helpdesk::show/$1');
    $routes->get('helpdesk/(:num)',             'Helpdesk::show/$1');
    $routes->post('helpdesk/verify/(:num)',     'Helpdesk::verify/$1');
    $routes->post('helpdesk/approve/(:num)',    'Helpdesk::approve/$1');
    $routes->post('helpdesk/reject/(:num)',     'Helpdesk::reject/$1');
    $routes->post('helpdesk/convert/(:num)',    'Helpdesk::convertToWo/$1');
    $routes->post('helpdesk/(:num)/verify',     'Helpdesk::verify/$1');
    $routes->post('helpdesk/(:num)/approve',    'Helpdesk::approve/$1');
    $routes->post('helpdesk/(:num)/convert',    'Helpdesk::convertToWo/$1');

    // Work orders (canonical URLs used by sidebar/views)
    $routes->get('workorders',                        'WorkOrders::index');
    $routes->get('workorders/create',                 'WorkOrders::create');
    $routes->post('workorders',                       'WorkOrders::store');
    $routes->get('workorders/view/(:num)',            'WorkOrders::view/$1');
    $routes->get('workorders/edit/(:num)',            'WorkOrders::edit/$1');
    $routes->post('workorders/update/(:num)',         'WorkOrders::update/$1');
    $routes->post('workorders/delete/(:num)',         'WorkOrders::delete/$1');
    $routes->get('workorders/schedule',               'WorkOrders::schedulePage');
    $routes->post('workorders/assign-supervisor/(:num)', 'WorkOrders::assignSupervisor/$1');
    $routes->post('workorders/schedule/(:num)',       'WorkOrders::schedule/$1');
    $routes->post('workorders/submit-qc/(:num)',      'WorkOrders::submitQc/$1');
    $routes->post('workorders/qc/(:num)',             'WorkOrders::approveQc/$1');
    $routes->post('workorders/qa-approve/(:num)',      'WorkOrders::qaApprove/$1');
    $routes->get('workorders/prepare-invoice/(:num)', 'WorkOrders::prepareInvoice/$1');
    $routes->post('workorders/prepare-invoice/(:num)', 'WorkOrders::storePreparedInvoice/$1');
    $routes->post('workorders/qa-reject/(:num)',      'WorkOrders::qaReject/$1');
    $routes->post('workorders/client-approve/(:num)',  'WorkOrders::clientApprove/$1');
    $routes->post('workorders/client-reject/(:num)',   'WorkOrders::clientReject/$1');
    $routes->post('workorders/close/(:num)',          'WorkOrders::close/$1');
    $routes->post('workorders/comment/(:num)',         'WorkOrders::addComment/$1');
    $routes->post('workorders/approve/(:num)',         'WorkOrders::approve/$1');
    $routes->post('workorders/reject/(:num)',          'WorkOrders::reject/$1');
    $routes->get('workorders/escalate/(:num)',         'WorkOrders::escalate/$1');
    $routes->post('workorders/labor/add/(:num)',       'WorkOrders::addLabor/$1');
    $routes->get('workorders/labor/delete/(:num)',     'WorkOrders::deleteLabor/$1');
    $routes->post('workorders/material/add/(:num)',    'WorkOrders::addMaterial/$1');
    $routes->get('workorders/material/delete/(:num)',  'WorkOrders::deleteMaterial/$1');
    $routes->post('workorders/meter/(:num)',           'WorkOrders::addMeter/$1');
    $routes->post('workorders/upload/(:num)',         'WorkOrders::upload/$1');
    $routes->post('workorders/status',                'WorkOrders::quickStatus');
    $routes->post('workorders/sync-job-cards/(:num)', 'WorkOrders::syncJobCardCosts/$1');

    // Work order invoice prep (was 404)

    // Accounts Payable (was 404 — maps to finance/accounts-payable)
    $routes->get('finance/accounts-payable',           'Finance::accountsPayable');
    $routes->post('finance/accounts-payable/pay/(:num)','Finance::payAccountsPayable/$1');

    // GRN index (was 404 — /procurement/grn needs a list endpoint)
    $routes->get('procurement/grn',                    'Procurement::grnIndex');

    // Site visits inside work order
    $routes->post('workorders/(:num)/site-visit',      'SiteVisits::storeForWo/$1');

    // Reports (legacy aliases only — canonical routes defined in the Reports block below)
    $routes->get('reports/procurement', 'Reports::procurement');
    $routes->get('reports/inventory',   'Reports::inventory');
    $routes->get('reports/activity',    'Reports::activityLog');
    $routes->get('reports/export/(:segment)/(:segment)', 'Reports::export/$1/$2');


    $routes->post('workorders/ajax/assign-supervisor/(:num)', 'WorkOrders::ajaxAssignSupervisor/$1');
    $routes->post('workorders/ajax/quick-status/(:num)',      'WorkOrders::ajaxQuickStatus/$1');
    $routes->post('workorders/ajax/approve/(:num)',           'WorkOrders::ajaxApprove/$1');
    $routes->post('workorders/ajax/escalate/(:num)',          'WorkOrders::ajaxEscalate/$1');
    $routes->get('workorders/ajax/actions/(:num)',            'WorkOrders::ajaxActionsPanel/$1');


    // Legacy hyphenated aliases
    $routes->get('work-orders',                    'WorkOrders::index');
    $routes->get('work-orders/create',             'WorkOrders::create');
    $routes->post('work-orders',                   'WorkOrders::store');
    $routes->get('work-orders/(:num)',             'WorkOrders::view/$1');
    $routes->get('work-orders/(:num)/edit',         'WorkOrders::edit/$1');
    $routes->post('work-orders/(:num)/update',     'WorkOrders::update/$1');
    $routes->post('work-orders/(:num)/delete',     'WorkOrders::delete/$1');
    $routes->post('work-orders/(:num)/assign-supervisor', 'WorkOrders::assignSupervisor/$1');
    $routes->post('work-orders/(:num)/schedule',   'WorkOrders::schedule/$1');
    $routes->post('work-orders/(:num)/submit-qc',  'WorkOrders::submitQc/$1');
    $routes->post('work-orders/(:num)/qc',          'WorkOrders::approveQc/$1');
    $routes->post('work-orders/(:num)/close',       'WorkOrders::close/$1');
    $routes->post('work-orders/(:num)/comment',     'WorkOrders::addComment/$1');

    // Job cards
    $routes->get('job-cards',                     'JobCards::index');
    $routes->get('job-cards/create/(:num)',       'JobCards::create/$1');
    $routes->post('job-cards/(:num)',             'JobCards::store/$1');
    $routes->get('job-cards/view/(:num)',          'JobCards::show/$1');
    $routes->get('job-cards/edit/(:num)',         'JobCards::edit/$1');
    $routes->get('job-cards/(:num)/edit',         'JobCards::edit/$1');
    $routes->post('job-cards/update/(:num)',      'JobCards::update/$1');
    $routes->post('job-cards/(:num)/update',      'JobCards::update/$1');
    $routes->post('job-cards/material/add/(:num)', 'JobCards::addMaterial/$1');
    $routes->post('job-cards/(:num)/materials',   'JobCards::addMaterial/$1');
    $routes->get('job-cards/(:num)/print',        'JobCardsPrint::printCard/$1');
    $routes->get('job-cards/(:num)',              'JobCards::show/$1');
    $routes->post('job-cards/(:num)/start',       'JobCards::startWork/$1');
    $routes->post('job-cards/(:num)/complete',    'JobCards::complete/$1');
    $routes->post('job-cards/(:num)/approve',     'JobCards::approve/$1');

    // Facilities & units
    $routes->get('facilities',                    'Facilities::index');
    $routes->get('facilities/create',             'Facilities::create');
    $routes->post('facilities',                   'Facilities::store');
    $routes->get('facilities/view/(:num)',        'Facilities::show/$1');
    $routes->get('facilities/(:num)',             'Facilities::show/$1');
    $routes->get('facilities/edit/(:num)',        'Facilities::edit/$1');
    $routes->post('facilities/update/(:num)',     'Facilities::update/$1');
    $routes->post('facilities/(:num)/update',     'Facilities::update/$1');
    $routes->post('facilities/delete/(:num)',     'Facilities::delete/$1');
    $routes->post('facilities/(:num)/delete',     'Facilities::delete/$1');
    $routes->get('facilities/(:num)/units',              'Units::index/$1');
    $routes->get('facilities/(:num)/units/create',       'Units::create/$1');
    $routes->post('facilities/(:num)/units/store',       'Units::store/$1');
    $routes->get('units',                         'Units::all');
    $routes->get('units/view/(:num)',             'Units::view/$1');
    $routes->get('units/(:num)/parking-contract', 'Units::parkingContract/$1');
    $routes->get('units/(:num)/parking-contract/print', 'Units::parkingContractPrint/$1');
    $routes->post('units/(:num)/parking-contract/print', 'Units::parkingContractPrint/$1');
    $routes->get('units/(:num)',                  'Units::index/$1');
    $routes->get('units/edit/(:num)',             'Units::edit/$1');
    $routes->post('units/update/(:num)',          'Units::update/$1');
    $routes->get('units/checklist/(:num)/(:segment)', 'Units::checklist/$1/$2');
    $routes->post('units/checklist/store',        'Units::storeChecklist');
    $routes->get('units/checklist/print/(:num)',  'Units::printChecklist/$1');

    // Assets (asset-register URLs)
    $routes->get('asset-register',                'Assets::index');
    $routes->get('asset-register/create',         'Assets::create');
    $routes->post('asset-register/store',         'Assets::store');
    $routes->get('asset-register/view/(:num)',    'Assets::view/$1');
    $routes->get('asset-register/edit/(:num)',    'Assets::edit/$1');
    $routes->post('asset-register/update/(:num)', 'Assets::update/$1');
    $routes->post('asset-register/delete/(:num)', 'Assets::delete/$1');
    $routes->post('asset-register/deactivate/(:num)', 'Assets::deactivate/$1');
    $routes->get('asset-register/qrcode/(:num)',       'Assets::qrcode/$1');
    $routes->get('asset-register/print-label/(:num)',  'Assets::printLabel/$1');
    $routes->get('asset-register/print-labels',        'Assets::printLabelsBulk');
    $routes->post('asset-register/upload-document/(:num)', 'Assets::uploadDocument/$1');
    $routes->get('asset-register/history/(:num)',    'Assets::history/$1');
    $routes->get('assets',                        'Assets::index');
    $routes->get('assets/create',                 'Assets::create');
    $routes->post('assets',                       'Assets::store');
    $routes->get('assets/(:num)',                 'Assets::view/$1');
    $routes->get('assets/(:num)/edit',            'Assets::edit/$1');
    $routes->post('assets/(:num)/update',         'Assets::update/$1');
    $routes->post('assets/(:num)/delete',         'Assets::delete/$1');

    // Finance module
    $routes->get('finance',                              'Finance::index');
    $routes->get('finance/invoices',                     'Finance::invoices');
    $routes->get('finance/invoices/create',              'Finance::createInvoice');
    $routes->post('finance/invoices/store',              'Finance::storeInvoice');
    $routes->get('finance/invoices/view/(:num)',         'Finance::viewInvoice/$1');
    $routes->get('finance/invoices/edit/(:num)',         'Finance::editInvoice/$1');
    $routes->post('finance/invoices/update/(:num)',       'Finance::updateInvoice/$1');
    $routes->post('finance/invoices/status/(:num)',      'Finance::updateInvoiceStatus/$1');
    $routes->get('finance/invoices/print/(:num)',        'Finance::printInvoice/$1');
    $routes->get('finance/payments',                     'Finance::payments');
    $routes->post('finance/payments/record/(:num)',      'Finance::recordPayment/$1');
    $routes->get('finance/cash-flow',                    'Finance::cashFlow');
    $routes->get('finance/ledger',                       'Finance::ledger');
    $routes->get('finance/contracts',                    'Finance::contracts');
    $routes->get('finance/contracts/create',             'Finance::createContract');
    $routes->post('finance/contracts/store',             'Finance::storeContract');
    $routes->get('finance/contracts/view/(:num)',        'Finance::viewContract/$1');
    $routes->get('finance/contracts/edit/(:num)',        'Finance::editContract/$1');
    $routes->post('finance/contracts/update/(:num)',     'Finance::updateContract/$1');
    $routes->get('finance/expenses',                     'Finance::expenses');
    $routes->get('finance/expenses/create',              'Finance::createExpense');
    $routes->post('finance/expenses/store',              'Finance::storeExpense');
    $routes->post('finance/expenses/approve/(:num)',     'Finance::approveExpense/$1');
    $routes->post('finance/expenses/reject/(:num)',      'Finance::rejectExpense/$1');
    $routes->get('finance/petty-cash',                   'Finance::pettyCash');
    $routes->get('finance/petty-cash/create',            'Finance::createPettyCash');
    $routes->post('finance/petty-cash/store',            'Finance::storePettyCash');
    $routes->get('finance/petty-cash/view/(:num)',       'Finance::viewPettyCash/$1');
    $routes->post('finance/petty-cash/approve/(:num)',    'Finance::approvePettyCash/$1');
    $routes->post('finance/petty-cash/reject/(:num)',     'Finance::rejectPettyCash/$1');
    $routes->post('finance/petty-cash/issue/(:num)',      'Finance::issuePettyCash/$1');
    $routes->post('finance/petty-cash/reconcile/(:num)',  'Finance::reconcilePettyCash/$1');
    $routes->post('finance/petty-cash/close/(:num)',      'Finance::closePettyCash/$1');
    $routes->get('finance/reimbursements',               'Finance::reimbursements');
    $routes->get('finance/reimbursements/create',        'Finance::createReimbursement');
    $routes->post('finance/reimbursements/store',        'Finance::storeReimbursement');
    $routes->post('finance/reimbursements/approve/(:num)','Finance::approveReimbursement/$1');
    $routes->post('finance/reimbursements/pay/(:num)',   'Finance::payReimbursement/$1');

    // Finance ERP hub (COA, GL, AP, AMC, budgets)
    $routes->get('finance/hub',                         'FinanceErp::hub');
    $routes->get('finance/coa',                         'FinanceErp::coa');
    $routes->get('finance/gl',                          'FinanceErp::gl');
    $routes->get('finance/vendor-bills',              'FinanceErp::vendorBills');
    $routes->post('finance/vendor-bills/pay/(:num)',  'FinanceErp::payVendorBill/$1');
    $routes->get('finance/amc-billing',                 'FinanceErp::amcBilling');
    $routes->post('finance/amc-billing/run',            'FinanceErp::runAmcBilling');
    $routes->get('finance/budgets',                     'FinanceErp::budgets');
    $routes->get('finance/budgets/create',            'FinanceErp::createBudget');
    $routes->post('finance/budgets/store',            'FinanceErp::storeBudget');
    $routes->get('finance/trial-balance',             'FinanceErp::trialBalance');
    $routes->get('finance/balance-sheet',             'FinanceErp::balanceSheet');
    $routes->get('finance/ar-aging',                    'FinanceErp::arAging');
    $routes->get('finance/bank-reconciliation',         'FinanceErp::bankReconciliation');
    $routes->get('finance/payroll-finance',             'FinanceErp::payrollFinance');
    $routes->get('finance/reports',                     'FinanceErp::reports');
    $routes->get('finance/integration-log',             'FinanceErp::integrationLog');

    // Finance legacy short URLs
    $routes->get('invoices',                    'Finance::invoices');
    $routes->get('invoices/create',             'Finance::createInvoice');
    $routes->post('invoices',                   'Finance::storeInvoice');
    $routes->get('invoices/(:num)',             'Finance::viewInvoice/$1');
    $routes->get('expenses',                    'Finance::expenses');
    $routes->get('expenses/create',             'Finance::createExpense');
    $routes->post('expenses',                   'Finance::storeExpense');
    $routes->post('expenses/(:num)/approve',    'Finance::approveExpense/$1');
    $routes->get('petty-cash',                  'Finance::pettyCash');
    $routes->get('petty-cash/create',           'Finance::createPettyCash');
    $routes->post('petty-cash',                 'Finance::storePettyCash');
    $routes->get('petty-cash/(:num)',           'Finance::viewPettyCash/$1');
    $routes->post('petty-cash/(:num)/approve',  'Finance::approvePettyCash/$1');
    $routes->post('petty-cash/(:num)/issue',    'Finance::issuePettyCash/$1');
    $routes->post('petty-cash/(:num)/reconcile','Finance::reconcilePettyCash/$1');
    $routes->post('petty-cash/(:num)/close',    'Finance::closePettyCash/$1');
    $routes->get('reimbursements',              'Finance::reimbursements');
    $routes->get('reimbursements/create',       'Finance::createReimbursement');
    $routes->post('reimbursements',             'Finance::storeReimbursement');
    $routes->post('reimbursements/(:num)/approve','Finance::approveReimbursement/$1');

    // ── PM ERP modules (lease contracts at /contracts per spec) ─────────────
    $routes->get('tenants',                         'Tenants::index');
    $routes->get('tenants/create',                  'Tenants::create');
    $routes->post('tenants',                        'Tenants::store');
    $routes->get('tenants/(:num)',                  'Tenants::show/$1');
    $routes->get('tenants/(:num)/edit',             'Tenants::edit/$1');
    $routes->post('tenants/store',                  'Tenants::store');
    $routes->post('tenants/(:num)/update',          'Tenants::update/$1');
    $routes->post('tenants/(:num)/delete',          'Tenants::delete/$1');
    $routes->post('tenants/(:num)/blacklist',       'Tenants::blacklist/$1', ['filter' => 'permission']);
    $routes->post('tenants/(:num)/unblacklist',     'Tenants::unblacklist/$1', ['filter' => 'permission']);
    $routes->post('tenants/action/(:num)/(:segment)', 'Tenants::action/$1/$2', ['filter' => 'permission']);

    $routes->get('landlords',                            'Landlords::index');
    $routes->get('landlords/create',                     'Landlords::create');
    $routes->post('landlords',                           'Landlords::store');
    $routes->get('landlords/(:num)/show',                'Landlords::show/$1');
    $routes->get('landlords/(:num)',                     'Landlords::show/$1');
    $routes->get('landlords/(:num)/edit',                'Landlords::edit/$1');
    $routes->post('landlords/store',                     'Landlords::store');
    $routes->post('landlords/(:num)/update',             'Landlords::update/$1');
    $routes->post('landlords/(:num)/delete',             'Landlords::delete/$1');
    $routes->get('landlords/(:num)/payout',              'Landlords::payout/$1');
    $routes->post('landlords/(:num)/payout',             'Landlords::payout/$1');
    $routes->post('landlords/payouts/(:num)/mark-paid',  'Landlords::markPaid/$1', ['filter' => 'permission']);
    $routes->get('landlords/(:num)/revenue',             'Landlords::revenue/$1');
    $routes->get('landlords/(:num)/payouts',             'Landlords::payouts/$1');
    $routes->post('landlords/(:num)/documents',          'Landlords::uploadDoc/$1');
    $routes->post('landlords/documents/(:num)/delete',   'Landlords::deleteDoc/$1');
    $routes->post('landlords/(:num)/reminders/dismiss',  'Landlords::dismissReminder/$1');

    // Backward-compatible aliases for old menu/bookmark URLs
    $routes->get('leases',                                  'Leases::index');
    $routes->get('lease-contracts',                         'Leases::index');
    $routes->get('rent-payments',                           'Payments::index');
    $routes->get('rent-payments/create',                    'Payments::create');
    $routes->get('rent-payments/view/(:num)',               'Payments::show/$1');
    $routes->get('rent-payments/(:num)',                    'Payments::show/$1');
    $routes->get('complaints',                              'Helpdesk::index');
    $routes->get('landlord-payouts',                        'Landlords::index');

    $routes->get('pm/(:segment)',                         'PmModules::index/$1');
    $routes->get('pm/(:segment)/create',                  'PmModules::create/$1');
    $routes->post('pm/(:segment)',                        'PmModules::store/$1');
    $routes->get('pm/(:segment)/(:num)',                  'PmModules::show/$1/$2');
    $routes->get('pm/(:segment)/(:num)/edit',             'PmModules::edit/$1/$2');
    $routes->post('pm/(:segment)/(:num)/update',          'PmModules::update/$1/$2');

    $routes->get('contracts',                                  'Leases::index');
    $routes->get('contracts/export-csv',                      'Leases::exportCsv');
    $routes->post('contracts/sync-units',                   'Leases::syncFromUnits');
    $routes->get('contracts/create',                          'Leases::create');
    $routes->post('contracts',                                'Leases::store');
    $routes->get('contracts/(:num)',                          'Leases::show/$1');
    $routes->get('contracts/(:num)/edit',                     'Leases::edit/$1');
    $routes->post('contracts/(:num)/update',                  'Leases::update/$1');
    $routes->post('contracts/(:num)/delete',                  'Leases::delete/$1');
    $routes->post('contracts/(:num)/renew',                   'Leases::renew/$1');
    $routes->post('contracts/(:num)/terminate',               'Leases::terminate/$1', ['filter' => 'permission']);
    $routes->post('contracts/(:num)/amendment',               'Leases::amendment/$1');
    $routes->get('contracts/(:num)/renew',                    'Leases::renewForm/$1');
    $routes->get('contracts/(:num)/terminate',                'Leases::terminateForm/$1');
    $routes->get('contracts/(:num)/amendment',                'Leases::amendmentForm/$1');
    $routes->post('contracts/(:num)/penalties',               'Leases::applyPenalties/$1', ['filter' => 'permission']);
    $routes->post('contracts/(:num)/save-print',              'Leases::savePrint/$1');
    $routes->get('contracts/(:num)/print',                    'Leases::printView/$1');
    $routes->get('contracts/(:num)/parking-print',            'Leases::printParkingContract/$1');
    $routes->post('contracts/(:num)/parking-print',           'Leases::printParkingContract/$1');
    $routes->get('contracts/ajax/units/(:num)',               'Leases::ajaxUnits/$1');
    $routes->post('contracts/(:num)/generate-invoices',       'Leases::generateInvoices/$1');

    $routes->get('payments',                        'Payments::index');
    $routes->get('payments/export-csv',             'Payments::exportCsv');
    $routes->get('payments/create',                 'Payments::create');
    $routes->post('payments',                       'Payments::store');
    $routes->get('payments/(:num)',                 'Payments::show/$1');
    $routes->get('payments/(:num)/edit',            'Payments::edit/$1');
    $routes->post('payments/(:num)/update',         'Payments::update/$1');
    $routes->post('payments/(:num)/collect',        'Payments::collect/$1');
    $routes->post('payments/(:num)/partial',        'Payments::partial/$1');
    $routes->post('payments/(:num)/postpone',       'Payments::postpone/$1');
    $routes->post('payments/(:num)/refund',         'Payments::refund/$1');

    $routes->get('cheques',                              'Cheques::index');
    $routes->get('cheques/export-csv',                   'Cheques::exportCsv');
    $routes->get('cheques/import',                       'Cheques::importCsv');
    $routes->post('cheques/import',                      'Cheques::importCsv');
    $routes->get('cheques/create',                       'Cheques::create');
    $routes->post('cheques',                             'Cheques::store');
    $routes->get('cheques/(:num)',                       'Cheques::show/$1');
    $routes->post('cheques/(:num)/bounce',               'Cheques::bounce/$1');
    $routes->post('cheques/(:num)/deposit',              'Cheques::deposit/$1');
    $routes->post('cheques/(:num)/clear',                'Cheques::clear/$1');
    $routes->post('cheques/(:num)/convert-to-cash',      'Cheques::convertToCash/$1');

    $routes->get('outgoing-cheques',                'OutgoingCheques::index');
    $routes->get('outgoing-cheques/create',         'OutgoingCheques::create');
    $routes->post('outgoing-cheques',               'OutgoingCheques::store');
    $routes->get('outgoing-cheques/(:num)/edit',    'OutgoingCheques::edit/$1');
    $routes->post('outgoing-cheques/(:num)/update', 'OutgoingCheques::update/$1');

    $routes->get('crm',                             'Crm::index');
    $routes->get('crm/reports',                     'Crm::reports');
    $routes->get('crm/create',                      'Crm::create');
    $routes->post('crm',                             'Crm::store');
    $routes->get('crm/(:num)',                      'Crm::show/$1');
    $routes->get('crm/(:num)/edit',                 'Crm::edit/$1');
    $routes->post('crm/(:num)/update',              'Crm::update/$1');
    $routes->post('crm/(:num)/delete',              'Crm::delete/$1');
    $routes->post('crm/(:num)/activity',            'Crm::addActivity/$1');
    $routes->post('crm/(:num)/visit',               'Crm::addVisit/$1');
    $routes->post('crm/(:num)/stage',               'Crm::updateStage/$1');
    $routes->post('crm/(:num)/convert',             'Crm::convert/$1');

    $routes->get('sales',                           'Sales::index');
    $routes->get('sales/commission-rules',          'Sales::commissionRules');
    $routes->post('sales/commission-rules/store',   'Sales::storeCommissionRule');
    $routes->post('sales/commission-rules/(:num)/delete', 'Sales::deleteCommissionRule/$1');
    $routes->get('sales/create',                    'Sales::create');
    $routes->post('sales',                          'Sales::store');
    $routes->get('sales/(:num)/edit',               'Sales::edit/$1');
    $routes->post('sales/(:num)/update',            'Sales::update/$1');

    // Complimentary Offers
    $routes->get('complimentary-offers',                        'ComplimentaryOffers::index');
    $routes->get('complimentary-offers/create',                 'ComplimentaryOffers::create');
    $routes->post('complimentary-offers',                       'ComplimentaryOffers::store');
    $routes->get('complimentary-offers/(:num)/edit',            'ComplimentaryOffers::edit/$1');
    $routes->post('complimentary-offers/(:num)/update',         'ComplimentaryOffers::update/$1');
    $routes->post('complimentary-offers/(:num)/expire',         'ComplimentaryOffers::expire/$1');
    $routes->post('complimentary-offers/(:num)/delete',         'ComplimentaryOffers::delete/$1');

    // Utility Billing (PM)
    $routes->get('utilities',                                   'UtilityBilling::index');
    $routes->get('utilities/create',                            'UtilityBilling::create');
    $routes->post('utilities',                                  'UtilityBilling::store');
    $routes->get('utilities/(:num)/edit',                       'UtilityBilling::edit/$1');
    $routes->post('utilities/(:num)/update',                    'UtilityBilling::update/$1');
    $routes->post('utilities/(:num)/delete',                    'UtilityBilling::delete/$1');
    $routes->get('utilities/view/(:num)',                       'UtilityBilling::view/$1');
    $routes->get('utilities/by-unit/(:num)',                    'UtilityBilling::by_unit/$1');
    $routes->match(['get','post'], 'utilities/(:num)/transfer-to-tenant', 'UtilityBilling::transfer_to_tenant/$1');
    $routes->match(['get','post'], 'utilities/(:num)/transfer-back', 'UtilityBilling::transfer_back/$1');
    $routes->get('utilities/(:num)/bills',                      'UtilityBilling::bills/$1');
    $routes->post('utilities/(:num)/bills/add',                 'UtilityBilling::addBill/$1');
    $routes->post('utilities/bills/(:num)/transfer-to-tenant',  'UtilityBilling::transferToTenant/$1');
    $routes->post('utilities/bills/(:num)/transfer-to-owner',   'UtilityBilling::transferToOwner/$1');
    $routes->post('utilities/bills/(:num)/paid',                'UtilityBilling::markBillPaid/$1');
    $routes->match(['get','post'], 'utilities/bills/(:num)/pay', 'UtilityBilling::markBillPaid/$1');

    // Budgeting
    $routes->get('budgets',                                     'Budgeting::index');
    $routes->get('budgets/create',                              'Budgeting::create');
    $routes->post('budgets',                                    'Budgeting::store');
    $routes->get('budgets/reconcile',                           'Budgeting::reconcile');
    $routes->get('budgets/(:num)/(:num)/variance',              'Budgeting::variance/$1/$2');
    $routes->get('budgets/(:num)/forecast',                     'Budgeting::forecast/$1');

    // Cost Management
    $routes->get('cost-management',                             'CostManagement::index');
    $routes->get('cost-management/expense/create',              'CostManagement::createExpense');
    $routes->post('cost-management/expense/store',              'CostManagement::storeExpense');
    $routes->get('cost-management/reminders/create',            'CostManagement::createReminder');
    $routes->post('cost-management/reminders/store',            'CostManagement::storeReminder');
    $routes->get('cost-management/reminders/(:num)/edit',       'CostManagement::editReminder/$1');
    $routes->post('cost-management/reminders/(:num)/update',    'CostManagement::updateReminder/$1');
    $routes->post('cost-management/reminders/(:num)/done',      'CostManagement::doneReminder/$1');
    $routes->post('cost-management/reminders/(:num)/delete',    'CostManagement::deleteReminder/$1');

    $routes->get('documents',                       'Documents::index');
    $routes->post('documents/store',               'Documents::store');
    $routes->post('documents/delete/(:num)',       'Documents::delete/$1');

    // Procurement
    $routes->get('procurement/workflow', 'Procurement::workflowHub');
    $routes->get('procurement/rfq',                           'Procurement::index');
    $routes->get('procurement/orders',                        static fn () => redirect()->to(base_url('purchase-orders')));
    $routes->get('procurement',                           'Procurement::index');
    $routes->get('procurement/request/create',            'Procurement::createRequest');
    $routes->post('procurement/request/store',            'Procurement::storeRequest');
    $routes->get('procurement/request/view/(:num)',       'Procurement::viewRequest/$1');
    $routes->post('procurement/request/approve/(:num)',     'Procurement::approveRequest/$1');
    $routes->post('procurement/request/reject/(:num)',     'Procurement::rejectRequest/$1');
    $routes->get('procurement/order/create',              'Procurement::createOrder');
    $routes->post('procurement/order/store',              'Procurement::storeOrder');
    $routes->get('procurement/order/view/(:num)',          'Procurement::viewOrder/$1');
    $routes->post('procurement/order/approve/(:num)',      'Procurement::approveOrder/$1');
    $routes->get('procurement/order/print/(:num)',         'Procurement::printOrder/$1');
    $routes->get('procurement/rfq/create',                'Procurement::createRfq');
    $routes->post('procurement/rfq/store',                'Procurement::storeRfq');
    $routes->get('procurement/rfq/view/(:num)',            'Procurement::viewRfq/$1');
    $routes->post('procurement/rfq/quotation/(:num)',       'Procurement::addQuotation/$1');
    $routes->get('procurement/rfq/compare/(:num)',         'Procurement::compareQuotations/$1');
    $routes->get('procurement/grn/create/(:num)',          'Procurement::createGrn/$1');
    $routes->post('procurement/grn/store',                'Procurement::storeGrn');
    $routes->get('procurement/grn/view/(:num)',            'Procurement::viewGrn/$1');
    $routes->get('procurement/order/three-way/(:num)',   'Procurement::threeWayMatch/$1');
    $routes->post('procurement/order/three-way/(:num)',  'Procurement::recordThreeWayMatch/$1');
    $routes->post('procurement/order/three-way-approve/(:num)', 'Procurement::approveThreeWayException/$1');

    // Site visits
    $routes->get('site-visits',                 'SiteVisits::index');
    $routes->get('site-visits/create',          'SiteVisits::create');
    $routes->post('site-visits/store',          'SiteVisits::store');
    $routes->get('site-visits/view/(:num)',     'SiteVisits::view/$1');
    $routes->post('site-visits/complete/(:num)', 'SiteVisits::complete/$1');
    
    $routes->get('purchase-requests',                      'Procurement::index');
    $routes->get('purchase-requests/create',               'Procurement::createRequest');
    $routes->post('purchase-requests',                      'Procurement::storeRequest');
    $routes->get('purchase-requests/(:num)',                'Procurement::viewRequest/$1');
    $routes->get('rfq',                                     'Procurement::index');
    $routes->get('rfq/create',                              'Procurement::createRfq');
    $routes->get('purchase-orders',                       'Procurement::index');
    $routes->get('purchase-orders/create',                'Procurement::createOrder');
    $routes->post('purchase-orders',                      'Procurement::storeOrder');
    $routes->get('purchase-orders/(:num)',                'Procurement::viewOrder/$1');
    $routes->post('purchase-orders/(:num)/approve',       'Procurement::approveOrder/$1');

    // Vendors & inventory
    $routes->get('vendors',                   'Vendors::index');
    $routes->get('vendors/create',            'Vendors::create');
    $routes->post('vendors',                  'Vendors::store');
    $routes->get('vendors/view/(:num)',       'Vendors::view/$1');
    $routes->get('vendors/(:num)',            'Vendors::view/$1');
    $routes->post('vendors/update/(:num)',    'Vendors::update/$1');
    $routes->get('inventory',                 'Inventory::index');
    $routes->get('inventory/create',          'Inventory::create');
    $routes->post('inventory/store',          'Inventory::store');
    $routes->get('inventory/view/(:num)',     'Inventory::view/$1');
    $routes->get('inventory/edit/(:num)',    'Inventory::edit/$1');
    $routes->post('inventory/update/(:num)',  'Inventory::update/$1');
    $routes->get('inventory/movement',        'Inventory::movement');
    $routes->post('inventory/addMovement',    'Inventory::addMovement');
    $routes->get('inventory/(:num)',          'Inventory::view/$1');
    $routes->post('inventory/(:num)/adjust',  'Inventory::addMovement');

    // Compliance & inspections
    $routes->get('compliance',                           'Compliance::index');
    $routes->get('compliance/unit-inspections',          'Compliance::unitInspections');
    $routes->get('compliance/move-in-out',               'Compliance::unitInspections');
    $routes->get('compliance/audit/create',              'Compliance::createAudit');
    $routes->post('compliance/audit/store',              'Compliance::storeAudit');
    $routes->get('compliance/incident/create',           'Compliance::createIncident');
    $routes->post('compliance/incident/store',           'Compliance::storeIncident');
    $routes->get('compliance/inspections',               'Compliance::inspections');
    $routes->get('compliance/inspections/create',        'Compliance::createInspection');
    $routes->post('compliance/inspections/store',        'Compliance::storeInspection');
    $routes->get('compliance/inspections/view/(:num)',   'Compliance::viewInspection/$1');
    $routes->post('compliance/inspections/submit/(:num)','Compliance::submitInspection/$1');
    $routes->get('inspections',                          'Compliance::inspections');
    $routes->get('inspections/create',                   'Compliance::createInspection');
    $routes->post('inspections',                         'Compliance::storeInspection');
    $routes->get('inspections/(:num)',                   'Compliance::viewInspection/$1');
    $routes->post('inspections/(:num)/update',           'Compliance::submitInspection/$1');

    // Employees & attendance
    $routes->get('employees',                    'Employees::index');
    $routes->get('employees/create',             'Employees::create');
    $routes->post('employees/store',             'Employees::store');
    $routes->get('employees/view/(:num)',        'Employees::view/$1');
    $routes->get('employees/(:num)',             'Employees::view/$1');
    $routes->get('employees/edit/(:num)',        'Employees::edit/$1');
    $routes->post('employees/update/(:num)',     'Employees::update/$1');
    $routes->post('employees/delete/(:num)',     'Employees::delete/$1');
    $routes->get('employees/attendance',         'Employees::attendance');
    $routes->post('employees/checkin',           'Employees::checkin');
    $routes->post('employees/checkout',          'Employees::checkout');
    $routes->post('employees/break',             'Employees::startBreak');
    $routes->post('employees/break/end',         'Employees::endBreak');
    $routes->get('hr/employees',                 'Hr\HrEmployees::index');
    $routes->get('hr/employees/create',          'Hr\HrEmployees::create');
    $routes->post('hr/employees/store',          'Hr\HrEmployees::store');
    $routes->get('hr/employees/view/(:num)',     'Hr\HrEmployees::view/$1');
    $routes->get('hr/employees/(:num)/edit',     'Hr\HrEmployees::edit/$1');
    $routes->post('hr/employees/(:num)/update',  'Hr\HrEmployees::update/$1');
    $routes->post('hr/employees/(:num)/status',  'Hr\HrEmployees::status/$1');
    $routes->get('hr/expenses',                  'Hr\HrExpenses::index');
    $routes->get('hr/expenses/create',           'Hr\HrExpenses::create');
    $routes->post('hr/expenses/store',           'Hr\HrExpenses::store');
    $routes->post('hr/expenses/approve/(:num)',  'Hr\HrExpenses::approve/$1', ['filter' => 'permission']);
    $routes->post('hr/expenses/reject/(:num)',   'Hr\HrExpenses::reject/$1', ['filter' => 'permission']);
    $routes->get('hr/performance',               'Hr\HrPerformance::index');
    $routes->get('hr/performance/review/create', 'Hr\HrPerformance::createReview');
    $routes->post('hr/performance/review/store', 'Hr\HrPerformance::storeReview');
    $routes->get('hr/performance/goal/create',   'Hr\HrPerformance::createGoal');
    $routes->post('hr/performance/goal/store',   'Hr\HrPerformance::storeGoal');
    $routes->get('hr/assets',                    'Hr\HrAssets::index');
    $routes->get('hr/assets/create',             'Hr\HrAssets::create');
    $routes->post('hr/assets/store',             'Hr\HrAssets::store');
    $routes->post('hr/assets/return/(:num)',     'Hr\HrAssets::return/$1');
    $routes->get('hr',                             'Hr\Dashboard::index');
    $routes->get('hr/dashboard',                     'Hr\Dashboard::index');
    $routes->get('hr/settings',                  'Hr\HrSettings::index');
    $routes->post('hr/settings/store',           'Hr\HrSettings::storeLookup');
    $routes->get('hr/documents/expiry',          'Hr\DocumentExpiry::index');
    $routes->get('hr/contracts/expiry',        'Hr\ContractExpiry::index');
    $routes->post('hr/contracts/store',        'Hr\EmploymentContracts::store');
    $routes->post('hr/contracts/update/(:num)',  'Hr\EmploymentContracts::update/$1');
    $routes->post('hr/contracts/renew/(:num)',   'Hr\EmploymentContracts::renew/$1');
    $routes->post('hr/assignments/store',        'Hr\EmployeeAssignments::store');
    $routes->post('hr/assignments/update/(:num)', 'Hr\EmployeeAssignments::update/$1');
    $routes->post('hr/assignments/end/(:num)',    'Hr\EmployeeAssignments::end/$1');
    $routes->post('hr/assignments/transfer/(:num)', 'Hr\EmployeeAssignments::transfer/$1');
    $routes->get('hr/manpower',                  'Hr\Manpower::index');
    $routes->post('hr/manpower/store',           'Hr\Manpower::storeRequirement');
    $routes->get('hr/attendance',                'Hr\Attendance::index');
    $routes->get('hr/attendance/regularizations', 'Hr\Attendance::regularizations');
    $routes->post('hr/attendance/regularize',    'Hr\Attendance::submitRegularization');
    $routes->post('hr/attendance/regularizations/approve/(:num)', 'Hr\Attendance::approveRegularization/$1');
    $routes->post('hr/attendance/regularizations/reject/(:num)',  'Hr\Attendance::rejectRegularization/$1');
    $routes->post('hr/attendance/adjust/(:num)',  'Hr\Attendance::adjust/$1');
    $routes->get('hr/shifts',                    'Hr\Shifts::index');
    $routes->post('hr/shifts/store',             'Hr\Shifts::store');
    $routes->post('hr/shifts/assign',            'Hr\Shifts::assign');
    $routes->get('hr/leave',                     'Hr\Leave::index');
    $routes->post('hr/leave/apply',              'Hr\Leave::apply');
    $routes->get('hr/leave/approvals',           'Hr\Leave::approvals');
    $routes->post('hr/leave/approve/(:num)',      'Hr\Leave::approve/$1');
    $routes->post('hr/leave/reject/(:num)',      'Hr\Leave::reject/$1');
    $routes->post('hr/leave/init-balances/(:num)', 'Hr\Leave::initBalances/$1');
    $routes->get('hr/salary',                      'Hr\Salary::index');
    $routes->post('hr/salary/store',               'Hr\Salary::store');
    $routes->get('hr/compensation/advances',       'Hr\Compensation::advances');
    $routes->post('hr/compensation/advances/store', 'Hr\Compensation::storeAdvance');
    $routes->post('hr/compensation/advances/approve/(:num)', 'Hr\Compensation::approveAdvance/$1');
    $routes->post('hr/compensation/advances/reject/(:num)',  'Hr\Compensation::rejectAdvance/$1');
    $routes->get('hr/compensation/loans',          'Hr\Compensation::loans');
    $routes->post('hr/compensation/loans/store',   'Hr\Compensation::storeLoan');
    $routes->post('hr/compensation/loans/approve/(:num)', 'Hr\Compensation::approveLoan/$1');
    $routes->post('hr/compensation/loans/reject/(:num)',  'Hr\Compensation::rejectLoan/$1');
    $routes->get('hr/payroll',                     'Hr\Payroll::index');
    $routes->post('hr/payroll/create',             'Hr\Payroll::create');
    $routes->get('hr/payroll/view/(:num)',         'Hr\Payroll::view/$1');
    $routes->post('hr/payroll/calculate/(:num)',   'Hr\Payroll::calculate/$1');
    $routes->post('hr/payroll/approve/(:num)',     'Hr\Payroll::approve/$1');
    $routes->post('hr/payroll/lock/(:num)',        'Hr\Payroll::lock/$1');
    $routes->post('hr/payroll/unlock/(:num)',      'Hr\Payroll::unlock/$1');
    $routes->post('hr/payroll/post-gl/(:num)',     'Hr\Payroll::postGl/$1');
    $routes->post('hr/payroll/cancel/(:num)',      'Hr\Payroll::cancel/$1');
    $routes->get('hr/wps',                         'Hr\Wps::index');
    $routes->post('hr/wps/generate',               'Hr\Wps::generate');
    $routes->get('hr/wps/view/(:num)',             'Hr\Wps::view/$1');
    $routes->get('hr/wps/download/(:num)',         'Hr\Wps::download/$1');
    $routes->get('hr/onboarding',                  'Hr\Onboarding::index');
    $routes->post('hr/onboarding/start/(:num)',    'Hr\Onboarding::start/$1');
    $routes->post('hr/onboarding/task/(:num)/complete', 'Hr\Onboarding::completeTask/$1');
    $routes->get('hr/offboarding/(:num)',          'Hr\Offboarding::show/$1');
    $routes->post('hr/offboarding/(:num)/clearance/start', 'Hr\Offboarding::startClearance/$1');
    $routes->post('hr/offboarding/clearance/item/(:num)/clear', 'Hr\Offboarding::clearItem/$1');
    $routes->post('hr/offboarding/(:num)/settlement/calculate', 'Hr\Offboarding::calculateSettlement/$1');
    $routes->post('hr/offboarding/settlement/(:num)/approve', 'Hr\Offboarding::approveSettlement/$1');
    $routes->get('hr/requests',                    'Hr\Requests::index');
    $routes->get('hr/approvals',                   'Hr\Requests::approvals');
    $routes->get('hr/transfers',                   'Hr\Transfers::index');
    $routes->post('hr/transfers/store',            'Hr\Transfers::store');
    $routes->post('hr/transfers/approve/(:num)',  'Hr\Transfers::approve/$1');
    $routes->post('hr/transfers/reject/(:num)',   'Hr\Transfers::reject/$1');
    $routes->get('attendance',                   'Employees::attendance');
    $routes->post('attendance/checkin',          'Employees::checkin');
    $routes->post('attendance/checkout',         'Employees::checkout');
    $routes->post('attendance/break',           'Employees::startBreak');
    $routes->post('attendance/break/end',       'Employees::endBreak');

    // Estimations, costing, utility, AI
    $routes->get('estimations',                      'Estimations::index');
    $routes->get('estimations/create',               'Estimations::create');
    $routes->post('estimations/store',             'Estimations::store');
    $routes->get('estimations/view/(:num)',          'Estimations::view/$1');
    $routes->get('estimations/edit/(:num)',          'Estimations::edit/$1');
    $routes->post('estimations/update/(:num)',       'Estimations::update/$1');
    $routes->post('estimations/approve/(:num)',      'Estimations::approve/$1');
    $routes->post('estimations/submit/(:num)',        'Estimations::submitForApproval/$1');
    $routes->post('estimations/convert/(:num)',      'Estimations::convertToWorkOrder/$1');
    $routes->get('estimations/print/(:num)',         'Estimations::printView/$1');
    $routes->get('costing',                          'Costing::index');
    $routes->get('costing/create',                   'Costing::create');
    $routes->post('costing/store',                   'Costing::store');
    $routes->get('costing/view/(:num)',              'Costing::view/$1');
    $routes->get('utility',                          'Utility::index');
    $routes->get('utility/create',                   'Utility::create');
    $routes->post('utility/store',                   'Utility::store');

    // Reports
    $routes->get('reports/portal', 'Reports::portalHub');
    $routes->get('reports/builder',                 'Reports::builder');
    $routes->post('reports/builder/run',            'Reports::builderRun');
    $routes->post('reports/builder/save',           'Reports::builderSave');
    $routes->get('reports/builder/delete/(:num)',   'Reports::builderDelete/$1');
    $routes->get('reports/profit',                  'Reports::profit');
    $routes->get('reports/pnl',                     'Reports::pnl');
    $routes->get('reports/qc',                      'Reports::qc');
    $routes->get('reports',                          'Reports::index');
    $routes->get('reports/kpi',                      'Reports::kpi');
    $routes->get('reports/finance',                  'Reports::finance');
    $routes->get('reports/financial-internal',       'Reports::financialInternal');
    $routes->get('reports/activity-log',             'Reports::activityLog');
    $routes->get('reports/workorders',               'Reports::workorders');
    $routes->get('reports/sla',                      'Reports::sla');
    $routes->get('reports/assets',                   'Reports::assets');
    $routes->get('reports/occupancy',                'Reports::occupancy');
    $routes->get('reports/contracts',                'Reports::contracts');
    $routes->get('reports/technician',               'Reports::technicianPerformance');
    $routes->get('reports/work-orders',              'Reports::workorders');
    $routes->get('reports/financial',                'Reports::finance');

    // Settings, companies, users
    $routes->get('settings',                         'Settings::index');
    $routes->post('settings/update',                 'Settings::update');
    $routes->get('settings/workflow',                'Settings::workflow');
    $routes->post('settings/workflow/save',          'Settings::saveWorkflow');
    $routes->get('settings/finance-module',          'Settings::financeModule');
    $routes->get('settings/roles',                   'Settings::roles');
    $routes->post('settings/roles/save',             'Settings::saveRoles');
    $routes->get('settings/companies',               'Settings::companies');
    $routes->get('settings/companies/create',        'Settings::createCompany');
    $routes->post('settings/companies/store',        'Settings::storeCompany');
    $routes->get('settings/companies/edit/(:num)',   'Settings::editCompany/$1');
    $routes->post('settings/companies/update/(:num)', 'Settings::updateCompany/$1');
    $routes->get('settings/users',                   'Settings::users');
    $routes->get('settings/users/create',            'Settings::createUser');
    $routes->post('settings/users/store',            'Settings::storeUser');
    $routes->get('settings/users/edit/(:num)',       'Settings::editUser/$1');
    $routes->post('settings/users/update/(:num)',     'Settings::updateUser/$1');
    $routes->post('settings/users/delete/(:num)',     'Settings::deleteUser/$1');
    $routes->get('settings/activity-log',            'Settings::activityLog');
    $routes->get('settings/login-history',           'Settings::loginHistory');
    $routes->get('settings/workspaces',              'Settings::workspaces');
    $routes->post('settings/workspaces/save',        'Settings::saveWorkspaces');
    $routes->get('settings/contract-templates',      'Settings::contractTemplates');
    $routes->post('settings/contract-templates/save','Settings::saveContractTemplate');
    $routes->post('settings/switch-company',         'Settings::switchCompany');
    $routes->get('companies',                        'Settings::companies');
    $routes->get('companies/create',                 'Settings::createCompany');
    $routes->post('companies',                       'Settings::storeCompany');
    $routes->get('companies/(:num)',                 'Settings::editCompany/$1');
    $routes->get('companies/(:num)/edit',            'Settings::editCompany/$1');
    $routes->post('companies/(:num)/update',         'Settings::updateCompany/$1');
    $routes->get('users',                            'Settings::users');
    $routes->get('users/create',                     'Settings::createUser');
    $routes->post('users',                           'Settings::storeUser');
    $routes->get('users/(:num)/edit',                'Settings::editUser/$1');
    $routes->post('users/(:num)/update',             'Settings::updateUser/$1');
    $routes->post('users/(:num)/delete',             'Settings::deleteUser/$1');

    // ── Cash Collector ────────────────────────────────────────────────────────
    $routes->get('collector',                        'Collector::index');
    $routes->get('collector/session',                'Collector::session');
    $routes->post('collector/session/start',         'Collector::startSession');
    $routes->post('collector/session/close',         'Collector::closeSession');
    $routes->get('collector/search',                 'Collector::search');
    $routes->get('collector/tenant/(:num)',          'Collector::tenant/$1');
    $routes->get('collector/collect/(:num)',         'Collector::collect/$1');
    $routes->post('collector/collect/(:num)',        'Collector::collect/$1');
    $routes->get('collector/assignments',            'Collector::assignments');
    $routes->get('collector/assign',                 'Collector::assign');
    $routes->post('collector/assign',                'Collector::assign');
    $routes->get('collector/history',                'Collector::history');
    $routes->get('collector/handoff',                'Collector::handoff');
    $routes->post('collector/handoff/(:num)/ack',   'Collector::acknowledgeHandoff/$1');
    $routes->get('collector/report',                 'Collector::report');
    $routes->get('collector/ajax/search-tenant',       'Collector::search_tenant');
    $routes->get('collector/ajax/tenant-invoices/(:num)', 'Collector::tenant_invoices/$1');
    $routes->match(['get','post'], 'collector/process-payment/(:num)', 'Collector::process_payment/$1');
    $routes->post('collector/acknowledge/(:num)',      'Collector::acknowledge/$1');
    $routes->post('collector/bulk-acknowledge',        'Collector::bulk_acknowledge');

    // ── Bank & Cash Management (FinanceBank) ────────────────────────────────
    $routes->group('finance-bank', ['filter' => 'permission'], static function ($routes) {
        $routes->get('/',                                 'FinanceBank::index');
        $routes->get('bank-accounts',                      'FinanceBank::bankAccounts');
        $routes->get('bank-accounts/create',               'FinanceBank::bankAccountCreate');
        $routes->post('bank-accounts/store',               'FinanceBank::bankAccountStore');
        $routes->get('bank-accounts/view/(:num)',          'FinanceBank::bankAccountView/$1');
        $routes->get('bank-accounts/edit/(:num)',          'FinanceBank::bankAccountEdit/$1');
        $routes->post('bank-accounts/update/(:num)',       'FinanceBank::bankAccountUpdate/$1');
        $routes->post('bank-accounts/close/(:num)',        'FinanceBank::bankAccountClose/$1');
        $routes->get('cash-accounts',                      'FinanceBank::cashAccounts');
        $routes->get('cash-accounts/create',               'FinanceBank::cashAccountCreate');
        $routes->post('cash-accounts/store',               'FinanceBank::cashAccountStore');
        $routes->get('deposits',                           'FinanceBank::deposits');
        $routes->get('deposits/create',                    'FinanceBank::depositCreate');
        $routes->post('deposits/store',                    'FinanceBank::depositStore');
        $routes->post('deposits/submit/(:num)',            'FinanceBank::depositSubmit/$1');
        $routes->post('deposits/approve/(:num)',           'FinanceBank::depositApprove/$1');
        $routes->post('deposits/post/(:num)',              'FinanceBank::depositPost/$1');
        $routes->get('withdrawals',                        'FinanceBank::withdrawals');
        $routes->get('withdrawals/create',                 'FinanceBank::withdrawalCreate');
        $routes->post('withdrawals/store',                 'FinanceBank::withdrawalStore');
        $routes->post('withdrawals/submit/(:num)',         'FinanceBank::withdrawalSubmit/$1');
        $routes->post('withdrawals/approve/(:num)',        'FinanceBank::withdrawalApprove/$1');
        $routes->post('withdrawals/post/(:num)',           'FinanceBank::withdrawalPost/$1');
        $routes->get('transfers',                          'FinanceBank::transfers');
        $routes->get('transfers/create',                   'FinanceBank::transferCreate');
        $routes->post('transfers/store',                   'FinanceBank::transferStore');
        $routes->post('transfers/submit/(:num)',           'FinanceBank::transferSubmit/$1');
        $routes->post('transfers/approve/(:num)',          'FinanceBank::transferApprove/$1');
        $routes->post('transfers/post/(:num)',             'FinanceBank::transferPost/$1');
        $routes->get('income',                             'FinanceBank::income');
        $routes->get('expenses',                           'FinanceBank::expenses');
        $routes->get('receipts',                           'FinanceBank::receipts');
        $routes->get('payments',                           'FinanceBank::payments');
        $routes->get('transactions',                       'FinanceBank::transactions');
        $routes->post('transactions/reverse/(:num)',       'FinanceBank::reverseTransaction/$1');
        $routes->get('approvals',                          'FinanceBank::approvals');
        $routes->get('reconciliation',                     'FinanceBank::reconciliation');
        $routes->post('reconciliation/store',              'FinanceBank::reconciliationStore');
        $routes->get('reports',                            'FinanceBank::reports');
        $routes->get('reports/(:segment)',                 'FinanceBank::report/$1');
        $routes->get('settings',                           'FinanceBank::settings');
        $routes->post('settings/save',                     'FinanceBank::settingsSave');
        $routes->get('audit-logs',                         'FinanceBank::auditLogs');
        $routes->get('voucher/(:segment)/(:num)',          'FinanceBank::voucher/$1/$2');
    });

    // ── Petty Cash (FinancePettyCash) ────────────────────────────────────────
    $routes->group('finance-petty', ['filter' => 'permission'], static function ($routes) {
        $routes->get('/',                                 'FinancePettyCash::index');
        $routes->get('accounts',                           'FinancePettyCash::accounts');
        $routes->get('accounts/create',                    'FinancePettyCash::accountCreate');
        $routes->post('accounts/store',                    'FinancePettyCash::accountStore');
        $routes->get('accounts/view/(:num)',               'FinancePettyCash::accountView/$1');
        $routes->post('accounts/custodian/(:num)',         'FinancePettyCash::transferCustodian/$1');
        $routes->get('expenses',                           'FinancePettyCash::expenses');
        $routes->get('expenses/create',                    'FinancePettyCash::expenseCreate');
        $routes->post('expenses/store',                    'FinancePettyCash::expenseStore');
        $routes->post('expenses/submit/(:num)',            'FinancePettyCash::expenseSubmit/$1');
        $routes->post('expenses/approve/(:num)',           'FinancePettyCash::expenseApprove/$1');
        $routes->post('expenses/post/(:num)',              'FinancePettyCash::expensePost/$1');
        $routes->get('advances',                           'FinancePettyCash::advances');
        $routes->get('advances/create',                    'FinancePettyCash::advanceCreate');
        $routes->post('advances/store',                    'FinancePettyCash::advanceStore');
        $routes->post('advances/approve/(:num)',           'FinancePettyCash::advanceApprove/$1');
        $routes->post('advances/issue/(:num)',             'FinancePettyCash::advanceIssue/$1');
        $routes->match(['get', 'post'], 'advances/settle/(:num)', 'FinancePettyCash::advanceSettle/$1');
        $routes->get('replenishments',                     'FinancePettyCash::replenishments');
        $routes->get('replenishments/create',              'FinancePettyCash::replenishmentCreate');
        $routes->post('replenishments/store',              'FinancePettyCash::replenishmentStore');
        $routes->post('replenishments/approve/(:num)',     'FinancePettyCash::replenishmentApprove/$1');
        $routes->post('replenishments/post/(:num)',        'FinancePettyCash::replenishmentPost/$1');
        $routes->get('transfers',                          'FinancePettyCash::transfers');
        $routes->post('transfers/store',                   'FinancePettyCash::transferStore');
        $routes->post('transfers/approve/(:num)',          'FinancePettyCash::transferApprove/$1');
        $routes->post('transfers/post/(:num)',             'FinancePettyCash::transferPost/$1');
        $routes->get('counts',                             'FinancePettyCash::counts');
        $routes->get('counts/create',                      'FinancePettyCash::countCreate');
        $routes->post('counts/store',                      'FinancePettyCash::countStore');
        $routes->post('counts/approve/(:num)',             'FinancePettyCash::countApprove/$1');
        $routes->get('reconciliation',                     'FinancePettyCash::reconciliation');
        $routes->post('reconciliation/store',              'FinancePettyCash::reconciliationStore');
        $routes->get('reports',                            'FinancePettyCash::reports');
        $routes->get('audit-logs',                         'FinancePettyCash::auditLogs');
        $routes->get('legacy',                             'FinancePettyCash::legacy');
    });

    // PM Finance (general ledger)
    $routes->get('finance/pm',                         'PmFinance::index');
    $routes->get('finance/pm/ledger',                'PmFinance::ledger');
    $routes->get('finance/pm/journal',               'PmFinance::journal');
    $routes->get('finance/pm/property/(:num)',       'PmFinance::property/$1');
    $routes->get('finance/pm/unit/(:num)',            'PmFinance::unit/$1');
    $routes->match(['get','post'], 'finance/pm/transaction', 'PmFinance::add_transaction');
    $routes->post('finance/pm/property-cost',          'PmFinance::add_property_cost');
    $routes->post('finance/pm/property-cost/(:num)/delete', 'PmFinance::delete_property_cost/$1');
    $routes->post('finance/pm/unit-cost',              'PmFinance::add_unit_cost');
    $routes->post('finance/pm/unit-cost/(:num)/delete','PmFinance::delete_unit_cost/$1');
    $routes->get('finance/pm/trial-balance',           'PmFinance::trial_balance');
    $routes->get('finance/pm/collection-report',     'PmFinance::collection_report');
    $routes->get('finance/pm/owner-statement',         'PmFinance::owner_statement');
    $routes->get('finance/pm/owner-statement/(:num)',  'PmFinance::owner_statement/$1');
    $routes->get('finance/pm/vat-report',              'PmFinance::vat_report');
    $routes->get('finance/pm/aging',                  'PmFinance::aging');
    $routes->match(['get','post'], 'finance/pm/cash-acknowledge', 'PmFinance::cash_acknowledge');
    $routes->post('finance/pm/landlord-rent',          'PmFinance::add_landlord_rent');

    // PM Inspections (unit checklists — separate from FM compliance inspections)
    $routes->get('pm-inspections',                       'Inspections::index');
    $routes->get('pm-inspections/create',                 'Inspections::create');
    $routes->post('pm-inspections/store',               'Inspections::store');
    $routes->get('pm-inspections/view/(:num)',          'Inspections::view/$1');
    $routes->match(['get','post'], 'pm-inspections/checklist/(:num)', 'Inspections::checklist/$1');
    $routes->get('pm-inspections/compare/(:num)/(:num)', 'Inspections::compare/$1/$2');
    $routes->match(['get','post'], 'pm-inspections/link/(:num)', 'Inspections::link/$1');
    $routes->get('pm-inspections/print/(:num)',         'Inspections::print_report/$1');
    $routes->post('pm-inspections/delete/(:num)',       'Inspections::delete/$1');

    // PM reports
    $routes->get('reports/pm',                       'PmReports::index');
    $routes->get('reports/pm/portal',                'PmReports::portalHub');
    $routes->get('reports/pm/kpi',                   'PmReports::kpi');
    $routes->get('reports/pm/occupancy',             'PmReports::occupancy');
    $routes->get('reports/pm/leases',                'PmReports::leases');
    $routes->get('reports/pm/invoices',              'PmReports::invoices');
    $routes->get('reports/pm/payments',              'PmReports::payments');
    $routes->get('reports/pm/cheques',               'PmReports::cheques');
    $routes->get('reports/pm/expenses',              'PmReports::expenses');
    $routes->get('reports/pm/properties',            'PmReports::properties');
    $routes->get('reports/pm/landlord',              'PmReports::landlord');
    $routes->get('reports/pm/landlord/export',       'PmReports::landlordExport/collections');
    $routes->get('reports/pm/landlord/export/(:segment)', 'PmReports::landlordExport/$1');

    // Notifications & profile
    $routes->get('notifications',                    'Notifications::index');
    $routes->get('notifications/recent',           'Notifications::recent');
    $routes->get('notifications/(:num)/read',        'Notifications::markRead/$1');
    $routes->get('notifications/markAllRead',        'Notifications::markAllRead');
    $routes->get('profile',                          'Profile::index');
    $routes->post('profile/update',                  'Profile::update');
    $routes->post('profile/changePassword',          'Profile::changePassword');

    // MFA setup (authenticated)
    $routes->get('auth/mfa-setup',                   'Auth::mfaSetup');
    $routes->post('auth/mfa-setup',                  'Auth::mfaSetup');

    // Force password change
    $routes->get('profile/force-password-change',    'Profile::forcePasswordChange');
    $routes->post('profile/force-password-change',   'Profile::forcePasswordChange');

    // Compliance — convert inspection to invoice
    $routes->post('compliance/inspections/(:num)/convert-invoice', 'Compliance::convertToInvoice/$1');

    // Vendor Quotations (FM)
    $routes->get('quotations',                       'Quotations::index');
    $routes->get('quotations/create',                'Quotations::create');
    $routes->post('quotations',                      'Quotations::store');
    $routes->get('quotations/(:num)',                'Quotations::view/$1');
    $routes->get('quotations/(:num)/edit',           'Quotations::edit/$1');
    $routes->post('quotations/(:num)/update',        'Quotations::update/$1');
    $routes->post('quotations/(:num)/delete',        'Quotations::delete/$1');

    // Media Albums
    $routes->get('media',                            'Media::index');
    $routes->post('media/albums',                    'Media::createAlbum');
    $routes->get('media/albums/(:num)',              'Media::viewAlbum/$1');
    $routes->post('media/albums/(:num)/upload',      'Media::uploadItems/$1');
    $routes->post('media/albums/(:num)/lock',        'Media::lockAlbum/$1');
    $routes->get('media/panel/(:segment)/(:num)',    'Media::panel/$1/$2');

    // Settings — permissions matrix
    $routes->get('settings/permissions',             'Settings::permissionsMatrix');
    $routes->post('settings/permissions/save',       'Settings::savePermissionsMatrix');

    // Tenants export
    $routes->get('tenants/export',                   'Tenants::exportCsv');

    // CRM export
    $routes->get('crm/export',                       'Crm::exportCsv');

    // AI Insights (usable rule-based reports only)
    $routes->get('ai',                              'AiInsights::index');
    $routes->get('ai/predictive',                   'AiInsights::predictive');
    $routes->get('ai/risk',                         'AiInsights::risk');
    $routes->get('ai/cost',                         'AiInsights::cost');
    $routes->get('ai/assign',                       'AiInsights::smartAssign');
    $routes->get('ai/reports',                       'AiInsights::reports');

    // Ajax
    $routes->get('ajax/live-work-orders',            'Ajax::liveWorkOrders');
    $routes->get('ajax/notifications',               'Ajax::notifications');
    $routes->get('ajax/dashboard-stats',             'Ajax::dashboardStats');
    $routes->get('ajax/inventory-price/(:num)',      'Ajax::inventoryPrice/$1');
    $routes->get('ajax/wo-chat/(:num)',              'Ajax::woChat/$1');
    $routes->post('ajax/wo-chat/(:num)',             'Ajax::sendWoChat/$1');

    // File serve (authenticated). Public logo route is registered above.
    $routes->get('file/(:segment)/(:segment)',       'FileServe::serve/$1/$2');

    // ── Tenant Portal ──────────────────────────────────────────────────────
    $routes->get('portal',                           'Portal::index');
    $routes->get('portal/leases',                    'Portal::leases');
    $routes->get('portal/leases/(:num)',             'Portal::lease/$1');
    $routes->get('portal/payments',                  'Portal::payments');
    $routes->get('portal/tickets',                   'Portal::tickets');
    $routes->get('portal/tickets/create',            'Portal::createTicket');
    $routes->post('portal/tickets',                  'Portal::storeTicket');
});

// Cron (super_admin session or ?key=cron_secret)
$routes->get('cron/run', 'Cron::runAll');

// API v1
$routes->group('api/v1', static function ($routes) {
    $routes->post('auth/login', 'Api\V1\Auth::login');
    $routes->post('app-log', 'Api\V1\AppLog::store');
    $routes->group('', ['filter' => 'jwt'], static function ($routes) {
        $routes->get('auth/me', 'Api\V1\Auth::me');
        $routes->get('properties', 'Api\V1\Properties::index');
        $routes->get('properties/kpis/(:num)', 'Api\V1\Properties::kpis/$1');
        $routes->get('finance/trial-balance', 'Api\V1\Finance::trialBalance');
        $routes->get('finance/reconciliation', 'Api\V1\Finance::reconciliation');
        $routes->get('finance/invoices', 'Api\V1\Invoices::index');
        $routes->post('finance/invoices', 'Api\V1\Invoices::create');
        $routes->get('work-orders', 'Api\V1\WorkOrders::index');
        $routes->get('work-orders/(:num)', 'Api\V1\WorkOrders::show/$1');
        $routes->post('work-orders', 'Api\V1\WorkOrders::create');
        $routes->post('work-orders/(:num)', 'Api\V1\WorkOrders::update/$1');
        $routes->post('work-orders/(:num)/delete', 'Api\V1\WorkOrders::delete/$1');

        $routes->get('fm/dashboard', 'Api\V1\Fm::dashboard');
        $routes->get('fm/work-orders', 'Api\V1\Fm::workOrders');
        $routes->get('fm/work-orders/(:num)', 'Api\V1\Fm::workOrder/$1');
        $routes->post('fm/work-orders/(:num)/status', 'Api\V1\Fm::updateWorkOrderStatus/$1');
        $routes->post('fm/work-orders/(:num)/assign', 'Api\V1\Fm::assignWorkOrder/$1');
        $routes->post('fm/work-orders/(:num)/job-cards', 'Api\V1\Fm::createJobCard/$1');
        $routes->get('fm/complaints', 'Api\V1\Fm::complaints');
        $routes->get('fm/complaints/(:num)', 'Api\V1\Fm::complaint/$1');
        $routes->post('fm/complaints/(:num)/action', 'Api\V1\Fm::complaintAction/$1');
        $routes->get('fm/job-cards', 'Api\V1\Fm::jobCards');
        $routes->get('fm/job-cards/(:num)', 'Api\V1\Fm::jobCard/$1');
        $routes->post('fm/job-cards/(:num)/status', 'Api\V1\Fm::updateJobCardStatus/$1');
        $routes->get('fm/technicians', 'Api\V1\Fm::technicians');

        $routes->get('portal/contracts', 'Api\V1\Portal::contracts');
        $routes->get('portal/contracts/(:num)', 'Api\V1\Portal::contract/$1');
        $routes->get('portal/payments', 'Api\V1\Portal::payments');
        $routes->get('portal/payments/(:num)', 'Api\V1\Portal::payment/$1');
        $routes->get('portal/requests', 'Api\V1\Portal::requests');
        $routes->get('portal/requests/(:num)', 'Api\V1\Portal::request/$1');
        $routes->post('portal/requests', 'Api\V1\Portal::storeRequest');
        $routes->post('portal/requests/(:num)/messages', 'Api\V1\Portal::storeMessage/$1');
        $routes->get('portal/documents/(:num)/download', 'Api\V1\Portal::downloadDocument/$1');

        $routes->get('inspections/properties', 'Api\V1\Inspections::propertyList');
        $routes->get('inspections/properties/(:num)', 'Api\V1\Inspections::propertyDetail/$1');
        $routes->get('inspections/units', 'Api\V1\Inspections::unitList');
        $routes->get('inspections/units/(:num)', 'Api\V1\Inspections::unitDetail/$1');
    });
});

// Legacy unversioned API (JWT). Kept for existing mobile/PWA clients.
$routes->group('api/legacy', static function ($routes) {
    $routes->post('auth/login', 'Api\Auth::login');
    $routes->post('auth/register', 'Api\Auth::register');
    $routes->group('', ['filter' => 'jwt'], static function ($routes) {
        $routes->get('finance/invoices', 'Api\Finance::invoices');
        $routes->post('finance/invoices', 'Api\Finance::createInvoice');
        $routes->get('work-orders', 'Api\WorkOrders::index');
        $routes->get('work-orders/(:num)', 'Api\WorkOrders::show/$1');
        $routes->post('work-orders', 'Api\WorkOrders::create');
        $routes->post('work-orders/(:num)', 'Api\WorkOrders::update/$1');
        $routes->post('work-orders/(:num)/delete', 'Api\WorkOrders::delete/$1');
    });
});
$routes->post('api/public/maintenance', 'Api\PublicApi::requestMaintenance');
$routes->get('api/public/track/(:segment)', 'Api\PublicApi::trackRequest/$1');
