<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class PaymentTrackingService
{
    public function __construct(private BaseConnection $db)
    {
    }

    public function logStatusChange(int $paymentId, ?string $from, string $to, ?float $amount = null, ?string $notes = null, ?int $contractId = null, ?int $userId = null): void
    {
        if (! $this->db->tableExists('payment_status_history')) {
            return;
        }

        $this->db->table('payment_status_history')->insert([
            'payment_id'  => $paymentId,
            'contract_id' => $contractId,
            'from_status' => $from,
            'to_status'   => $to,
            'amount'      => $amount,
            'notes'       => $notes,
            'created_by'  => $userId,
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    /** @return list<array<string,mixed>> */
    public function historyForPayment(int $paymentId): array
    {
        if (! $this->db->tableExists('payment_status_history')) {
            return [];
        }

        return $this->db->table('payment_status_history')
            ->where('payment_id', $paymentId)
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function paidTotal(int $paymentId): float
    {
        if (! $this->db->tableExists('payment_partials')) {
            return 0.0;
        }

        $row = $this->db->table('payment_partials')
            ->selectSum('amount', 't')
            ->where('payment_id', $paymentId)
            ->get()
            ->getRowArray();

        return (float) ($row['t'] ?? 0);
    }

    /** @param array<string,mixed> $payment */
    public function balance(array $payment): float
    {
        $total = (float) ($payment['amount'] ?? 0);
        $paid  = (float) ($payment['amount_paid'] ?? 0);
        if ($paid <= 0) {
            $paid = $this->paidTotal((int) ($payment['id'] ?? 0));
        }

        return max(0, round($total - $paid, 2));
    }

    public function displayStatus(array $payment): string
    {
        $status = (string) ($payment['status'] ?? 'pending');
        if ($status === 'partial') {
            return 'partially_paid';
        }
        if ($status === 'paid' && ($payment['payment_method'] ?? '') === 'cheque') {
            return 'cheque_received';
        }

        return $status;
    }
}
