<?php

namespace App\Controllers;

use App\Services\CashCollectionService;

/**
 * Collector — Cash Collector field-app workspace.
 *
 * Access: cash_collector, super_admin, property_manager (assign/handoff only).
 */
class Collector extends BaseController
{
    // ─────────────────────────────────────────────────────────────────────────
    // Gate helper
    // ─────────────────────────────────────────────────────────────────────────

    private function requireCollector(): void
    {
        $this->requireRole('cash_collector', 'super_admin', 'property_manager', 'finance_manager');
    }

    private function requireCollectorOnly(): void
    {
        $this->requireRole('cash_collector', 'super_admin');
    }

    private function collectorId(): int
    {
        return (int) session()->get('user_id');
    }

    private function companyId(): ?int
    {
        $cid = session()->get('company_id');
        return $cid ? (int) $cid : null;
    }

    /** @return array<string,mixed>|null */
    private function findTenant(int $tenantId): ?array
    {
        if ($tenantId < 1 || ! $this->db->tableExists('tenants')) {
            return null;
        }

        $q = $this->db->table('tenants')->where('id', $tenantId);
        $cid = $this->companyId();
        if ($cid && $this->db->fieldExists('company_id', 'tenants')) {
            $q->where('company_id', $cid);
        }

        return $q->get()->getRowArray() ?: null;
    }

