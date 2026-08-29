<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * CI4-safe maintenance_requests queries (parameterized raw SQL only).
 */
class MaintenanceScopeQuery
{
    public const BUILD = '2026-08-29-5';

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
        } elseif ($assetId > 0 && self::hasColumn($db, 'maintenance_requests', 'asset_id')) {
            $sql .= ' AND mr.asset_id = ?';
            $params[] = $assetId;
        } elseif ($facilityId > 0) {
            $sql .= ' AND (mr.facility_id = ? OR u.facility_id = ?)';
            $params[] = $facilityId;
            $params[] = $facilityId;
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
            log_message('error', 'MaintenanceScopeQuery::listRecords: ' . $e->getMessage());

            return [];
        }
    }

    /** @return list<array<string, mixed>> */
    public static function unitsForFacility(BaseConnection $db, int $facilityId): array
    {
        if ($facilityId <= 0) {
            return [];
        }

        $sql = 'SELECT u.id, u.unit_number, u.floor, u.status
            FROM units u
            WHERE u.facility_id = ?
              AND u.deleted_at IS NULL
            ORDER BY u.unit_number ASC';

        try {
            return $db->query($sql, [$facilityId])->getResultArray();
        } catch (\Throwable $e) {
            try {
                return $db->query(
                    'SELECT u.id, u.unit_number, u.floor, u.status FROM units u WHERE u.facility_id = ? ORDER BY u.unit_number ASC',
                    [$facilityId]
                )->getResultArray();
            } catch (\Throwable $e2) {
                log_message('error', 'MaintenanceScopeQuery::unitsForFacility: ' . $e2->getMessage());

                return [];
            }
        }
    }

    /** @return array<string, mixed>|null */
    public static function resolveFacility(BaseConnection $db, int $facilityId): ?array
    {
        if ($facilityId <= 0) {
            return null;
        }

        try {
            $row = $db->query(
                'SELECT * FROM facilities WHERE id = ? AND deleted_at IS NULL LIMIT 1',
                [$facilityId]
            )->getRowArray();
            if ($row) {
                return $row;
            }
        } catch (\Throwable $e) {
            // deleted_at column may not exist
        }

        try {
            return $db->query('SELECT * FROM facilities WHERE id = ? LIMIT 1', [$facilityId])->getRowArray() ?: null;
        } catch (\Throwable $e) {
            log_message('error', 'MaintenanceScopeQuery::resolveFacility: ' . $e->getMessage());

            return null;
        }
    }

    /** @return array<string, mixed>|null */
    public static function resolveUnit(BaseConnection $db, int $unitId): ?array
    {
        if ($unitId <= 0) {
            return null;
        }

        $sql = 'SELECT u.*, f.name AS facility_name
            FROM units u
            LEFT JOIN facilities f ON f.id = u.facility_id
            WHERE u.id = ?
            LIMIT 1';

        try {
            return $db->query($sql, [$unitId])->getRowArray() ?: null;
        } catch (\Throwable $e) {
            log_message('error', 'MaintenanceScopeQuery::resolveUnit: ' . $e->getMessage());

            return null;
        }
    }

    /** @return array<string, mixed>|null */
    public static function resolveAsset(BaseConnection $db, int $assetId): ?array
    {
        if ($assetId <= 0) {
            return null;
        }

        $sql = 'SELECT a.*, f.name AS facility_name
            FROM assets a
            LEFT JOIN facilities f ON f.id = a.facility_id
            WHERE a.id = ?
            LIMIT 1';

        try {
            return $db->query($sql, [$assetId])->getRowArray() ?: null;
        } catch (\Throwable $e) {
            log_message('error', 'MaintenanceScopeQuery::resolveAsset: ' . $e->getMessage());

            return null;
        }
    }

    /** @return array<string, mixed>|null */
    public static function unitFacilityId(BaseConnection $db, int $unitId): ?int
    {
        try {
            $row = $db->query('SELECT facility_id FROM units WHERE id = ? LIMIT 1', [$unitId])->getRowArray();

            return isset($row['facility_id']) ? (int) $row['facility_id'] : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Insert a maintenance request using parameterized raw SQL (no query builder).
     *
     * @param array<string, mixed> $data
     */
    public static function insertRequest(BaseConnection $db, array $data): void
    {
        $columns = [
            'ticket_number', 'facility_id', 'unit_id', 'requester_name', 'requester_email',
            'requester_phone', 'category', 'description', 'priority', 'status', 'approval_status',
        ];
        $values = [
            $data['ticket_number'],
            $data['facility_id'],
            $data['unit_id'],
            $data['requester_name'],
            $data['requester_email'],
            $data['requester_phone'],
            $data['category'],
            $data['description'],
            $data['priority'],
            $data['status'],
            $data['approval_status'],
        ];

        if (! empty($data['asset_id']) && self::hasColumn($db, 'maintenance_requests', 'asset_id')) {
            $columns[] = 'asset_id';
            $values[] = (int) $data['asset_id'];
        }
        if (! empty($data['scan_source']) && self::hasColumn($db, 'maintenance_requests', 'scan_source')) {
            $columns[] = 'scan_source';
            $values[] = (string) $data['scan_source'];
        }

        $placeholders = implode(', ', array_fill(0, count($columns), '?'));
        $sql = 'INSERT INTO maintenance_requests (' . implode(', ', $columns) . ') VALUES (' . $placeholders . ')';

        $db->query($sql, $values);
    }

    private static function hasColumn(BaseConnection $db, string $table, string $column): bool
    {
        static $cache = [];
        $key = $table . '.' . $column;
        if (array_key_exists($key, $cache)) {
            return $cache[$key];
        }

        try {
            $cache[$key] = $db->fieldExists($column, $table);
        } catch (\Throwable $e) {
            $cache[$key] = false;
        }

        return $cache[$key];
    }
}
