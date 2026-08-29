<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class InvoiceEditLogService
{
    public function __construct(private BaseConnection $db) {}

    /**
     * @param array<string, mixed> $before
     * @param array<string, mixed> $after
     */
    public function logUpdate(int $invoiceId, int $userId, array $before, array $after): void
    {
        if (! $this->db->tableExists('invoice_edit_logs')) {
            return;
        }

        $track = ['facility_id', 'contract_id', 'invoice_type', 'issue_date', 'due_date', 'subtotal', 'vat_rate', 'vat_amount', 'total', 'notes', 'work_order_id'];
        $changes = [];
        foreach ($track as $field) {
            $old = $before[$field] ?? null;
            $new = $after[$field] ?? null;
            if ((string) $old !== (string) $new) {
                $changes[$field] = ['from' => $old, 'to' => $new];
            }
        }

        if ($changes === []) {
            return;
        }

        $parts = [];
        foreach ($changes as $field => $pair) {
            $label = ucwords(str_replace('_', ' ', $field));
            $parts[] = $label . ': ' . ($pair['from'] ?? '—') . ' → ' . ($pair['to'] ?? '—');
        }

        $this->db->table('invoice_edit_logs')->insert([
            'invoice_id'   => $invoiceId,
            'user_id'      => $userId,
            'action'       => 'update',
            'summary'      => implode('; ', $parts),
            'changes_json' => json_encode($changes),
            'created_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    /** @return list<array> */
    public function historyForInvoice(int $invoiceId): array
    {
        if (! $this->db->tableExists('invoice_edit_logs')) {
            return [];
        }

        return $this->db->table('invoice_edit_logs l')
            ->select('l.*, u.name AS user_name')
            ->join('users u', 'u.id = l.user_id', 'left')
            ->where('l.invoice_id', $invoiceId)
            ->orderBy('l.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /** @return array{edit_count:int,last_edited_at:?string,last_edited_by:?string} */
    public function summaryForInvoice(int $invoiceId): array
    {
        if (! $this->db->tableExists('invoice_edit_logs')) {
            return ['edit_count' => 0, 'last_edited_at' => null, 'last_edited_by' => null];
        }

        $editCount = (int) $this->db->table('invoice_edit_logs')
            ->where('invoice_id', $invoiceId)
            ->countAllResults();

        $last = $this->db->table('invoice_edit_logs l')
            ->select('l.created_at, u.name AS user_name')
            ->join('users u', 'u.id = l.user_id', 'left')
            ->where('l.invoice_id', $invoiceId)
            ->orderBy('l.created_at', 'DESC')
            ->limit(1)
            ->get()
            ->getRowArray();

        return [
            'edit_count'     => $editCount,
            'last_edited_at' => $last['created_at'] ?? null,
            'last_edited_by' => $last['user_name'] ?? null,
        ];
    }
}
