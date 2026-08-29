<?php
namespace App\Controllers;

class Procurement extends BaseController
{
    protected ?string $workspaceRequired = 'fm';
    private function countScopedPurchaseRequests(string $status): int
    {
        $q = $this->db->table('purchase_requests pr')
            ->join('inventory_items i', 'i.id=pr.item_id', 'left');
        if ($this->db->fieldExists('company_id', 'inventory_items')) {
            $this->scopeCompany($q, 'i.company_id');
        }
        $q->where('pr.status', $status);

        return $q->countAllResults();
    }

    /** @param list<string> $statuses */
    private function countScopedPurchaseOrders(array $statuses): int
    {
        $q = $this->db->table('purchase_orders po')
            ->join('vendors v', 'v.id=po.vendor_id', 'left')
            ->whereIn('po.status', $statuses);
        if ($this->db->fieldExists('company_id', 'purchase_orders')) {
            $this->scopeCompany($q, 'po.company_id');
        } elseif ($this->db->fieldExists('company_id', 'vendors')) {
            $this->scopeCompany($q, 'v.company_id');
        }

        return $q->countAllResults();
    }

    public function workflowHub()
    {
        $this->requireRole('super_admin','facility_manager','procurement_officer','finance_manager');
        return view('procurement/workflow_hub', $this->viewData(['title' => 'Procurement Workflow']));
    }

    public function index()
    {
        $reqQ = $this->db->table('purchase_requests pr')
            ->select('pr.*, u.name as requested_by_name, i.name as item_name')
            ->join('users u',          'u.id=pr.requested_by',  'left')
            ->join('inventory_items i','i.id=pr.item_id',        'left');
        if ($this->db->fieldExists('company_id', 'inventory_items')) {
            $this->scopeCompany($reqQ, 'i.company_id');
        }
        $pg       = $this->paginate(25);
        $total    = (clone $reqQ)->countAllResults(false);
        $requests = $reqQ->orderBy('pr.created_at', 'DESC')->limit($pg['perPage'], $pg['offset'])->get()->getResultArray();

        $ordQ = $this->db->table('purchase_orders po')
            ->select('po.*, v.name as vendor_name, u.name as created_by_name')
            ->join('vendors v','v.id=po.vendor_id', 'left')
            ->join('users u',  'u.id=po.created_by','left');
        if ($this->db->fieldExists('company_id', 'purchase_orders')) {
            $this->scopeCompany($ordQ, 'po.company_id');
        } elseif ($this->db->fieldExists('company_id', 'vendors')) {
            $this->scopeCompany($ordQ, 'v.company_id');
        }
        $orders = $ordQ->orderBy('po.created_at','DESC')->limit(10)->get()->getResultArray();

        $kpi = [
            'pending_requests'  => $this->countScopedPurchaseRequests('pending'),
            'approved_requests' => $this->countScopedPurchaseRequests('approved'),
            'open_orders'       => $this->countScopedPurchaseOrders(['pending', 'approved']),
            'pending_grn'       => $this->countScopedPurchaseOrders(['delivered']),
        ];

        return view('procurement/index', $this->viewData([
            'title'       => 'Procurement',
            'requests'    => $requests,
            'orders'      => $orders,
            'kpi'         => $kpi,
            'totalCount'  => $total,
            'perPage'     => $pg['perPage'],
            'currentPage' => $pg['page'],
        ]));
    }

    // ── Purchase Requests ─────────────────────────────────────

    public function createRequest()
    {
        $items = $this->db->table('inventory_items')->orderBy('name','ASC')->get()->getResultArray();
        return view('procurement/create_request', $this->viewData(['title'=>'Purchase Request','items'=>$items]));
    }

