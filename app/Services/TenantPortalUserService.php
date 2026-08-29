<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/** Link tenants to portal users (role: tenant) */
class TenantPortalUserService
{
  public function __construct(private BaseConnection $db)
  {
  }

  /**
   * Link existing user by email or create portal user for tenant.
   *
   * @return int|null users.id
   */
  public function linkOrCreateForTenant(int $tenantId): ?int
  {
    if (! $this->db->tableExists('tenants')) {
      return null;
    }

    $tenant = $this->db->table('tenants')->where('id', $tenantId)->get()->getRowArray();
    if (! $tenant) {
      return null;
    }

    if (! empty($tenant['user_id'])) {
      return (int) $tenant['user_id'];
    }

    $email = trim((string) ($tenant['email'] ?? ''));
    if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return null;
    }

    $existing = $this->db->table('users')->where('email', $email)->get()->getRowArray();
    if ($existing) {
      $this->db->table('tenants')->where('id', $tenantId)->update([
        'user_id'    => $existing['id'],
        'updated_at' => date('Y-m-d H:i:s'),
      ]);

      return (int) $existing['id'];
    }

    $roleId = (int) ($this->db->table('roles')->where('name', 'tenant')->get()->getRowArray()['id'] ?? 0);
    if ($roleId <= 0) {
      $roleId = (int) ($this->db->table('roles')->where('name', 'client')->get()->getRowArray()['id'] ?? 0);
    }

    if ($roleId <= 0) {
      return null;
    }

    $this->db->table('users')->insert([
      'role_id'    => $roleId,
      'company_id' => $tenant['company_id'] ?? 1,
      'name'       => $tenant['full_name'] ?? 'Tenant',
      'email'      => $email,
      'phone'      => $tenant['phone'] ?? '',
      'password'   => password_hash('password', PASSWORD_BCRYPT),
      'status'     => 'active',
      'created_at' => date('Y-m-d H:i:s'),
    ]);
    $userId = (int) $this->db->insertID();

    $this->db->table('tenants')->where('id', $tenantId)->update([
      'user_id'    => $userId,
      'updated_at' => date('Y-m-d H:i:s'),
    ]);

    return $userId;
  }
}
