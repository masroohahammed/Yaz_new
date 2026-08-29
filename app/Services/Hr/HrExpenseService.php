<?php

namespace App\Services\Hr;

use App\Services\Finance\GlPostingService;
use CodeIgniter\Database\BaseConnection;

class HrExpenseService
{
    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db ??= \Config\Database::connect();
    }

    /** @return list<array<string, mixed>> */
    public function list(array $filters = []): array
    {
        if (! $this->db->tableExists('hr_expense_claims')) {
            return [];
        }
        $q = $this->db->table('hr_expense_claims ec')
            ->select('ec.*, u.name AS employee_name')
            ->join('users u', 'u.id = ec.user_id', 'left')
            ->orderBy('ec.created_at', 'DESC');
        if (! empty($filters['status'])) {
            $q->where('ec.status', $filters['status']);
        }

        return $q->limit(300)->get()->getResultArray();
    }

    public function store(int $userId, array $data, ?string $receiptPath, ?int $profileId): int
    {
        $this->db->table('hr_expense_claims')->insert([
            'user_id'      => $userId,
            'profile_id'   => $profileId,
            'title'        => esc($data['title']),
            'category'     => esc($data['category'] ?? 'general'),
            'amount'       => (float) $data['amount'],
            'expense_date' => $data['expense_date'],
            'description'  => esc($data['description'] ?? '') ?: null,
            'receipt_path' => $receiptPath,
            'status'       => 'pending',
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insertID();
    }

    public function approve(int $id, int $approverId): bool
    {
        $row = $this->db->table('hr_expense_claims')->where('id', $id)->get()->getRowArray();
        if (! $row || $row['status'] !== 'pending') {
            return false;
        }

        $jid = (new GlPostingService($this->db))->postHrExpenseApproved($id, (float) $row['amount'], $approverId);

        $this->db->table('hr_expense_claims')->where('id', $id)->update([
            'status'       => 'approved',
            'approved_by'  => $approverId,
            'approved_at'  => date('Y-m-d H:i:s'),
            'journal_id'   => $jid,
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);

        return true;
    }

    public function reject(int $id, int $approverId): bool
    {
        $this->db->table('hr_expense_claims')->where('id', $id)->where('status', 'pending')->update([
            'status'      => 'rejected',
            'approved_by' => $approverId,
            'approved_at' => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        return true;
    }
}
