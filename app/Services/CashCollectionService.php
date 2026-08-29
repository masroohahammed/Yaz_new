<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Unified field collection + finance acknowledgement flow.
 */
class CashCollectionService
{
    public function __construct(private BaseConnection $db)
    {
    }

    /** @return list<array<string, mixed>> */
    public function searchTenants(string $query, ?int $companyId = null, int $limit = 20): array
    {
        if (! $this->db->tableExists('tenants') || $query === '') {
            return [];
        }

        $b = $this->db->table('tenants t')
            ->select('t.id, t.full_name, t.phone, t.email, u.unit_number, f.name AS property_name')
            ->join('units u', 'u.id = t.current_unit_id', 'left')
            ->join('facilities f', 'f.id = u.facility_id', 'left')
            ->groupStart()
                ->like('t.full_name', $query)
                ->orLike('t.phone', $query)
                ->orLike('t.email', $query)
                ->orLike('t.qid_no', $query)
                ->orLike('u.unit_number', $query)
            ->groupEnd()
            ->orderBy('t.full_name')
            ->limit($limit);

        if ($companyId && $this->db->fieldExists('company_id', 'tenants')) {
            $b->where('t.company_id', $companyId);
        }

        return $b->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function tenantOutstandingInvoices(int $tenantId): array
    {
        if (! $this->db->tableExists('lease_payments')) {
            return [];
        }

        return $this->db->table('lease_payments lp')
            ->select('lp.*, f.name AS property_name, u.unit_number')
            ->join('facilities f', 'f.id = lp.facility_id', 'left')
            ->join('units u', 'u.id = lp.unit_id', 'left')
            ->where('lp.tenant_id', $tenantId)
            ->whereIn('lp.status', ['pending', 'partial', 'overdue'])
            ->orderBy('lp.due_date', 'ASC')
            ->get()->getResultArray();
    }

    /**
     * Record field collection — payment marked paid but pending finance acknowledgement.
     *
     * @return array{ok: bool, error?: string, payment_id?: int}
     */
    public function collectPayment(int $paymentId, array $data, int $collectorUserId, ?int $sessionId = null): array
    {
        if (! $this->db->tableExists('lease_payments')) {
            return ['ok' => false, 'error' => 'Payments module not available.'];
        }

        $payment = $this->db->table('lease_payments')->where('id', $paymentId)->get()->getRowArray();
        if (! $payment) {
            return ['ok' => false, 'error' => 'Payment not found.'];
        }

        $amount = (float) ($data['collected_amount'] ?? 0);
        $method = (string) ($data['payment_method'] ?? '');
        if ($amount <= 0 || $method === '') {
            return ['ok' => false, 'error' => 'Amount and payment method are required.'];
        }

        if ($method === 'cheque') {
            if (empty($data['cheque_no']) || empty($data['cheque_bank']) || empty($data['cheque_maturity'])) {
                return ['ok' => false, 'error' => 'Cheque number, bank, and maturity date are required.'];
            }
        }

        $original = (float) $payment['amount'];
        $newStatus = $amount >= $original ? 'paid' : 'partial';
        $collectedAt = $data['collected_at'] ?? date('Y-m-d H:i:s');

        $update = [
            'status'         => $newStatus,
            'amount'         => $amount,
            'payment_method' => $method,
            'payment_date'   => date('Y-m-d', strtotime($collectedAt)),
            'notes'          => $data['notes'] ?? $payment['notes'],
            'acknowledged'   => 0,
            'updated_at'     => date('Y-m-d H:i:s'),
        ];

        $fields = $this->db->getFieldNames('lease_payments');
        if (in_array('received_by', $fields, true)) {
            $update['received_by'] = $collectorUserId;
        }
        if (in_array('collection_session_id', $fields, true) && $sessionId) {
            $update['collection_session_id'] = $sessionId;
        }
        if (in_array('collected_at', $fields, true)) {
            $update['collected_at'] = $collectedAt;
        }
        if (in_array('cheque_no', $fields, true) && ! empty($data['cheque_no'])) {
            $update['cheque_no'] = $data['cheque_no'];
        }
        if (in_array('cheque_bank', $fields, true)) {
            $update['cheque_bank'] = $data['cheque_bank'] ?? null;
        }
        if (in_array('cheque_maturity', $fields, true)) {
            $update['cheque_maturity'] = $data['cheque_maturity'] ?? null;
        }
        if (in_array('transfer_bank', $fields, true)) {
            $update['transfer_bank'] = $data['bank_name'] ?? null;
            $update['transfer_account'] = $data['bank_account'] ?? null;
            $update['transfer_date'] = $data['transfer_date'] ?? null;
            $update['transfer_ref'] = $data['transfer_ref'] ?? null;
        }

        $this->db->table('lease_payments')->where('id', $paymentId)->update($update);

        return ['ok' => true, 'payment_id' => $paymentId];
    }

    /** @return list<array<string, mixed>> */
    public function pendingAcknowledgements(?int $collectorId = null): array
    {
        if (! $this->db->tableExists('lease_payments')) {
            return [];
        }

        if (! $this->db->fieldExists('acknowledged', 'lease_payments')) {
            return [];
        }

        $q = $this->db->table('lease_payments lp')
            ->select('lp.*, t.full_name AS tenant_name, f.name AS property_name, u.name AS collector_name')
            ->join('tenants t', 't.id = lp.tenant_id', 'left')
            ->join('facilities f', 'f.id = lp.facility_id', 'left')
            ->join('users u', 'u.id = lp.received_by', 'left')
            ->whereIn('lp.status', ['paid', 'partial'])
            ->where('lp.acknowledged', 0)
            ->orderBy('lp.payment_date', 'DESC');

        if ($collectorId) {
            $q->where('lp.received_by', $collectorId);
        }

        return $q->get()->getResultArray();
    }

    public function acknowledge(int $paymentId, int $userId, string $depositDate, ?string $depositRef = null, ?string $notes = null): bool
    {
        return $this->acknowledgeBulk([$paymentId], $userId, $depositDate, $depositRef, $notes) > 0;
    }

    public function acknowledgeBulk(array $paymentIds, int $userId, string $depositDate, ?string $depositRef = null, ?string $notes = null): int
    {
        if (! $this->db->tableExists('lease_payments') || ! $this->db->fieldExists('acknowledged', 'lease_payments')) {
            return 0;
        }

        $count = 0;
        foreach ($paymentIds as $pid) {
            $pid = (int) $pid;
            if ($pid <= 0) {
                continue;
            }

            $p = $this->db->table('lease_payments')->where('id', $pid)->get()->getRowArray();
            if (! $p || (int) ($p['acknowledged'] ?? 0) === 1) {
                continue;
            }

            $this->db->table('lease_payments')->where('id', $pid)->update([
                'acknowledged'    => 1,
                'acknowledged_at' => date('Y-m-d H:i:s'),
                'acknowledged_by' => $userId,
                'deposit_date'    => $depositDate,
                'deposit_ref'     => $depositRef,
                'notes'           => $notes ? trim($notes) : $p['notes'],
                'updated_at'      => date('Y-m-d H:i:s'),
            ]);

            $this->journalCollection($p, $depositDate, $userId, $depositRef);
            $count++;
        }

        return $count;
    }

    private function journalCollection(array $payment, string $depositDate, int $userId, ?string $depositRef): void
    {
        if (! $this->db->tableExists('finance_entries')) {
            return;
        }

        $exists = $this->db->table('finance_entries')
            ->where('ref_module', 'lease_payments')
            ->where('ref_id', $payment['id'])
            ->countAllResults();
        if ($exists > 0) {
            return;
        }

        $this->db->table('finance_entries')->insert([
            'company_id'     => $payment['company_id'] ?? null,
            'entry_type'     => 'rent_collection',
            'direction'      => 'income',
            'amount'         => $payment['amount'] ?? 0,
            'facility_id'    => $payment['facility_id'] ?? null,
            'unit_id'        => $payment['unit_id'] ?? null,
            'tenant_id'      => $payment['tenant_id'] ?? null,
            'ref_module'     => 'lease_payments',
            'ref_id'         => $payment['id'],
            'description'    => 'Field collection acknowledged',
            'payment_method' => $payment['payment_method'] ?? null,
            'reference_no'   => $depositRef,
            'paid_by'        => 'tenant',
            'entry_date'     => $depositDate,
            'created_by'     => $userId,
            'created_at'     => date('Y-m-d H:i:s'),
        ]);
    }

    /** Collector history with optional filters */
    public function collectorHistory(?int $collectorId, string $from, string $to): array
    {
        if (! $this->db->tableExists('lease_payments')) {
            return [];
        }

        $q = $this->db->table('lease_payments lp')
            ->select('lp.*, t.full_name AS tenant_name, f.name AS facility_name')
            ->join('tenants t', 't.id = lp.tenant_id', 'left')
            ->join('facilities f', 'f.id = lp.facility_id', 'left')
            ->where('lp.payment_date >=', $from)
            ->where('lp.payment_date <=', $to)
            ->whereIn('lp.status', ['paid', 'partial'])
            ->orderBy('lp.payment_date', 'DESC');

        if ($collectorId && $this->db->fieldExists('received_by', 'lease_payments')) {
            $q->where('lp.received_by', $collectorId);
        }

        return $q->get()->getResultArray();
    }

    /** Daily report aggregates by method */
    public function dailyReport(?int $collectorId, string $date): array
    {
        $payments = $this->collectorHistory($collectorId, $date, $date);
        $byMethod = [];
        foreach ($payments as $p) {
            $m = $p['payment_method'] ?? 'other';
            $byMethod[$m] = ($byMethod[$m] ?? 0) + (float) $p['amount'];
        }

        return [
            'payments' => $payments,
            'total'    => array_sum(array_column($payments, 'amount')),
            'byMethod' => $byMethod,
        ];
    }
}
