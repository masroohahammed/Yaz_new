<?php

namespace App\Services\Finance;

use CodeIgniter\Database\BaseConnection;

class PettyCashService
{
    public function __construct(
        private BaseConnection $db,
        private ?FinanceLedgerService $ledger = null,
        private ?FinanceApprovalService $approval = null
    ) {
        $this->ledger   ??= new FinanceLedgerService($this->db);
        $this->approval ??= new FinanceApprovalService($this->db);
    }

    public function generateNumber(string $type): string
    {
        $prefix = match ($type) {
            'expense'       => $this->setting('petty_prefix_expense', 'PCE'),
            'advance'       => $this->setting('petty_prefix_advance', 'PCA'),
            'replenishment' => $this->setting('petty_prefix_replenishment', 'PCR'),
            'transfer'      => $this->setting('petty_prefix_transfer', 'PCT'),
            'count'         => $this->setting('petty_prefix_count', 'PCC'),
            default         => 'PC',
        };
        $table = match ($type) {
            'expense'       => 'finance_petty_expenses',
            'advance'       => 'finance_petty_advances',
            'replenishment' => 'finance_petty_replenishments',
            'transfer'      => 'finance_petty_transfers',
            'count'         => 'finance_petty_counts',
            default         => 'finance_petty_cash_accounts',
        };
        $column = match ($type) {
            'expense'       => 'expense_number',
            'advance'       => 'advance_number',
            'replenishment' => 'replenishment_number',
            'transfer'      => 'transfer_number',
            'count'         => 'count_number',
            default         => 'account_code',
        };

        if (! $this->db->tableExists($table)) {
            return $prefix . '-' . date('Y') . '-00001';
        }

        $like = $prefix . '-' . date('Y') . '-';
        $last = $this->db->table($table)->like($column, $like, 'after')->orderBy('id', 'DESC')->limit(1)->get()->getRowArray();
        $seq  = 1;
        if ($last && ! empty($last[$column])) {
            helper('fm');
            $seq = fm_sequence_from_code((string) $last[$column]) + 1;
        }

        return $like . str_pad((string) $seq, 5, '0', STR_PAD_LEFT);
    }

    /** @return list<array<string,mixed>> */
    public function accounts(array $filters = []): array
    {
        if (! $this->db->tableExists('finance_petty_cash_accounts')) {
            return [];
        }

        $q = $this->db->table('finance_petty_cash_accounts pa')
            ->select('pa.*, u.name AS custodian_name, fb.name AS branch_name, f.name AS facility_name')
            ->join('users u', 'u.id = pa.custodian_user_id', 'left')
            ->join('finance_branches fb', 'fb.id = pa.branch_id', 'left')
            ->join('facilities f', 'f.id = pa.facility_id', 'left')
            ->orderBy('pa.name');

        if (! empty($filters['status'])) {
            $q->where('pa.status', $filters['status']);
        } else {
            $q->where('pa.status !=', 'closed');
        }
        if (! empty($filters['branch_id'])) {
            $q->where('pa.branch_id', (int) $filters['branch_id']);
        }
        if (! empty($filters['facility_id'])) {
            $q->where('pa.facility_id', (int) $filters['facility_id']);
        }
        if (! empty($filters['custodian_user_id'])) {
            $q->where('pa.custodian_user_id', (int) $filters['custodian_user_id']);
        }

        return $q->get()->getResultArray();
    }

    public function account(int $id): ?array
    {
        if (! $this->db->tableExists('finance_petty_cash_accounts')) {
            return null;
        }

        return $this->db->table('finance_petty_cash_accounts pa')
            ->select('pa.*, u.name AS custodian_name, u.email AS custodian_email')
            ->join('users u', 'u.id = pa.custodian_user_id', 'left')
            ->where('pa.id', $id)->get()->getRowArray() ?: null;
    }

