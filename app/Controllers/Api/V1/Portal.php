<?php

namespace App\Controllers\Api\V1;

/**
 * Tenant Portal mobile API (Property Management).
 *
 * Scoped to the authenticated user's linked tenant record.
 *
 * Routes (all JWT):
 *  GET  /api/v1/portal/contracts
 *  GET  /api/v1/portal/contracts/{id}
 *  GET  /api/v1/portal/payments
 *  GET  /api/v1/portal/payments/{id}
 *  GET  /api/v1/portal/requests
 *  GET  /api/v1/portal/requests/{id}
 *  POST /api/v1/portal/requests
 *  POST /api/v1/portal/requests/{id}/messages
 *  GET  /api/v1/portal/documents/{id}/download
 */
class Portal extends BaseApiController
{
    /** @var array<string,mixed>|false|null */
    private $tenantCache = false;

    public function contracts()
    {
        $tenant = $this->requireTenant();
        if ($tenant instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $tenant;
        }

        $tenantId = (int) $tenant['id'];
        $currency = $this->currency();

        $rows = [];
        if ($this->db->tableExists('lease_contracts')) {
            $rows = $this->db->table('lease_contracts lc')
                ->select('lc.id, lc.contract_number, lc.status, lc.start_date, lc.end_date, lc.rent_amount, lc.security_deposit, lc.payment_frequency, f.name AS facility_name, u.unit_number')
                ->join('facilities f', 'f.id = lc.facility_id', 'left')
                ->join('units u', 'u.id = lc.unit_id', 'left')
                ->where('lc.tenant_id', $tenantId)
                ->where('lc.deleted_at', null)
                ->orderBy('lc.status', 'ASC')
                ->orderBy('lc.end_date', 'DESC')
                ->get()
                ->getResultArray();
        }

        $data = array_map(function (array $r) use ($currency, $tenant) {
            return [
                'id'               => (int) $r['id'],
                'contract_number'  => $r['contract_number'],
                'status'           => $r['status'],
                'tenant_name'      => $tenant['full_name'] ?? '',
                'property_unit'    => trim(($r['facility_name'] ?? '') . ', ' . ($r['unit_number'] ?? ''), ', '),
                'facility_name'    => $r['facility_name'] ?? '',
                'unit_number'      => $r['unit_number'] ?? '',
                'start_date'       => $r['start_date'],
                'end_date'         => $r['end_date'],
                'annual_rent'      => $this->annualRent((float) $r['rent_amount'], $r['payment_frequency'] ?? 'monthly'),
                'rent_amount'      => (float) $r['rent_amount'],
                'security_deposit' => (float) ($r['security_deposit'] ?? 0),
                'currency'         => $currency,
            ];
        }, $rows);

        return $this->response->setJSON([
            'status' => true,
            'data'   => $data,
            'count'  => count($data),
        ]);
    }

    public function contract(int $id)
    {
        $tenant = $this->requireTenant();
        if ($tenant instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $tenant;
        }

        $tenantId = (int) $tenant['id'];
        $currency = $this->currency();

        if (! $this->db->tableExists('lease_contracts')) {
            return $this->fail('Lease module unavailable', 404);
        }

        $lease = $this->db->table('lease_contracts lc')
            ->select('lc.*, f.name AS facility_name, f.address AS facility_address, f.city AS facility_city, u.unit_number, u.floor, u.area_sqft')
            ->join('facilities f', 'f.id = lc.facility_id', 'left')
            ->join('units u', 'u.id = lc.unit_id', 'left')
            ->where('lc.id', $id)
            ->where('lc.tenant_id', $tenantId)
            ->where('lc.deleted_at', null)
            ->get()
            ->getRowArray();

        if (! $lease) {
            return $this->fail('Contract not found', 404);
        }

        $documents = $this->contractDocuments($id, (int) $lease['facility_id']);

        return $this->response->setJSON([
            'status' => true,
            'data'   => [
                'id'               => (int) $lease['id'],
                'contract_number'  => $lease['contract_number'],
                'status'           => $lease['status'],
                'tenant_name'      => $tenant['full_name'] ?? '',
                'property_unit'    => trim(($lease['facility_name'] ?? '') . ', ' . ($lease['unit_number'] ?? ''), ', '),
                'facility_name'    => $lease['facility_name'] ?? '',
                'facility_address' => $lease['facility_address'] ?? '',
                'unit_number'      => $lease['unit_number'] ?? '',
                'floor'            => $lease['floor'] ?? null,
                'area_sqft'        => isset($lease['area_sqft']) ? (float) $lease['area_sqft'] : null,
                'start_date'       => $lease['start_date'],
                'end_date'         => $lease['end_date'],
                'signed_date'      => $lease['signed_date'] ?? null,
                'annual_rent'      => $this->annualRent((float) $lease['rent_amount'], $lease['payment_frequency'] ?? 'monthly'),
                'rent_amount'      => (float) $lease['rent_amount'],
                'security_deposit' => (float) ($lease['security_deposit'] ?? 0),
                'payment_frequency'=> $lease['payment_frequency'] ?? 'monthly',
                'currency'         => $currency,
                'hero_image_url'   => null,
                'documents'        => $documents,
            ],
        ]);
    }

