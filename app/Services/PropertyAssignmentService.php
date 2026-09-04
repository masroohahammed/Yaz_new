<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Property scoping via user_property_assignments (multi manager / REM / landlord).
 */
class PropertyAssignmentService
{
    /** @var list<string> */
    private const STAFF_ROLE_TYPES = [
        'property_manager',
        'real_estate_manager',
        'landlord',
        'manager',
        'caretaker',
    ];

    public function __construct(private BaseConnection $db)
    {
    }

    /** @return list<int>|null null = all properties (no filter) */
    public function assignedFacilityIds(int $userId, string $role): ?array
    {
        if ($role === 'super_admin' || UserFacilityService::hasCompanyWideAccess($role)) {
            return null;
        }

        return UserFacilityService::assignedFacilityIds($this->db, $userId, $role);
    }

    public function canAccessFacility(int $userId, string $role, int $facilityId): bool
    {
        $ids = $this->assignedFacilityIds($userId, $role);
        if ($ids === null) {
            return true;
        }

        return in_array($facilityId, $ids, true);
    }

    /**
     * Replace staff assignments for a property (supports multiple PM / REM / landlord users).
     *
     * @param array<string, list<int>> $staffByRole e.g. ['property_manager' => [1,2], 'real_estate_manager' => [3]]
     */
    public function syncPropertyStaff(int $facilityId, array $staffByRole, int $assignedBy): void
    {
        if (! $this->db->tableExists('user_property_assignments')) {
            return;
        }

        UserFacilityService::ensureTableAutoIncrement($this->db, 'user_property_assignments');

        $this->db->table('user_property_assignments')
            ->where('facility_id', $facilityId)
            ->whereIn('role_type', self::STAFF_ROLE_TYPES)
            ->delete();

        $now = date('Y-m-d H:i:s');
        foreach ($staffByRole as $roleType => $userIds) {
            if (! in_array($roleType, self::STAFF_ROLE_TYPES, true)) {
                continue;
            }
            $i = 0;
            foreach (array_values(array_unique(array_filter(array_map('intval', $userIds)))) as $uid) {
                if ($uid < 1) {
                    continue;
                }
                $this->db->table('user_property_assignments')->insert([
                    'user_id'     => $uid,
                    'facility_id' => $facilityId,
                    'role_type'   => $roleType,
                    'is_primary'  => $i === 0 ? 1 : 0,
                    'assigned_by' => $assignedBy > 0 ? $assignedBy : null,
                    'assigned_at' => $now,
                ]);
                $i++;
            }
        }

        $primaryPm = (int) ($staffByRole['property_manager'][0] ?? $staffByRole['manager'][0] ?? 0);
        if ($this->db->fieldExists('manager_id', 'facilities') && $primaryPm > 0) {
            $this->db->table('facilities')->where('id', $facilityId)->update(['manager_id' => $primaryPm]);
        }

        $primaryCaretaker = (int) ($staffByRole['caretaker'][0] ?? 0);
        if ($this->db->fieldExists('caretaker_id', 'facilities') && $primaryCaretaker > 0) {
            $this->db->table('facilities')->where('id', $facilityId)->update(['caretaker_id' => $primaryCaretaker]);
        }
    }

    /** @return array<string, list<int>> */
    public function staffIdsForFacility(int $facilityId): array
    {
        $out = array_fill_keys(self::STAFF_ROLE_TYPES, []);
        if (! $this->db->tableExists('user_property_assignments')) {
            return $out;
        }

        $rows = $this->db->table('user_property_assignments')
            ->select('user_id, role_type')
            ->where('facility_id', $facilityId)
            ->whereIn('role_type', self::STAFF_ROLE_TYPES)
            ->orderBy('is_primary', 'DESC')
            ->orderBy('id', 'ASC')
            ->get()->getResultArray();

        foreach ($rows as $row) {
            $type = (string) ($row['role_type'] ?? 'manager');
            if (! isset($out[$type])) {
                $out[$type] = [];
            }
            $out[$type][] = (int) $row['user_id'];
        }

        foreach ($out as $type => $ids) {
            $out[$type] = array_values(array_unique(array_filter($ids)));
        }

        return $out;
    }

    /** Backward-compatible wrapper */
    public function syncAssignments(int $facilityId, array $managerIds, array $caretakerIds, int $assignedBy): void
    {
        $this->syncPropertyStaff($facilityId, [
            'manager'   => $managerIds,
            'caretaker' => $caretakerIds,
        ], $assignedBy);
    }
}
