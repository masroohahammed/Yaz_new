<?php

namespace App\Services\Finance;

use CodeIgniter\Database\BaseConnection;

class FinanceAccountService
{
    public function __construct(private BaseConnection $db)
    {
    }

    public function maskAccountNumber(?string $number): string
    {
        $number = preg_replace('/\s+/', '', (string) $number);
        if ($number === '') {
            return '—';
        }
        $last4 = substr($number, -4);

        return 'XXXX XXXX ' . $last4;
    }

    /** @return list<array<string,mixed>> */
    public function bankAccounts(array $filters = []): array
    {
        if (! $this->db->tableExists('finance_bank_accounts')) {
            return [];
        }

        $q = $this->db->table('finance_bank_accounts ba')
            ->select('ba.*, fb.name AS branch_label, f.name AS facility_name')
            ->join('finance_branches fb', 'fb.id = ba.branch_id', 'left')
            ->join('facilities f', 'f.id = ba.facility_id', 'left')
            ->orderBy('ba.name', 'ASC');

        if (! empty($filters['status'])) {
            $q->where('ba.status', $filters['status']);
        } else {
            $q->where('ba.status !=', 'closed');
        }
        if (! empty($filters['branch_id'])) {
            $q->where('ba.branch_id', (int) $filters['branch_id']);
        }
        if (! empty($filters['facility_id'])) {
            $q->where('ba.facility_id', (int) $filters['facility_id']);
        }
        if (! empty($filters['company_id'])) {
            $q->where('ba.company_id', (int) $filters['company_id']);
        }

        return $q->get()->getResultArray();
    }

    public function bankAccount(int $id): ?array
    {
        if (! $this->db->tableExists('finance_bank_accounts')) {
            return null;
        }

        return $this->db->table('finance_bank_accounts')->where('id', $id)->get()->getRowArray() ?: null;
    }

    /** @param array<string,mixed> $data */
    public function createBankAccount(array $data, int $userId): int
    {
        $now = date('Y-m-d H:i:s');
        $opening = (float) ($data['opening_balance'] ?? 0);

        $row = [
            'company_id'                 => $data['company_id'] ?? null,
            'branch_id'                  => $data['branch_id'] ?? null,
            'facility_id'                => $data['facility_id'] ?? null,
            'department'                 => $data['department'] ?? null,
            'scope_type'                 => $data['scope_type'] ?? 'company',
            'name'                       => $data['name'],
            'bank_name'                  => $data['bank_name'] ?? null,
            'branch_name'                => $data['branch_name'] ?? null,
            'account_number'             => $data['account_number'] ?? null,
            'iban'                       => $data['iban'] ?? null,
            'swift_bic'                  => $data['swift_bic'] ?? null,
            'currency'                   => $data['currency'] ?? 'QAR',
            'account_type'               => $data['account_type'] ?? 'current',
            'opening_balance'            => $opening,
            'opening_balance_date'       => $data['opening_balance_date'] ?? date('Y-m-d'),
            'opening_balance_notes'      => $data['opening_balance_notes'] ?? null,
            'opening_balance_created_by' => $userId,
            'opening_balance_created_at' => $now,
            'bank_contact'               => $data['bank_contact'] ?? null,
            'bank_address'               => $data['bank_address'] ?? null,
            'notes'                      => $data['notes'] ?? null,
            'status'                     => $data['status'] ?? 'active',
            'is_active'                  => ($data['status'] ?? 'active') === 'active' ? 1 : 0,
            'current_balance'            => $opening,
            'available_balance'        => $opening,
            'min_balance_alert'          => $data['min_balance_alert'] ?? null,
            'account_opening_date'       => $data['account_opening_date'] ?? null,
            'gl_account_id'              => $data['gl_account_id'] ?? null,
            'created_by'                 => $userId,
            'created_at'                 => $now,
            'updated_at'                 => $now,
        ];

        $this->db->table('finance_bank_accounts')->insert($row);
        $id = (int) $this->db->insertID();

        if ($opening != 0.0) {
            (new FinanceLedgerService($this->db))->postOpeningBalance('bank', $id, $opening, $row['currency'], $userId, $row['opening_balance_date']);
        }

        return $id;
    }