    /** @param array<string,mixed> $data */
    public function createAccount(array $data, int $userId): int
    {
        $now     = date('Y-m-d H:i:s');
        $opening = (float) ($data['opening_balance'] ?? 0);
        $code    = $data['account_code'] ?? $this->generateAccountCode();

        $this->db->table('finance_petty_cash_accounts')->insert([
            'account_code'          => $code,
            'name'                  => $data['name'],
            'company_id'            => $data['company_id'] ?? null,
            'branch_id'             => $data['branch_id'] ?? null,
            'facility_id'           => $data['facility_id'] ?? null,
            'department'            => $data['department'] ?? null,
            'currency'              => $data['currency'] ?? 'QAR',
            'custodian_user_id'     => $data['custodian_user_id'] ?? null,
            'custodian_assigned_at' => ! empty($data['custodian_user_id']) ? $now : null,
            'opening_balance'       => $opening,
            'opening_balance_date'  => $data['opening_balance_date'] ?? date('Y-m-d'),
            'max_cash_limit'        => $data['max_cash_limit'] ?? null,
            'replenishment_level'   => $data['replenishment_level'] ?? null,
            'current_balance'       => $opening,
            'status'                => $data['status'] ?? 'active',
            'notes'                 => $data['notes'] ?? null,
            'created_by'            => $userId,
            'created_at'            => $now,
            'updated_at'            => $now,
        ]);

        $id = (int) $this->db->insertID();

        if (! empty($data['custodian_user_id'])) {
            $this->recordCustodianChange($id, null, (int) $data['custodian_user_id'], $userId, 'Initial assignment');
        }

        if ($opening != 0.0) {
            $this->ledger->postTransaction([
                'account_type'     => 'petty',
                'account_id'       => $id,
                'transaction_type' => 'opening_balance',
                'credit'           => $opening,
                'currency'         => $data['currency'] ?? 'QAR',
                'description'      => 'Petty cash opening balance',
                'status'           => 'posted',
                'posted_by'        => $userId,
                'created_by'       => $userId,
            ]);
        }

        return $id;
    }

    public function transferCustodian(int $accountId, int $toUserId, int $byUserId, ?string $reason = null): bool
    {
        $acct = $this->account($accountId);
        if (! $acct) {
            return false;
        }

        $from = (int) ($acct['custodian_user_id'] ?? 0);
        $this->db->table('finance_petty_cash_accounts')->where('id', $accountId)->update([
            'custodian_user_id'     => $toUserId,
            'custodian_assigned_at' => date('Y-m-d H:i:s'),
            'updated_at'            => date('Y-m-d H:i:s'),
        ]);

        if ($from > 0) {
            $this->db->table('finance_petty_custodian_history')
                ->where('petty_account_id', $accountId)->where('status', 'active')
                ->update(['status' => 'transferred']);
        }

        $this->recordCustodianChange($accountId, $from ?: null, $toUserId, $byUserId, $reason);

        return true;
    }

