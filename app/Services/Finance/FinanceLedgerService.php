<?php

namespace App\Services\Finance;

use CodeIgniter\Database\BaseConnection;

class FinanceLedgerService
{
    public function __construct(private BaseConnection $db)
    {
    }

    public function generateNumber(string $docType): string
    {
        $settings = new FinanceSettingsService($this->db);
        $prefix   = $settings->prefix($docType);
        $table    = match ($docType) {
            'deposit'    => 'finance_deposits',
            'withdrawal' => 'finance_withdrawals',
            'transfer'   => 'finance_transfers',
            'income'     => 'finance_income_records',
            'expense'    => 'finance_expense_records',
            default      => 'finance_transactions',
        };
        $column = match ($docType) {
            'deposit'    => 'deposit_number',
            'withdrawal' => 'withdrawal_number',
            'transfer'   => 'transfer_number',
            'income'     => 'income_number',
            'expense'    => 'expense_number',
            default      => 'transaction_number',
        };

        if (! $this->db->tableExists($table)) {
            return $prefix . '-' . date('Y') . '-0001';
        }

        $like = $prefix . '-' . date('Y') . '-';
        $last = $this->db->table($table)
            ->like($column, $like, 'after')
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        $seq = 1;
        if ($last && ! empty($last[$column])) {
            helper('fm');
            $seq = fm_sequence_from_code((string) $last[$column]) + 1;
        }

        return $like . str_pad((string) $seq, 5, '0', STR_PAD_LEFT);
    }

    public function postOpeningBalance(string $accountType, int $accountId, float $amount, string $currency, int $userId, ?string $date = null): int
    {
        if ($amount == 0.0) {
            return 0;
        }

        return $this->postTransaction([
            'transaction_date'  => $date ?? date('Y-m-d'),
            'account_type'      => $accountType,
            'account_id'        => $accountId,
            'transaction_type'  => 'opening_balance',
            'credit'            => $amount > 0 ? $amount : 0,
            'debit'             => $amount < 0 ? abs($amount) : 0,
            'currency'          => $currency,
            'description'       => 'Opening balance',
            'status'            => 'posted',
            'counts_as_income'  => 0,
            'counts_as_expense' => 0,
            'created_by'        => $userId,
            'posted_by'         => $userId,
            'posted_at'         => date('Y-m-d H:i:s'),
        ]);
    }

    /** @param array<string,mixed> $data */
    public function postTransaction(array $data): int
    {
        if (! $this->db->tableExists('finance_transactions')) {
            return 0;
        }

        $accountType = (string) $data['account_type'];
        $accountId   = (int) $data['account_id'];
        $debit       = round((float) ($data['debit'] ?? 0), 2);
        $credit      = round((float) ($data['credit'] ?? 0), 2);
        $prevBalance = $this->runningBalance($accountType, $accountId);
        $newBalance  = round($prevBalance + $credit - $debit, 2);

        $txNumber = $data['transaction_number']
            ?? $this->generateNumber($data['transaction_type'] ?? 'ledger');

        $row = [
            'transaction_number'    => $txNumber,
            'transaction_date'      => $data['transaction_date'] ?? date('Y-m-d'),
            'account_type'          => $accountType,
            'account_id'            => $accountId,
            'transaction_type'      => $data['transaction_type'],
            'debit'                 => $debit,
            'credit'                => $credit,
            'balance_after'         => $newBalance,
            'currency'              => $data['currency'] ?? 'QAR',
            'exchange_rate'         => $data['exchange_rate'] ?? null,
            'base_amount'           => $data['base_amount'] ?? null,
            'reference_type'        => $data['reference_type'] ?? null,
            'reference_id'          => $data['reference_id'] ?? null,
            'linked_transaction_id' => $data['linked_transaction_id'] ?? null,
            'branch_id'             => $data['branch_id'] ?? null,
            'facility_id'           => $data['facility_id'] ?? null,
            'unit_id'               => $data['unit_id'] ?? null,
            'department'            => $data['department'] ?? null,
            'client_id'             => $data['client_id'] ?? null,
            'vendor_id'             => $data['vendor_id'] ?? null,
            'contract_id'           => $data['contract_id'] ?? null,
            'invoice_id'            => $data['invoice_id'] ?? null,
            'work_order_id'         => $data['work_order_id'] ?? null,
            'category_id'           => $data['category_id'] ?? null,
            'payment_method'        => $data['payment_method'] ?? null,
            'description'           => $data['description'] ?? null,
            'attachment'            => $data['attachment'] ?? null,
            'notes'                 => $data['notes'] ?? null,
            'status'                => $data['status'] ?? 'posted',
            'reversal_of'           => $data['reversal_of'] ?? null,
            'is_reversal'           => (int) ($data['is_reversal'] ?? 0),
            'counts_as_income'      => (int) ($data['counts_as_income'] ?? 0),
            'counts_as_expense'     => (int) ($data['counts_as_expense'] ?? 0),
            'created_by'            => $data['created_by'] ?? null,
            'approved_by'           => $data['approved_by'] ?? null,
            'posted_by'             => $data['posted_by'] ?? null,
            'posted_at'             => $data['posted_at'] ?? date('Y-m-d H:i:s'),
            'created_at'            => date('Y-m-d H:i:s'),
        ];

        $this->db->transStart();
        $this->db->table('finance_transactions')->insert($row);
        $txId = (int) $this->db->insertID();

        if (($row['status'] ?? '') === 'posted') {
            $this->syncAccountBalance($accountType, $accountId, $newBalance, $row['transaction_date']);
            $this->checkLowBalanceAlert($accountType, $accountId, $newBalance);
        }

        $this->db->transComplete();

        return $txId;
    }

