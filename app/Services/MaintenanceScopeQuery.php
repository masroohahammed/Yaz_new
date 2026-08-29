<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * CI4-safe maintenance_requests scoping (PHP 8.1+ requires string where keys).
 */
class MaintenanceScopeQuery
{
    /**
     * @param array<string, mixed> $scope
     */
    public static function applyScope(object $builder, BaseConnection $db, array $scope, string $alias = 'mr', string $unitsAlias = 'u'): void
    {
        $unitId = (int) ($scope['unit_id'] ?? 0);
        $assetId = (int) ($scope['asset_id'] ?? 0);
        $facilityId = (int) ($scope['facility_id'] ?? 0);

        if ($unitId > 0) {
            $builder->where($alias . '.unit_id', $unitId);

            return;
        }

        if ($assetId > 0 && $db->fieldExists('asset_id', 'maintenance_requests')) {
            $builder->where($alias . '.asset_id', $assetId);

            return;
        }

        if ($facilityId <= 0) {
            return;
        }

        if ($db->fieldExists('facility_id', 'maintenance_requests')) {
            $builder->where($alias . '.facility_id', $facilityId);

            return;
        }

        // Caller must join units (see listRecords).
        $builder->where($unitsAlias . '.facility_id', $facilityId);
    }

    /**
     * @param array<string, mixed> $scope
     * @return list<array<string, mixed>>
     */
    public static function listRecords(
        BaseConnection $db,
        array $scope,
        string $select = 'mr.id, mr.ticket_number, mr.category, mr.priority, mr.status, mr.created_at, mr.requester_name, u.unit_number',
        int $limit = 25,
        ?array $statusIn = null
    ): array {
        if (! $db->tableExists('maintenance_requests')) {
            return [];
        }

        try {
            $q = $db->table('maintenance_requests mr')
                ->select($select)
                ->join('units u', 'u.id = mr.unit_id', 'left')
                ->orderBy('mr.created_at', 'DESC')
                ->limit($limit);

            if ($statusIn !== null && $statusIn !== []) {
                $q->whereIn('mr.status', $statusIn);
            }

            self::applyScope($q, $db, $scope);

            return $q->get()->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'MaintenanceScopeQuery::listRecords failed: ' . $e->getMessage());

            return [];
        }
    }
}