    public function payments()
    {
        $tenant = $this->requireTenant();
        if ($tenant instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $tenant;
        }

        $tenantId = (int) $tenant['id'];
        $currency = $this->currency();
        $search   = trim((string) ($this->request->getGet('q') ?? ''));
        $status   = trim((string) ($this->request->getGet('status') ?? ''));
        $page     = max(1, (int) ($this->request->getGet('page') ?? 1));
        $perPage  = min(50, max(1, (int) ($this->request->getGet('per_page') ?? 20)));
        $offset   = ($page - 1) * $perPage;

        $overview = [
            'currency'          => $currency,
            'total_outstanding' => 0.0,
            'due_in_days'       => null,
            'paid_ytd'          => 0.0,
            'invoice_count'     => 0,
            'upcoming_amount'   => 0.0,
            'upcoming_date'     => null,
        ];

        $history = [];
        $total   = 0;

        if ($this->db->tableExists('lease_payments')) {
            $outstanding = $this->db->table('lease_payments')
                ->selectSum('amount', 't')
                ->where('tenant_id', $tenantId)
                ->whereIn('status', ['pending', 'overdue', 'partial'])
                ->get()
                ->getRowArray();
            $overview['total_outstanding'] = (float) ($outstanding['t'] ?? 0);

            $nextDue = $this->db->table('lease_payments')
                ->select('due_date, amount')
                ->where('tenant_id', $tenantId)
                ->whereIn('status', ['pending', 'overdue', 'partial'])
                ->where('due_date >=', date('Y-m-d'))
                ->orderBy('due_date', 'ASC')
                ->limit(1)
                ->get()
                ->getRowArray();

            if ($nextDue) {
                $overview['upcoming_amount'] = (float) $nextDue['amount'];
                $overview['upcoming_date']   = $nextDue['due_date'];
                $overview['due_in_days']     = (int) max(0, (strtotime($nextDue['due_date']) - strtotime(date('Y-m-d'))) / 86400);
            } else {
                $overdueDue = $this->db->table('lease_payments')
                    ->select('due_date')
                    ->where('tenant_id', $tenantId)
                    ->where('status', 'overdue')
                    ->orderBy('due_date', 'ASC')
                    ->limit(1)
                    ->get()
                    ->getRowArray();
                if ($overdueDue) {
                    $overview['due_in_days'] = (int) ((strtotime($overdueDue['due_date']) - strtotime(date('Y-m-d'))) / 86400);
                }
            }

            $paidYtd = $this->db->table('lease_payments')
                ->selectSum('amount', 't')
                ->where('tenant_id', $tenantId)
                ->where('status', 'paid')
                ->where('payment_date >=', date('Y-01-01'))
                ->get()
                ->getRowArray();
            $overview['paid_ytd'] = (float) ($paidYtd['t'] ?? 0);
            $overview['invoice_count'] = $this->db->table('lease_payments')
                ->where('tenant_id', $tenantId)
                ->where('status', 'paid')
                ->where('payment_date >=', date('Y-01-01'))
                ->countAllResults();

            $countQ = $this->db->table('lease_payments lp')
                ->join('lease_contracts lc', 'lc.id = lp.contract_id', 'left')
                ->where('lp.tenant_id', $tenantId);
            if ($status !== '') {
                $countQ->where('lp.status', $status);
            }
            if ($search !== '') {
                $countQ->groupStart()
                    ->like('lp.payment_number', $search)
                    ->orLike('lc.contract_number', $search)
                    ->orLike('lp.reference_no', $search)
                    ->groupEnd();
            }
            $total = $countQ->countAllResults();

            $builder = $this->db->table('lease_payments lp')
                ->select('lp.*, f.name AS facility_name, u.unit_number, lc.contract_number')
                ->join('facilities f', 'f.id = lp.facility_id', 'left')
                ->join('units u', 'u.id = lp.unit_id', 'left')
                ->join('lease_contracts lc', 'lc.id = lp.contract_id', 'left')
                ->where('lp.tenant_id', $tenantId);

            if ($status !== '') {
                $builder->where('lp.status', $status);
            }
            if ($search !== '') {
                $builder->groupStart()
                    ->like('lp.payment_number', $search)
                    ->orLike('lc.contract_number', $search)
                    ->orLike('lp.reference_no', $search)
                    ->groupEnd();
            }

            $rows = $builder->orderBy('lp.due_date', 'DESC')
                ->limit($perPage, $offset)
                ->get()
                ->getResultArray();

            foreach ($rows as $r) {
                $history[] = $this->mapPayment($r, $currency);
            }
        }

        return $this->response->setJSON([
            'status'   => true,
            'overview' => $overview,
            'data'     => $history,
            'meta'     => [
                'page'     => $page,
                'per_page' => $perPage,
                'total'    => $total,
            ],
        ]);
    }

