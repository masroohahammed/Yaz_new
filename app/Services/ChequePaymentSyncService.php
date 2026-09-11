<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class ChequePaymentSyncService
{
    public function __construct(private BaseConnection $db)
    {
    }

    /** @param array<string,mixed> $cheque */
    public function syncLinkedPayment(array $cheque, string $paymentStatus, ?string $notes = null, ?int $userId = null): void
    {
        $paymentId = (int) ($cheque['payment_id'] ?? 0);
        if ($paymentId < 1 || ! $this->db->tableExists('lease_payments')) {
            return;
        }

        if (! $this->db->fieldExists('status', 'lease_payments')) {
            return;
        }

        $payment = $this->db->table('lease_payments')->where('id', $paymentId)->get()->getRowArray();
        if (! $payment) {
            return;
        }

        $update = ['updated_at' => date('Y-m-d H:i:s'), 'status' => $paymentStatus];
        if ($paymentStatus === 'paid' || $paymentStatus === 'cheque_received') {
            $update['payment_date'] = date('Y-m-d');
            if ($this->db->fieldExists('amount_paid', 'lease_payments')) {
                $update['amount_paid'] = $cheque['amount'] ?? $payment['amount'];
            }
        }

        $this->db->table('lease_payments')->where('id', $paymentId)->update($update);

        (new PaymentTrackingService($this->db))->logStatusChange(
            $paymentId,
            $payment['status'] ?? null,
            $paymentStatus,
            isset($cheque['amount']) ? (float) $cheque['amount'] : null,
            $notes,
            (int) ($cheque['contract_id'] ?? $payment['contract_id'] ?? 0) ?: null,
            $userId
        );
    }

    /** @param array<string,mixed> $cheque */
    public function onChequeRegistered(array $cheque, ?int $userId = null): void
    {
        $this->syncLinkedPayment($cheque, 'cheque_received', 'Cheque registered #' . ($cheque['cheque_no'] ?? ''), $userId);
    }

    /** @param array<string,mixed> $cheque */
    public function onChequeBounced(array $cheque, ?string $reason = null, ?int $userId = null): void
    {
        $this->syncLinkedPayment($cheque, 'cheque_bounced', $reason ?? 'Cheque bounced', $userId);
    }

    /** @param array<string,mixed> $cheque */
    public function onConvertedToCash(array $cheque, ?string $notes = null, ?int $userId = null): void
    {
        $this->syncLinkedPayment($cheque, 'converted_to_cash', $notes ?? 'Cheque converted to cash', $userId);
    }

    /** @param array<string,mixed> $cheque */
    public function onChequeCleared(array $cheque, ?int $userId = null): void
    {
        $this->syncLinkedPayment($cheque, 'paid', 'Cheque cleared #' . ($cheque['cheque_no'] ?? ''), $userId);
    }
}
