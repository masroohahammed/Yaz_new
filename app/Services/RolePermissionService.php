<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Granular CRUD from role_permissions table (Settings-configurable).
 */
class RolePermissionService
{
  public function __construct(private BaseConnection $db)
  {
  }

  public function can(int $roleId, string $module, string $action = 'view'): bool
  {
    if ($roleId <= 0 || ! $this->db->tableExists('role_permissions')) {
      return true;
    }

    $row = $this->db->table('role_permissions')
      ->where('role_id', $roleId)
      ->where('module', $module)
      ->get()->getRowArray();

    if (! $row) {
      return true;
    }

    return match ($action) {
      'create' => (bool) $row['can_create'],
      'edit'   => (bool) $row['can_edit'],
      'delete' => (bool) $row['can_delete'],
      default  => (bool) $row['can_view'],
    };
  }
}
