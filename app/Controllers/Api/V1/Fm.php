<?php

namespace App\Controllers\Api\V1;

/**
 * Facility Management mobile API — role-scoped for
 * facility_manager, supervisor, technician (and super_admin).
 *
 * GET  /api/v1/fm/dashboard
 * GET  /api/v1/fm/work-orders
 * GET  /api/v1/fm/work-orders/{id}
 * POST /api/v1/fm/work-orders/{id}/status
 * POST /api/v1/fm/work-orders/{id}/assign
 * POST /api/v1/fm/work-orders/{id}/job-cards
 * GET  /api/v1/fm/complaints
 * GET  /api/v1/fm/complaints/{id}
 * POST /api/v1/fm/complaints/{id}/action
 * GET  /api/v1/fm/job-cards
 * GET  /api/v1/fm/job-cards/{id}
 * POST /api/v1/fm/job-cards/{id}/status
 * GET  /api/v1/fm/technicians
 */
class Fm extends BaseApiController
{
    private const FM_ROLES = ['facility_manager', 'supervisor', 'technician', 'super_admin', 'qa_inspector'];

    public function dashboard()
    {
        $user = $this->requireFmUser();
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $user;
        }

        $role = (string) ($user['role_name'] ?? '');
        $uid  = (int) $user['id'];