    public function payment(int $id)
    {
        $tenant = $this->requireTenant();
        if ($tenant instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $tenant;
        }

        if (! $this->db->tableExists('lease_payments')) {
            return $this->fail('Payments module unavailable', 404);
        }

        $row = $this->db->table('lease_payments lp')
            ->select('lp.*, f.name AS facility_name, u.unit_number, lc.contract_number')
            ->join('facilities f', 'f.id = lp.facility_id', 'left')
            ->join('units u', 'u.id = lp.unit_id', 'left')
            ->join('lease_contracts lc', 'lc.id = lp.contract_id', 'left')
            ->where('lp.id', $id)
            ->where('lp.tenant_id', (int) $tenant['id'])
            ->get()
            ->getRowArray();

        if (! $row) {
            return $this->fail('Payment not found', 404);
        }

        return $this->response->setJSON([
            'status' => true,
            'data'   => $this->mapPayment($row, $this->currency()),
        ]);
    }

    public function requests()
    {
        $user   = $this->jwtUser();
        $tenant = $this->resolveTenant($user);
        $email  = (string) ($user['email'] ?? '');

        $rows = [];
        if ($this->db->tableExists('maintenance_requests')) {
            $q = $this->db->table('maintenance_requests mr')
                ->select('mr.id, mr.ticket_number, mr.title, mr.category, mr.priority, mr.status, mr.created_at, mr.description, f.name AS facility_name, u.unit_number')
                ->join('facilities f', 'f.id = mr.facility_id', 'left')
                ->join('units u', 'u.id = mr.unit_id', 'left')
                ->orderBy('mr.created_at', 'DESC');

            $this->applyTicketScope($q, $tenant, $email, true);
            $rows = $q->get()->getResultArray();
        }

        $data = array_map(fn (array $r) => $this->mapTicketList($r), $rows);

        return $this->response->setJSON([
            'status' => true,
            'data'   => $data,
            'count'  => count($data),
        ]);
    }

