<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * CI4-safe facility filters (PHP 8.1+ strict where-key typing).
 */
class FacilityQueryScope
{
    /**
     * @param list<int> $ids
     */
    public static function whereInOrNull(object $builder, string $facilityColumn, array $ids, bool $includeNull = true): void
    {
        $ids = array_values(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0));

        if ($ids === []) {
            if ($includeNull) {
                $builder->where($facilityColumn . ' IS NULL', null, false);
            } else {
                $builder->where('1 = 0', null, false);
            }

            return;
        }

        if (! $includeNull) {
            $builder->whereIn($facilityColumn, $ids);

            return;
        }

        $builder->groupStart()
            ->whereIn($facilityColumn, $ids)
            ->orWhere($facilityColumn . ' IS NULL', null, false)
            ->groupEnd();
    }

    /** @return list<int> */
    public static function managedFacilityIds(BaseConnection $db, int $userId): array
    {
        if ($userId < 1) {
            return [];
        }

        $rows = $db->table('facilities')->select('id')->where('manager_id', $userId);
        if ($db->fieldExists('deleted_at', 'facilities')) {
            $rows->where('deleted_at IS NULL', null, false);
        }

        $ids = array_column($rows->get()->getResultArray(), 'id');

        return array_values(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0));
    }

    /** @return list<int> */
    public static function supervisorFacilityIds(BaseConnection $db, int $userId): array
    {
        if ($userId < 1) {
            return [];
        }

        $q = $db->table('work_orders')->select('facility_id')->where('supervisor_id', $userId);
        if ($db->fieldExists('deleted_at', 'work_orders')) {
            $q->where('deleted_at IS NULL', null, false);
        }

        $ids = array_column($q->get()->getResultArray(), 'facility_id');

        return array_values(array_filter(array_map('intval', $ids), static fn (int $id): bool => $id > 0));
    }
}
