<?php
namespace App\Controllers;

class Vendors extends BaseController
{
    protected ?string $workspaceRequired = 'fm';
    public function index()
    {
        $search = $this->request->getGet('search') ?? '';
        $status = $this->request->getGet('status') ?? '';

        $q = $this->db->table('vendors v');
        if ($this->db->fieldExists('company_id', 'vendors')) {
            $this->scopeCompany($q, 'v.company_id');
        }
        $q->select('v.*,
                COUNT(DISTINCT po.id) AS total_orders,
                SUM(CASE WHEN po.status IN (\'approved\',\'delivered\') THEN po.total_amount ELSE 0 END) AS total_spend')
            ->join('purchase_orders po','po.vendor_id=v.id','left')
            ->groupBy('v.id');

        if ($search) $q->groupStart()->like('v.name', $search)->orLike('v.email', $search)->groupEnd();
        if ($status) $q->where('v.status', $status);

        $pg          = $this->paginate(20);
        $perPage     = $pg['perPage'];
        $currentPage = $pg['page'];
        $offset      = $pg['offset'];
        $total       = $this->db->table('vendors')->countAllResults();
        $vendors     = $q->orderBy('v.name', 'ASC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        $kpi = [
            'total_active'   => $this->db->table('vendors')->where('status','active')->countAllResults(),
            'total_inactive' => $this->db->table('vendors')->where('status','inactive')->countAllResults(),
            'total_spend'    => (float)($this->db->table('purchase_orders')->whereIn('status',['approved','delivered'])->selectSum('total_amount','t')->get()->getRowArray()['t'] ?? 0),
        ];

        return view('vendors/index', $this->viewData([
            'title'       => 'Vendors',
            'vendors'     => $vendors,
            'kpi'         => $kpi,
            'totalCount'  => $total,
            'perPage'     => $perPage,
            'currentPage' => $currentPage,
            'search'      => $search,
            'filterStatus'=> $status,
        ]));
    }

    public function create()
    {
        $this->requireRole('super_admin', 'facility_manager', 'procurement_officer');
        return view('vendors/create', $this->viewData(['title' => 'Add Vendor']));
    }

    public function store()
    {
        $this->requireRole('super_admin', 'facility_manager', 'procurement_officer');
        $rules = [
            'name'  => 'required|min_length[2]|max_length[200]',
            'email' => 'permit_empty|valid_email',
            'phone' => 'permit_empty|max_length[30]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->db->table('vendors')->insert([
            'name'           => esc($this->request->getPost('name')),
            'contact_person' => esc($this->request->getPost('contact_person') ?? ''),
            'phone'          => esc($this->request->getPost('phone') ?? ''),
            'email'          => $this->request->getPost('email') ?? '',
            'address'        => esc($this->request->getPost('address') ?? ''),
            'vat_number'     => esc($this->request->getPost('vat_number') ?? ''),
            'category'       => $this->request->getPost('category') ?? 'general',
            'rating'         => (int)($this->request->getPost('rating') ?? 3),
            'status'         => 'active',
            'notes'          => esc($this->request->getPost('notes') ?? ''),
            'created_by'     => session()->get('user_id'),
        ]);
        $this->logActivity('create', 'vendors', $this->db->insertID());
        return redirect()->to(base_url('vendors'))->with('success', 'Vendor added successfully.');
    }

    public function view(int $id)
    {
        $vendor = $this->db->table('vendors')->where('id', $id)->get()->getRowArray();
        if (!$vendor) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $orders = $this->db->table('purchase_orders')
            ->where('vendor_id', $id)->orderBy('created_at','DESC')->limit(10)->get()->getResultArray();

        $quotations = $this->db->table('rfq_quotations rq')
            ->select('rq.*, r.rfq_number, r.title')
            ->join('rfq r','r.id=rq.rfq_id','left')
            ->where('rq.vendor_id', $id)->orderBy('rq.created_at','DESC')->get()->getResultArray();

        $totalSpend = array_sum(array_map(
            fn($o) => in_array($o['status'],['approved','delivered']) ? (float)$o['total_amount'] : 0,
            $orders
        ));

        return view('vendors/view', $this->viewData([
            'title'      => $vendor['name'],
            'vendor'     => $vendor,
            'orders'     => $orders,
            'quotations' => $quotations,
            'totalSpend' => $totalSpend,
        ]));
    }

    // FIX ROUTE-04: edit was missing
    public function edit(int $id)
    {
        $this->requireRole('super_admin', 'facility_manager', 'procurement_officer');
        $vendor = $this->db->table('vendors')->where('id', $id)->get()->getRowArray();
        if (!$vendor) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        return view('vendors/edit', $this->viewData(['title' => 'Edit Vendor', 'vendor' => $vendor]));
    }

    // FIX ROUTE-04: update was missing
    public function update(int $id)
    {
        $this->requireRole('super_admin', 'facility_manager', 'procurement_officer');
        $rules = [
            'name'  => 'required|min_length[2]|max_length[200]',
            'email' => 'permit_empty|valid_email',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->db->table('vendors')->where('id', $id)->update([
            'name'           => esc($this->request->getPost('name')),
            'contact_person' => esc($this->request->getPost('contact_person') ?? ''),
            'phone'          => esc($this->request->getPost('phone') ?? ''),
            'email'          => $this->request->getPost('email') ?? '',
            'address'        => esc($this->request->getPost('address') ?? ''),
            'vat_number'     => esc($this->request->getPost('vat_number') ?? ''),
            'category'       => $this->request->getPost('category') ?? 'general',
            'rating'         => (int)($this->request->getPost('rating') ?? 3),
            'status'         => $this->request->getPost('status') ?? 'active',
            'notes'          => esc($this->request->getPost('notes') ?? ''),
        ]);
        $this->logActivity('update', 'vendors', $id);
        return redirect()->to(base_url('vendors/view/' . $id))->with('success', 'Vendor updated.');
    }

    // FIX ROUTE-04: delete was missing
    public function delete(int $id)
    {
        $this->requireRole('super_admin', 'facility_manager');
        // Check for open orders
        $openOrders = $this->db->table('purchase_orders')
            ->where('vendor_id', $id)->whereIn('status',['pending','approved'])->countAllResults();
        if ($openOrders > 0) {
            return redirect()->back()->with('error', "Cannot deactivate vendor with $openOrders open purchase order(s). Close orders first.");
        }
        $this->db->table('vendors')->where('id', $id)->update([
            'status'     => 'inactive',
            'deleted_at' => date('Y-m-d H:i:s'),
        ]);
        $this->logActivity('delete', 'vendors', $id);
        return redirect()->to(base_url('vendors'))->with('success', 'Vendor deactivated.');
    }

    public function performance(int $id)
    {
        $vendor = $this->db->table('vendors')->where('id', $id)->get()->getRowArray();
        if (!$vendor) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $perf = $this->db->query("
            SELECT
              COUNT(po.id) AS total_orders,
              SUM(po.total_amount) AS total_value,
              AVG(rq.total_amount) AS avg_quote,
              COUNT(rq.id) AS total_quotes,
              SUM(CASE WHEN rq.is_selected=1 THEN 1 ELSE 0 END) AS won_quotes
            FROM vendors v
            LEFT JOIN purchase_orders po ON po.vendor_id=v.id
            LEFT JOIN rfq_quotations rq ON rq.vendor_id=v.id
            WHERE v.id = ?
        ", [$id])->getRowArray();

        return view('vendors/performance', $this->viewData([
            'title'  => $vendor['name'] . ' — Performance',
            'vendor' => $vendor,
            'perf'   => $perf,
        ]));
    }
}
