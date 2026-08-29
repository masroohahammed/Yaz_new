<?php

namespace App\Controllers;

/**
 * Tenant / Client Portal controller.
 *
 * Accessible to users with workspace = 'portal' (roles: client, tenant).
 * All data is scoped to the resolved tenant record so nothing leaks.
 */
class Portal extends BaseController
{
    protected ?string $workspaceRequired = null;

    // ─────────────────────────────────────────────────────────────────────────
    // Portal gate — must be called at the top of every action
    // ─────────────────────────────────────────────────────────────────────────

    private function requirePortal(): void
    {
        $ws = $this->currentWorkspace();
        if ($ws === 'portal' || $ws === 'both') {
            return;
        }

        // ERP staff who land on /portal by accident go back to their dashboard
        session()->setFlashdata('error', 'Tenant Portal is not available in your workspace.');
        redirect()->to(base_url('dashboard'))->send();
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Tenant resolver — call once and cache per request
    // ─────────────────────────────────────────────────────────────────────────

    /** null = tenant has not yet been loaded for this request. */
    private ?array $_tenant = null;

    /** Distinguishes "not loaded" from "loaded, no tenant row". */
    private bool $_tenantResolved = false;

    /**
     * Find the tenants row that corresponds to the logged-in portal user.
     *
     * Resolution order:
     *  1. users.tenant_id  (explicit FK set by admin)
     *  2. tenants.user_id = current user id
     *  3. tenants.email   = current user email
     *
     * Returns null when no tenant record can be found (guest tenant, new signup).
     */
    private function resolveTenant(): ?array
    {
        if ($this->_tenantResolved) {
            return $this->_tenant;
        }

        $this->_tenantResolved = true;
        $this->_tenant = null;

        $userId = (int) session()->get('user_id');
        $email  = (string) session()->get('user_email');

        if (! $this->db->tableExists('tenants')) {
            $this->_tenant = null;
            return null;
        }

        // 1. users.tenant_id
        if ($this->db->fieldExists('tenant_id', 'users')) {
            $row = $this->db->table('users')->select('tenant_id')->where('id', $userId)->get()->getRowArray();
            if (! empty($row['tenant_id'])) {
                $t = $this->db->table('tenants')->where('id', (int) $row['tenant_id'])->get()->getRowArray();
                if ($t) {
                    $this->_tenant = $t;
                    return $t;
                }
            }
        }

        // 2. tenants.user_id
        if ($this->db->fieldExists('user_id', 'tenants')) {
            $t = $this->db->table('tenants')->where('user_id', $userId)->get()->getRowArray();
            if ($t) {
                $this->_tenant = $t;
                return $t;
            }
        }

        // 3. tenants.email
        if ($email !== '' && $this->db->fieldExists('email', 'tenants')) {
            $t = $this->db->table('tenants')->where('email', $email)->get()->getRowArray();
            if ($t) {
                $this->_tenant = $t;
                return $t;
            }
        }

        $this->_tenant = null;
        return null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Dashboard
    // ─────────────────────────────────────────────────────────────────────────

    public function index(): string
    {
        $this->requirePortal();

        $tenant  = $this->resolveTenant();
        $tenantId = $tenant['id'] ?? null;
        $email   = (string) session()->get('user_email');
        $currency = $this->settings['currency'] ?? 'QAR';

        $activeLeases  = 0;
        $pendingPayments = 0;
        $openTickets   = 0;

        if ($tenantId && $this->db->tableExists('lease_contracts')) {
            $activeLeases = $this->db->table('lease_contracts')
                ->where('tenant_id', $tenantId)
                ->where('status', 'active')
                ->countAllResults();
        }

        if ($tenantId && $this->db->tableExists('lease_payments')) {
            $pendingPayments = $this->db->table('lease_payments')
                ->where('tenant_id', $tenantId)
                ->whereIn('status', ['pending', 'overdue'])
                ->countAllResults();
        }

        if ($this->db->tableExists('maintenance_requests')) {
            $q = $this->db->table('maintenance_requests')
                ->whereIn('status', ['pending', 'reviewed', 'approved']);
            if ($tenantId) {
                $q->groupStart()
                  ->where('tenant_id', $tenantId)
                  ->orWhere('requester_email', $email)
                  ->groupEnd();
            } else {
                $q->where('requester_email', $email);
            }
            $openTickets = $q->countAllResults();
        }

        // Recent leases
        $recentLeases = [];
        if ($tenantId && $this->db->tableExists('lease_contracts')) {
            $recentLeases = $this->db->table('lease_contracts lc')
                ->select('lc.id, lc.contract_number, lc.start_date, lc.end_date, lc.rent_amount, lc.status, f.name AS facility_name, u.unit_number')
                ->join('facilities f', 'f.id = lc.facility_id', 'left')
                ->join('units u', 'u.id = lc.unit_id', 'left')
                ->where('lc.tenant_id', $tenantId)
                ->orderBy('lc.created_at', 'DESC')
                ->limit(3)
                ->get()->getResultArray();
        }

        // Recent payments
        $recentPayments = [];
        if ($tenantId && $this->db->tableExists('lease_payments')) {
            $recentPayments = $this->db->table('lease_payments lp')
                ->select('lp.id, lp.payment_number, lp.amount, lp.status, lp.due_date, lp.payment_date, f.name AS facility_name')
                ->join('facilities f', 'f.id = lp.facility_id', 'left')
                ->where('lp.tenant_id', $tenantId)
                ->orderBy('lp.created_at', 'DESC')
                ->limit(4)
                ->get()->getResultArray();
        }

        // Recent tickets
        $recentTickets = [];
        if ($this->db->tableExists('maintenance_requests')) {
            $q = $this->db->table('maintenance_requests mr')
                ->select('mr.id, mr.ticket_number, mr.category, mr.priority, mr.status, mr.created_at, f.name AS facility_name')
                ->join('facilities f', 'f.id = mr.facility_id', 'left')
                ->orderBy('mr.created_at', 'DESC')
                ->limit(4);
            if ($tenantId) {
                $q->groupStart()
                  ->where('mr.tenant_id', $tenantId)
                  ->orWhere('mr.requester_email', $email)
                  ->groupEnd();
            } else {
                $q->where('mr.requester_email', $email);
            }
            $recentTickets = $q->get()->getResultArray();
        }

        return view('portal/dashboard', $this->viewData([
            'title'           => 'My Portal',
            'tenant'          => $tenant,
            'currency'        => $currency,
            'activeLeases'    => $activeLeases,
            'pendingPayments' => $pendingPayments,
            'openTickets'     => $openTickets,
            'recentLeases'    => $recentLeases,
            'recentPayments'  => $recentPayments,
            'recentTickets'   => $recentTickets,
        ]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Leases
    // ─────────────────────────────────────────────────────────────────────────

    public function leases(): string
    {
        $this->requirePortal();

        $tenant   = $this->resolveTenant();
        $tenantId = $tenant['id'] ?? null;
        $currency = $this->settings['currency'] ?? 'QAR';

        $leases = [];
        if ($tenantId && $this->db->tableExists('lease_contracts')) {
            $leases = $this->db->table('lease_contracts lc')
                ->select('lc.*, f.name AS facility_name, u.unit_number, u.floor_number')
                ->join('facilities f', 'f.id = lc.facility_id', 'left')
                ->join('units u', 'u.id = lc.unit_id', 'left')
                ->where('lc.tenant_id', $tenantId)
                ->orderBy('lc.status', 'ASC')
                ->orderBy('lc.end_date', 'DESC')
                ->get()->getResultArray();
        }

        return view('portal/leases', $this->viewData([
            'title'    => 'My Leases',
            'tenant'   => $tenant,
            'leases'   => $leases,
            'currency' => $currency,
        ]));
    }

    public function lease(int $id): string
    {
        $this->requirePortal();

        $tenant   = $this->resolveTenant();
        $tenantId = $tenant['id'] ?? null;
        $currency = $this->settings['currency'] ?? 'QAR';

        if (! $tenantId || ! $this->db->tableExists('lease_contracts')) {
            return redirect()->to(base_url('portal/leases'))->with('error', 'No lease records found.');
        }

        $lease = $this->db->table('lease_contracts lc')
            ->select('lc.*, f.name AS facility_name, f.address AS facility_address, u.unit_number, u.floor_number, u.area_sqm')
            ->join('facilities f', 'f.id = lc.facility_id', 'left')
            ->join('units u', 'u.id = lc.unit_id', 'left')
            ->where('lc.id', $id)
            ->where('lc.tenant_id', $tenantId)
            ->get()->getRowArray();

        if (! $lease) {
            return redirect()->to(base_url('portal/leases'))->with('error', 'Lease not found or access denied.');
        }

        // Payments for this contract
        $payments = [];
        if ($this->db->tableExists('lease_payments')) {
            $payments = $this->db->table('lease_payments')
                ->where('contract_id', $id)
                ->where('tenant_id', $tenantId)
                ->orderBy('due_date', 'ASC')
                ->get()->getResultArray();
        }

        return view('portal/lease_show', $this->viewData([
            'title'    => 'Lease ' . esc($lease['contract_number']),
            'tenant'   => $tenant,
            'lease'    => $lease,
            'payments' => $payments,
            'currency' => $currency,
        ]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Payments
    // ─────────────────────────────────────────────────────────────────────────

    public function payments(): string
    {
        $this->requirePortal();

        $tenant   = $this->resolveTenant();
        $tenantId = $tenant['id'] ?? null;
        $email    = (string) session()->get('user_email');
        $currency = $this->settings['currency'] ?? 'QAR';

        $payments = [];
        if ($tenantId && $this->db->tableExists('lease_payments')) {
            $payments = $this->db->table('lease_payments lp')
                ->select('lp.*, f.name AS facility_name, u.unit_number, lc.contract_number')
                ->join('facilities f', 'f.id = lp.facility_id', 'left')
                ->join('units u', 'u.id = lp.unit_id', 'left')
                ->join('lease_contracts lc', 'lc.id = lp.contract_id', 'left')
                ->where('lp.tenant_id', $tenantId)
                ->orderBy('lp.due_date', 'DESC')
                ->get()->getResultArray();
        }

        // Also look for regular invoices scoped to this tenant / email
        $invoices = [];
        if ($this->db->tableExists('invoices')) {
            $q = $this->db->table('invoices i')
                ->select('i.id, i.invoice_number, i.total, i.status, i.due_date, i.issue_date, f.name AS facility_name')
                ->join('facilities f', 'f.id = i.facility_id', 'left');
            if ($tenantId) {
                $q->groupStart()
                  ->where('i.tenant_id', $tenantId)
                  ->orWhere('i.created_by', (int) session()->get('user_id'))
                  ->groupEnd();
            } else {
                $q->where('i.created_by', (int) session()->get('user_id'));
            }
            $invoices = $q->orderBy('i.due_date', 'DESC')->limit(30)->get()->getResultArray();
        }

        return view('portal/payments', $this->viewData([
            'title'    => 'Payments & Invoices',
            'tenant'   => $tenant,
            'payments' => $payments,
            'invoices' => $invoices,
            'currency' => $currency,
        ]));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Maintenance Tickets
    // ─────────────────────────────────────────────────────────────────────────

    public function tickets(): string
    {
        $this->requirePortal();

        $tenant   = $this->resolveTenant();
        $tenantId = $tenant['id'] ?? null;
        $email    = (string) session()->get('user_email');

        $tickets = [];
        if ($this->db->tableExists('maintenance_requests')) {
            $q = $this->db->table('maintenance_requests mr')
                ->select('mr.id, mr.ticket_number, mr.title, mr.category, mr.priority, mr.status, mr.created_at, f.name AS facility_name, u.unit_number')
                ->join('facilities f', 'f.id = mr.facility_id', 'left')
                ->join('units u', 'u.id = mr.unit_id', 'left')
                ->orderBy('mr.created_at', 'DESC');

            if ($tenantId) {
                $q->groupStart()
                  ->where('mr.tenant_id', $tenantId)
                  ->orWhere('mr.requester_email', $email)
                  ->groupEnd();
            } else {
                $q->where('mr.requester_email', $email);
            }

            $tickets = $q->get()->getResultArray();
        }

        return view('portal/tickets', $this->viewData([
            'title'   => 'My Maintenance Tickets',
            'tenant'  => $tenant,
            'tickets' => $tickets,
        ]));
    }

    public function createTicket(): string
    {
        $this->requirePortal();

        $tenant = $this->resolveTenant();

        // Build unit list — if tenant has a lease, pre-select unit
        $units = [];
        if ($tenant && $this->db->tableExists('units')) {
            $units = $this->tenantUnits((int) $tenant['id']);
        }

        return view('portal/ticket_form', $this->viewData([
            'title'  => 'Submit Maintenance Ticket',
            'tenant' => $tenant,
            'units'  => $units,
        ]));
    }

    public function storeTicket()
    {
        $this->requirePortal();

        $rules = [
            'title'       => 'required|min_length[5]|max_length[200]',
            'category'    => 'required',
            'priority'    => 'required|in_list[low,medium,high,critical]',
            'description' => 'required|min_length[10]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tenant   = $this->resolveTenant();
        $tenantId = $tenant['id'] ?? null;
        $user     = $this->currentUser();

        // Resolve facility/unit
        $unitId     = (int) ($this->request->getPost('unit_id') ?? 0);
        $facilityId = 0;
        if ($tenantId && $unitId > 0 && ! $this->tenantOwnsUnit((int) $tenantId, $unitId)) {
            return redirect()->back()->withInput()->with('error', 'Selected unit is not linked to your account.');
        }
        if ($unitId > 0 && $this->db->tableExists('units')) {
            $unitRow = $this->db->table('units')->where('id', $unitId)->get()->getRowArray();
            $facilityId = (int) ($unitRow['facility_id'] ?? 0);
        }

        $ticketNumber = $this->generateNumber('TKT', 'maintenance_requests', 'ticket_number');

        $photo = null;
        $file  = $this->request->getFile('photo');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/', $newName);
            $photo = $newName;
        }

        $data = [
            'ticket_number'  => $ticketNumber,
            'requester_name' => $user['name'],
            'requester_email'=> $user['email'],
            'title'          => $this->request->getPost('title'),
            'category'       => $this->request->getPost('category'),
            'priority'       => $this->request->getPost('priority'),
            'description'    => $this->request->getPost('description'),
            'status'         => 'pending',
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s'),
        ];

        if ($facilityId > 0) {
            $data['facility_id'] = $facilityId;
        }
        if ($unitId > 0) {
            $data['unit_id'] = $unitId;
        }
        if ($tenantId) {
            $data['tenant_id'] = $tenantId;
        }
        $companyId = (int) ($tenant['company_id'] ?? session()->get('company_id') ?? 0);
        if ($companyId > 0 && $this->db->fieldExists('company_id', 'maintenance_requests')) {
            $data['company_id'] = $companyId;
        }
        if ($photo) {
            $data['photo'] = $photo;
        }

        try {
            $this->db->table('maintenance_requests')->insert($data);
            $newId = (int) $this->db->insertID();
        } catch (\Throwable $e) {
            log_message('error', 'Portal ticket insert failed: ' . $e->getMessage());

            return redirect()->back()->withInput()->with('error', 'Could not submit the ticket. Please try again.');
        }

        $this->logActivity('create', 'portal_ticket', $newId, "Ticket {$ticketNumber} submitted via portal");
        $this->notifyManagers(
            'New Maintenance Ticket: ' . esc($this->request->getPost('title')),
            "Submitted by {$user['name']} via Tenant Portal. Priority: " . ucfirst($this->request->getPost('priority'))
        );

        return redirect()->to(base_url('portal/tickets'))
            ->with('success', "Ticket #{$ticketNumber} submitted successfully. Our team will be in touch.");
    }

    /** @return list<array<string,mixed>> */
    private function tenantUnits(int $tenantId): array
    {
        if ($tenantId < 1 || ! $this->db->tableExists('lease_contracts')) {
            return [];
        }

        $q = $this->db->table('units u')
            ->select('u.id, u.unit_number, f.name AS facility_name')
            ->join('lease_contracts lc', 'lc.unit_id = u.id')
            ->join('facilities f', 'f.id = u.facility_id', 'left')
            ->where('lc.tenant_id', $tenantId)
            ->where('lc.status', 'active')
            ->where('lc.deleted_at', null)
            ->groupBy('u.id, u.unit_number, f.name')
            ->orderBy('f.name', 'ASC')
            ->orderBy('u.unit_number', 'ASC');

        return $q->get()->getResultArray();
    }

    private function tenantOwnsUnit(int $tenantId, int $unitId): bool
    {
        foreach ($this->tenantUnits($tenantId) as $row) {
            if ((int) $row['id'] === $unitId) {
                return true;
            }
        }

        return false;
    }
}
