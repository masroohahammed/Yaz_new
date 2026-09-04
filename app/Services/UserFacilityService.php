<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Resolves which facility IDs a user may access (manager assignment + user_facilities pivot).
 */
class UserFacilityService
{
    /** Roles that use explicit facility assignment instead of company-wide access. */
    private const ASSIGNED_ROLES = [
        'facility_manager',
        'property_manager',
        'real_estate_manager',
        'manager',
        'caretaker',
        'landlord',
        'maintenance',
        'maintenance_staff',
        'maintenance_supervisor',
        'technician',
    ];

    /**
     * Roles that get zero access when no property assignments exist.
     * facility_manager is excluded — unassigned FM managers keep company-wide scope.
     */
    private const STRICT_ASSIGNMENT_ROLES = [
        'property_manager',
        'real_estate_manager',
        'manager',
        'caretaker',
        'landlord',
        'maintenance',
        'maintenance_staff',
        'maintenance_supervisor',
        'technician',
    ];

    /** @deprecated Use requiresExplicitAssignments() */
    private const RESTRICTED_ROLES = self::STRICT_ASSIGNMENT_ROLES;

    /**
     * @return list<int> Empty = no facilities; use with care for super_admin (see CompanyScopeService).
     */
    public static function assignedFacilityIds(BaseConnection $db, int $userId, string $role, ?int $companyId = null): array
    {
        if ($userId < 1 || ! in_array($role, self::ASSIGNED_ROLES, true)) {
            return [];
        }

        $ids = [];

        if ($db->tableExists('facilities')) {
            if ($role === 'facility_manager' || $role === 'property_manager' || $role === 'manager' || $role === 'real_estate_manager') {
                $managed = $db->table('facilities')
                    ->select('id')
                    ->where('manager_id', $userId)
                    ->where('deleted_at', null)
                    ->get()
                    ->getResultArray();
                $ids = array_merge($ids, array_map(static fn ($r) => (int) $r['id'], $managed));
            }

            if ($role === 'caretaker') {
                $caretaker = $db->table('facilities')
                    ->select('id')
                    ->where('caretaker_id', $userId)
                    ->where('deleted_at', null)
                    ->get()
                    ->getResultArray();
                $ids = array_merge($ids, array_map(static fn ($r) => (int) $r['id'], $caretaker));
            }

            if ($role === 'landlord') {
                $landlordId = self::landlordIdForUser($db, $userId);
                if ($landlordId > 0) {
                    $owned = $db->table('facilities')
                        ->select('id')
                        ->where('landlord_id', $landlordId)
                        ->where('deleted_at', null)
                        ->get()
                        ->getResultArray();
                    $ids = array_merge($ids, array_map(static fn ($r) => (int) $r['id'], $owned));
                }
            }
        }

        if ($db->tableExists('user_facilities')) {
            $pivotQ = $db->table('user_facilities uf')
                ->select('uf.facility_id')
                ->where('uf.user_id', $userId);
            if ($db->tableExists('facilities')) {
                $pivotQ->join('facilities f', 'f.id = uf.facility_id', 'inner');
                if ($db->fieldExists('deleted_at', 'facilities')) {
                    $pivotQ->where('f.deleted_at', null);
                }
            }
            $pivot = $pivotQ->get()->getResultArray();
            $ids   = array_merge($ids, array_map(static fn ($r) => (int) $r['facility_id'], $pivot));
        }

        if ($db->tableExists('user_property_assignments')) {
            $assignments = $db->table('user_property_assignments')
                ->select('facility_id')
                ->where('user_id', $userId)
                ->get()
                ->getResultArray();
            $ids = array_merge($ids, array_map(static fn ($r) => (int) $r['facility_id'], $assignments));
        }

        $ids = array_values(array_unique(array_filter($ids)));

        if ($companyId !== null && $companyId > 0 && $ids !== []) {
            $ids = self::filterFacilityIdsByCompany($db, $ids, $companyId);
        }

        return $ids;
    }

    /** @param list<int> $facilityIds @return list<int> */
    public static function filterFacilityIdsByCompany(BaseConnection $db, array $facilityIds, int $companyId): array
    {
        if ($facilityIds === [] || ! $db->tableExists('facilities')) {
            return [];
        }

        $q = $db->table('facilities')->select('id')->whereIn('id', $facilityIds)->where('company_id', $companyId);
        if ($db->fieldExists('deleted_at', 'facilities')) {
            $q->where('deleted_at', null);
        }

        return array_map(static fn ($r) => (int) $r['id'], $q->get()->getResultArray());
    }

