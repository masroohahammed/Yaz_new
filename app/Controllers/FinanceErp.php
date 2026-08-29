<?php

namespace App\Controllers;

use App\Services\Finance\AmcBillingService;
use App\Services\Finance\FinanceModuleRegistry;
use App\Services\Finance\GlReportService;

/**
 * Finance ERP hub — COA, GL, AP, AMC billing, budgets, integration map.
 */
class FinanceErp extends BaseController
{
    protected ?string $workspaceRequired = 'pm';
    public function hub()
    {
        return redirect()->to(base_url('settings/finance-module'));
    }

    public function coa()
    {
        $this->requireRole('super_admin', 'finance_manager');
        $groups   = [];
        $accounts = [];
        if ($this->db->tableExists('finance_account_groups')) {
            $groups   = $this->db->table('finance_account_groups')->orderBy('sort_order')->get()->getResultArray();
            $accounts = $this->db->table('finance_accounts fa')
                ->select('fa.*, fg.name as group_name, fg.account_type')
                ->join('finance_account_groups fg', 'fg.id = fa.group_id', 'left')
                ->orderBy('fa.code')
                ->get()->getResultArray();
        }

        return view('finance/coa', $this->viewData([
            'title'    => 'Chart of Accounts',
            'groups'   => $groups,
            'accounts' => $accounts,
        ]));
    }

    public function gl()
    {
        $this->requireRole('super_admin', 'finance_manager');
        $entries = [];
        if ($this->db->tableExists('finance_journal_entries')) {
            $entries = $this->db->table('finance_journal_entries')
                ->orderBy('entry_date', 'DESC')
                ->orderBy('id', 'DESC')
                ->limit(100)
                ->get()->getResultArray();
        }

        return view('finance/gl', $this->viewData([
            'title'   => 'General Ledger',
            'entries' => $entries,
        ]));
    }

    public function vendorBills()
    {
        $this->requireRole('super_admin', 'finance_manager', 'finance_user');
        $bills    = [];
        $matchSvc = new \App\Services\ProcurementThreeWayMatchService($this->db);
        if ($this->db->tableExists('finance_vendor_bills')) {
            $q = $this->db->table('finance_vendor_bills vb')
                ->select('vb.*, v.name as vendor_name, po.po_number, po.id as po_id, tm.match_status as three_way_status')
                ->join('vendors v', 'v.id = vb.vendor_id', 'left')
                ->join('purchase_orders po', 'po.id = vb.purchase_order_id', 'left');
            if ($this->db->tableExists('procurement_three_way_matches')) {
                $q->join('procurement_three_way_matches tm', 'tm.po_id = po.id', 'left');
            }
            $bills = $q->orderBy('vb.id', 'DESC')->limit(100)->get()->getResultArray();
            foreach ($bills as &$b) {
                $poId = (int) ($b['po_id'] ?? 0);
                if ($poId > 0) {
                    $check                 = $matchSvc->paymentAllowed($poId);
                    $b['can_pay']          = $check['allowed'] && in_array($b['status'], ['approved', 'pending'], true);
                    $b['pay_block_reason'] = $check['reason'];
                } else {
                    $b['can_pay']          = in_array($b['status'], ['approved', 'pending'], true);
                    $b['pay_block_reason'] = '';
                }
            }
            unset($b);
        }

        return view('finance/vendor_bills', $this->viewData([
            'title' => 'Accounts Payable — Vendor Bills',
            'bills' => $bills,
        ]));
    }

    public function payVendorBill(int $id)
    {
        $this->requireRole('super_admin', 'finance_manager');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }
        $bill = $this->db->table('finance_vendor_bills')->where('id', $id)->get()->getRowArray();
        if (! $bill) {
            return redirect()->back()->with('error', 'Vendor bill not found.');
        }
        if (in_array($bill['status'], ['paid', 'cancelled'], true)) {
            return redirect()->back()->with('error', 'Bill is already ' . $bill['status'] . '.');
        }