    public function request(int $id)
    {
        $user   = $this->jwtUser();
        $tenant = $this->resolveTenant($user);
        $email  = (string) ($user['email'] ?? '');

        if (! $this->db->tableExists('maintenance_requests')) {
            return $this->fail('Requests module unavailable', 404);
        }

        $q = $this->db->table('maintenance_requests mr')
            ->select('mr.*, f.name AS facility_name, u.unit_number')
            ->join('facilities f', 'f.id = mr.facility_id', 'left')
            ->join('units u', 'u.id = mr.unit_id', 'left')
            ->where('mr.id', $id);
        $this->applyTicketScope($q, $tenant, $email, true);
        $ticket = $q->get()->getRowArray();

        if (! $ticket) {
            return $this->fail('Request not found', 404);
        }

        $progress = $this->ticketProgress($ticket);
        $photos   = $this->ticketPhotos($ticket);
        $tech     = $this->ticketTechnician($ticket);
        $activity = $this->ticketActivity((int) $ticket['id'], (int) ($ticket['converted_to_wo'] ?? 0));

        $title = $ticket['title'] ?? null;
        if (! $title) {
            $title = trim(($ticket['category'] ?? 'Request') . ' – ' . substr((string) $ticket['description'], 0, 60));
        }

        return $this->response->setJSON([
            'status' => true,
            'data'   => [
                'id'              => (int) $ticket['id'],
                'reference'       => $ticket['ticket_number'],
                'title'           => $title,
                'category'        => $ticket['category'] ?? '',
                'priority'        => $ticket['priority'] ?? 'medium',
                'status'          => $ticket['status'],
                'description'     => $ticket['description'],
                'created_at'      => $ticket['created_at'],
                'facility_name'   => $ticket['facility_name'] ?? '',
                'unit_number'     => $ticket['unit_number'] ?? '',
                'expected_response_hours' => 24,
                'progress'        => $progress,
                'photos'          => $photos,
                'technician'      => $tech,
                'activity'        => $activity,
            ],
        ]);
    }

    public function storeRequest()
    {
        $user = $this->jwtUser();
        if (! $user) {
            return $this->fail('Unauthorized', 401);
        }

        $payload = $this->request->getJSON(true) ?? $this->request->getPost();
        $title       = trim((string) ($payload['title'] ?? ''));
        $category    = trim((string) ($payload['category'] ?? 'Maintenance'));
        $priority    = strtolower(trim((string) ($payload['priority'] ?? 'medium')));
        $description = trim((string) ($payload['description'] ?? ''));
        $unitId      = (int) ($payload['unit_id'] ?? 0);

        if (strlen($title) < 5 || strlen($description) < 10) {
            return $this->fail('Title (min 5) and description (min 10) are required', 422);
        }
        if (! in_array($priority, ['low', 'medium', 'high', 'critical'], true)) {
            return $this->fail('Invalid priority', 422);
        }

        $tenant   = $this->resolveTenant($user);
        $tenantId = $tenant['id'] ?? null;

        $facilityId = 0;
        if ($unitId > 0 && $this->db->tableExists('units')) {
            $unitRow = $this->db->table('units')->where('id', $unitId)->get()->getRowArray();
            $facilityId = (int) ($unitRow['facility_id'] ?? 0);
        } elseif ($tenantId && $this->db->tableExists('lease_contracts')) {
            $active = $this->db->table('lease_contracts')
                ->where('tenant_id', (int) $tenantId)
                ->where('status', 'active')
                ->where('deleted_at', null)
                ->orderBy('id', 'DESC')
                ->get()
                ->getRowArray();
            if ($active) {
                $facilityId = (int) $active['facility_id'];
                $unitId     = (int) $active['unit_id'];
            }
        }

        $ticketNumber = $this->generateNumber('REQ', 'maintenance_requests', 'ticket_number');

        $data = [
            'ticket_number'   => $ticketNumber,
            'requester_name'  => $user['name'],
            'requester_email' => $user['email'],
            'category'        => $category,
            'description'     => $description,
            'priority'        => $priority,
            'status'          => 'pending',
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        if ($this->db->fieldExists('title', 'maintenance_requests')) {
            $data['title'] = $title;
        } else {
            // Fallback: prepend title into description
            $data['description'] = $title . "\n\n" . $description;
        }
        if ($facilityId > 0) {
            $data['facility_id'] = $facilityId;
        }
        if ($unitId > 0 && $this->db->fieldExists('unit_id', 'maintenance_requests')) {
            $data['unit_id'] = $unitId;
        }
        if ($tenantId && $this->db->fieldExists('tenant_id', 'maintenance_requests')) {
            $data['tenant_id'] = (int) $tenantId;
        }

        // Optional multipart photo
        $file = $this->request->getFile('photo');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $newName = $file->getRandomName();
            $dir     = WRITEPATH . 'uploads/helpdesk/';
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $file->move($dir, $newName);
            $path = 'uploads/helpdesk/' . $newName;
            if ($this->db->fieldExists('photo', 'maintenance_requests')) {
                $data['photo'] = $path;
            }
            if ($this->db->fieldExists('image_path', 'maintenance_requests')) {
                $data['image_path'] = $path;
            }
        }

        $this->db->table('maintenance_requests')->insert($data);
        $newId = (int) $this->db->insertID();

        $this->logActivity('create', 'portal_ticket', $newId, "Ticket {$ticketNumber} submitted via mobile API");

        return $this->response->setStatusCode(201)->setJSON([
            'status'  => true,
            'message' => 'Request submitted successfully',
            'data'    => [
                'id'              => $newId,
                'reference'       => $ticketNumber,
                'category'        => $category,
                'expected_response_hours' => 24,
            ],
        ]);
    }

