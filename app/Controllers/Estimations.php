<?php
namespace App\Controllers;

use App\Services\EstimationService;
use App\Services\FinancialVisibilityService;

class Estimations extends BaseController
{
    protected ?string $workspaceRequired = 'fm';
    private EstimationService $estimationService;
    private FinancialVisibilityService $visibility;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->estimationService = new EstimationService($this->db);
        $this->visibility        = new FinancialVisibilityService();
    }

    public function index()
    {
        $pg          = $this->paginate(20);
        $currentPage = $pg['page'];
        $perPage     = $pg['perPage'];
        $offset      = $pg['offset'];

        $q = $this->db->table('estimations e')
            ->select('e.*, f.name as facility_name, u.name as created_by_name')
            ->join('facilities f','f.id=e.facility_id','left')
            ->join('users u','u.id=e.created_by','left');

        $status = $this->request->getGet('status') ?? '';
        if ($status) $q->where('e.status',$status);
        $total = (clone $q)->countAllResults(false);
        $ests  = $q->orderBy('e.created_at','DESC')->limit($perPage,$offset)->get()->getResultArray();

        $kpi = [
            'draft'    => $this->db->table('estimations')->where('status','draft')->countAllResults(),
            'pending'  => $this->db->table('estimations')->where('status','pending_approval')->countAllResults(),
            'approved' => $this->db->table('estimations')->where('status','approved')->countAllResults(),
            'converted'=> $this->db->table('estimations')->where('status','converted')->countAllResults(),
        ];

        return view('estimations/index', $this->viewData([
            'title'=>'Estimations','ests'=>$ests,'kpi'=>$kpi,
            'totalCount'=>$total,'perPage'=>$perPage,'currentPage'=>$currentPage,'filterStatus'=>$status,
            'canViewInternal'=>$this->canViewInternalFinancials(),
        ]));
    }

    public function create()
    {
        $facilities = $this->db->table('facilities')->where('status','active')->get()->getResultArray();
        $workOrders = $this->db->table('work_orders')->whereIn('status',['new','assigned','in_progress'])->orderBy('created_at','DESC')->limit(50)->get()->getResultArray();
        $vatEnabled = ($this->settings['vat_enabled']??'0')==='1';
        $vatRate    = (float)($this->settings['vat_rate']??5);
        return view('estimations/create', $this->viewData([
            'title'=>'New Estimation','facilities'=>$facilities,'workOrders'=>$workOrders,
            'vatEnabled'=>$vatEnabled,'vatRate'=>$vatRate,
            'canViewInternal'=>$this->canViewInternalFinancials(),
        ]));
    }

    public function store()
    {
        $rules = ['facility_id'=>'required|integer','title'=>'required|max_length[255]'];
        if (!$this->validate($rules)) return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());

        $vatEnabled = ($this->settings['vat_enabled']??'0')==='1';
        $vatRate    = $vatEnabled ? (float)($this->settings['vat_rate']??5) : 0;
        $estNum     = $this->generateNumber('EST','estimations','est_number');

        $header = [
            'est_number'  => $estNum,
            'facility_id' => (int)$this->request->getPost('facility_id'),
            'wo_id'       => $this->request->getPost('wo_id') ?: null,
            'title'       => esc($this->request->getPost('title')),
            'description' => esc($this->request->getPost('description')??''),
            'status'      => 'draft',
            'notes'       => esc($this->request->getPost('notes')??''),
            'created_by'  => session()->get('user_id'),
            'actual_labor_cost'     => (float)($this->request->getPost('actual_labor_cost') ?? 0),
            'actual_material_cost'  => (float)($this->request->getPost('actual_material_cost') ?? 0),
            'actual_transport_cost' => (float)($this->request->getPost('actual_transport_cost') ?? 0),
            'actual_equipment_cost' => (float)($this->request->getPost('actual_equipment_cost') ?? 0),
            'actual_misc_cost'      => (float)($this->request->getPost('actual_misc_cost') ?? 0),
            'actual_other_cost'     => (float)($this->request->getPost('actual_other_cost') ?? 0),
        ];

        $payload = $this->estimationService->buildPayload($header, $this->parseLineItemsFromPost(), $vatEnabled, $vatRate);
        $estId   = $this->estimationService->saveEstimation($payload['header']);
        $this->estimationService->saveItems($estId, $payload['items']);

        $this->logActivity('create','estimations',$estId,"Created $estNum");
        return redirect()->to(base_url('estimations/view/'.$estId))->with('success',"Estimation $estNum created.");
    }

    public function view(int $id)
    {
        $est = $this->db->table('estimations e')
            ->select('e.*, f.name as facility_name, u.name as created_by_name, ap.name as approved_by_name, wo.wo_number')
            ->join('facilities f','f.id=e.facility_id','left')
            ->join('users u','u.id=e.created_by','left')
            ->join('users ap','ap.id=e.approved_by','left')
            ->join('work_orders wo','wo.id=e.wo_id','left')
            ->where('e.id',$id)->get()->getRowArray();
        if (!$est) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $canViewInternal = $this->canViewInternalFinancials();
        $items           = $this->estimationService->getItems($id);
        if (!$canViewInternal) {
            $items = $this->visibility->filterEstimationItems($items, session()->get('user_role'));
        }

        $linkedWo      = null;
        $linkedInvoice = null;
        $invoices      = [];
        if ($est['wo_id']) {
            $linkedWo = $this->db->table('work_orders')->where('id', $est['wo_id'])->get()->getRowArray();
            if ($linkedWo) {
                $invoices = $this->db->table('invoices')
                    ->where('work_order_id', $linkedWo['id'])
                    ->where('deleted_at', null)
                    ->orderBy('created_at','DESC')
                    ->get()->getResultArray();
                $linkedInvoice = $invoices[0] ?? null;
            }
        }

        return view('estimations/view', $this->viewData([
            'title'           => $est['est_number'],
            'est'             => $est,
            'items'           => $items,
            'linkedWo'        => $linkedWo,
            'linkedInvoice'   => $linkedInvoice,
            'woInvoices'      => $invoices,
            'canViewInternal' => $canViewInternal,
        ]));
    }

    public function edit(int $id)
    {
        $est = $this->db->table('estimations')->where('id',$id)->get()->getRowArray();
        if (!$est || in_array($est['status'],['approved','converted'])) return redirect()->back()->with('error','Cannot edit this estimation.');
        $items      = $this->estimationService->getItems($id);
        $facilities = $this->db->table('facilities')->where('status','active')->get()->getResultArray();
        $workOrders = $this->db->table('work_orders')->whereIn('status',['new','assigned','in_progress'])->get()->getResultArray();
        $vatEnabled = ($this->settings['vat_enabled']??'0')==='1';
        $vatRate    = (float)($this->settings['vat_rate']??5);
        return view('estimations/edit', $this->viewData([
            'title'=>'Edit '.$est['est_number'],'est'=>$est,'items'=>$items,
            'facilities'=>$facilities,'workOrders'=>$workOrders,'vatEnabled'=>$vatEnabled,'vatRate'=>$vatRate,
            'canViewInternal'=>$this->canViewInternalFinancials(),
        ]));
    }

    public function update(int $id)
    {
        $est = $this->db->table('estimations')->where('id',$id)->get()->getRowArray();
        if (!$est) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $vatEnabled = ($this->settings['vat_enabled']??'0')==='1';
        $vatRate    = $vatEnabled ? (float)($this->settings['vat_rate']??5) : 0;

        $header = [
            'facility_id' => (int)$this->request->getPost('facility_id'),
            'wo_id'       => $this->request->getPost('wo_id') ?: null,
            'title'       => esc($this->request->getPost('title')),
            'description' => esc($this->request->getPost('description')??''),
            'revision'    => $est['revision'] + 1,
            'notes'       => esc($this->request->getPost('notes')??''),
            'actual_labor_cost'     => (float)($this->request->getPost('actual_labor_cost') ?? 0),
            'actual_material_cost'  => (float)($this->request->getPost('actual_material_cost') ?? 0),
            'actual_transport_cost' => (float)($this->request->getPost('actual_transport_cost') ?? 0),
            'actual_equipment_cost' => (float)($this->request->getPost('actual_equipment_cost') ?? 0),
            'actual_misc_cost'      => (float)($this->request->getPost('actual_misc_cost') ?? 0),
            'actual_other_cost'     => (float)($this->request->getPost('actual_other_cost') ?? 0),
        ];

        $payload = $this->estimationService->buildPayload($header, $this->parseLineItemsFromPost(), $vatEnabled, $vatRate);
        $this->estimationService->saveEstimation($payload['header'], $id);
        $this->estimationService->saveItems($id, $payload['items']);

        $this->logActivity('update','estimations',$id);
        return redirect()->to(base_url('estimations/view/'.$id))->with('success','Estimation updated.');
    }

    public function approve(int $id)
    {
        $this->requireRole('super_admin','facility_manager','finance_manager');
        $this->db->table('estimations')->where('id',$id)->update([
            'status'=>'approved','approved_by'=>session()->get('user_id'),'approved_at'=>date('Y-m-d H:i:s'),
        ]);
        $this->logActivity('approve','estimations',$id);
        return redirect()->to(base_url('estimations/view/'.$id))->with('success','Estimation approved.');
    }

    public function submitForApproval(int $id)
    {
        $est = $this->db->table('estimations')->where('id',$id)->get()->getRowArray();
        if (!$est || $est['status'] !== 'draft') {
            return redirect()->back()->with('error','Only draft estimations can be submitted.');
        }
        $this->db->table('estimations')->where('id',$id)->update(['status'=>'pending_approval']);
        $this->logActivity('submit','estimations',$id,'Submitted for approval');
        return redirect()->to(base_url('estimations/view/'.$id))->with('success','Estimation submitted for approval.');
    }

    public function convertToWorkOrder(int $id)
    {
        $this->requireRole('super_admin','facility_manager');
        $est = $this->db->table('estimations')->where('id',$id)->get()->getRowArray();
        if (!$est || $est['status']!=='approved') return redirect()->back()->with('error','Only approved estimations can be converted.');

        $woNum = $this->generateNumber('WO','work_orders','wo_number');
        $woData = [
            'wo_number'       => $woNum,
            'facility_id'     => $est['facility_id'],
            'title'           => $est['title'],
            'description'     => $est['description'],
            'type'            => 'corrective',
            'priority'        => 'medium',
            'status'          => 'new',
            'estimated_cost'  => $est['estimated_subtotal'] ?? $est['subtotal'],
            'selling_total'   => $est['selling_subtotal'] ?? $est['subtotal'],
            'estimation_id'   => $id,
            'approval_status' => 'approved',
            'created_by'      => session()->get('user_id'),
        ];
        if ($this->db->fieldExists('actual_total_cost', 'work_orders')) {
            $woData['actual_total_cost'] = $est['actual_total_cost'] ?? $est['actual_total'] ?? 0;
        }
        $this->db->table('work_orders')->insert($woData);
        $woId = $this->db->insertID();
        $this->db->table('estimations')->where('id',$id)->update(['status'=>'converted','wo_id'=>$woId]);
        $this->logActivity('convert','estimations',$id,"Converted to WO $woNum");
        return redirect()->to(base_url('workorders/view/'.$woId))->with('success',"Estimation converted to Work Order $woNum.");
    }

    public function printView(int $id)
    {
        $est = $this->db->table('estimations e')
            ->select('e.*, f.name as facility_name, u.name as created_by_name')
            ->join('facilities f','f.id=e.facility_id','left')
            ->join('users u','u.id=e.created_by','left')
            ->where('e.id',$id)->get()->getRowArray();
        if (!$est) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $role  = session()->get('user_role');
        $items = $this->visibility->filterEstimationItems(
            $this->estimationService->getItems($id),
            $this->canViewInternalFinancials() ? $role : 'client'
        );

        return view('estimations/print', $this->viewData([
            'title'=>'Print: '.$est['est_number'],
            'est'=>$est,
            'items'=>$items,
            'usePdf'=>true,
            'clientView'=>!$this->canViewInternalFinancials(),
        ]));
    }

    /** @return list<array<string, mixed>> */
    private function parseLineItemsFromPost(): array
    {
        $names   = $this->request->getPost('item_name')   ?? [];
        $descs   = $this->request->getPost('item_desc')   ?? [];
        $types   = $this->request->getPost('item_type')   ?? [];
        $qtys    = $this->request->getPost('item_qty')    ?? [];
        $units   = $this->request->getPost('item_unit')   ?? [];
        $prices  = $this->request->getPost('item_price')  ?? [];
        $estCosts= $this->request->getPost('item_est_cost') ?? [];
        $actCosts= $this->request->getPost('item_act_cost') ?? [];

        $rows = [];
        foreach ($names as $i => $name) {
            $rows[] = [
                'item_name'           => $name,
                'description'         => $descs[$i] ?? $name,
                'type'                => $types[$i] ?? 'material',
                'quantity'            => $qtys[$i] ?? 1,
                'unit'                => $units[$i] ?? 'unit',
                'unit_price'          => $prices[$i] ?? 0,
                'estimated_unit_cost' => $estCosts[$i] ?? 0,
                'actual_unit_cost'    => $actCosts[$i] ?? 0,
            ];
        }

        return $rows;
    }

    private function canViewInternalFinancials(): bool
    {
        return $this->visibility->canViewInternalFinancialsForUser($this->currentUser());
    }
}