    /** @return array<string,mixed> */
    public function dashboardKpis(?int $custodianUserId = null): array
    {
        $totalPetty   = 0.0;
        $activeCount  = 0;
        $pendingRep   = 0;
        $pendingExp   = 0;
        $pendingAppr  = 0;
        $outstanding  = 0.0;
        $shortage     = 0.0;
        $excess       = 0.0;

        if ($this->db->tableExists('finance_petty_cash_accounts')) {
            $q = $this->db->table('finance_petty_cash_accounts')->where('status', 'active');
            if ($custodianUserId) {
                $q->where('custodian_user_id', $custodianUserId);
            }
            $totalPetty  = (float) ($q->selectSum('current_balance', 't')->get()->getRowArray()['t'] ?? 0);
            $activeCount = (clone $q)->countAllResults();
        }

        if ($this->db->tableExists('finance_petty_cash_accounts')) {
            $accounts = $this->accounts(['status' => 'active']);
            foreach ($accounts as $a) {
                $level = (float) ($a['replenishment_level'] ?? 0);
                $bal   = (float) ($a['current_balance'] ?? 0);
                if ($level > 0 && $bal <= $level) {
                    $pendingRep++;
                }
            }
        }

        if ($this->db->tableExists('finance_petty_expenses')) {
            $pendingExp = $this->db->table('finance_petty_expenses')
                ->whereIn('status', ['draft', 'submitted', 'pending_approval', 'approved', 'paid'])
                ->countAllResults();
        }

        if ($this->db->tableExists('finance_petty_advances')) {
            $outstanding = (float) ($this->db->table('finance_petty_advances')
                ->selectSum('issued_amount', 't')
                ->whereIn('status', ['issued', 'outstanding'])
                ->get()->getRowArray()['t'] ?? 0);
            $pendingAppr = $this->db->table('finance_petty_advances')
                ->whereIn('status', ['requested', 'pending_approval'])
                ->countAllResults();
        }

        if ($this->db->tableExists('finance_petty_counts')) {
            $shortage = (float) ($this->db->table('finance_petty_counts')
                ->selectSum('shortage', 't')->where('status', 'submitted')->get()->getRowArray()['t'] ?? 0);
            $excess = (float) ($this->db->table('finance_petty_counts')
                ->selectSum('excess', 't')->where('status', 'submitted')->get()->getRowArray()['t'] ?? 0);
        }

        return [
            'total_petty_cash'      => $totalPetty,
            'active_accounts'       => $activeCount,
            'available_cash'        => $totalPetty,
            'pending_replenishment' => $pendingRep,
            'pending_expenses'      => $pendingExp,
            'pending_approvals'     => $pendingAppr,
            'outstanding_advances'  => $outstanding,
            'cash_shortage'         => $shortage,
            'cash_excess'           => $excess,
        ];
    }

    /** @return array<string,float> */
    public function accountSummary(int $accountId): array
    {
        $received = $repl = $exp = $adv = $adj = 0.0;
        if (! $this->db->tableExists('finance_transactions')) {
            return compact('received', 'repl', 'exp', 'adv', 'adj');
        }

        $rows = $this->db->table('finance_transactions')
            ->select('transaction_type, SUM(credit) AS cr, SUM(debit) AS dr')
            ->where('account_type', 'petty')
            ->where('account_id', $accountId)
            ->where('status', 'posted')
            ->where('is_reversal', 0)
            ->groupBy('transaction_type')
            ->get()->getResultArray();

        foreach ($rows as $r) {
            match ($r['transaction_type']) {
                'cash_received', 'replenishment', 'cash_return' => $received += (float) $r['cr'],
                'cash_expense' => $exp += (float) $r['dr'],
                'cash_advance' => $adv += (float) $r['dr'],
                'cash_adjustment', 'adjustment' => $adj += (float) $r['cr'] - (float) $r['dr'],
                default => null,
            };
        }

        return [
            'cash_received'   => $received,
            'replenishments'  => $repl,
            'expenses'        => $exp,
            'advances'        => $adv,
            'adjustments'     => $adj,
        ];
    }

