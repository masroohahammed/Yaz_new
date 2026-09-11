<?php
/**
 * Generates docs/API_REFERENCE.html — run: php docs/build_api_reference_html.php
 */

$base = '{{BASE_URL}}'; // e.g. https://your-domain.com/public/api/v1
$token = '{{TOKEN}}';

/** @return list<array<string,mixed>> */
function endpoints(): array
{
    global $base, $token;

    $authHeader = '-H "Authorization: Bearer ' . $token . '"';
    $jsonHeader = '-H "Content-Type: application/json"';

    return [
        // ── System ──
        [
            'group' => 'System',
            'method' => 'GET', 'path' => '/api/v1/health', 'auth' => 'None',
            'desc' => 'Health check for load balancers, mobile startup, and monitoring. Confirms the API router is reachable.',
            'curl' => "curl -X GET \"{$base}/health\" \\\n  -H \"Accept: application/json\"",
            'request' => null,
            'response' => ['status' => true, 'message' => 'API is healthy'],
        ],
        [
            'group' => 'Authentication',
            'method' => 'POST', 'path' => '/api/v1/auth/login', 'auth' => 'None',
            'desc' => 'Authenticate with email and password. Returns a session token valid for 24 hours (stored in user_sessions).',
            'curl' => "curl -X POST \"{$base}/auth/login\" \\\n  {$jsonHeader} \\\n  -d '{\"email\":\"user@example.com\",\"password\":\"your-password\"}'",
            'request' => ['email' => 'user@example.com', 'password' => 'your-password'],
            'response' => [
                'status' => true, 'message' => 'Login successful',
                'token' => 'a1b2c3d4e5f6789012345678901234567890abcdef1234567890abcdef123456',
                'user' => ['id' => 1, 'name' => 'Ahmed Ali', 'email' => 'user@example.com', 'role' => 'facility_manager'],
            ],
        ],
        [
            'group' => 'Authentication',
            'method' => 'GET', 'path' => '/api/v1/auth/me', 'auth' => 'Bearer JWT',
            'desc' => 'Return the currently authenticated user profile and role.',
            'curl' => "curl -X GET \"{$base}/auth/me\" \\\n  {$authHeader} \\\n  -H \"Accept: application/json\"",
            'request' => null,
            'response' => [
                'status' => true,
                'user' => [
                    'id' => 1, 'name' => 'Ahmed Ali', 'email' => 'user@example.com',
                    'phone' => '+97450000000', 'role' => 'facility_manager',
                    'role_display' => 'Facility Manager', 'company_id' => 1,
                ],
            ],
        ],
        [
            'group' => 'App Telemetry',
            'method' => 'POST', 'path' => '/api/v1/app-log', 'auth' => 'None (JWT optional)',
            'desc' => 'Log mobile app events (splash screen, errors, CTA clicks) to app_mobile_logs.',
            'curl' => "curl -X POST \"{$base}/app-log\" \\\n  {$jsonHeader} \\\n  -d '{\"action\":\"splash_loaded\",\"status\":\"success\",\"app_version\":\"1.2.0\",\"platform\":\"android\"}'",
            'request' => [
                'action' => 'splash_loaded', 'status' => 'success',
                'message' => 'App opened', 'app_version' => '1.2.0',
                'platform' => 'android', 'user_id' => 0,
                'context' => ['screen' => 'home'],
            ],
            'response' => ['status' => true, 'message' => 'Logged'],
        ],

        // ── Properties ──
        [
            'group' => 'Property Management',
            'method' => 'GET', 'path' => '/api/v1/properties', 'auth' => 'Bearer JWT',
            'desc' => 'List properties (facilities) scoped to the user\'s company and assigned facilities.',
            'curl' => "curl -X GET \"{$base}/properties\" \\\n  {$authHeader}",
            'request' => null,
            'response' => [
                'status' => true,
                'data' => [['id' => 12, 'name' => 'Al Yazwa Tower', 'code' => 'AYT-01', 'city' => 'Doha', 'status' => 'active']],
                'count' => 1,
            ],
        ],
        [
            'group' => 'Property Management',
            'method' => 'GET', 'path' => '/api/v1/properties/kpis/{facilityId}', 'auth' => 'Bearer JWT',
            'desc' => 'Property KPIs: occupancy, active contracts, overdue payments, open maintenance, optional AI health score.',
            'curl' => "curl -X GET \"{$base}/properties/kpis/12\" \\\n  {$authHeader}",
            'request' => null,
            'response' => [
                'status' => true,
                'property' => ['id' => 12, 'name' => 'Al Yazwa Tower', 'status' => 'active'],
                'kpis' => [
                    'total_units' => 48, 'occupied_units' => 42, 'occupancy_pct' => 87.5,
                    'active_contracts' => 40, 'expiring_contracts' => 3, 'overdue_payments' => 2,
                    'pending_rent' => 15000.0, 'open_maintenance' => 1, 'currency' => 'QAR',
                ],
            ],
        ],

        // ── Work orders (generic) ──
        [
            'group' => 'Work Orders (PM)',
            'method' => 'GET', 'path' => '/api/v1/work-orders', 'auth' => 'Bearer JWT',
            'desc' => 'List work orders for facilities in the user\'s company scope (max 50, newest first).',
            'curl' => "curl -X GET \"{$base}/work-orders\" \\\n  {$authHeader}",
            'request' => null,
            'response' => [
                'status' => true,
                'data' => [['id' => 101, 'wo_number' => 'WO-2026-0042', 'title' => 'AC repair', 'status' => 'new', 'priority' => 'high']],
                'count' => 1,
            ],
        ],
        [
            'group' => 'Work Orders (PM)',
            'method' => 'GET', 'path' => '/api/v1/work-orders/{id}', 'auth' => 'Bearer JWT',
            'desc' => 'Get a single work order with facility and assignee details.',
            'curl' => "curl -X GET \"{$base}/work-orders/101\" \\\n  {$authHeader}",
            'request' => null,
            'response' => ['status' => true, 'data' => ['id' => 101, 'wo_number' => 'WO-2026-0042', 'title' => 'AC repair', 'status' => 'assigned']],
        ],
        [
            'group' => 'Work Orders (PM)',
            'method' => 'POST', 'path' => '/api/v1/work-orders', 'auth' => 'Bearer JWT',
            'desc' => 'Create a new work order for a facility.',
            'curl' => "curl -X POST \"{$base}/work-orders\" \\\n  {$authHeader} \\\n  {$jsonHeader} \\\n  -d '{\"title\":\"AC not cooling\",\"facility_id\":12,\"description\":\"Unit 304\",\"priority\":\"high\"}'",
            'request' => ['title' => 'AC not cooling', 'facility_id' => 12, 'description' => 'Unit 304', 'type' => 'corrective', 'priority' => 'high', 'estimated_cost' => 500],
            'response' => ['status' => true, 'message' => 'Work order created', 'wo_number' => 'WO-2026-0043', 'id' => 102],
        ],
        [
            'group' => 'Work Orders (PM)',
            'method' => 'POST', 'path' => '/api/v1/work-orders/{id}', 'auth' => 'Bearer JWT',
            'desc' => 'Update work order fields (title, status, assigned_to, actual_cost, etc.).',
            'curl' => "curl -X POST \"{$base}/work-orders/101\" \\\n  {$authHeader} \\\n  {$jsonHeader} \\\n  -d '{\"status\":\"in_progress\",\"assigned_to\":42}'",
            'request' => ['status' => 'in_progress', 'assigned_to' => 42, 'actual_cost' => 450],
            'response' => ['status' => true, 'message' => 'Work order updated'],
        ],
        [
            'group' => 'Work Orders (PM)',
            'method' => 'POST', 'path' => '/api/v1/work-orders/{id}/delete', 'auth' => 'Bearer JWT',
            'desc' => 'Cancel a work order (sets status to cancelled).',
            'curl' => "curl -X POST \"{$base}/work-orders/101/delete\" \\\n  {$authHeader}",
            'request' => null,
            'response' => ['status' => true, 'message' => 'Work order cancelled'],
        ],

        // ── Finance ──
        [
            'group' => 'Finance',
            'method' => 'GET', 'path' => '/api/v1/finance/invoices', 'auth' => 'Bearer JWT',
            'desc' => 'List invoices for facilities in scope with summary totals.',
            'curl' => "curl -X GET \"{$base}/finance/invoices\" \\\n  {$authHeader}",
            'request' => null,
            'response' => [
                'status' => true,
                'data' => [['id' => 55, 'invoice_number' => 'INV-2026-0100', 'status' => 'draft', 'total' => 5250.0]],
                'count' => 1, 'totals' => ['draft' => 5250.0, 'paid' => 120000.0],
            ],
        ],
        [
            'group' => 'Finance',
            'method' => 'POST', 'path' => '/api/v1/finance/invoices', 'auth' => 'Bearer JWT',
            'desc' => 'Create a draft invoice. VAT applied automatically when enabled in settings.',
            'curl' => "curl -X POST \"{$base}/finance/invoices\" \\\n  {$authHeader} \\\n  {$jsonHeader} \\\n  -d '{\"facility_id\":12,\"subtotal\":5000,\"issue_date\":\"2026-09-01\",\"due_date\":\"2026-10-01\"}'",
            'request' => ['facility_id' => 12, 'subtotal' => 5000, 'issue_date' => '2026-09-01', 'due_date' => '2026-10-01'],
            'response' => ['status' => true, 'invoice_number' => 'INV-2026-0101', 'total' => 5250.0],
        ],
        [
            'group' => 'Finance',
            'method' => 'GET', 'path' => '/api/v1/finance/trial-balance?as_of=YYYY-MM-DD', 'auth' => 'Bearer JWT (finance role)',
            'desc' => 'General ledger trial balance as of a date. Requires finance_manager, finance_user, or super_admin.',
            'curl' => "curl -X GET \"{$base}/finance/trial-balance?as_of=2026-09-11\" \\\n  {$authHeader}",
            'request' => null,
            'response' => [
                'status' => true, 'as_of' => '2026-09-11', 'gl_enabled' => true,
                'rows' => [['account_code' => '1000', 'account_name' => 'Cash', 'debit' => 50000, 'credit' => 0]],
                'total_debit' => 50000, 'total_credit' => 50000,
            ],
        ],
        [
            'group' => 'Finance',
            'method' => 'GET', 'path' => '/api/v1/finance/reconciliation?from=&to=', 'auth' => 'Bearer JWT (finance role)',
            'desc' => 'Bank activity and reconciliation summary for a date range.',
            'curl' => "curl -X GET \"{$base}/finance/reconciliation?from=2026-09-01&to=2026-09-11\" \\\n  {$authHeader}",
            'request' => null,
            'response' => [
                'status' => true, 'from' => '2026-09-01', 'to' => '2026-09-11',
                'activity' => [], 'total_in' => 25000, 'total_out' => 8000, 'net' => 17000,
            ],
        ],

        // ── FM Mobile ──
        [
            'group' => 'Facility Management (FM)',
            'method' => 'GET', 'path' => '/api/v1/fm/dashboard', 'auth' => 'Bearer JWT (FM roles)',
            'desc' => 'Role-specific FM dashboard KPIs for technician, supervisor, or facility manager.',
            'curl' => "curl -X GET \"{$base}/fm/dashboard\" \\\n  {$authHeader}",
            'request' => null,
            'response' => ['status' => true, 'role' => 'facility_manager', 'data' => ['open_work_orders' => 12, 'sla_breaches' => 1]],
        ],
        [
            'group' => 'Facility Management (FM)',
            'method' => 'GET', 'path' => '/api/v1/fm/work-orders?status=&q=', 'auth' => 'Bearer JWT (FM roles)',
            'desc' => 'List work orders filtered by role (technician sees assigned only). Query: status, q (search).',
            'curl' => "curl -X GET \"{$base}/fm/work-orders?status=in_progress&q=AC\" \\\n  {$authHeader}",
            'request' => null,
            'response' => ['status' => true, 'data' => [['id' => 101, 'wo_number' => 'WO-2026-0042', 'title' => 'AC repair', 'status' => 'in_progress']], 'count' => 1],
        ],
        [
            'group' => 'Facility Management (FM)',
            'method' => 'GET', 'path' => '/api/v1/fm/work-orders/{id}', 'auth' => 'Bearer JWT (FM roles)',
            'desc' => 'Work order detail with job cards and allowed status actions for the current user.',
            'curl' => "curl -X GET \"{$base}/fm/work-orders/101\" \\\n  {$authHeader}",
            'request' => null,
            'response' => ['status' => true, 'data' => ['id' => 101, 'wo_number' => 'WO-2026-0042', 'job_cards' => [], 'actions' => ['in_progress', 'completed']]],
        ],
        [
            'group' => 'Facility Management (FM)',
            'method' => 'POST', 'path' => '/api/v1/fm/work-orders/{id}/status', 'auth' => 'Bearer JWT (FM roles)',
            'desc' => 'Update work order status (role-gated transitions). Optional execution_percent and notes.',
            'curl' => "curl -X POST \"{$base}/fm/work-orders/101/status\" \\\n  {$authHeader} \\\n  {$jsonHeader} \\\n  -d '{\"status\":\"in_progress\",\"execution_percent\":50,\"notes\":\"Started repair\"}'",
            'request' => ['status' => 'in_progress', 'execution_percent' => 50, 'notes' => 'Started repair'],
            'response' => ['status' => true, 'message' => 'Work order updated'],
        ],
        [
            'group' => 'Facility Management (FM)',
            'method' => 'POST', 'path' => '/api/v1/fm/work-orders/{id}/assign', 'auth' => 'Bearer JWT (supervisor/FM)',
            'desc' => 'Assign a technician to a work order.',
            'curl' => "curl -X POST \"{$base}/fm/work-orders/101/assign\" \\\n  {$authHeader} \\\n  {$jsonHeader} \\\n  -d '{\"technician_id\":42}'",
            'request' => ['technician_id' => 42],
            'response' => ['status' => true, 'message' => 'Technician assigned'],
        ],
        [
            'group' => 'Facility Management (FM)',
            'method' => 'POST', 'path' => '/api/v1/fm/work-orders/{id}/job-cards', 'auth' => 'Bearer JWT (supervisor/FM)',
            'desc' => 'Create a job card linked to a work order.',
            'curl' => "curl -X POST \"{$base}/fm/work-orders/101/job-cards\" \\\n  {$authHeader} \\\n  {$jsonHeader} \\\n  -d '{\"technician_id\":42,\"description\":\"Replace filter\",\"scheduled_date\":\"2026-09-15\"}'",
            'request' => ['technician_id' => 42, 'description' => 'Replace filter', 'scheduled_date' => '2026-09-15'],
            'response' => ['status' => true, 'message' => 'Job card created', 'id' => 7, 'jc_number' => 'JC-2026-0007'],
        ],
        [
            'group' => 'Facility Management (FM)',
            'method' => 'GET', 'path' => '/api/v1/fm/complaints', 'auth' => 'Bearer JWT (FM roles)',
            'desc' => 'List maintenance complaints (helpdesk requests) for FM review.',
            'curl' => "curl -X GET \"{$base}/fm/complaints\" \\\n  {$authHeader}",
            'request' => null,
            'response' => ['status' => true, 'data' => [['id' => 20, 'ticket_number' => 'MR-2026-0015', 'status' => 'pending']], 'count' => 1],
        ],
        [
            'group' => 'Facility Management (FM)',
            'method' => 'GET', 'path' => '/api/v1/fm/complaints/{id}', 'auth' => 'Bearer JWT (FM roles)',
            'desc' => 'Complaint detail with requester info and available actions.',
            'curl' => "curl -X GET \"{$base}/fm/complaints/20\" \\\n  {$authHeader}",
            'request' => null,
            'response' => ['status' => true, 'data' => ['id' => 20, 'ticket_number' => 'MR-2026-0015', 'description' => 'Water leak', 'actions' => ['verify', 'approve', 'reject']]],
        ],
        [
            'group' => 'Facility Management (FM)',
            'method' => 'POST', 'path' => '/api/v1/fm/complaints/{id}/action', 'auth' => 'Bearer JWT (FM roles)',
            'desc' => 'Verify, approve, or reject a maintenance complaint. reason required on reject.',
            'curl' => "curl -X POST \"{$base}/fm/complaints/20/action\" \\\n  {$authHeader} \\\n  {$jsonHeader} \\\n  -d '{\"action\":\"approve\"}'",
            'request' => ['action' => 'approve', 'reason' => 'Required when action is reject'],
            'response' => ['status' => true, 'message' => 'Complaint approved'],
        ],
        [
            'group' => 'Facility Management (FM)',
            'method' => 'GET', 'path' => '/api/v1/fm/job-cards', 'auth' => 'Bearer JWT (FM roles)',
            'desc' => 'List job cards scoped to the user\'s role and facilities.',
            'curl' => "curl -X GET \"{$base}/fm/job-cards\" \\\n  {$authHeader}",
            'request' => null,
            'response' => ['status' => true, 'data' => [['id' => 7, 'jc_number' => 'JC-2026-0007', 'status' => 'assigned']], 'count' => 1],
        ],
        [
            'group' => 'Facility Management (FM)',
            'method' => 'GET', 'path' => '/api/v1/fm/job-cards/{id}', 'auth' => 'Bearer JWT (FM roles)',
            'desc' => 'Job card detail with work order link and technician info.',
            'curl' => "curl -X GET \"{$base}/fm/job-cards/7\" \\\n  {$authHeader}",
            'request' => null,
            'response' => ['status' => true, 'data' => ['id' => 7, 'jc_number' => 'JC-2026-0007', 'status' => 'in_progress', 'wo_id' => 101]],
        ],
        [
            'group' => 'Facility Management (FM)',
            'method' => 'POST', 'path' => '/api/v1/fm/job-cards/{id}/status', 'auth' => 'Bearer JWT (FM roles)',
            'desc' => 'Update job card status (assigned → in_progress → completed, etc.).',
            'curl' => "curl -X POST \"{$base}/fm/job-cards/7/status\" \\\n  {$authHeader} \\\n  {$jsonHeader} \\\n  -d '{\"status\":\"completed\",\"labor_hours\":2.5}'",
            'request' => ['status' => 'completed', 'labor_hours' => 2.5, 'notes' => 'Filter replaced'],
            'response' => ['status' => true, 'message' => 'Job card updated'],
        ],
        [
            'group' => 'Facility Management (FM)',
            'method' => 'GET', 'path' => '/api/v1/fm/technicians', 'auth' => 'Bearer JWT (FM roles)',
            'desc' => 'List active technicians available for work order / job card assignment.',
            'curl' => "curl -X GET \"{$base}/fm/technicians\" \\\n  {$authHeader}",
            'request' => null,
            'response' => ['status' => true, 'data' => [['id' => 42, 'name' => 'Mohammed Hassan', 'phone' => '+97451111111']], 'count' => 1],
        ],

        // ── Tenant Portal ──
        [
            'group' => 'Tenant Portal',
            'method' => 'GET', 'path' => '/api/v1/portal/contracts', 'auth' => 'Bearer JWT (linked tenant)',
            'desc' => 'List lease contracts for the authenticated tenant user.',
            'curl' => "curl -X GET \"{$base}/portal/contracts\" \\\n  {$authHeader}",
            'request' => null,
            'response' => ['status' => true, 'data' => [['id' => 500, 'contract_number' => 'LC-2026-0100', 'status' => 'active', 'rent_amount' => 8000]], 'count' => 1],
        ],
        [
            'group' => 'Tenant Portal',
            'method' => 'GET', 'path' => '/api/v1/portal/contracts/{id}', 'auth' => 'Bearer JWT (linked tenant)',
            'desc' => 'Lease contract detail with property, unit, and downloadable documents list.',
            'curl' => "curl -X GET \"{$base}/portal/contracts/500\" \\\n  {$authHeader}",
            'request' => null,
            'response' => ['status' => true, 'data' => ['id' => 500, 'contract_number' => 'LC-2026-0100', 'documents' => [['id' => 88, 'title' => 'Signed lease PDF']]]],
        ],
        [
            'group' => 'Tenant Portal',
            'method' => 'GET', 'path' => '/api/v1/portal/payments?q=&status=&page=&per_page=', 'auth' => 'Bearer JWT (linked tenant)',
            'desc' => 'Payment history with overview (outstanding, upcoming due, paid YTD). Supports search and pagination.',
            'curl' => "curl -X GET \"{$base}/portal/payments?status=pending&page=1&per_page=20\" \\\n  {$authHeader}",
            'request' => null,
            'response' => [
                'status' => true,
                'overview' => ['total_outstanding' => 8000, 'currency' => 'QAR', 'due_in_days' => 15],
                'data' => [['id' => 900, 'payment_number' => 'PAY-2026-0200', 'amount' => 8000, 'status' => 'pending']],
                'pagination' => ['page' => 1, 'per_page' => 20, 'total' => 1],
            ],
        ],
        [
            'group' => 'Tenant Portal',
            'method' => 'GET', 'path' => '/api/v1/portal/payments/{id}', 'auth' => 'Bearer JWT (linked tenant)',
            'desc' => 'Single payment / invoice detail for the tenant.',
            'curl' => "curl -X GET \"{$base}/portal/payments/900\" \\\n  {$authHeader}",
            'request' => null,
            'response' => ['status' => true, 'data' => ['id' => 900, 'payment_number' => 'PAY-2026-0200', 'amount' => 8000, 'due_date' => '2026-09-25', 'status' => 'pending']],
        ],
        [
            'group' => 'Tenant Portal',
            'method' => 'GET', 'path' => '/api/v1/portal/requests', 'auth' => 'Bearer JWT (linked tenant)',
            'desc' => 'List tenant service / maintenance requests submitted via the portal.',
            'curl' => "curl -X GET \"{$base}/portal/requests\" \\\n  {$authHeader}",
            'request' => null,
            'response' => ['status' => true, 'data' => [['id' => 30, 'title' => 'AC not cooling', 'status' => 'open']], 'count' => 1],
        ],
        [
            'group' => 'Tenant Portal',
            'method' => 'GET', 'path' => '/api/v1/portal/requests/{id}', 'auth' => 'Bearer JWT (linked tenant)',
            'desc' => 'Service request detail including message thread.',
            'curl' => "curl -X GET \"{$base}/portal/requests/30\" \\\n  {$authHeader}",
            'request' => null,
            'response' => ['status' => true, 'data' => ['id' => 30, 'title' => 'AC not cooling', 'messages' => [['id' => 1, 'body' => 'Still waiting for technician']]]],
        ],
        [
            'group' => 'Tenant Portal',
            'method' => 'POST', 'path' => '/api/v1/portal/requests', 'auth' => 'Bearer JWT (linked tenant)',
            'desc' => 'Submit a new service request. Supports JSON or multipart/form-data with photo attachment.',
            'curl' => "curl -X POST \"{$base}/portal/requests\" \\\n  {$authHeader} \\\n  {$jsonHeader} \\\n  -d '{\"title\":\"AC not cooling\",\"category\":\"Maintenance\",\"priority\":\"high\",\"description\":\"No cold air\",\"unit_id\":9536}'",
            'request' => ['title' => 'AC not cooling', 'category' => 'Maintenance', 'priority' => 'high', 'description' => 'Unit living room AC runs but no cold air.', 'unit_id' => 9536],
            'response' => ['status' => true, 'message' => 'Request submitted', 'id' => 31],
        ],
        [
            'group' => 'Tenant Portal',
            'method' => 'POST', 'path' => '/api/v1/portal/requests/{id}/messages', 'auth' => 'Bearer JWT (linked tenant)',
            'desc' => 'Add a message to an existing service request thread.',
            'curl' => "curl -X POST \"{$base}/portal/requests/30/messages\" \\\n  {$authHeader} \\\n  {$jsonHeader} \\\n  -d '{\"body\":\"Any update on the technician visit?\"}'",
            'request' => ['body' => 'Any update on the technician visit?'],
            'response' => ['status' => true, 'message' => 'Message added', 'id' => 2],
        ],
        [
            'group' => 'Tenant Portal',
            'method' => 'GET', 'path' => '/api/v1/portal/documents/{id}/download', 'auth' => 'Bearer JWT (linked tenant)',
            'desc' => 'Download a contract document file (PDF/image) linked to the tenant\'s lease.',
            'curl' => "curl -X GET \"{$base}/portal/documents/88/download\" \\\n  {$authHeader} \\\n  -o lease-document.pdf",
            'request' => null,
            'response' => '(Binary file stream — Content-Disposition: attachment)',
        ],

        // ── Inspections ──
        [
            'group' => 'Inspections',
            'method' => 'GET', 'path' => '/api/v1/inspections/properties?facility_id=&status=&frequency=', 'auth' => 'Bearer JWT',
            'desc' => 'List property-level compliance inspections with optional filters.',
            'curl' => "curl -X GET \"{$base}/inspections/properties?facility_id=12&status=active\" \\\n  {$authHeader}",
            'request' => null,
            'response' => ['status' => true, 'data' => [['id' => 5, 'title' => 'Fire Safety Q3', 'status' => 'active', 'frequency' => 'quarterly']], 'count' => 1],
        ],
        [
            'group' => 'Inspections',
            'method' => 'GET', 'path' => '/api/v1/inspections/properties/{id}', 'auth' => 'Bearer JWT',
            'desc' => 'Property inspection detail including checklist items and results.',
            'curl' => "curl -X GET \"{$base}/inspections/properties/5\" \\\n  {$authHeader}",
            'request' => null,
            'response' => ['status' => true, 'data' => ['id' => 5, 'title' => 'Fire Safety Q3', 'items' => [['id' => 1, 'label' => 'Extinguishers checked', 'status' => 'pass']]]],
        ],
        [
            'group' => 'Inspections',
            'method' => 'GET', 'path' => '/api/v1/inspections/units?facility_id=&type=&frequency=', 'auth' => 'Bearer JWT',
            'desc' => 'List unit-level inspections with optional filters.',
            'curl' => "curl -X GET \"{$base}/inspections/units?facility_id=12\" \\\n  {$authHeader}",
            'request' => null,
            'response' => ['status' => true, 'data' => [['id' => 8, 'unit_number' => '304', 'inspection_type' => 'move-in']], 'count' => 1],
        ],
        [
            'group' => 'Inspections',
            'method' => 'GET', 'path' => '/api/v1/inspections/units/{id}', 'auth' => 'Bearer JWT',
            'desc' => 'Unit inspection detail with checklist items.',
            'curl' => "curl -X GET \"{$base}/inspections/units/8\" \\\n  {$authHeader}",
            'request' => null,
            'response' => ['status' => true, 'data' => ['id' => 8, 'unit_number' => '304', 'items' => []]],
        ],

        // ── Public ──
        [
            'group' => 'Public (no login)',
            'method' => 'POST', 'path' => '/api/public/maintenance', 'auth' => 'None',
            'desc' => 'Submit a public maintenance request without authentication. Returns a ticket number for tracking.',
            'curl' => "curl -X POST \"{{PUBLIC_BASE}}/api/public/maintenance\" \\\n  {$jsonHeader} \\\n  -d '{\"requester_name\":\"John Doe\",\"description\":\"Water leak in lobby\",\"facility_id\":12,\"priority\":\"high\"}'",
            'request' => ['requester_name' => 'John Doe', 'description' => 'Water leak in lobby', 'facility_id' => 12, 'category' => 'Plumbing', 'priority' => 'high'],
            'response' => ['status' => true, 'ticket_number' => 'MR-2026-0016', 'message' => 'Request submitted successfully'],
        ],
        [
            'group' => 'Public (no login)',
            'method' => 'GET', 'path' => '/api/public/track/{ticket}', 'auth' => 'None',
            'desc' => 'Track status of a public maintenance ticket by ticket number.',
            'curl' => "curl -X GET \"{{PUBLIC_BASE}}/api/public/track/MR-2026-0016\" \\\n  -H \"Accept: application/json\"",
            'request' => null,
            'response' => ['status' => true, 'data' => ['ticket_number' => 'MR-2026-0016', 'status' => 'pending', 'priority' => 'high', 'submitted_at' => '2026-09-11 10:30:00']],
        ],

        // ── Errors ──
        [
            'group' => 'Errors',
            'method' => 'GET', 'path' => '/api/v1/{unknown-path}', 'auth' => 'Varies',
            'desc' => 'Any unmatched API path returns JSON 404 (not HTML).',
            'curl' => "curl -X GET \"{$base}/non-existing-endpoint\" \\\n  {$authHeader}",
            'request' => null,
            'response' => ['status' => false, 'message' => 'Endpoint not found'],
        ],
    ];
}