    /** @return array<string,mixed>|null */
    private function findPayment(int $paymentId): ?array
    {
        if ($paymentId < 1 || ! $this->db->tableExists('lease_payments')) {
            return null;
        }

        $q = $this->db->table('lease_payments lp')
            ->select('lp.*, t.full_name AS tenant_name, t.phone AS tenant_phone, f.name AS facility_name')
            ->join('tenants t', 't.id = lp.tenant_id', 'left')
            ->join('facilities f', 'f.id = lp.facility_id', 'left')
            ->where('lp.id', $paymentId);

        $cid = $this->companyId();
        if ($cid && $this->db->fieldExists('company_id', 'lease_payments')) {
            $q->where('lp.company_id', $cid);
        }

        $facilityIds = $this->companyScope()->facilityIds();
        if ($facilityIds !== null) {
            if ($facilityIds === []) {
                return null;
            }
            $q->whereIn('lp.facility_id', $facilityIds);
        }

        return $q->get()->getRowArray() ?: null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Open session helper
    // ─────────────────────────────────────────────────────────────────────────

    private function openSession(int $collectorId): ?array
    {
        if (! $this->db->tableExists('collector_sessions')) {
            return null;
        }
        return $this->db->table('collector_sessions')
            ->where('collector_id', $collectorId)
            ->where('status', 'open')
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()->getRowArray() ?: null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 1. Dashboard
    // ─────────────────────────────────────────────────────────────────────────

    public function index(): string
    {
        $this->requireCollector();
        $uid        = $this->collectorId();
        $openSession = $this->openSession($uid);
        $today      = date('Y-m-d');
        $currency   = $this->settings['currency'] ?? 'QAR';

        $todayCount  = 0;
        $todaySum    = 0.0;
        $pendingCount = 0;

        if ($this->db->tableExists('lease_payments')) {
            $lpCols = $this->db->getFieldNames('lease_payments');
            if (in_array('received_by', $lpCols, true)) {
                $row = $this->db->table('lease_payments')
                    ->selectSum('amount', 'total')
                    ->selectCount('id', 'cnt')
                    ->where('received_by', $uid)
                    ->where('DATE(payment_date)', $today)
                    ->get()->getRowArray();
                $todayCount = (int) ($row['cnt'] ?? 0);
                $todaySum   = (float) ($row['total'] ?? 0);
            }
        }

        if ($this->db->tableExists('collection_assignments')) {
            $pendingCount = $this->db->table('collection_assignments')
                ->where('collector_id', $uid)
                ->where('status', 'pending')
                ->countAllResults();
        }

        $pendingHandoffs = 0;
        if ($this->db->tableExists('collector_handoffs')) {
            $pendingHandoffs = $this->db->table('collector_handoffs')
                ->where('collector_id', $uid)
                ->where('status', 'pending')
                ->countAllResults();
        }

        return view('collector/dashboard', $this->viewData([
            'title'           => 'Collector Dashboard',
            'openSession'     => $openSession,
            'todayCount'      => $todayCount,
            'todaySum'        => $todaySum,
            'pendingCount'    => $pendingCount,
            'pendingHandoffs' => $pendingHandoffs,
            'currency'        => $currency,
        ]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. Session management
    // ─────────────────────────────────────────────────────────────────────────

    public function session(): string
    {
        $this->requireCollectorOnly();
        $uid        = $this->collectorId();
        $openSession = $this->openSession($uid);

        $recentSessions = [];
        if ($this->db->tableExists('collector_sessions')) {
            $recentSessions = $this->db->table('collector_sessions')
                ->where('collector_id', $uid)
                ->orderBy('id', 'DESC')
                ->limit(10)
                ->get()->getResultArray();
        }

        return view('collector/session', $this->viewData([
            'title'          => 'My Session',
            'openSession'    => $openSession,
            'recentSessions' => $recentSessions,
        ]));
    }

    public function startSession(): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->requireCollectorOnly();
        $uid = $this->collectorId();

        $existing = $this->openSession($uid);
        if ($existing) {
            return redirect()->to(base_url('collector/session'))
                ->with('error', 'You already have an open session: ' . $existing['session_code']);
        }

        $float = (float) ($this->request->getPost('opening_float') ?? 0);
        $code  = $this->generateNumber('CS', 'collector_sessions', 'session_code');

        $this->db->table('collector_sessions')->insert([
            'company_id'    => $this->companyId(),
            'collector_id'  => $uid,
            'session_code'  => $code,
            'started_at'    => date('Y-m-d H:i:s'),
            'status'        => 'open',
            'opening_float' => $float,
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        $this->logActivity('session_start', 'collector', $this->db->insertID(), "Session {$code} opened");

        return redirect()->to(base_url('collector/session'))
            ->with('success', "Session {$code} started.");
    }

    public function closeSession(): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->requireCollectorOnly();
        $uid     = $this->collectorId();
        $session = $this->openSession($uid);

        if (! $session) {
            return redirect()->to(base_url('collector/session'))
                ->with('error', 'No open session found.');
        }

        $closingCash = (float) ($this->request->getPost('closing_cash') ?? 0);
        $notes       = trim((string) ($this->request->getPost('notes') ?? ''));

        $this->db->table('collector_sessions')
            ->where('id', $session['id'])
            ->update([
                'status'       => 'closed',
                'closed_at'    => date('Y-m-d H:i:s'),
                'closing_cash' => $closingCash,
                'notes'        => $notes ?: null,
            ]);

        // Create pending handoff
        if ($closingCash > 0 && $this->db->tableExists('collector_handoffs')) {
            $this->db->table('collector_handoffs')->insert([
                'company_id'   => $this->companyId(),
                'session_id'   => $session['id'],
                'collector_id' => $uid,
                'amount'       => $closingCash,
                'status'       => 'pending',
                'notes'        => "Handoff for session {$session['session_code']}",
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        }

        $this->logActivity('session_close', 'collector', $session['id'], "Session {$session['session_code']} closed");

        return redirect()->to(base_url('collector/session'))
            ->with('success', "Session {$session['session_code']} closed. Handoff created.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. Search tenants
    // ─────────────────────────────────────────────────────────────────────────

    /** AJAX tenant search */
    public function search_tenant()
    {
        $this->requireCollector();
        $q = trim((string) ($this->request->getGet('search_query') ?? $this->request->getGet('q') ?? ''));
        $svc = new CashCollectionService($this->db);
        $rows = $svc->searchTenants($q, $this->companyId());

        return $this->response->setJSON(['tenants' => $rows]);
    }

    /** AJAX outstanding invoices for tenant */
    public function tenant_invoices(int $tenantId = 0)
    {
        $this->requireCollector();
        $tenantId = $tenantId ?: (int) ($this->request->getGet('tenant_id') ?? 0);
        if (! $this->findTenant($tenantId)) {
            return $this->response->setStatusCode(404)->setJSON(['invoices' => []]);
        }
        $svc = new CashCollectionService($this->db);

        return $this->response->setJSON([
            'invoices' => $svc->tenantOutstandingInvoices($tenantId),
        ]);
    }

    public function process_payment(int $tenantId)
    {
        $this->requireCollector();
        $tenant = $this->findTenant($tenantId);
        if (! $tenant) {
            return redirect()->to(base_url('collector/search'))->with('error', 'Tenant not found.');
        }

        if ($this->request->is('post')) {
            return $this->processPaymentSubmit($tenantId);
        }

        $svc = new CashCollectionService($this->db);
        $uid = $this->collectorId();

        return view('collector/process_payment', $this->viewData([
            'title'        => 'Process Payment',
            'tenant'       => $tenant,
            'invoices'     => $svc->tenantOutstandingInvoices($tenantId),
            'openSession'  => $this->openSession($uid),
            'currency'     => $this->settings['currency'] ?? 'QAR',
        ]));
    }

    private function processPaymentSubmit(int $tenantId): \CodeIgniter\HTTP\RedirectResponse
    {
        $uid = $this->collectorId();
        $session = $this->openSession($uid);
        if (! $session) {
            return redirect()->to(base_url('collector/session'))->with('error', 'Start a session first.');
        }

        $paymentId = (int) $this->request->getPost('payment_id');
        $svc = new CashCollectionService($this->db);
        $result = $svc->collectPayment($paymentId, [
            'collected_amount' => $this->request->getPost('collected_amount'),
            'payment_method'   => $this->request->getPost('payment_method'),
            'collected_at'     => $this->request->getPost('collected_at') ?: date('Y-m-d H:i:s'),
            'notes'            => $this->request->getPost('notes'),
            'cheque_no'        => $this->request->getPost('cheque_no'),
            'cheque_bank'      => $this->request->getPost('cheque_bank'),
            'cheque_maturity'  => $this->request->getPost('cheque_maturity'),
            'bank_name'        => $this->request->getPost('bank_name'),
            'bank_account'     => $this->request->getPost('bank_account'),
            'transfer_date'    => $this->request->getPost('transfer_date'),
            'transfer_ref'     => $this->request->getPost('transfer_ref'),
        ], $uid, (int) $session['id']);

        if (! $result['ok']) {
            return redirect()->back()->withInput()->with('error', $result['error']);
        }

        return redirect()->to(base_url('collector/process-payment/' . $tenantId))
            ->with('success', 'Payment collected — pending finance acknowledgement.');
    }

    public function acknowledge(int $id)
    {
        $this->requireRole('super_admin', 'finance_manager', 'property_manager');
        $depositDate = $this->request->getPost('deposit_date');
        if (! $depositDate) {
            return redirect()->back()->with('error', 'Deposit date required.');
        }

        $svc = new CashCollectionService($this->db);
        $svc->acknowledge($id, $this->collectorId(), $depositDate, $this->request->getPost('deposit_ref'), $this->request->getPost('notes'));

        return redirect()->back()->with('success', 'Collection acknowledged.');
    }

    public function bulk_acknowledge()
    {
        $this->requireRole('super_admin', 'finance_manager', 'property_manager');
        $ids = (array) ($this->request->getPost('collection_ids') ?? []);
        $depositDate = $this->request->getPost('deposit_date');
        if (empty($ids) || ! $depositDate) {
            return redirect()->back()->with('error', 'Select collections and deposit date.');
        }

        $svc = new CashCollectionService($this->db);
        $count = $svc->acknowledgeBulk($ids, $this->collectorId(), $depositDate, $this->request->getPost('deposit_ref'), $this->request->getPost('notes'));

        return redirect()->back()->with('success', "{$count} collection(s) acknowledged.");
    }

    public function search(): string
    {
        $this->requireCollector();

        $q       = trim((string) ($this->request->getGet('q') ?? $this->request->getGet('search_query') ?? ''));
        $tenants = [];

        if ($q !== '' && $this->db->tableExists('tenants')) {
            $builder = $this->db->table('tenants t')
                ->select('t.id, t.full_name, t.phone, t.email, t.qid_no, t.company_name')
                ->groupStart()
                    ->like('t.full_name', $q)
                    ->orLike('t.phone', $q)
                    ->orLike('t.qid_no', $q)
                    ->orLike('t.email', $q)
                ->groupEnd()
                ->orderBy('t.full_name', 'ASC')
                ->limit(30);

            $cid = $this->companyId();
            if ($cid && $this->db->fieldExists('company_id', 'tenants')) {
                $builder->where('t.company_id', $cid);
            }

            $tenants = $builder->get()->getResultArray();

            // Attach open payment count to each tenant
            if ($this->db->tableExists('lease_payments')) {
                foreach ($tenants as &$tenant) {
                    $tenant['open_payments'] = $this->db->table('lease_payments')
                        ->whereIn('status', ['pending', 'partial', 'overdue'])
                        ->where('tenant_id', $tenant['id'])
                        ->countAllResults();
                }
                unset($tenant);
            }
        }

        return view('collector/search', $this->viewData([
            'title'   => 'Search Tenant',
            'q'       => $q,
            'tenants' => $tenants,
        ]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. Tenant detail with open payments
    // ─────────────────────────────────────────────────────────────────────────

    public function tenant(int $tenantId): string
    {
        $this->requireCollector();

        $tenant = $this->findTenant($tenantId);

        if (! $tenant) {
            return redirect()->to(base_url('collector/search'))
                ->with('error', 'Tenant not found.');
        }

        $openPayments = [];
        if ($this->db->tableExists('lease_payments')) {
            $openPayments = $this->db->table('lease_payments lp')
                ->select('lp.*, f.name AS facility_name')
                ->join('facilities f', 'f.id = lp.facility_id', 'left')
                ->where('lp.tenant_id', $tenantId)
                ->whereIn('lp.status', ['pending', 'partial', 'overdue'])
                ->orderBy('lp.due_date', 'ASC')
                ->get()->getResultArray();
        }

        $activeLease = null;
        if ($this->db->tableExists('lease_contracts')) {
            $activeLease = $this->db->table('lease_contracts lc')
                ->select('lc.*, f.name AS facility_name, u.unit_number')
                ->join('facilities f', 'f.id = lc.facility_id', 'left')
                ->join('units u', 'u.id = lc.unit_id', 'left')
                ->where('lc.tenant_id', $tenantId)
                ->where('lc.status', 'active')
                ->orderBy('lc.id', 'DESC')
                ->limit(1)
                ->get()->getRowArray() ?: null;
        }

        return view('collector/tenant', $this->viewData([
            'title'        => 'Tenant — ' . ($tenant['full_name'] ?? ''),
            'tenant'       => $tenant,
            'openPayments' => $openPayments,
            'activeLease'  => $activeLease,
            'currency'     => $this->settings['currency'] ?? 'QAR',
        ]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 5. Collect payment (GET form + POST process)
    // ─────────────────────────────────────────────────────────────────────────

    public function collect(int $paymentId): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $this->requireCollector();

        $payment = $this->findPayment($paymentId);

        if (! $payment) {
            return redirect()->to(base_url('collector/search'))
                ->with('error', 'Payment record not found.');
        }

        if ($this->request->is('post')) {
            $uid = $this->collectorId();
            $session = $this->openSession($uid);
            if (! $session) {
                return redirect()->to(base_url('collector/session'))->with('error', 'Start a session first.');
            }

            $svc = new CashCollectionService($this->db);
            $result = $svc->collectPayment($paymentId, [
                'collected_amount' => $this->request->getPost('amount'),
                'payment_method'   => $this->request->getPost('payment_method'),
                'notes'            => $this->request->getPost('notes'),
                'cheque_no'        => $this->request->getPost('cheque_no'),
                'cheque_bank'      => $this->request->getPost('cheque_bank'),
                'cheque_maturity'  => $this->request->getPost('cheque_maturity'),
            ], $uid, (int) $session['id']);

            if (! $result['ok']) {
                return redirect()->back()->withInput()->with('error', $result['error']);
            }

            $tenantId = (int) ($payment['tenant_id'] ?? 0);
            $this->logActivity('collect_payment', 'collector', $paymentId, 'Payment collected');

            return redirect()->to(base_url('collector/tenant/' . $tenantId))->with('success', 'Payment collected — pending acknowledgement.');
        }

        $uid         = $this->collectorId();
        $openSession = $this->openSession($uid);

        return view('collector/collect', $this->viewData([
            'title'       => 'Collect Payment',
            'payment'     => $payment,
            'openSession' => $openSession,
            'currency'    => $this->settings['currency'] ?? 'QAR',
        ]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6. Assignments
    // ─────────────────────────────────────────────────────────────────────────

    public function assignments(): string
    {
        $this->requireCollector();
        $uid = $this->collectorId();
        $role = (string) session()->get('user_role');

        $assignments = [];
        if ($this->db->tableExists('collection_assignments')) {
            $builder = $this->db->table('collection_assignments ca')
                ->select('ca.*, t.full_name AS tenant_name, t.phone AS tenant_phone, f.name AS facility_name, lp.amount AS payment_amount, lp.due_date, lp.payment_number')
                ->join('tenants t', 't.id = ca.tenant_id', 'left')
                ->join('facilities f', 'f.id = ca.facility_id', 'left')
                ->join('lease_payments lp', 'lp.id = ca.payment_id', 'left')
                ->orderBy('ca.assigned_date', 'DESC')
                ->orderBy('ca.id', 'DESC');

            if (! in_array($role, ['super_admin', 'property_manager', 'finance_manager'], true)) {
                $builder->where('ca.collector_id', $uid);
            }

            $cid = $this->companyId();
            if ($cid && $this->db->fieldExists('company_id', 'collection_assignments')) {
                $builder->where('ca.company_id', $cid);
            }

            $assignments = $builder->get()->getResultArray();
        }

        $collectors = [];
        if (in_array($role, ['super_admin', 'property_manager', 'finance_manager'], true)) {
            $collectors = $this->db->table('users u')
                ->select('u.id, u.name')
                ->join('roles r', 'r.id = u.role_id', 'left')
                ->where('r.name', 'cash_collector')
                ->where('u.status', 'active')
                ->orderBy('u.name', 'ASC')
                ->get()->getResultArray();
        }

        return view('collector/assignments', $this->viewData([
            'title'       => 'Collection Assignments',
            'assignments' => $assignments,
            'collectors'  => $collectors,
            'currency'    => $this->settings['currency'] ?? 'QAR',
        ]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 7. Assign (GET form for admin / POST create)
    // ─────────────────────────────────────────────────────────────────────────

    public function assign(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $this->requireRole('super_admin', 'property_manager', 'finance_manager');

        if ($this->request->is('post')) {
            return $this->assignBulk();
        }

        // GET form
        $collectors = $this->db->table('users u')
            ->select('u.id, u.name')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->where('r.name', 'cash_collector')
            ->where('u.status', 'active')
            ->orderBy('u.name', 'ASC')
            ->get()->getResultArray();

        $openPayments = [];
        if ($this->db->tableExists('lease_payments')) {
            $openPayments = $this->db->table('lease_payments lp')
                ->select('lp.id, lp.payment_number, lp.amount, lp.due_date, lp.status, t.full_name AS tenant_name, f.name AS facility_name')
                ->join('tenants t', 't.id = lp.tenant_id', 'left')
                ->join('facilities f', 'f.id = lp.facility_id', 'left')
                ->whereIn('lp.status', ['pending', 'partial', 'overdue'])
                ->orderBy('lp.due_date', 'ASC')
                ->limit(100);

            $cid = $this->companyId();
            if ($cid && $this->db->fieldExists('company_id', 'lease_payments')) {
                $openPayments->where('lp.company_id', $cid);
            }
            $facilityIds = $this->companyScope()->facilityIds();
            if ($facilityIds !== null && $facilityIds !== []) {
                $openPayments->whereIn('lp.facility_id', $facilityIds);
            }

            $openPayments = $openPayments->get()->getResultArray();
        }

        return view('collector/assign', $this->viewData([
            'title'        => 'Assign Collections',
            'collectors'   => $collectors,
            'openPayments' => $openPayments,
            'currency'     => $this->settings['currency'] ?? 'QAR',
        ]));
    }

    private function assignBulk(): \CodeIgniter\HTTP\RedirectResponse
    {
        $collectorId = (int) ($this->request->getPost('collector_id') ?? 0);
        $paymentIds  = (array) ($this->request->getPost('payment_ids') ?? []);
        $notes       = trim((string) ($this->request->getPost('notes') ?? ''));
        $assignedDate = $this->request->getPost('assigned_date') ?: date('Y-m-d');

        if (! $collectorId || empty($paymentIds)) {
            return redirect()->back()->with('error', 'Please select a collector and at least one payment.');
        }

        $createdBy = $this->collectorId();
        $cid       = $this->companyId();
        $now       = date('Y-m-d H:i:s');
        $count     = 0;

        foreach ($paymentIds as $pid) {
            $pid = (int) $pid;
            if ($pid < 1) {
                continue;
            }

            $payment = $this->findPayment($pid);
            if (! $payment) {
                continue;
            }

            // Skip if already assigned pending
            $alreadyAssigned = $this->db->table('collection_assignments')
                ->where('payment_id', $pid)
                ->where('collector_id', $collectorId)
                ->where('status', 'pending')
                ->countAllResults();
            if ($alreadyAssigned) {
                continue;
            }

            $this->db->table('collection_assignments')->insert([
                'company_id'    => $cid,
                'collector_id'  => $collectorId,
                'tenant_id'     => $payment['tenant_id'] ?? null,
                'facility_id'   => $payment['facility_id'] ?? null,
                'payment_id'    => $pid,
                'assigned_date' => $assignedDate,
                'status'        => 'pending',
                'notes'         => $notes ?: null,
                'created_by'    => $createdBy,
                'created_at'    => $now,
            ]);
            $count++;
        }

        return redirect()->to(base_url('collector/assignments'))
            ->with('success', "{$count} assignment(s) created.");
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 8. History
    // ─────────────────────────────────────────────────────────────────────────

    public function history(): string
    {
        $this->requireCollector();
        $uid  = $this->collectorId();
        $role = (string) session()->get('user_role');

        $from = $this->request->getGet('from') ?: date('Y-m-01');
        $to   = $this->request->getGet('to')   ?: date('Y-m-d');

        $payments = [];
        if ($this->db->tableExists('lease_payments')) {
            $builder = $this->db->table('lease_payments lp')
                ->select('lp.*, t.full_name AS tenant_name, f.name AS facility_name')
                ->join('tenants t', 't.id = lp.tenant_id', 'left')
                ->join('facilities f', 'f.id = lp.facility_id', 'left')
                ->where('lp.payment_date >=', $from)
                ->where('lp.payment_date <=', $to)
                ->whereIn('lp.status', ['paid', 'partial'])
                ->orderBy('lp.payment_date', 'DESC');

            $lpFields = $this->db->getFieldNames('lease_payments');
            if (in_array('received_by', $lpFields, true)
                && ! in_array($role, ['super_admin', 'property_manager', 'finance_manager'], true)
            ) {
                $builder->where('lp.received_by', $uid);
            }

            $payments = $builder->get()->getResultArray();
        }

        $total = array_sum(array_column($payments, 'amount'));

        return view('collector/history', $this->viewData([
            'title'    => 'Collection History',
            'payments' => $payments,
            'total'    => $total,
            'from'     => $from,
            'to'       => $to,
            'currency' => $this->settings['currency'] ?? 'QAR',
        ]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 9. Handoff
    // ─────────────────────────────────────────────────────────────────────────

    public function handoff(): string
    {
        $this->requireCollector();
        $uid  = $this->collectorId();
        $role = (string) session()->get('user_role');

        $handoffs = [];
        if ($this->db->tableExists('collector_handoffs')) {
            $builder = $this->db->table('collector_handoffs ch')
                ->select('ch.*, u.name AS collector_name, ack.name AS acknowledged_by_name, cs.session_code')
                ->join('users u', 'u.id = ch.collector_id', 'left')
                ->join('users ack', 'ack.id = ch.acknowledged_by', 'left')
                ->join('collector_sessions cs', 'cs.id = ch.session_id', 'left')
                ->orderBy('ch.id', 'DESC');

            if (! in_array($role, ['super_admin', 'property_manager', 'finance_manager'], true)) {
                $builder->where('ch.collector_id', $uid);
            }

            $handoffs = $builder->get()->getResultArray();
        }

        return view('collector/handoff', $this->viewData([
            'title'    => 'Cash Handoff',
            'handoffs' => $handoffs,
            'currency' => $this->settings['currency'] ?? 'QAR',
        ]));
    }

    public function acknowledgeHandoff(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $this->requireRole('super_admin', 'property_manager', 'finance_manager');

        if (! $this->db->tableExists('collector_handoffs')) {
            return redirect()->back()->with('error', 'Handoff table not available.');
        }

        $handoff = $this->db->table('collector_handoffs')->where('id', $id)->get()->getRowArray();
        if (! $handoff) {
            return redirect()->back()->with('error', 'Handoff not found.');
        }
        if ($handoff['status'] === 'acknowledged') {
            return redirect()->back()->with('error', 'Handoff already acknowledged.');
        }

        $uid = $this->collectorId();
        $this->db->table('collector_handoffs')->where('id', $id)->update([
            'status'          => 'acknowledged',
            'acknowledged_by' => $uid,
            'acknowledged_at' => date('Y-m-d H:i:s'),
        ]);

        $this->logActivity('acknowledge_handoff', 'collector', $id, "Handoff #{$id} acknowledged");

        return redirect()->back()->with('success', 'Handoff acknowledged successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 10. Daily report (printable)
    // ─────────────────────────────────────────────────────────────────────────

    public function report(): string
    {
        $this->requireCollector();
        $uid  = $this->collectorId();
        $role = (string) session()->get('user_role');
        $date = $this->request->getGet('date') ?: date('Y-m-d');

        $payments = [];
        $total    = 0.0;

        if ($this->db->tableExists('lease_payments')) {
            $builder = $this->db->table('lease_payments lp')
                ->select('lp.*, t.full_name AS tenant_name, t.phone AS tenant_phone, f.name AS facility_name')
                ->join('tenants t', 't.id = lp.tenant_id', 'left')
                ->join('facilities f', 'f.id = lp.facility_id', 'left')
                ->where('lp.payment_date', $date)
                ->whereIn('lp.status', ['paid', 'partial'])
                ->orderBy('lp.payment_date', 'ASC');

            $lpFields = $this->db->getFieldNames('lease_payments');
            if (in_array('received_by', $lpFields, true)
                && ! in_array($role, ['super_admin', 'property_manager', 'finance_manager'], true)
            ) {
                $builder->where('lp.received_by', $uid);
            }

            $payments = $builder->get()->getResultArray();
            $total    = (float) array_sum(array_column($payments, 'amount'));
        }

        $collector = $this->currentUserProfile();
        $session   = null;
        if ($this->db->tableExists('collector_sessions')) {
            $session = $this->db->table('collector_sessions')
                ->where('collector_id', $uid)
                ->where('DATE(started_at)', $date)
                ->orderBy('id', 'DESC')
                ->limit(1)
                ->get()->getRowArray() ?: null;
        }

        return view('collector/report', $this->viewData([
            'title'     => 'Daily Collection Report — ' . $date,
            'date'      => $date,
            'payments'  => $payments,
            'total'     => $total,
            'collector' => $collector,
            'session'   => $session,
            'currency'  => $this->settings['currency'] ?? 'QAR',
            'usePdf'    => true,
        ]));
    }
}
