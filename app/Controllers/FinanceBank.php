<?php

namespace App\Controllers;

use App\Services\Finance\FinanceAccountService;
use App\Services\Finance\FinanceApprovalService;
use App\Services\Finance\FinanceLedgerService;
use App\Services\Finance\FinanceSettingsService;
use App\Services\RbacService;

/**
 * Finance & Bank Management — bank/cash accounts, deposits, withdrawals, transfers, ledger.
 */
class FinanceBank extends BaseController
{
    protected ?string $workspaceRequired = null;

    private FinanceLedgerService $ledger;
    private FinanceAccountService $accounts;
    private FinanceApprovalService $approval;
    private FinanceSettingsService $finSettings;
    private RbacService $rbac;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request,
                                   \CodeIgniter\HTTP\ResponseInterface $response,
                                   \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
        $this->ledger       = new FinanceLedgerService($this->db);
        $this->accounts     = new FinanceAccountService($this->db);
        $this->approval     = new FinanceApprovalService($this->db);
        $this->finSettings  = new FinanceSettingsService($this->db);
        $this->rbac         = new RbacService($this->db);
    }

    private function requirePerm(string $perm): void
    {
        $role = (string) session()->get('user_role');
        if (! $this->rbac->can($role, $perm)) {
            if ($this->request->isAJAX()) {
                $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Access denied.'])->send();
                exit;
            }
            session()->setFlashdata('error', 'You do not have permission for this finance action.');
            redirect()->to(base_url('dashboard'))->send();
            exit;
        }
    }

    private function can(string $perm): bool
    {
        $role = (string) session()->get('user_role');

        return $this->rbac->can($role, $perm);
    }

    /** @return array<string,mixed> */
    private function filters(): array
    {
        return array_filter([
            'date_from'        => $this->request->getGet('date_from'),
            'date_to'          => $this->request->getGet('date_to'),
            'branch_id'        => $this->request->getGet('branch_id'),
            'facility_id'      => $this->request->getGet('facility_id'),
            'account_type'     => $this->request->getGet('account_type'),
            'account_id'       => $this->request->getGet('account_id'),
            'transaction_type' => $this->request->getGet('transaction_type'),
            'status'           => $this->request->getGet('status'),
            'bank_account_id'  => $this->request->getGet('bank'),
        ]);
    }

    // ── Dashboard ─────────────────────────────────────────────────────────────

    public function index()
    {
        $this->requirePerm('finance.dashboard');
        $filters = $this->filters();
        $kpis    = $this->ledger->dashboardKpis($filters);
        $banks   = $this->accounts->bankAccounts($filters);

        foreach ($banks as &$b) {
            $b['account_number_masked'] = $this->accounts->maskAccountNumber($b['account_number'] ?? '');
        }
        unset($b);

        return view('finance_bank/dashboard', $this->viewData([
            'title'   => 'Finance Dashboard',
            'kpis'    => $kpis,
            'banks'   => $banks,
            'filters' => $filters,
            'canFull' => $this->can('finance.bank.full_details'),
        ]));
    }

    // ── Bank accounts ─────────────────────────────────────────────────────────

    public function bankAccounts()
    {
        $this->requirePerm('finance.bank.view');
        $accounts = $this->accounts->bankAccounts($this->filters());
        foreach ($accounts as &$a) {
            $a['account_number_display'] = $this->can('finance.bank.full_details')
                ? ($a['account_number'] ?? '—')
                : $this->accounts->maskAccountNumber($a['account_number'] ?? '');
        }
        unset($a);

        return view('finance_bank/bank_accounts', $this->viewData([
            'title'    => 'Bank Accounts',
            'accounts' => $accounts,
            'canCreate'=> $this->can('finance.bank.create'),
            'canEdit'  => $this->can('finance.bank.edit'),
        ]));
    }

    public function bankAccountCreate()
    {
        $this->requirePerm('finance.bank.create');

        return view('finance_bank/bank_account_form', $this->viewData([
            'title'      => 'Add Bank Account',
            'account'    => null,
            'branches'   => $this->lookupBranches(),
            'facilities' => $this->lookupFacilities(),
        ]));
    }

    public function bankAccountStore()
    {
        $this->requirePerm('finance.bank.create');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        $user = $this->currentUser();
        $id   = $this->accounts->createBankAccount([
            'name'                 => trim((string) $this->request->getPost('name')),
            'bank_name'            => trim((string) $this->request->getPost('bank_name')),
            'branch_name'          => trim((string) $this->request->getPost('branch_name')),
            'account_number'       => trim((string) $this->request->getPost('account_number')),
            'iban'                 => trim((string) $this->request->getPost('iban')),
            'swift_bic'            => trim((string) $this->request->getPost('swift_bic')),
            'currency'             => trim((string) $this->request->getPost('currency')) ?: 'QAR',
            'account_type'         => $this->request->getPost('account_type') ?: 'current',
            'opening_balance'      => (float) $this->request->getPost('opening_balance'),
            'opening_balance_date' => $this->request->getPost('opening_balance_date') ?: date('Y-m-d'),
            'bank_contact'         => trim((string) $this->request->getPost('bank_contact')),
            'bank_address'         => trim((string) $this->request->getPost('bank_address')),
            'notes'                => trim((string) $this->request->getPost('notes')),
            'branch_id'            => $this->request->getPost('branch_id') ?: null,
            'facility_id'          => $this->request->getPost('facility_id') ?: null,
            'department'           => trim((string) $this->request->getPost('department')),
            'scope_type'           => $this->request->getPost('scope_type') ?: 'company',
            'company_id'           => $user['company_id'],
            'min_balance_alert'    => $this->request->getPost('min_balance_alert') ?: null,
            'account_opening_date' => $this->request->getPost('account_opening_date'),
        ], $user['id']);

        $this->logActivity('create', 'finance_bank_accounts', $id, 'Bank account created');
        $this->ledger->logAudit($user['id'], $user['role_name'], 'created', 'finance_bank_accounts', $id, null, null, null, null, $this->request->getIPAddress());

        return redirect()->to(base_url('finance-bank/bank-accounts/view/' . $id))->with('success', 'Bank account created.');
    }

    public function bankAccountView(int $id)
    {
        $this->requirePerm('finance.bank.view');
        $account = $this->accounts->bankAccount($id);
        if (! $account) {
            return redirect()->to(base_url('finance-bank/bank-accounts'))->with('error', 'Account not found.');
        }

        $account['account_number_display'] = $this->can('finance.bank.full_details')
            ? ($account['account_number'] ?? '—')
            : $this->accounts->maskAccountNumber($account['account_number'] ?? '');

        $txs = $this->ledger->listTransactions([
            'account_type' => 'bank',
            'account_id'   => $id,
            'status'       => 'posted',
        ], 50);

        return view('finance_bank/bank_account_view', $this->viewData([
            'title'   => $account['name'],
            'account' => $account,
            'txs'     => $txs,
            'canEdit' => $this->can('finance.bank.edit'),
            'canClose'=> $this->can('finance.bank.close'),
        ]));
    }

    public function bankAccountEdit(int $id)
    {
        $this->requirePerm('finance.bank.edit');
        $account = $this->accounts->bankAccount($id);
        if (! $account) {
            return redirect()->to(base_url('finance-bank/bank-accounts'))->with('error', 'Account not found.');
        }

        return view('finance_bank/bank_account_form', $this->viewData([
            'title'      => 'Edit Bank Account',
            'account'    => $account,
            'branches'   => $this->lookupBranches(),
            'facilities' => $this->lookupFacilities(),
        ]));
    }

    public function bankAccountUpdate(int $id)
    {
        $this->requirePerm('finance.bank.edit');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        $user = $this->currentUser();
        $canAdjust = in_array($user['role_name'], explode(',', $this->finSettings->get('fin_opening_balance_adjust_role', 'super_admin,finance_manager')), true);

        $this->accounts->updateBankAccount($id, [
            'name'                 => trim((string) $this->request->getPost('name')),
            'bank_name'            => trim((string) $this->request->getPost('bank_name')),
            'branch_name'          => trim((string) $this->request->getPost('branch_name')),
            'account_number'       => trim((string) $this->request->getPost('account_number')),
            'iban'                 => trim((string) $this->request->getPost('iban')),
            'swift_bic'            => trim((string) $this->request->getPost('swift_bic')),
            'currency'             => trim((string) $this->request->getPost('currency')),
            'account_type'         => $this->request->getPost('account_type'),
            'bank_contact'         => trim((string) $this->request->getPost('bank_contact')),
            'bank_address'         => trim((string) $this->request->getPost('bank_address')),
            'notes'                => trim((string) $this->request->getPost('notes')),
            'branch_id'            => $this->request->getPost('branch_id') ?: null,
            'facility_id'          => $this->request->getPost('facility_id') ?: null,
            'department'           => trim((string) $this->request->getPost('department')),
            'scope_type'           => $this->request->getPost('scope_type'),
            'min_balance_alert'    => $this->request->getPost('min_balance_alert'),
            'status'               => $this->request->getPost('status'),
        ], $user['id'], $canAdjust);

        $this->logActivity('update', 'finance_bank_accounts', $id, 'Bank account updated');

        return redirect()->to(base_url('finance-bank/bank-accounts/view/' . $id))->with('success', 'Bank account updated.');
    }

    public function bankAccountClose(int $id)
    {
        $this->requirePerm('finance.bank.close');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        if ($this->accounts->accountHasPostedTransactions('bank', $id)) {
            $this->accounts->closeBankAccount($id, $this->currentUser()['id']);
            $this->logActivity('close', 'finance_bank_accounts', $id, 'Bank account closed (has transactions)');

            return redirect()->to(base_url('finance-bank/bank-accounts'))->with('success', 'Account marked as closed.');
        }

        $this->accounts->closeBankAccount($id, $this->currentUser()['id']);

        return redirect()->to(base_url('finance-bank/bank-accounts'))->with('success', 'Account closed.');
    }

    // ── Cash accounts ─────────────────────────────────────────────────────────

    public function cashAccounts()
    {
        $this->requirePerm('finance.bank.view');
        $accounts = $this->accounts->cashAccounts($this->filters());

        return view('finance_bank/cash_accounts', $this->viewData([
            'title'    => 'Cash Accounts',
            'accounts' => $accounts,
            'canCreate'=> $this->can('finance.bank.create'),
        ]));
    }

    public function cashAccountCreate()
    {
        $this->requirePerm('finance.bank.create');

        return view('finance_bank/cash_account_form', $this->viewData([
            'title'      => 'Add Cash Account',
            'account'    => null,
            'branches'   => $this->lookupBranches(),
            'facilities' => $this->lookupFacilities(),
            'users'      => $this->lookupUsers(),
        ]));
    }

    public function cashAccountStore()
    {
        $this->requirePerm('finance.bank.create');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        $user = $this->currentUser();
        $id   = $this->accounts->createCashAccount([
            'name'                  => trim((string) $this->request->getPost('name')),
            'account_type'          => $this->request->getPost('account_type') ?: 'main',
            'currency'              => trim((string) $this->request->getPost('currency')) ?: 'QAR',
            'opening_balance'       => (float) $this->request->getPost('opening_balance'),
            'opening_balance_date'  => $this->request->getPost('opening_balance_date') ?: date('Y-m-d'),
            'responsible_user_id'   => $this->request->getPost('responsible_user_id') ?: null,
            'branch_id'             => $this->request->getPost('branch_id') ?: null,
            'facility_id'           => $this->request->getPost('facility_id') ?: null,
            'department'            => trim((string) $this->request->getPost('department')),
            'notes'                 => trim((string) $this->request->getPost('notes')),
            'company_id'            => $user['company_id'],
        ], $user['id']);

        return redirect()->to(base_url('finance-bank/cash-accounts'))->with('success', 'Cash account created.');
    }

    // ── Deposits ──────────────────────────────────────────────────────────────

    public function deposits()
    {
        $this->requirePerm('finance.deposit.create');
        $rows = [];
        if ($this->db->tableExists('finance_deposits')) {
            $q = $this->db->table('finance_deposits d')
                ->select('d.*, ba.name AS bank_account_name')
                ->join('finance_bank_accounts ba', 'ba.id = d.bank_account_id', 'left')
                ->orderBy('d.deposit_date', 'DESC');
            if ($st = $this->request->getGet('status')) {
                $q->where('d.status', $st);
            }
            $rows = $q->limit(100)->get()->getResultArray();
        }

        return view('finance_bank/deposits', $this->viewData([
            'title'    => 'Deposits',
            'deposits' => $rows,
            'canCreate'=> $this->can('finance.deposit.create'),
        ]));
    }

    public function depositCreate()
    {
        $this->requirePerm('finance.deposit.create');

        return view('finance_bank/deposit_form', $this->viewData([
            'title'        => 'New Deposit',
            'banks'        => $this->accounts->bankAccounts(['status' => 'active']),
            'categories'   => $this->lookupCategories('deposit'),
            'facilities'   => $this->lookupFacilities(),
            'branches'     => $this->lookupBranches(),
        ]));
    }

    public function depositStore()
    {
        $this->requirePerm('finance.deposit.create');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        $user = $this->currentUser();
        $num  = $this->ledger->generateNumber('deposit');

        $this->db->table('finance_deposits')->insert([
            'deposit_number'   => $num,
            'deposit_date'     => $this->request->getPost('deposit_date') ?: date('Y-m-d'),
            'bank_account_id'  => (int) $this->request->getPost('bank_account_id'),
            'amount'           => (float) $this->request->getPost('amount'),
            'currency'         => trim((string) $this->request->getPost('currency')) ?: 'QAR',
            'deposit_source'   => trim((string) $this->request->getPost('deposit_source')),
            'category_id'      => $this->request->getPost('category_id') ?: null,
            'branch_id'        => $this->request->getPost('branch_id') ?: null,
            'facility_id'      => $this->request->getPost('facility_id') ?: null,
            'reference_number' => trim((string) $this->request->getPost('reference_number')),
            'payment_method'   => trim((string) $this->request->getPost('payment_method')),
            'description'      => trim((string) $this->request->getPost('description')),
            'notes'            => trim((string) $this->request->getPost('notes')),
            'status'           => 'draft',
            'created_by'       => $user['id'],
            'created_at'       => date('Y-m-d H:i:s'),
        ]);

        $id = (int) $this->db->insertID();
        $this->logActivity('create', 'finance_deposits', $id, 'Deposit ' . $num);

        return redirect()->to(base_url('finance-bank/deposits'))->with('success', 'Deposit saved as draft.');
    }

    public function depositSubmit(int $id)
    {
        $this->requirePerm('finance.deposit.create');
        $dep = $this->db->table('finance_deposits')->where('id', $id)->get()->getRowArray();
        if (! $dep) {
            return redirect()->back()->with('error', 'Deposit not found.');
        }
        $result = $this->approval->submit('deposit', $id, $this->currentUser()['id'], (float) $dep['amount']);

        return redirect()->back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function depositApprove(int $id)
    {
        $this->requirePerm('finance.deposit.approve');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }
        $user   = $this->currentUser();
        $result = $this->approval->approve('deposit', $id, $user['id'], $user['role_name']);

        return redirect()->back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function depositPost(int $id)
    {
        $this->requirePerm('finance.deposit.post');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }
        $ok = $this->ledger->postDeposit($id, $this->currentUser()['id']);

        return redirect()->back()->with($ok ? 'success' : 'error', $ok ? 'Deposit posted to ledger.' : 'Could not post deposit.');
    }

    // ── Withdrawals ───────────────────────────────────────────────────────────

    public function withdrawals()
    {
        $this->requirePerm('finance.withdrawal.create');
        $rows = [];
        if ($this->db->tableExists('finance_withdrawals')) {
            $rows = $this->db->table('finance_withdrawals w')
                ->select('w.*, ba.name AS bank_name, ca.name AS cash_name')
                ->join('finance_bank_accounts ba', 'ba.id = w.bank_account_id', 'left')
                ->join('finance_cash_accounts ca', 'ca.id = w.cash_account_id', 'left')
                ->orderBy('w.withdrawal_date', 'DESC')
                ->limit(100)->get()->getResultArray();
        }

        return view('finance_bank/withdrawals', $this->viewData([
            'title'       => 'Withdrawals',
            'withdrawals' => $rows,
            'canCreate'   => $this->can('finance.withdrawal.create'),
        ]));
    }

    public function withdrawalCreate()
    {
        $this->requirePerm('finance.withdrawal.create');

        return view('finance_bank/withdrawal_form', $this->viewData([
            'title'      => 'New Withdrawal',
            'banks'      => $this->accounts->bankAccounts(['status' => 'active']),
            'cash'       => $this->accounts->cashAccounts(['status' => 'active']),
            'categories' => $this->lookupCategories('withdrawal'),
            'vendors'    => $this->lookupVendors(),
            'facilities' => $this->lookupFacilities(),
        ]));
    }

    public function withdrawalStore()
    {
        $this->requirePerm('finance.withdrawal.create');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        $user = $this->currentUser();
        $num  = $this->ledger->generateNumber('withdrawal');

        $this->db->table('finance_withdrawals')->insert([
            'withdrawal_number' => $num,
            'withdrawal_date'   => $this->request->getPost('withdrawal_date') ?: date('Y-m-d'),
            'bank_account_id'   => $this->request->getPost('bank_account_id') ?: null,
            'cash_account_id'   => $this->request->getPost('cash_account_id') ?: null,
            'amount'            => (float) $this->request->getPost('amount'),
            'currency'          => trim((string) $this->request->getPost('currency')) ?: 'QAR',
            'category_id'       => $this->request->getPost('category_id') ?: null,
            'vendor_id'         => $this->request->getPost('vendor_id') ?: null,
            'facility_id'       => $this->request->getPost('facility_id') ?: null,
            'description'       => trim((string) $this->request->getPost('description')),
            'notes'             => trim((string) $this->request->getPost('notes')),
            'status'            => 'draft',
            'created_by'        => $user['id'],
            'created_at'        => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('finance-bank/withdrawals'))->with('success', 'Withdrawal saved as draft.');
    }

    public function withdrawalSubmit(int $id)
    {
        $this->requirePerm('finance.withdrawal.create');
        $w = $this->db->table('finance_withdrawals')->where('id', $id)->get()->getRowArray();
        if (! $w) {
            return redirect()->back()->with('error', 'Withdrawal not found.');
        }
        $result = $this->approval->submit('withdrawal', $id, $this->currentUser()['id'], (float) $w['amount']);

        return redirect()->back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function withdrawalApprove(int $id)
    {
        $this->requirePerm('finance.withdrawal.approve');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }
        $user   = $this->currentUser();
        $result = $this->approval->approve('withdrawal', $id, $user['id'], $user['role_name']);

        return redirect()->back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function withdrawalPost(int $id)
    {
        $this->requirePerm('finance.withdrawal.post');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }
        $ok = $this->ledger->postWithdrawal($id, $this->currentUser()['id']);

        return redirect()->back()->with($ok ? 'success' : 'error', $ok ? 'Withdrawal posted.' : 'Could not post withdrawal.');
    }

    // ── Transfers ─────────────────────────────────────────────────────────────

    public function transfers()
    {
        $this->requirePerm('finance.transfer.create');
        $rows = $this->db->tableExists('finance_transfers')
            ? $this->db->table('finance_transfers')->orderBy('transfer_date', 'DESC')->limit(100)->get()->getResultArray()
            : [];

        return view('finance_bank/transfers', $this->viewData([
            'title'     => 'Bank Transfers',
            'transfers' => $rows,
            'canCreate' => $this->can('finance.transfer.create'),
        ]));
    }

    public function transferCreate()
    {
        $this->requirePerm('finance.transfer.create');

        return view('finance_bank/transfer_form', $this->viewData([
            'title' => 'New Transfer',
            'banks' => $this->accounts->bankAccounts(['status' => 'active']),
            'cash'  => $this->accounts->cashAccounts(['status' => 'active']),
        ]));
    }

    public function transferStore()
    {
        $this->requirePerm('finance.transfer.create');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        $user = $this->currentUser();
        $num  = $this->ledger->generateNumber('transfer');

        $this->db->table('finance_transfers')->insert([
            'transfer_number'   => $num,
            'transfer_date'     => $this->request->getPost('transfer_date') ?: date('Y-m-d'),
            'from_account_type' => $this->request->getPost('from_account_type'),
            'from_account_id'   => (int) $this->request->getPost('from_account_id'),
            'to_account_type'   => $this->request->getPost('to_account_type'),
            'to_account_id'     => (int) $this->request->getPost('to_account_id'),
            'amount'            => (float) $this->request->getPost('amount'),
            'currency'          => trim((string) $this->request->getPost('currency')) ?: 'QAR',
            'transfer_fee'      => (float) ($this->request->getPost('transfer_fee') ?? 0),
            'purpose'           => trim((string) $this->request->getPost('purpose')),
            'notes'             => trim((string) $this->request->getPost('notes')),
            'status'            => 'draft',
            'created_by'        => $user['id'],
            'created_at'        => date('Y-m-d H:i:s'),
        ]);

        $id = (int) $this->db->insertID();

        return redirect()->to(base_url('finance-bank/transfers'))->with('success', 'Transfer saved.');
    }

    public function transferSubmit(int $id)
    {
        $this->requirePerm('finance.transfer.create');
        $t = $this->db->table('finance_transfers')->where('id', $id)->get()->getRowArray();
        if (! $t) {
            return redirect()->back()->with('error', 'Transfer not found.');
        }
        $result = $this->approval->submit('transfer', $id, $this->currentUser()['id'], (float) $t['amount']);

        return redirect()->back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function transferApprove(int $id)
    {
        $this->requirePerm('finance.transfer.approve');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }
        $user   = $this->currentUser();
        $result = $this->approval->approve('transfer', $id, $user['id'], $user['role_name']);

        return redirect()->back()->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    public function transferPost(int $id)
    {
        $this->requirePerm('finance.transfer.approve');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }
        $ok = $this->ledger->postTransfer($id, $this->currentUser()['id']);

        return redirect()->back()->with($ok ? 'success' : 'error', $ok ? 'Transfer posted.' : 'Could not post transfer.');
    }

    // ── Income / Expenses (centralized lists) ─────────────────────────────────

    public function income()
    {
        $this->requirePerm('finance.income.create');

        return redirect()->to(base_url('finance/invoices'));
    }

    public function expenses()
    {
        $this->requirePerm('finance.expense.create');

        return redirect()->to(base_url('finance/expenses'));
    }

    public function receipts()
    {
        $this->requirePerm('finance.transaction.view');

        return redirect()->to(base_url('finance/payments'));
    }

    public function payments()
    {
        $this->requirePerm('finance.transaction.view');

        return redirect()->to(base_url('finance/payments'));
    }

    // ── Transactions ledger ───────────────────────────────────────────────────

    public function transactions()
    {
        $this->requirePerm('finance.transaction.view');
        $filters = $this->filters();
        $txs     = $this->ledger->listTransactions($filters, 100);

        return view('finance_bank/transactions', $this->viewData([
            'title'       => 'Financial Transactions',
            'transactions'=> $txs,
            'filters'     => $filters,
            'banks'       => $this->accounts->bankAccounts(),
            'canReverse'  => $this->can('finance.transaction.reverse'),
        ]));
    }

    public function reverseTransaction(int $id)
    {
        $this->requirePerm('finance.transaction.reverse');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        $reason = trim((string) $this->request->getPost('reason'));
        if ($reason === '') {
            return redirect()->back()->with('error', 'Reversal reason is required.');
        }

        $user  = $this->currentUser();
        $revId = $this->ledger->reverseTransaction($id, $user['id'], $reason);
        if (! $revId) {
            return redirect()->back()->with('error', 'Could not reverse transaction.');
        }

        $this->ledger->logAudit($user['id'], $user['role_name'], 'reversed', 'finance_transactions', $id, $revId, null, null, $reason, $this->request->getIPAddress());

        return redirect()->back()->with('success', 'Transaction reversed.');
    }

    // ── Approvals ─────────────────────────────────────────────────────────────

    public function approvals()
    {
        $this->requirePerm('finance.deposit.approve');
        $pending = $this->approval->pendingDocuments();

        return view('finance_bank/approvals', $this->viewData([
            'title'   => 'Pending Approvals',
            'pending' => $pending,
        ]));
    }

    // ── Reconciliation ────────────────────────────────────────────────────────

    public function reconciliation()
    {
        $this->requirePerm('finance.reconciliation');
        $recs = $this->db->tableExists('finance_reconciliations')
            ? $this->db->table('finance_reconciliations r')
                ->select('r.*, ba.name AS bank_account_name')
                ->join('finance_bank_accounts ba', 'ba.id = r.bank_account_id', 'left')
                ->orderBy('r.statement_date', 'DESC')
                ->limit(50)->get()->getResultArray()
            : [];

        return view('finance_bank/reconciliation', $this->viewData([
            'title'  => 'Bank Reconciliation',
            'recs'   => $recs,
            'banks'  => $this->accounts->bankAccounts(['status' => 'active']),
        ]));
    }

    public function reconciliationStore()
    {
        $this->requirePerm('finance.reconciliation');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        $bankId = (int) $this->request->getPost('bank_account_id');
        $system = $this->ledger->runningBalance('bank', $bankId);
        $closing = (float) $this->request->getPost('statement_closing');
        $opening = (float) $this->request->getPost('statement_opening');

        $this->db->table('finance_reconciliations')->insert([
            'bank_account_id'   => $bankId,
            'statement_date'    => $this->request->getPost('statement_date') ?: date('Y-m-d'),
            'statement_opening' => $opening,
            'statement_closing' => $closing,
            'system_balance'    => $system,
            'difference'        => $closing - $system,
            'status'            => 'in_progress',
            'notes'             => trim((string) $this->request->getPost('notes')),
            'created_by'        => $this->currentUser()['id'],
            'created_at'        => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(base_url('finance-bank/reconciliation'))->with('success', 'Reconciliation started.');
    }

    // ── Reports ───────────────────────────────────────────────────────────────

    public function reports()
    {
        $this->requirePerm('finance.reports');

        return view('finance_bank/reports', $this->viewData([
            'title' => 'Financial Reports',
        ]));
    }

    public function report(string $type)
    {
        $this->requirePerm('finance.reports');
        $filters = $this->filters();
        $txs     = $this->ledger->listTransactions(array_merge($filters, [
            'status' => 'posted',
        ]), 500);

        return view('finance_bank/report_detail', $this->viewData([
            'title'        => ucwords(str_replace('-', ' ', $type)),
            'reportType'   => $type,
            'transactions' => $txs,
            'filters'      => $filters,
        ]));
    }

    // ── Settings ──────────────────────────────────────────────────────────────

    public function settings()
    {
        $this->requirePerm('finance.settings');
        $settings = [];
        foreach (FinanceSettingsService::KEYS as $k => $v) {
            $settings[$k] = $this->finSettings->get($k, $v);
        }

        return view('finance_bank/settings', $this->viewData([
            'title'    => 'Finance Settings',
            'settings' => $settings,
        ]));
    }

    public function settingsSave()
    {
        $this->requirePerm('finance.settings');
        if (! $this->request->is('post')) {
            return redirect()->back();
        }

        $values = [];
        foreach (array_keys(FinanceSettingsService::KEYS) as $key) {
            $post = $this->request->getPost($key);
            if ($post !== null) {
                $values[$key] = (string) $post;
            }
        }
        $this->finSettings->save($values);

        return redirect()->to(base_url('finance-bank/settings'))->with('success', 'Finance settings saved.');
    }

    // ── Audit logs ────────────────────────────────────────────────────────────

    public function auditLogs()
    {
        $this->requirePerm('finance.audit_log');
        $logs = $this->db->tableExists('finance_audit_logs')
            ? $this->db->table('finance_audit_logs')->orderBy('id', 'DESC')->limit(200)->get()->getResultArray()
            : [];

        return view('finance_bank/audit_logs', $this->viewData([
            'title' => 'Finance Audit Log',
            'logs'  => $logs,
        ]));
    }

    // ── Vouchers ──────────────────────────────────────────────────────────────

    public function voucher(string $type, int $id)
    {
        $this->requirePerm('finance.transaction.view');

        return view('finance_bank/voucher', $this->viewData([
            'title'  => ucfirst($type) . ' Voucher',
            'type'   => $type,
            'record' => $this->loadVoucherRecord($type, $id),
            'usePdf' => true,
        ]));
    }

    // ── Lookups ───────────────────────────────────────────────────────────────

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
    private function lookupVendors(): array
    {
        return $this->db->tableExists('vendors')
            ? $this->db->table('vendors')->select('id, name')->orderBy('name')->limit(200)->get()->getResultArray()
            : [];
    }

    /** @return list<array<string,mixed>> */
    private function lookupUsers(): array
    {
        return $this->db->table('users')->select('id, name')->where('status', 'active')->orderBy('name')->limit(200)->get()->getResultArray();
    }

    /** @return list<array<string,mixed>> */
    private function lookupCategories(string $type): array
    {
        if (! $this->db->tableExists('finance_categories')) {
            return [];
        }

        return $this->db->table('finance_categories')
            ->where('category_type', $type)
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get()->getResultArray();
    }

    /** @return array<string,mixed>|null */
    private function loadVoucherRecord(string $type, int $id): ?array
    {
        $table = match ($type) {
            'deposit'    => 'finance_deposits',
            'withdrawal' => 'finance_withdrawals',
            'transfer'   => 'finance_transfers',
            default      => null,
        };
        if (! $table || ! $this->db->tableExists($table)) {
            return null;
        }

        return $this->db->table($table)->where('id', $id)->get()->getRowArray() ?: null;
    }
}
