<?php
namespace App\Controllers;

/**
 * Finance — complete sellable FM Finance module.
 *
 * Invoice lifecycle: draft → sent → paid | overdue | cancelled
 * Contract lifecycle: active → expired | renewed | cancelled
 * Expense workflow: pending → approved | rejected
 * Petty cash: pending → approved → issued → reconciliation → closed | rejected
 * Reimbursements: pending → approved → paid | rejected
 */
class Finance extends BaseController
{
    protected ?string $workspaceRequired = 'pm';
    // ── Dashboard ─────────────────────────────────────────────
    public function index()
    {
        $this->requireRole('super_admin','facility_manager','finance_manager','finance_user');

        // Auto-expire overdue invoices before rendering
        $this->_syncOverdueInvoices();
        // Auto-expire contracts past end_date
        $this->_syncExpiredContracts();

        $currency   = $this->settings['currency']    ?? 'QAR';
        $vatEnabled = ($this->settings['vat_enabled'] ?? '0') === '1';

        $paid        = (float)($this->db->table('invoices')->where('status','paid')->selectSum('total','t')->get()->getRowArray()['t'] ?? 0);
        $exp         = (float)($this->db->table('expenses')->where('status','approved')->selectSum('amount','t')->get()->getRowArray()['t'] ?? 0);
        $netProfit   = $paid - $exp;
        $overdueInv  = $this->db->table('invoices')->where('status','overdue')->countAllResults();
        $pendingExp  = $this->db->table('expenses')->where('status','pending')->countAllResults();

        $recentInv = $this->db->table('invoices i')
            ->select('i.*, f.name as facility_name, c.contract_number')
            ->join('facilities f','f.id=i.facility_id','left')
            ->join('contracts c','c.id=i.contract_id','left')
            ->orderBy('i.created_at','DESC')->limit(10)->get()->getResultArray();

        // MTD revenue trend
        $trend = $this->db->query("
            SELECT DATE_FORMAT(issue_date,'%b') as mon,
                   DATE_FORMAT(issue_date,'%Y-%m') as ym,
                   SUM(CASE WHEN status='paid' THEN total ELSE 0 END) as revenue,
                   SUM(CASE WHEN status IN ('sent','overdue') THEN total ELSE 0 END) as outstanding
            FROM invoices
            WHERE issue_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(issue_date,'%Y-%m'), DATE_FORMAT(issue_date,'%b')
            ORDER BY ym
        ")->getResultArray();

        $expiringContracts = $this->db->table('contracts c')
            ->select('c.id, c.contract_number, c.client_name, c.end_date, f.name as facility_name')
            ->join('facilities f','f.id=c.facility_id','left')
            ->where('c.status','active')
            ->where('c.end_date <=', date('Y-m-d',strtotime('+30 days')))
            ->where('c.end_date >=', date('Y-m-d'))
            ->orderBy('c.end_date','ASC')->limit(5)->get()->getResultArray();

        return view('finance/index', $this->viewData([
            'title'             => 'Finance',
            'totalRevenue'      => $paid,
            'totalExpenses'     => $exp,
            'netProfit'         => $netProfit,
            'overdueInv'        => $overdueInv,
            'pendingExp'        => $pendingExp,
            'recentInv'         => $recentInv,
            'trend'             => $trend,
            'vatEnabled'        => $vatEnabled,
            'expiringContracts' => $expiringContracts,
        ]));
    }

    // ── Invoices ─────────────────────────────────────────────

    public function invoices()
    {
        $this->requireRole('super_admin','facility_manager','finance_manager','finance_user');
        $this->_syncOverdueInvoices();

        $filterStatus   = $this->request->getGet('status')   ?? '';
        $filterFacility = (int)($this->request->getGet('facility') ?? 0);
        $from           = $this->request->getGet('from')     ?? date('Y-01-01');
        $to             = $this->request->getGet('to')       ?? date('Y-m-d');
        $vatEnabled     = ($this->settings['vat_enabled'] ?? '0') === '1';

        $pg          = $this->paginate(25);
        $currentPage = $pg['page'];
        $perPage     = $pg['perPage'];
        $offset      = $pg['offset'];

        $q = $this->db->table('invoices i')
            ->select('i.*, f.name as facility_name, c.contract_number')
            ->join('facilities f','f.id=i.facility_id','left')
            ->join('contracts c','c.id=i.contract_id','left');
        $this->scopeFacilities($q, 'i.facility_id');
        if ($filterStatus)   $q->where('i.status',$filterStatus);
        if ($filterFacility) $q->where('i.facility_id',$filterFacility);
        if ($from) $q->where('DATE(i.issue_date) >=',$from);
        if ($to)   $q->where('DATE(i.issue_date) <=',$to);
        $total    = (clone $q)->countAllResults(false);
        $invoices = $q->orderBy('i.created_at','DESC')->limit($perPage,$offset)->get()->getResultArray();

        $facilities = $this->db->table('facilities')->where('status','active')->get()->getResultArray();

        // Summary totals
        $summary = $this->db->query("
            SELECT
              SUM(CASE WHEN status='draft'   THEN total ELSE 0 END) as draft,
              SUM(CASE WHEN status='sent'    THEN total ELSE 0 END) as sent,
              SUM(CASE WHEN status='paid'    THEN total ELSE 0 END) as paid,
              SUM(CASE WHEN status='overdue' THEN total ELSE 0 END) as overdue,
              COUNT(CASE WHEN status='overdue' THEN 1 END)          as overdue_count
            FROM invoices
        ")->getRowArray();

        return view('finance/invoices', $this->viewData([
            'title'          => 'Invoices',
            'invoices'       => $invoices,
            'filterStatus'   => $filterStatus,
            'filterFacility' => $filterFacility,
            'from'           => $from,
            'to'             => $to,
            'facilities'     => $facilities,
            'vatEnabled'     => $vatEnabled,
            'summary'        => $summary,
            'totalCount'     => $total,
            'perPage'        => $perPage,
            'currentPage'    => $currentPage,
        ]));
    }

    public function createInvoice()
    {
        $this->requireRole('super_admin','facility_manager','finance_manager');
        $facilities = $this->db->table('facilities')->where('status','active')->get()->getResultArray();
        $contracts  = $this->db->table('contracts')->where('status','active')->orderBy('contract_number','ASC')->get()->getResultArray();
        $workOrders = $this->db->table('work_orders')->whereIn('status',['completed'])->orderBy('created_at','DESC')->limit(50)->get()->getResultArray();
        $vatEnabled = ($this->settings['vat_enabled'] ?? '0') === '1';
        $vatRate    = (float)($this->settings['vat_rate'] ?? 5);

        return view('finance/create_invoice', $this->viewData([
            'title'      => 'Create Invoice',
            'facilities' => $facilities,
            'contracts'  => $contracts,
            'workOrders' => $workOrders,
            'vatEnabled' => $vatEnabled,
            'vatRate'    => $vatRate,
        ]));
    }

    public function storeInvoice()
    {
        $this->requireRole('super_admin','facility_manager','finance_manager');
        $rules = [
            'facility_id' => 'required|integer',
            'subtotal'    => 'required|numeric|greater_than[0]',
            'issue_date'  => 'required|valid_date',
            'due_date'    => 'required|valid_date',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());
        }

        // Validate due_date >= issue_date
        $issueDate = $this->request->getPost('issue_date');
        $dueDate   = $this->request->getPost('due_date');
        if ($dueDate < $issueDate) {
            return redirect()->back()->withInput()->with('error','Due date cannot be before issue date.');
        }

        $facilityId = (int) $this->request->getPost('facility_id');
        $this->assertFacilityAccess($facilityId);

        // ── Duplicate invoice prevention ──────────────────────────────────
        $woIdForCheck = $this->request->getPost('work_order_id') ?: null;
        if ($woIdForCheck) {
            $existingInv = $this->db->table('invoices')
                ->where('work_order_id', (int)$woIdForCheck)
                ->whereIn('status', ['draft', 'sent', 'partial', 'paid'])
                ->where('deleted_at', null)
                ->get()->getRowArray();
            if ($existingInv) {
                return redirect()->back()->withInput()->with(
                    'error',
                    'An invoice (' . $existingInv['invoice_number'] . ') already exists for this Work Order (status: ' . ucfirst($existingInv['status']) . '). Duplicate invoices are not allowed.'
                );
            }
        }

        $vatEnabled = ($this->settings['vat_enabled'] ?? '0') === '1';
        $vatRate    = $vatEnabled ? (float)($this->settings['vat_rate'] ?? 5) : 0;
        $subtotal   = (float)$this->request->getPost('subtotal');
        $vatAmt     = $vatEnabled ? round($subtotal * $vatRate / 100, 2) : 0;
        $total      = $subtotal + $vatAmt;
        $invNum     = $this->generateNumber('INV','invoices','invoice_number');

        $this->db->table('invoices')->insert([
            'invoice_number' => $invNum,
            'facility_id'    => (int)$this->request->getPost('facility_id'),
            'contract_id'    => $this->request->getPost('contract_id') ?: null,
            'work_order_id'  => $this->request->getPost('work_order_id') ?: null,
            'invoice_type'   => $this->request->getPost('invoice_type') ?? 'adhoc',
            'issue_date'     => $issueDate,
            'due_date'       => $dueDate,
            'subtotal'       => $subtotal,
            'vat_rate'       => $vatRate,
            'vat_amount'     => $vatAmt,
            'total'          => $total,
            'currency'       => $this->settings['currency'] ?? 'QAR',
            'status'         => 'draft',
            'notes'          => esc($this->request->getPost('notes') ?? ''),
            'created_by'     => session()->get('user_id'),
        ]);
        $invId = $this->db->insertID();
        $this->logActivity('create','invoices',$invId,"Created $invNum");
        return redirect()->to(base_url('finance/invoices/view/'.$invId))->with('success',"Invoice $invNum created.");
    }

    public function viewInvoice(int $id)
    {
        $role  = session()->get('user_role');
        $email = session()->get('user_email');
        $this->requireRole('super_admin', 'facility_manager', 'finance_manager', 'finance_user', 'client');

        $q = $this->db->table('invoices i')
            ->select('i.*, f.name as facility_name, f.address as facility_address, c.contract_number, c.client_email, cu.name as created_by_name, pu.name as paid_by_name')
            ->join('facilities f', 'f.id=i.facility_id', 'left')
            ->join('contracts c', 'c.id=i.contract_id', 'left')
            ->join('users cu', 'cu.id=i.created_by', 'left')
            ->join('users pu', 'pu.id=i.paid_by', 'left')
            ->where('i.id', $id);
        $this->scopeFacilities($q, 'i.facility_id');
        if ($role === 'client') {
            $q->groupStart()
                ->where('c.client_email', $email)
                ->orWhere('i.created_by', session()->get('user_id'))
            ->groupEnd();
        }
        $inv = $q->get()->getRowArray();
        if (!$inv) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $vatEnabled = ($this->settings['vat_enabled'] ?? '0') === '1';
        $items = [];
        if ($this->db->tableExists('invoice_items')) {
            $items = $this->db->table('invoice_items')->where('invoice_id', $id)->orderBy('sort_order')->get()->getResultArray();
        }
        $payments = [];
        $paidTotal  = 0.0;
        if ($this->db->tableExists('invoice_payments')) {
            $payments  = $this->paymentService()->paymentHistory($id);
            $paidTotal = $this->paymentService()->getPaidTotal($id);
        } elseif ($inv['status'] === 'paid') {
            $paidTotal = (float) $inv['total'];
        }
        $balanceDue = round(max(0, (float) $inv['total'] - $paidTotal), 2);
        $canRecordPayment = $this->canManageFinancePayments()
            && \App\Services\PaymentService::canAcceptPayment((string) $inv['status'])
            && $balanceDue > 0.009;
        $canMarkAsSent = in_array(session()->get('user_role'), ['super_admin', 'facility_manager', 'finance_manager'], true)
            && $inv['status'] === 'draft';

        return view('finance/view_invoice', $this->viewData([
            'title'            => $inv['invoice_number'],
            'inv'              => $inv,
            'vatEnabled'       => $vatEnabled,
            'invoiceItems'     => $items,
            'payments'         => $payments,
            'paidTotal'        => $paidTotal,
            'balanceDue'       => $balanceDue,
            'canRecordPayment' => $canRecordPayment,
            'canMarkAsSent'    => $canMarkAsSent,
        ]));
    }

    public function editInvoice(int $id)
    {
        $this->requireRole('super_admin','finance_manager');
        $inv = $this->db->table('invoices')->where('id',$id)->get()->getRowArray();
        if (!$inv || !in_array($inv['status'],['draft','sent'])) {
            return redirect()->back()->with('error','Only draft or sent invoices can be edited.');
        }
        $this->assertFacilityAccess((int) $inv['facility_id']);
        $facilities = $this->db->table('facilities')->where('status','active')->get()->getResultArray();
        $contracts  = $this->db->table('contracts')->where('status','active')->get()->getResultArray();
        $vatEnabled = ($this->settings['vat_enabled'] ?? '0') === '1';
        $vatRate    = (float)($this->settings['vat_rate'] ?? 5);
        return view('finance/create_invoice', $this->viewData([
            'title'      => 'Edit Invoice',
            'inv'        => $inv,
            'facilities' => $facilities,
            'contracts'  => $contracts,
            'workOrders' => [],
            'vatEnabled' => $vatEnabled,
            'vatRate'    => $vatRate,
        ]));
    }

    public function updateInvoice(int $id)
    {
        $this->requireRole('super_admin','finance_manager');
        $inv = $this->db->table('invoices')->where('id',$id)->get()->getRowArray();
        if (!$inv || !in_array($inv['status'],['draft','sent'])) {
            return redirect()->back()->with('error','Cannot edit this invoice.');
        }
        $this->assertFacilityAccess((int) $inv['facility_id']);
        $facilityId = (int) $this->request->getPost('facility_id');
        $this->assertFacilityAccess($facilityId);
        $vatEnabled = ($this->settings['vat_enabled'] ?? '0') === '1';
        $vatRate    = $vatEnabled ? (float)($this->settings['vat_rate'] ?? 5) : 0;
        $subtotal   = (float)$this->request->getPost('subtotal');
        $vatAmt     = $vatEnabled ? round($subtotal * $vatRate / 100, 2) : 0;
        $newTotal   = $subtotal + $vatAmt;
        $paidSoFar  = $this->paymentService()->getPaidTotal($id);
        if ($newTotal < $paidSoFar - 0.01) {
            return redirect()->back()->with('error', 'Invoice total cannot be less than amount already paid.');
        }
        $this->db->table('invoices')->where('id',$id)->update([
            'facility_id'  => $facilityId,
            'contract_id'  => $this->request->getPost('contract_id') ?: null,
            'invoice_type' => $this->request->getPost('invoice_type') ?? 'adhoc',
            'issue_date'   => $this->request->getPost('issue_date'),
            'due_date'     => $this->request->getPost('due_date'),
            'subtotal'     => $subtotal,
            'vat_rate'     => $vatRate,
            'vat_amount'   => $vatAmt,
            'total'        => $newTotal,
            'notes'        => esc($this->request->getPost('notes') ?? ''),
        ]);
        $this->logActivity('update','invoices',$id);
        return redirect()->to(base_url('finance/invoices/view/'.$id))->with('success','Invoice updated.');
    }

    /**
     * Invoice status transitions — paid only via Record Payment (no manual paid).
     */
    public function updateInvoiceStatus(int $id)
    {
        $this->requireRole('super_admin','facility_manager','finance_manager');
        $inv     = $this->db->table('invoices')->where('id',$id)->get()->getRowArray();
        if (!$inv) return redirect()->back()->with('error','Invoice not found.');

        $this->assertFacilityAccess((int) $inv['facility_id']);

        $newStatus = $this->request->getPost('status');
        if ($newStatus === 'paid') {
            return redirect()->back()->with('error', 'Use Record Payment — paid status is set only when payment is logged.');
        }

        $transitions = [
            'draft'     => ['sent', 'cancelled'],
            'sent'      => ['cancelled'],
            'overdue'   => ['cancelled'],
            'partial'   => ['cancelled'],
            'paid'      => [],
            'cancelled' => [],
        ];

        $allowed = $transitions[$inv['status']] ?? [];
        if (! in_array($newStatus, $allowed, true)) {
            return redirect()->back()->with('error', "Cannot move invoice from '{$inv['status']}' to '$newStatus'.");
        }

        $update = ['status' => $newStatus];
        $this->db->table('invoices')->where('id', $id)->update($update);
        cache()->delete('fm_sync_overdue_invoices');
        if ($newStatus === 'sent' && in_array($inv['status'], ['draft'], true)) {
            try {
                (new \App\Services\Finance\GlPostingService($this->db))->postInvoiceRevenue(
                    $id,
                    (float) $inv['subtotal'],
                    (float) ($inv['vat_amount'] ?? 0),
                    (int) session()->get('user_id')
                );
            } catch (\Throwable $e) {
                log_message('warning', 'GL invoice revenue: ' . $e->getMessage());
            }
        }
        $this->logActivity('status_change','invoices',$id,"Status → $newStatus");

        // Notify invoice creator
        $this->_notify($inv['created_by'],"Invoice {$inv['invoice_number']} $newStatus",
            "Invoice has been marked as ".ucfirst($newStatus).".",'invoice',$id);

        return redirect()->to(base_url('finance/invoices/view/'.$id))
            ->with('success',"Invoice marked as ".ucfirst($newStatus).".");
    }

    public function printInvoice(int $id)
    {
        $this->requireRole('super_admin','facility_manager','finance_manager','finance_user');
        $inv = $this->db->table('invoices i')
            ->select('i.*, f.name as facility_name, f.address as facility_address, c.contract_number, cu.name as created_by_name')
            ->join('facilities f','f.id=i.facility_id','left')
            ->join('contracts c','c.id=i.contract_id','left')
            ->join('users cu','cu.id=i.created_by','left')
            ->where('i.id',$id)->get()->getRowArray();
        if (!$inv) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        $data = $this->viewData(['title'=>'Invoice '.$inv['invoice_number'],'inv'=>$inv,'usePdf'=>true]);

        if ($this->request->getGet('pdf') && class_exists(\Dompdf\Dompdf::class)) {
            $html = view('finance/print_invoice', $data);
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="Invoice_' . $inv['invoice_number'] . '.pdf"')
                ->setBody($dompdf->output());
        }

        return view('finance/print_invoice', $data);
    }

    // ── Contracts ─────────────────────────────────────────────

    public function contracts()
    {
        $this->requireRole('super_admin','facility_manager','finance_manager','finance_user');
        $this->_syncExpiredContracts();

        $filterStatus   = $this->request->getGet('status')   ?? '';
        $filterFacility = (int)($this->request->getGet('facility') ?? 0);

        $pg = $this->paginate(25);
        $q  = $this->db->table('contracts c')
            ->select('c.*, f.name as facility_name, u.unit_number')
            ->join('facilities f', 'f.id=c.facility_id', 'left')
            ->join('units u', 'u.id=c.unit_id', 'left');
        $this->scopeFacilities($q, 'c.facility_id');
        if ($filterStatus) {
            $q->where('c.status', $filterStatus);
        }
        if ($filterFacility) {
            $q->where('c.facility_id', $filterFacility);
        }
        $total     = (clone $q)->countAllResults(false);
        $contracts = $q->orderBy('c.end_date', 'ASC')->limit($pg['perPage'], $pg['offset'])->get()->getResultArray();
        $facilities = $this->db->table('facilities')->where('status','active')->get()->getResultArray();

        $summary = [
            'active'    => count(array_filter($contracts,fn($c)=>$c['status']==='active')),
            'expired'   => count(array_filter($contracts,fn($c)=>$c['status']==='expired')),
            'expiring30'=> count(array_filter($contracts,fn($c)=>$c['status']==='active'&&strtotime($c['end_date'])<strtotime('+30 days')&&strtotime($c['end_date'])>=time())),
        ];

        return view('finance/contracts', $this->viewData([
            'title'          => 'Contracts',
            'contracts'      => $contracts,
            'facilities'     => $facilities,
            'filterStatus'   => $filterStatus,
            'filterFacility' => $filterFacility,
            'summary'        => $summary,
            'totalCount'     => $total,
            'perPage'        => $pg['perPage'],
            'currentPage'    => $pg['page'],
        ]));
    }

    public function createContract()
    {
        $this->requireRole('super_admin','facility_manager','finance_manager');
        $facilities = $this->db->table('facilities')->where('status','active')->get()->getResultArray();
        return view('finance/create_contract', $this->viewData(['title'=>'New Contract','facilities'=>$facilities]));
    }

    public function storeContract()
    {
        $this->requireRole('super_admin','facility_manager','finance_manager');
        $rules = [
            'client_name' => 'required|max_length[200]',
            'facility_id' => 'required|integer',
            'start_date'  => 'required|valid_date',
            'end_date'    => 'required|valid_date',
            'value'       => 'required|numeric|greater_than_equal_to[0]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());
        }
        if ($this->request->getPost('end_date') <= $this->request->getPost('start_date')) {
            return redirect()->back()->withInput()->with('error','End date must be after start date.');
        }
        $num = $this->generateNumber('CON','contracts','contract_number');
        $row = [
            'contract_number' => $num,
            'facility_id'     => (int)$this->request->getPost('facility_id'),
            'unit_id'         => $this->request->getPost('unit_id') ?: null,
            'client_name'     => esc($this->request->getPost('client_name')),
            'client_email'    => esc($this->request->getPost('client_email')  ?? ''),
            'client_mobile'   => esc($this->request->getPost('client_mobile') ?? ''),
            'contract_type'   => $this->request->getPost('contract_type') ?? 'fm_services',
            'start_date'      => $this->request->getPost('start_date'),
            'end_date'        => $this->request->getPost('end_date'),
            'value'           => (float)$this->request->getPost('value'),
            'payment_terms'   => esc($this->request->getPost('payment_terms') ?? ''),
            'notes'           => esc($this->request->getPost('notes') ?? ''),
            'status'          => 'active',
            'created_by'      => session()->get('user_id'),
        ];
        if ($this->db->fieldExists('billing_frequency', 'contracts')) {
            $row['billing_frequency'] = $this->request->getPost('billing_frequency') ?? 'quarterly';
            $row['billing_day']       = (int) ($this->request->getPost('billing_day') ?? 1);
        }
        $this->db->table('contracts')->insert($row);
        $cid = (int) $this->db->insertID();
        try {
            (new \App\Services\Finance\AmcBillingService($this->db))->ensureScheduleForContract($cid);
        } catch (\Throwable $e) {
            log_message('warning', 'AMC schedule: ' . $e->getMessage());
        }
        $this->logActivity('create','contracts',$cid,"Created $num");
        return redirect()->to(base_url('finance/contracts/view/'.$cid))->with('success',"Contract $num created.");
    }

    public function viewContract(int $id)
    {
        $this->requireRole('super_admin','facility_manager','finance_manager','finance_user');
        $contract = $this->db->table('contracts c')
            ->select('c.*, f.name as facility_name, u.name as created_by_name, un.unit_number')
            ->join('facilities f','f.id=c.facility_id','left')
            ->join('users u','u.id=c.created_by','left')
            ->join('units un','un.id=c.unit_id','left')
            ->where('c.id',$id)->get()->getRowArray();
        if (!$contract) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        $invoices = $this->db->table('invoices')->where('contract_id',$id)->orderBy('issue_date','DESC')->get()->getResultArray();
        return view('finance/view_contract', $this->viewData([
            'title'    => $contract['contract_number'],
            'contract' => $contract,
            'invoices' => $invoices,
        ]));
    }

    public function editContract(int $id)
    {
        $this->requireRole('super_admin','finance_manager','facility_manager');
        $contract   = $this->db->table('contracts')->where('id',$id)->get()->getRowArray();
        if (!$contract) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        $facilities = $this->db->table('facilities')->where('status','active')->get()->getResultArray();
        return view('finance/create_contract', $this->viewData(['title'=>'Edit Contract','contract'=>$contract,'facilities'=>$facilities]));
    }

    public function updateContract(int $id)
    {
        $this->requireRole('super_admin','finance_manager','facility_manager');
        // Fix: only allow valid contracts.status ENUM values (active, expired, terminated, draft)
        $allowedStatuses = ['active', 'expired', 'terminated', 'draft'];
        $statusInput = $this->request->getPost('status');
        $status = in_array($statusInput, $allowedStatuses) ? $statusInput : 'active';
        $this->db->table('contracts')->where('id',$id)->update([
            'client_name'   => esc($this->request->getPost('client_name')),
            'client_email'  => esc($this->request->getPost('client_email')  ?? ''),
            'client_mobile' => esc($this->request->getPost('client_mobile') ?? ''),
            'contract_type' => $this->request->getPost('contract_type'),
            'start_date'    => $this->request->getPost('start_date'),
            'end_date'      => $this->request->getPost('end_date'),
            'value'         => (float)$this->request->getPost('value'),
            'payment_terms' => esc($this->request->getPost('payment_terms') ?? ''),
            'notes'         => esc($this->request->getPost('notes') ?? ''),
            'status'        => $status,
        ]);
        $this->logActivity('update','contracts',$id);
        return redirect()->to(base_url('finance/contracts/view/'.$id))->with('success','Contract updated.');
    }

    // ── Expenses ──────────────────────────────────────────────

    public function expenses()
    {
        $this->requireRole('super_admin','facility_manager','finance_manager','finance_user');
        $filterStatus   = $this->request->getGet('status')   ?? '';
        $filterFacility = (int)($this->request->getGet('facility') ?? 0);

        $pg          = $this->paginate(25);
        $currentPage = $pg['page'];
        $perPage     = $pg['perPage'];
        $offset      = $pg['offset'];

        $q = $this->db->table('expenses e')
            ->select('e.*, f.name as facility_name, u.name as created_by_name, wo.wo_number')
            ->join('facilities f','f.id=e.facility_id','left')
            ->join('users u','u.id=e.created_by','left')
            ->join('work_orders wo','wo.id=e.work_order_id','left');
        $this->scopeFacilities($q, 'e.facility_id');
        if ($filterStatus)   $q->where('e.status',$filterStatus);
        if ($filterFacility) $q->where('e.facility_id',$filterFacility);
        $total    = (clone $q)->countAllResults(false);
        $expenses = $q->orderBy('e.expense_date','DESC')->limit($perPage,$offset)->get()->getResultArray();
        $facilities = $this->db->table('facilities')->where('status','active')->get()->getResultArray();

        $summary = $this->db->query("SELECT SUM(CASE WHEN status='pending' THEN amount ELSE 0 END) pending_amt, SUM(CASE WHEN status='approved' THEN amount ELSE 0 END) approved_amt, COUNT(CASE WHEN status='pending' THEN 1 END) pending_count FROM expenses")->getRowArray();

        return view('finance/expenses', $this->viewData([
            'title'          => 'Expenses',
            'expenses'       => $expenses,
            'filterStatus'   => $filterStatus,
            'filterFacility' => $filterFacility,
            'facilities'     => $facilities,
            'summary'        => $summary,
            'totalCount'     => $total,
            'perPage'        => $perPage,
            'currentPage'    => $currentPage,
        ]));
    }

    public function createExpense()
    {
        $facilities = $this->db->table('facilities')->where('status','active')->get()->getResultArray();
        $workOrders = $this->db->table('work_orders')->whereIn('status',['in_progress','completed'])->orderBy('created_at','DESC')->limit(50)->get()->getResultArray();
        return view('finance/create_expense', $this->viewData(['title'=>'Log Expense','facilities'=>$facilities,'workOrders'=>$workOrders]));
    }

    public function storeExpense()
    {
        $rules = [
            'facility_id'  => 'required|integer',
            'description'  => 'required|min_length[5]|max_length[500]',
            'amount'       => 'required|numeric|greater_than[0]',
            'expense_date' => 'required|valid_date',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());
        }
        $this->db->table('expenses')->insert([
            'facility_id'    => (int)$this->request->getPost('facility_id'),
            'work_order_id'  => $this->request->getPost('work_order_id') ?: null,
            'category'       => $this->request->getPost('category') ?? 'other',
            'description'    => esc($this->request->getPost('description')),
            'amount'         => (float)$this->request->getPost('amount'),
            'currency'       => $this->settings['currency'] ?? 'QAR',
            'expense_date'   => $this->request->getPost('expense_date'),
            'status'         => 'pending',
            'created_by'     => session()->get('user_id'),
        ]);
        $this->logActivity('create','expenses',$this->db->insertID());
        return redirect()->to(base_url('finance/expenses'))->with('success','Expense logged and pending approval.');
    }