    public function storeRequest()
    {
        $rules = [
            'item_id'  => 'required|integer',
            'quantity' => 'required|is_natural_no_zero',
            'reason'   => 'required|min_length[5]|max_length[1000]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $this->db->table('purchase_requests')->insert([
            'item_id'      => (int)$this->request->getPost('item_id'),
            'quantity'     => (int)$this->request->getPost('quantity'),
            'reason'       => esc($this->request->getPost('reason')),
            'priority'     => $this->request->getPost('priority') ?? 'medium',
            'status'       => 'pending',
            'requested_by' => session()->get('user_id'),
        ]);
        $this->logActivity('create', 'purchase_requests', $this->db->insertID());
        return redirect()->to(base_url('procurement'))->with('success','Purchase request submitted.');
    }

    public function viewRequest(int $id)
    {
        $req = $this->db->table('purchase_requests pr')
            ->select('pr.*, i.name as item_name, i.item_code, i.unit, i.quantity as stock_qty,
                      u.name as requested_by_name, ap.name as approved_by_name')
            ->join('inventory_items i','i.id=pr.item_id',       'left')
            ->join('users u',          'u.id=pr.requested_by',   'left')
            ->join('users ap',         'ap.id=pr.approved_by',   'left')
            ->where('pr.id', $id)->get()->getRowArray();

        if (!$req) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        return view('procurement/view_request', $this->viewData(['title' => 'Purchase Request', 'req' => $req]));
    }

    public function approveRequest(int $id)
    {
        // FIX SEC-03: role guard
        $this->requireRole('super_admin', 'facility_manager', 'procurement_officer');
        $this->db->table('purchase_requests')->where('id', $id)->update([
            'status'      => 'approved',
            'approved_by' => session()->get('user_id'),
            'approved_at' => date('Y-m-d H:i:s'),
        ]);
        $this->logActivity('approve', 'purchase_requests', $id);
        return redirect()->to(base_url('procurement'))->with('success','Request approved.');
    }

    public function rejectRequest(int $id)
    {
        $this->requireRole('super_admin', 'facility_manager', 'procurement_officer');
        $this->db->table('purchase_requests')->where('id', $id)->update([
            'status'      => 'rejected',
            'approved_by' => session()->get('user_id'),
            'approved_at' => date('Y-m-d H:i:s'),
        ]);
        $this->logActivity('reject', 'purchase_requests', $id);
        return redirect()->to(base_url('procurement'))->with('success','Request rejected.');
    }

    // ── Purchase Orders ──────────────────────────────────────

    public function createOrder()
    {
        $this->requireRole('super_admin', 'facility_manager', 'procurement_officer');
        $vendors  = $this->db->table('vendors')->where('status','active')->orderBy('name','ASC')->get()->getResultArray();
        $requests = $this->db->table('purchase_requests pr')
            ->select('pr.*, i.name as item_name, i.unit')
            ->join('inventory_items i','i.id=pr.item_id','left')
            ->where('pr.status','approved')->get()->getResultArray();
        return view('procurement/create_order', $this->viewData([
            'title'    => 'Purchase Order',
            'vendors'  => $vendors,
            'requests' => $requests,
        ]));
    }

    public function storeOrder()
    {
        $this->requireRole('super_admin', 'facility_manager', 'procurement_officer');
        $rules = ['vendor_id' => 'required|integer', 'delivery_date' => 'required|valid_date'];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $poNumber = $this->generateNumber('PO', 'purchase_orders', 'po_number');

        $this->db->table('purchase_orders')->insert([
            'po_number'     => $poNumber,
            'vendor_id'     => (int)$this->request->getPost('vendor_id'),
            'delivery_date' => $this->request->getPost('delivery_date'),
            'total_amount'  => (float)($this->request->getPost('total_amount') ?? 0),
            'status'        => 'pending',
            'notes'         => esc($this->request->getPost('notes') ?? ''),
            'created_by'    => session()->get('user_id'),
        ]);

        // FIX BUG-02: capture PO ID IMMEDIATELY after insert
        $poId = $this->db->insertID();

        // Link approved requests to this PO
        $requestIds = $this->request->getPost('request_ids') ?? [];
        if (is_array($requestIds)) {
            foreach ($requestIds as $rid) {
                $this->db->table('purchase_requests')
                    ->where('id', (int)$rid)
                    ->update(['status' => 'ordered', 'po_id' => $poId]); // Use captured $poId
            }
        }

        $this->logActivity('create', 'purchase_orders', $poId, "Created PO $poNumber");
        return redirect()->to(base_url('procurement'))->with('success', "Purchase Order $poNumber created.");
    }

    public function viewOrder(int $id)
    {
        $po = $this->db->table('purchase_orders po')
            ->select('po.*, v.name as vendor_name, v.email as vendor_email, v.phone as vendor_phone,
                      u.name as created_by_name')
            ->join('vendors v','v.id=po.vendor_id','left')
            ->join('users u','u.id=po.created_by','left')
            ->where('po.id', $id)->get()->getRowArray();

        if (!$po) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $lineItems = $this->db->table('purchase_requests pr')
            ->select('pr.*, i.name as item_name, i.unit')
            ->join('inventory_items i','i.id=pr.item_id','left')
            ->where('pr.po_id', $id)->get()->getResultArray();

        $threeWay = null;
        if ($this->db->tableExists('procurement_three_way_matches')) {
            $threeWay = $this->db->table('procurement_three_way_matches')->where('po_id', $id)->get()->getRowArray();
        }

        return view('procurement/view_order', $this->viewData([
            'title'     => $po['po_number'],
            'po'        => $po,
            'lineItems' => $lineItems,
            'threeWay'  => $threeWay,
        ]));
    }

    public function threeWayMatch(int $id)
    {
        $this->requireRole('super_admin', 'facility_manager', 'finance_manager', 'procurement_officer');
        $svc = new \App\Services\ProcurementThreeWayMatchService($this->db);
        try {
            $analysis = $svc->analyze($id);
        } catch (\Throwable $e) {
            return redirect()->to(base_url('procurement/order/view/' . $id))->with('error', $e->getMessage());
        }
        $history = [];
        if ($this->db->tableExists('procurement_three_way_matches')) {
            $history = $this->db->table('procurement_three_way_matches')->where('po_id', $id)->get()->getRowArray();
        }

        return view('procurement/three_way_match', $this->viewData([
            'title'    => '3-Way Match — ' . ($analysis['po']['po_number'] ?? ''),
            'analysis' => $analysis,
            'poId'     => $id,
            'history'  => $history,
        ]));
    }

    public function recordThreeWayMatch(int $id)
    {
        $this->requireRole('super_admin', 'facility_manager', 'finance_manager', 'procurement_officer');
        $svc = new \App\Services\ProcurementThreeWayMatchService($this->db);
        $svc->recordMatch($id, (int) session()->get('user_id'), $this->request->getPost('notes'));
        $this->logActivity('three_way_match', 'purchase_orders', $id, '3-way match recorded');

        return redirect()->to(base_url('procurement/order/three-way/' . $id))->with('success', 'Match record saved.');
    }

    public function approveThreeWayException(int $id)
    {
        $this->requireRole('super_admin', 'finance_manager');
        $svc = new \App\Services\ProcurementThreeWayMatchService($this->db);
        $svc->approveException($id, (int) session()->get('user_id'), $this->request->getPost('notes'));
        $this->logActivity('three_way_approve', 'purchase_orders', $id, 'Finance approved 3-way exception');

        return redirect()->to(base_url('procurement/order/three-way/' . $id))->with('success', 'Exception approved — vendor bill may be paid.');
    }

    public function approveOrder(int $id)
    {
        $this->requireRole('super_admin', 'facility_manager');
        $this->db->table('purchase_orders')->where('id', $id)->update([
            'status'      => 'approved',
            'approved_by' => session()->get('user_id'),
            'approved_at' => date('Y-m-d H:i:s'),
        ]);
        $this->logActivity('approve', 'purchase_orders', $id);
        $msg = 'Purchase Order approved.';
        try {
            $billId = (new \App\Services\Finance\FinanceIntegrationService($this->db))
                ->createVendorBillFromPo($id, (int) session()->get('user_id'));
            if ($billId) {
                $msg .= ' Vendor bill created — see Finance → Vendor Bills (AP).';
            }
        } catch (\Throwable $e) {
            log_message('error', 'PO→AP: ' . $e->getMessage());
        }

        return redirect()->to(base_url('procurement/order/view/' . $id))->with('success', $msg);
    }

    public function printOrder(int $id)
    {
        $po = $this->db->table('purchase_orders po')
            ->select('po.*, v.name as vendor_name, v.address as vendor_address, v.email as vendor_email')
            ->join('vendors v','v.id=po.vendor_id','left')
            ->where('po.id', $id)->get()->getRowArray();

        if (!$po) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();

        $lineItems = $this->db->table('purchase_requests pr')
            ->select('pr.*, i.name as item_name, i.unit')
            ->join('inventory_items i','i.id=pr.item_id','left')
            ->where('pr.po_id', $id)->get()->getResultArray();

        return view('procurement/print_order', $this->viewData([
            'title'     => 'Print: ' . $po['po_number'],
            'po'        => $po,
            'lineItems' => $lineItems,
            'usePdf'    => true,
        ]));
    }

    // ── RFQ ──────────────────────────────────────────────────

    public function createRfq()
    {
        $this->requireRole('super_admin', 'facility_manager', 'procurement_officer');
        $vendors  = $this->db->table('vendors')->where('status','active')->get()->getResultArray();
        $requests = $this->db->table('purchase_requests pr')
            ->select('pr.*, i.name as item_name')
            ->join('inventory_items i','i.id=pr.item_id','left')
            ->where('pr.status','approved')->get()->getResultArray();
        return view('procurement/create_rfq', $this->viewData([
            'title'    => 'Request for Quotation',
            'vendors'  => $vendors,
            'requests' => $requests,
        ]));
    }

    public function storeRfq()
    {
        $this->requireRole('super_admin', 'facility_manager', 'procurement_officer');
        // RFQ table: id, rfq_number, title, description, deadline, status, created_by
        $rfqNumber = $this->generateNumber('RFQ', 'rfq', 'rfq_number');
        $this->db->table('rfq')->insert([
            'rfq_number'  => $rfqNumber,
            'title'       => esc($this->request->getPost('title')),
            'description' => esc($this->request->getPost('description') ?? ''),
            'deadline'    => $this->request->getPost('deadline'),
            'status'      => 'open',
            'created_by'  => session()->get('user_id'),
        ]);
        $rfqId = $this->db->insertID();
        // Link vendors
        $vendorIds = $this->request->getPost('vendor_ids') ?? [];
        foreach ($vendorIds as $vid) {
            $this->db->table('rfq_vendors')->insert([
                'rfq_id' => $rfqId, 'vendor_id' => (int)$vid, 'status' => 'sent',
            ]);
        }
        $this->logActivity('create', 'rfq', $rfqId, "Created RFQ $rfqNumber");
        return redirect()->to(base_url('procurement'))->with('success', "RFQ $rfqNumber created and sent to vendors.");
    }

    public function viewRfq(int $id)
    {
        $rfq = $this->db->table('rfq')->where('id', $id)->get()->getRowArray();
        if (!$rfq) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        $vendors    = $this->db->table('rfq_vendors rv')
            ->select('rv.*, v.name as vendor_name, v.email')
            ->join('vendors v','v.id=rv.vendor_id','left')
            ->where('rv.rfq_id', $id)->get()->getResultArray();
        $quotations = $this->db->table('rfq_quotations rq')
            ->select('rq.*, v.name as vendor_name')
            ->join('vendors v','v.id=rq.vendor_id','left')
            ->where('rq.rfq_id', $id)->get()->getResultArray();
        return view('procurement/view_rfq', $this->viewData([
            'title'      => $rfq['rfq_number'],
            'rfq'        => $rfq,
            'vendors'    => $vendors,
            'quotations' => $quotations,
        ]));
    }

    public function addQuotation(int $rfqId)
    {
        $rules = ['vendor_id'=>'required|integer','unit_price'=>'required|numeric','total_amount'=>'required|numeric'];
        if (!$this->validate($rules)) return redirect()->back()->with('errors', $this->validator->getErrors());
        $this->db->table('rfq_quotations')->insert([
            'rfq_id'       => $rfqId,
            'vendor_id'    => (int)$this->request->getPost('vendor_id'),
            'unit_price'   => (float)$this->request->getPost('unit_price'),
            'total_amount' => (float)$this->request->getPost('total_amount'),
            'lead_time'    => esc($this->request->getPost('lead_time') ?? ''),
            'validity'     => $this->request->getPost('validity') ?: null,
            'notes'        => esc($this->request->getPost('notes') ?? ''),
            'added_by'     => session()->get('user_id'),
        ]);
        return redirect()->to(base_url('procurement/rfq/view/'.$rfqId))->with('success','Quotation added.');
    }

    public function compareQuotations(int $rfqId)
    {
        $rfq = $this->db->table('rfq')->where('id', $rfqId)->get()->getRowArray();
        $quotations = $this->db->table('rfq_quotations rq')
            ->select('rq.*, v.name as vendor_name, v.rating as vendor_rating')
            ->join('vendors v','v.id=rq.vendor_id','left')
            ->where('rq.rfq_id', $rfqId)
            ->orderBy('rq.total_amount','ASC')->get()->getResultArray();
        return view('procurement/compare_quotations', $this->viewData([
            'title'      => 'Compare Quotations: ' . ($rfq['rfq_number'] ?? ''),
            'rfq'        => $rfq,
            'quotations' => $quotations,
        ]));
    }

    // ── GRN ──────────────────────────────────────────────────

    public function grnIndex()
    {
        $this->requireRole(['super_admin', 'facility_manager', 'procurement_officer']);
        $from = $this->request->getGet('from') ?? date('Y-m-01');
        $to   = $this->request->getGet('to')   ?? date('Y-m-d');

        $grns = $this->db->table('grn g')
            ->select('g.*, po.po_number, v.name AS vendor_name, u.name AS received_by_name')
            ->join('purchase_orders po', 'po.id = g.po_id', 'left')
            ->join('vendors v',             'v.id = po.vendor_id', 'left')
            ->join('users u',               'u.id = g.received_by', 'left')
            ->where('DATE(g.received_date) >=', $from)
            ->where('DATE(g.received_date) <=', $to)
            ->orderBy('g.received_date', 'DESC')
            ->get()->getResultArray();

        return view('procurement/grn_index', $this->viewData([
            'title' => 'Goods Received Notes (GRN)',
            'grns'  => $grns,
            'from'  => $from,
            'to'    => $to,
        ]));
    }

    public function createGrn(int $poId)
    {
        $this->requireRole('super_admin', 'facility_manager', 'procurement_officer');
        $po = $this->db->table('purchase_orders po')
            ->select('po.*, v.name as vendor_name')
            ->join('vendors v','v.id=po.vendor_id','left')
            ->where('po.id', $poId)->get()->getRowArray();
        if (!$po) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        $lineItems = $this->db->table('purchase_requests pr')
            ->select('pr.*, i.name as item_name, i.unit')
            ->join('inventory_items i','i.id=pr.item_id','left')
            ->where('pr.po_id', $poId)->get()->getResultArray();
        return view('procurement/create_grn', $this->viewData([
            'title'     => 'Goods Receipt Note',
            'po'        => $po,
            'lineItems' => $lineItems,
        ]));
    }

    public function storeGrn()
    {
        $this->requireRole('super_admin', 'facility_manager', 'procurement_officer');
        $rules = ['po_id'=>'required|integer','received_date'=>'required|valid_date'];
        if (!$this->validate($rules)) return redirect()->back()->with('errors',$this->validator->getErrors());

        $poId = (int)$this->request->getPost('po_id');
        $grnNumber = $this->generateNumber('GRN', 'grn', 'grn_number');

        $this->db->table('grn')->insert([
            'grn_number'    => $grnNumber,
            'po_id'         => $poId,
            'received_date' => $this->request->getPost('received_date'),
            'received_by'   => session()->get('user_id'),
            'status'        => 'partial',
            'notes'         => esc($this->request->getPost('notes') ?? ''),
        ]);
        $grnId = $this->db->insertID();

        // Process received quantities and update inventory
        $itemIds       = $this->request->getPost('item_ids')      ?? [];
        $receivedQtys  = $this->request->getPost('received_qty')  ?? [];
        $prIds         = $this->request->getPost('pr_ids')        ?? [];

        foreach ($itemIds as $idx => $itemId) {
            $receivedQty = (int)($receivedQtys[$idx] ?? 0);
            if ($receivedQty <= 0) continue;

            // Update inventory stock
            $inv = $this->db->table('inventory_items')->where('id', (int)$itemId)->get()->getRowArray();
            if ($inv) {
                $this->db->table('inventory_items')->where('id', (int)$itemId)
                    ->update(['quantity' => $inv['quantity'] + $receivedQty]);
                $this->db->table('stock_movements')->insert([
                    'item_id'       => (int)$itemId,
                    'movement_type' => 'in',
                    'quantity'      => $receivedQty,
                    'reference'     => $grnNumber,
                    'notes'         => 'Received via GRN',
                    'created_by'    => session()->get('user_id'),
                ]);
            }

            // Record GRN line item
            $this->db->table('grn_items')->insert([
                'grn_id'       => $grnId,
                'pr_id'        => (int)($prIds[$idx] ?? 0) ?: null,
                'item_id'      => (int)$itemId,
                'received_qty' => $receivedQty,
            ]);
        }

        // Update PO status
        $this->db->table('purchase_orders')->where('id', $poId)->update(['status' => 'delivered']);
        $this->logActivity('create', 'grn', $grnId, "Created GRN $grnNumber");
        return redirect()->to(base_url('procurement'))->with('success', "GRN $grnNumber recorded. Inventory updated.");
    }

    public function viewGrn(int $id)
    {
        $grn = $this->db->table('grn g')
            ->select('g.*, po.po_number, v.name as vendor_name, u.name as received_by_name')
            ->join('purchase_orders po','po.id=g.po_id','left')
            ->join('vendors v','v.id=po.vendor_id','left')
            ->join('users u','u.id=g.received_by','left')
            ->where('g.id', $id)->get()->getRowArray();
        if (!$grn) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        $items = $this->db->table('grn_items gi')
            ->select('gi.*, i.name as item_name, i.unit')
            ->join('inventory_items i','i.id=gi.item_id','left')
            ->where('gi.grn_id', $id)->get()->getResultArray();
        return view('procurement/view_grn', $this->viewData(['title' => $grn['grn_number'], 'grn' => $grn, 'items' => $items]));
    }
}
