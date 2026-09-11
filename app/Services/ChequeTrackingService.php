<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class ChequeTrackingService
{
    public function __construct(private BaseConnection $db)
    {
    }

    public function logStatusChange(int $chequeId, ?string $from, string $to, ?string $notes = null, ?int $userId = null): void
    {
        if (! $this->db->tableExists('cheque_status_history')) {
            return;
        }

        $this->db->table('cheque_status_history')->insert([
            'cheque_id'   => $chequeId,
            'from_status' => $from,
            'to_status'   => $to,
            'notes'       => $notes,
            'created_by'  => $userId,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    /** @return list<array<string,mixed>> */
    public function historyForCheque(int $chequeId): array
    {
        if (! $this->db->tableExists('cheque_status_history')) {
            return [];
        }

        return $this->db->table('cheque_status_history')
            ->where('cheque_id', $chequeId)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /** @param array<string,mixed> $data */
    public function createCheque(array $data, ?int $userId = null): int
    {
        $data['created_at'] = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->db->table('cheques')->insert($data);
        $id = (int) $this->db->insertID();
        $this->logStatusChange($id, null, (string) ($data['status'] ?? 'pending'), 'Cheque registered', $userId);

        return $id;
    }

    public function updateStatus(int $chequeId, string $newStatus, ?string $notes = null, ?int $userId = null): void
    {
        $cheque = $this->db->table('cheques')->where('id', $chequeId)->get()->getRowArray();
        if (! $cheque) {
            return;
        }

        $this->db->table('cheques')->where('id', $chequeId)->update([
            'status'     => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $this->logStatusChange($chequeId, $cheque['status'] ?? null, $newStatus, $notes, $userId);
    }
}