    public function approveExpense(int $id)
    {
        $this->requireRole('super_admin','facility_manager','finance_manager');
        $exp = $this->db->table('expenses')->where('id',$id)->get()->getRowArray();
        if (!$exp || $exp['status'] !== 'pending') {
            return redirect()->back()->with('error','Only pending expenses can be approved.');
        }
        $this->db->table('expenses')->where('id',$id)->update([
            'status'      => 'approved',
            'approved_by' => session()->get('user_id'),
            'approved_at' => date('Y-m-d H:i:s'),
        ]);
        $this->_notify($exp['created_by'],'Expense Approved',"Your expense of {$this->settings['currency']} {$exp['amount']} has been approved.",'expense',$id);
        $this->logActivity('approve','expenses',$id);
        try {
            (new \App\Services\Finance\GlPostingService($this->db))->postExpenseApproved($id, (float) $exp['amount'], (int) session()->get('user_id'));
        } catch (\Throwable $e) {
            log_message('warning', 'GL expense post: ' . $e->getMessage());
        }

        return redirect()->back()->with('success','Expense approved.');
    }

    public function rejectExpense(int $id)
    {
        $this->requireRole('super_admin','facility_manager','finance_manager');
        $exp = $this->db->table('expenses')->where('id',$id)->get()->getRowArray();
        if (!$exp || $exp['status'] !== 'pending') {
            return redirect()->back()->with('error','Only pending expenses can be rejected.');
        }
        $this->db->table('expenses')->where('id',$id)->update(['status'=>'rejected','approved_by'=>session()->get('user_id'),'approved_at'=>date('Y-m-d H:i:s')]);
        $this->_notify($exp['created_by'],'Expense Rejected',"Your expense of {$this->settings['currency']} {$exp['amount']} was rejected.",'expense',$id);
        $this->logActivity('reject','expenses',$id);
        return redirect()->back()->with('success','Expense rejected.');
    }

