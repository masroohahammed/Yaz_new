<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class PmFinanceService
{
    public function __construct(private BaseConnection $db)
    {
    }

    /** @return list<array<string, mixed>> */
    public function ledgerEntries(string $from, string $to, ?int $facilityId = null, ?int $unitId = null): array
    {
        if (! $this->db->tableExists('finance_entries')) {
            return [];
        }

        $q = $this->db->table('finance_entries fe')
            ->select('fe.*, f.name AS facility_name, u.unit_number, ct.name AS cost_type_name')
            ->join('facilities f', 'f.id = fe.facility_id', 'left')
            ->join('units u', 'u.id = fe.unit_id', 'left')
            ->join('pm_cost_types ct', 'ct.id = fe.cost_type_id', 'left')
            ->where('fe.entry_date >=', $from)
            ->where('fe.entry_date <=', $to)
            ->orderBy('fe.entry_date', 'DESC');

        if ($facilityId) {
            $q->where('fe.facility_id', $facilityId);
        }
        if ($unitId) {
            $q->where('fe.unit_id', $unitId);
        }

        return $q->get()->getResultArray();
    }

    public function propertyPnL(int $facilityId, string $from, string $to): array
    {
        $income  = 0.0;
        $expense = 0.0;
        $rows    = $this->ledgerEntries($from, $to, $facilityId);

        foreach ($rows as $r) {
            if ($r['direction'] === 'income') {
                $income += (float) $r['amount'];
            } else {
                $expense += (float) $r['amount'];
            }
        }

        return ['income' => $income, 'expense' => $expense, 'net' => $income - $expense, 'entries' => $rows];
    }

    public function unitPnL(int $unitId, string $from, string $to): array
    {
        $unit = $this->db->table('units')->where('id', $unitId)->get()->getRowArray();
        $facilityId = (int) ($unit['facility_id'] ?? 0);
        $income  = 0.0;
        $expense = 0.0;
        $rows    = $this->ledgerEntries($from, $to, $facilityId ?: null, $unitId);

        foreach ($rows as $r) {
            if ($r['direction'] === 'income') {
                $income += (float) $r['amount'];
            } else {
                $expense += (float) $r['amount'];
            }
        }

        return ['income' => $income, 'expense' => $expense, 'net' => $income - $expense, 'entries' => $rows, 'unit' => $unit];
    }

    public function trialBalance(string $asOf): array
    {
        if (! $this->db->tableExists('finance_entries') || ! $this->db->tableExists('pm_cost_types')) {
            return [];
        }

        $rows = $this->db->query("
            SELECT ct.id, ct.code, ct.name, ct.category,
                   SUM(CASE WHEN fe.direction='income' THEN fe.amount ELSE 0 END) AS income,
                   SUM(CASE WHEN fe.direction='expense' THEN fe.amount ELSE 0 END) AS expense
            FROM pm_cost_types ct
            LEFT JOIN finance_entries fe ON fe.cost_type_id = ct.id AND fe.entry_date <= ?
            GROUP BY ct.id, ct.code, ct.name, ct.category
            ORDER BY ct.code
        ", [$asOf])->getResultArray();

        return $rows;
    }

    public function collectionReport(string $from, string $to, ?int $facilityId = null): array
    {
        if (! $this->db->tableExists('lease_payments')) {
            return ['expected' => 0, 'collected' => 0, 'rows' => []];
        }

        $expectedQ = $this->db->table('lease_payments')
            ->selectSum('amount', 'total')
            ->where('due_date >=', $from)
            ->where('due_date <=', $to);
        if ($facilityId) {
            $expectedQ->where('facility_id', $facilityId);
        }
        $expected = (float) ($expectedQ->get()->getRowArray()['total'] ?? 0);

        $collectedQ = $this->db->table('lease_payments')
            ->select('id, payment_number, tenant_id, facility_id, amount, due_date, payment_date, status, payment_method')
            ->where('payment_date >=', $from)
            ->where('payment_date <=', $to)
            ->whereIn('status', ['paid', 'partial']);
        if ($facilityId) {
            $collectedQ->where('facility_id', $facilityId);
        }
        $rows = $collectedQ->get()->getResultArray();
        $collected = array_sum(array_column($rows, 'amount'));

        return ['expected' => $expected, 'collected' => $collected, 'rows' => $rows];
    }

    public function ownerStatement(int $landlordId, string $from, string $to): array
    {
        $landlord = $this->db->table('landlords')->where('id', $landlordId)->get()->getRowArray();
        $properties = $this->db->table('facilities')->where('landlord_id', $landlordId)->get()->getResultArray();
        $propertyIds = array_column($properties, 'id');

        $payouts = [];
        if ($this->db->tableExists('landlord_payouts')) {
            $payouts = $this->db->table('landlord_payouts')
                ->where('landlord_id', $landlordId)
                ->where('period_from >=', $from)
                ->where('period_to <=', $to)
                ->orderBy('period_to', 'DESC')
                ->get()->getResultArray();
        }

        $income = 0.0;
        if ($propertyIds && $this->db->tableExists('finance_entries')) {
            $income = (float) ($this->db->table('finance_entries')
                ->selectSum('amount', 't')
                ->whereIn('facility_id', $propertyIds)
                ->where('direction', 'income')
                ->where('entry_date >=', $from)
                ->where('entry_date <=', $to)
                ->get()->getRowArray()['t'] ?? 0);
        }

        return [
            'landlord'   => $landlord,
            'properties' => $properties,
            'payouts'    => $payouts,
            'grossIncome'=> $income,
            'totalPayouts'=> array_sum(array_column($payouts, 'net_amount')),
        ];
    }

    public function vatReport(string $from, string $to): array
    {
        if (! $this->db->tableExists('lease_payments')) {
            return ['collected' => 0, 'vat' => 0];
        }

        $total = (float) ($this->db->table('lease_payments')
            ->selectSum('amount', 't')
            ->whereIn('status', ['paid', 'partial'])
            ->where('payment_date >=', $from)
            ->where('payment_date <=', $to)
            ->get()->getRowArray()['t'] ?? 0);

        $vatRate = 5.0;

        return ['gross' => $total, 'vat_rate' => $vatRate, 'vat' => round($total * $vatRate / 105, 2)];
    }

    public function agingReport(?int $facilityId = null): array
    {
        if (! $this->db->tableExists('lease_payments')) {
            return [];
        }

        $q = $this->db->table('lease_payments')
            ->select('id, tenant_id, facility_id, amount, due_date, status')
            ->whereIn('status', ['pending', 'partial', 'overdue']);
        if ($facilityId) {
            $q->where('facility_id', $facilityId);
        }
        $rows = $q->get()->getResultArray();
        $buckets = ['0-30' => 0, '31-60' => 0, '61-90' => 0, '90+' => 0];
        $today = date('Y-m-d');

        foreach ($rows as $r) {
            $days = (int) ((strtotime($today) - strtotime($r['due_date'])) / 86400);
            $amt  = (float) $r['amount'];
            if ($days <= 30) {
                $buckets['0-30'] += $amt;
            } elseif ($days <= 60) {
                $buckets['31-60'] += $amt;
            } elseif ($days <= 90) {
                $buckets['61-90'] += $amt;
            } else {
                $buckets['90+'] += $amt;
            }
        }

        return $buckets;
    }

    public function insertEntry(array $data, int $userId): int
    {
        $this->db->table('finance_entries')->insert(array_merge($data, [
            'created_by' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
        ]));

        return (int) $this->db->insertID();
    }

    /** @return list<array<string, mixed>> */
    public function costTypes(): array
    {
        if (! $this->db->tableExists('pm_cost_types')) {
            return [];
        }

        return $this->db->table('pm_cost_types')->where('is_active', 1)->orderBy('code')->get()->getResultArray();
    }
}
