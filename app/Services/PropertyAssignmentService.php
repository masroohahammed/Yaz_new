<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Property scoping via user_property_assignments (manager / caretaker).
 */
class PropertyAssignmentService
{
  /** Roles scoped to assigned properties only */
  private const SCOPED_ROLES = ['manager', 'caretaker', 'property_manager'];

  public function __construct(private BaseConnection $db)
  {
  }

  /** @return list<int>|null null = all properties (no filter) */
  public function assignedFacilityIds(int $userId, string $role): ?array
  {
    if ($role === 'super_admin') {
      return null;
    }

    if (! in_array($role, self::SCOPED_ROLES, true)) {
      return null;
    }

    if (! $this->db->tableExists('user_property_assignments')) {
      return null;
    }

    $rows = $this->db->table('user_property_assignments')
      ->select('facility_id')
      ->where('user_id', $userId)
      ->get()->getResultArray();

    if (empty($rows)) {
      // Unassigned scoped role — no properties
      return [];
    }

    return array_map(fn ($r) => (int) $r['facility_id'], $rows);
  }

  public function canAccessFacility(int $userId, string $role, int $facilityId): bool
  {
    $ids = $this->assignedFacilityIds($userId, $role);
    if ($ids === null) {
      return true;
    }

    return in_array($facilityId, $ids, true);
  }

  /** Replace manager/caretaker assignments for a property */
  public function syncAssignments(int $facilityId, array $managerIds, array $caretakerIds, int $assignedBy): void
  {
    if (! $this->db->tableExists('user_property_assignments')) {
      return;
    }

    $this->db->table('user_property_assignments')
      ->where('facility_id', $facilityId)
      ->whereIn('role_type', ['manager', 'caretaker'])
      ->delete();

    $now = date('Y-m-d H:i:s');
    foreach ($managerIds as $i => $uid) {
      if ((int) $uid > 0) {
        $this->db->table('user_property_assignments')->insert([
          'user_id'      => (int) $uid,
          'facility_id'  => $facilityId,
          'role_type'    => 'manager',
          'is_primary'   => $i === 0 ? 1 : 0,
          'assigned_by'  => $assignedBy,
          'assigned_at'  => $now,
        ]);
      }
    }
    foreach ($caretakerIds as $uid) {
      if ((int) $uid > 0) {
        $this->db->table('user_property_assignments')->insert([
          'user_id'      => (int) $uid,
          'facility_id'  => $facilityId,
          'role_type'    => 'caretaker',
          'is_primary'   => 0,
          'assigned_by'  => $assignedBy,
          'assigned_at'  => $now,
        ]);
      }
    }

    // Backward-compat primary manager on facilities.manager_id
    if ($this->db->fieldExists('manager_id', 'facilities') && ! empty($managerIds)) {
      $this->db->table('facilities')->where('id', $facilityId)->update([
        'manager_id' => (int) $managerIds[0],
      ]);
    }
    if ($this->db->fieldExists('caretaker_id', 'facilities') && ! empty($caretakerIds)) {
      $this->db->table('facilities')->where('id', $facilityId)->update([
        'caretaker_id' => (int) $caretakerIds[0],
      ]);
    }
  }
}