    // ── Petty Cash ────────────────────────────────────────────

    public function pettyCash()
    {
        $this->requireRole('super_admin','facility_manager','finance_manager','finance_user','technician','property_manager');
        $filter = $this->request->getGet('status') ?? '';
        $pg = $this->paginate(25);
        $q  = $this->db->table('petty_cash pc')
            ->select('pc.*, u.name as requested_by_name, ap.name as approved_by_name')
            ->join('users u', 'u.id=pc.requested_by', 'left')
            ->join('users ap', 'ap.id=pc.approved_by', 'left');
        $this->scopeFacilities($q, 'pc.facility_id');
        if ($filter) {
            $q->where('pc.status', $filter);
        }
        $total   = (clone $q)->countAllResults(false);
        $records = $q->orderBy('pc.created_at', 'DESC')->limit($pg['perPage'], $pg['offset'])->get()->getResultArray();
        $totalApproved = array_sum(array_map(
            fn ($r) => in_array($r['status'], ['approved','issued','reconciliation','closed'], true) ? (float) $r['amount'] : 0,
            $records
        ));

        return view('finance/petty_cash', $this->viewData([
            'title'         => 'Petty Cash',
            'records'       => $records,
            'totalApproved' => $totalApproved,
            'filterStatus'  => $filter,
            'totalCount'    => $total,
            'perPage'       => $pg['perPage'],
            'currentPage'   => $pg['page'],
        ]));
    }

