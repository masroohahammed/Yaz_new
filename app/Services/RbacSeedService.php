<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Seeds PM unit/lease view permissions into role_permissions and rbac_overrides.
 */
class RbacSeedService
{
    /** @var list<string> */
    private const PM_ROLE_NAMES = [
        'property_manager',
        'real_estate_manager',
        'salesman',
        'finance_manager',
        'finance_user',
        'supervisor',
        'landlord',
        'leasing_agent',
        'crm_agent',
        'accountant',
    ];

    /** @var list<string> */
    private const PM_UNIT_EDIT_ROLES = [
        'property_manager',
        'real_estate_manager',
        'leasing_agent',
    ];

    public function __construct(private BaseConnection $db)
    {
    }

    /**
     * @return array{role_permissions: int, rbac_roles: int}
     */
    public function seedPmUnitAndLeaseAccess(): array
    {
        $rpCount = $this->seedRolePermissionsMatrix();
        $rbCount = $this->patchRbacOverrides();

        cache()->delete('fm_system_settings');
        cache()->delete('system_settings');

        return ['role_permissions' => $rpCount, 'rbac_roles' => $rbCount];
    }

    private function seedRolePermissionsMatrix(): int
    {
        if (! $this->db->tableExists('role_permissions') || ! $this->db->tableExists('roles')) {
            return 0;
        }

        $count = 0;
        $roles = $this->db->table('roles')
            ->whereIn('name', self::PM_ROLE_NAMES)
            ->get()->getResultArray();

        foreach ($roles as $role) {
            $name   = (string) ($role['name'] ?? '');
            $roleId = (int) ($role['id'] ?? 0);
            if ($roleId < 1) {
                continue;
            }

            $canUnitCreate = in_array($name, self::PM_UNIT_EDIT_ROLES, true) ? 1 : 0;
            $canUnitEdit   = in_array($name, self::PM_UNIT_EDIT_ROLES, true) ? 1 : 0;

            $count += $this->upsertRolePermission($roleId, 'units', 1, $canUnitCreate, $canUnitEdit, 0);
            $count += $this->upsertRolePermission($roleId, 'leases', 1, $canUnitCreate, $canUnitEdit, 0);
            $count += $this->upsertRolePermission($roleId, 'facilities', 1, $canUnitCreate, $canUnitEdit, 0);
            $count += $this->upsertRolePermission($roleId, 'tenants', 1, $canUnitCreate, 0, 0);
        }

        // FM facility managers need unit view as well
        $fmRoles = $this->db->table('roles')->where('name', 'facility_manager')->get()->getResultArray();
        foreach ($fmRoles as $role) {
            $roleId = (int) ($role['id'] ?? 0);
            if ($roleId > 0) {
                $count += $this->upsertRolePermission($roleId, 'units', 1, 1, 1, 0);
                $count += $this->upsertRolePermission($roleId, 'leases', 1, 0, 0, 0);
            }
        }

        return $count;
    }

    private function upsertRolePermission(int $roleId, string $module, int $view, int $create, int $edit, int $delete): int
    {
        $existing = $this->db->table('role_permissions')
            ->where('role_id', $roleId)
            ->where('module', $module)
            ->get()->getRowArray();

        $data = [
            'can_view'   => max($view, (int) ($existing['can_view'] ?? 0)),
            'can_create' => max($create, (int) ($existing['can_create'] ?? 0)),
            'can_edit'   => max($edit, (int) ($existing['can_edit'] ?? 0)),
            'can_delete' => max($delete, (int) ($existing['can_delete'] ?? 0)),
        ];

        if ($existing) {
            $this->db->table('role_permissions')->where('id', (int) $existing['id'])->update($data);

            return 1;
        }

        $this->db->table('role_permissions')->insert(array_merge($data, [
            'role_id' => $roleId,
            'module'  => $module,
        ]));

        return 1;
    }

    private function patchRbacOverrides(): int
    {
        if (! $this->db->tableExists('system_settings')) {
            return 0;
        }

        $row = $this->db->table('system_settings')
            ->where('setting_key', 'rbac_overrides')
            ->get()->getRowArray();

        $overrides = [];
        if ($row && ! empty($row['setting_value'])) {
            $decoded = json_decode((string) $row['setting_value'], true);
            if (is_array($decoded)) {
                $overrides = $decoded;
            }
        }

        $rbac     = new RbacService($this->db);
        $codeMap  = $rbac->permissionsMap();
        $patched  = 0;

        $grant = [
            'units.view',
            'leases',
            'facilities',
            'tenants',
        ];

        $grantEdit = ['units.create', 'units.edit', 'facilities.create', 'facilities.edit'];

        foreach (self::PM_ROLE_NAMES as $roleName) {
            $base = $overrides[$roleName] ?? $codeMap[$roleName] ?? [];
            if (! is_array($base)) {
                $base = [];
            }

            foreach ($grant as $perm) {
                if (! in_array($perm, $base, true) && ! in_array('*', $base, true)) {
                    $base[] = $perm;
                }
            }

            if (in_array($roleName, self::PM_UNIT_EDIT_ROLES, true)) {
                foreach ($grantEdit as $perm) {
                    if (! in_array($perm, $base, true)) {
                        $base[] = $perm;
                    }
                }
            }

            $overrides[$roleName] = array_values(array_unique($base));
            $patched++;
        }

        $json = json_encode($overrides, JSON_UNESCAPED_UNICODE);
        if ($row) {
            $this->db->table('system_settings')->where('setting_key', 'rbac_overrides')->update([
                'setting_value' => $json,
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
        } else {
            $this->db->table('system_settings')->insert([
                'setting_key'   => 'rbac_overrides',
                'setting_value' => $json,
                'setting_group' => 'general',
                'updated_at'    => date('Y-m-d H:i:s'),
            ]);
        }

        return $patched;
    }
}