    public function storeMessage(int $id)
    {
        $user   = $this->jwtUser();
        $tenant = $this->resolveTenant($user);
        $email  = (string) ($user['email'] ?? '');

        $q = $this->db->table('maintenance_requests')->where('id', $id);
        $this->applyTicketScope($q, $tenant, $email, false);
        $ticket = $q->get()->getRowArray();
        if (! $ticket) {
            return $this->fail('Request not found', 404);
        }

        $payload = $this->request->getJSON(true) ?? $this->request->getPost();
        $message = trim((string) ($payload['message'] ?? ''));
        if ($message === '') {
            return $this->fail('Message is required', 422);
        }

        $woId = (int) ($ticket['converted_to_wo'] ?? 0);
        if ($woId > 0 && $this->db->tableExists('wo_chat_messages')) {
            $this->db->table('wo_chat_messages')->insert([
                'work_order_id' => $woId,
                'user_id'       => (int) $user['id'],
                'message'       => $message,
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        } else {
            $this->logActivity('message', 'portal_ticket', $id, $message);
        }

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Message sent',
        ]);
    }

    public function downloadDocument(int $id)
    {
        $tenant = $this->requireTenant();
        if ($tenant instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $tenant;
        }

        if (! $this->db->tableExists('documents')) {
            return $this->fail('Document not found', 404);
        }

        $doc = $this->db->table('documents')->where('id', $id)->get()->getRowArray();
        if (! $doc || empty($doc['file_path'])) {
            return $this->fail('Document not found', 404);
        }

        // Authorize: document linked to tenant contract/facility
        $allowed = false;
        $tenantId = (int) $tenant['id'];
        if ($this->db->fieldExists('module', 'documents') && ($doc['module'] ?? '') === 'lease' && ! empty($doc['ref_id'])) {
            $lease = $this->db->table('lease_contracts')
                ->where('id', (int) $doc['ref_id'])
                ->where('tenant_id', $tenantId)
                ->get()
                ->getRowArray();
            $allowed = (bool) $lease;
        } elseif (! empty($doc['facility_id']) && $this->db->tableExists('lease_contracts')) {
            $lease = $this->db->table('lease_contracts')
                ->where('facility_id', (int) $doc['facility_id'])
                ->where('tenant_id', $tenantId)
                ->get()
                ->getRowArray();
            $allowed = (bool) $lease;
        }

        if (! $allowed) {
            return $this->fail('Access denied', 403);
        }

        $path = $doc['file_path'];
        if (! is_file($path)) {
            $candidates = [
                WRITEPATH . ltrim($path, '/'),
                WRITEPATH . 'uploads/' . ltrim($path, '/'),
                FCPATH . ltrim($path, '/'),
                ROOTPATH . ltrim($path, '/'),
            ];
            $path = null;
            foreach ($candidates as $c) {
                if (is_file($c)) {
                    $path = $c;
                    break;
                }
            }
        }

        if (! $path) {
            return $this->fail('File missing on server', 404);
        }

        return $this->response->download($path, null);
    }

