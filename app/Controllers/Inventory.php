<?php
namespace App\Controllers;

class Inventory extends BaseController
{
    protected ?string $workspaceRequired = 'fm';
    public function index()
    {
        $search   = $this->request->getGet('search')   ?? '';
        $category = $this->request->getGet('category') ?? '';
        $filter   = $this->request->getGet('filter')   ?? '';

        $q = $this->db->table('inventory_items');
        if ($this->db->fieldExists('company_id', 'inventory_items')) {
            $this->scopeCompany($q, 'company_id');
        }
        if ($search)   $q->groupStart()->like('name',$search)->orLike('item_code',$search)->groupEnd();
        if ($category) $q->where('category',$category);
        if ($filter === 'low')  $q->where('quantity <= min_quantity', null, false)->where('quantity >',0);
        if ($filter === 'zero') $q->where('quantity',0);

        $pg          = $this->paginate(25);
        $perPage     = $pg['perPage'];
        $currentPage = $pg['page'];
        $offset      = $pg['offset'];
        $total       = (clone $q)->countAllResults(false);
        $items       = $q->orderBy('quantity','ASC')->orderBy('name','ASC')->limit($perPage,$offset)->get()->getResultArray();

        $categories = $this->db->table('inventory_items')->select('DISTINCT category')->orderBy('category','ASC')->get()->getResultArray();
        $lowStock   = $this->db->table('inventory_items')->where('quantity <= min_quantity', null, false)->where('quantity >',0)->countAllResults();
        $zeroStock  = $this->db->table('inventory_items')->where('quantity',0)->countAllResults();
        $totalValue = (float)($this->db->query("SELECT SUM(quantity * unit_cost) t FROM inventory_items")->getRowArray()['t'] ?? 0);

        return view('inventory/index', $this->viewData([
            'title'       => 'Inventory',
            'items'       => $items,
            'search'      => $search,
            'category'    => $category,
            'filter'      => $filter,
            'categories'  => array_column($categories,'category'),
            'lowStock'    => $lowStock,
            'zeroStock'   => $zeroStock,
            'totalValue'  => $totalValue,
            'totalCount'  => $total,
            'perPage'     => $perPage,
            'currentPage' => $currentPage,
        ]));
    }

    public function create()
    {
        return view('inventory/create', $this->viewData(['title'=>'Add Inventory Item']));
    }

    public function store()
    {
        $rules = [
            'item_code'    => 'required|max_length[50]|is_unique[inventory_items.item_code]',
            'name'         => 'required|max_length[200]',
            'quantity'     => 'required|is_natural',
            'min_quantity' => 'required|is_natural',
            'unit_cost'    => 'required|numeric|greater_than_equal_to[0]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());
        }
        $this->db->table('inventory_items')->insert([
            'item_code'    => strtoupper(esc($this->request->getPost('item_code'))),
            'name'         => esc($this->request->getPost('name')),
            'category'     => esc($this->request->getPost('category') ?? 'general'),
            'description'  => esc($this->request->getPost('description') ?? ''),
            'unit'         => $this->request->getPost('unit') ?: 'pcs',
            'quantity'     => (int)$this->request->getPost('quantity'),
            'min_quantity' => (int)$this->request->getPost('min_quantity'),
            'unit_cost'    => (float)$this->request->getPost('unit_cost'),
            'location'     => esc($this->request->getPost('location') ?? ''),
            'supplier'     => esc($this->request->getPost('supplier') ?? ''),
            'created_by'   => session()->get('user_id'),
        ]);
        $id = $this->db->insertID();
        // Record initial stock-in movement
        if ((int)$this->request->getPost('quantity') > 0) {
            $this->db->table('stock_movements')->insert([
                'item_id'       => $id,
                'movement_type' => 'in',
                'quantity'      => (int)$this->request->getPost('quantity'),
                'reference'     => 'INITIAL',
                'notes'         => 'Initial stock entry',
                'created_by'    => session()->get('user_id'),
            ]);
        }
        $this->logActivity('create','inventory',$id);
        return redirect()->to(base_url('inventory'))->with('success','Item added to inventory.');
    }

    public function view(int $id)
    {
        $item = $this->db->table('inventory_items')->where('id',$id)->get()->getRowArray();
        if (!$item) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $movements = $this->db->table('stock_movements m')
            ->select('m.*, u.name as created_by_name')
            ->join('users u','u.id=m.created_by','left')
            ->where('m.item_id',$id)
            ->orderBy('m.created_at','DESC')->limit(20)->get()->getResultArray();

        // Running balance (most recent first)
        $balance = $item['quantity'];
        foreach ($movements as &$m) {
            $m['balance_after'] = $balance;
            $balance -= match($m['movement_type']) {
                'in'         => $m['quantity'],
                'out'        => -$m['quantity'],
                'adjustment' => 0,
                default      => 0,
            };
        }
        unset($m);

        return view('inventory/view', $this->viewData([
            'title'     => $item['name'],
            'item'      => $item,
            'movements' => $movements,
        ]));
    }