    public function runningBalance(string $accountType, int $accountId): float
    {
        if (! $this->db->tableExists('finance_transactions')) {
            return 0.0;
        }

        $row = $this->db->table('finance_transactions')
            ->select('balance_after')
            ->where('account_type', $accountType)
            ->where('account_id', $accountId)
            ->where('status', 'posted')
            ->where('is_reversal', 0)
            ->orderBy('transaction_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        if ($row) {
            return (float) $row['balance_after'];
        }

        if ($accountType === 'bank') {
            $a = $this->db->table('finance_bank_accounts')->where('id', $accountId)->get()->getRowArray();

            return (float) ($a['opening_balance'] ?? 0);
        }

        if ($accountType === 'petty') {
            $a = $this->db->table('finance_petty_cash_accounts')->where('id', $accountId)->get()->getRowArray();

            return (float) ($a['opening_balance'] ?? 0);
        }

        $a = $this->db->table('finance_cash_accounts')->where('id', $accountId)->get()->getRowArray();

        return (float) ($a['opening_balance'] ?? 0);
    }

    private function syncAccountBalance(string $accountType, int $accountId, float $balance, string $txDate): void
    {
        $table = match ($accountType) {
            'bank'  => 'finance_bank_accounts',
            'petty' => 'finance_petty_cash_accounts',
            default => 'finance_cash_accounts',
        };
        if (! $this->db->tableExists($table)) {
            return;
        }

        $update = [
            'current_balance'       => $balance,
            'last_transaction_date' => $txDate,
            'updated_at'            => date('Y-m-d H:i:s'),
        ];
        if ($this->db->fieldExists('available_balance', $table)) {
            $update['available_balance'] = $balance;
        }

        $this->db->table($table)->where('id', $accountId)->update($update);
    }

