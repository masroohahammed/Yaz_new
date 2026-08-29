<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table          = 'users';
    protected $primaryKey     = 'id';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';

    protected $allowedFields = [
        'role_id', 'name', 'email', 'phone', 'password',
        'status', 'last_login', 'company_id',
    ];

    protected $hidden = ['password'];

    /**
     * Get users by role name, optionally scoped to a company.
     */
    public function getUsersByRole(string $roleName, ?int $companyId = null): array
    {
        return $this->getUsersByRoles([$roleName], $companyId)[$roleName] ?? [];
    }

    /**
     * @param  list<string>  $roleNames
     * @return array<string, list<array<string, mixed>>>
     */
    public function getUsersByRoles(array $roleNames, ?int $companyId = null): array
    {
        $roleNames = array_values(array_unique(array_filter($roleNames)));
        $out       = array_fill_keys($roleNames, []);
        if ($roleNames === []) {
            return $out;
        }

        $builder = $this->db->table('users u')
            ->select('u.id, u.name, u.email, u.phone, u.status, u.company_id, r.name AS role_name')
            ->join('roles r', 'r.id = u.role_id')
            ->whereIn('r.name', $roleNames)
            ->where('u.status', 'active')
            ->where('u.deleted_at', null)
            ->orderBy('u.name', 'ASC');

        if ($companyId) {
            $builder->where('u.company_id', $companyId);
        }

        foreach ($builder->get()->getResultArray() as $row) {
            $role = (string) ($row['role_name'] ?? '');
            if ($role !== '' && isset($out[$role])) {
                $out[$role][] = $row;
            }
        }

        return $out;
    }

    /**
     * Find user by email for auth.
     */
    public function findByEmail(string $email): ?array
    {
        return $this->db->table('users u')
            ->select('u.*, r.name AS role_name, r.display_name AS role_label')
            ->join('roles r', 'r.id = u.role_id')
            ->where('u.email', $email)
            ->where('u.deleted_at', null)
            ->get()->getRowArray() ?: null;
    }

    /**
     * Get all users with role name (for admin listing).
     */
    public function getAllWithRoles(?int $companyId = null): array
    {
        $builder = $this->db->table('users u')
            ->select('u.id, u.name, u.email, u.phone, u.status, u.created_at, u.last_login, u.company_id,
                      r.name AS role_name, r.display_name AS role_label,
                      c.name AS company_name')
            ->join('roles r', 'r.id = u.role_id')
            ->join('companies c', 'c.id = u.company_id', 'left')
            ->where('u.deleted_at', null)
            ->orderBy('u.name', 'ASC');

        if ($companyId) {
            $builder->where('u.company_id', $companyId);
        }

        return $builder->get()->getResultArray();
    }
}