    public function edit(int $id)
    {
        $item = $this->db->table('inventory_items')->where('id',$id)->get()->getRowArray();
        if (!$item) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        return view('inventory/edit', $this->viewData(['title'=>'Edit Item','item'=>$item]));
    }

    public function update(int $id)
    {
        $item = $this->db->table('inventory_items')->where('id',$id)->get()->getRowArray();
        if (!$item) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $rules = [
            'name'         => 'required|max_length[200]',
            'min_quantity' => 'required|is_natural',
            'unit_cost'    => 'required|numeric|greater_than_equal_to[0]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());
        }

        $this->db->table('inventory_items')->where('id',$id)->update([
            'name'         => esc($this->request->getPost('name')),
            'category'     => esc($this->request->getPost('category') ?? 'general'),
            'description'  => esc($this->request->getPost('description') ?? ''),
            'unit'         => $this->request->getPost('unit'),
            'min_quantity' => (int)$this->request->getPost('min_quantity'),
            'unit_cost'    => (float)$this->request->getPost('unit_cost'),
            'location'     => esc($this->request->getPost('location') ?? ''),
            'supplier'     => esc($this->request->getPost('supplier') ?? ''),
        ]);
        $this->logActivity('update','inventory',$id);
        return redirect()->to(base_url('inventory'))->with('success','Item updated.');
    }

    public function delete(int $id)
    {
        $this->requireRole('super_admin','facility_manager');
        // Safety check — don't delete if used in WO materials
        $inUse = $this->db->table('wo_materials')->where('item_id',$id)->countAllResults()
               + $this->db->table('jc_materials')->where('item_id',$id)->countAllResults();
        if ($inUse > 0) {
            return redirect()->back()->with('error',"Cannot delete — item is referenced in $inUse work order / job card record(s).");
        }
        $this->db->table('inventory_items')->where('id',$id)->update(['deleted_at'=>date('Y-m-d H:i:s')]);
        $this->db->table('stock_movements')->where('item_id',$id)->delete();
        $this->logActivity('delete','inventory',$id);
        return redirect()->to(base_url('inventory'))->with('success','Item removed from inventory.');
    }

    public function movement()
    {
        $pg          = $this->paginate(30);
        $currentPage = $pg['page'];
        $perPage     = $pg['perPage'];
        $offset      = $pg['offset'];

        $items     = $this->db->table('inventory_items')->where('quantity >',0)->orderBy('name','ASC')->get()->getResultArray();
        $movements = $this->db->table('stock_movements m')
            ->select('m.*, i.name as item_name, i.unit, u.name as created_by_name')
            ->join('inventory_items i','i.id=m.item_id','left')
            ->join('users u','u.id=m.created_by','left')
            ->orderBy('m.created_at','DESC')
            ->limit($perPage,$offset)->get()->getResultArray();

        $total = $this->db->table('stock_movements')->countAllResults();

        return view('inventory/movement', $this->viewData([
            'title'       => 'Stock Movements',
            'items'       => $items,
            'movements'   => $movements,
            'totalCount'  => $total,
            'perPage'     => $perPage,
            'currentPage' => $currentPage,
        ]));
    }

    public function addMovement()
    {
        $rules = [
            'item_id'       => 'required|integer',
            'movement_type' => 'required|in_list[in,out,adjustment]',
            'quantity'      => 'required|is_natural_no_zero',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors',$this->validator->getErrors());
        }

        $itemId = (int)$this->request->getPost('item_id');
        $type   = $this->request->getPost('movement_type');
        $qty    = (int)$this->request->getPost('quantity');

        $item = $this->db->table('inventory_items')->where('id',$itemId)->get()->getRowArray();
        if (!$item) return redirect()->back()->with('error','Item not found.');

        // FIX BUG-07: Hard guard — never allow negative stock
        if ($type === 'out' && $item['quantity'] < $qty) {
            return redirect()->back()->with('error',
                "Insufficient stock. Available: {$item['quantity']} {$item['unit']}. Requested: $qty.");
        }

        $newQty = match($type) {
            'in'         => $item['quantity'] + $qty,
            'out'        => $item['quantity'] - $qty,
            'adjustment' => $qty,
            default      => $item['quantity'],
        };

        $this->db->table('inventory_items')->where('id',$itemId)->update(['quantity'=>$newQty]);
        $this->db->table('stock_movements')->insert([
            'item_id'       => $itemId,
            'movement_type' => $type,
            'quantity'      => $qty,
            'reference'     => esc($this->request->getPost('reference') ?? ''),
            'notes'         => esc($this->request->getPost('notes') ?? ''),
            'created_by'    => session()->get('user_id'),
        ]);

        $this->logActivity('movement','inventory',$itemId,"$type $qty {$item['unit']} of {$item['name']}");
        return redirect()->to(base_url('inventory/movement'))->with('success','Stock movement recorded. New balance: '.$newQty.' '.$item['unit'].'.');
    }

    /** Route alias — maps /assets/(:num) etc. to view(). */
    public function show(int $id)
    {
        return $this->view($id);
    }
}