        $poId = (int) ($bill['purchase_order_id'] ?? 0);
        if ($poId > 0) {
            $check = (new \App\Services\ProcurementThreeWayMatchService($this->db))->paymentAllowed($poId);
            if (! $check['allowed']) {
                return redirect()->back()->with('error', $check['reason']);
            }
        }

        $this->db->table('finance_vendor_bills')->where('id', $id)->update([
            'status' => 'paid',
            'notes'  => trim(($bill['notes'] ?? '') . "\nPaid " . date('Y-m-d H:i') . ' ref: ' . ($this->request->getPost('reference_no') ?? '')),
        ]);
        $this->logActivity('pay', 'finance_vendor_bills', $id, 'Vendor bill paid');

        return redirect()->to(base_url('finance/vendor-bills'))->with('success', 'Vendor bill marked as paid.');
    }

    public function amcBilling()
    {
        $this->requireRole('super_admin', 'finance_manager', 'facility_manager');
        $schedules = [];
        if ($this->db->tableExists('finance_amc_schedules')) {
            $schedules = $this->db->table('finance_amc_schedules s')
                ->select('s.*, c.contract_number, c.client_name, f.name as facility_name')
                ->join('contracts c', 'c.id = s.contract_id')
                ->join('facilities f', 'f.id = c.facility_id', 'left')
                ->orderBy('s.next_bill_date')
                ->get()->getResultArray();
        }

        return view('finance/amc_billing', $this->viewData([
            'title'     => 'AMC Contract Billing',
            'schedules' => $schedules,
        ]));
    }

    public function runAmcBilling()
    {
        $this->requireRole('super_admin', 'finance_manager');
        $n = (new AmcBillingService($this->db))->processDueSchedules((int) session()->get('user_id'));

        return redirect()->to(base_url('finance/amc-billing'))->with('success', "{$n} AMC invoice(s) generated.");
    }

    public function budgets()
    {
        $this->requireRole('super_admin', 'finance_manager');
        $budgets = [];
        if ($this->db->tableExists('finance_budgets')) {
            $budgets = $this->db->table('finance_budgets')->orderBy('fiscal_year', 'DESC')->get()->getResultArray();
        }

        return view('finance/budgets', $this->viewData([
            'title'   => 'Budget Management',
            'budgets' => $budgets,
        ]));
    }

    public function createBudget()
    {
        $this->requireRole('super_admin', 'finance_manager');
        if (! $this->db->tableExists('finance_budgets')) {
            return redirect()->to(base_url('finance/budgets'))->with('error', 'Run finance ERP migration first.');
        }

        return view('finance/create_budget', $this->viewData(['title' => 'New Budget']));
    }

    public function storeBudget()
    {
        $this->requireRole('super_admin', 'finance_manager');
        $name = trim((string) $this->request->getPost('name'));
        $year = (int) $this->request->getPost('fiscal_year');
        $total = (float) $this->request->getPost('total_amount');
        if ($name === '' || $year < 2000) {
            return redirect()->back()->with('error', 'Name and fiscal year are required.');
        }
        $this->db->table('finance_budgets')->insert([
            'name'         => $name,
            'fiscal_year'  => $year,
            'total_amount' => $total,
            'status'       => 'draft',
            'created_by'   => session()->get('user_id'),
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
        $id = (int) $this->db->insertID();
        $categories = (array) ($this->request->getPost('categories') ?? []);
        $amounts    = (array) ($this->request->getPost('amounts') ?? []);
        if ($this->db->tableExists('finance_budget_lines')) {
            foreach ($categories as $i => $cat) {
                $cat = trim((string) $cat);
                $amt = (float) ($amounts[$i] ?? 0);
                if ($cat === '' && $amt <= 0) {
                    continue;
                }
                $this->db->table('finance_budget_lines')->insert([
                    'budget_id' => $id,
                    'category'  => $cat ?: 'General',
                    'amount'    => $amt,
                ]);
            }
        }
        $this->logActivity('create', 'finance_budgets', $id, $name);

        return redirect()->to(base_url('finance/budgets'))->with('success', 'Budget created.');
    }

    public function trialBalance()
    {
        $this->requireRole('super_admin', 'finance_manager');
        $asOf  = $this->request->getGet('as_of') ?? date('Y-m-d');
        $svc   = new GlReportService($this->db);
        $rows  = $svc->trialBalance($asOf);
        $totalDebit  = array_sum(array_column($rows, 'debit'));
        $totalCredit = array_sum(array_column($rows, 'credit'));

        return view('finance/trial_balance', $this->viewData([
            'title'       => 'Trial Balance',
            'rows'        => $rows,
            'asOf'        => $asOf,
            'totalDebit'  => $totalDebit,
            'totalCredit' => $totalCredit,
            'glEnabled'   => $svc->isEnabled(),
        ]));
    }

    public function balanceSheet()
    {
        $this->requireRole('super_admin', 'finance_manager');
        $asOf = $this->request->getGet('as_of') ?? date('Y-m-d');
        $svc  = new GlReportService($this->db);
        $data = $svc->balanceSheet($asOf);

        return view('finance/balance_sheet', $this->viewData([
            'title'     => 'Balance Sheet',
            'asOf'      => $asOf,
            'sheet'     => $data,
            'glEnabled' => $svc->isEnabled(),
        ]));
    }

    public function arAging()
    {
        $this->requireRole('super_admin', 'finance_manager', 'finance_user');
        $rows = (new GlReportService($this->db))->arAging();
        $buckets = ['current' => 0.0, '1-30' => 0.0, '31-60' => 0.0, '61-90' => 0.0, '90+' => 0.0];
        foreach ($rows as $r) {
            $buckets[$r['bucket']] = ($buckets[$r['bucket']] ?? 0) + (float) $r['total'];
        }

        return view('finance/ar_aging', $this->viewData([
            'title'   => 'AR Aging',
            'rows'    => $rows,
            'buckets' => $buckets,
        ]));
    }

    public function bankReconciliation()
    {
        $this->requireRole('super_admin', 'finance_manager');
        $from = $this->request->getGet('from') ?? date('Y-m-01');
        $to   = $this->request->getGet('to') ?? date('Y-m-d');
        $act  = (new GlReportService($this->db))->bankActivity($from, $to);
        $bankAccounts = [];
        if ($this->db->tableExists('finance_bank_accounts')) {
            $bankAccounts = $this->db->table('finance_bank_accounts')->where('is_active', 1)->get()->getResultArray();
        }

        return view('finance/bank_reconciliation', $this->viewData([
            'title'        => 'Bank Reconciliation',
            'from'         => $from,
            'to'           => $to,
            'activity'     => $act,
            'bankAccounts' => $bankAccounts,
        ]));
    }

    public function payrollFinance()
    {
        $this->requireRole('super_admin', 'finance_manager');
        $employees = $this->db->table('users u')
            ->select('u.id, u.name, u.email, r.name as role_name')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->whereIn('r.name', ['technician', 'supervisor', 'facility_manager'])
            ->orderBy('u.name')
            ->limit(200)
            ->get()
            ->getResultArray();

        return view('finance/payroll_finance', $this->viewData([
            'title'     => 'Payroll & HR Finance',
            'employees' => $employees,
        ]));
    }

    public function reports()
    {
        $this->requireRole('super_admin', 'finance_manager', 'finance_user');

        return view('finance/reports_hub', $this->viewData(['title' => 'Financial Reports']));
    }

    public function integrationLog()
    {
        $this->requireRole('super_admin', 'finance_manager');
        $logs = [];
        if ($this->db->tableExists('finance_integration_log')) {
            $logs = $this->db->table('finance_integration_log')
                ->orderBy('id', 'DESC')
                ->limit(80)
                ->get()->getResultArray();
        }

        return view('finance/integration_log', $this->viewData([
            'title' => 'Finance Integration Log',
            'logs'  => $logs,
        ]));
    }
}