    /** @param array<string,mixed> $data */
    public function updateBankAccount(int $id, array $data, int $userId, bool $canAdjustOpening = false): bool
    {
        $existing = $this->bankAccount($id);
        if (! $existing) {
            return false;
        }

        $hasTx = $this->accountHasPostedTransactions('bank', $id);
        $update = [
            'name'                => $data['name'] ?? $existing['name'],
            'bank_name'           => $data['bank_name'] ?? $existing['bank_name'],
            'branch_name'         => $data['branch_name'] ?? $existing['branch_name'],
            'account_number'      => $data['account_number'] ?? $existing['account_number'],
            'iban'                => $data['iban'] ?? $existing['iban'],
            'swift_bic'           => $data['swift_bic'] ?? $existing['swift_bic'],
            'currency'            => $data['currency'] ?? $existing['currency'],
            'account_type'        => $data['account_type'] ?? $existing['account_type'],
            'bank_contact'        => $data['bank_contact'] ?? $existing['bank_contact'],
            'bank_address'        => $data['bank_address'] ?? $existing['bank_address'],
            'notes'               => $data['notes'] ?? $existing['notes'],
            'branch_id'           => $data['branch_id'] ?? $existing['branch_id'],
            'facility_id'         => $data['facility_id'] ?? $existing['facility_id'],
            'department'          => $data['department'] ?? $existing['department'],
            'scope_type'          => $data['scope_type'] ?? $existing['scope_type'],
            'company_id'          => $data['company_id'] ?? $existing['company_id'],
            'min_balance_alert'   => $data['min_balance_alert'] ?? $existing['min_balance_alert'],
            'account_opening_date'=> $data['account_opening_date'] ?? $existing['account_opening_date'],
            'updated_at'          => date('Y-m-d H:i:s'),
        ];

        if (isset($data['status'])) {
            $update['status']    = $data['status'];
            $update['is_active'] = $data['status'] === 'active' ? 1 : 0;
        }

        if ($canAdjustOpening && ! $hasTx && isset($data['opening_balance'])) {
            $update['opening_balance']      = (float) $data['opening_balance'];
            $update['opening_balance_date'] = $data['opening_balance_date'] ?? $existing['opening_balance_date'];
        }

        $this->db->table('finance_bank_accounts')->where('id', $id)->update($update);

        return true;
    }

    public function closeBankAccount(int $id, int $userId): bool
    {
        return (bool) $this->db->table('finance_bank_accounts')->where('id', $id)->update([
            'status'     => 'closed',
            'is_active'  => 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /** @return list<array<string,mixed>> */
    public function cashAccounts(array $filters = []): array
    {
        if (! $this->db->tableExists('finance_cash_accounts')) {
            return [];
        }

        $q = $this->db->table('finance_cash_accounts ca')
            ->select('ca.*, fb.name AS branch_label, f.name AS facility_name, u.name AS responsible_name')
            ->join('finance_branches fb', 'fb.id = ca.branch_id', 'left')
            ->join('facilities f', 'f.id = ca.facility_id', 'left')
            ->join('users u', 'u.id = ca.responsible_user_id', 'left')
            ->orderBy('ca.name', 'ASC');

        if (! empty($filters['status'])) {
            $q->where('ca.status', $filters['status']);
        } else {
            $q->where('ca.status !=', 'closed');
        }

        return $q->get()->getResultArray();
    }

    public function cashAccount(int $id): ?array
    {
        if (! $this->db->tableExists('finance_cash_accounts')) {
            return null;
        }

        return $this->db->table('finance_cash_accounts')->where('id', $id)->get()->getRowArray() ?: null;
    }

    /** @param array<string,mixed> $data */
    public function createCashAccount(array $data, int $userId): int
    {
        $now     = date('Y-m-d H:i:s');
        $opening = (float) ($data['opening_balance'] ?? 0);

        $this->db->table('finance_cash_accounts')->insert([
            'company_id'            => $data['company_id'] ?? null,
            'branch_id'             => $data['branch_id'] ?? null,
            'facility_id'           => $data['facility_id'] ?? null,
            'department'            => $data['department'] ?? null,
            'name'                  => $data['name'],
            'account_type'          => $data['account_type'] ?? 'main',
            'currency'              => $data['currency'] ?? 'QAR',
            'opening_balance'       => $opening,
            'opening_balance_date'  => $data['opening_balance_date'] ?? date('Y-m-d'),
            'opening_balance_notes' => $data['opening_balance_notes'] ?? null,
            'responsible_user_id'   => $data['responsible_user_id'] ?? null,
            'current_balance'       => $opening,
            'available_balance'     => $opening,
            'min_balance_alert'     => $data['min_balance_alert'] ?? null,
            'status'                => $data['status'] ?? 'active',
            'notes'                 => $data['notes'] ?? null,
            'created_by'            => $userId,
            'created_at'            => $now,
            'updated_at'            => $now,
        ]);

        $id = (int) $this->db->insertID();

        if ($opening != 0.0) {
            (new FinanceLedgerService($this->db))->postOpeningBalance('cash', $id, $opening, $data['currency'] ?? 'QAR', $userId, $data['opening_balance_date'] ?? date('Y-m-d'));
        }

        return $id;
    }

    public function accountHasPostedTransactions(string $accountType, int $accountId): bool
    {
        if (! $this->db->tableExists('finance_transactions')) {
            return false;
        }

        return $this->db->table('finance_transactions')
            ->where('account_type', $accountType)
            ->where('account_id', $accountId)
            ->where('status', 'posted')
            ->where('transaction_type !=', 'opening_balance')
            ->countAllResults() > 0;
    }

    public function accountLabel(string $accountType, int $accountId): string
    {
        if ($accountType === 'bank') {
            $a = $this->bankAccount($accountId);

            return $a ? ($a['name'] . ' (' . ($a['bank_name'] ?? 'Bank') . ')') : 'Bank #' . $accountId;
        }

        $a = $this->cashAccount($accountId);

        return $a ? $a['name'] : 'Cash #' . $accountId;
    }
}
