<?php

namespace App\Controllers;

use App\Models\HelpdeskModel;
use App\Models\WorkOrderModel;
use App\Models\FacilityModel;
use App\Models\UserModel;
use App\Services\MaintenanceScopeQuery;

/**
 * Helpdesk — manages the first two stages of the Work Order flow:
 *   Stage 1: Complaint Received
 *   Stage 2: Complaint Verification
 *   Stage 3: Approval Process
 *   Stage 4: Convert to Work Order
 */
class Helpdesk extends BaseController
{
    private HelpdeskModel $model;
    private WorkOrderModel $woModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->model   = new HelpdeskModel();
        $this->woModel = new WorkOrderModel();
    }

    // ----------------------------------------------------------
    // Index — list complaints
    // ----------------------------------------------------------

    public function index()
    {
        $user    = $this->currentUser();
        $filters = $this->request->getGet();
        $perPage = 20;
        $page    = max(1, (int) ($filters['page'] ?? 1));
        $offset  = ($page - 1) * $perPage;

        $total    = MaintenanceScopeQuery::countForUser($this->db, $user, $filters);
        $requests = MaintenanceScopeQuery::listForUser($this->db, $user, $filters, $perPage, $offset);

        return view('helpdesk/index', $this->viewData([
            'title'       => 'Helpdesk',
            'pageTitle'   => 'Helpdesk — Complaints',
            'requests'    => $requests,
            'filters'     => $filters,
            'total'       => $total,
            'perPage'     => $perPage,
            'currentPage' => $page,
            'readOnly'    => false,
            'listUrl'     => base_url('helpdesk'),
            'resetUrl'    => base_url('helpdesk'),
            'detailPath'  => 'helpdesk/view/',
        ]));
    }

    // ----------------------------------------------------------
    // Create / Store  (Stage 1 — Complaint Received)
    // ----------------------------------------------------------

    public function create()
    {
        if ($this->currentWorkspace() === 'pm') {
            return redirect()->to(base_url('maintenance/list'))
                ->with('error', 'Maintenance creation is available in the Facility Management workspace only.');
        }

        $facilityModel = new FacilityModel();
        $facilities    = $facilityModel->scopeForUser($this->currentUser())
                                       ->findAll();

        $assetId  = (int) ($this->request->getGet('asset_id') ?? 0);
        $asset    = null;
        $facilityPrefill = (int) ($this->request->getGet('facility_id') ?? 0);
        if ($assetId > 0) {
            $asset = $this->db->table('assets')->where('id', $assetId)->where('deleted_at', null)->get()->getRowArray();
            if ($asset) {
                $facilityPrefill = (int) ($asset['facility_id'] ?? $facilityPrefill);
            }
        }

        return view('helpdesk/create', [
            'pageTitle'       => 'Submit Complaint',
            'facilities'      => $facilities,
            'units'           => [],
            'linkedAsset'     => $asset,
            'facilityPrefill' => $facilityPrefill,
        ]);
    }

    // ----------------------------------------------------------
    // AJAX — return units for a given facility (JSON)
    // ----------------------------------------------------------

    public function ajaxUnitsForFacility(int $facilityId)
    {
        if (! $this->db->tableExists('units')) {
            return $this->response->setJSON([]);
        }

        $q = $this->db->table('units u')
            ->select('u.id, u.unit_number, u.facility_id')
            ->where('u.facility_id', $facilityId)
            ->orderBy('u.unit_number', 'ASC');

        if ($this->db->fieldExists('deleted_at', 'units')) {
            $q->where('u.deleted_at', null);
        }
        if ($this->db->fieldExists('status', 'units')) {
            $q->where('u.status !=', 'inactive');
        }

        return $this->response->setJSON($q->get()->getResultArray());
    }

    public function store()
    {
        $rules = [
            'requester_name'  => 'required|min_length[2]|max_length[150]',
            'requester_email' => 'permit_empty|valid_email',
            'requester_phone' => 'permit_empty|max_length[30]',
            'facility_id'     => 'permit_empty',
            'unit_id'         => 'permit_empty',
            'category'        => 'required|max_length[100]',
            'description'     => 'required|min_length[10]',
            'priority'        => 'required|in_list[critical,high,medium,low]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Sanitise: send null not empty string so DB FK constraint doesn't fail
        $facilityId = (int)$this->request->getPost('facility_id') ?: null;
        $unitId     = (int)$this->request->getPost('unit_id')     ?: null;

        $assetId = (int) ($this->request->getPost('asset_id') ?: 0) ?: null;

        $data = [
            'ticket_number'   => $this->model->generateTicketNumber(),
            'facility_id'     => $facilityId,
            'unit_id'         => $unitId,
            'requester_name'  => $this->request->getPost('requester_name'),
            'requester_email' => $this->request->getPost('requester_email'),
            'requester_phone' => $this->request->getPost('requester_phone'),
            'category'        => $this->request->getPost('category'),
            'description'     => $this->request->getPost('description'),
            'priority'        => $this->request->getPost('priority'),
            'status'          => 'pending',
            'approval_status' => 'pending',
        ];
        if ($assetId && $this->db->fieldExists('asset_id', 'maintenance_requests')) {
            $data['asset_id'] = $assetId;
        }
        if ($this->db->fieldExists('scan_source', 'maintenance_requests')) {
            $src = $this->request->getPost('scan_source');
            $data['scan_source'] = $src ?: ($assetId ? 'asset_page' : null);
        }

        // Handle image upload
        $image = $this->request->getFile('image');
        if ($image && $image->isValid() && ! $image->hasMoved()) {
            $newName = $image->getRandomName();
            $image->move(ROOTPATH . 'public/uploads/helpdesk', $newName);
            $data['image_path'] = 'uploads/helpdesk/' . $newName;
        }

        $id = $this->model->insert($data);

        if (! $id) {
            log_message('error', 'Helpdesk insert failed: ' . json_encode($this->model->errors()));
            return redirect()->back()->withInput()
                ->with('errors', $this->model->errors() ?: ['db' => 'Could not save complaint. Please try again.']);
        }

        $this->logActivity('create', 'helpdesk', $id, 'Complaint submitted: ' . $data['ticket_number']);
        $this->notifyManagers(
            'New Complaint: ' . $data['ticket_number'],
            'Submitted by ' . $data['requester_name'] . ($facilityId ? '' : ' (no facility)')
        );

        return redirect()->to('/helpdesk/' . $id)->with('success', 'Complaint submitted successfully. Ticket: ' . $data['ticket_number']);
    }

    // ----------------------------------------------------------
    // Show
    // ----------------------------------------------------------

    public function show(int $id)
    {
        $complaint = $this->model->getDetail($id);
        if (! $complaint) {
            return redirect()->to('/helpdesk')->with('error', 'Complaint not found.');
        }

        $this->authorizeRecord($complaint);

        $userModel   = new UserModel();
        $managers    = $userModel->getUsersByRole('facility_manager');
        $supervisors = $userModel->getUsersByRole('supervisor');

        return view('helpdesk/show', [
            'pageTitle'   => 'Complaint — ' . $complaint['ticket_number'],
            'complaint'   => $complaint,
            'managers'    => $managers,
            'supervisors' => $supervisors,
        ]);
    }

    // ----------------------------------------------------------
    // Stage 2 — Verify complaint
    // ----------------------------------------------------------

    public function verify(int $id)
    {
        $this->requireRole(['super_admin', 'facility_manager', 'supervisor']);

        $before = $this->model->find($id);
        $notes = $this->request->getPost('verification_notes');

        $this->model->update($id, [
            'status'             => 'reviewed',
            'verified_by'        => $this->currentUser()['id'],
            'verified_at'        => date('Y-m-d H:i:s'),
            'verification_notes' => $notes,
        ]);

        $complaint = $this->model->find($id);
        $this->recordRequestHistory($id, 'verify', $before['status'] ?? null, 'reviewed', $notes);
        $this->logActivity('verify', 'helpdesk', $id, 'Complaint verified');

        return redirect()->to('/helpdesk/' . $id)->with('success', 'Complaint verified.');
    }

    // ----------------------------------------------------------
    // Stage 3 — Approve / Reject
    // ----------------------------------------------------------

    public function approve(int $id)
    {
        // facility_manager: approve/reject
        // supervisor: can assign the complaint to themselves (acts as verify+forward)
        $this->requireRole(['super_admin', 'facility_manager', 'supervisor']);

        $before = $this->model->find($id);
        $action = $this->request->getPost('action'); // 'approve' | 'reject'
        $reason = $this->request->getPost('rejection_reason');

        $data = [
            'approval_status' => $action === 'reject' ? 'rejected' : 'approved',
            'approved_by'     => $this->currentUser()['id'],
            'approved_at'     => date('Y-m-d H:i:s'),
        ];

        if ($action === 'reject') {
            $data['status']           = 'rejected';
            $data['rejection_reason'] = $reason;
        }

        $this->model->update($id, $data);
        $newStatus = $data['status'] ?? ($before['status'] ?? null);
        $this->recordRequestHistory($id, (string) $action, $before['status'] ?? null, $newStatus, $reason);
        $this->logActivity($action, 'helpdesk', $id, 'Complaint ' . $action . 'd');

        return redirect()->to('/helpdesk/' . $id)->with('success', 'Complaint has been ' . $action . 'd.');
    }

    // ----------------------------------------------------------
    // Stage 4 — Convert to Work Order
    // ----------------------------------------------------------


    public function reject(int $id)
    {
        $this->requireRole(['super_admin', 'facility_manager', 'supervisor']);
        $before = $this->model->find($id);
        $this->model->update($id, [
            'approval_status' => 'rejected',
            'status'          => 'rejected',
            'rejection_reason'=> $this->request->getPost('rejection_reason') ?? $this->request->getPost('notes') ?? '',
            'approved_by'     => $this->currentUser()['id'],
            'approved_at'     => date('Y-m-d H:i:s'),
        ]);
        $this->recordRequestHistory($id, 'reject', $before['status'] ?? null, 'rejected', $this->request->getPost('rejection_reason') ?? $this->request->getPost('notes'));
        $this->logActivity('reject', 'helpdesk', $id, 'Complaint rejected');
        return redirect()->to(base_url('helpdesk/view/' . $id))->with('success', 'Complaint rejected.');
    }

    public function convertToWo(int $id)
    {
        $this->requireRole(['super_admin', 'facility_manager', 'supervisor']);

        $complaint = $this->model->getDetail($id);
        if (! $complaint || $complaint['status'] === 'converted') {
            return redirect()->to('/helpdesk/' . $id)->with('error', 'Cannot convert this complaint.');
        }

        if ($complaint['approval_status'] !== 'approved') {
            return redirect()->to('/helpdesk/' . $id)->with('error', 'Complaint must be approved before converting.');
        }

        $supervisorId = $this->request->getPost('supervisor_id');
        $woNumber     = $this->generateNumber('WO', 'work_orders', 'wo_number');

        $woData = [
            'wo_number'      => $woNumber,
            'facility_id'    => $complaint['facility_id'] ?: ($this->request->getPost('facility_id') ?: null),
            'unit_id'        => $complaint['unit_id'] ?? ($this->request->getPost('unit_id') ?: null),
            'asset_id'       => ! empty($complaint['asset_id']) ? (int) $complaint['asset_id'] : null,
            'title'          => $this->request->getPost('title') ?: $complaint['category'] . ' — ' . substr($complaint['description'], 0, 80),
            'description'    => $complaint['description'],
            'type'           => $this->request->getPost('type') ?: 'corrective',
            'category'       => strtolower($complaint['category']),
            'priority'       => $complaint['priority'],
            'status'         => 'new',
            'workflow_stage' => 'converted_to_wo',
            'created_by'     => $this->currentUser()['id'],
            'approval_status'=> 'approved',
            'requester_name' => $complaint['requester_name'],
            'requester_phone'=> $complaint['requester_phone'],
            'requester_email'=> $complaint['requester_email'],
            'supervisor_id'  => $supervisorId ?: null,
        ];

        if ($supervisorId) {
            $woData['workflow_stage'] = 'assigned_to_supervisor';
            $woData['status']         = 'assigned';
        }

        // Calculate SLA due
        $slaModel = new \App\Models\SlaRuleModel();
        $sla      = $slaModel->where('priority', $complaint['priority'])->first();
        if ($sla) {
            $woData['sla_due'] = date('Y-m-d H:i:s', strtotime('+' . $sla['resolution_hours'] . ' hours'));
        }

        $woId = $this->woModel->insert($woData);

        $this->model->update($id, [
            'status'         => 'converted',
            'converted_to_wo'=> $woId,
        ]);

        $this->recordRequestHistory($id, 'convert', $complaint['status'] ?? null, 'converted', 'WO ' . $woNumber);
        $this->logActivity('convert', 'helpdesk', $id, 'Converted to ' . $woNumber);

        return redirect()->to(base_url('workorders/view/' . $woId))->with('success', 'Complaint converted to Work Order ' . $woNumber);
    }

    // ----------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------

    private function authorizeRecord(array $complaint): void
    {
        $user = $this->currentUser();
        $role = $user['role_name'];

        if ($role === 'super_admin') return;

        if ($role === 'client' && $complaint['requester_email'] !== $user['email']) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
    }

    private function recordRequestHistory(int $requestId, string $action, ?string $previousStatus, ?string $newStatus, mixed $note = null): void
    {
        if (! $this->db->tableExists('maintenance_request_history')) {
            return;
        }

        try {
            $this->db->table('maintenance_request_history')->insert([
                'maintenance_request_id' => $requestId,
                'action'                 => $action,
                'previous_status'        => $previousStatus,
                'new_status'             => $newStatus,
                'performed_by'           => $this->currentUser()['id'] ?: null,
                'note'                   => $note !== null && $note !== '' ? (string) $note : null,
                'created_at'             => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'maintenance_request_history insert failed: ' . $e->getMessage());
        }
    }
}