    // ── helpers ──────────────────────────────────────────────────────────

    private function requireTenant()
    {
        $user = $this->jwtUser();
        if (! $user) {
            return $this->fail('Unauthorized', 401);
        }

        $tenant = $this->resolveTenant($user);
        if (! $tenant) {
            return $this->fail('No tenant profile linked to this account', 403);
        }

        return $tenant;
    }

    /** @param array<string,mixed>|null $user */
    private function resolveTenant(?array $user): ?array
    {
        if ($this->tenantCache !== false) {
            return $this->tenantCache;
        }

        if (! $user || ! $this->db->tableExists('tenants')) {
            $this->tenantCache = null;
            return null;
        }

        $userId = (int) $user['id'];
        $email  = (string) ($user['email'] ?? '');

        if ($this->db->fieldExists('tenant_id', 'users') && ! empty($user['tenant_id'])) {
            $t = $this->db->table('tenants')->where('id', (int) $user['tenant_id'])->get()->getRowArray();
            if ($t) {
                return $this->tenantCache = $t;
            }
        }

        if ($this->db->fieldExists('user_id', 'tenants')) {
            $t = $this->db->table('tenants')->where('user_id', $userId)->get()->getRowArray();
            if ($t) {
                return $this->tenantCache = $t;
            }
        }

        if ($email !== '' && $this->db->fieldExists('email', 'tenants')) {
            $t = $this->db->table('tenants')->where('email', $email)->get()->getRowArray();
            if ($t) {
                return $this->tenantCache = $t;
            }
        }

        return $this->tenantCache = null;
    }

    private function currency(): string
    {
        if ($this->db->tableExists('system_settings')) {
            $row = $this->db->table('system_settings')->where('setting_key', 'currency')->get()->getRowArray();
            if ($row && ! empty($row['setting_value'])) {
                return (string) $row['setting_value'];
            }
        }

        return 'QAR';
    }

    private function annualRent(float $rent, string $frequency): float
    {
        return match (strtolower($frequency)) {
            'yearly', 'annual', 'annually' => $rent,
            'quarterly' => $rent * 4,
            'weekly' => $rent * 52,
            default => $rent * 12, // monthly
        };
    }

    private function fail(string $message, int $code)
    {
        return $this->response->setStatusCode($code)->setJSON([
            'status'  => false,
            'message' => $message,
        ]);
    }

    /** @return list<array<string,mixed>> */
    private function contractDocuments(int $contractId, int $facilityId): array
    {
        if (! $this->db->tableExists('documents')) {
            return [];
        }

        $docs = [];
        $builder = $this->db->table('documents')->orderBy('id', 'DESC');

        if ($this->db->fieldExists('module', 'documents') && $this->db->fieldExists('ref_id', 'documents')) {
            $leaseDocs = (clone $builder)
                ->where('module', 'lease')
                ->where('ref_id', $contractId)
                ->get()
                ->getResultArray();
            $docs = array_merge($docs, $leaseDocs);
        }

        if ($facilityId > 0) {
            $facilityDocs = $this->db->table('documents')
                ->where('facility_id', $facilityId)
                ->orderBy('id', 'DESC')
                ->limit(20)
                ->get()
                ->getResultArray();
            $docs = array_merge($docs, $facilityDocs);
        }

        // Deduplicate by id
        $seen = [];
        $out  = [];
        foreach ($docs as $d) {
            $did = (int) $d['id'];
            if (isset($seen[$did])) {
                continue;
            }
            $seen[$did] = true;
            $fileName = basename((string) ($d['file_path'] ?? $d['title'] ?? 'document'));
            $out[] = [
                'id'           => $did,
                'title'        => $d['title'] ?? $fileName,
                'file_name'    => $fileName,
                'doc_type'     => $d['doc_type'] ?? 'general',
                'is_pdf'       => str_ends_with(strtolower($fileName), '.pdf'),
                'download_url' => base_url('api/v1/portal/documents/' . $did . '/download'),
            ];
        }

        return $out;
    }