    public static function landlordIdForUser(BaseConnection $db, int $userId, ?string $email = null): int
    {
        if ($userId < 1) {
            return 0;
        }

        if ($db->fieldExists('landlord_id', 'users')) {
            $row = $db->table('users')->select('landlord_id')->where('id', $userId)->get()->getRowArray();
            $lid = (int) ($row['landlord_id'] ?? 0);
            if ($lid > 0) {
                return $lid;
            }
        }

        if ($email === null) {
            $user = $db->table('users')->select('email')->where('id', $userId)->get()->getRowArray();
            $email = trim((string) ($user['email'] ?? ''));
        }

        if ($email !== '' && $db->tableExists('landlords')) {
            $q = $db->table('landlords')
                ->select('id')
                ->where('deleted_at', null)
                ->groupStart()
                ->where('email', $email);
            if ($db->fieldExists('contact_email', 'landlords')) {
                $q->orWhere('contact_email', $email);
            }
            $landlord = $q->groupEnd()
                ->limit(1)
                ->get()
                ->getRowArray();

            return (int) ($landlord['id'] ?? 0);
        }

        return 0;
    }

    /** @param list<int> $facilityIds @return list<int> */
    public static function tenantIdsForFacilities(BaseConnection $db, array $facilityIds): array
    {
        if ($facilityIds === []) {
            return [];
        }

        $ids = [];

        if ($db->tableExists('lease_contracts')) {
            $q = $db->table('lease_contracts')
                ->select('tenant_id')
                ->whereIn('facility_id', $facilityIds)
                ->where('tenant_id IS NOT NULL', null, false);
            if ($db->fieldExists('deleted_at', 'lease_contracts')) {
                $q->where('deleted_at', null);
            }
            $ids = array_merge($ids, array_map(static fn ($r) => (int) $r['tenant_id'], $q->get()->getResultArray()));
        }

        if ($db->tableExists('units') && $db->fieldExists('tenant_id', 'units')) {
            $q = $db->table('units')
                ->select('tenant_id')
                ->whereIn('facility_id', $facilityIds)
                ->where('tenant_id IS NOT NULL', null, false);
            if ($db->fieldExists('deleted_at', 'units')) {
                $q->where('deleted_at', null);
            }
            $ids = array_merge($ids, array_map(static fn ($r) => (int) $r['tenant_id'], $q->get()->getResultArray()));
        }

        return array_values(array_unique(array_filter($ids)));
    }

    /** @param list<int> $facilityIds @return list<int> */
    public static function landlordIdsForFacilities(BaseConnection $db, array $facilityIds): array
    {
        if ($facilityIds === [] || ! $db->tableExists('facilities')) {
            return [];
        }

        $q = $db->table('facilities')
            ->select('landlord_id')
            ->whereIn('id', $facilityIds)
            ->where('landlord_id IS NOT NULL', null, false);
        if ($db->fieldExists('deleted_at', 'facilities')) {
            $q->where('deleted_at', null);
        }

        return array_values(array_unique(array_filter(array_map(
            static fn ($r) => (int) $r['landlord_id'],
            $q->get()->getResultArray()
        ))));
    }

    public static function tenantAccessible(BaseConnection $db, int $tenantId, array $facilityIds): bool
    {
        if ($tenantId < 1 || $facilityIds === []) {
            return false;
        }

        return in_array($tenantId, self::tenantIdsForFacilities($db, $facilityIds), true);
    }

    public static function landlordAccessible(BaseConnection $db, int $landlordId, array $facilityIds): bool
    {
        if ($landlordId < 1 || $facilityIds === []) {
            return false;
        }

        return in_array($landlordId, self::landlordIdsForFacilities($db, $facilityIds), true);
    }

    /**
     * Facility IDs a user may see in helpdesk (assigned + company facilities for Facility Manager).
     *
     * @return list<int>
     */
    public static function helpdeskFacilityIdsForUser(BaseConnection $db, array $user): array
    {
        $role      = (string) ($user['role_name'] ?? '');
        $userId    = (int) ($user['id'] ?? 0);
        $companyId = (int) ($user['company_id'] ?? 0);
        if ($companyId < 1 && function_exists('session')) {
            $companyId = (int) (session()->get('company_id') ?? 0);
        }

        $ids = self::assignedFacilityIds($db, $userId, $role, $companyId > 0 ? $companyId : null);

        if ($role !== 'facility_manager' || ! $db->tableExists('facilities')) {
            return $ids;
        }

        if ($companyId < 1) {
            return $ids;
        }

        $q = $db->table('facilities')->select('id')->where('company_id', $companyId);
        if ($db->fieldExists('deleted_at', 'facilities')) {
            $q->where('deleted_at', null);
        }
        if ($db->fieldExists('status', 'facilities')) {
            $q->where('status', 'active');
        }

        $companyIds = array_map(static fn ($r) => (int) $r['id'], $q->get()->getResultArray());

        return array_values(array_unique(array_merge($ids, $companyIds)));
    }

