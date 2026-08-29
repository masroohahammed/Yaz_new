<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Sync inline unit / legacy FM contract rows into lease_contracts (PM lease module).
 */
class UnitLeaseSyncService
{
    public function __construct(private BaseConnection $db)
    {
    }

    /**
     * @param list<int>|null $facilityIds Scope (null = no facility filter)
     * @return array{created: int, updated: int, skipped: int, errors: list<string>}
     */
    public function syncAll(?int $companyId = null, ?array $facilityIds = null, ?int $createdBy = null): array
    {
        $stats = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];

        if (! $this->db->tableExists('units') || ! $this->db->tableExists('lease_contracts')) {
            $stats['errors'][] = 'Required tables units / lease_contracts are missing.';

            return $stats;
        }

        $q = $this->db->table('units u')
            ->select('u.*, f.company_id as facility_company_id')
            ->join('facilities f', 'f.id = u.facility_id', 'left');

        if ($this->db->fieldExists('deleted_at', 'units')) {
            $q->where('u.deleted_at', null);
        }

        if ($companyId !== null && $this->db->fieldExists('company_id', 'facilities')) {
            $q->where('f.company_id', $companyId);
        }

        if ($facilityIds !== null) {
            if ($facilityIds === []) {
                return $stats;
            }
            $q->whereIn('u.facility_id', $facilityIds);
        }

        $units = $q->orderBy('u.id', 'ASC')->get()->getResultArray();

        foreach ($units as $unit) {
            try {
                $result = $this->syncUnitRow($unit, $createdBy);
                $stats[$result]++;
            } catch (\Throwable $e) {
                $stats['errors'][] = 'Unit #' . ($unit['id'] ?? '?') . ': ' . $e->getMessage();
            }
        }