    /** @param array<string,mixed> $r */
    private function mapPayment(array $r, string $currency): array
    {
        $status = $r['status'] ?? 'pending';
        $isPaid = $status === 'paid';

        return [
            'id'              => (int) $r['id'],
            'payment_number'  => $r['payment_number'],
            'contract_number' => $r['contract_number'] ?? null,
            'amount'          => (float) $r['amount'],
            'currency'        => $currency,
            'status'          => $status,
            'is_paid'         => $isPaid,
            'receipt_issued'  => $isPaid,
            'payment_date'    => $r['payment_date'] ?? null,
            'due_date'        => $r['due_date'] ?? null,
            'period_from'     => $r['period_from'] ?? null,
            'period_to'       => $r['period_to'] ?? null,
            'payment_method'  => $r['payment_method'] ?? null,
            'reference_no'    => $r['reference_no'] ?? $r['payment_number'],
            'facility_name'   => $r['facility_name'] ?? '',
            'unit_number'     => $r['unit_number'] ?? '',
            'receipt_url'     => $isPaid ? base_url('api/v1/portal/payments/' . (int) $r['id']) : null,
        ];
    }

    /**
     * @param object $q Query builder
     * @param bool   $aliased true when table alias is `mr`
     */
    private function applyTicketScope($q, ?array $tenant, string $email, bool $aliased): void
    {
        $tenantCol = $aliased ? 'mr.tenant_id' : 'tenant_id';
        $emailCol  = $aliased ? 'mr.requester_email' : 'requester_email';
        $tenantId  = $tenant['id'] ?? null;

        if ($tenantId && $this->db->fieldExists('tenant_id', 'maintenance_requests')) {
            $q->groupStart()
                ->where($tenantCol, (int) $tenantId)
                ->orWhere($emailCol, $email)
                ->groupEnd();
            return;
        }

        $q->where($emailCol, $email);
    }

    /** @param array<string,mixed> $r */
    private function mapTicketList(array $r): array
    {
        $title = $r['title'] ?? null;
        if (! $title) {
            $title = trim(($r['category'] ?? 'Request') . ' – ' . substr((string) ($r['description'] ?? ''), 0, 40));
        }

        return [
            'id'            => (int) $r['id'],
            'reference'     => $r['ticket_number'],
            'title'         => $title,
            'category'      => $r['category'] ?? '',
            'priority'      => $r['priority'] ?? 'medium',
            'status'        => $r['status'],
            'created_at'    => $r['created_at'],
            'facility_name' => $r['facility_name'] ?? '',
            'unit_number'   => $r['unit_number'] ?? '',
        ];
    }

    /** @param array<string,mixed> $ticket */
    private function ticketProgress(array $ticket): array
    {
        $status = $ticket['status'] ?? 'pending';
        $woId   = (int) ($ticket['converted_to_wo'] ?? 0);
        $woStatus = null;
        $wo       = null;
        if ($woId > 0 && $this->db->tableExists('work_orders')) {
            $wo = $this->db->table('work_orders')->select('status, created_at, updated_at')->where('id', $woId)->get()->getRowArray();
            $woStatus = $wo['status'] ?? null;
        }

        $steps = [
            ['key' => 'submitted', 'label' => 'Submitted', 'at' => $ticket['created_at'] ?? null],
            ['key' => 'assigned', 'label' => 'Assigned', 'at' => $ticket['reviewed_at'] ?? $ticket['verified_at'] ?? null],
            ['key' => 'in_progress', 'label' => 'In Progress', 'at' => null],
            ['key' => 'completed', 'label' => 'Completed', 'at' => null],
        ];

        $done = 1;
        $active = 0;

        if (in_array($status, ['reviewed', 'converted'], true) || ($ticket['approval_status'] ?? '') === 'approved') {
            $done = 2;
            $active = 1;
        }
        if ($status === 'converted' || in_array($woStatus, ['assigned', 'in_progress', 'completed'], true)) {
            $done = 3;
            $active = 2;
            if ($wo && ($woStatus === 'in_progress' || $woStatus === 'assigned')) {
                $steps[2]['at'] = $wo['updated_at'] ?? $wo['created_at'] ?? null;
            }
        }
        if ($woStatus === 'completed' || $status === 'rejected') {
            $done = 4;
            $active = 3;
            $steps[3]['at'] = $wo['updated_at'] ?? null;
        }

        return [
            'steps'        => $steps,
            'done_count'   => $done,
            'active_index' => $active,
        ];
    }

