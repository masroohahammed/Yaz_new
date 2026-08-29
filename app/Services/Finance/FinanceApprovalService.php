<?php

namespace App\Services\Finance;

use CodeIgniter\Database\BaseConnection;

class FinanceApprovalService
{
    public function __construct(private BaseConnection $db)
    {
    }

    /**
     * Submit document for approval workflow.
     *
     * @return array{ok: bool, message: string}
     */
    public function submit(string $refType, int $refId, int $userId, float $amount): array
    {
        $settings = new FinanceSettingsService($this->db);
        $table    = $this->tableFor($refType);

        if (! $table || ! $this->db->tableExists($table)) {
            return ['ok' => false, 'message' => 'Invalid document type.'];
        }

        $doc = $this->db->table($table)->where('id', $refId)->get()->getRowArray();
        if (! $doc) {
            return ['ok' => false, 'message' => 'Document not found.'];
        }
        if (! in_array($doc['status'], ['draft', 'submitted'], true)) {
            return ['ok' => false, 'message' => 'Document cannot be submitted in current status.'];
        }

        if (! $settings->approvalEnabled()) {
            $this->db->table($table)->where('id', $refId)->update(['status' => 'approved']);

            return ['ok' => true, 'message' => 'Approved (workflow disabled).'];
        }

        $roles = $settings->approvalRolesForAmount($amount);
        $this->db->table($table)->where('id', $refId)->update(['status' => 'pending_approval']);

        if ($this->db->tableExists('finance_transaction_approvals')) {
            $this->db->table('finance_transaction_approvals')
                ->where('transaction_ref_type', $refType)
                ->where('transaction_ref_id', $refId)
                ->delete();

            $level = 1;
            foreach ($roles as $role) {
                $this->db->table('finance_transaction_approvals')->insert([
                    'transaction_ref_type' => $refType,
                    'transaction_ref_id'   => $refId,
                    'approval_level'       => $level++,
                    'required_role'        => $role,
                    'status'               => $level === 2 ? 'pending' : 'pending',
                    'created_at'           => date('Y-m-d H:i:s'),
                ]);
            }
        }

        return ['ok' => true, 'message' => 'Submitted for approval.'];
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function approve(string $refType, int $refId, int $userId, string $userRole, ?string $comments = null): array
    {
        $settings = new FinanceSettingsService($this->db);
        $table    = $this->tableFor($refType);
        $doc      = $this->db->table($table)->where('id', $refId)->get()->getRowArray();

        if (! $doc || ! in_array($doc['status'], ['pending_approval', 'submitted'], true)) {
            return ['ok' => false, 'message' => 'Document is not pending approval.'];
        }

        if ((int) ($doc['created_by'] ?? 0) === $userId && ! $settings->selfApprovalOverrideAllowed()) {
            return ['ok' => false, 'message' => 'You cannot approve your own transaction.'];
        }

        if ($this->db->tableExists('finance_transaction_approvals')) {
            $pending = $this->db->table('finance_transaction_approvals')
                ->where('transaction_ref_type', $refType)
                ->where('transaction_ref_id', $refId)
                ->where('status', 'pending')
                ->orderBy('approval_level', 'ASC')
                ->get()->getResultArray();

            if ($pending) {
                $step = $pending[0];
                $required = (string) ($step['required_role'] ?? '');
                if ($required !== '' && $userRole !== 'super_admin' && $userRole !== $required) {
                    return ['ok' => false, 'message' => 'Your role cannot approve at this level.'];
                }

                $this->db->table('finance_transaction_approvals')->where('id', $step['id'])->update([
                    'status'   => 'approved',
                    'acted_by' => $userId,
                    'acted_at' => date('Y-m-d H:i:s'),
                    'comments' => $comments,
                ]);

                $remaining = $this->db->table('finance_transaction_approvals')
                    ->where('transaction_ref_type', $refType)
                    ->where('transaction_ref_id', $refId)
                    ->where('status', 'pending')
                    ->countAllResults();

                if ($remaining > 0) {
                    return ['ok' => true, 'message' => 'Partial approval recorded. Awaiting next level.'];
                }
            }
        }

        $this->db->table($table)->where('id', $refId)->update([
            'status'      => 'approved',
            'approved_by' => $userId,
        ]);

        return ['ok' => true, 'message' => 'Document approved.'];
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function reject(string $refType, int $refId, int $userId, ?string $comments = null): array
    {
        $table = $this->tableFor($refType);
        $doc   = $this->db->table($table)->where('id', $refId)->get()->getRowArray();

        if (! $doc || ! in_array($doc['status'], ['pending_approval', 'submitted', 'approved'], true)) {
            return ['ok' => false, 'message' => 'Document cannot be rejected.'];
        }

        $this->db->table($table)->where('id', $refId)->update(['status' => 'rejected']);

        if ($this->db->tableExists('finance_transaction_approvals')) {
            $this->db->table('finance_transaction_approvals')
                ->where('transaction_ref_type', $refType)
                ->where('transaction_ref_id', $refId)
                ->where('status', 'pending')
                ->update([
                    'status'   => 'rejected',
                    'acted_by' => $userId,
                    'acted_at' => date('Y-m-d H:i:s'),
                    'comments' => $comments,
                ]);
        }

        return ['ok' => true, 'message' => 'Document rejected.'];
    }

    /** @return list<array<string,mixed>> */
    public function pendingDocuments(): array
    {
        $items = [];

        foreach ([
            ['deposit', 'finance_deposits', 'deposit_number', 'deposit_date', 'amount'],
            ['withdrawal', 'finance_withdrawals', 'withdrawal_number', 'withdrawal_date', 'amount'],
            ['transfer', 'finance_transfers', 'transfer_number', 'transfer_date', 'amount'],
        ] as [$type, $table, $numCol, $dateCol, $amtCol]) {
            if (! $this->db->tableExists($table)) {
                continue;
            }
            $rows = $this->db->table($table)
                ->where('status', 'pending_approval')
                ->orderBy($dateCol, 'DESC')
                ->limit(50)
                ->get()->getResultArray();

            foreach ($rows as $r) {
                $items[] = [
                    'ref_type'   => $type,
                    'ref_id'     => $r['id'],
                    'number'     => $r[$numCol],
                    'date'       => $r[$dateCol],
                    'amount'     => $r[$amtCol],
                    'created_by' => $r['created_by'] ?? null,
                    'status'     => $r['status'],
                ];
            }
        }

        usort($items, fn ($a, $b) => strcmp((string) $b['date'], (string) $a['date']));

        return $items;
    }

    private function tableFor(string $refType): ?string
    {
        return match ($refType) {
            'deposit'    => 'finance_deposits',
            'withdrawal' => 'finance_withdrawals',
            'transfer'   => 'finance_transfers',
            'income'     => 'finance_income_records',
            'expense'    => 'finance_expense_records',
            default      => null,
        };
    }
}
