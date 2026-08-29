<?php

namespace App\Controllers;

use App\Models\JobCardModel;
use App\Models\WorkOrderModel;
use App\Models\UserModel;

/**
 * JobCards — Stages 6, 7, 9, 11 of the WO / Job Card workflow.
 *
 * Stage 6:  Supervisor creates job card
 * Stage 7:  Technician assigned
 * Stage 9:  Work execution (technician updates progress)
 * Stage 11: Job completed
 */
class JobCards extends BaseController
{
    protected ?string $workspaceRequired = 'fm';
    private JobCardModel   $model;
    private WorkOrderModel $woModel;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->model   = new JobCardModel();
        $this->woModel = new WorkOrderModel();
    }

    // ----------------------------------------------------------
    // Index
    // ----------------------------------------------------------

    public function index()
    {
        $user    = $this->currentUser();
        $filters = $this->request->getGet();

        $builder = $this->db->table('job_cards jc')
            ->select('jc.id, jc.jc_number, jc.status, jc.scheduled_date, jc.labor_hours, jc.created_at,
                      wo.wo_number, wo.title AS wo_title, wo.priority,
                      f.name AS facility_name,
                      u1.name AS technician_name,
                      u2.name AS supervisor_name')
            ->join('work_orders wo', 'wo.id = jc.wo_id', 'left')
            ->join('facilities f',   'f.id = wo.facility_id', 'left')
            ->join('users u1',       'u1.id = jc.assigned_to', 'left')
            ->join('users u2',       'u2.id = jc.supervisor_id', 'left')
            ->where('jc.deleted_at', null)
            ->orderBy('jc.created_at', 'DESC');

        // Role-based scoping applied directly on this joined builder
        $role = $user['role_name'];
        switch ($role) {
            case 'facility_manager':
                $fmIds = array_column(
                    $this->db->table('facilities')
                             ->select('id')->where('manager_id', $user['id'])
                             ->where('deleted_at', null)->get()->getResultArray(),
                    'id'
                );
                $fmIds ? $builder->whereIn('wo.facility_id', $fmIds)
                       : $builder->where('1', '0');
                break;
            case 'supervisor':
                $builder->where('jc.supervisor_id', $user['id']);
                break;
            case 'technician':
                $builder->where('jc.assigned_to', $user['id']);
                break;
            case 'super_admin':
                break;
            default:
                $builder->where('1', '0');
        }

        if (! empty($filters['status'])) $builder->where('jc.status', $filters['status']);
        if (! empty($filters['search'])) $builder->like('jc.jc_number', $filters['search'])
                                                  ->orLike('wo.wo_number', $filters['search']);

        $perPage     = 20;
        $page        = max(1, (int) ($filters['page'] ?? 1));
        $total       = (clone $builder)->countAllResults(false);
        $jobCards    = $builder->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray();

        return view('job_cards/index', [
            'pageTitle'   => 'Job Cards',
            'jobCards'    => $jobCards,
            'filters'     => $filters,
            'total'       => $total,
            'perPage'     => $perPage,
            'currentPage' => $page,
        ]);
    }

    // ----------------------------------------------------------
    // Create  (Stage 6 — Supervisor creates job card)
    // ----------------------------------------------------------

    public function create(int $woId)
    {
        $this->requireRole(['super_admin', 'facility_manager', 'supervisor']);

        $wo = $this->woModel->getDetail($woId);
        if (! $wo) return redirect()->to('/work-orders')->with('error', 'Work order not found.');

        // Supervisor can only create JC for WOs assigned to them
        $user = $this->currentUser();
        if ($user['role_name'] === 'supervisor' && (int) $wo['supervisor_id'] !== (int) $user['id']) {
            return redirect()->back()->with('error', 'You are not the assigned supervisor for this work order.');
        }

        $userModel   = new UserModel();
        $technicians = $userModel->getUsersByRole('technician');

        return view('job_cards/create', [
            'pageTitle'   => 'Create Job Card for ' . $wo['wo_number'],
            'wo'          => $wo,
            'technicians' => $technicians,
        ]);
    }

    public function store(int $woId)
    {
        $this->requireRole(['super_admin', 'facility_manager', 'supervisor']);

        $wo = $this->woModel->find($woId);
        if (! $wo) return redirect()->to('/work-orders')->with('error', 'Work order not found.');

        $rules = [
            'assigned_to'    => 'required|is_natural_no_zero',
            'description'    => 'required|min_length[5]',
            'scheduled_date' => 'permit_empty|valid_date',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to(base_url('workorders/view/' . $woId) . '#wo-actions')->withInput()->with('errors', $this->validator->getErrors())->with('open_modal', 'createJobCard');
        }

        $post        = $this->request->getPost();
        $jcNumber    = $this->model->generateJcNumber();
        $technicianId = (int) $post['assigned_to'];

        $data = [
            'jc_number'       => $jcNumber,
            'wo_id'           => $woId,
            'supervisor_id'   => $this->currentUser()['id'],
            'assigned_to'     => $technicianId,
            'description'     => $post['description'],
            'status'          => 'draft',
            'scheduled_date'  => $post['scheduled_date'] ?: null,
            'scheduled_hours' => $post['scheduled_hours'] ?: null,
            'created_by'      => $this->currentUser()['id'],
        ];

        $jcId = $this->model->insert($data);

        $supervisorId = (int) ($wo['supervisor_id'] ?? $this->currentUser()['id']);

        // Advance WO: job card created → technician assigned (stay in forward flow)
        $this->woModel->advanceStage($woId, 'technician_assigned', [
            'assigned_to'   => $technicianId,
            'supervisor_id' => $supervisorId ?: null,
            'status'        => 'assigned',
        ]);

        $this->logActivity('create', 'job_cards', $jcId, 'Job Card created: ' . $jcNumber);
        $this->sendNotification($technicianId, 'Job Card Assigned', 'Job Card ' . $jcNumber . ' has been assigned to you.', 'work_order');

        return redirect()->to(base_url('workorders/view/' . $woId) . '#wo-workflow')
            ->with('success', 'Job Card ' . $jcNumber . ' created. Technician assigned — continue on this work order.');
    }

    // ----------------------------------------------------------
    // Show
    // ----------------------------------------------------------

    public function show(int $id)
    {
        $jc = $this->model->getDetail($id);
        if (! $jc) return redirect()->to('/job-cards')->with('error', 'Job card not found.');

        $this->authorizeJc($jc);

        $jc['assigned_to_name'] = $jc['technician_name'] ?? ($jc['assigned_to_name'] ?? '');

        $materials   = $this->model->getMaterialsForCard($id);
        $attachments = $this->model->getAttachmentsForCard($id);

        $inventoryItems = [];
        if ($this->db->tableExists('inventory_items')) {
            $q = $this->db->table('inventory_items')->orderBy('name');
            if ($this->db->fieldExists('deleted_at', 'inventory_items')) {
                $q->where('deleted_at', null);
            }
            $inventoryItems = $q->get()->getResultArray();
        }

        $totalMaterialCost = 0.0;
        foreach ($materials as $m) {
            $totalMaterialCost += (float) ($m['total_cost'] ?? 0);
        }
        $laborRate = (float) ($this->settings['default_labor_rate'] ?? 0);
        $laborCost = (float) ($jc['labor_hours'] ?? 0) * $laborRate;

        return view('job_cards/view', $this->viewData([
            'title'             => 'Job Card — ' . $jc['jc_number'],
            'jc'                => $jc,
            'materials'         => $materials,
            'attachments'       => $attachments,
            'inventoryItems'    => $inventoryItems,
            'totalMaterialCost' => $totalMaterialCost,
            'laborCost'         => $laborCost,
        ]));
    }

    public function edit(int $id)
    {
        $this->requireRole(['super_admin', 'facility_manager', 'supervisor']);

        $jc = $this->model->getDetail($id);
        if (! $jc) {
            return redirect()->to(base_url('job-cards'))->with('error', 'Job card not found.');
        }
        $this->authorizeJc($jc);

        if (($jc['status'] ?? '') === 'approved') {
            return redirect()->to(base_url('job-cards/' . $id))->with('error', 'Approved job cards cannot be edited.');
        }

        return view('job_cards/edit', $this->viewData([
            'title' => 'Edit ' . $jc['jc_number'],
            'jc'    => $jc,
        ]));
    }

    public function update(int $id)
    {
        if (! $this->request->is('post')) {
            return redirect()->to(base_url('job-cards/' . $id . '/edit'));
        }

        $this->requireRole(['super_admin', 'facility_manager', 'supervisor']);

        $jc = $this->model->getDetail($id);
        if (! $jc) {
            return redirect()->to(base_url('job-cards'))->with('error', 'Job card not found.');
        }
        $this->authorizeJc($jc);

        if (($jc['status'] ?? '') === 'approved') {
            return redirect()->to(base_url('job-cards/' . $id))->with('error', 'Approved job cards cannot be updated.');
        }

        $rules = [
            'description'      => 'permit_empty|min_length[5]',
            'status'           => 'permit_empty|in_list[draft,in_progress,completed]',
            'labor_hours'      => 'permit_empty|decimal',
            'completion_notes' => 'permit_empty|max_length[4000]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [];
        $description = trim((string) $this->request->getPost('description'));
        if ($description !== '') {
            $data['description'] = $description;
        }
        $status = (string) $this->request->getPost('status');
        if (in_array($status, ['draft', 'in_progress', 'completed'], true)) {
            $data['status'] = $status;
        }
        if ($this->request->getPost('labor_hours') !== null && $this->request->getPost('labor_hours') !== '') {
            $hours = (float) $this->request->getPost('labor_hours');
            if ($hours < 0) {
                return redirect()->back()->withInput()->with('error', 'Labor hours cannot be negative.');
            }
            $data['labor_hours'] = $hours;
        }
        if ($this->request->getPost('completion_notes') !== null) {
            $data['completion_notes'] = $this->request->getPost('completion_notes');
        }

        $beforePath = $this->uploadImage('before_image', 'job_cards');
        $afterPath  = $this->uploadImage('after_image', 'job_cards');
        if ($beforePath) {
            $data['before_image'] = $beforePath;
        }
        if ($afterPath) {
            $data['after_image'] = $afterPath;
        }

        if ($data === []) {
            return redirect()->to(base_url('job-cards/' . $id))->with('warning', 'No changes submitted.');
        }

        $this->db->transStart();
        try {
            $this->model->update($id, $data);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return redirect()->back()->withInput()->with('error', 'Could not update job card.');
            }
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Job card update failed: ' . $e->getMessage());

            return redirect()->back()->withInput()->with('error', 'Could not update job card. Please try again.');
        }

        $this->logActivity('update', 'job_cards', $id, 'Job card updated');

        return redirect()->to(base_url('job-cards/' . $id))->with('success', 'Job card updated.');
    }

    public function addMaterial(int $id)
    {
        if (! $this->request->is('post')) {
            return redirect()->to(base_url('job-cards/' . $id));
        }

        $this->requireRole(['super_admin', 'facility_manager', 'supervisor', 'technician']);

        $jc = $this->model->getDetail($id);
        if (! $jc) {
            return redirect()->to(base_url('job-cards'))->with('error', 'Job card not found.');
        }
        $this->authorizeJc($jc);

        $qty    = (float) ($this->request->getPost('quantity') ?? 0);
        $cost   = (float) ($this->request->getPost('unit_cost') ?? 0);
        $itemId = (int) ($this->request->getPost('item_id') ?? 0);
        $name   = trim((string) $this->request->getPost('item_name'));

        if ($qty <= 0) {
            return redirect()->back()->with('error', 'Quantity must be greater than zero.');
        }
        if ($cost < 0) {
            return redirect()->back()->with('error', 'Unit cost cannot be negative.');
        }

        $item = [];
        if ($itemId > 0 && $this->db->tableExists('inventory_items')) {
            $item = $this->db->table('inventory_items')->where('id', $itemId)->get()->getRowArray() ?: [];
            if ($item) {
                $name = $name !== '' ? $name : (string) ($item['name'] ?? 'Material');
                if ($cost <= 0 && isset($item['unit_cost'])) {
                    $cost = (float) $item['unit_cost'];
                }
            }
        }
        if ($name === '') {
            $name = 'Material';
        }

        $row = [
            'jc_id'      => $id,
            'item_id'    => $itemId > 0 ? $itemId : null,
            'item_name'  => $name,
            'quantity'   => $qty,
            'unit_cost'  => $cost,
            'total_cost' => round($qty * $cost, 2),
            'created_at' => date('Y-m-d H:i:s'),
        ];
        if ($this->db->fieldExists('added_by', 'jc_materials')) {
            $row['added_by'] = $this->currentUser()['id'] ?: null;
        }
        if ($this->db->fieldExists('notes', 'jc_materials')) {
            $row['notes'] = $this->request->getPost('notes') ?: $this->request->getPost('mat_notes') ?: null;
        }

        $this->db->transStart();
        try {
            if ($itemId > 0 && $item) {
                $locked = $this->db->table('inventory_items')->where('id', $itemId)->get()->getRowArray();
                $onHand = (float) ($locked['quantity'] ?? 0);
                if ($onHand < $qty) {
                    $this->db->transRollback();

                    return redirect()->back()->with('error', 'Insufficient stock for ' . $name . '.');
                }
                $this->db->query('UPDATE inventory_items SET quantity = quantity - ? WHERE id = ? AND quantity >= ?', [$qty, $itemId, $qty]);
                if ($this->db->affectedRows() < 1) {
                    $this->db->transRollback();

                    return redirect()->back()->with('error', 'Stock was updated by another user. Please retry.');
                }
            }
            $this->db->table('jc_materials')->insert($row);
            $this->db->transComplete();
            if ($this->db->transStatus() === false) {
                return redirect()->back()->with('error', 'Could not add material.');
            }
        } catch (\Throwable $e) {
            $this->db->transRollback();
            log_message('error', 'Job card add material failed: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Could not add material. Please try again.');
        }

        $this->logActivity('material_add', 'job_cards', $id, 'Material added: ' . $name);

        return redirect()->to(base_url('job-cards/' . $id) . '#tab-materials')->with('success', 'Material added.');
    }

    // ----------------------------------------------------------
    // Stage 9 — Start execution (technician)
    // ----------------------------------------------------------

    public function startWork(int $id)
    {
        $this->requireRole(['super_admin', 'facility_manager', 'supervisor', 'technician']);

        $jc = $this->model->find($id);
        $this->authorizeJc($jc);

        $this->model->update($id, ['status' => 'in_progress']);
        $this->woModel->advanceStage($jc['wo_id'], 'work_execution', [
            'status'     => 'in_progress',
            'started_at' => date('Y-m-d H:i:s'),
        ]);

        $this->logActivity('start_work', 'job_cards', $id, 'Work started');

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['ok' => true, 'stage' => 'work_execution']);
        }
        return redirect()->to(base_url('workorders/view/' . $jc['wo_id']) . '#wo-workflow')->with('success', 'Work execution started.');
    }

    // ----------------------------------------------------------
    // Stage 11 — Complete job card (technician)
    // ----------------------------------------------------------

    public function complete(int $id)
    {
        $this->requireRole(['super_admin', 'facility_manager', 'supervisor', 'technician']);

        $jc = $this->model->find($id);
        $this->authorizeJc($jc);

        $rules = [
            'labor_hours'       => 'required|decimal',
            'completion_notes'  => 'required|min_length[5]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->to(base_url('workorders/view/' . ($jc['wo_id'] ?? 0)) . '#wo-actions')
                ->withInput()->with('errors', $this->validator->getErrors())
                ->with('open_modal', 'completeJobCard');
        }

        $post = $this->request->getPost();

        // Handle before/after images
        $beforePath = $this->uploadImage('before_image', 'job_cards');
        $afterPath  = $this->uploadImage('after_image', 'job_cards');

        $updateData = [
            'status'           => 'completed',
            'labor_hours'      => $post['labor_hours'],
            'completion_notes' => $post['completion_notes'],
            'technician_notes' => $post['technician_notes'] ?? null,
            'completed_at'     => date('Y-m-d H:i:s'),
        ];

        if ($beforePath) $updateData['before_image'] = $beforePath;
        if ($afterPath)  $updateData['after_image']  = $afterPath;

        $custSig = (new \App\Services\SignatureStorageService())
            ->storeFromPost($this->request->getPost('customer_signature'), 'jc_' . $id);
        if ($custSig) {
            $updateData['customer_signature'] = $custSig;
        }

        $this->model->update($id, $updateData);

        // Save materials used
        if (! empty($post['materials']) && is_array($post['materials'])) {
            foreach ($post['materials'] as $mat) {
                if (! is_array($mat)) {
                    continue;
                }
                $itemName = trim((string) ($mat['item_name'] ?? ''));
                if ($itemName === '') {
                    continue;
                }
                $qty  = (float) ($mat['quantity'] ?? 1);
                $cost = (float) ($mat['unit_cost'] ?? 0);
                if ($qty <= 0) {
                    $qty = 1;
                }
                $this->db->table('jc_materials')->insert([
                    'jc_id'      => $id,
                    'item_id'    => ! empty($mat['item_id']) ? (int) $mat['item_id'] : null,
                    'item_name'  => $itemName,
                    'quantity'   => $qty,
                    'unit_cost'  => $cost,
                    'total_cost' => $qty * $cost,
                ]);
            }
        }


        // Sync labor/materials to work order + costing for finance
        $costs = (new \App\Services\WoJobCardSyncService($this->db, $this->settings))
            ->syncWorkOrderFromJobCards((int) $jc['wo_id'], $id);

        $woNotes = trim($post['completion_notes']);
        if (! empty($post['technician_notes'])) {
            $woNotes .= "\n\nTechnician: " . trim($post['technician_notes']);
        }

        $this->woModel->advanceStage($jc['wo_id'], 'inspection_qc', [
            'qa_status'               => 'pending',
            'client_approval_status'  => 'none',
            'completion_notes'        => $woNotes,
            'actual_cost'             => $costs['total'],
        ]);

        $this->logActivity('complete', 'job_cards', $id, 'JC ' . $jc['jc_number'] . ' → QC | Labor ' . number_format($costs['labor_total'], 2) . ' + Materials ' . number_format($costs['material_total'], 2));
        $this->logActivity('submit_qc', 'work_orders', (int) $jc['wo_id'], 'Auto-submitted for QC after job card ' . $jc['jc_number']);

        $this->notifyManagers('QC Required', 'Job Card ' . $jc['jc_number'] . ' completed — QA review needed on WO.');

        return redirect()->to(base_url('workorders/view/' . $jc['wo_id']) . '#tab-closure')
            ->with('success', 'Job card completed. Labor & materials synced. Proceed with QA in Closure tab.')
            ->with('open_tab', 'tab-closure');
    }

    // ----------------------------------------------------------
    // Supervisor approves job card
    // ----------------------------------------------------------

    public function approve(int $id)
    {
        $this->requireRole(['super_admin', 'facility_manager', 'supervisor']);

        $this->model->update($id, [
            'status'      => 'approved',
            'approved_by' => $this->currentUser()['id'],
            'approved_at' => date('Y-m-d H:i:s'),
        ]);

        $this->logActivity('approve', 'job_cards', $id, 'Job Card approved by supervisor');
        return redirect()->back()->with('success', 'Job Card approved.');
    }

    // ----------------------------------------------------------
    // Helpers
    // ----------------------------------------------------------

    private function authorizeJc(?array $jc): void
    {
        if (! $jc) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }

        $user = $this->currentUser();
        $role = $user['role_name'];

        $companyId = session()->get('company_id');
        if ($companyId && $role !== 'super_admin' && ! empty($jc['wo_id']) && $this->db->tableExists('work_orders')) {
            $wo = $this->db->table('work_orders wo')
                ->select('wo.id, f.company_id, wo.facility_id')
                ->join('facilities f', 'f.id = wo.facility_id', 'left')
                ->where('wo.id', (int) $jc['wo_id'])
                ->get()->getRowArray();
            if ($wo && ! empty($wo['company_id']) && (int) $wo['company_id'] !== (int) $companyId) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException();
            }
            if (! empty($wo['facility_id'])) {
                $this->assertFacilityAccess((int) $wo['facility_id']);
            }
        }

        if (in_array($role, ['super_admin', 'facility_manager'], true)) {
            return;
        }

        if ($role === 'supervisor' && (int) ($jc['supervisor_id'] ?? 0) !== (int) $user['id']) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }

        if ($role === 'technician' && (int) ($jc['assigned_to'] ?? 0) !== (int) $user['id']) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
    }

    // ----------------------------------------------------------
    // Print / PDF export
    // ----------------------------------------------------------

    /**
     * GET /job-cards/{id}/print
     *
     * Renders a self-contained, print-optimised HTML page.
     * Browser → Print → Save as PDF  works out of the box.
     *
     * Optional query strings:
     *   ?auto=1   trigger the browser print dialog automatically
     *   ?pdf=1    force a server-side PDF download (requires dompdf/dompdf)
     */
    public function printCard(int $id)
    {
        $jc = $this->model->getDetail($id);
        if (! $jc) {
            return redirect()->to('/job-cards')->with('error', 'Job card not found.');
        }

        $this->authorizeJc($jc);

        $materials = $this->model->getMaterialsForCard($id);
        $settings  = $this->settings;

        $data = [
            'jc'            => $jc,
            'materials'     => $materials,
            'companyName'   => $settings['company_name']        ?? 'FM ERP',
            'companyLogo'   => $settings['company_logo']        ?? null,
            'companyAddress'=> $settings['company_address']     ?? null,
            'companyPhone'  => $settings['company_phone']       ?? null,
            'companyEmail'  => $settings['company_email']       ?? null,
            'primaryColor'  => $settings['primary_color']       ?? '#76002b',
            'currency'      => $settings['currency']            ?? 'QAR',
            'laborRate'     => (float) ($settings['default_labor_rate'] ?? 0),
            'autoPrint'     => (bool)  $this->request->getGet('auto'),
        ];

        // Optional server-side PDF via DOMPDF (?pdf=1)
        // Install: composer require dompdf/dompdf
        if ($this->request->getGet('pdf') && class_exists(\Dompdf\Dompdf::class)) {
            return $this->renderDompdf($data);
        }

        // Default: HTML print view (self-contained, no layout wrapper)
        return view('job_cards/print', $data);
    }

    private function renderDompdf(array $data): \CodeIgniter\HTTP\ResponseInterface
    {
        $html = view('job_cards/print', $data);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled',      true);
        $options->set('defaultFont',          'Arial');
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'JobCard_' . $data['jc']['jc_number'] . '_' . date('Ymd') . '.pdf';

        return $this->response
                    ->setHeader('Content-Type',        'application/pdf')
                    ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                    ->setBody($dompdf->output());
    }


    // ----------------------------------------------------------

    private function uploadImage(string $field, string $folder): ?string
    {
        $file = $this->request->getFile($field);
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            $name = $file->getRandomName();
            $file->move(ROOTPATH . 'public/uploads/' . $folder, $name);
            return 'uploads/' . $folder . '/' . $name;
        }
        return null;
    }
}