    /** @param array<string,mixed> $ticket
     *  @return list<array{url:string,caption:?string}>
     */
    private function ticketPhotos(array $ticket): array
    {
        $photos = [];
        foreach (['photo', 'image_path'] as $col) {
            if (! empty($ticket[$col])) {
                $rel = (string) $ticket[$col];
                $photos[] = [
                    'url'     => base_url(ltrim($rel, '/')),
                    'caption' => null,
                ];
            }
        }

        if ($this->db->tableExists('media_albums') && $this->db->tableExists('media_items')) {
            $albums = $this->db->table('media_albums')
                ->where('module', 'maintenance_request')
                ->where('ref_id', (int) $ticket['id'])
                ->where('deleted_at', null)
                ->get()
                ->getResultArray();
            foreach ($albums as $album) {
                $items = $this->db->table('media_items')
                    ->where('album_id', (int) $album['id'])
                    ->orderBy('sort_order', 'ASC')
                    ->get()
                    ->getResultArray();
                foreach ($items as $item) {
                    $photos[] = [
                        'url'     => base_url(ltrim((string) $item['file_path'], '/')),
                        'caption' => $item['caption'] ?? null,
                    ];
                }
            }
        }

        return $photos;
    }

    /** @param array<string,mixed> $ticket */
    private function ticketTechnician(array $ticket): ?array
    {
        $woId = (int) ($ticket['converted_to_wo'] ?? 0);
        if ($woId < 1 || ! $this->db->tableExists('work_orders')) {
            return null;
        }

        $wo = $this->db->table('work_orders')->where('id', $woId)->get()->getRowArray();
        if (! $wo) {
            return null;
        }

        $techId = (int) ($wo['assigned_to'] ?? $wo['technician_id'] ?? $wo['supervisor_id'] ?? 0);
        if ($techId < 1) {
            return null;
        }

        $user = $this->db->table('users')->where('id', $techId)->get()->getRowArray();
        if (! $user) {
            return null;
        }

        return [
            'id'         => (int) $user['id'],
            'name'       => $user['name'],
            'role'       => 'Technician',
            'avatar_url' => null,
            'phone'      => $user['phone'] ?? null,
        ];
    }

    /** @return list<array<string,mixed>> */
    private function ticketActivity(int $ticketId, int $woId): array
    {
        $items = [];

        if ($this->db->tableExists('activity_logs')) {
            $logs = $this->db->table('activity_logs')
                ->whereIn('module', ['helpdesk', 'portal_ticket', 'maintenance_requests'])
                ->where('record_id', $ticketId)
                ->orderBy('created_at', 'ASC')
                ->get()
                ->getResultArray();
            foreach ($logs as $log) {
                $items[] = [
                    'title' => $log['description'] ?: ucfirst((string) $log['action']),
                    'at'    => $log['created_at'],
                    'icon'  => $log['action'] === 'message' ? 'chat' : 'activity',
                ];
            }
        }

        if ($woId > 0 && $this->db->tableExists('wo_comments')) {
            $comments = $this->db->table('wo_comments')
                ->where('work_order_id', $woId)
                ->orderBy('created_at', 'ASC')
                ->get()
                ->getResultArray();
            foreach ($comments as $c) {
                $items[] = [
                    'title' => $c['comment'] ?? $c['message'] ?? 'Update',
                    'at'    => $c['created_at'] ?? null,
                    'icon'  => 'pin',
                ];
            }
        }

        return $items;
    }
}
