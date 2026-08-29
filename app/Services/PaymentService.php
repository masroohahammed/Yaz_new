<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Invoice payment recording with partial / full settlement.
 */
class PaymentService
{
    /** Statuses that can receive customer payments (includes WO draft invoices). */
    public const COLLECTIBLE_STATUSES = ['draft', 'sent', 'overdue', 'partial'];

    public function __construct(private BaseConnection $db)
    {
    }

    public static function canAcceptPayment(string $status): bool
    {
        return in_array($status, self::COLLECTIBLE_STATUSES, true);
    }

    /**
     * @return array{paid_total: float, balance: float, status: string}
     */
    public function recordPayment(
        int $invoiceId,
        float $amount,
        string $method,
        int $recordedBy,
        ?string $referenceNo = null,
        ?string $notes = null
    ): array {
        $inv = $this->db->table('invoices')->where('id', $invoiceId)->get()->getRowArray();
        if (!$inv) {
            throw new \RuntimeException('Invoice not found.');
        }
        if (!self::canAcceptPayment((string) $inv['status'])) {
            throw new \RuntimeException('Invoice cannot accept payments in status: ' . $inv['status']);
        }
        if ($amount <= 0) {
            throw new \RuntimeException('Payment amount must be greater than zero.');
        }

        $paidSoFar = $this->getPaidTotal($invoiceId);
        $balance   = round((float) $inv['total'] - $paidSoFar, 2);
        if ($amount > $balance + 0.01) {
            throw new \RuntimeException("Payment exceeds balance due ({$balance}).");
        }

        if (!$this->db->tableExists('invoice_payments')) {
            throw new \RuntimeException('Run enterprise migration to enable payment ledger.');
        }

        $this->db->table('invoice_payments')->insert([
            'invoice_id'     => $invoiceId,
            'amount'         => round($amount, 2),
            'payment_method' => in_array($method, ['cash', 'bank', 'card', 'cheque', 'online'], true) ? $method : 'bank',
            'reference_no'   => $referenceNo,
            'notes'          => $notes,
            'paid_at'        => date('Y-m-d H:i:s'),
            'recorded_by'    => $recordedBy,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);

        $newPaid   = $paidSoFar + $amount;
        $calc      = new FinancialCalculationService();
        $payState  = $calc->invoicePaymentState((float) $inv['total'], $newPaid);
        $newStatus = $payState['status'];

        $update = ['status' => $newStatus];
        if ($this->db->fieldExists('paid_amount', 'invoices')) {
            $update['paid_amount']    = $payState['paid_amount'];
            $update['due_amount']     = $payState['due_amount'];
            $update['pending_amount'] = $payState['pending_amount'];
            $update['is_partial']     = $newStatus === 'partial' ? 1 : 0;
        }
        if ($newStatus === 'paid') {
            $update['paid_at'] = date('Y-m-d H:i:s');
            $update['paid_by'] = $recordedBy;
        }

        $this->db->table('invoices')->where('id', $invoiceId)->update($update);

        if ($this->db->fieldExists('billed_amount', 'work_orders') && ! empty($inv['work_order_id'])) {
            $this->syncWorkOrderBilling((int) $inv['work_order_id']);
        }

        try {
            (new \App\Services\Finance\GlPostingService($this->db))->postCustomerPayment($invoiceId, round($amount, 2), $recordedBy);
        } catch (\Throwable $e) {
            log_message('warning', 'GL payment post skipped: ' . $e->getMessage());
        }

        return [
            'paid_total' => round($newPaid, 2),
            'balance'    => round(max(0, (float) $inv['total'] - $newPaid), 2),
            'status'     => $newStatus,
            'paid_amount'=> $payState['paid_amount'],
            'due_amount' => $payState['due_amount'],
        ];
    }

    private function syncWorkOrderBilling(int $woId): void
    {
        $invoices = $this->db->table('invoices')
            ->select('total, paid_amount, status')
            ->where('work_order_id', $woId)
            ->where('deleted_at', null)
            ->whereNotIn('status', ['cancelled'])
            ->get()
            ->getResultArray();

        $billed = 0.0;
        $paid   = 0.0;
        foreach ($invoices as $inv) {
            $billed += (float) ($inv['total'] ?? 0);
            $paid   += (float) ($inv['paid_amount'] ?? 0);
            if (($inv['status'] ?? '') === 'paid' && empty($inv['paid_amount'])) {
                $paid += (float) ($inv['total'] ?? 0);
            }
        }

        $this->db->table('work_orders')->where('id', $woId)->update([
            'billed_amount'          => round($billed, 2),
            'pending_billing_amount' => round(max(0, $billed - $paid), 2),
            'billing_percent'        => $billed > 0 ? round(($paid / $billed) * 100, 2) : 0,
        ]);
    }

    public function getPaidTotal(int $invoiceId): float
    {
        if (!$this->db->tableExists('invoice_payments')) {
            return $this->db->table('invoices')->where('id', $invoiceId)->where('status', 'paid')->countAllResults()
                ? (float) ($this->db->table('invoices')->where('id', $invoiceId)->get()->getRowArray()['total'] ?? 0)
                : 0;
        }

        return (float) ($this->db->table('invoice_payments')
            ->selectSum('amount', 't')
            ->where('invoice_id', $invoiceId)
            ->get()
            ->getRowArray()['t'] ?? 0);
    }

    /** @return list<array<string, mixed>> */
    public function paymentHistory(int $invoiceId): array
    {
        if (!$this->db->tableExists('invoice_payments')) {
            return [];
        }

        return $this->db->table('invoice_payments p')
            ->select('p.*, u.name AS recorded_by_name')
            ->join('users u', 'u.id = p.recorded_by', 'left')
            ->where('p.invoice_id', $invoiceId)
            ->orderBy('p.paid_at', 'DESC')
            ->get()
            ->getResultArray();
    }
}