        return match ($role) {
            'technician' => $this->response->setJSON([
                'status' => true,
                'role'   => $role,
                'data'   => $this->technicianDashboard($uid),
            ]),
            'supervisor' => $this->response->setJSON([
                'status' => true,
                'role'   => $role,
                'data'   => $this->supervisorDashboard($uid),
            ]),
            default => $this->response->setJSON([
                'status' => true,
                'role'   => $role,
                'data'   => $this->facilityManagerDashboard(),
            ]),
        };
    }

    public function workOrders()
    {
        $user = $this->requireFmUser();
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $user;
        }

        $role   = (string) $user['role_name'];
        $uid    = (int) $user['id'];
        $status = trim((string) ($this->request->getGet('status') ?? ''));
        $qText  = trim((string) ($this->request->getGet('q') ?? ''));

        $q = $this->db->table('work_orders w')
            ->select('w.id, w.wo_number, w.title, w.priority, w.status, w.type, w.sla_due, w.sla_breached, w.updated_at, w.assigned_to, w.supervisor_id, f.name AS facility_name, u.name AS assigned_name')
            ->join('facilities f', 'f.id = w.facility_id', 'left')
            ->join('users u', 'u.id = w.assigned_to', 'left')
            ->whereNotIn('w.status', ['cancelled']);

        if ($role === 'technician') {
            $q->where('w.assigned_to', $uid);
        } elseif ($role === 'supervisor') {
            $q->groupStart()
                ->where('w.supervisor_id', $uid)
                ->orWhere('w.assigned_to', $uid)
                ->groupEnd();
        } else {
            $this->scopeFacilitiesForApi($q, 'w.facility_id');
        }

        if ($status !== '') {
            $q->where('w.status', $status);
        }
        if ($qText !== '') {
            $q->groupStart()
                ->like('w.wo_number', $qText)
                ->orLike('w.title', $qText)
                ->groupEnd();
        }

        $rows = $q->orderBy('w.priority', 'ASC')
            ->orderBy('w.updated_at', 'DESC')
            ->limit(100)
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'status' => true,
            'data'   => array_map([$this, 'mapWo'], $rows),
            'count'  => count($rows),
        ]);
    }

    public function workOrder(int $id)
    {
        $user = $this->requireFmUser();
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $user;
        }

        $wo = $this->findWorkOrderForUser($id, $user);
        if (! $wo) {
            return $this->fail('Work order not found', 404);
        }

        $jobCards = [];
        if ($this->db->tableExists('job_cards')) {
            $jobCards = $this->db->table('job_cards jc')
                ->select('jc.id, jc.jc_number, jc.status, jc.assigned_to, jc.labor_hours, jc.scheduled_date, u.name AS technician_name')
                ->join('users u', 'u.id = jc.assigned_to', 'left')
                ->where('jc.wo_id', $id)
                ->where('jc.deleted_at', null)
                ->orderBy('jc.id', 'DESC')
                ->get()
                ->getResultArray();
        }

        return $this->response->setJSON([
            'status' => true,
            'data'   => array_merge($this->mapWo($wo), [
                'description'     => $wo['description'] ?? '',
                'workflow_stage'  => $wo['workflow_stage'] ?? null,
                'planned_start'   => $wo['planned_start'] ?? null,
                'planned_end'     => $wo['planned_end'] ?? null,
                'started_at'      => $wo['started_at'] ?? null,
                'completed_at'    => $wo['completed_at'] ?? null,
                'execution_percent'=> isset($wo['execution_percent']) ? (float) $wo['execution_percent'] : null,
                'job_cards'       => array_map(static function (array $jc) {
                    return [
                        'id'               => (int) $jc['id'],
                        'jc_number'        => $jc['jc_number'],
                        'status'           => $jc['status'],
                        'assigned_to'      => (int) $jc['assigned_to'],
                        'technician_name'  => $jc['technician_name'] ?? '',
                        'labor_hours'      => (float) ($jc['labor_hours'] ?? 0),
                        'scheduled_date'   => $jc['scheduled_date'] ?? null,
                    ];
                }, $jobCards),
                'actions' => $this->allowedWoActions($user, $wo),
            ]),
        ]);
    }

    public function updateWorkOrderStatus(int $id)
    {
        $user = $this->requireFmUser();
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $user;
        }

        $wo = $this->findWorkOrderForUser($id, $user);
        if (! $wo) {
            return $this->fail('Work order not found', 404);
        }

        $payload = $this->request->getJSON(true) ?? $this->request->getPost();
        $status  = strtolower(trim((string) ($payload['status'] ?? '')));
        $allowed = $this->allowedWoActions($user, $wo);

        if (! in_array($status, $allowed, true)) {
            return $this->fail('Status transition not allowed for your role', 403);
        }

        $update = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];
        if ($status === 'in_progress' && empty($wo['started_at'])) {
            $update['started_at'] = date('Y-m-d H:i:s');
        }
        if ($status === 'completed') {
            $update['completed_at'] = date('Y-m-d H:i:s');
        }
        if (isset($payload['execution_percent']) && $this->db->fieldExists('execution_percent', 'work_orders')) {
            $update['execution_percent'] = (float) $payload['execution_percent'];
        }
        if (! empty($payload['notes']) && $this->db->fieldExists('completion_notes', 'work_orders')) {
            $update['completion_notes'] = (string) $payload['notes'];
        }

        $this->db->table('work_orders')->where('id', $id)->update($update);
        $this->logActivity('status_change', 'work_orders', $id, 'Status → ' . $status);

        return $this->response->setJSON(['status' => true, 'message' => 'Work order updated']);
    }

    public function assignWorkOrder(int $id)
    {
        $user = $this->requireFmUser(['facility_manager', 'supervisor', 'super_admin']);
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $user;
        }

        $wo = $this->findWorkOrderForUser($id, $user);
        if (! $wo) {
            return $this->fail('Work order not found', 404);
        }

        $payload = $this->request->getJSON(true) ?? $this->request->getPost();
        $techId  = (int) ($payload['technician_id'] ?? 0);
        if ($techId < 1) {
            return $this->fail('technician_id required', 422);
        }

        $tech = $this->db->table('users u')
            ->select('u.id, u.name, r.name as role_name')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->where('u.id', $techId)
            ->where('u.status', 'active')
            ->get()
            ->getRowArray();

        if (! $tech || $tech['role_name'] !== 'technician') {
            return $this->fail('Invalid technician', 422);
        }

        $update = [
            'assigned_to' => $techId,
            'status'      => 'assigned',
            'updated_at'  => date('Y-m-d H:i:s'),
        ];
        if (($user['role_name'] ?? '') === 'supervisor' && $this->db->fieldExists('supervisor_id', 'work_orders')) {
            $update['supervisor_id'] = (int) $user['id'];
        }

        $this->db->table('work_orders')->where('id', $id)->update($update);
        $this->logActivity('assign', 'work_orders', $id, 'Assigned to ' . $tech['name']);

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Technician assigned',
            'data'    => ['technician_id' => $techId, 'technician_name' => $tech['name']],
        ]);
    }

    public function createJobCard(int $id)
    {
        $user = $this->requireFmUser(['facility_manager', 'supervisor', 'super_admin']);
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $user;
        }

        if (! $this->db->tableExists('job_cards')) {
            return $this->fail('Job cards not available', 404);
        }

        $wo = $this->findWorkOrderForUser($id, $user);
        if (! $wo) {
            return $this->fail('Work order not found', 404);
        }

        $payload = $this->request->getJSON(true) ?? $this->request->getPost();
        $techId  = (int) ($payload['technician_id'] ?? $payload['assigned_to'] ?? 0);
        $desc    = trim((string) ($payload['description'] ?? ''));
        $sched   = trim((string) ($payload['scheduled_date'] ?? ''));

        if ($techId < 1) {
            return $this->fail('technician_id required', 422);
        }
        if (strlen($desc) < 5) {
            return $this->fail('description must be at least 5 characters', 422);
        }

        $tech = $this->db->table('users u')
            ->select('u.id, u.name, r.name as role_name')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->where('u.id', $techId)
            ->where('u.status', 'active')
            ->get()
            ->getRowArray();

        if (! $tech || $tech['role_name'] !== 'technician') {
            return $this->fail('Invalid technician', 422);
        }

        $jcNumber = (new \App\Models\JobCardModel())->generateJcNumber();
        $now      = date('Y-m-d H:i:s');
        $supervisorId = (int) ($wo['supervisor_id'] ?? 0);
        if ($supervisorId < 1 || ($user['role_name'] ?? '') === 'supervisor') {
            $supervisorId = (int) $user['id'];
        }

        $insert = [
            'jc_number'      => $jcNumber,
            'wo_id'          => $id,
            'supervisor_id'  => $supervisorId,
            'assigned_to'    => $techId,
            'description'    => $desc,
            'status'         => 'draft',
            'scheduled_date' => $sched !== '' ? $sched : null,
            'created_by'     => (int) $user['id'],
            'created_at'     => $now,
            'updated_at'     => $now,
        ];

        $this->db->table('job_cards')->insert($insert);
        $jcId = (int) $this->db->insertID();

        $woUpdate = [
            'assigned_to'   => $techId,
            'supervisor_id' => $supervisorId,
            'status'        => 'assigned',
            'updated_at'    => $now,
        ];
        $this->db->table('work_orders')->where('id', $id)->update($woUpdate);
        $this->logActivity('create', 'job_cards', $jcId, 'Job Card created: ' . $jcNumber);

        return $this->response->setJSON([
            'status'  => true,
            'message' => 'Job card created',
            'data'    => [
                'id'               => $jcId,
                'jc_number'        => $jcNumber,
                'technician_id'    => $techId,
                'technician_name'  => $tech['name'],
            ],
        ]);
    }

    public function complaints()
    {
        $user = $this->requireFmUser(['facility_manager', 'supervisor', 'super_admin', 'qa_inspector']);
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $user;
        }

        if (! $this->db->tableExists('maintenance_requests')) {
            return $this->response->setJSON(['status' => true, 'data' => [], 'count' => 0]);
        }

        $status = trim((string) ($this->request->getGet('status') ?? ''));
        $q = $this->db->table('maintenance_requests mr')
            ->select('mr.id, mr.ticket_number, mr.title, mr.category, mr.priority, mr.status, mr.requester_name, mr.created_at, f.name AS facility_name, u.unit_number')
            ->join('facilities f', 'f.id = mr.facility_id', 'left')
            ->join('units u', 'u.id = mr.unit_id', 'left');

        $this->scopeFacilitiesForApi($q, 'mr.facility_id');
        if ($status !== '') {
            $q->where('mr.status', $status);
        }

        $rows = $q->orderBy('mr.created_at', 'DESC')->limit(100)->get()->getResultArray();

        $data = array_map(static function (array $r) {
            $title = $r['title'] ?? null;
            if (! $title) {
                $title = trim(($r['category'] ?? 'Complaint') . ' – ' . ($r['requester_name'] ?? ''));
            }

            return [
                'id'             => (int) $r['id'],
                'ticket_number'  => $r['ticket_number'],
                'title'          => $title,
                'category'       => $r['category'] ?? '',
                'priority'       => $r['priority'] ?? 'medium',
                'status'         => $r['status'],
                'requester_name' => $r['requester_name'] ?? '',
                'facility_name'  => $r['facility_name'] ?? '',
                'unit_number'    => $r['unit_number'] ?? '',
                'created_at'     => $r['created_at'],
            ];
        }, $rows);

        return $this->response->setJSON(['status' => true, 'data' => $data, 'count' => count($data)]);
    }

    public function complaint(int $id)
    {
        $user = $this->requireFmUser(['facility_manager', 'supervisor', 'super_admin', 'qa_inspector']);
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $user;
        }

        $q = $this->db->table('maintenance_requests mr')
            ->select('mr.*, f.name AS facility_name, u.unit_number')
            ->join('facilities f', 'f.id = mr.facility_id', 'left')
            ->join('units u', 'u.id = mr.unit_id', 'left')
            ->where('mr.id', $id);
        $this->scopeFacilitiesForApi($q, 'mr.facility_id');
        $row = $q->get()->getRowArray();
        if (! $row) {
            return $this->fail('Complaint not found', 404);
        }

        return $this->response->setJSON([
            'status' => true,
            'data'   => [
                'id'             => (int) $row['id'],
                'ticket_number'  => $row['ticket_number'],
                'title'          => $row['title'] ?? ($row['category'] ?? 'Complaint'),
                'category'       => $row['category'] ?? '',
                'priority'       => $row['priority'] ?? 'medium',
                'status'         => $row['status'],
                'description'    => $row['description'] ?? '',
                'requester_name' => $row['requester_name'] ?? '',
                'requester_email'=> $row['requester_email'] ?? '',
                'facility_name'  => $row['facility_name'] ?? '',
                'unit_number'    => $row['unit_number'] ?? '',
                'created_at'     => $row['created_at'],
                'actions'        => $this->allowedComplaintActions($user, $row),
            ],
        ]);
    }

    public function complaintAction(int $id)
    {
        $user = $this->requireFmUser(['facility_manager', 'super_admin', 'supervisor']);
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $user;
        }

        $row = $this->db->table('maintenance_requests mr')
            ->select('mr.*')
            ->where('mr.id', $id)
            ->get()
            ->getRowArray();
        if (! $row) {
            return $this->fail('Complaint not found', 404);
        }

        $payload = $this->request->getJSON(true) ?? $this->request->getPost();
        $action  = strtolower(trim((string) ($payload['action'] ?? '')));
        $allowed = $this->allowedComplaintActions($user, $row);
        if (! in_array($action, $allowed, true)) {
            return $this->fail('Action not allowed', 403);
        }

        $update = ['updated_at' => date('Y-m-d H:i:s')];
        if ($action === 'verify') {
            $update['status'] = 'reviewed';
            if ($this->db->fieldExists('verified_by', 'maintenance_requests')) {
                $update['verified_by'] = (int) $user['id'];
                $update['verified_at'] = date('Y-m-d H:i:s');
            }
        } elseif ($action === 'approve') {
            $update['status'] = 'reviewed';
            if ($this->db->fieldExists('approval_status', 'maintenance_requests')) {
                $update['approval_status'] = 'approved';
                $update['approved_by'] = (int) $user['id'];
                $update['approved_at'] = date('Y-m-d H:i:s');
            }
        } elseif ($action === 'reject') {
            $update['status'] = 'rejected';
            if (! empty($payload['reason']) && $this->db->fieldExists('rejection_reason', 'maintenance_requests')) {
                $update['rejection_reason'] = (string) $payload['reason'];
            }
        }

        $this->db->table('maintenance_requests')->where('id', $id)->update($update);
        $this->logActivity($action, 'helpdesk', $id, 'Complaint ' . $action);

        return $this->response->setJSON(['status' => true, 'message' => 'Complaint updated']);
    }

    public function jobCards()
    {
        $user = $this->requireFmUser();
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $user;
        }

        if (! $this->db->tableExists('job_cards')) {
            return $this->response->setJSON(['status' => true, 'data' => [], 'count' => 0]);
        }

        $role = (string) $user['role_name'];
        $uid  = (int) $user['id'];

        $q = $this->db->table('job_cards jc')
            ->select('jc.id, jc.jc_number, jc.status, jc.labor_hours, jc.scheduled_date, jc.assigned_to, wo.wo_number, wo.title AS wo_title, wo.priority, f.name AS facility_name, u.name AS technician_name')
            ->join('work_orders wo', 'wo.id = jc.wo_id', 'left')
            ->join('facilities f', 'f.id = wo.facility_id', 'left')
            ->join('users u', 'u.id = jc.assigned_to', 'left')
            ->where('jc.deleted_at', null);

        if ($role === 'technician') {
            $q->where('jc.assigned_to', $uid);
        } elseif ($role === 'supervisor') {
            $q->groupStart()
                ->where('jc.supervisor_id', $uid)
                ->orWhere('wo.supervisor_id', $uid)
                ->groupEnd();
        } else {
            $this->scopeFacilitiesForApi($q, 'wo.facility_id');
        }

        $rows = $q->orderBy('jc.created_at', 'DESC')->limit(100)->get()->getResultArray();

        $data = array_map(static function (array $r) {
            return [
                'id'              => (int) $r['id'],
                'jc_number'       => $r['jc_number'],
                'status'          => $r['status'],
                'labor_hours'     => (float) ($r['labor_hours'] ?? 0),
                'scheduled_date'  => $r['scheduled_date'] ?? null,
                'wo_number'       => $r['wo_number'] ?? '',
                'wo_title'        => $r['wo_title'] ?? '',
                'priority'        => $r['priority'] ?? 'medium',
                'facility_name'   => $r['facility_name'] ?? '',
                'technician_name' => $r['technician_name'] ?? '',
            ];
        }, $rows);

        return $this->response->setJSON(['status' => true, 'data' => $data, 'count' => count($data)]);
    }

    public function jobCard(int $id)
    {
        $user = $this->requireFmUser();
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $user;
        }

        $row = $this->db->table('job_cards jc')
            ->select('jc.*, wo.wo_number, wo.title AS wo_title, wo.priority, f.name AS facility_name, u.name AS technician_name')
            ->join('work_orders wo', 'wo.id = jc.wo_id', 'left')
            ->join('facilities f', 'f.id = wo.facility_id', 'left')
            ->join('users u', 'u.id = jc.assigned_to', 'left')
            ->where('jc.id', $id)
            ->where('jc.deleted_at', null)
            ->get()
            ->getRowArray();

        if (! $row) {
            return $this->fail('Job card not found', 404);
        }

        // Access check
        $role = (string) $user['role_name'];
        $uid  = (int) $user['id'];
        if ($role === 'technician' && (int) $row['assigned_to'] !== $uid) {
            return $this->fail('Access denied', 403);
        }

        return $this->response->setJSON([
            'status' => true,
            'data'   => [
                'id'               => (int) $row['id'],
                'jc_number'        => $row['jc_number'],
                'status'           => $row['status'],
                'description'      => $row['description'] ?? '',
                'labor_hours'      => (float) ($row['labor_hours'] ?? 0),
                'scheduled_date'   => $row['scheduled_date'] ?? null,
                'technician_notes' => $row['technician_notes'] ?? '',
                'completion_notes' => $row['completion_notes'] ?? '',
                'wo_number'        => $row['wo_number'] ?? '',
                'wo_title'         => $row['wo_title'] ?? '',
                'priority'         => $row['priority'] ?? 'medium',
                'facility_name'    => $row['facility_name'] ?? '',
                'technician_name'  => $row['technician_name'] ?? '',
                'actions'          => $this->allowedJcActions($user, $row),
            ],
        ]);
    }

    public function updateJobCardStatus(int $id)
    {
        $user = $this->requireFmUser();
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $user;
        }

        $row = $this->db->table('job_cards')->where('id', $id)->where('deleted_at', null)->get()->getRowArray();
        if (! $row) {
            return $this->fail('Job card not found', 404);
        }

        $payload = $this->request->getJSON(true) ?? $this->request->getPost();
        $status  = strtolower(trim((string) ($payload['status'] ?? '')));
        $allowed = $this->allowedJcActions($user, $row);
        if (! in_array($status, $allowed, true)) {
            return $this->fail('Status not allowed', 403);
        }

        $update = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];
        if ($status === 'completed') {
            $update['completed_at'] = date('Y-m-d H:i:s');
            if (isset($payload['labor_hours'])) {
                $update['labor_hours'] = (float) $payload['labor_hours'];
            }
            if (! empty($payload['completion_notes'])) {
                $update['completion_notes'] = (string) $payload['completion_notes'];
            }
        }
        if (! empty($payload['technician_notes'])) {
            $update['technician_notes'] = (string) $payload['technician_notes'];
        }

        $this->db->table('job_cards')->where('id', $id)->update($update);
        $this->logActivity('status_change', 'job_cards', $id, 'JC → ' . $status);

        // Sync WO to in_progress / completed when needed
        if ($status === 'in_progress' && ! empty($row['wo_id'])) {
            $this->db->table('work_orders')->where('id', (int) $row['wo_id'])->whereIn('status', ['assigned', 'new'])
                ->update(['status' => 'in_progress', 'started_at' => date('Y-m-d H:i:s')]);
        }

        return $this->response->setJSON(['status' => true, 'message' => 'Job card updated']);
    }

    public function technicians()
    {
        $user = $this->requireFmUser(['facility_manager', 'supervisor', 'super_admin']);
        if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
            return $user;
        }

        $rows = $this->db->table('users u')
            ->select('u.id, u.name, u.email, u.phone, u.status')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->where('r.name', 'technician')
            ->where('u.status', 'active')
            ->where('u.deleted_at', null)
            ->orderBy('u.name', 'ASC')
            ->get()
            ->getResultArray();

        $data = array_map(static function (array $r) {
            return [
                'id'    => (int) $r['id'],
                'name'  => $r['name'],
                'email' => $r['email'] ?? '',
                'phone' => $r['phone'] ?? '',
            ];
        }, $rows);

        return $this->response->setJSON(['status' => true, 'data' => $data, 'count' => count($data)]);
    }

    // ── dashboards ─────────────────────────────────────────────

    private function facilityManagerDashboard(): array
    {
        $openWO = $this->db->table('work_orders')->whereIn('status', ['new', 'assigned', 'in_progress'])->countAllResults();
        $critical = $this->db->table('work_orders')->whereIn('status', ['new', 'assigned', 'in_progress'])->whereIn('priority', ['critical', 'high'])->countAllResults();
        $slaBreaches = $this->db->table('work_orders')->where('sla_breached', 1)->whereIn('status', ['new', 'assigned', 'in_progress'])->countAllResults();
        $pendingReq = $this->db->tableExists('maintenance_requests')
            ? $this->db->table('maintenance_requests')->where('status', 'pending')->countAllResults()
            : 0;

        $urgent = $this->db->table('work_orders w')
            ->select('w.id, w.wo_number, w.title, w.priority, w.status, w.sla_due, f.name AS facility_name, u.name AS assigned_name')
            ->join('facilities f', 'f.id=w.facility_id', 'left')
            ->join('users u', 'u.id=w.assigned_to', 'left')
            ->whereIn('w.priority', ['critical', 'high'])
            ->whereIn('w.status', ['new', 'assigned', 'in_progress'])
            ->orderBy('w.updated_at', 'DESC')
            ->limit(8)
            ->get()
            ->getResultArray();

        return [
            'kpis' => [
                ['key' => 'open_wo', 'label' => 'Open Work Orders', 'value' => $openWO],
                ['key' => 'urgent', 'label' => 'Urgent / High', 'value' => $critical],
                ['key' => 'sla', 'label' => 'SLA Breaches', 'value' => $slaBreaches],
                ['key' => 'complaints', 'label' => 'Pending Complaints', 'value' => $pendingReq],
            ],
            'urgent_work_orders' => array_map([$this, 'mapWo'], $urgent),
        ];
    }

    private function supervisorDashboard(int $uid): array
    {
        $assigned = $this->db->table('work_orders')
            ->groupStart()->where('supervisor_id', $uid)->orWhere('assigned_to', $uid)->groupEnd()
            ->whereIn('status', ['new', 'assigned', 'in_progress'])
            ->countAllResults();
        $inProgress = $this->db->table('work_orders')
            ->groupStart()->where('supervisor_id', $uid)->orWhere('assigned_to', $uid)->groupEnd()
            ->where('status', 'in_progress')
            ->countAllResults();
        $jcOpen = $this->db->tableExists('job_cards')
            ? $this->db->table('job_cards')->where('supervisor_id', $uid)->whereIn('status', ['draft', 'in_progress'])->countAllResults()
            : 0;

        $list = $this->db->table('work_orders w')
            ->select('w.id, w.wo_number, w.title, w.priority, w.status, w.sla_due, f.name AS facility_name, u.name AS assigned_name')
            ->join('facilities f', 'f.id=w.facility_id', 'left')
            ->join('users u', 'u.id=w.assigned_to', 'left')
            ->groupStart()->where('w.supervisor_id', $uid)->orWhere('w.assigned_to', $uid)->groupEnd()
            ->whereIn('w.status', ['new', 'assigned', 'in_progress'])
            ->orderBy('w.updated_at', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        return [
            'kpis' => [
                ['key' => 'my_wo', 'label' => 'My Work Orders', 'value' => $assigned],
                ['key' => 'in_progress', 'label' => 'In Progress', 'value' => $inProgress],
                ['key' => 'job_cards', 'label' => 'Open Job Cards', 'value' => $jcOpen],
                ['key' => 'team', 'label' => 'Needs Assignment', 'value' => $this->db->table('work_orders')->where('supervisor_id', $uid)->where('assigned_to', null)->whereIn('status', ['new', 'assigned'])->countAllResults()],
            ],
            'urgent_work_orders' => array_map([$this, 'mapWo'], $list),
        ];
    }

    private function technicianDashboard(int $uid): array
    {
        $myWO = $this->db->table('work_orders w')
            ->select('w.id, w.wo_number, w.title, w.priority, w.status, w.sla_due, w.sla_breached, f.name AS facility_name')
            ->join('facilities f', 'f.id=w.facility_id', 'left')
            ->where('w.assigned_to', $uid)
            ->whereIn('w.status', ['assigned', 'in_progress'])
            ->orderBy('w.sla_due', 'ASC')
            ->get()
            ->getResultArray();

        $completedToday = $this->db->table('work_orders')
            ->where('assigned_to', $uid)
            ->where('status', 'completed')
            ->where('DATE(completed_at)', date('Y-m-d'))
            ->countAllResults();

        $kpi = [
            ['key' => 'assigned', 'label' => 'Assigned', 'value' => count(array_filter($myWO, static fn ($w) => $w['status'] === 'assigned'))],
            ['key' => 'in_progress', 'label' => 'In Progress', 'value' => count(array_filter($myWO, static fn ($w) => $w['status'] === 'in_progress'))],
            ['key' => 'done_today', 'label' => 'Completed Today', 'value' => $completedToday],
            ['key' => 'overdue', 'label' => 'SLA Risk', 'value' => count(array_filter($myWO, static fn ($w) => (int) ($w['sla_breached'] ?? 0) === 1))],
        ];

        return [
            'kpis' => $kpi,
            'urgent_work_orders' => array_map([$this, 'mapWo'], $myWO),
        ];
    }

    // ── helpers ────────────────────────────────────────────────

    /** @param list<string>|null $roles */
    private function requireFmUser(?array $roles = null)
    {
        $user = $this->jwtUser();
        if (! $user) {
            return $this->fail('Unauthorized', 401);
        }

        $role = (string) ($user['role_name'] ?? '');
        $allowed = $roles ?? self::FM_ROLES;
        if (! in_array($role, $allowed, true)) {
            return $this->fail('FM access required', 403);
        }

        return $user;
    }

    private function fail(string $message, int $code)
    {
        return $this->response->setStatusCode($code)->setJSON([
            'status'  => false,
            'message' => $message,
        ]);
    }

    /** @param array<string,mixed> $user */
    private function findWorkOrderForUser(int $id, array $user): ?array
    {
        $role = (string) $user['role_name'];
        $uid  = (int) $user['id'];

        $q = $this->db->table('work_orders w')
            ->select('w.*, f.name AS facility_name, u.name AS assigned_name')
            ->join('facilities f', 'f.id = w.facility_id', 'left')
            ->join('users u', 'u.id = w.assigned_to', 'left')
            ->where('w.id', $id);

        if ($role === 'technician') {
            $q->where('w.assigned_to', $uid);
        } elseif ($role === 'supervisor') {
            $q->groupStart()->where('w.supervisor_id', $uid)->orWhere('w.assigned_to', $uid)->groupEnd();
        } else {
            $this->scopeFacilitiesForApi($q, 'w.facility_id');
        }

        return $q->get()->getRowArray() ?: null;
    }

    /** @param array<string,mixed> $r */
    private function mapWo(array $r): array
    {
        return [
            'id'             => (int) $r['id'],
            'wo_number'      => $r['wo_number'],
            'title'          => $r['title'],
            'priority'       => $r['priority'] ?? 'medium',
            'status'         => $r['status'],
            'type'           => $r['type'] ?? null,
            'sla_due'        => $r['sla_due'] ?? null,
            'sla_breached'   => (int) ($r['sla_breached'] ?? 0) === 1,
            'facility_name'  => $r['facility_name'] ?? '',
            'assigned_name'  => $r['assigned_name'] ?? '',
            'assigned_to'    => isset($r['assigned_to']) ? (int) $r['assigned_to'] : null,
            'supervisor_id'  => isset($r['supervisor_id']) ? (int) $r['supervisor_id'] : null,
            'updated_at'     => $r['updated_at'] ?? null,
        ];
    }

    /** @return list<string> */
    private function allowedWoActions(array $user, array $wo): array
    {
        $role   = (string) $user['role_name'];
        $status = (string) $wo['status'];

        if ($role === 'technician') {
            return match ($status) {
                'assigned'    => ['in_progress'],
                'in_progress' => ['on_hold', 'completed'],
                'on_hold'     => ['in_progress'],
                default       => [],
            };
        }

        if ($role === 'supervisor') {
            return match ($status) {
                'new', 'assigned' => ['assigned', 'in_progress'],
                'in_progress'     => ['on_hold', 'completed'],
                'on_hold'         => ['in_progress'],
                'completed'       => ['closed'],
                default           => [],
            };
        }

        // facility_manager / super_admin
        return match ($status) {
            'new'         => ['assigned', 'cancelled'],
            'assigned'    => ['in_progress', 'cancelled'],
            'in_progress' => ['on_hold', 'completed'],
            'on_hold'     => ['in_progress', 'cancelled'],
            'completed'   => ['closed'],
            default       => [],
        };
    }

    /** @return list<string> */
    private function allowedComplaintActions(array $user, array $row): array
    {
        $role   = (string) $user['role_name'];
        $status = (string) $row['status'];
        if (! in_array($role, ['facility_manager', 'super_admin', 'supervisor'], true)) {
            return [];
        }
        if ($status === 'pending') {
            return ['verify', 'reject'];
        }
        if ($status === 'reviewed') {
            return ['approve', 'reject'];
        }

        return [];
    }

    /** @return list<string> */
    private function allowedJcActions(array $user, array $row): array
    {
        $role   = (string) $user['role_name'];
        $status = (string) $row['status'];
        $uid    = (int) $user['id'];

        if ($role === 'technician' && (int) $row['assigned_to'] !== $uid) {
            return [];
        }

        return match ($status) {
            'draft'       => ['in_progress'],
            'in_progress' => ['completed'],
            default       => in_array($role, ['supervisor', 'facility_manager', 'super_admin'], true) && $status === 'completed'
                ? ['approved']
                : [],
        };
    }
}