function escHtml(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function jsonPretty(mixed $data): string
{
    if ($data === null) {
        return '<em class="muted">None</em>';
    }
    if (is_string($data)) {
        return escHtml($data);
    }

    return escHtml(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') !== realpath(__FILE__)) {
    return;
}

$eps = endpoints();
$groups = [];
foreach ($eps as $ep) {
    $groups[$ep['group']][] = $ep;
}

ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>FM ERP — API Reference (Postman / cURL)</title>
<style>
:root { --bg:#0f1419; --card:#1a2332; --border:#2d3a4f; --text:#e7ecf3; --muted:#8b9cb3; --get:#61affe; --post:#49cc90; --accent:#7c6cff; }
* { box-sizing:border-box; }
body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background:var(--bg); color:var(--text); margin:0; line-height:1.5; }
header { background:linear-gradient(135deg,#1a2332,#252d3d); border-bottom:1px solid var(--border); padding:2rem; }
header h1 { margin:0 0 .5rem; font-size:1.75rem; }
header p { margin:.25rem 0; color:var(--muted); max-width:720px; }
.wrap { display:flex; gap:0; max-width:1400px; margin:0 auto; }
nav { width:260px; flex-shrink:0; position:sticky; top:0; height:100vh; overflow-y:auto; padding:1rem; border-right:1px solid var(--border); background:#121820; }
nav h2 { font-size:.75rem; text-transform:uppercase; letter-spacing:.08em; color:var(--muted); margin:1.25rem 0 .5rem; }
nav a { display:block; color:#b8c5d6; text-decoration:none; font-size:.85rem; padding:.25rem 0; }
nav a:hover { color:#fff; }
main { flex:1; padding:1.5rem 2rem 3rem; min-width:0; }
.vars { background:var(--card); border:1px solid var(--border); border-radius:8px; padding:1rem 1.25rem; margin-bottom:2rem; }
.vars code { background:#0d1117; padding:.15rem .4rem; border-radius:4px; font-size:.85rem; }
.endpoint { background:var(--card); border:1px solid var(--border); border-radius:10px; padding:1.25rem 1.5rem; margin-bottom:1.25rem; scroll-margin-top:1rem; }
.endpoint h3 { margin:0 0 .75rem; font-size:1.05rem; display:flex; flex-wrap:wrap; align-items:center; gap:.5rem; }
.method { font-size:.7rem; font-weight:700; padding:.2rem .55rem; border-radius:4px; text-transform:uppercase; color:#000; }
.method.get { background:var(--get); }
.method.post { background:var(--post); }
.path { font-family: ui-monospace, monospace; font-size:.9rem; color:#c9d6e3; }
.desc { color:var(--muted); margin:0 0 .75rem; }
.meta { font-size:.85rem; margin-bottom:1rem; }
.meta strong { color:var(--text); }
h4 { font-size:.8rem; text-transform:uppercase; letter-spacing:.06em; color:var(--muted); margin:1rem 0 .4rem; }
pre { background:#0d1117; border:1px solid var(--border); border-radius:6px; padding:.85rem 1rem; overflow-x:auto; font-size:.78rem; line-height:1.45; margin:0; position:relative; }
pre.curl { border-left:3px solid var(--accent); white-space:pre-wrap; word-break:break-all; }
.copy-btn { position:absolute; top:.5rem; right:.5rem; background:#2d3a4f; border:none; color:#fff; font-size:.7rem; padding:.25rem .5rem; border-radius:4px; cursor:pointer; }
.copy-btn:hover { background:var(--accent); }
.muted { color:var(--muted); font-style:italic; }
.group-title { font-size:1.35rem; margin:2rem 0 1rem; padding-bottom:.5rem; border-bottom:1px solid var(--border); }
.note { background:#1e2a3a; border-left:3px solid var(--get); padding:.75rem 1rem; border-radius:0 6px 6px 0; margin-bottom:1.5rem; font-size:.9rem; }
@media (max-width:900px) { .wrap { flex-direction:column; } nav { width:100%; height:auto; position:relative; } }
</style>
</head>
<body>
<header>
  <h1>FM ERP — REST API Reference</h1>
  <p>CodeIgniter 4 · API v1 · Postman-ready cURL for every endpoint</p>
</header>
<div class="wrap">
<nav>
  <h2>Setup</h2>
  <a href="#variables">Variables</a>
  <a href="#postman">Postman import</a>
  <?php foreach (array_keys($groups) as $gName): ?>
  <h2><?= escHtml($gName) ?></h2>
  <?php foreach ($groups[$gName] as $ep):
      $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($ep['method'] . '-' . $ep['path']));
  ?>
  <a href="#<?= escHtml($slug) ?>"><?= escHtml($ep['method']) ?> <?= escHtml($ep['path']) ?></a>
  <?php endforeach; endforeach; ?>
</nav>
<main>
<section id="variables" class="vars">
  <h2 style="margin-top:0;font-size:1.1rem;">Replace before calling</h2>
  <p><code>{{BASE_URL}}</code> — API base, e.g. <code>https://your-domain.com/public/api/v1</code></p>
  <p><code>{{PUBLIC_BASE}}</code> — Site root, e.g. <code>https://your-domain.com/public</code></p>
  <p><code>{{TOKEN}}</code> — JWT from <code>POST /auth/login</code> response (<code>Authorization: Bearer …</code>)</p>
</section>
<section id="postman" class="note">
  <strong>Import into Postman:</strong> Click <em>Import → Raw text</em> and paste any cURL block below. Postman converts it to a ready request. Set collection variables <code>BASE_URL</code> and <code>TOKEN</code> to avoid editing each request.
</section>
<?php foreach ($groups as $gName => $items): ?>
<h2 class="group-title"><?= escHtml($gName) ?></h2>
<?php foreach ($items as $ep):
    $slug = preg_replace('/[^a-z0-9]+/i', '-', strtolower($ep['method'] . '-' . $ep['path']));
    $mClass = strtolower($ep['method']);
?>
<article class="endpoint" id="<?= escHtml($slug) ?>">
  <h3>
    <span class="method <?= escHtml($mClass) ?>"><?= escHtml($ep['method']) ?></span>
    <span class="path"><?= escHtml($ep['path']) ?></span>
  </h3>
  <p class="desc"><?= escHtml($ep['desc']) ?></p>
  <p class="meta"><strong>Auth:</strong> <?= escHtml($ep['auth']) ?></p>
  <h4>cURL — Postman import</h4>
  <pre class="curl" data-copy><?= escHtml($ep['curl']) ?><button type="button" class="copy-btn" onclick="copyPre(this)">Copy</button></pre>
  <h4>Request body (JSON)</h4>
  <pre class="json"><?= jsonPretty($ep['request']) ?></pre>
  <h4>Response example</h4>
  <pre class="json"><?= jsonPretty($ep['response']) ?></pre>
</article>
<?php endforeach; endforeach; ?>
</main>
</div>
<script>
function copyPre(btn) {
  const pre = btn.parentElement;
  const text = pre.textContent.replace('Copy', '').trim();
  navigator.clipboard.writeText(text).then(() => {
    btn.textContent = 'Copied!';
    setTimeout(() => btn.textContent = 'Copy', 1500);
  });
}
</script>
</body>
</html>
<?php
$html = ob_get_clean();
$out = __DIR__ . '/API_REFERENCE.html';
file_put_contents($out, $html);
echo "Written {$out} (" . strlen($html) . " bytes)\n";
