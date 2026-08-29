<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class UtilityAccountService
{
    public function __construct(private BaseConnection $db)
    {
    }

    public function transferToTenantForUnit(int $unitId, int $tenantId, string $transferDate): int
    {
        if (! $this->db->tableExists('utility_accounts')) {
            return 0;
        }

        $accounts = $this->db->table('utility_accounts')
            ->where('unit_id', $unitId)
            ->where('status', 'active')
            ->where('deleted_at', null)
            ->get()->getResultArray();

        $count = 0;
        foreach ($accounts as $acc) {
            $this->transferAccountToTenant((int) $acc['id'], $tenantId, $transferDate);
            $count++;
        }

        return $count;
    }

    public function transferBackForUnit(int $unitId, string $transferDate, ?string $reason = null): int
    {
        if (! $this->db->tableExists('utility_accounts')) {
            return 0;
        }

        $accounts = $this->db->table('utility_accounts')
            ->where('unit_id', $unitId)
            ->where('status', 'active')
            ->get()->getResultArray();

        $count = 0;
        foreach ($accounts as $acc) {
            $this->transferAccountBack((int) $acc['id'], $transferDate, $reason);
            $count++;
        }

        return $count;
    }

    public function transferAccountToTenant(int $accountId, int $tenantId, string $transferDate): void
    {
        $update = [
            'billing_mode' => 'tenant_pays_direct',
            'updated_at'   => date('Y-m-d H:i:s'),
        ];
        if ($this->db->fieldExists('paid_by', 'utility_accounts')) {
            $update['paid_by'] = 'tenant';
            $update['tenant_id'] = $tenantId;
            $update['transfer_date'] = $transferDate;
        }

        $this->db->table('utility_accounts')->where('id', $accountId)->update($update);
    }

    public function transferAccountBack(int $accountId, string $transferDate, ?string $reason = null): void
    {
        $update = [
            'billing_mode' => 'included',
            'updated_at'   => date('Y-m-d H:i:s'),
        ];
        if ($this->db->fieldExists('paid_by', 'utility_accounts')) {
            $update['paid_by'] = 'company';
            $update['tenant_id'] = null;
            $update['transfer_date'] = $transferDate;
            $update['transfer_reason'] = $reason;
        }

        $this->db->table('utility_accounts')->where('id', $accountId)->update($update);
    }

    /** @return list<array<string, mixed>> */
    public function byUnit(int $unitId): array
    {
        if (! $this->db->tableExists('utility_accounts')) {
            return [];
        }

        $accounts = $this->db->table('utility_accounts ua')
            ->select('ua.*, f.name AS facility_name')
            ->join('facilities f', 'f.id = ua.facility_id', 'left')
            ->where('ua.unit_id', $unitId)
            ->where('ua.deleted_at', null)
            ->get()->getResultArray();

        if ($this->db->tableExists('utility_bills')) {
            foreach ($accounts as &$acc) {
                $acc['bills'] = $this->db->table('utility_bills')
                    ->where('account_id', $acc['id'])
                    ->orderBy('bill_date', 'DESC')
                    ->limit(10)
                    ->get()->getResultArray();
            }
            unset($acc);
        }

        return $accounts;
    }
}
