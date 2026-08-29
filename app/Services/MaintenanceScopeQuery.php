<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * CI4-safe maintenance_requests scoping.
 * Uses parameterized raw SQL to avoid BaseBuilder int whereKey errors (PHP 8.1+).
 */
class MaintenanceScopeQuery
{
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

        $unitId = (int) ($scope['unit_id'] ?? 0);
        $assetId = (int) ($scope['asset_id'] ?? 0);
        $facilityId = (int) ($scope['facility_id'] ?? 0);

        $sql = 'SELECT ' . $select . '
            FROM maintenance_requests mr
            LEFT JOIN units u ON u.id = mr.unit_id
            WHERE 1=1';
        $params = [];

        if ($unitId > 0) {
            $sql .= ' AND mr.unit_id = ?';
            $params[] = $unitId;
        } elseif ($assetId > 0 && $db->fieldExists('asset_id', 'maintenance_requests')) {
            $sql .= ' AND mr.asset_id = ?';
            $params[] = $assetId;
        } elseif ($facilityId > 0) {
            if ($db->fieldExists('facility_id', 'maintenance_requests')) {
                $sql .= ' AND mr.facility_id = ?';
                $params[] = $facilityId;
            } else {
                $sql .= ' AND u.facility_id = ?';
                $params[] = $facilityId;
            }
        }

        if ($statusIn !== null && $statusIn !== []) {
            $placeholders = implode(',', array_fill(0, count($statusIn), '?'));
            $sql .= ' AND mr.status IN (' . $placeholders . ')';
            foreach ($statusIn as $status) {
                $params[] = (string) $status;
            }
        }

        $sql .= ' ORDER BY mr.created_at DESC LIMIT ' . max(1, (int) $limit);

        try {
            return $db->query($sql, $params)->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'MaintenanceScopeQuery::listRecords failed: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function unitsForFacility(BaseConnection $db, int $facilityId): array
    {
        if ($facilityId <= 0 || ! $db->tableExists('units')) {
            return [];
        }

        $sql = 'SELECT u.id, u.unit_number, u.floor, u.status
            FROM units u
            WHERE u.facility_id = ?';
        $params = [$facilityId];

        if ($db->fieldExists('deleted_at', 'units')) {
            $sql .= ' AND u.deleted_at IS NULL';
        }

        $sql .= ' ORDER BY u.unit_number ASC';

        try {
            return $db->query($sql, $params)->getResultArray();
        } catch (\Throwable $e) {
            log_message('error', 'MaintenanceScopeQuery::unitsForFacility failed: ' . $e->getMessage());

            return [];
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function resolveFacility(BaseConnection $db, int $facilityId): ?array
    {
        if ($facilityId <= 0 || ! $db->tableExists('facilities')) {
            return null;
        }

        $sql = 'SELECT * FROM facilities WHERE id = ?';
        $params = [$facilityId];

        if ($db->fieldExists('deleted_at', 'facilities')) {
            $sql .= ' AND deleted_at IS NULL';
        }

        $sql .= ' LIMIT 1';

        try {
            return $db->query($sql, $params)->getRowArray() ?: null;
        } catch (\Throwable $e) {
            log_message('error', 'MaintenanceScopeQuery::resolveFacility failed: ' . $e->getMessage());

            return null;
        }
    }
}