        return $stats;
    }

    /**
     * @param array<string, mixed> $unit
     * @return 'created'|'updated'|'skipped'
     */
    public function syncUnitRow(array $unit, ?int $createdBy = null): string
    {
        $unitId = (int) ($unit['id'] ?? 0);
        if ($unitId < 1) {
            return 'skipped';
        }

        $legacy = $this->legacyContractForUnit($unitId);
        $start  = $unit['contract_start'] ?? $legacy['start_date'] ?? null;
        $end    = $unit['contract_end'] ?? $legacy['end_date'] ?? null;

        if (! $start || ! $end) {
            return 'skipped';
        }

        $tenantName = trim((string) ($unit['tenant_name'] ?? $legacy['client_name'] ?? ''));
        if ($tenantName === '') {
            return 'skipped';
        }

        $tenantId = $this->resolveTenantId(
            $tenantName,
            (string) ($unit['tenant_mobile'] ?? $legacy['client_mobile'] ?? ''),
            (string) ($unit['tenant_email'] ?? $legacy['client_email'] ?? ''),
            (int) ($unit['facility_company_id'] ?? $unit['company_id'] ?? 0) ?: null
        );

        if (! $tenantId) {
            throw new \RuntimeException('Could not resolve tenant for ' . $tenantName);
        }

        $isParking = strtolower(trim((string) ($unit['unit_type'] ?? ''))) === 'parking';
        $contractNo = trim((string) ($unit['contract_number'] ?? $legacy['contract_number'] ?? ''));
        if ($contractNo === '') {
            $contractNo = $this->generateLeaseNumber();
        }

        $existing = $this->findExistingLease($unitId, $contractNo);
        $status   = $this->deriveStatus($unit, $end);
        $rent     = (float) ($unit['rent_amount'] ?? $legacy['value'] ?? 0);

        $row = [
            'tenant_id'        => $tenantId,
            'facility_id'      => (int) $unit['facility_id'],
            'unit_id'          => $unitId,
            'status'           => $status,
            'signed_date'      => $start,
            'billing_start_date'=> $start,
            'start_date'       => $start,
            'end_date'         => $end,
            'rent_amount'      => $rent,
            'security_deposit' => $unit['security_deposit'] ?? null,
            'payment_frequency'=> 'monthly',
            'payment_type'     => 'cash',
            'updated_at'       => date('Y-m-d H:i:s'),
        ];

        if ($this->db->fieldExists('company_id', 'lease_contracts')) {
            $row['company_id'] = (int) ($unit['facility_company_id'] ?? $unit['company_id'] ?? 0) ?: null;
        }

        if ($this->db->fieldExists('contract_kind', 'lease_contracts')) {
            $row['contract_kind'] = $isParking ? 'parking' : 'standard';
        }

        if ($isParking && $this->db->fieldExists('plate_number', 'lease_contracts')) {
            $row['plate_number'] = trim((string) ($unit['plate_number'] ?? '')) ?: null;
        }

        if ($isParking && $this->db->fieldExists('vehicle_type', 'lease_contracts')) {
            $row['vehicle_type'] = 'Motorcycle';
        }

        if ($this->db->fieldExists('tenant_qid', 'lease_contracts') && $tenantId > 0) {
            $tenantRow = $this->db->table('tenants')->where('id', $tenantId)->get()->getRowArray();
            $qid       = trim((string) ($tenantRow['qid_no'] ?? $tenantRow['passport_no'] ?? ''));
            if ($qid !== '') {
                $row['tenant_qid'] = $qid;
            }
        }

        if ($existing) {
            if ($contractNo !== '' && ($existing['contract_number'] ?? '') === '') {
                $row['contract_number'] = $contractNo;
            }
            $this->db->table('lease_contracts')->where('id', (int) $existing['id'])->update($row);

            return 'updated';
        }

        $row['contract_number'] = $contractNo;
        $row['created_by']      = $createdBy;
        $row['created_at']      = date('Y-m-d H:i:s');
        if ($this->db->fieldExists('auto_generate_invoices', 'lease_contracts')) {
            $row['auto_generate_invoices'] = 0;
        }
        if ($this->db->fieldExists('auto_renew', 'lease_contracts')) {
            $row['auto_renew'] = 0;
        }
        if ($this->db->fieldExists('vat_applicable', 'lease_contracts')) {
            $row['vat_applicable'] = 0;
        }

        $this->db->table('lease_contracts')->insert($row);

        return 'created';
    }

    /** @return array<string, mixed>|null */
    private function legacyContractForUnit(int $unitId): ?array
    {
        if (! $this->db->tableExists('contracts')) {
            return null;
        }

        $q = $this->db->table('contracts')
            ->where('unit_id', $unitId)
            ->orderBy('id', 'DESC')
            ->limit(1);

        if ($this->db->fieldExists('deleted_at', 'contracts')) {
            $q->where('deleted_at', null);
        }

        if ($this->db->fieldExists('status', 'contracts')) {
            $q->whereIn('status', ['active', 'draft']);
        }

        return $q->get()->getRowArray() ?: null;
    }

  /** @return array<string, mixed>|null */
    private function findExistingLease(int $unitId, string $contractNo): ?array
    {
        if ($contractNo !== '') {
            $byNumber = $this->db->table('lease_contracts')
                ->where('contract_number', $contractNo)
                ->where('deleted_at', null)
                ->limit(1)
                ->get()->getRowArray();
            if ($byNumber) {
                return $byNumber;
            }
        }

        return $this->db->table('lease_contracts')
            ->where('unit_id', $unitId)
            ->where('deleted_at', null)
            ->whereIn('status', ['active', 'draft', 'renewed'])
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()->getRowArray() ?: null;
    }

    private function resolveTenantId(string $name, string $phone, string $email, ?int $companyId): ?int
    {
        if (! $this->db->tableExists('tenants')) {
            return null;
        }

        $phone = trim($phone);
        $name  = trim($name);

        if ($phone !== '') {
            $q = $this->db->table('tenants')->where('phone', $phone)->where('deleted_at', null);
            if ($companyId) {
                $q->where('company_id', $companyId);
            }
            $row = $q->limit(1)->get()->getRowArray();
            if ($row) {
                return (int) $row['id'];
            }
        }

        if ($name !== '') {
            $q = $this->db->table('tenants')->where('full_name', $name)->where('deleted_at', null);
            if ($companyId) {
                $q->where('company_id', $companyId);
            }
            $row = $q->limit(1)->get()->getRowArray();
            if ($row) {
                return (int) $row['id'];
            }
        }

        $insert = [
            'full_name'   => $name,
            'phone'       => $phone !== '' ? $phone : '00000000',
            'email'       => $email !== '' ? $email : null,
            'tenant_type' => 'Personal',
            'status'      => 'active',
            'created_at'  => date('Y-m-d H:i:s'),
        ];
        if ($companyId && $this->db->fieldExists('company_id', 'tenants')) {
            $insert['company_id'] = $companyId;
        }

        $this->db->table('tenants')->insert($insert);

        return (int) $this->db->insertID() ?: null;
    }

    /** @param array<string, mixed> $unit */
    private function deriveStatus(array $unit, string $endDate): string
    {
        $today = date('Y-m-d');
        if ($endDate < $today) {
            return 'expired';
        }
        if (($unit['status'] ?? '') === 'occupied') {
            return 'active';
        }

        return 'draft';
    }

    private function generateLeaseNumber(): string
    {
        $prefix = 'LC';
        $year   = date('Y');
        $count  = $this->db->table('lease_contracts')->countAllResults() + 1;

        return $prefix . '-' . $year . '-' . str_pad((string) $count, 5, '0', STR_PAD_LEFT);
    }
}
