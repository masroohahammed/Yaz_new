<?php

namespace App\Controllers;

use App\Models\WorkOrderModel;
use App\Models\JobCardModel;
use App\Models\UserModel;
use App\Models\FacilityModel;
use App\Models\AssetModel;

/**
 * WorkOrders — Stages 4 through 12 of the WO / Job Card workflow.
 *
 * Stage 4:  converted_to_wo      (created by Helpdesk::convertToWo)
 * Stage 5:  assigned_to_supervisor
 * Stage 6:  job_card_created     (handled by JobCards controller)
 * Stage 7:  technician_assigned  (handled by JobCards controller)
 * Stage 8:  planning_scheduling
 * Stage 9:  work_execution       (handled by JobCards controller)
 * Stage 10: inspection_qc
 * Stage 11: job_completed        (handled by JobCards controller)
 * Stage 12: wo_closed
 */
class WorkOrders extends BaseController
{
    protected ?string $workspaceRequired = 'fm';
    private WorkOrderModel $model;
    private JobCardModel   $jcModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->model   = new WorkOrderModel();
        $this->jcModel = new JobCardModel();
    }

    // ----------------------------------------------------------
    // Index
    // ----------------------------------------------------------

    public function index()
    {
        return view('workorders/index', $this->legacyIndexData());
    }

    // ----------------------------------------------------------
    // Create (direct WO without helpdesk)
    // ----------------------------------------------------------

    public function create()
    {
        $this->requireRole(['super_admin', 'facility_manager']);

        $user          = $this->currentUser();
        $facilityModel = new FacilityModel();
        $userModel     = new UserModel();
        $assetModel    = new AssetModel();

        $facilities  = $facilityModel->scopeForUser($user)->findAll();
        $supervisors = $userModel->getUsersByRole('supervisor');
        $technicians = $userModel->getUsersByRole('technician');
        $assets      = $assetModel->findAll();

        // Vendors — was missing, caused "Undefined variable $vendors"
        $vendors = $this->db->table('vendors')
            ->where('status', 'active')
            ->orderBy('name', 'ASC')
            ->get()->getResultArray();

        // Units for facility → unit cascade dropdown
        $units = [];
        if ($this->db->tableExists('units')) {
            $units = $this->db->table('units u')
                ->select('u.id, u.unit_number, u.facility_id, f.name AS facility_name, u.status')
                ->join('facilities f', 'f.id = u.facility_id', 'left')
                ->orderBy('f.name', 'ASC')
                ->orderBy('u.unit_number', 'ASC')
                ->get()->getResultArray();
        }

        $prefillAssetId    = (int) ($this->request->getGet('asset_id') ?? 0);
        $prefillFacilityId = (int) ($this->request->getGet('facility_id') ?? 0);
        if ($prefillAssetId && ! $prefillFacilityId) {
            $aRow = $this->db->table('assets')->select('facility_id')->where('id', $prefillAssetId)->get()->getRowArray();
            $prefillFacilityId = (int) ($aRow['facility_id'] ?? 0);
        }

        return view('workorders/create', $this->viewData([
            'title'             => 'Create Work Order',
            'facilities'        => $facilities,
            'supervisors'       => $supervisors,
            'technicians'       => $technicians,
            'assets'            => $assets,
            'vendors'           => $vendors,
            'units'             => $units,
            'prefillAssetId'    => $prefillAssetId,
            'prefillFacilityId' => $prefillFacilityId,
        ]));
    }

    public function store()
    {
        $this->requireRole(['super_admin', 'facility_manager']);

        $rules = [
            'title'       => 'required|min_length[3]|max_length[255]',
            'facility_id' => 'permit_empty',
            'type'        => 'required',
            'priority'    => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $post         = $this->request->getPost();
        $supervisorId = $post['supervisor_id'] ?? null;
        $woNumber     = $this->model->generateWoNumber();

        $stage  = 'converted_to_wo';
        $status = 'new';
        if ($supervisorId) {
            $stage  = 'assigned_to_supervisor';
            $status = 'assigned';
        }

        $data = [
            'wo_number'       => $woNumber,
            'facility_id'     => $post['facility_id'] ? (int) $post['facility_id'] : null,
            'asset_id'        => ! empty($post['asset_id']) ? (int) $post['asset_id'] : null,
            'unit_id'         => $post['unit_id'] ?: null,
            'title'           => $post['title'],
            'description'     => $post['description'] ?? '',
            'type'            => $post['type'],
            'category'        => $post['category'] ?? null,
            'priority'        => $post['priority'],
            'status'          => $status,
            'workflow_stage'  => $stage,
            'supervisor_id'   => $supervisorId ?: null,
            'assigned_to'     => $post['assigned_to'] ?: null,
            'estimated_cost'  => $post['estimated_cost'] ?: null,
            'planned_start'   => $post['planned_start'] ?: null,
            'planned_end'     => $post['planned_end'] ?: null,
            'created_by'      => $this->currentUser()['id'],
            'approval_status' => 'approved',
            'requester_name'  => $post['requester_name'] ?? '',
            'requester_phone' => $post['requester_phone'] ?? '',
            'requester_email' => $post['requester_email'] ?? '',
        ];

        // SLA calculation
        $sla = $this->db->table('sla_rules')->where('priority', $post['priority'])->get()->getRowArray();
        if ($sla) {
            $data['sla_due'] = date('Y-m-d H:i:s', strtotime('+' . $sla['resolution_hours'] . ' hours'));
        }

        $id = $this->model->insert($data);
        $this->logActivity('create', 'work_orders', $id, 'WO created: ' . $woNumber);

        if ($supervisorId) {
            $this->sendNotification($supervisorId, 'Work Order Assigned to You', 'WO ' . $woNumber . ' has been assigned to you as supervisor.', 'work_order');
        }

        return redirect()->to(base_url('workorders/view/' . $id))->with('success', 'Work Order ' . $woNumber . ' created.');
    }

    // ----------------------------------------------------------
    // Show
    // ----------------------------------------------------------

    public function show(int $id)
    {
        return $this->view($id);
    }

    // ----------------------------------------------------------
    // ----------------------------------------------------------
    // Invoice Preparation (fixes 404 on /workorders/prepare-invoice/{id})
    // ----------------------------------------------------------

    public function prepareInvoice(int $id)
    {
        $this->requireRole(['super_admin', 'facility_manager', 'finance_manager', 'supervisor']);
        $wo = $this->model->getDetail($id);
        if (! $wo) return redirect()->to(base_url('workorders'))->with('error', 'Not found.');

        // If invoice already exists, go directly to invoice view (no preparation stage)
        $existing = $this->db->table('invoices')
            ->where('work_order_id', $id)
            ->where('deleted_at', null)
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('created_at', 'DESC')
            ->get()->getRowArray();
        if ($existing) {
            return redirect()->to(base_url('finance/invoices/view/' . $existing['id']))
                ->with('info', 'Invoice ' . $existing['invoice_number'] . ' already exists for this work order.');
        }

        $materials     = $this->woMaterialsForOrder($id);
        $labor         = $this->db->table('wo_labor wl')
            ->select('wl.*, u.name AS tech_name')
            ->join('users u', 'u.id = wl.user_id', 'left')
            ->where('wl.wo_id', $id)->get()->getResultArray();
        $laborTotal    = array_sum(array_column($labor, 'labor_cost'));
        $materialTotal = array_sum(array_column($materials, 'total_cost'));

        return view('workorders/prepare_invoice', $this->viewData([
            'title'         => 'Prepare Invoice — ' . $wo['wo_number'],
            'wo'            => $wo,
            'materials'     => $materials,
            'labor'         => $labor,
            'laborTotal'    => $laborTotal,
            'materialTotal' => $materialTotal,
            'currency'      => $this->settings['currency'] ?? 'QAR',
        ]));
    }

    public function storePreparedInvoice(int $id)
    {
        $this->requireRole(['super_admin', 'facility_manager', 'finance_manager', 'supervisor']);
        $wo = $this->model->getDetail($id);
        if (! $wo) return redirect()->to(base_url('workorders'))->with('error', 'Not found.');

        $lineItems  = $this->request->getPost('lines')   ?? [];
        $extraItems = $this->request->getPost('extra')   ?? [];
        $vatRate    = (float)($this->request->getPost('tax_rate')  ?? 0);
        $discount   = (float)($this->request->getPost('discount')  ?? 0);
        $notes      = $this->request->getPost('notes')   ?? '';

        // Build lines
        $lines = [];
        foreach (array_merge($lineItems, $extraItems) as $li) {
            if (empty($li['description'])) continue;
            $qty          = (float)($li['qty']              ?? 1);
            $unitPrice    = (float)($li['unit_price']       ?? 0);
            $internalCost = (float)($li['internal_cost']    ?? 0);
            $lines[] = [
                'description'   => $li['description'],
                'qty'           => $qty,
                'unit_price'    => $unitPrice,
                'total'         => $qty * $unitPrice,
                'internal_cost' => $internalCost,
            ];
        }

        $subtotal  = array_sum(array_column($lines, 'total'));
        $vatAmt    = round($subtotal * $vatRate / 100, 2);
        $total     = round($subtotal + $vatAmt - $discount, 2);

        // Check if an invoice already exists for this WO (avoid duplicate invoice creation)
        $existing = $this->db->table('invoices')
            ->where('work_order_id', $id)
            ->whereIn('status', ['draft', 'sent', 'partial', 'paid'])
            ->where('deleted_at', null)
            ->get()->getRowArray();
        if ($existing) {
            $statusLabel = ucfirst($existing['status']);
            if (in_array($existing['status'], ['paid'])) {
                return redirect()->to(base_url('finance/invoices/view/' . $existing['id']))
                    ->with('error', 'Invoice ' . $existing['invoice_number'] . ' is already marked as PAID for this work order. Duplicate invoices are not allowed.');
            }
            return redirect()->to(base_url('finance/invoices/view/' . $existing['id']))
                ->with('info', 'Invoice ' . $existing['invoice_number'] . ' (status: ' . $statusLabel . ') already exists for this work order.');
        }

        // Generate a unique sequential number (INV-WO-YYYY-NNNN)
        $year    = date('Y');
        $prefix  = 'INV-WO-' . $year . '-';
        $last    = $this->db->table('invoices')
            ->like('invoice_number', $prefix, 'after')
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()->getRowArray();
        $seq     = 1;
        if ($last && ! empty($last['invoice_number'])) {
            helper('fm');
            $seq = fm_sequence_from_code((string) $last['invoice_number']) + 1;
        }
        $invNumber = $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);

        // Insert into invoices using real column names
        $insert = [
            'invoice_number'  => $invNumber,
            'facility_id'     => $wo['facility_id'] ?? null,
            'work_order_id'   => $id,
            'invoice_type'    => 'work_order',
            'issue_date'      => date('Y-m-d'),
            'due_date'        => date('Y-m-d', strtotime('+30 days')),
            'subtotal'        => $subtotal,
            'vat_rate'        => $vatRate,
            'vat_amount'      => $vatAmt,
            'total'           => $total,
            'currency'        => $this->settings['currency'] ?? 'QAR',
            'status'          => 'draft',
            'notes'           => $notes,
            'created_by'      => session()->get('user_id'),
            'created_at'      => date('Y-m-d H:i:s'),
        ];
        if ($this->db->fieldExists('due_amount', 'invoices')) {
            $insert['paid_amount']    = 0;
            $insert['due_amount']     = $total;
            $insert['pending_amount'] = $total;
        }
        $this->db->table('invoices')->insert($insert);
        $invoiceId = (int) $this->db->insertID();

        // Insert invoice_items rows
        foreach ($lines as $sort => $li) {
            $this->db->table('invoice_items')->insert([
                'invoice_id'          => $invoiceId,
                'line_type'           => 'service',
                'description'         => $li['description'],
                'quantity'            => $li['qty'],
                'unit_price'          => $li['unit_price'],
                'unit_cost_internal'  => $li['internal_cost'],
                'amount'              => $li['total'],
                'work_order_id'       => $id,
                'sort_order'          => $sort,
            ]);
        }

        $this->model->update($id, ['status' => 'invoice_ready']);
        $this->logActivity('prepare_invoice', 'work_orders', $id, 'Invoice ' . $invNumber . ' created');

        return redirect()->to(base_url('finance/invoices/view/' . $invoiceId))
            ->with('success', 'Draft invoice ' . $invNumber . ' created.');
    }

    // ----------------------------------------------------------
    // Sync job card costs (fixes 404 /workorders/sync-job-cards/{id})
    // ----------------------------------------------------------

    public function syncJobCardCosts(int $id)
    {
        $this->syncJobCardsToWorkOrder($id);
        if ($this->request->isAJAX()) return $this->response->setJSON(['ok' => true]);
        return redirect()->back()->with('success', 'Costs synced.');
    }

    // ----------------------------------------------------------
    // AJAX CTA Actions — return JSON, no full-page reload
    // ----------------------------------------------------------
    // ----------------------------------------------------------

    /**
     * POST /workorders/ajax/assign-supervisor/{id}
     * Returns JSON so the action panel updates without reload.
     */
    public function ajaxAssignSupervisor(int $id)
    {
        $this->requireRole(['super_admin', 'facility_manager']);
        $supervisorId = (int) $this->request->getPost('supervisor_id');
        if (! $supervisorId) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'Select a supervisor.']);
        }
        $this->model->advanceStage($id, 'assigned_to_supervisor', [
            'supervisor_id' => $supervisorId,
            'status'        => 'assigned',
        ]);
        $wo = $this->model->find($id);
        $this->sendNotification($supervisorId, 'WO Assigned to You as Supervisor', 'Work Order ' . $wo['wo_number'] . ' is assigned to you.', 'work_order');
        $this->logActivity('assign_supervisor', 'work_orders', $id, 'Supervisor assigned via AJAX');
        return $this->response->setJSON(['ok' => true, 'stage' => 'assigned_to_supervisor']);
    }

    /**
     * POST /workorders/ajax/quick-status/{id}
     */
    public function ajaxQuickStatus(int $id)
    {
        $status = $this->request->getPost('status');
        if (! $status) {
            return $this->response->setJSON(['ok' => false, 'msg' => 'No status.']);
        }
        $extra = $status === 'completed' ? ['completed_at' => date('Y-m-d H:i:s')] : [];
        $this->model->update($id, array_merge(['status' => $status], $extra));
        $this->logActivity('status_change', 'work_orders', $id, 'Status → ' . $status . ' (AJAX)');
        return $this->response->setJSON(['ok' => true, 'status' => $status]);
    }

    /**
     * POST /workorders/ajax/approve/{id}
     */
    public function ajaxApprove(int $id)
    {
        $this->requireRole(['super_admin', 'facility_manager']);
        $this->model->update($id, [
            'approval_status' => 'approved',
            'approved_by'     => $this->currentUser()['id'],
            'approved_at'     => date('Y-m-d H:i:s'),
        ]);
        $this->logActivity('approve', 'work_orders', $id, 'WO approved via AJAX');
        return $this->response->setJSON(['ok' => true]);
    }

    /**
     * POST /workorders/ajax/escalate/{id}
     */
    public function ajaxEscalate(int $id)
    {
        $this->requireRole(['super_admin', 'facility_manager']);
        $this->model->update($id, ['priority' => 'critical', 'sla_breached' => 1]);
        $this->logActivity('escalate', 'work_orders', $id, 'WO escalated via AJAX');
        return $this->response->setJSON(['ok' => true, 'priority' => 'critical']);
    }

    /**
     * GET /workorders/ajax/actions/{id}
     * Returns rendered action panel HTML fragment for partial refresh.
     */
    public function ajaxActionsPanel(int $id)
    {
        $wo          = $this->model->getDetail($id);
        $jobCards    = $this->db->table('job_cards jc')
            ->select('jc.id, jc.jc_number, jc.status, jc.assigned_to, u.name AS technician_name')
            ->join('users u', 'u.id = jc.assigned_to', 'left')
            ->where('jc.wo_id', $id)->where('jc.deleted_at', null)
            ->orderBy('jc.created_at', 'DESC')->get()->getResultArray();

        $userModel   = new \App\Models\UserModel();
        $html = view('workorders/_wo_actions_panel', [
            'wo'          => $wo,
            'jobCards'    => $jobCards,
            'supervisors' => $userModel->getUsersByRole('supervisor'),
            'technicians' => $userModel->getUsersByRole('technician'),
        ]);
        return $this->response->setHeader('Content-Type', 'text/html')->setBody($html);
    }



    public function assignSupervisor(int $id)
    {
        $this->requireRole(['super_admin', 'facility_manager']);

        $supervisorId = $this->request->getPost('supervisor_id');
        if (! $supervisorId) {
            return redirect()->back()->with('error', 'Please select a supervisor.');
        }

        $this->model->advanceStage($id, 'assigned_to_supervisor', [
            'supervisor_id' => $supervisorId,
            'status'        => 'assigned',
        ]);

        $wo = $this->model->find($id);
        $this->logActivity('assign_supervisor', 'work_orders', $id, 'Supervisor assigned');
        $this->sendNotification($supervisorId, 'WO Assigned to You as Supervisor', 'Work Order ' . $wo['wo_number'] . ' is assigned to you.', 'work_order');

        return redirect()->to(base_url('workorders/view/' . $id) . '#wo-workflow')->with('success', 'Supervisor assigned.');
    }

    // ----------------------------------------------------------
    // Stage 8 — Planning & Scheduling
    // ----------------------------------------------------------

    public function schedule(int $id)
    {
        $this->requireRole(['super_admin', 'facility_manager', 'supervisor']);

        $rules = [
            'planned_start' => 'required',
            'planned_end'   => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $this->model->advanceStage($id, 'planning_scheduling', [
            'planned_start' => $this->request->getPost('planned_start'),
            'planned_end'   => $this->request->getPost('planned_end'),
            'status'        => 'assigned',
        ]);

        $this->logActivity('schedule', 'work_orders', $id, 'Work scheduled');
        return redirect()->to(base_url('workorders/view/' . $id) . '#wo-workflow')->with('success', 'Work scheduled.');
    }

    // ----------------------------------------------------------
    // Stage 10 — QC / Inspection
    // ----------------------------------------------------------

    public function submitQc(int $id)
    {
        $this->requireRole(['super_admin', 'facility_manager', 'supervisor']);

        $this->model->advanceStage($id, 'inspection_qc', [
            'qa_status' => 'pending',
        ]);

        $this->logActivity('submit_qc', 'work_orders', $id, 'Submitted for QC inspection');
        return redirect()->to(base_url('workorders/view/' . $id))->with('success', 'Work order submitted for quality check.');
    }

    public function approveQc(int $id)
    {
        $this->requireRole(['super_admin', 'facility_manager', 'supervisor']);

        $action = $this->request->getPost('action'); // approve | reject
        $notes  = $this->request->getPost('qa_notes');

        if ($action === 'approve') {
            $this->model->advanceStage($id, 'job_completed', [
                'qa_status'       => 'approved',
                'qa_approved_by'  => $this->currentUser()['id'],
                'qa_approved_at'  => date('Y-m-d H:i:s'),
                'status'          => 'completed',
                'completed_at'    => date('Y-m-d H:i:s'),
                'completion_notes'=> $notes,
            ]);
            $this->logActivity('qa_approve', 'work_orders', $id, 'QC approved');
        } else {
            $this->model->update($id, [
                'qa_status'      => 'rejected',
                'workflow_stage' => 'work_execution',
                'status'         => 'in_progress',
                'completion_notes'=> $notes,
            ]);
            $this->logActivity('qa_reject', 'work_orders', $id, 'QC rejected — sent back to execution');
        }

        return redirect()->to(base_url('workorders/view/' . $id))->with('success', 'QC decision recorded.');
    }

    // ----------------------------------------------------------
    // Stage 12 — Close Work Order
    // ----------------------------------------------------------

    public function close(int $id)
    {
        $this->requireRole(['super_admin', 'facility_manager', 'supervisor']);

        $wo = $this->model->getDetail($id);
        if ($wo['qa_status'] !== 'approved' && $wo['status'] !== 'completed') {
            return redirect()->back()->with('error', 'QC must be approved before closing.');
        }

        $this->model->advanceStage($id, 'wo_closed', [
            'status'                => 'closed',
            'client_approval_status'=> 'approved',
            'client_approved_by'    => $this->currentUser()['id'],
            'client_approved_at'    => date('Y-m-d H:i:s'),
        ]);

        $this->logActivity('close', 'work_orders', $id, 'Work Order closed');
        return redirect()->to(base_url('workorders/view/' . $id))->with('success', 'Work Order closed successfully.');
    }

    // ----------------------------------------------------------
    // Add comment
    // ----------------------------------------------------------

    public function addComment(int $id)
    {
        $comment = trim($this->request->getPost('comment') ?? '');
        if ($comment === '') {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => false, 'message' => 'Comment cannot be empty.']);
            }
            return redirect()->back()->with('error', 'Comment cannot be empty.');
        }

        $this->db->table('wo_comments')->insert([
            'wo_id'      => $id,
            'user_id'    => $this->currentUser()['id'],
            'comment'    => $comment,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('workorders/view/' . $id . '#comments'))->with('success', 'Comment added.');
    }

    // ----------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------



    /**
     * Copy job card labor hours and materials into wo_labor / wo_materials (idempotent).
     */
    protected function syncJobCardsToWorkOrder(int $woId): void
    {
        try {
            $cards = $this->db->table('job_cards')
                ->where('wo_id', $woId)
                ->where('deleted_at', null)
                ->whereIn('status', ['in_progress', 'completed', 'approved'])
                ->get()->getResultArray();

            $uid  = $this->currentUser()['id'] ?: 1;
            $rate = (float) ($this->settings['default_hourly_rate'] ?? 35);

            foreach ($cards as $jc) {
                $marker = 'Job Card ' . $jc['jc_number'];
                $hours  = (float) ($jc['labor_hours'] ?? 0);

                if ($hours > 0) {
                    $exists = $this->db->table('wo_labor')
                        ->where('wo_id', $woId)
                        ->like('notes', $marker, 'after')
                        ->countAllResults();
                    if (! $exists) {
                        $workDate = ! empty($jc['completed_at'])
                            ? date('Y-m-d', strtotime($jc['completed_at']))
                            : date('Y-m-d');
                        $this->db->table('wo_labor')->insert([
                            'wo_id'        => $woId,
                            'user_id'      => (int) ($jc['assigned_to'] ?? $uid),
                            'work_date'    => $workDate,
                            'hours_worked' => $hours,
                            'hourly_rate'  => $rate,
                            'labor_cost'   => $hours * $rate,
                            'notes'        => $marker,
                            'created_by'   => $uid,
                            'created_at'   => date('Y-m-d H:i:s'),
                        ]);
                    }
                }

                $mats = $this->db->table('jc_materials')->where('jc_id', $jc['id'])->get()->getResultArray();
                foreach ($mats as $m) {
                    $existsM = $this->db->table('wo_materials')
                        ->where('wo_id', $woId)
                        ->where('item_name', $m['item_name'])
                        ->like('notes', $marker, 'after')
                        ->countAllResults();
                    if ($existsM) {
                        continue;
                    }
                    $qty  = (float) ($m['quantity'] ?? 1);
                    $cost = (float) ($m['unit_cost'] ?? 0);
                    $this->db->table('wo_materials')->insert([
                        'wo_id'               => $woId,
                        'item_id'             => $m['item_id'] ?: null,
                        'item_name'           => $m['item_name'],
                        'quantity'            => $qty,
                        'unit_cost'           => $cost,
                        'total_cost'          => (float) ($m['total_cost'] ?? ($qty * $cost)),
                        'deducted_from_stock' => 0,
                        'notes'               => $marker,
                        'added_by'            => $uid,
                        'created_at'          => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'syncJobCardsToWorkOrder: ' . $e->getMessage());
        }
    }

    public function view(int $id)
    {
        $wo = $this->model->getDetail($id);
        if (! $wo) {
            return redirect()->to(base_url('workorders'))->with('error', 'Work Order not found.');
        }
        $this->authorizeWo($wo);

        // NOTE: sync is now triggered only on JC complete, not on every page view.
        $labor = $this->db->table('wo_labor wl')
            ->select('wl.*, u.name AS tech_name')
            ->join('users u', 'u.id = wl.user_id', 'left')
            ->where('wl.wo_id', $id)
            ->orderBy('wl.work_date', 'DESC')
            ->get()->getResultArray();

        $materials = $this->woMaterialsForOrder($id);

        $laborTotal    = array_sum(array_map(static function ($r) { return (float) ($r['labor_cost'] ?? 0); }, $labor));
        $materialTotal = array_sum(array_map(static function ($r) { return (float) ($r['total_cost'] ?? 0); }, $materials));
        $vendorCost    = (float) ($wo['vendor_cost'] ?? 0);
        $totalCost     = $laborTotal + $materialTotal + $vendorCost;

        $attachments = [];
        if ($this->db->tableExists('wo_attachments')) {
            $attachments = $this->db->table('wo_attachments')->where('wo_id', $id)->get()->getResultArray();
        }

        $comments = $this->db->table('wo_comments wc')
            ->select('wc.*, u.name AS user_name')
            ->join('users u', 'u.id = wc.user_id', 'left')
            ->where('wc.wo_id', $id)
            ->orderBy('wc.created_at', 'ASC')
            ->get()->getResultArray();

        $meterReadings = [];
        if ($wo['asset_id'] && $this->db->tableExists('asset_meter_readings')) {
            $meterReadings = $this->db->table('asset_meter_readings')
                ->where('asset_id', $wo['asset_id'])
                ->orderBy('reading_date', 'DESC')
                ->limit(10)
                ->get()->getResultArray();
        }

        $workflow = (new \App\Services\WorkOrderWorkflowService($this->db, $this->settings))
            ->workflowSummary($id);

        $inventoryItems = $this->inventoryItemsForSelect();

        // Single batched query for supervisors + technicians (avoids 2 separate full-table scans)
        $userRows = $this->db->table('users u')
            ->select('u.id, u.name, r.name AS role_name')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->whereIn('r.name', ['supervisor', 'technician'])
            ->where('u.status', 'active')
            ->orderBy('u.name', 'ASC')
            ->get()->getResultArray();
        $supervisors = array_values(array_filter($userRows, fn($u) => $u['role_name'] === 'supervisor'));
        $technicians = array_values(array_filter($userRows, fn($u) => $u['role_name'] === 'technician'));

        $jobCards = $this->db->table('job_cards jc')
            ->select('jc.*, u.name AS technician_name, wo.started_at AS wo_started_at')
            ->join('users u', 'u.id = jc.assigned_to', 'left')
            ->join('work_orders wo', 'wo.id = jc.wo_id', 'left')
            ->where('jc.wo_id', $id)
            ->where('jc.deleted_at', null)
            ->orderBy('jc.created_at', 'DESC')
            ->get()->getResultArray();

        $approvals = [];
        if ($this->db->tableExists('wo_approvals')) {
            $approvals = $this->db->table('wo_approvals wa')
                ->select('wa.*, u.name AS actioned_by_name')
                ->join('users u', 'u.id = wa.actioned_by', 'left')
                ->where('wa.wo_id', $id)
                ->orderBy('wa.created_at', 'DESC')
                ->get()->getResultArray();
        }

        $chatCount = 0;
        if ($this->db->tableExists('wo_chat_messages')) {
            $chatCount = (int) $this->db->table('wo_chat_messages')->where('wo_id', $id)->countAllResults();
        }

        // Site visits linked to this WO
        $siteVisits = [];
        if ($this->db->tableExists('site_visits')) {
            $siteVisits = $this->db->table('site_visits sv')
                ->select('sv.*, u.name AS technician_name')
                ->join('users u', 'u.id = sv.technician_id', 'left')
                ->where('sv.work_order_id', $id)
                ->orderBy('sv.scheduled_at', 'DESC')
                ->get()->getResultArray();
        }

        $techniciansForSv = $technicians; // reuse already-loaded technicians

        return view('workorders/view', $this->viewData([
            'title'          => 'Work Order — ' . $wo['wo_number'],
            'wo'             => $wo,
            'labor'          => $labor,
            'materials'      => $materials,
            'attachments'    => $attachments,
            'comments'       => $comments,
            'meterReadings'  => $meterReadings,
            'workflow'       => $workflow,
            'inventoryItems' => $inventoryItems,
            'technicians'    => $technicians,
            'supervisors'    => $supervisors,
            'jobCards'       => $jobCards,
            'approvals'      => $approvals,
            'stageFlow'      => $this->buildStageFlow($wo, count($jobCards)),
            'laborTotal'     => $laborTotal,
            'materialTotal'  => $materialTotal,
            'vendorCost'     => $vendorCost,
            'totalCost'      => $totalCost,
            'chatCount'      => $chatCount,
            'siteVisits'     => $siteVisits,
            'techniciansForSv' => $techniciansForSv,
        ]));
    }

    public function edit(int $id)
    {
        $this->requireRole('super_admin', 'facility_manager');
        $wo = $this->model->getDetail($id);
        if (! $wo) {
            return redirect()->to(base_url('workorders'))->with('error', 'Work Order not found.');
        }

        $facilityModel = new \App\Models\FacilityModel();
        $userModel     = new \App\Models\UserModel();

        return view('workorders/edit', $this->viewData([
            'title'        => 'Edit ' . $wo['wo_number'],
            'wo'           => $wo,
            'facilities'   => $facilityModel->scopeForUser($this->currentUser())->findAll(),
            'assets'       => $this->db->table('assets')->where('facility_id', $wo['facility_id'])->where('status', 'active')->get()->getResultArray(),
            'technicians'  => $userModel->getUsersByRole('technician'),
            'vendors'      => $this->db->table('vendors')->where('status', 'active')->get()->getResultArray(),
        ]));
    }

    public function update(int $id)
    {
        $this->requireRole('super_admin', 'facility_manager');
        $wo = $this->model->find($id);
        if (! $wo) {
            return redirect()->to(base_url('workorders'))->with('error', 'Work Order not found.');
        }

        $post = $this->request->getPost();
        $data = [
            'title'             => $post['title'],
            'description'       => $post['description'] ?? '',
            'facility_id'       => $post['facility_id'],
            'asset_id'          => $post['asset_id'] ?: null,
            'type'              => $post['type'],
            'category'          => $post['category'] ?: null,
            'priority'          => $post['priority'],
            'status'            => $post['status'],
            'assigned_to'       => $post['assigned_to'] ?: null,
            'vendor_id'         => $post['vendor_id'] ?: null,
            'planned_start'     => $post['planned_start'] ?: null,
            'planned_end'       => $post['planned_end'] ?: null,
            'estimated_cost'    => $post['estimated_cost'] ?: null,
            'actual_cost'       => $post['actual_cost'] ?: null,
            'completion_notes'  => $post['completion_notes'] ?? '',
            'requester_name'    => $post['requester_name'] ?? '',
            'requester_phone'   => $post['requester_phone'] ?? '',
            'requester_email'   => $post['requester_email'] ?? '',
        ];

        if (($post['status'] ?? '') === 'completed' && empty($wo['completed_at'])) {
            $data['completed_at'] = date('Y-m-d H:i:s');
        }

        $this->model->update($id, $data);
        $this->logActivity('update', 'work_orders', $id, 'Work order updated');

        return redirect()->to(base_url('workorders/view/' . $id))->with('success', 'Work order updated.');
    }

    public function delete(int $id)
    {
        $this->requireRole('super_admin', 'facility_manager');
        $this->model->delete($id);
        $this->logActivity('delete', 'work_orders', $id, 'Work order deleted');

        return redirect()->to(base_url('workorders'))->with('success', 'Work order removed.');
    }

    public function schedulePage()
    {
        $this->requireRole('super_admin', 'facility_manager');
        $user   = $this->currentUser();
        $result = $this->model->getListWithRelations(['type' => 'preventive'], 50, $user);

        return view('workorders/schedule', $this->viewData([
            'title'      => 'PM Schedule',
            'workOrders' => $result['data'],
        ]));
    }

    public function qaApprove(int $id)
    {
        $this->requireRole('super_admin', 'facility_manager', 'supervisor');
        try {
            (new \App\Services\WorkOrderWorkflowService($this->db, $this->settings))
                ->qaApprove($id, $this->currentUser()['id'], $this->request->getPost('notes'));
            $this->logActivity('qa_approve', 'work_orders', $id, null);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to(base_url('workorders/view/' . $id))->with('success', 'QA approved.');
    }

    public function qaReject(int $id)
    {
        $this->requireRole('super_admin', 'facility_manager', 'supervisor');
        try {
            (new \App\Services\WorkOrderWorkflowService($this->db, $this->settings))
                ->qaReject($id, $this->currentUser()['id'], $this->request->getPost('notes'));
            $this->logActivity('qa_reject', 'work_orders', $id, null);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to(base_url('workorders/view/' . $id))->with('success', 'QA rejected — sent back to execution.');
    }

    public function clientApprove(int $id)
    {
        $this->requireRole('super_admin', 'facility_manager', 'client');
        try {
            $inv = (new \App\Services\WorkOrderWorkflowService($this->db, $this->settings))
                ->clientApprove($id, $this->currentUser()['id'], $this->request->getPost('notes'));
            $this->logActivity('client_approve', 'work_orders', $id, 'Invoice ' . ($inv['invoice_number'] ?? ''));
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to(base_url('workorders/view/' . $id))->with('success', 'Client approved and invoice created.');
    }

    public function clientReject(int $id)
    {
        $this->requireRole('super_admin', 'facility_manager', 'client');
        try {
            (new \App\Services\WorkOrderWorkflowService($this->db, $this->settings))
                ->clientReject($id, $this->currentUser()['id'], $this->request->getPost('notes'));
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->to(base_url('workorders/view/' . $id))->with('success', 'Client rejected.');
    }

    public function approve(int $id)
    {
        $this->requireRole('super_admin', 'facility_manager');
        $this->model->update($id, [
            'approval_status' => 'approved',
            'approved_by'     => $this->currentUser()['id'],
            'approved_at'     => date('Y-m-d H:i:s'),
        ]);
        $this->logActivity('approve', 'work_orders', $id, 'WO approved');

        return redirect()->to(base_url('workorders/view/' . $id))->with('success', 'Work order approved.');
    }

    public function reject(int $id)
    {
        $this->requireRole('super_admin', 'facility_manager');
        $this->model->update($id, [
            'approval_status' => 'rejected',
            'status'          => 'cancelled',
            'approved_by'     => $this->currentUser()['id'],
            'approved_at'     => date('Y-m-d H:i:s'),
        ]);
        $this->logActivity('reject', 'work_orders', $id, 'WO rejected');

        return redirect()->to(base_url('workorders/view/' . $id))->with('success', 'Work order rejected.');
    }

    public function escalate(int $id)
    {
        $this->requireRole('super_admin', 'facility_manager');
        $this->model->update($id, ['priority' => 'critical', 'sla_breached' => 1]);
        $this->logActivity('escalate', 'work_orders', $id, 'WO escalated');
        $this->notifyManagers('WO Escalated', 'Work order #' . $id . ' has been escalated to critical.');

        return redirect()->to(base_url('workorders/view/' . $id))->with('success', 'Work order escalated.');
    }

    public function quickStatus()
    {
        $id     = (int) $this->request->getPost('wo_id');
        $status = $this->request->getPost('status');
        if ($id && $status) {
            $extra = [];
            if ($status === 'completed') {
                $extra['completed_at'] = date('Y-m-d H:i:s');
            }
            $this->model->update($id, array_merge(['status' => $status], $extra));
            $this->logActivity('status_change', 'work_orders', $id, 'Status → ' . $status);
        }

        return redirect()->to(base_url('workorders/view/' . $id))->with('success', 'Status updated.');
    }

    public function addLabor(int $id)
    {
        $post = $this->request->getPost();
        $hours = (float) ($post['hours_worked'] ?? 0);
        $rate  = (float) ($post['hourly_rate'] ?? 0);
        $this->db->table('wo_labor')->insert([
            'wo_id'         => $id,
            'user_id'       => $post['user_id'] ?: $this->currentUser()['id'],
            'work_date'     => $post['work_date'] ?? date('Y-m-d'),
            'hours_worked'  => $hours,
            'hourly_rate'   => $rate,
            'labor_cost'    => $hours * $rate,
            'notes'         => $post['notes'] ?? '',
            'created_by'    => $this->currentUser()['id'],
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('workorders/view/' . $id . '#tab-labor'))->with('success', 'Labor entry added.');
    }

    public function deleteLabor(int $laborId)
    {
        $row = $this->db->table('wo_labor')->where('id', $laborId)->get()->getRowArray();
        if ($row) {
            $this->db->table('wo_labor')->where('id', $laborId)->delete();
            return redirect()->to(base_url('workorders/view/' . $row['wo_id'] . '#tab-labor'))->with('success', 'Labor entry removed.');
        }

        return redirect()->back()->with('error', 'Entry not found.');
    }

    public function addMaterial(int $id)
    {
        $post   = $this->request->getPost();
        $qty    = (float) ($post['quantity'] ?? 0);
        $cost   = (float) ($post['unit_cost'] ?? 0);
        $itemId = (int) ($post['item_id'] ?? 0);

        $item     = [];
        $itemName = $post['item_name'] ?? '';
        if ($itemId) {
            $item = $this->db->table('inventory_items')->where('id', $itemId)->get()->getRowArray() ?? [];
            $itemName = $item['name'] ?? $itemName;
        }
        $itemCode = $post['item_code'] ?? '';
        if ($itemId && ! empty($item['item_code'])) {
            $itemCode = $item['item_code'];
        }
        $this->db->table('wo_materials')->insert([
            'wo_id'      => $id,
            'item_id'    => $itemId ?: null,
            'item_name'  => $itemName ?: 'Material',
            'item_code'  => $itemCode ?: null,
            'quantity'   => $qty,
            'unit_cost'  => $cost,
            'total_cost' => $qty * $cost,
            'deducted_from_stock' => $itemId ? 1 : 0,
            'notes'      => $post['mat_notes'] ?? $post['notes'] ?? '',
            'added_by'   => $this->currentUser()['id'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        if ($itemId) {
            $this->db->query('UPDATE inventory_items SET quantity = quantity - ? WHERE id = ?', [$qty, $itemId]);
        }

        return redirect()->to(base_url('workorders/view/' . $id . '#tab-materials'))->with('success', 'Material added.');
    }

    public function deleteMaterial(int $materialId)
    {
        $row = $this->db->table('wo_materials')->where('id', $materialId)->get()->getRowArray();
        if ($row) {
            if (! empty($row['item_id'])) {
                $this->db->query('UPDATE inventory_items SET quantity = quantity + ? WHERE id = ?', [$row['quantity'], $row['item_id']]);
            }
            $this->db->table('wo_materials')->where('id', $materialId)->delete();
            return redirect()->to(base_url('workorders/view/' . $row['wo_id'] . '#tab-materials'))->with('success', 'Material removed.');
        }

        return redirect()->back()->with('error', 'Material not found.');
    }

    public function addMeter(int $id)
    {
        $wo = $this->model->find($id);
        if ($wo && $wo['asset_id']) {
            $this->db->table('asset_meter_readings')->insert([
                'asset_id'      => $wo['asset_id'],
                'reading_date'  => date('Y-m-d'),
                'reading_value' => $this->request->getPost('reading_value'),
                'notes'         => $this->request->getPost('notes') ?? '',
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        }

        return redirect()->to(base_url('workorders/view/' . $id . '#tab-asset'))->with('success', 'Meter reading recorded.');
    }

    public function upload(int $id)
    {
        $file = $this->request->getFile('attachment');
        if ($file && $file->isValid() && ! $file->hasMoved() && $this->db->tableExists('wo_attachments')) {
            $name = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads/work_orders', $name);
            $this->db->table('wo_attachments')->insert([
                'wo_id'       => $id,
                'file_path'   => 'uploads/work_orders/' . $name,
                'file_name'   => $file->getClientName(),
                'uploaded_by' => $this->currentUser()['id'],
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        return redirect()->to(base_url('workorders/view/' . $id . '#tab-docs'))->with('success', 'File uploaded.');
    }


    /** @return list<array{slug: string, label: string, icon: string, done: bool, current: bool}> */
    protected function buildStageFlow(array $wo, int $jobCardCount = 0): array
    {
        $stages = [
            ['complaint_received',     'Complaint Received',              'bi-envelope'],
            ['complaint_verification', 'Complaint Verification',          'bi-search'],
            ['approval_process',       'Approval Process',                'bi-check-circle'],
            ['converted_to_wo',        'Convert to Work Order',           'bi-file-earmark-plus'],
            ['assigned_to_supervisor', 'WO Assigned to Supervisor',       'bi-person-check'],
            ['job_card_created',       'Supervisor Creates Job Card',     'bi-card-checklist'],
            ['technician_assigned',    'Technician Assignment',           'bi-tools'],
            ['planning_scheduling',    'Planning & Scheduling',           'bi-calendar-event'],
            ['work_execution',         'Work Execution',                    'bi-gear'],
            ['inspection_qc',          'Inspection / Quality Check',      'bi-clipboard-check'],
            ['job_completed',          'Job Completion',                    'bi-check-all'],
            ['wo_closed',              'Work Order Closure',              'bi-lock'],
        ];

        $current = $wo['workflow_stage'] ?? 'converted_to_wo';
        $order   = array_column($stages, 0);
        $pos     = array_search($current, $order, true);
        if ($pos === false) {
            $pos = 0;
        }
        // If job card exists but stage was left on job_card_created, show forward progress
        if ($jobCardCount > 0) {
            $techPos = array_search('technician_assigned', $order, true);
            if ($techPos !== false && $pos < $techPos) {
                $pos     = $techPos;
                $current = 'technician_assigned';
            }
        }

        $result = [];
        foreach ($stages as $i => [$slug, $label, $icon]) {
            $short = preg_replace('/^\d+\.\s*/', '', $label);
            $short = match ($slug) {
                'complaint_received'     => 'Received',
                'complaint_verification' => 'Verify',
                'approval_process'       => 'Approve',
                'converted_to_wo'        => 'WO',
                'assigned_to_supervisor' => 'Supervisor',
                'job_card_created'       => 'Job Card',
                'technician_assigned'    => 'Tech',
                'planning_scheduling'    => 'Plan',
                'work_execution'         => 'Execute',
                'inspection_qc'          => 'QC',
                'job_completed'          => 'Done',
                'wo_closed'              => 'Closed',
                default => $short,
            };
            $result[] = [
                'slug'    => $slug,
                'label'   => $label,
                'short'   => $short,
                'icon'    => $icon,
                'done'    => $i < $pos,
                'current' => $slug === $order[$pos],
            ];
        }

        return $result;
    }

    protected function legacyIndexData(): array
    {
        $user    = $this->currentUser();
        $filters = $this->request->getGet();
        $result  = $this->model->getListWithRelations($filters, 20, $user);

        $kpiBuilder = $this->db->table('work_orders')->where('deleted_at', null);
        $this->scopeFacilities($kpiBuilder, 'facility_id');
        $kpi = [
            'open'        => (clone $kpiBuilder)->whereIn('status', ['new', 'assigned'])->countAllResults(),
            'in_progress' => (clone $kpiBuilder)->where('status', 'in_progress')->countAllResults(),
            'overdue'     => (clone $kpiBuilder)->where('sla_breached', 1)->whereNotIn('status', ['completed', 'closed', 'cancelled'])->countAllResults(),
            'completed'   => (clone $kpiBuilder)->whereIn('status', ['completed', 'closed'])->countAllResults(),
        ];

        $workOrders = array_map(static function (array $row): array {
            $row['approval_status'] = $row['approval_status'] ?? 'approved';
            $row['category']        = $row['category'] ?? null;
            $row['asset_name']      = $row['asset_name'] ?? null;
            return $row;
        }, $result['data']);

        return $this->viewData([
            'title'       => 'Work Orders',
            'workOrders'  => $workOrders,
            'filters'     => $filters,
            'kpi'         => $kpi,
        ]);
    }

    private function authorizeWo(array $wo): void
    {
        $user = $this->currentUser();
        $role = $user['role_name'];

        if ($role === 'super_admin') return;

        if ($role === 'technician' && (int) $wo['assigned_to'] !== (int) $user['id']) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
    }

    protected function getStageFlow(): array
    {
        return [
            'complaint_received'    => ['label' => '1. Complaint Received',           'icon' => 'bi-envelope'],
            'complaint_verification'=> ['label' => '2. Complaint Verification',        'icon' => 'bi-search'],
            'approval_process'      => ['label' => '3. Approval Process',              'icon' => 'bi-check-circle'],
            'converted_to_wo'       => ['label' => '4. Converted to Work Order',       'icon' => 'bi-file-earmark-plus'],
            'assigned_to_supervisor'=> ['label' => '5. Assigned to Supervisor',        'icon' => 'bi-person-check'],
            'job_card_created'      => ['label' => '6. Job Card Created',              'icon' => 'bi-card-checklist'],
            'technician_assigned'   => ['label' => '7. Technician Assigned',           'icon' => 'bi-tools'],
            'planning_scheduling'   => ['label' => '8. Planning & Scheduling',         'icon' => 'bi-calendar-event'],
            'work_execution'        => ['label' => '9. Work Execution',                'icon' => 'bi-gear'],
            'inspection_qc'         => ['label' => '10. Inspection / Quality Check',  'icon' => 'bi-clipboard-check'],
            'job_completed'         => ['label' => '11. Job Completed',               'icon' => 'bi-check-all'],
            'wo_closed'             => ['label' => '12. Work Order Closed',           'icon' => 'bi-lock'],
        ];
    }
}