    public function postDeposit(int $depositId, int $userId): bool
    {
        $dep = $this->db->table('finance_deposits')->where('id', $depositId)->get()->getRowArray();
        if (! $dep || $dep['status'] === 'posted') {
            return false;
        }

        $exists = $this->db->table('finance_transactions')
            ->where('reference_type', 'deposit')
            ->where('reference_id', $depositId)
            ->where('status', 'posted')
            ->countAllResults();
        if ($exists) {
            return false;
        }

        $amount = (float) $dep['amount'];
        $this->postTransaction([
            'transaction_date'  => $dep['deposit_date'],
            'account_type'      => 'bank',
            'account_id'        => (int) $dep['bank_account_id'],
            'transaction_type'  => 'deposit',
            'credit'            => $amount,
            'currency'          => $dep['currency'],
            'reference_type'    => 'deposit',
            'reference_id'      => $depositId,
            'branch_id'         => $dep['branch_id'],
            'facility_id'       => $dep['facility_id'],
            'client_id'         => $dep['client_id'],
            'contract_id'       => $dep['contract_id'],
            'category_id'       => $dep['category_id'],
            'payment_method'    => $dep['payment_method'],
            'description'       => $dep['description'] ?? ('Deposit ' . $dep['deposit_number']),
            'counts_as_income'  => 0,
            'counts_as_expense' => 0,
            'created_by'        => $dep['created_by'],
            'approved_by'       => $dep['approved_by'],
            'posted_by'         => $userId,
        ]);

        $this->db->table('finance_deposits')->where('id', $depositId)->update([
            'status'    => 'posted',
            'posted_by' => $userId,
            'posted_at' => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    public function postWithdrawal(int $withdrawalId, int $userId): bool
    {
        $w = $this->db->table('finance_withdrawals')->where('id', $withdrawalId)->get()->getRowArray();
        if (! $w || $w['status'] === 'posted') {
            return false;
        }

        $accountType = ! empty($w['bank_account_id']) ? 'bank' : 'cash';
        $accountId   = (int) ($w['bank_account_id'] ?: $w['cash_account_id']);
        $amount      = (float) $w['amount'];

        $this->postTransaction([
            'transaction_date'  => $w['withdrawal_date'],
            'account_type'      => $accountType,
            'account_id'        => $accountId,
            'transaction_type'  => 'withdrawal',
            'debit'             => $amount,
            'currency'          => $w['currency'],
            'reference_type'    => 'withdrawal',
            'reference_id'      => $withdrawalId,
            'branch_id'         => $w['branch_id'],
            'facility_id'       => $w['facility_id'],
            'vendor_id'         => $w['vendor_id'],
            'category_id'       => $w['category_id'],
            'description'       => $w['description'] ?? ('Withdrawal ' . $w['withdrawal_number']),
            'counts_as_income'  => 0,
            'counts_as_expense' => 0,
            'created_by'        => $w['created_by'],
            'approved_by'       => $w['approved_by'],
            'posted_by'         => $userId,
        ]);

        $this->db->table('finance_withdrawals')->where('id', $withdrawalId)->update([
            'status'    => 'posted',
            'posted_by' => $userId,
            'posted_at' => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    public function postTransfer(int $transferId, int $userId): bool
    {
        $t = $this->db->table('finance_transfers')->where('id', $transferId)->get()->getRowArray();
        if (! $t || $t['status'] === 'posted') {
            return false;
        }

        $amount = (float) $t['amount'];
        $fee    = (float) ($t['transfer_fee'] ?? 0);
        $type   = str_contains($t['from_account_type'] . $t['to_account_type'], 'bank') ? 'bank_transfer' : 'cash_transfer';

        $outId = $this->postTransaction([
            'transaction_date'  => $t['transfer_date'],
            'account_type'      => $t['from_account_type'],
            'account_id'        => (int) $t['from_account_id'],
            'transaction_type'  => $type,
            'debit'             => $amount + $fee,
            'currency'          => $t['currency'],
            'reference_type'    => 'transfer',
            'reference_id'      => $transferId,
            'description'       => $t['purpose'] ?? ('Transfer out ' . $t['transfer_number']),
            'counts_as_income'  => 0,
            'counts_as_expense' => 0,
            'created_by'        => $t['created_by'],
            'posted_by'         => $userId,
        ]);

        $inId = $this->postTransaction([
            'transaction_date'      => $t['transfer_date'],
            'account_type'          => $t['to_account_type'],
            'account_id'            => (int) $t['to_account_id'],
            'transaction_type'      => $type,
            'credit'                => $amount,
            'currency'              => $t['currency'],
            'reference_type'        => 'transfer',
            'reference_id'          => $transferId,
            'linked_transaction_id' => $outId,
            'description'           => $t['purpose'] ?? ('Transfer in ' . $t['transfer_number']),
            'counts_as_income'      => 0,
            'counts_as_expense'     => 0,
            'created_by'            => $t['created_by'],
            'posted_by'             => $userId,
        ]);

        if ($outId && $inId) {
            $this->db->table('finance_transactions')->where('id', $outId)->update(['linked_transaction_id' => $inId]);
        }

        $this->db->table('finance_transfers')->where('id', $transferId)->update([
            'status'    => 'posted',
            'posted_by' => $userId,
            'posted_at' => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    public function reverseTransaction(int $txId, int $userId, string $reason): ?int
    {
        $orig = $this->db->table('finance_transactions')->where('id', $txId)->get()->getRowArray();
        if (! $orig || $orig['status'] !== 'posted' || (int) $orig['is_reversal'] === 1) {
            return null;
        }

        $revId = $this->postTransaction([
            'transaction_date'  => date('Y-m-d'),
            'account_type'      => $orig['account_type'],
            'account_id'        => (int) $orig['account_id'],
            'transaction_type'  => $orig['transaction_type'],
            'debit'             => (float) $orig['credit'],
            'credit'            => (float) $orig['debit'],
            'currency'          => $orig['currency'],
            'reference_type'    => $orig['reference_type'],
            'reference_id'      => $orig['reference_id'],
            'description'       => 'Reversal: ' . ($orig['description'] ?? $orig['transaction_number']),
            'notes'             => $reason,
            'status'            => 'posted',
            'reversal_of'       => $txId,
            'is_reversal'       => 1,
            'counts_as_income'  => 0,
            'counts_as_expense' => 0,
            'created_by'        => $userId,
            'posted_by'         => $userId,
        ]);

        $this->db->table('finance_transactions')->where('id', $txId)->update(['status' => 'reversed']);

        return $revId;
    }

    /** @return list<array<string,mixed>> */
    public function listTransactions(array $filters = [], int $limit = 100, int $offset = 0): array
    {
        if (! $this->db->tableExists('finance_transactions')) {
            return [];
        }

        $q = $this->db->table('finance_transactions ft')
            ->select('ft.*, f.name AS facility_name')
            ->join('facilities f', 'f.id = ft.facility_id', 'left')
            ->orderBy('ft.transaction_date', 'DESC')
            ->orderBy('ft.id', 'DESC');

        if (! empty($filters['status'])) {
            $q->where('ft.status', $filters['status']);
        }
        if (! empty($filters['account_type'])) {
            $q->where('ft.account_type', $filters['account_type']);
        }
        if (! empty($filters['account_id'])) {
            $q->where('ft.account_id', (int) $filters['account_id']);
        }
        if (! empty($filters['transaction_type'])) {
            $q->where('ft.transaction_type', $filters['transaction_type']);
        }
        if (! empty($filters['facility_id'])) {
            $q->where('ft.facility_id', (int) $filters['facility_id']);
        }
        if (! empty($filters['branch_id'])) {
            $q->where('ft.branch_id', (int) $filters['branch_id']);
        }
        if (! empty($filters['date_from'])) {
            $q->where('ft.transaction_date >=', $filters['date_from']);
        }
        if (! empty($filters['date_to'])) {
            $q->where('ft.transaction_date <=', $filters['date_to']);
        }

        return $q->limit($limit, $offset)->get()->getResultArray();
    }

    /** @return array<string,float|int> */
    public function dashboardKpis(array $filters = []): array
    {
        $bankBalance  = 0.0;
        $cashBalance  = 0.0;
        $pettyBalance = 0.0;
        $totalIncome  = 0.0;
        $totalExpense = 0.0;

        if ($this->db->tableExists('finance_bank_accounts')) {
            $bankBalance = (float) ($this->db->table('finance_bank_accounts')
                ->selectSum('current_balance', 't')
                ->where('status', 'active')
                ->get()->getRowArray()['t'] ?? 0);
        }
        if ($this->db->tableExists('finance_cash_accounts')) {
            $cashBalance = (float) ($this->db->table('finance_cash_accounts')
                ->selectSum('current_balance', 't')
                ->where('status', 'active')
                ->get()->getRowArray()['t'] ?? 0);
        }
        if ($this->db->tableExists('finance_petty_cash_accounts')) {
            $pettyBalance = (float) ($this->db->table('finance_petty_cash_accounts')
                ->selectSum('current_balance', 't')
                ->where('status', 'active')
                ->get()->getRowArray()['t'] ?? 0);
        }

        if ($this->db->tableExists('finance_transactions')) {
            $from = $filters['date_from'] ?? date('Y-01-01');
            $to   = $filters['date_to'] ?? date('Y-m-d');

            $incomeQ = $this->db->table('finance_transactions')
                ->selectSum('credit', 't')
                ->where('status', 'posted')
                ->where('is_reversal', 0)
                ->where('counts_as_income', 1)
                ->where('transaction_date >=', $from)
                ->where('transaction_date <=', $to);
            $totalIncome = (float) ($incomeQ->get()->getRowArray()['t'] ?? 0);

            $expenseQ = $this->db->table('finance_transactions')
                ->selectSum('debit', 't')
                ->where('status', 'posted')
                ->where('is_reversal', 0)
                ->where('counts_as_expense', 1)
                ->where('transaction_date >=', $from)
                ->where('transaction_date <=', $to);
            $totalExpense = (float) ($expenseQ->get()->getRowArray()['t'] ?? 0);
        }

        $pendingDeposits = $this->db->tableExists('finance_deposits')
            ? $this->db->table('finance_deposits')->whereIn('status', ['draft', 'pending_approval', 'approved'])->countAllResults()
            : 0;
        $pendingWithdrawals = $this->db->tableExists('finance_withdrawals')
            ? $this->db->table('finance_withdrawals')->whereIn('status', ['draft', 'pending_approval', 'approved'])->countAllResults()
            : 0;
        $pendingApprovals = $pendingDeposits + $pendingWithdrawals;

        $receivables = 0.0;
        $payables    = 0.0;
        if ($this->db->tableExists('invoices')) {
            $receivables = (float) ($this->db->table('invoices')
                ->selectSum('total', 't')
                ->whereIn('status', ['sent', 'overdue'])
                ->get()->getRowArray()['t'] ?? 0);
        }
        if ($this->db->tableExists('finance_vendor_bills')) {
            $payables = (float) ($this->db->table('finance_vendor_bills')
                ->selectSum('total', 't')
                ->whereIn('status', ['pending', 'approved'])
                ->get()->getRowArray()['t'] ?? 0);
        }

        return [
            'total_bank_balance'      => $bankBalance,
            'total_cash_balance'      => $cashBalance,
            'total_petty_balance'     => $pettyBalance,
            'total_available_balance' => $bankBalance + $cashBalance + $pettyBalance,
            'total_income'            => $totalIncome,
            'total_expense'           => $totalExpense,
            'net_balance'             => $totalIncome - $totalExpense,
            'pending_deposits'        => $pendingDeposits,
            'pending_withdrawals'     => $pendingWithdrawals,
            'pending_approvals'       => $pendingApprovals,
            'receivables'             => $receivables,
            'payables'                => $payables,
        ];
    }

    private function checkLowBalanceAlert(string $accountType, int $accountId, float $balance): void
    {
        $table = $accountType === 'bank' ? 'finance_bank_accounts' : 'finance_cash_accounts';
        if (! $this->db->tableExists($table)) {
            return;
        }

        $acct = $this->db->table($table)->where('id', $accountId)->get()->getRowArray();
        if (! $acct) {
            return;
        }

        $min = $acct['min_balance_alert'] ?? null;
        if ($min === null || $min === '') {
            $settings = new FinanceSettingsService($this->db);
            $min      = (float) $settings->get('fin_low_balance_default', '10000');
        } else {
            $min = (float) $min;
        }

        if ($balance >= $min) {
            return;
        }

        $managers = $this->db->table('users u')
            ->select('u.id')
            ->join('roles r', 'r.id = u.role_id')
            ->whereIn('r.name', ['super_admin', 'finance_manager'])
            ->where('u.status', 'active')
            ->get()->getResultArray();

        $label = (new FinanceAccountService($this->db))->accountLabel($accountType, $accountId);
        foreach ($managers as $mgr) {
            $this->db->table('notifications')->insert([
                'user_id'    => (int) $mgr['id'],
                'title'      => 'Low bank balance alert',
                'message'    => $label . ' balance is ' . number_format($balance, 2) . ' (below ' . number_format($min, 2) . ')',
                'type'       => 'finance',
                'is_read'    => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function logAudit(int $userId, string $role, string $action, string $module, ?int $recordId, ?int $txId, ?string $old, ?string $new, ?string $reason, ?string $ip): void
    {
        if (! $this->db->tableExists('finance_audit_logs')) {
            return;
        }

        $this->db->table('finance_audit_logs')->insert([
            'user_id'        => $userId,
            'user_role'      => $role,
            'action'         => $action,
            'module'         => $module,
            'record_id'      => $recordId,
            'transaction_id' => $txId,
            'old_value'      => $old,
            'new_value'      => $new,
            'reason'         => $reason,
            'ip_address'     => $ip,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
    }
}