    public function viewPettyCash(int $id)
    {
        $this->requireRole('super_admin','facility_manager','finance_manager','finance_user','technician','property_manager');
        $pc = $this->db->table('petty_cash pc')
            ->select('pc.*, u.name as requested_by_name, ap.name as approved_by_name, f.name as facility_name')
            ->join('users u','u.id=pc.requested_by','left')
            ->join('users ap','ap.id=pc.approved_by','left')
            ->join('facilities f','f.id=pc.facility_id','left')
            ->where('pc.id',$id)->get()->getRowArray();
        if (!$pc) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('finance/view_petty_cash', $this->viewData(['title' => $pc['pc_number'], 'pc' => $pc]));
    }

    public function createPettyCash()
    {
        $facilities = $this->db->table('facilities')->where('status','active')->get()->getResultArray();
        return view('finance/create_petty_cash', $this->viewData(['title'=>'Petty Cash Request','facilities'=>$facilities]));
    }

    public function storePettyCash()
    {
        $rules = ['amount'=>'required|numeric|greater_than[0]','purpose'=>'required|min_length[5]|max_length[500]'];
        if (!$this->validate($rules)) return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());
        $pcNum = $this->generateNumber('PC','petty_cash','pc_number');
        $this->db->table('petty_cash')->insert([
            'pc_number'    => $pcNum,
            'facility_id'  => $this->request->getPost('facility_id') ?: null,
            'requested_by' => session()->get('user_id'),
            'amount'       => (float)$this->request->getPost('amount'),
            'purpose'      => esc($this->request->getPost('purpose')),
            'category'     => $this->request->getPost('category') ?? 'general',
            'status'       => 'pending',
        ]);
        return redirect()->to(base_url('finance/petty-cash'))->with('success',"Petty cash request $pcNum submitted.");
    }

    public function approvePettyCash(int $id)
    {
        $this->requireRole('super_admin', 'facility_manager', 'finance_manager');
        $pc = $this->db->table('petty_cash')->where('id',$id)->get()->getRowArray();
        if (!$pc || $pc['status'] !== 'pending') return redirect()->back()->with('error','Only pending requests can be approved.');
        $this->db->table('petty_cash')->where('id',$id)->update(['status'=>'approved','approved_by'=>session()->get('user_id'),'approved_at'=>date('Y-m-d H:i:s')]);
        $this->_notify($pc['requested_by'],'Petty Cash Approved',"Your petty cash request of {$this->settings['currency']} {$pc['amount']} has been approved.",'petty_cash',$id);
        return redirect()->to(base_url('finance/petty-cash'))->with('success','Petty cash approved.');
    }

    public function rejectPettyCash(int $id)
    {
        $this->requireRole('super_admin', 'facility_manager', 'finance_manager');
        $pc = $this->db->table('petty_cash')->where('id', $id)->get()->getRowArray();
        if (!$pc || $pc['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Only pending requests can be rejected.');
        }
        $this->db->table('petty_cash')->where('id', $id)->update([
            'status' => 'rejected',
            'notes'  => esc($this->request->getPost('notes') ?? ''),
        ]);
        $this->_notify($pc['requested_by'], 'Petty Cash Rejected', 'Your petty cash request was rejected.', 'petty_cash', $id);

        return redirect()->to(base_url('finance/petty-cash'))->with('success', 'Petty cash rejected.');
    }

    public function issuePettyCash(int $id)
    {
        $this->requireRole('super_admin', 'finance_manager', 'finance_user');
        $pc = $this->db->table('petty_cash')->where('id', $id)->get()->getRowArray();
        if (!$pc || $pc['status'] !== 'approved') {
            return redirect()->back()->with('error', 'Only approved requests can be issued.');
        }
        $this->db->table('petty_cash')->where('id', $id)->update([
            'status'    => 'issued',
            'issued_by' => session()->get('user_id'),
            'issued_at' => date('Y-m-d H:i:s'),
        ]);
        $this->_notify($pc['requested_by'], 'Petty Cash Issued', "Funds issued for {$pc['pc_number']}.", 'petty_cash', $id);

        return redirect()->back()->with('success', 'Petty cash marked as issued.');
    }

    public function reconcilePettyCash(int $id)
    {
        $this->requireRole('super_admin', 'finance_manager', 'finance_user');
        $pc = $this->db->table('petty_cash')->where('id', $id)->get()->getRowArray();
        if (!$pc || $pc['status'] !== 'issued') {
            return redirect()->back()->with('error', 'Only issued requests can enter reconciliation.');
        }
        $receipt = $this->request->getFile('receipt');
        $update  = [
            'status'        => 'reconciliation',
            'reconciled_by' => session()->get('user_id'),
            'reconciled_at' => date('Y-m-d H:i:s'),
            'notes'         => esc($this->request->getPost('notes') ?? ''),
        ];
        if ($receipt && $receipt->isValid() && ! $receipt->hasMoved()) {
            $dir = WRITEPATH . 'uploads/petty_cash/';
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $name = 'pc_' . $id . '_' . time() . '.' . $receipt->getExtension();
            $receipt->move($dir, $name);
            $update['receipt_path'] = 'uploads/petty_cash/' . $name;
        }
        $this->db->table('petty_cash')->where('id', $id)->update($update);

        return redirect()->back()->with('success', 'Petty cash moved to reconciliation.');
    }

    public function closePettyCash(int $id)
    {
        $this->requireRole('super_admin', 'finance_manager', 'finance_user');
        $pc = $this->db->table('petty_cash')->where('id', $id)->get()->getRowArray();
        if (!$pc || $pc['status'] !== 'reconciliation') {
            return redirect()->back()->with('error', 'Only reconciliation records can be closed.');
        }
        $now = date('Y-m-d H:i:s');
        $this->db->table('petty_cash')->where('id', $id)->update([
            'status'     => 'closed',
            'closed_at'  => $now,
            'settled_at' => $now,
            'notes'      => esc($this->request->getPost('notes') ?? $pc['notes'] ?? ''),
        ]);
        $this->db->table('expenses')->insert([
            'facility_id'  => $pc['facility_id'],
            'category'     => $pc['category'] ?? 'petty_cash',
            'description'  => 'Petty Cash: ' . $pc['purpose'],
            'amount'       => $pc['amount'],
            'currency'     => $this->settings['currency'] ?? 'QAR',
            'expense_date' => date('Y-m-d'),
            'status'       => 'approved',
            'created_by'   => session()->get('user_id'),
            'approved_by'  => session()->get('user_id'),
            'approved_at'  => $now,
        ]);
        $this->logActivity('close', 'petty_cash', $id);

        return redirect()->to(base_url('finance/petty-cash'))->with('success', 'Petty cash closed and expense recorded.');
    }

    // ── Reimbursements ────────────────────────────────────────

    public function reimbursements()
    {
        $this->requireRole('super_admin','facility_manager','finance_manager','finance_user');
        $pg = $this->paginate(25);
        $q  = $this->db->table('reimbursements r')
            ->select('r.*, u.name as requested_by_name, ap.name as approved_by_name')
            ->join('users u', 'u.id=r.requested_by', 'left')
            ->join('users ap', 'ap.id=r.approved_by', 'left');
        if ($this->db->fieldExists('company_id', 'users')) {
            $this->scopeCompany($q, 'u.company_id');
        }
        $q->orderBy('r.created_at', 'DESC');
        $total   = (clone $q)->countAllResults(false);
        $records = $q->limit($pg['perPage'], $pg['offset'])->get()->getResultArray();

        return view('finance/reimbursements', $this->viewData([
            'title'       => 'Reimbursements',
            'records'     => $records,
            'totalCount'  => $total,
            'perPage'     => $pg['perPage'],
            'currentPage' => $pg['page'],
        ]));
    }

    public function createReimbursement()
    {
        $this->requireRole('super_admin', 'facility_manager', 'technician', 'finance_manager', 'finance_user');
        return view('finance/create_reimbursement', $this->viewData(['title'=>'Reimbursement Request']));
    }

    public function storeReimbursement()
    {
        $this->requireRole('super_admin', 'facility_manager', 'technician', 'finance_manager', 'finance_user');
        $rules = [
            'amount'       => 'required|numeric|greater_than[0]',
            'description'  => 'required|min_length[5]|max_length[500]',
            'expense_date' => 'required|valid_date',
        ];
        if (!$this->validate($rules)) return redirect()->back()->withInput()->with('errors',$this->validator->getErrors());
        if ($this->request->getPost('expense_date') > date('Y-m-d')) {
            return redirect()->back()->withInput()->with('error','Expense date cannot be in the future.');
        }

        $rmbNum     = $this->generateNumber('RMB','reimbursements','rmb_number');
        $receiptPath= null;
        $file       = $this->request->getFile('receipt');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $allowed = ['image/jpeg','image/png','image/webp','application/pdf'];
            if (in_array($file->getMimeType(),$allowed) && $file->getSize() <= 5*1024*1024) {
                $dir = WRITEPATH.'uploads/receipts/';
                if (!is_dir($dir)) mkdir($dir,0755,true);
                $name = bin2hex(random_bytes(12)).'.'.$file->getExtension();
                $file->move($dir,$name);
                $receiptPath = 'uploads/receipts/'.$name;
            }
        }

        $this->db->table('reimbursements')->insert([
            'rmb_number'   => $rmbNum,
            'requested_by' => session()->get('user_id'),
            'amount'       => (float)$this->request->getPost('amount'),
            'description'  => esc($this->request->getPost('description')),
            'expense_date' => $this->request->getPost('expense_date'),
            'category'     => $this->request->getPost('category') ?? 'general',
            'receipt_path' => $receiptPath,
            'status'       => 'pending',
        ]);
        return redirect()->to(base_url('finance/reimbursements'))->with('success',"Reimbursement request $rmbNum submitted.");
    }

    /** Supervisor approval: pending → approved */
    public function approveReimbursement(int $id)
    {
        $this->requireRole('super_admin', 'facility_manager', 'finance_manager');
        $r = $this->db->table('reimbursements')->where('id', $id)->get()->getRowArray();
        if (!$r || $r['status'] !== 'pending') {
            return redirect()->back()->with('error', 'Only pending claims can be approved.');
        }
        $this->db->table('reimbursements')->where('id', $id)->update([
            'status'      => 'approved',
            'approved_by' => session()->get('user_id'),
            'approved_at' => date('Y-m-d H:i:s'),
        ]);
        $this->_notify($r['requested_by'], 'Reimbursement Approved', "Your reimbursement of {$this->settings['currency']} {$r['amount']} was approved and awaits payment.", 'reimbursement', $id);
        $this->logActivity('approve', 'reimbursements', $id);

        return redirect()->to(base_url('finance/reimbursements'))->with('success', 'Reimbursement approved.');
    }

    /** Finance payment: approved → paid */
    public function payReimbursement(int $id)
    {
        $this->requireRole('super_admin', 'finance_manager');
        $r = $this->db->table('reimbursements')->where('id', $id)->get()->getRowArray();
        if (!$r || $r['status'] !== 'approved') {
            return redirect()->back()->with('error', 'Only approved reimbursements can be marked paid.');
        }
        $this->db->table('reimbursements')->where('id', $id)->update([
            'status'  => 'paid',
            'paid_at' => date('Y-m-d H:i:s'),
        ]);
        $this->_notify($r['requested_by'], 'Reimbursement Paid', "Your reimbursement of {$this->settings['currency']} {$r['amount']} has been paid.", 'reimbursement', $id);
        $this->logActivity('pay', 'reimbursements', $id);

        return redirect()->to(base_url('finance/reimbursements'))->with('success', 'Reimbursement marked as paid.');
    }

    // ── Payments (invoice collection) ─────────────────────────

    public function payments()
    {
        $this->requireRole('super_admin', 'facility_manager', 'finance_manager', 'finance_user');
        $this->_syncOverdueInvoices();

        $filterFacility = (int) ($this->request->getGet('facility') ?? 0);
        $filterType     = (string) ($this->request->getGet('type') ?? '');

        $pendingQ = $this->db->table('invoices i')
            ->select('i.*, f.name as facility_name, w.wo_number')
            ->join('facilities f', 'f.id=i.facility_id', 'left')
            ->join('work_orders w', 'w.id=i.work_order_id', 'left')
            ->whereIn('i.status', \App\Services\PaymentService::COLLECTIBLE_STATUSES);
        $this->scopeFacilities($pendingQ, 'i.facility_id');
        if ($filterFacility) {
            $pendingQ->where('i.facility_id', $filterFacility);
        }
        if ($filterType !== '') {
            $pendingQ->where('i.invoice_type', $filterType);
        }
        $candidates = $pendingQ->orderBy('i.due_date', 'ASC')->limit(100)->get()->getResultArray();
        $ps = $this->paymentService();
        $pending = [];
        $outstandingBalance = 0.0;
        foreach ($candidates as $row) {
            $paid = $ps->getPaidTotal((int) $row['id']);
            $balance = round(max(0, (float) $row['total'] - $paid), 2);
            if ($balance < 0.01) {
                continue;
            }
            $row['paid_total']  = $paid;
            $row['balance_due'] = $balance;
            $pending[] = $row;
            $outstandingBalance += $balance;
        }

        $paidQ = $this->db->table('invoices i')
            ->select('i.*, f.name as facility_name, u.name as paid_by_name')
            ->join('facilities f', 'f.id=i.facility_id', 'left')
            ->join('users u', 'u.id=i.paid_by', 'left')
            ->where('i.status', 'paid');
        $this->scopeFacilities($paidQ, 'i.facility_id');
        $paidRecent = $paidQ->orderBy('i.paid_at', 'DESC')->limit(20)->get()->getResultArray();

        [$facSql, $facParams] = $this->sqlFacilityFilter('');
        $stats = $this->db->query("
            SELECT
              SUM(CASE WHEN status='paid' AND YEAR(paid_at)=YEAR(CURDATE()) THEN total ELSE 0 END) AS collected_ytd
            FROM invoices
            WHERE 1=1 {$facSql}
        ", $facParams)->getRowArray();
        $stats['outstanding']     = $outstandingBalance;
        $stats['pending_count']   = count($pending);

        $facilities = $this->db->table('facilities')->where('status', 'active')->get()->getResultArray();

        return view('finance/payments', $this->viewData([
            'title'            => 'Payments',
            'pending'          => $pending,
            'paidRecent'       => $paidRecent,
            'stats'            => $stats,
            'canManagePayments'=> $this->canManageFinancePayments(),
            'facilities'       => $facilities,
            'filterFacility'   => $filterFacility,
            'filterType'       => $filterType,
        ]));
    }

    public function recordPayment(int $id)
    {
        $this->requireRole('super_admin', 'facility_manager', 'finance_manager');
        if (! $this->request->is('post')) {
            return redirect()->back()->with('error', 'Invalid request.');
        }

        $inv = $this->db->table('invoices')->where('id', $id)->get()->getRowArray();
        if (! $inv) {
            return redirect()->back()->with('error', 'Invoice not found.');
        }
        $this->assertFacilityAccess((int) $inv['facility_id']);

        if (! \App\Services\PaymentService::canAcceptPayment((string) $inv['status'])) {
            return redirect()->back()->with('error', 'This invoice cannot accept payments in its current status.');
        }

        $paidSoFar = $this->paymentService()->getPaidTotal($id);
        $balance   = round(max(0, (float) $inv['total'] - $paidSoFar), 2);
        $amount    = round((float) $this->request->getPost('amount'), 2);
        if ($amount <= 0 || $amount > $balance + 0.01) {
            return redirect()->back()->with('error', "Payment must be between 0.01 and {$balance}.");
        }

        $method = (string) ($this->request->getPost('payment_method') ?? 'bank');
        if (! in_array($method, ['cash', 'bank', 'card', 'cheque', 'online'], true)) {
            $method = 'bank';
        }

        try {
            $result = $this->paymentService()->recordPayment(
                $id,
                $amount,
                $method,
                (int) session()->get('user_id'),
                $this->request->getPost('reference_no'),
                $this->request->getPost('notes')
            );
            $this->logActivity('pay', 'invoices', $id, 'Payment ' . $amount . ' — status ' . $result['status']);

            $redirectTo = (string) ($this->request->getPost('redirect_to') ?? '');
            if ($redirectTo !== '' && str_starts_with($redirectTo, base_url())) {
                return redirect()->to($redirectTo)
                    ->with('success', 'Payment recorded. Balance: ' . $result['balance']);
            }

            return redirect()->to(base_url('finance/invoices/view/' . $id))
                ->with('success', 'Payment recorded. Balance: ' . $result['balance']);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /** Cash flow — money in/out with filters */
    public function cashFlow()
    {
        $this->requireRole('super_admin', 'facility_manager', 'finance_manager', 'finance_user');

        $from           = $this->request->getGet('from') ?? date('Y-m-01');
        $to             = $this->request->getGet('to') ?? date('Y-m-d');
        $filterFacility = (int) ($this->request->getGet('facility') ?? 0);
        $flow           = (string) ($this->request->getGet('flow') ?? 'all');
        $sourceType     = (string) ($this->request->getGet('source') ?? 'all');
        $payMethod      = (string) ($this->request->getGet('method') ?? '');
        $invoiceType    = (string) ($this->request->getGet('invoice_type') ?? '');

        if (! in_array($flow, ['all', 'in', 'out'], true)) {
            $flow = 'all';
        }

        $rows       = [];
        $facilities = $this->db->table('facilities')->where('status', 'active')->get()->getResultArray();

        $includeIn  = $flow === 'all' || $flow === 'in';
        $includeOut = $flow === 'all' || $flow === 'out';

        if ($includeIn && ($sourceType === 'all' || $sourceType === 'invoice_payment')) {
            if ($this->db->tableExists('invoice_payments')) {
                $q = $this->db->table('invoice_payments p')
                    ->select('p.paid_at AS entry_date, p.amount, p.payment_method, p.reference_no, p.notes,
                        i.invoice_number, i.invoice_type, i.work_order_id, f.name AS facility_name, f.id AS facility_id')
                    ->join('invoices i', 'i.id = p.invoice_id')
                    ->join('facilities f', 'f.id = i.facility_id', 'left')
                    ->where('DATE(p.paid_at) >=', $from)
                    ->where('DATE(p.paid_at) <=', $to);
                $this->scopeFacilities($q, 'i.facility_id');
                if ($filterFacility) {
                    $q->where('i.facility_id', $filterFacility);
                }
                if ($payMethod !== '') {
                    $q->where('p.payment_method', $payMethod);
                }
                if ($invoiceType !== '') {
                    $q->where('i.invoice_type', $invoiceType);
                }
                foreach ($q->orderBy('p.paid_at', 'DESC')->get()->getResultArray() as $r) {
                    $rows[] = [
                        'entry_date'     => $r['entry_date'],
                        'flow'           => 'in',
                        'source'         => 'invoice_payment',
                        'source_label'   => 'Customer payment',
                        'ref_no'         => $r['invoice_number'],
                        'facility_name'  => $r['facility_name'] ?? '',
                        'payment_method' => $r['payment_method'] ?? '',
                        'amount'         => (float) $r['amount'],
                        'detail'         => trim(($r['notes'] ?? '') . ' ' . ($r['reference_no'] ?? '')),
                        'invoice_type'   => $r['invoice_type'] ?? '',
                    ];
                }
            }
        }

        if ($includeOut && ($sourceType === 'all' || $sourceType === 'expense') && $payMethod === '') {
            $q = $this->db->table('expenses e')
                ->select('e.expense_date AS entry_date, e.amount, e.category, e.description,
                    CONCAT("EXP-", e.id) AS ref_no, f.name AS facility_name')
                ->join('facilities f', 'f.id = e.facility_id', 'left')
                ->where('e.status', 'approved')
                ->where('DATE(e.expense_date) >=', $from)
                ->where('DATE(e.expense_date) <=', $to);
            $this->scopeFacilities($q, 'e.facility_id');
            if ($filterFacility) {
                $q->where('e.facility_id', $filterFacility);
            }
            foreach ($q->orderBy('e.expense_date', 'DESC')->get()->getResultArray() as $r) {
                $rows[] = [
                    'entry_date'     => $r['entry_date'],
                    'flow'           => 'out',
                    'source'         => 'expense',
                    'source_label'   => 'Expense',
                    'ref_no'         => $r['ref_no'],
                    'facility_name'  => $r['facility_name'] ?? '',
                    'payment_method' => $r['payment_method'] ?? '',
                    'amount'         => (float) $r['amount'],
                    'detail'         => ($r['category'] ?? '') . ' — ' . ($r['description'] ?? ''),
                    'invoice_type'   => '',
                ];
            }
        }

        if ($includeOut && ($sourceType === 'all' || $sourceType === 'reimbursement') && $payMethod === '') {
            $q = $this->db->table('reimbursements r')
                ->select('COALESCE(r.paid_at, r.approved_at, r.created_at) AS entry_date, r.amount, r.description,
                    r.rmb_number AS ref_no, u.name AS facility_name')
                ->join('users u', 'u.id = r.requested_by', 'left')
                ->where('r.status', 'paid')
                ->where('DATE(COALESCE(r.paid_at, r.approved_at)) >=', $from)
                ->where('DATE(COALESCE(r.paid_at, r.approved_at)) <=', $to);
            foreach ($q->orderBy('entry_date', 'DESC')->get()->getResultArray() as $r) {
                $rows[] = [
                    'entry_date'     => $r['entry_date'],
                    'flow'           => 'out',
                    'source'         => 'reimbursement',
                    'source_label'   => 'Reimbursement',
                    'ref_no'         => $r['ref_no'],
                    'facility_name'  => '',
                    'payment_method' => '',
                    'amount'         => (float) $r['amount'],
                    'detail'         => $r['description'] ?? '',
                    'invoice_type'   => '',
                ];
            }
        }

        if ($includeOut && ($sourceType === 'all' || $sourceType === 'petty_cash') && ($payMethod === '' || $payMethod === 'cash')) {
            $q = $this->db->table('petty_cash pc')
                ->select('COALESCE(pc.issued_at, pc.approved_at) AS entry_date, pc.amount, pc.purpose, pc.category,
                    pc.pc_number AS ref_no, f.name AS facility_name')
                ->join('facilities f', 'f.id = pc.facility_id', 'left')
                ->whereIn('pc.status', ['issued', 'reconciliation', 'closed'])
                ->where('DATE(COALESCE(pc.issued_at, pc.approved_at)) >=', $from)
                ->where('DATE(COALESCE(pc.issued_at, pc.approved_at)) <=', $to);
            $this->scopeFacilities($q, 'pc.facility_id');
            if ($filterFacility) {
                $q->where('pc.facility_id', $filterFacility);
            }
            foreach ($q->orderBy('entry_date', 'DESC')->get()->getResultArray() as $r) {
                $rows[] = [
                    'entry_date'     => $r['entry_date'],
                    'flow'           => 'out',
                    'source'         => 'petty_cash',
                    'source_label'   => 'Petty cash',
                    'ref_no'         => $r['ref_no'],
                    'facility_name'  => $r['facility_name'] ?? '',
                    'payment_method' => 'cash',
                    'amount'         => (float) $r['amount'],
                    'detail'         => ($r['category'] ?? '') . ' — ' . ($r['purpose'] ?? ''),
                    'invoice_type'   => '',
                ];
            }
        }

        usort($rows, fn ($a, $b) => strcmp((string) $b['entry_date'], (string) $a['entry_date']));

        $totalIn  = array_sum(array_map(fn ($r) => $r['flow'] === 'in' ? $r['amount'] : 0, $rows));
        $totalOut = array_sum(array_map(fn ($r) => $r['flow'] === 'out' ? $r['amount'] : 0, $rows));

        return view('finance/cash_flow', $this->viewData([
            'title'          => 'Cash Flow',
            'rows'           => $rows,
            'from'           => $from,
            'to'             => $to,
            'filterFacility' => $filterFacility,
            'flow'           => $flow,
            'sourceType'     => $sourceType,
            'payMethod'      => $payMethod,
            'invoiceType'    => $invoiceType,
            'totalIn'        => $totalIn,
            'totalOut'       => $totalOut,
            'netCash'        => $totalIn - $totalOut,
            'facilities'     => $facilities,
        ]));
    }

    // ── Ledger (income / expense audit trail) ─────────────────

    public function ledger()
    {
        $this->requireRole('super_admin', 'finance_manager', 'finance_user');
        $from = $this->request->getGet('from') ?? date('Y-m-01');
        $to   = $this->request->getGet('to')   ?? date('Y-m-d');

        [$facSql, $facParams] = $this->sqlFacilityFilter('i');
        $income = $this->db->query("
            SELECT 'income' AS entry_type, i.invoice_number AS ref_no, i.issue_date AS entry_date,
                   i.total AS amount, f.name AS facility_name, i.status, 'Invoice' AS category
            FROM invoices i
            LEFT JOIN facilities f ON f.id = i.facility_id
            WHERE i.status = 'paid' AND i.issue_date BETWEEN ? AND ? {$facSql}
            ORDER BY i.issue_date DESC
        ", array_merge([$from, $to], $facParams))->getResultArray();

        [$facSqlE, $facParamsE] = $this->sqlFacilityFilter('e');
        $expense = $this->db->query("
            SELECT 'expense' AS entry_type, CONCAT('EXP-', e.id) AS ref_no, e.expense_date AS entry_date,
                   e.amount, f.name AS facility_name, e.status, e.category
            FROM expenses e
            LEFT JOIN facilities f ON f.id = e.facility_id
            WHERE e.status = 'approved' AND e.expense_date BETWEEN ? AND ? {$facSqlE}
            ORDER BY e.expense_date DESC
        ", array_merge([$from, $to], $facParamsE))->getResultArray();

        $entries = array_merge($income, $expense);
        usort($entries, fn ($a, $b) => strcmp($b['entry_date'], $a['entry_date']));

        $totalIncome  = array_sum(array_map(fn ($e) => $e['entry_type'] === 'income' ? (float) $e['amount'] : 0, $entries));
        $totalExpense = array_sum(array_map(fn ($e) => $e['entry_type'] === 'expense' ? (float) $e['amount'] : 0, $entries));

        return view('finance/ledger', $this->viewData([
            'title'        => 'Finance Ledger',
            'entries'      => $entries,
            'from'         => $from,
            'to'           => $to,
            'totalIncome'  => $totalIncome,
            'totalExpense' => $totalExpense,
            'netProfit'    => $totalIncome - $totalExpense,
        ]));
    }

    // ── Private Helpers ───────────────────────────────────────

    /**
     * Auto-mark invoices as overdue when due_date has passed.
     * Runs on every finance page load — lightweight (index-only query).
     */
    private function paymentService(): \App\Services\PaymentService
    {
        return new \App\Services\PaymentService($this->db);
    }

    private function _syncOverdueInvoices(): void
    {
        if (cache()->get('fm_sync_overdue_invoices')) {
            return;
        }
        try {
            $this->db->query("
                UPDATE invoices
                SET status='overdue'
                WHERE status='sent'
                  AND due_date < CURDATE()
            ");
            cache()->save('fm_sync_overdue_invoices', 1, 900);
        } catch (\Throwable $e) {}
    }

    /**
     * Auto-expire contracts past end_date.
     */
    private function _syncExpiredContracts(): void
    {
        if (cache()->get('fm_sync_expired_contracts')) {
            return;
        }
        try {
            $this->db->query("
                UPDATE contracts
                SET status='expired'
                WHERE status='active'
                  AND end_date < CURDATE()
            ");
            $this->db->query("
                UPDATE units u
                JOIN contracts c ON c.unit_id=u.id
                SET u.status='vacant'
                WHERE c.status='expired'
                  AND c.end_date < DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                  AND u.status='occupied'
            ");
            cache()->save('fm_sync_expired_contracts', 1, 900);
        } catch (\Throwable $e) {}
    }

    // ----------------------------------------------------------
    // Accounts Payable (fixes /finance/accounts-payable 404)
    // ----------------------------------------------------------

    public function accountsPayable()
    {
        $this->requireRole(['super_admin', 'facility_manager', 'finance_manager', 'finance_user', 'procurement_officer']);

        $from   = $this->request->getGet('from')   ?? date('Y-m-01');
        $to     = $this->request->getGet('to')     ?? date('Y-m-d');
        $status = $this->request->getGet('status') ?? '';

        // purchase_orders is the canonical table; payment_status + due_date
        // columns are added by patch_procurement_orders.sql if missing.
        $poTable = $this->db->tableExists('purchase_orders') ? 'purchase_orders' : 'procurement_orders';
        $hasPmtStatus = $this->db->fieldExists('payment_status', 'purchase_orders');
        $hasDueDate   = $this->db->fieldExists('due_date', 'purchase_orders');
        $hasPmtTable  = $this->db->tableExists('po_payments');

        $selectCols = "po.id, po.po_number, po.total_amount, po.created_at, v.name AS vendor_name";
        $selectCols .= $hasPmtStatus ? ", po.payment_status" : ", po.status AS payment_status";
        $selectCols .= $hasDueDate   ? ", po.due_date"       : ", po.delivery_date AS due_date";
        $selectCols .= $hasPmtTable  ? ", COALESCE(SUM(pm.amount), 0) AS paid_amount" : ", 0 AS paid_amount";

        $q = $this->db->table("{$poTable} po")
            ->select($selectCols)
            ->join('vendors v', 'v.id = po.vendor_id', 'left');

        if ($hasPmtTable) {
            $q->join('po_payments pm', 'pm.po_id = po.id', 'left');
        }

        $q->where('DATE(po.created_at) >=', $from)
          ->where('DATE(po.created_at) <=', $to)
          ->groupBy('po.id');

        if ($status && $hasPmtStatus) $q->where('po.payment_status', $status);

        $payables = $q->orderBy($hasDueDate ? 'po.due_date' : 'po.delivery_date', 'ASC')->get()->getResultArray();

        $totalDue    = array_sum(array_column($payables, 'total_amount'));
        $totalPaid   = array_sum(array_column($payables, 'paid_amount'));
        $outstanding = $totalDue - $totalPaid;

        return view('finance/accounts_payable', $this->viewData([
            'title'       => 'Accounts Payable',
            'payables'    => $payables,
            'totalDue'    => $totalDue,
            'totalPaid'   => $totalPaid,
            'outstanding' => $outstanding,
            'from'        => $from,
            'to'          => $to,
            'status'      => $status,
            'currency'    => $this->settings['currency'] ?? 'QAR',
        ]));
    }

    public function payAccountsPayable(int $poId)
    {
        $this->requireRole(['super_admin', 'finance_manager']);
        $amount = (float)($this->request->getPost('amount') ?? 0);
        $method = $this->request->getPost('method') ?? 'bank_transfer';
        $ref    = $this->request->getPost('reference') ?? '';

        if ($amount <= 0) return redirect()->back()->with('error', 'Invalid amount.');

        $this->db->table('po_payments')->insert([
            'po_id'      => $poId,
            'amount'     => $amount,
            'method'     => $method,
            'reference'  => $ref,
            'paid_by'    => session()->get('user_id'),
            'paid_at'    => date('Y-m-d H:i:s'),
        ]);

        // Update payment_status on po
        $po     = $this->db->table('purchase_orders')->where('id', $poId)->get()->getRowArray();
        $paid   = (float)$this->db->table('po_payments')->where('po_id', $poId)->selectSum('amount', 's')->get()->getRowArray()['s'];
        $newSt  = $paid >= (float)($po['total_amount'] ?? 0) ? 'paid' : 'partial';
        if ($this->db->fieldExists('payment_status', 'purchase_orders')) { $this->db->table('purchase_orders')->where('id', $poId)->update(['payment_status' => $newSt]); }

        $this->logActivity('ap_payment', 'purchase_orders', $poId, 'Payment recorded: ' . $amount);
        return redirect()->to(base_url('finance/accounts-payable'))->with('success', 'Payment recorded.');
    }

    private function _notify(int $userId, string $title, string $message, string $type, int $refId): void
    {
        try {
            $this->db->table('notifications')->insert([
                'user_id'=>$userId,'title'=>$title,'message'=>$message,'type'=>$type,'reference_id'=>$refId,
            ]);
        } catch (\Throwable $e) {}
    }
}
