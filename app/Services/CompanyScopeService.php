<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Session\Session;
use App\Services\UserFacilityService;

/**
 * Multi-company data isolation via company_id and facility relationships.
 */
class CompanyScopeService
{
    /** @var list<int>|null null = all companies (super admin global view) */
    private ?array $facilityIds = null;

    private bool $facilityIdsResolved = false;

    public function __construct(
        private BaseConnection $db,
        private Session $session
    ) {
    }

    public function activeCompanyId(): ?int
    {
        $role = $this->session->get('user_role');
        $cid  = $this->session->get('company_id');

        if ($role === 'super_admin' && !$cid) {
            return null;
        }

        return $cid ? (int) $cid : null;
    }

    /** @return list<int>|null */
    public function facilityIds(): ?array
    {
        if ($this->facilityIdsResolved) {
            return $this->facilityIds;
        }

        $this->facilityIdsResolved = true;
        $companyId                 = $this->activeCompanyId();
        $role                      = (string) $this->session->get('user_role');
        $userId                    = (int) $this->session->get('user_id');

        if ($companyId === null) {
            $this->facilityIds = null;

            return null;
        }

        // Roles that operate on explicitly-assigned facilities only.
        if (UserFacilityService::usesAssignedFacilities($role)) {
            $assigned = UserFacilityService::assignedFacilityIds($this->db, $userId, $role);

            if ($assigned !== []) {
                $this->facilityIds = $assigned;

                return $this->facilityIds;
            }

            // Restricted roles with no assignments get zero access.
            if (UserFacilityService::isRestrictedRole($role)) {
                $this->facilityIds = [];

                return $this->facilityIds;
            }
            // property_manager / real_estate_manager / facility_manager: company-wide when unassigned.
        }

        $cacheKey = 'fm_facilities_' . $companyId;
        $cached   = cache()->get($cacheKey);
        if (is_array($cached)) {
            $this->facilityIds = array_map('intval', $cached);

            return $this->facilityIds;
        }

        $rows = $this->db->table('facilities')
            ->select('id')
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->get()
            ->getResultArray();

        $this->facilityIds = array_map(fn ($r) => (int) $r['id'], $rows);
        cache()->save($cacheKey, $this->facilityIds, 60);

        return $this->facilityIds;
    }

    /**
     * Restrict builder to facilities in the active company.
     *
     * @param object $builder Query builder
     */
    public function applyFacilityScope($builder, string $facilityColumn = 'facility_id'): object
    {
        $ids = $this->facilityIds();
        if ($ids === null) {
            return $builder;
        }

        if ($ids === []) {
            return $builder->where('1 = 0', null, false);
        }

        return $builder->whereIn($facilityColumn, $ids);
    }

    public function applyCompanyColumn($builder, string $column = 'company_id'): object
    {
        $companyId = $this->activeCompanyId();
        if ($companyId === null) {
            return $builder;
        }

        return $builder->where($column, $companyId);
    }

    public function canAccessFacility(int $facilityId): bool
    {
        $ids = $this->facilityIds();
        if ($ids === null) {
            return true;
        }

        return in_array($facilityId, $ids, true);
    }

    public function canAccessWorkOrder(int $woId): bool
    {
        $wo = $this->db->table('work_orders')->select('facility_id')->where('id', $woId)->get()->getRowArray();
        if (!$wo) {
            return false;
        }

        return $this->canAccessFacility((int) $wo['facility_id']);
    }

    /** @return list<array<string, mixed>> */
    public function activeCompanies(): array
    {
        return $this->db->table('companies')->where('status', 'active')->orderBy('name')->get()->getResultArray();
    }

    /**
     * Apply tenant scope: company_id column when present, else facility_id.
     */
    public function scopeTable($builder, string $table, string $facilityColumn = 'facility_id'): object
    {
        $companyId = $this->activeCompanyId();

        if ($companyId !== null && $this->db->fieldExists('company_id', $table)) {
            return $builder->where($table . '.company_id', $companyId);
        }

        return $this->applyFacilityScope($builder, $facilityColumn);
    }

    /** Sync company_id on row from facility when column exists. */
    public function resolveCompanyIdForFacility(int $facilityId): ?int
    {
        $row = $this->db->table('facilities')->select('company_id')->where('id', $facilityId)->get()->getRowArray();

        return isset($row['company_id']) ? (int) $row['company_id'] : $this->activeCompanyId();
    }
}
