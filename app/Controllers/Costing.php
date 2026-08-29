<?php
namespace App\Controllers;

class Costing extends BaseController
{
    protected ?string $workspaceRequired = 'fm';
    public function index()
    {
        $pg = $this->paginate(25);
        $q  = $this->db->table('maintenance_costing mc')
            ->select('mc.*, w.wo_number, w.title as wo_title, f.name as facility_name')
            ->join('work_orders w', 'w.id=mc.wo_id', 'left')
            ->join('facilities f', 'f.id=w.facility_id', 'left');
        $this->scopeFacilities($q, 'w.facility_id');
        $total    = (clone $q)->countAllResults(false);
        $costings = $q->orderBy('mc.created_at', 'DESC')->limit($pg['perPage'], $pg['offset'])->get()->getResultArray();

        $totals = [
            'labor'  => array_sum(array_column($costings,'labor_cost')),
            'parts'  => array_sum(array_column($costings,'parts_cost')),
            'vendor' => array_sum(array_column($costings,'vendor_cost')),
            'emergency'=> array_sum(array_column($costings,'emergency_surcharge')),
        ];

        return view('costing/index', $this->viewData([
            'title'       => 'Maintenance Costing',
            'costings'    => $costings,
            'totals'      => $totals,
            'totalCount'  => $total,
            'perPage'     => $pg['perPage'],
            'currentPage' => $pg['page'],
        ]));
    }

    public function create()
    {
        $workOrders = $this->db->table('work_orders')->whereIn('status',['in_progress','completed'])->orderBy('wo_number','DESC')->get()->getResultArray();
        $items = $this->db->table('inventory_items')->get()->getResultArray();
        return view('costing/create', $this->viewData(['title'=>'Log Maintenance Cost','workOrders'=>$workOrders,'items'=>$items]));
    }

    public function store()
    {
        $rules = ['wo_id'=>'required'];
        if (!$this->validate($rules)) return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());

        $laborCost     = (float)$this->request->getPost('labor_cost');
        $partsCost     = (float)$this->request->getPost('parts_cost');
        $vendorCost    = (float)$this->request->getPost('vendor_cost');
        $surcharge     = (float)$this->request->getPost('emergency_surcharge');
        $totalCost     = $laborCost + $partsCost + $vendorCost + $surcharge;

        $woId = (int)$this->request->getPost('wo_id');
        $estimate = (float)$this->request->getPost('cost_estimate');

        $this->db->table('maintenance_costing')->insert([
            'wo_id'               => $woId,
            'labor_cost'          => $laborCost,
            'labor_hours'         => (float)$this->request->getPost('labor_hours'),
            'parts_cost'          => $partsCost,
            'vendor_cost'         => $vendorCost,
            'emergency_surcharge' => $surcharge,
            'cost_estimate'       => $estimate,
            'total_cost'          => $totalCost,
            'job_profit'          => $estimate - $totalCost,
            'notes'               => esc($this->request->getPost('notes')),
            'created_by'          => session()->get('user_id'),
        ]);

        // Update work order actual cost
        $this->db->table('work_orders')->where('id',$woId)->update(['actual_cost'=>$totalCost]);

        return redirect()->to(base_url('costing'))->with('success','Costing record saved.');
    }

    public function view(int $id)
    {
        $costing = $this->db->table('maintenance_costing mc')
            ->select('mc.*, w.wo_number, w.title as wo_title, w.type, f.name as facility_name, u.name as created_by_name')
            ->join('work_orders w','w.id=mc.wo_id','left')
            ->join('facilities f','f.id=w.facility_id','left')
            ->join('users u','u.id=mc.created_by','left')
            ->where('mc.id',$id)->get()->getRowArray();

        if (!$costing) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        return view('costing/view', $this->viewData(['title'=>'Costing — '.$costing['wo_number'],'costing'=>$costing]));
    }
}
