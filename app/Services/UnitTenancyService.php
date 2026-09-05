<?php

namespace App\Services;

use App\Database\AutoIncrementRepair;
use CodeIgniter\Database\BaseConnection;

/**
 * Unit number uniqueness, vacant-only tenancy rules, and legacy PK repair.
 */
class UnitTenancyService
{
    public function __construct(private BaseConnection $db)
    {
    }

    public function ensureInsertTablesReady(): void
    {
        AutoIncrementRepair::ensure($this->db, 'units');
        AutoIncrementRepair::ensure($this->db, 'contracts');
    }

    /**
     * @param array<string, mixed> $row
     */
    public function insertUnit(array $row): int
    {
        $this->ensureInsertTablesReady();
        helper('fm');

        return fm_insert_row_id($this->db, 'units', $row);
    }

    /**
     * @param array<string, mixed> $row
     */
    public function insertLegacyContract(array $row): int
    {
        $this->ensureInsertTablesReady();
        helper('fm');

        return fm_insert_row_id($this->db, 'contracts', $row);
    }

    public function unitNumberTaken(int $facilityId, string $unitNumber, int $excludeUnitId = 0): bool
    {
        if ($facilityId < 1 || trim($unitNumber) === '' || ! $this->db->tableExists('units')) {
            return false;
        }

        $q = $this->db->table('units')
            ->where('facility_id', $facilityId)
            ->where('unit_number', trim($unitNumber));
        if ($this->db->fieldExists('deleted_at', 'units')) {
            $q->where('deleted_at', null);
        }
        if ($excludeUnitId > 0) {
            $q->where('id !=', $excludeUnitId);
        }

        return $q->countAllResults() > 0;
    }

    /** @param array<string, mixed>|null $unit */
    public function unitIsVacant(?array $unit): bool
    {
        if (! $unit) {
            return false;
        }

        return strtolower(trim((string) ($unit['status'] ?? ''))) === 'vacant';
    }

    /**
     * @param array<string, mixed> $input
     */
    public function requestAssignsTenancy(array $input): bool
    {
        if (trim((string) ($input['tenant_name'] ?? '')) !== '') {
            return true;
        }
        if (trim((string) ($input['contract_start'] ?? '')) !== '') {
            return true;
        }
        if (trim((string) ($input['contract_end'] ?? '')) !== '') {
            return true;
        }
        if (trim((string) ($input['tenant_id'] ?? '')) !== '' && (int) ($input['tenant_id'] ?? 0) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Allow editing an existing tenancy on an occupied unit; block new tenant/contract on non-vacant units.
     *
     * @param array<string, mixed>      $unit
     * @param array<string, mixed>      $input
     * @param array<string, mixed>|null $existingContract optional active lease row for this unit
     */
    public function canAssignTenancy(array $unit, array $input, ?array $existingContract = null): bool
    {
        if (! $this->requestAssignsTenancy($input)) {
            return true;
        }

        if ($this->unitIsVacant($unit)) {
            return true;
        }

        $tenantName = trim((string) ($input['tenant_name'] ?? ''));
        $unitTenant = trim((string) ($unit['tenant_name'] ?? ''));
        if ($tenantName !== '' && $unitTenant !== '' && strcasecmp($tenantName, $unitTenant) === 0) {
            return true;
        }

        if ($existingContract && (int) ($input['tenant_id'] ?? 0) === (int) ($existingContract['tenant_id'] ?? 0)) {
            return true;
        }

        return false;
    }

    public function vacantOnlyMessage(): string
    {
        return 'Only vacant units can receive a new tenant or contract. Choose a vacant unit or set the unit status to Vacant first.';
    }

    /** @return array<string, mixed>|null */
    public function unitRow(int $unitId): ?array
    {
        if ($unitId < 1 || ! $this->db->tableExists('units')) {
            return null;
        }

        $q = $this->db->table('units')->where('id', $unitId);
        if ($this->db->fieldExists('deleted_at', 'units')) {
            $q->where('deleted_at', null);
        }

        return $q->get()->getRowArray() ?: null;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function vacantUnitsForFacility(int $facilityId, int $includeUnitId = 0): array
    {
        if ($facilityId < 1 || ! $this->db->tableExists('units')) {
            return [];
        }

        $q = $this->db->table('units')
            ->select('id, unit_number, unit_type, plate_number, status')
            ->where('facility_id', $facilityId)
            ->groupStart()
                ->where('status', 'vacant');
        if ($includeUnitId > 0) {
            $q->orWhere('id', $includeUnitId);
        }
        $q->groupEnd()
            ->orderBy('unit_number');
        if ($this->db->fieldExists('deleted_at', 'units')) {
            $q->where('deleted_at', null);
        }

        return $q->get()->getResultArray();
    }

    public function markUnitOccupied(int $unitId, array $tenantFields = []): void
    {
        if ($unitId < 1 || ! $this->db->tableExists('units')) {
            return;
        }

        $patch = array_merge($tenantFields, [
            'status'     => 'occupied',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $this->db->table('units')->where('id', $unitId)->update($patch);
    }
}
