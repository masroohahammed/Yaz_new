<?php

namespace App\Controllers;

use App\Services\Finance\FinanceLedgerService;
use App\Services\Finance\PettyCashService;
use App\Services\RbacService;

/**
 * Petty Cash Management — accounts, expenses, advances, replenishment, counts, reconciliation.
 */
class FinancePettyCash extends BaseController
{
    protected ?string $workspaceRequired = null;

    private PettyCashService $petty;
    private FinanceLedgerService $ledger;
    private RbacService $rbac;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->petty  = new PettyCashService($this->db);
        $this->ledger = new FinanceLedgerService($this->db);
        $this->rbac   = new RbacService($this->db);
    }

    private function requirePerm(string $perm): void
    {
        $role = (string) session()->get('user_role');
        if (! $this->rbac->can($role, $perm)) {
            session()->setFlashdata('error', 'You do not have permission for this petty cash action.');
            redirect()->to(base_url('dashboard'))->send();
            exit;
        }
    }

    private function can(string $perm): bool
    {
        return $this->rbac->can((string) session()->get('user_role'), $perm);
    }

    // ── Dashboard ─────────────────────────────────────────────────────────────

    public function index()
    {
        $this->requirePerm('petty_cash.view');
        $userId    = $this->currentUser()['id'];
        $custodian = $this->can('petty_cash.create') && ! $this->can('petty_cash.approve') ? $userId : null;
        $kpis      = $this->petty->dashboardKpis($custodian);
        $accounts  = $this->petty->accounts($this->scopeFilters());

        foreach ($accounts as &$a) {
            $a['summary'] = $this->petty->accountSummary((int) $a['id']);
            $a['needs_replenishment'] = ($a['replenishment_level'] ?? 0) > 0
                && (float) $a['current_balance'] <= (float) $a['replenishment_level'];
        }
        unset($a);

        return view('finance_petty/dashboard', $this->viewData([
            'title'    => 'Petty Cash Dashboard',
            'kpis'     => $kpis,
            'accounts' => $accounts,
        ]));
    }

    // ── Accounts ──────────────────────────────────────────────────────────────

    public function accounts()
    {
        $this->requirePerm('petty_cash.view');
        $accounts = $this->petty->accounts($this->scopeFilters());

        return view('finance_petty/accounts', $this->viewData([
            'title'     => 'Petty Cash Accounts',
            'accounts'  => $accounts,
            'canCreate' => $this->can('petty_cash.create'),
        ]));
    }

    public function accountCreate()
    {
        $this->requirePerm('petty_cash.create');

        return view('finance_petty/account_form', $this->viewData([
            'title'      => 'New Petty Cash Account',
            'account'    => null,
            'branches'   => $this->lookupBranches(),
            'facilities' => $this->lookupFacilities(),
            'users'      => $this->lookupUsers(),
        ]));
    }

    public function accountStore()
    {
        $this->requirePerm('petty_cash.create');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        $user = $this->currentUser();
        $id   = $this->petty->createAccount([
            'name'                  => trim((string) $this->request->getPost('name')),
            'account_code'          => trim((string) $this->request->getPost('account_code')),
            'branch_id'             => $this->request->getPost('branch_id') ?: null,
            'facility_id'           => $this->request->getPost('facility_id') ?: null,
            'department'            => trim((string) $this->request->getPost('department')),
            'currency'              => trim((string) $this->request->getPost('currency')) ?: 'QAR',
            'custodian_user_id'     => $this->request->getPost('custodian_user_id') ?: null,
            'opening_balance'       => (float) $this->request->getPost('opening_balance'),
            'opening_balance_date'  => $this->request->getPost('opening_balance_date') ?: date('Y-m-d'),
            'max_cash_limit'        => $this->request->getPost('max_cash_limit') ?: null,
            'replenishment_level'   => $this->request->getPost('replenishment_level') ?: null,
            'notes'                 => trim((string) $this->request->getPost('notes')),
            'company_id'            => $user['company_id'],
        ], $user['id']);

        $this->logActivity('create', 'finance_petty_cash_accounts', $id, 'Petty cash account created');

        return redirect()->to(base_url('finance-petty/accounts/view/' . $id))->with('success', 'Petty cash account created.');
    }

    public function accountView(int $id)
    {
        $this->requirePerm('petty_cash.view');
        $account = $this->petty->account($id);
        if (! $account) {
            return redirect()->to(base_url('finance-petty/accounts'))->with('error', 'Account not found.');
        }

        $txs = $this->ledger->listTransactions([
            'account_type' => 'petty',
            'account_id'   => $id,
            'status'       => 'posted',
        ], 50);

        return view('finance_petty/account_view', $this->viewData([
            'title'   => $account['name'],
            'account' => $account,
            'summary' => $this->petty->accountSummary($id),
            'txs'     => $txs,
            'canEdit' => $this->can('petty_cash.edit'),
            'users'   => $this->lookupUsers(),
        ]));
    }

    public function transferCustodian(int $id)
    {
        $this->requirePerm('petty_cash.edit');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        $user = $this->currentUser();
        $ok   = $this->petty->transferCustodian($id, (int) $this->request->getPost('custodian_user_id'), $user['id'], trim((string) $this->request->getPost('reason')));

        return redirect()->back()->with($ok ? 'success' : 'error', $ok ? 'Custodian updated.' : 'Could not update custodian.');
    }

    // ── Expenses ────────────────────────────────────────────────────────────────

    public function expenses()
    {
        $this->requirePerm('petty_cash.view');
        $rows = $this->db->tableExists('finance_petty_expenses')
            ? $this->db->table('finance_petty_expenses e')
                ->select('e.*, pa.name AS account_name')
                ->join('finance_petty_cash_accounts pa', 'pa.id = e.petty_account_id', 'left')
                ->orderBy('e.expense_date', 'DESC')->limit(100)->get()->getResultArray()
            : [];

        return view('finance_petty/expenses', $this->viewData([
            'title'     => 'Petty Cash Expenses',
            'expenses'  => $rows,
            'canCreate' => $this->can('petty_cash.create'),
        ]));
    }

    public function expenseCreate()
    {
        $this->requirePerm('petty_cash.create');

        return view('finance_petty/expense_form', $this->viewData([
            'title'      => 'New Petty Cash Expense',
            'accounts'   => $this->petty->accounts(['status' => 'active']),
            'categories' => $this->lookupCategories(),
            'facilities' => $this->lookupFacilities(),
            'vendors'    => $this->lookupVendors(),
            'workOrders' => $this->lookupWorkOrders(),
        ]));
    }

    public function expenseStore()
    {
        $this->requirePerm('petty_cash.create');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        $user = $this->currentUser();
        $id   = $this->petty->createExpense([
            'expense_date'     => $this->request->getPost('expense_date') ?: date('Y-m-d'),
            'petty_account_id' => (int) $this->request->getPost('petty_account_id'),
            'custodian_user_id'=> $this->request->getPost('custodian_user_id') ?: null,
            'category_id'      => $this->request->getPost('category_id') ?: null,
            'amount'           => (float) $this->request->getPost('amount'),
            'vendor_id'        => $this->request->getPost('vendor_id') ?: null,
            'facility_id'      => $this->request->getPost('facility_id') ?: null,
            'work_order_id'    => $this->request->getPost('work_order_id') ?: null,
            'receipt_number'   => trim((string) $this->request->getPost('receipt_number')),
            'description'      => trim((string) $this->request->getPost('description')),
            'notes'            => trim((string) $this->request->getPost('notes')),
        ], $user['id']);

        return redirect()->to(base_url('finance-petty/expenses'))->with('success', 'Expense saved as draft.');
    }

    public function expenseSubmit(int $id)
    {
        $this->requirePerm('petty_cash.submit');
        $result = $this->petty->submitExpense($id, $this->currentUser()['id']);

        return redirect()->back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function expenseApprove(int $id)
    {
        $this->requirePerm('petty_cash.approve');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }
        $user   = $this->currentUser();
        $result = $this->petty->approveExpense($id, $user['id'], $user['role_name']);

        return redirect()->back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function expensePost(int $id)
    {
        $this->requirePerm('petty_cash.post');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }
        $ok = $this->petty->postExpense($id, $this->currentUser()['id']);

        return redirect()->back()->with($ok ? 'success' : 'error', $ok ? 'Expense posted to ledger.' : 'Could not post expense.');
    }

    // ── Advances ──────────────────────────────────────────────────────────────

    public function advances()
    {
        $this->requirePerm('petty_cash.view');
        $rows = $this->db->tableExists('finance_petty_advances')
            ? $this->db->table('finance_petty_advances a')
                ->select('a.*, pa.name AS account_name, u.name AS employee_name')
                ->join('finance_petty_cash_accounts pa', 'pa.id = a.petty_account_id', 'left')
                ->join('users u', 'u.id = a.employee_id', 'left')
                ->orderBy('a.created_at', 'DESC')->limit(100)->get()->getResultArray()
            : [];

        return view('finance_petty/advances', $this->viewData([
            'title'     => 'Petty Cash Advances',
            'advances'  => $rows,
            'canCreate' => $this->can('petty_cash.advance'),
        ]));
    }

    public function advanceCreate()
    {
        $this->requirePerm('petty_cash.advance');

        return view('finance_petty/advance_form', $this->viewData([
            'title'    => 'Request Advance',
            'accounts' => $this->petty->accounts(['status' => 'active']),
            'users'    => $this->lookupUsers(),
        ]));
    }

    public function advanceStore()
    {
        $this->requirePerm('petty_cash.advance');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        $user = $this->currentUser();
        $this->petty->createAdvance([
            'petty_account_id'         => (int) $this->request->getPost('petty_account_id'),
            'employee_id'              => (int) ($this->request->getPost('employee_id') ?: $user['id']),
            'amount'                   => (float) $this->request->getPost('amount'),
            'purpose'                  => trim((string) $this->request->getPost('purpose')),
            'required_date'            => $this->request->getPost('required_date'),
            'expected_settlement_date' => $this->request->getPost('expected_settlement_date'),
            'facility_id'              => $this->request->getPost('facility_id') ?: null,
        ], $user['id']);

        return redirect()->to(base_url('finance-petty/advances'))->with('success', 'Advance request submitted.');
    }

    public function advanceApprove(int $id)
    {
        $this->requirePerm('petty_cash.approve');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }
        $result = $this->petty->approveAdvance($id, $this->currentUser()['id']);

        return redirect()->back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function advanceIssue(int $id)
    {
        $this->requirePerm('petty_cash.post');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }
        $ok = $this->petty->issueAdvance($id, $this->currentUser()['id']);

        return redirect()->back()->with($ok ? 'success' : 'error', $ok ? 'Advance issued.' : 'Could not issue advance.');
    }

    public function advanceSettle(int $id)
    {
        $this->requirePerm('petty_cash.settle');
        $adv = $this->db->table('finance_petty_advances')->where('id', $id)->get()->getRowArray();
        if (! $adv) {
            return redirect()->to(base_url('finance-petty/advances'))->with('error', 'Advance not found.');
        }

        if ($this->request->is('post')) {
            $result = $this->petty->settleAdvance($id, [
                'expense_amount'     => (float) $this->request->getPost('expense_amount'),
                'return_amount'      => (float) $this->request->getPost('return_amount'),
                'additional_payment' => (float) ($this->request->getPost('additional_payment') ?? 0),
                'notes'              => trim((string) $this->request->getPost('notes')),
            ], $this->currentUser()['id']);

            return redirect()->to(base_url('finance-petty/advances'))->with($result['ok'] ? 'success' : 'error', $result['message']);
        }

        return view('finance_petty/advance_settle', $this->viewData([
            'title'   => 'Settle Advance',
            'advance' => $adv,
        ]));
    }

    // ── Replenishment ─────────────────────────────────────────────────────────

    public function replenishments()
    {
        $this->requirePerm('petty_cash.view');
        $rows = $this->db->tableExists('finance_petty_replenishments')
            ? $this->db->table('finance_petty_replenishments r')
                ->select('r.*, pa.name AS account_name')
                ->join('finance_petty_cash_accounts pa', 'pa.id = r.petty_account_id', 'left')
                ->orderBy('r.replenishment_date', 'DESC')->limit(50)->get()->getResultArray()
            : [];

        return view('finance_petty/replenishments', $this->viewData([
            'title'           => 'Replenishments',
            'replenishments'  => $rows,
            'canReplenish'    => $this->can('petty_cash.replenish'),
        ]));
    }

    public function replenishmentCreate()
    {
        $this->requirePerm('petty_cash.replenish');

        return view('finance_petty/replenishment_form', $this->viewData([
            'title'    => 'Replenish Petty Cash',
            'accounts' => $this->petty->accounts(['status' => 'active']),
            'banks'    => $this->lookupBankAccounts(),
            'cash'     => $this->lookupCashAccounts(),
        ]));
    }

    public function replenishmentStore()
    {
        $this->requirePerm('petty_cash.replenish');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        $user = $this->currentUser();
        $num  = $this->petty->generateNumber('replenishment');
        $this->db->table('finance_petty_replenishments')->insert([
            'replenishment_number' => $num,
            'replenishment_date'   => $this->request->getPost('replenishment_date') ?: date('Y-m-d'),
            'petty_account_id'     => (int) $this->request->getPost('petty_account_id'),
            'source_account_type'  => $this->request->getPost('source_account_type'),
            'source_account_id'    => (int) $this->request->getPost('source_account_id'),
            'amount'               => (float) $this->request->getPost('amount'),
            'currency'             => trim((string) $this->request->getPost('currency')) ?: 'QAR',
            'notes'                => trim((string) $this->request->getPost('notes')),
            'status'               => 'pending_approval',
            'created_by'           => $user['id'],
            'created_at'           => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('finance-petty/replenishments'))->with('success', 'Replenishment request created.');
    }

    public function replenishmentApprove(int $id)
    {
        $this->requirePerm('petty_cash.approve');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }
        $this->db->table('finance_petty_replenishments')->where('id', $id)->update([
            'status'      => 'approved',
            'approved_by' => $this->currentUser()['id'],
        ]);

        return redirect()->back()->with('success', 'Replenishment approved.');
    }

    public function replenishmentPost(int $id)
    {
        $this->requirePerm('petty_cash.post');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }
        $ok = $this->petty->postReplenishment($id, $this->currentUser()['id']);

        return redirect()->back()->with($ok ? 'success' : 'error', $ok ? 'Replenishment posted.' : 'Could not post replenishment.');
    }

    // ── Transfers ─────────────────────────────────────────────────────────────

    public function transfers()
    {
        $this->requirePerm('petty_cash.view');
        $rows = $this->db->tableExists('finance_petty_transfers')
            ? $this->db->table('finance_petty_transfers')->orderBy('transfer_date', 'DESC')->limit(50)->get()->getResultArray()
            : [];

        return view('finance_petty/transfers', $this->viewData([
            'title'       => 'Petty Cash Transfers',
            'transfers'   => $rows,
            'accounts'    => $this->petty->accounts(['status' => 'active']),
            'canTransfer' => $this->can('petty_cash.transfer'),
        ]));
    }

    public function transferStore()
    {
        $this->requirePerm('petty_cash.transfer');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        $num = $this->petty->generateNumber('transfer');
        $this->db->table('finance_petty_transfers')->insert([
            'transfer_number'        => $num,
            'transfer_date'          => $this->request->getPost('transfer_date') ?: date('Y-m-d'),
            'from_petty_account_id'  => (int) $this->request->getPost('from_petty_account_id'),
            'to_petty_account_id'    => (int) $this->request->getPost('to_petty_account_id'),
            'amount'                 => (float) $this->request->getPost('amount'),
            'currency'               => 'QAR',
            'purpose'                => trim((string) $this->request->getPost('purpose')),
            'status'                 => 'pending_approval',
            'created_by'             => $this->currentUser()['id'],
            'created_at'             => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('finance-petty/transfers'))->with('success', 'Transfer request created.');
    }

    public function transferApprove(int $id)
    {
        $this->requirePerm('petty_cash.approve');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }
        $this->db->table('finance_petty_transfers')->where('id', $id)->update([
            'status'      => 'approved',
            'approved_by' => $this->currentUser()['id'],
        ]);

        return redirect()->back()->with('success', 'Transfer approved.');
    }

    public function transferPost(int $id)
    {
        $this->requirePerm('petty_cash.post');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }
        $ok = $this->petty->postTransfer($id, $this->currentUser()['id']);

        return redirect()->back()->with($ok ? 'success' : 'error', $ok ? 'Transfer posted.' : 'Could not post transfer.');
    }

    // ── Physical count ────────────────────────────────────────────────────────

    public function counts()
    {
        $this->requirePerm('petty_cash.view');
        $rows = $this->db->tableExists('finance_petty_counts')
            ? $this->db->table('finance_petty_counts c')
                ->select('c.*, pa.name AS account_name')
                ->join('finance_petty_cash_accounts pa', 'pa.id = c.petty_account_id', 'left')
                ->orderBy('c.count_date', 'DESC')->limit(50)->get()->getResultArray()
            : [];

        return view('finance_petty/counts', $this->viewData([
            'title'    => 'Physical Cash Counts',
            'counts'   => $rows,
            'canCount' => $this->can('petty_cash.count'),
        ]));
    }

    public function countCreate()
    {
        $this->requirePerm('petty_cash.count');

        return view('finance_petty/count_form', $this->viewData([
            'title'    => 'Physical Cash Count',
            'accounts' => $this->petty->accounts(['status' => 'active']),
        ]));
    }

    public function countStore()
    {
        $this->requirePerm('petty_cash.count');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        $denoms   = (array) $this->request->getPost('denomination');
        $qtys     = (array) $this->request->getPost('quantity');
        $lines    = [];
        foreach ($denoms as $i => $d) {
            $q = (int) ($qtys[$i] ?? 0);
            if ($q <= 0 || trim((string) $d) === '') {
                continue;
            }
            $val = (float) preg_replace('/[^0-9.]/', '', (string) $d);
            $lines[] = [
                'denomination' => (string) $d,
                'quantity'     => $q,
                'line_total'   => $val * $q,
            ];
        }

        $id = $this->petty->createCount(
            (int) $this->request->getPost('petty_account_id'),
            $this->request->getPost('count_date') ?: date('Y-m-d'),
            $lines,
            $this->currentUser()['id'],
            trim((string) $this->request->getPost('reason'))
        );

        return redirect()->to(base_url('finance-petty/counts'))->with('success', 'Cash count recorded.');
    }

    public function countApprove(int $id)
    {
        $this->requirePerm('petty_cash.adjust');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }
        $ok = $this->petty->approveCountAdjustment($id, $this->currentUser()['id']);

        return redirect()->back()->with($ok ? 'success' : 'error', $ok ? 'Count adjustment posted.' : 'Could not post adjustment.');
    }

    // ── Reconciliation ────────────────────────────────────────────────────────

    public function reconciliation()
    {
        $this->requirePerm('petty_cash.reconcile');
        $recs = $this->db->tableExists('finance_petty_reconciliations')
            ? $this->db->table('finance_petty_reconciliations r')
                ->select('r.*, pa.name AS account_name')
                ->join('finance_petty_cash_accounts pa', 'pa.id = r.petty_account_id', 'left')
                ->orderBy('r.reconciliation_date', 'DESC')->limit(50)->get()->getResultArray()
            : [];

        return view('finance_petty/reconciliation', $this->viewData([
            'title'    => 'Petty Cash Reconciliation',
            'recs'     => $recs,
            'accounts' => $this->petty->accounts(['status' => 'active']),
        ]));
    }

    public function reconciliationStore()
    {
        $this->requirePerm('petty_cash.reconcile');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        $acctId  = (int) $this->request->getPost('petty_account_id');
        $system  = $this->ledger->runningBalance('petty', $acctId);
        $physical = (float) $this->request->getPost('physical_cash');
        $diff    = $physical - $system;

        $this->db->table('finance_petty_reconciliations')->insert([
            'petty_account_id'    => $acctId,
            'reconciliation_date' => $this->request->getPost('reconciliation_date') ?: date('Y-m-d'),
            'custodian_user_id'   => $this->request->getPost('custodian_user_id') ?: null,
            'system_balance'      => $system,
            'physical_cash'       => $physical,
            'shortage'            => $diff < 0 ? abs($diff) : 0,
            'excess'              => $diff > 0 ? $diff : 0,
            'final_difference'    => $diff,
            'status'              => abs($diff) < 0.01 ? 'reconciled' : 'difference_found',
            'notes'               => trim((string) $this->request->getPost('notes')),
            'created_by'          => $this->currentUser()['id'],
            'created_at'          => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('finance-petty/reconciliation'))->with('success', 'Reconciliation saved.');
    }

    // ── Reports & audit ─────────────────────────────────────────────────────

    public function reports()
    {
        $this->requirePerm('petty_cash.report');

        return view('finance_petty/reports', $this->viewData(['title' => 'Petty Cash Reports']));
    }

    public function auditLogs()
    {
        $this->requirePerm('petty_cash.view');
        $logs = $this->db->tableExists('finance_petty_audit_logs')
            ? $this->db->table('finance_petty_audit_logs')->orderBy('id', 'DESC')->limit(200)->get()->getResultArray()
            : [];

        return view('finance_petty/audit_logs', $this->viewData([
            'title' => 'Petty Cash Audit Log',
            'logs'  => $logs,
        ]));
    }

    public function legacy()
    {
        $this->requirePerm('petty_cash.view');

        return redirect()->to(base_url('finance/petty-cash'));
    }

    /** @return array<string,mixed> */
    private function scopeFilters(): array
    {
        $filters = [];
        $role    = (string) session()->get('user_role');

        if (in_array($role, ['property_manager'], true)) {
            $ids = $this->companyScope()->facilityIds();
            if (is_array($ids) && $ids !== []) {
                // applied in accounts query via facility - simplified: custodian filter for non-managers
            }
        }

        return $filters;
    }

    /** @return list<array<string,mixed>> */
    private function lookupBranches(): array
    {
        return $this->db->tableExists('finance_branches')
            ? $this->db->table('finance_branches')->where('is_active', 1)->orderBy('name')->get()->getResultArray()
            : [];
    }

    /** @return list<array<string,mixed>> */
    private function lookupFacilities(): array
    {
        return $this->scopedFacilitiesList('id, name');
    }

    /** @return list<array<string,mixed>> */
    private function lookupUsers(): array
    {
        return $this->db->table('users')->select('id, name')->where('status', 'active')->orderBy('name')->limit(300)->get()->getResultArray();
    }

    /** @return list<array<string,mixed>> */
    private function lookupVendors(): array
    {
        return $this->db->tableExists('vendors')
            ? $this->db->table('vendors')->select('id, name')->orderBy('name')->limit(200)->get()->getResultArray()
            : [];
    }

    /** @return list<array<string,mixed>> */
    private function lookupCategories(): array
    {
        return $this->db->tableExists('finance_categories')
            ? $this->db->table('finance_categories')->where('category_type', 'expense')->where('is_active', 1)->orderBy('sort_order')->get()->getResultArray()
            : [];
    }

    /** @return list<array<string,mixed>> */
    private function lookupWorkOrders(): array
    {
        if (! $this->db->tableExists('work_orders')) {
            return [];
        }

        return $this->db->table('work_orders')->select('id, wo_number, title')->orderBy('id', 'DESC')->limit(100)->get()->getResultArray();
    }

    /** @return list<array<string,mixed>> */
    private function lookupBankAccounts(): array
    {
        return $this->db->tableExists('finance_bank_accounts')
            ? $this->db->table('finance_bank_accounts')->where('status', 'active')->orderBy('name')->get()->getResultArray()
            : [];
    }

    /** @return list<array<string,mixed>> */
    private function lookupCashAccounts(): array
    {
        return $this->db->tableExists('finance_cash_accounts')
            ? $this->db->table('finance_cash_accounts')->where('status', 'active')->orderBy('name')->get()->getResultArray()
            : [];
    }
}