    /**
     * Whether this user may open a helpdesk complaint record.
     */
    public static function canAccessComplaint(BaseConnection $db, array $user, array $complaint): bool
    {
        $role = (string) ($user['role_name'] ?? '');

        if ($role === 'super_admin') {
            return true;
        }

        if ($role === 'salesman') {
            return (int) ($complaint['salesman_id'] ?? 0) === (int) ($user['id'] ?? 0);
        }

        if ($role === 'client') {
            return ($complaint['requester_email'] ?? '') === ($user['email'] ?? '');
        }

        if ($role === 'facility_manager' && helpdesk_is_non_facility($complaint)) {
            return true;
        }

        if (! in_array($role, ['facility_manager', 'property_manager', 'real_estate_manager', 'manager', 'landlord'], true)) {
            return false;
        }

        if (helpdesk_is_non_facility($complaint)) {
            return $role === 'facility_manager';
        }

        $companyId = (int) ($user['company_id'] ?? 0);
        $facilityIds = $role === 'facility_manager'
            ? self::helpdeskFacilityIdsForUser($db, $user)
            : self::assignedFacilityIds($db, (int) ($user['id'] ?? 0), $role, $companyId > 0 ? $companyId : null);
        if ($facilityIds === []) {
            return false;
        }

        $fid = (int) ($complaint['facility_id'] ?? 0);
        if ($fid > 0 && in_array($fid, $facilityIds, true)) {
            return true;
        }

        $uid = (int) ($complaint['unit_id'] ?? 0);
        if ($uid > 0 && $db->tableExists('units')) {
            $unit = $db->table('units')->select('facility_id')->where('id', $uid)->get()->getRowArray();

            return $unit && in_array((int) $unit['facility_id'], $facilityIds, true);
        }

        return false;
    }

    /** @return list<int> */
    public static function unitIdsForFacilities(BaseConnection $db, array $facilityIds): array
    {
        if ($facilityIds === [] || ! $db->tableExists('units')) {
            return [];
        }

        $q = $db->table('units')->select('id')->whereIn('facility_id', $facilityIds);
        if ($db->fieldExists('deleted_at', 'units')) {
            $q->where('deleted_at', null);
        }

        return array_map(static fn ($r) => (int) $r['id'], $q->get()->getResultArray());
    }

    /**
     * Restrict helpdesk complaints to facilities assigned to this user.
     *
     * @param \CodeIgniter\Model $model Helpdesk model (maintenance_requests)
     */
    public static function applyHelpdeskScope($model, BaseConnection $db, array $user): void
    {
        $role   = (string) ($user['role_name'] ?? '');
        $userId = (int) ($user['id'] ?? 0);
        $companyId = (int) ($user['company_id'] ?? 0);

        switch ($role) {
            case 'super_admin':
                return;

            case 'facility_manager':
            case 'property_manager':
            case 'real_estate_manager':
            case 'manager':
            case 'landlord':
                $facilityIds = $role === 'facility_manager'
                    ? self::helpdeskFacilityIdsForUser($db, $user)
                    : self::assignedFacilityIds($db, $userId, $role, $companyId > 0 ? $companyId : null);

                if ($role === 'facility_manager') {
                    $model->groupStart();
                    if ($db->fieldExists('work_type', 'maintenance_requests')) {
                        $model->where('work_type', 'non_facility');
                    } else {
                        $model->where('facility_id', null);
                    }
                    if ($facilityIds !== []) {
                        $unitIds = self::unitIdsForFacilities($db, $facilityIds);
                        $model->orGroupStart();
                        $model->whereIn('facility_id', $facilityIds);
                        if ($unitIds !== []) {
                            $model->orWhereIn('unit_id', $unitIds);
                        }
                        if ($db->fieldExists('work_type', 'maintenance_requests')) {
                            $model->where('work_type !=', 'non_facility');
                        }
                        $model->groupEnd();
                    }
                    $model->groupEnd();

                    return;
                }

                if ($facilityIds === []) {
                    $model->where('1', '0', false);

                    return;
                }

                $unitIds = self::unitIdsForFacilities($db, $facilityIds);
                $model->groupStart();
                $model->whereIn('facility_id', $facilityIds);
                if ($unitIds !== []) {
                    $model->orWhereIn('unit_id', $unitIds);
                }
                $model->groupEnd();

                return;

            case 'supervisor':
                $facilityIds = array_unique(array_filter(array_column(
                    $db->table('work_orders')->select('facility_id')
                        ->where('supervisor_id', $userId)
                        ->where('deleted_at', null)
                        ->get()->getResultArray(),
                    'facility_id'
                )));
                if ($facilityIds === []) {
                    $model->where('1', '0', false);

                    return;
                }
                $model->whereIn('facility_id', $facilityIds);

                return;

            case 'salesman':
                $model->where('salesman_id', $userId);

                return;

            case 'client':
                $model->where('requester_email', (string) ($user['email'] ?? ''));

                return;

            default:
                $model->where('1', '0', false);
        }
    }

