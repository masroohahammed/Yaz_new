<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Resolve effective contract expiry per unit (units row + lease_contracts).
 */
class UnitExpiryService
{
    public function __construct(private BaseConnection $db)
    {
    }

    /**
     * @param  list<array<string, mixed>> $units
     * @return list<array<string, mixed>>
     */
    public function enrichUnits(array $units): array
    {
        if ($units === []) {
            return [];
        }

        $leaseByUnit = $this->leaseDatesByUnitId(array_map(static fn ($u) => (int) ($u['id'] ?? 0), $units));
        $renewSvc    = new ContractRenewalService();

        foreach ($units as &$unit) {
            $unitId = (int) ($unit['id'] ?? 0);
            $lease  = $leaseByUnit[$unitId] ?? null;

            $start = trim((string) ($unit['contract_start'] ?? ''));
            $end   = trim((string) ($unit['contract_end'] ?? ''));
            if ($start === '' && $lease) {
                $start = trim((string) ($lease['start_date'] ?? ''));
            }
            if ($end === '' && $lease) {
                $end = trim((string) ($lease['end_date'] ?? ''));
            }

            $unit['effective_contract_start'] = $start !== '' ? $start : null;
            $unit['effective_contract_end']   = $end !== '' ? $end : null;
            $unit['lease_contract_number']    = $lease['contract_number'] ?? null;
            $unit['lease_status']             = $lease['status'] ?? null;
            $unit['expiry_days']              = $renewSvc->daysUntilExpiry($end !== '' ? $end : null);
        }
        unset($unit);

        return $units;
    }

    /**
     * Units/contracts nearing expiry or already expired (for dashboards).
     *
     * @param  list<int> $facilityIds
     * @return list<array<string, mixed>>
     */
    public function expiryAlerts(array $facilityIds, int $withinDays = 60, bool $includeExpired = true): array
    {
        if ($facilityIds === [] || ! $this->db->tableExists('units')) {
            return [];
        }

        $q = $this->db->table('units u')
            ->select('u.id, u.unit_number, u.unit_type, u.status, u.tenant_name, u.contract_end, u.facility_id, f.name AS facility_name')
            ->join('facilities f', 'f.id = u.facility_id', 'left')
            ->whereIn('u.facility_id', $facilityIds)
            ->where('u.status', 'occupied');
        if ($this->db->fieldExists('deleted_at', 'units')) {
            $q->where('u.deleted_at', null);
        }

        $rows = $this->enrichUnits($q->orderBy('u.unit_number')->get()->getResultArray());
        $cutoffFuture = date('Y-m-d', strtotime('+' . $withinDays . ' days'));
        $today        = date('Y-m-d');

        return array_values(array_filter($rows, static function (array $row) use ($cutoffFuture, $today, $includeExpired): bool {
            $end = trim((string) ($row['effective_contract_end'] ?? ''));
            if ($end === '') {
                return false;
            }

            if ($includeExpired && $end < $today) {
                return true;
            }

            return $end >= $today && $end <= $cutoffFuture;
        }));
    }

    /**
     * @param  list<int> $unitIds
     * @return array<int, array<string, mixed>>
     */
    private function leaseDatesByUnitId(array $unitIds): array
    {
        $unitIds = array_values(array_filter(array_unique(array_map('intval', $unitIds)), static fn ($id) => $id > 0));
        if ($unitIds === [] || ! $this->db->tableExists('lease_contracts')) {
            return [];
        }

        $q = $this->db->table('lease_contracts')
            ->select('id, unit_id, start_date, end_date, status, contract_number, tenant_id')
            ->whereIn('unit_id', $unitIds)
            ->whereIn('status', ['active', 'draft', 'expired']);
        if ($this->db->fieldExists('deleted_at', 'lease_contracts')) {
            $q->where('deleted_at', null);
        }

        $rows = $q->orderBy('FIELD(status, \'active\', \'draft\', \'expired\')', '', false)
            ->orderBy('end_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->get()->getResultArray();

        $map = [];
        foreach ($rows as $row) {
            $uid = (int) ($row['unit_id'] ?? 0);
            if ($uid > 0 && ! isset($map[$uid])) {
                $map[$uid] = $row;
            }
        }

        return $map;
    }
}
