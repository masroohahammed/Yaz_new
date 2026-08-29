<?php

namespace App\Controllers\Api\V1;

use App\Controllers\BaseController;

abstract class BaseApiController extends BaseController
{
    protected function jwtUserId(): int
    {
        return (int) ($this->request->jwt_user_id ?? 0);
    }

    /** @return array<string, mixed>|null */
    protected function jwtUser(): ?array
    {
        $userId = $this->jwtUserId();
        if ($userId < 1) {
            return null;
        }

        return $this->db->table('users u')
            ->select('u.*, r.name as role_name, r.display_name as role_display')
            ->join('roles r', 'r.id=u.role_id', 'left')
            ->where('u.id', $userId)
            ->where('u.status', 'active')
            ->get()
            ->getRowArray() ?: null;
    }

    protected function scopeFacilitiesForApi(object $builder, string $facilityColumn = 'facility_id'): object
    {
        $user = $this->jwtUser();
        if (! $user) {
            return $builder->where('1=0', null, false);
        }

        if ($user['role_name'] === 'super_admin' && empty($user['company_id'])) {
            return $builder;
        }

        $companyId = $user['company_id'] ?? null;
        if (! $companyId) {
            return $builder;
        }

        $rows = $this->db->table('facilities')
            ->select('id')
            ->where('company_id', (int) $companyId)
            ->where('status', 'active')
            ->get()
            ->getResultArray();

        $facilityIds = array_map(static fn ($r) => (int) $r['id'], $rows);
        if ($facilityIds === []) {
            return $builder->where('1=0', null, false);
        }

        return $builder->whereIn($facilityColumn, $facilityIds);
    }
}