    /**
     * Restrict work order queries (alias wo) — Facility Manager sees company facilities + all non-facility WOs.
     *
     * @param object $builder Query builder with work_orders joined as $alias
     */
    public static function applyWorkOrderScope($builder, BaseConnection $db, array $user, string $alias = 'wo'): void
    {
        $role      = (string) ($user['role_name'] ?? '');
        $userId    = (int) ($user['id'] ?? 0);
        $companyId = (int) ($user['company_id'] ?? 0);
        $fid       = static fn (string $col) => "{$alias}.{$col}";

        switch ($role) {
            case 'super_admin':
            case 'client':
                return;

            case 'facility_manager':
                $facilityIds = self::helpdeskFacilityIdsForUser($db, $user);
                $builder->groupStart();
                if ($db->fieldExists('customer_type', 'work_orders')) {
                    $builder->where($fid('customer_type'), 'non_facility');
                } else {
                    $builder->where($fid('facility_id'), null);
                }
                if ($facilityIds !== []) {
                    $unitIds = self::unitIdsForFacilities($db, $facilityIds);
                    $builder->orGroupStart();
                    $builder->whereIn($fid('facility_id'), $facilityIds);
                    if ($unitIds !== []) {
                        $builder->orWhereIn($fid('unit_id'), $unitIds);
                    }
                    if ($db->fieldExists('customer_type', 'work_orders')) {
                        $builder->where($fid('customer_type') . ' !=', 'non_facility');
                    }
                    $builder->groupEnd();
                }
                $builder->groupEnd();

                return;

            case 'property_manager':
            case 'real_estate_manager':
            case 'manager':
            case 'landlord':
                $facilityIds = self::assignedFacilityIds($db, $userId, $role, $companyId > 0 ? $companyId : null);
                if ($facilityIds === []) {
                    $builder->where('1', '0', false);

                    return;
                }
                $unitIds = self::unitIdsForFacilities($db, $facilityIds);
                $builder->groupStart();
                $builder->whereIn($fid('facility_id'), $facilityIds);
                if ($unitIds !== []) {
                    $builder->orWhereIn($fid('unit_id'), $unitIds);
                }
                $builder->groupEnd();

                return;

            case 'supervisor':
                $builder->where($fid('supervisor_id'), $userId);

                return;

            case 'technician':
                $builder->where($fid('assigned_to'), $userId);

                return;

            case 'finance_manager':
            case 'finance_user':
            case 'procurement_officer':
                if (! empty($user['company_id'])) {
                    $builder->where($fid('company_id'), (int) $user['company_id']);
                }

                return;

            default:
                $builder->where('1', '0', false);
        }
    }

    public static function usesAssignedFacilities(string $role): bool
    {
        return in_array($role, self::ASSIGNED_ROLES, true);
    }

    public static function requiresExplicitAssignments(string $role): bool
    {
        return in_array($role, self::STRICT_ASSIGNMENT_ROLES, true);
    }

    /** @deprecated Use requiresExplicitAssignments() */
    public static function isRestrictedRole(string $role): bool
    {
        return self::requiresExplicitAssignments($role);
    }

    /** @param list<int> $facilityIds */
    public static function syncUserFacilities(BaseConnection $db, int $userId, array $facilityIds): void
    {
        if (! $db->tableExists('user_facilities')) {
            return;
        }

        $db->table('user_facilities')->where('user_id', $userId)->delete();

        foreach (array_unique(array_filter(array_map('intval', $facilityIds))) as $fid) {
            if ($fid < 1) {
                continue;
            }
            $db->table('user_facilities')->insert([
                'user_id'     => $userId,
                'facility_id' => $fid,
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        }
    }

    /** @return list<int> */
    public static function facilityIdsForUser(BaseConnection $db, int $userId): array
    {
        if (! $db->tableExists('user_facilities')) {
            return [];
        }

        return array_map(
            static fn ($r) => (int) $r['facility_id'],
            $db->table('user_facilities')->select('facility_id')->where('user_id', $userId)->get()->getResultArray()
        );
    }

    /** @return list<int> */
    public static function idsForUser(BaseConnection $db, int $userId, string $role): array
    {
        if (self::usesAssignedFacilities($role)) {
            return self::assignedFacilityIds($db, $userId, $role);
        }

        return [];
    }
}
