<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\HTTP\IncomingRequest;

/**
 * Shared mobile/API operations used by /api/v1 and /api/legacy.
 */
class ApiOperationsService
{
    public function __construct(private BaseConnection $db)
    {
    }

    /** @return array<string, mixed>|null */
    public function userFromJwt(IncomingRequest $request): ?array
    {
        $userId = (int) ($request->jwt_user_id ?? 0);
        if ($userId < 1) {
            return null;
        }

        return $this->db->table('users u')
            ->select('u.id, u.company_id, u.name, r.name as role_name')
            ->join('roles r', 'r.id = u.role_id', 'left')
            ->where('u.id', $userId)
            ->where('u.status', 'active')
            ->get()->getRowArray() ?: null;
    }

    /** @return list<int>|null */
    public function facilityIds(?array $user): ?array
    {
        if (! $user) {
            return [];
        }
        if (($user['role_name'] ?? '') === 'super_admin' && empty($user['company_id'])) {
            return null;
        }
        $companyId = (int) ($user['company_id'] ?? 0);
        if ($companyId < 1) {
            return [];
        }
        $rows = $this->db->table('facilities')->select('id')->where('company_id', $companyId)->where('status', 'active')->get()->getResultArray();

        return array_map(static fn ($r) => (int) $r['id'], $rows);
    }

    public function applyFacilityScope(object $builder, string $column, ?array $user): object
    {
        $ids = $this->facilityIds($user);
        if ($ids === null) {
            return $builder;
        }
        if ($ids === []) {
            return $builder->where('1=0', null, false);
        }

        return $builder->whereIn($column, $ids);
    }

    public function canAccessFacility(?array $user, int $facilityId): bool
    {
        $ids = $this->facilityIds($user);
        if ($ids === null) {
            return true;
        }

        return $facilityId > 0 && in_array($facilityId, $ids, true);
    }
}