    /** @param array<string,mixed> $data */
    public function createExpense(array $data, int $userId): int
    {
        $num = $this->generateNumber('expense');
        $this->db->table('finance_petty_expenses')->insert([
            'expense_number'      => $num,
            'expense_date'        => $data['expense_date'] ?? date('Y-m-d'),
            'petty_account_id'    => (int) $data['petty_account_id'],
            'custodian_user_id'   => $data['custodian_user_id'] ?? null,
            'category_id'         => $data['category_id'] ?? null,
            'amount'              => (float) $data['amount'],
            'currency'            => $data['currency'] ?? 'QAR',
            'vendor_id'           => $data['vendor_id'] ?? null,
            'employee_id'         => $data['employee_id'] ?? null,
            'branch_id'           => $data['branch_id'] ?? null,
            'facility_id'         => $data['facility_id'] ?? null,
            'unit_id'             => $data['unit_id'] ?? null,
            'department'          => $data['department'] ?? null,
            'work_order_id'       => $data['work_order_id'] ?? null,
            'asset_id'            => $data['asset_id'] ?? null,
            'purchase_request_id' => $data['purchase_request_id'] ?? null,
            'receipt_number'      => $data['receipt_number'] ?? null,
            'description'         => $data['description'] ?? null,
            'attachment'          => $data['attachment'] ?? null,
            'notes'               => $data['notes'] ?? null,
            'status'              => 'draft',
            'created_by'          => $userId,
            'created_at'          => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insertID();
    }

    public function submitExpense(int $id, int $userId): array
    {
        $exp = $this->db->table('finance_petty_expenses')->where('id', $id)->get()->getRowArray();
        if (! $exp || ! in_array($exp['status'], ['draft', 'submitted'], true)) {
            return ['ok' => false, 'message' => 'Expense cannot be submitted.'];
        }

        if ((int) ($exp['created_by'] ?? 0) === $userId && $this->setting('petty_self_approval_override', '0') === '0') {
            // submit only — approval by someone else
        }

        $this->db->table('finance_petty_expenses')->where('id', $id)->update(['status' => 'pending_approval']);

        return ['ok' => true, 'message' => 'Expense submitted for approval.'];
    }

    public function approveExpense(int $id, int $userId, string $userRole): array
    {
        $exp = $this->db->table('finance_petty_expenses')->where('id', $id)->get()->getRowArray();
        if (! $exp || $exp['status'] !== 'pending_approval') {
            return ['ok' => false, 'message' => 'Expense is not pending approval.'];
        }
        if ((int) $exp['created_by'] === $userId && $this->setting('petty_self_approval_override', '0') === '0') {
            return ['ok' => false, 'message' => 'You cannot approve your own expense.'];
        }

        $this->db->table('finance_petty_expenses')->where('id', $id)->update([
            'status'      => 'approved',
            'approved_by' => $userId,
        ]);

        return ['ok' => true, 'message' => 'Expense approved.'];
    }

    public function postExpense(int $id, int $userId): bool
    {
        $exp = $this->db->table('finance_petty_expenses')->where('id', $id)->get()->getRowArray();
        if (! $exp || ! in_array($exp['status'], ['approved', 'paid'], true)) {
            return false;
        }

        $exists = $this->db->table('finance_transactions')
            ->where('reference_type', 'petty_expense')->where('reference_id', $id)
            ->where('status', 'posted')->countAllResults();
        if ($exists) {
            return false;
        }

        $amount = (float) $exp['amount'];
        $this->ledger->postTransaction([
            'transaction_date'  => $exp['expense_date'],
            'account_type'      => 'petty',
            'account_id'        => (int) $exp['petty_account_id'],
            'transaction_type'  => 'cash_expense',
            'debit'             => $amount,
            'currency'          => $exp['currency'],
            'reference_type'    => 'petty_expense',
            'reference_id'      => $id,
            'facility_id'       => $exp['facility_id'],
            'unit_id'           => $exp['unit_id'],
            'branch_id'         => $exp['branch_id'],
            'vendor_id'         => $exp['vendor_id'],
            'work_order_id'     => $exp['work_order_id'],
            'category_id'       => $exp['category_id'],
            'description'       => $exp['description'] ?? ('Petty expense ' . $exp['expense_number']),
            'counts_as_expense' => 1,
            'created_by'        => $exp['created_by'],
            'approved_by'       => $exp['approved_by'],
            'posted_by'         => $userId,
        ]);

        $this->db->table('finance_petty_expenses')->where('id', $id)->update([
            'status'    => 'posted',
            'posted_by' => $userId,
            'posted_at' => date('Y-m-d H:i:s'),
        ]);

        $this->syncWorkOrderCost((int) ($exp['work_order_id'] ?? 0), $amount);
        $this->checkReplenishmentAlert((int) $exp['petty_account_id']);

        return true;
    }

    /** @param array<string,mixed> $data */
    public function createAdvance(array $data, int $userId): int
    {
        $num = $this->generateNumber('advance');
        $this->db->table('finance_petty_advances')->insert([
            'advance_number'           => $num,
            'petty_account_id'         => (int) $data['petty_account_id'],
            'employee_id'              => (int) $data['employee_id'],
            'branch_id'                => $data['branch_id'] ?? null,
            'facility_id'              => $data['facility_id'] ?? null,
            'department'               => $data['department'] ?? null,
            'amount'                   => (float) $data['amount'],
            'currency'                 => $data['currency'] ?? 'QAR',
            'purpose'                  => $data['purpose'] ?? null,
            'required_date'            => $data['required_date'] ?? null,
            'expected_settlement_date' => $data['expected_settlement_date'] ?? null,
            'attachment'               => $data['attachment'] ?? null,
            'notes'                    => $data['notes'] ?? null,
            'status'                   => 'requested',
            'created_by'               => $userId,
            'created_at'               => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insertID();
    }

    public function approveAdvance(int $id, int $userId): array
    {
        $adv = $this->db->table('finance_petty_advances')->where('id', $id)->get()->getRowArray();
        if (! $adv || ! in_array($adv['status'], ['requested', 'pending_approval'], true)) {
            return ['ok' => false, 'message' => 'Advance not pending approval.'];
        }
        if ((int) $adv['created_by'] === $userId && $this->setting('petty_self_approval_override', '0') === '0') {
            return ['ok' => false, 'message' => 'You cannot approve your own advance.'];
        }

        $this->db->table('finance_petty_advances')->where('id', $id)->update([
            'status'      => 'approved',
            'approved_by' => $userId,
        ]);

        return ['ok' => true, 'message' => 'Advance approved.'];
    }

    public function issueAdvance(int $id, int $userId): bool
    {
        $adv = $this->db->table('finance_petty_advances')->where('id', $id)->get()->getRowArray();
        if (! $adv || $adv['status'] !== 'approved') {
            return false;
        }

        $amount = (float) $adv['amount'];
        $this->ledger->postTransaction([
            'transaction_date' => date('Y-m-d'),
            'account_type'     => 'petty',
            'account_id'       => (int) $adv['petty_account_id'],
            'transaction_type' => 'cash_advance',
            'debit'            => $amount,
            'currency'         => $adv['currency'],
            'reference_type'   => 'petty_advance',
            'reference_id'     => $id,
            'facility_id'      => $adv['facility_id'],
            'branch_id'        => $adv['branch_id'],
            'description'      => 'Cash advance ' . $adv['advance_number'],
            'created_by'       => $userId,
            'posted_by'        => $userId,
        ]);

        $this->db->table('finance_petty_advances')->where('id', $id)->update([
            'status'        => 'outstanding',
            'issued_amount' => $amount,
            'issued_by'     => $userId,
            'issued_at'     => date('Y-m-d H:i:s'),
        ]);

        $this->checkReplenishmentAlert((int) $adv['petty_account_id']);

        return true;
    }

    /** @param array<string,mixed> $data */
    public function settleAdvance(int $advanceId, array $data, int $userId): array
    {
        $adv = $this->db->table('finance_petty_advances')->where('id', $advanceId)->get()->getRowArray();
        if (! $adv || ! in_array($adv['status'], ['issued', 'outstanding'], true)) {
            return ['ok' => false, 'message' => 'Advance is not outstanding.'];
        }

        $issued   = (float) $adv['issued_amount'];
        $expense  = (float) ($data['expense_amount'] ?? 0);
        $returned = (float) ($data['return_amount'] ?? 0);
        $extra    = (float) ($data['additional_payment'] ?? 0);

        if (round($expense + $returned - $extra, 2) !== round($issued, 2)) {
            return ['ok' => false, 'message' => 'Settlement amounts do not match issued advance.'];
        }

        $this->db->table('finance_petty_advance_settlements')->insert([
            'advance_id'         => $advanceId,
            'settlement_date'    => $data['settlement_date'] ?? date('Y-m-d'),
            'expense_amount'     => $expense,
            'return_amount'      => $returned,
            'additional_payment' => $extra,
            'notes'              => $data['notes'] ?? null,
            'status'             => 'posted',
            'created_by'         => $userId,
            'posted_by'          => $userId,
            'posted_at'          => date('Y-m-d H:i:s'),
            'created_at'         => date('Y-m-d H:i:s'),
        ]);

        if ($expense > 0) {
            $this->ledger->postTransaction([
                'transaction_date'  => date('Y-m-d'),
                'account_type'      => 'petty',
                'account_id'        => (int) $adv['petty_account_id'],
                'transaction_type'  => 'advance_settlement',
                'debit'             => $expense,
                'currency'          => $adv['currency'],
                'reference_type'    => 'petty_advance_settlement',
                'reference_id'      => $advanceId,
                'description'       => 'Advance settlement expense',
                'counts_as_expense' => 1,
                'posted_by'         => $userId,
            ]);
        }

        if ($returned > 0) {
            $this->ledger->postTransaction([
                'transaction_date' => date('Y-m-d'),
                'account_type'     => 'petty',
                'account_id'       => (int) $adv['petty_account_id'],
                'transaction_type' => 'cash_return',
                'credit'           => $returned,
                'currency'         => $adv['currency'],
                'reference_type'   => 'petty_advance_settlement',
                'reference_id'     => $advanceId,
                'description'      => 'Advance cash return',
                'posted_by'        => $userId,
            ]);
        }

        $this->db->table('finance_petty_advances')->where('id', $advanceId)->update([
            'status'          => 'settled',
            'settled_amount'  => $expense,
            'returned_amount' => $returned,
            'settled_at'      => date('Y-m-d H:i:s'),
        ]);

        return ['ok' => true, 'message' => 'Advance settled.'];
    }

    public function postReplenishment(int $id, int $userId): bool
    {
        $rep = $this->db->table('finance_petty_replenishments')->where('id', $id)->get()->getRowArray();
        if (! $rep || $rep['status'] !== 'approved') {
            return false;
        }

        $amount = (float) $rep['amount'];
        $this->ledger->postTransaction([
            'transaction_date' => $rep['replenishment_date'],
            'account_type'     => $rep['source_account_type'],
            'account_id'       => (int) $rep['source_account_id'],
            'transaction_type' => 'bank_transfer',
            'debit'            => $amount,
            'currency'         => $rep['currency'],
            'reference_type'   => 'petty_replenishment',
            'reference_id'     => $id,
            'description'      => 'Replenishment out ' . $rep['replenishment_number'],
            'posted_by'        => $userId,
        ]);

        $this->ledger->postTransaction([
            'transaction_date' => $rep['replenishment_date'],
            'account_type'     => 'petty',
            'account_id'       => (int) $rep['petty_account_id'],
            'transaction_type' => 'replenishment',
            'credit'           => $amount,
            'currency'         => $rep['currency'],
            'reference_type'   => 'petty_replenishment',
            'reference_id'     => $id,
            'description'      => 'Replenishment in ' . $rep['replenishment_number'],
            'posted_by'        => $userId,
        ]);

        $this->db->table('finance_petty_replenishments')->where('id', $id)->update([
            'status'    => 'posted',
            'posted_by' => $userId,
            'posted_at' => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    public function postTransfer(int $id, int $userId): bool
    {
        $t = $this->db->table('finance_petty_transfers')->where('id', $id)->get()->getRowArray();
        if (! $t || $t['status'] !== 'approved') {
            return false;
        }

        $amount = (float) $t['amount'];
        $outId  = $this->ledger->postTransaction([
            'transaction_date' => $t['transfer_date'],
            'account_type'     => 'petty',
            'account_id'       => (int) $t['from_petty_account_id'],
            'transaction_type' => 'petty_transfer',
            'debit'            => $amount,
            'currency'         => $t['currency'],
            'reference_type'   => 'petty_transfer',
            'reference_id'     => $id,
            'description'      => 'Petty transfer out',
            'posted_by'        => $userId,
        ]);

        $inId = $this->ledger->postTransaction([
            'transaction_date'      => $t['transfer_date'],
            'account_type'          => 'petty',
            'account_id'            => (int) $t['to_petty_account_id'],
            'transaction_type'      => 'petty_transfer',
            'credit'                => $amount,
            'currency'              => $t['currency'],
            'reference_type'        => 'petty_transfer',
            'reference_id'          => $id,
            'linked_transaction_id' => $outId,
            'description'           => 'Petty transfer in',
            'posted_by'             => $userId,
        ]);

        if ($outId && $inId) {
            $this->db->table('finance_transactions')->where('id', $outId)->update(['linked_transaction_id' => $inId]);
        }

        $this->db->table('finance_petty_transfers')->where('id', $id)->update([
            'status'    => 'posted',
            'posted_by' => $userId,
            'posted_at' => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    /** @param list<array{denomination:string,quantity:int,line_total:float}> $lines */
    public function createCount(int $accountId, string $date, array $lines, int $userId, ?string $reason = null): int
    {
        $system  = $this->ledger->runningBalance('petty', $accountId);
        $physical = array_sum(array_column($lines, 'line_total'));
        $diff    = round($physical - $system, 2);
        $short   = $diff < 0 ? abs($diff) : 0;
        $excess  = $diff > 0 ? $diff : 0;

        $num = $this->generateNumber('count');
        $this->db->table('finance_petty_counts')->insert([
            'count_number'      => $num,
            'petty_account_id'  => $accountId,
            'count_date'        => $date,
            'system_balance'    => $system,
            'physical_total'    => $physical,
            'difference'        => $diff,
            'shortage'          => $short,
            'excess'            => $excess,
            'reason'            => $reason,
            'status'            => 'submitted',
            'counted_by'        => $userId,
            'created_at'        => date('Y-m-d H:i:s'),
        ]);

        $countId = (int) $this->db->insertID();
        foreach ($lines as $line) {
            $this->db->table('finance_petty_count_lines')->insert([
                'count_id'     => $countId,
                'denomination' => $line['denomination'],
                'quantity'     => (int) $line['quantity'],
                'line_total'   => (float) $line['line_total'],
            ]);
        }

        $this->db->table('finance_petty_cash_accounts')->where('id', $accountId)->update([
            'physical_balance' => $physical,
            'last_count_date'  => $date,
        ]);

        return $countId;
    }

    public function approveCountAdjustment(int $countId, int $userId): bool
    {
        $count = $this->db->table('finance_petty_counts')->where('id', $countId)->get()->getRowArray();
        if (! $count || $count['status'] !== 'submitted') {
            return false;
        }

        $acctId = (int) $count['petty_account_id'];
        if ((float) $count['shortage'] > 0) {
            $this->ledger->postTransaction([
                'transaction_date' => $count['count_date'],
                'account_type'     => 'petty',
                'account_id'       => $acctId,
                'transaction_type' => 'cash_adjustment',
                'debit'            => (float) $count['shortage'],
                'reference_type'   => 'petty_count',
                'reference_id'     => $countId,
                'description'      => 'Cash shortage adjustment',
                'notes'            => $count['reason'],
                'counts_as_expense'=> 1,
                'posted_by'        => $userId,
            ]);
        }
        if ((float) $count['excess'] > 0) {
            $this->ledger->postTransaction([
                'transaction_date' => $count['count_date'],
                'account_type'     => 'petty',
                'account_id'       => $acctId,
                'transaction_type' => 'cash_adjustment',
                'credit'           => (float) $count['excess'],
                'reference_type'   => 'petty_count',
                'reference_id'     => $countId,
                'description'      => 'Cash excess adjustment',
                'notes'            => $count['reason'],
                'posted_by'        => $userId,
            ]);
        }

        $this->db->table('finance_petty_counts')->where('id', $countId)->update([
            'status'      => 'posted',
            'approved_by' => $userId,
            'posted_by'   => $userId,
        ]);

        return true;
    }

    public function logAudit(int $userId, string $role, string $action, string $module, ?int $recordId, ?string $old, ?string $new, ?string $reason, ?string $ip): void
    {
        if (! $this->db->tableExists('finance_petty_audit_logs')) {
            return;
        }

        $this->db->table('finance_petty_audit_logs')->insert([
            'user_id'    => $userId,
            'user_role'  => $role,
            'action'     => $action,
            'module'     => $module,
            'record_id'  => $recordId,
            'old_value'  => $old,
            'new_value'  => $new,
            'reason'     => $reason,
            'ip_address' => $ip,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function generateAccountCode(): string
    {
        $prefix = 'PC-A';
        $last   = $this->db->table('finance_petty_cash_accounts')->like('account_code', $prefix, 'after')->orderBy('id', 'DESC')->limit(1)->get()->getRowArray();
        $seq    = 1;
        if ($last) {
            $seq = (int) preg_replace('/\D/', '', (string) $last['account_code']) + 1;
        }

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    private function recordCustodianChange(int $accountId, ?int $from, int $to, int $by, ?string $reason): void
    {
        if (! $this->db->tableExists('finance_petty_custodian_history')) {
            return;
        }

        $this->db->table('finance_petty_custodian_history')->insert([
            'petty_account_id' => $accountId,
            'from_user_id'     => $from,
            'to_user_id'       => $to,
            'assigned_at'      => date('Y-m-d H:i:s'),
            'assigned_by'      => $by,
            'reason'           => $reason,
            'status'           => 'active',
        ]);
    }

    private function checkReplenishmentAlert(int $accountId): void
    {
        $acct = $this->account($accountId);
        if (! $acct) {
            return;
        }

        $level = (float) ($acct['replenishment_level'] ?? 0);
        $bal   = (float) ($acct['current_balance'] ?? 0);
        if ($level <= 0 || $bal > $level) {
            return;
        }

        $managers = $this->db->table('users u')
            ->select('u.id')->join('roles r', 'r.id = u.role_id')
            ->whereIn('r.name', ['super_admin', 'finance_manager'])
            ->where('u.status', 'active')->get()->getResultArray();

        foreach ($managers as $m) {
            $this->db->table('notifications')->insert([
                'user_id'    => (int) $m['id'],
                'title'      => 'Petty cash replenishment required',
                'message'    => $acct['name'] . ' balance ' . number_format($bal, 2) . ' is at/below replenishment level.',
                'type'       => 'finance',
                'is_read'    => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function syncWorkOrderCost(int $woId, float $amount): void
    {
        if ($woId < 1 || ! $this->db->tableExists('work_orders')) {
            return;
        }

        $wo = $this->db->table('work_orders')->where('id', $woId)->get()->getRowArray();
        if (! $wo) {
            return;
        }

        $field = $this->db->fieldExists('actual_cost', 'work_orders') ? 'actual_cost' : null;
        if ($field) {
            $this->db->table('work_orders')->where('id', $woId)->update([
                $field => round((float) ($wo[$field] ?? 0) + $amount, 2),
            ]);
        }

        if ($this->db->tableExists('finance_entries') && ! empty($wo['facility_id'])) {
            $exists = $this->db->table('finance_entries')
                ->where('reference_type', 'petty_expense')
                ->where('reference_id', $woId)
                ->where('amount', $amount)
                ->countAllResults();
            if (! $exists) {
                $this->db->table('finance_entries')->insert([
                    'entry_date'     => date('Y-m-d'),
                    'facility_id'    => $wo['facility_id'],
                    'direction'      => 'expense',
                    'amount'         => $amount,
                    'description'    => 'Petty cash — WO ' . ($wo['wo_number'] ?? $woId),
                    'reference_type' => 'work_order',
                    'reference_id'   => $woId,
                    'created_at'     => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    private function setting(string $key, string $default = ''): string
    {
        if (! $this->db->tableExists('system_settings')) {
            return $default;
        }

        $row = $this->db->table('system_settings')->where('setting_key', $key)->get()->getRowArray();

        return (string) ($row['setting_value'] ?? $default);
    }
}
