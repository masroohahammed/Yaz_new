<?php

namespace App\Models;

use CodeIgniter\Model;

class Tenant_model extends Model
{
    protected $table          = 'tenants';
    protected $primaryKey     = 'id';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';

    protected $allowedFields = [
        'company_id', 'user_id', 'tenant_type', 'full_name', 'nationality', 'gender', 'dob',
        'phone', 'whatsapp', 'email', 'company_name', 'company_cr', 'qid_no', 'qid_expiry',
        'passport_no', 'passport_expiry', 'emergency_name', 'emergency_phone', 'emergency_relation',
        'status', 'notes', 'current_unit_id', 'is_blacklisted',
        'blacklist_reason', 'blacklisted_at', 'blacklisted_by',
        'unblacklist_reason', 'unblacklisted_at', 'unblacklisted_by',
    ];

    public function listPaginated(array $filters, int $perPage = 20, int $page = 1, ?array $facilityIds = null): array
    {
        $builder = $this->db->table('tenants t')
            ->where('t.deleted_at', null);

        if ($this->hasCurrentUnitId()) {
            $builder->select('t.*, u.unit_number AS current_unit_number, f.name AS current_property_name')
                ->join('units u', 'u.id = t.current_unit_id', 'left')
                ->join('facilities f', 'f.id = u.facility_id', 'left');
        } else {
            $builder->select('t.*');
        }

        if ($facilityIds !== null) {
            if (empty($facilityIds)) {
                $builder->where('1 = 0', null, false);
            } elseif ($this->hasCurrentUnitId()) {
                $builder->groupStart()
                    ->whereIn('f.id', $facilityIds)
                    ->orWhereIn('t.id', $this->tenantIdsForFacilities($facilityIds))
                    ->groupEnd();
            } else {
                $builder->whereIn('t.id', $this->tenantIdsForFacilities($facilityIds));
            }
        }

        $this->applyListFilters($builder, $filters);

        $total = (clone $builder)->countAllResults(false);
        $rows  = $builder->orderBy('t.full_name', 'ASC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()->getResultArray();

        foreach ($rows as &$row) {
            $row['is_blacklisted'] = $this->tenantIsBlacklisted($row);
        }
        unset($row);

        return [
            'rows'    => $rows,
            'total'   => $total,
            'perPage' => $perPage,
            'page'    => $page,
        ];
    }

    public function findDetail(int $id): ?array
    {
        $builder = $this->db->table('tenants t');

        if ($this->hasCurrentUnitId()) {
            $builder->select('t.*, u.unit_number AS current_unit_number, u.facility_id AS current_facility_id, f.name AS current_property_name, pu.name AS portal_user_name')
                ->join('units u', 'u.id = t.current_unit_id', 'left')
                ->join('facilities f', 'f.id = u.facility_id', 'left');
        } else {
            $builder->select('t.*, pu.name AS portal_user_name');
        }

        $row = $builder
            ->join('users pu', 'pu.id = t.user_id', 'left')
            ->where('t.id', $id)
            ->where('t.deleted_at', null)
            ->get()->getRowArray();

        if ($row) {
            $row['is_blacklisted'] = $this->tenantIsBlacklisted($row);
        }

        return $row ?: null;
    }

    /** Tenant 360° profile aggregates */
    public function get360Data(int $tenantId): array
    {
        $out = [
            'activeLease'      => null,
            'leaseHistory'     => [],
            'payments'         => [],
            'outstanding'      => ['count' => 0, 'total' => 0],
            'cheques'          => [],
            'complaints'       => [],
            'workOrders'       => [],
            'occupants'        => [],
            'documents'        => [],
        ];

        if ($this->db->tableExists('lease_contracts')) {
            $out['leaseHistory'] = $this->db->table('lease_contracts lc')
                ->select('lc.*, f.name AS property_name, u.unit_number')
                ->join('facilities f', 'f.id = lc.facility_id', 'left')
                ->join('units u', 'u.id = lc.unit_id', 'left')
                ->where('lc.tenant_id', $tenantId)
                ->where('lc.deleted_at', null)
                ->orderBy('lc.start_date', 'DESC')
                ->get()->getResultArray();

            foreach ($out['leaseHistory'] as $lc) {
                if ($lc['status'] === 'active') {
                    $out['activeLease'] = $lc;
                    break;
                }
            }
        }

        if ($this->db->tableExists('lease_payments')) {
            $out['payments'] = $this->db->table('lease_payments')
                ->where('tenant_id', $tenantId)
                ->orderBy('due_date', 'DESC')
                ->limit(20)
                ->get()->getResultArray();

            $pending = $this->db->table('lease_payments')
                ->select('COUNT(*) AS cnt, SUM(amount) AS total')
                ->where('tenant_id', $tenantId)
                ->whereIn('status', ['pending', 'overdue', 'partial'])
                ->get()->getRowArray();
            $out['outstanding'] = [
                'count' => (int) ($pending['cnt'] ?? 0),
                'total' => (float) ($pending['total'] ?? 0),
            ];
        }

        if ($this->db->tableExists('cheques')) {
            $out['cheques'] = $this->db->table('cheques')
                ->where('tenant_id', $tenantId)
                ->orderBy('cheque_date', 'DESC')
                ->limit(10)
                ->get()->getResultArray();
        }

        $unitIds = array_unique(array_filter(array_column($out['leaseHistory'], 'unit_id')));
        $tenant  = $this->find($tenantId);
        if ($tenant && $this->hasCurrentUnitId() && ! empty($tenant['current_unit_id'])) {
            $unitIds[] = (int) $tenant['current_unit_id'];
        }
        $unitIds = array_unique($unitIds);

        if ($unitIds && $this->db->tableExists('work_orders')) {
            $out['workOrders'] = $this->db->table('work_orders w')
                ->select('w.id, w.wo_number, w.title, w.status, w.priority, w.created_at, u.unit_number')
                ->join('units u', 'u.id = w.unit_id', 'left')
                ->whereIn('w.unit_id', $unitIds)
                ->where('w.deleted_at', null)
                ->orderBy('w.created_at', 'DESC')
                ->limit(10)
                ->get()->getResultArray();
        }

        if ($tenant && $this->db->tableExists('maintenance_requests')) {
            $q = $this->db->table('maintenance_requests')
                ->select('id, ticket_number, category, priority, status, created_at')
                ->orderBy('created_at', 'DESC')
                ->limit(10);
            if ($unitIds) {
                $q->whereIn('unit_id', $unitIds);
            } elseif (! empty($tenant['phone'])) {
                $q->where('requester_phone', $tenant['phone']);
            } else {
                $q->where('1 = 0', null, false);
            }
            $out['complaints'] = $q->get()->getResultArray();
        }

        if ($unitIds && $this->db->tableExists('unit_occupants')) {
            $out['occupants'] = $this->db->table('unit_occupants')
                ->whereIn('unit_id', $unitIds)
                ->where('status', 'active')
                ->orderBy('move_in_date', 'DESC')
                ->get()->getResultArray();
        }

        if ($this->db->tableExists('documents')) {
            $out['documents'] = $this->db->table('documents')
                ->where('module', 'tenants')
                ->where('ref_id', $tenantId)
                ->orderBy('created_at', 'DESC')
                ->get()->getResultArray();
        }

        return $out;
    }

    /** @return array{errors: array<string, string>, warnings: array<string, string>} */
    public function validateTenantData(array $post, bool $isEdit, int $tenantId = 0): array
    {
        $errors   = [];
        $warnings = [];

        $fullName = trim((string) ($post['full_name'] ?? ''));
        $phone    = trim((string) ($post['phone'] ?? ''));
        $email    = trim((string) ($post['email'] ?? ''));
        $type     = $post['tenant_type'] ?? '';
        $qid      = trim((string) ($post['qid_no'] ?? ''));
        $passport = trim((string) ($post['passport_no'] ?? ''));

        if ($fullName === '') {
            $errors['full_name'] = 'Full name is required.';
        }
        if ($phone === '') {
            $errors['phone'] = 'Phone is required.';
        } elseif (! preg_match('/^[0-9+\-\s()]{6,30}$/', $phone)) {
            $errors['phone'] = 'Phone format is invalid.';
        }
        if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email format is invalid.';
        }
        if ($type === '') {
            $errors['tenant_type'] = 'Tenant type is required.';
        }
        $typeNorm = $this->normalizeTenantType($type);
        if ($typeNorm === 'Corporate' && empty(trim((string) ($post['company_name'] ?? '')))) {
            $errors['company_name'] = 'Company name is required for company tenants.';
        }
        if ($qid === '' && $passport === '') {
            $errors['qid_no'] = 'Provide QID or passport number.';
        }

        if ($phone !== '') {
            $dup = $this->db->table('tenants')->where('phone', $phone)->where('deleted_at', null);
            if ($tenantId > 0) {
                $dup->where('id !=', $tenantId);
            }
            if ($dup->countAllResults() > 0) {
                $warnings['phone'] = 'Another tenant already uses this phone number.';
            }
        }
        if ($qid !== '') {
            $dup = $this->db->table('tenants')->where('qid_no', $qid)->where('deleted_at', null);
            if ($tenantId > 0) {
                $dup->where('id !=', $tenantId);
            }
            if ($dup->countAllResults() > 0) {
                $warnings['qid_no'] = 'Another tenant already uses this QID.';
            }
        }

        return ['errors' => $errors, 'warnings' => $warnings];
    }

    public function normalizeTenantType(string $type): string
    {
        $map = [
            'individual' => 'Personal',
            'company'    => 'Corporate',
            'personal'   => 'Personal',
            'corporate'  => 'Corporate',
        ];

        return $map[strtolower($type)] ?? $type;
    }

    public function syncCurrentUnit(int $tenantId, ?int $oldUnitId, ?int $newUnitId): void
    {
        if (! $this->db->tableExists('units')) {
            return;
        }

        if ($oldUnitId && $oldUnitId !== $newUnitId) {
            $hasActive = $this->db->tableExists('lease_contracts')
                && $this->db->table('lease_contracts')
                    ->where('unit_id', $oldUnitId)
                    ->where('status', 'active')
                    ->where('deleted_at', null)
                    ->countAllResults() > 0;
            if (! $hasActive) {
                $this->db->table('units')->where('id', $oldUnitId)->update([
                    'status'     => 'vacant',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        if ($newUnitId) {
            $this->db->table('units')->where('id', $newUnitId)->update([
                'status'     => 'occupied',
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

  private function applyListFilters($builder, array $filters): void
  {
    if (! empty($filters['search'])) {
      $s = $filters['search'];
      $builder->groupStart()
        ->like('t.full_name', $s)
        ->orLike('t.phone', $s)
        ->orLike('t.email', $s)
        ->orLike('t.qid_no', $s)
        ->groupEnd();
    }
    if (! empty($filters['status'])) {
      $builder->where('t.status', $filters['status']);
    }
    if (! empty($filters['tenant_type'])) {
      $builder->where('t.tenant_type', $filters['tenant_type']);
    }
    if (isset($filters['blacklisted']) && $filters['blacklisted'] !== '') {
      if ($this->hasIsBlacklisted()) {
        $builder->where('t.is_blacklisted', (int) $filters['blacklisted']);
      } else {
        $builder->where('t.status', (int) $filters['blacklisted'] ? 'blacklisted' : 'active');
      }
    }
  }

  private function hasCurrentUnitId(): bool
  {
      return $this->db->tableExists('tenants')
          && $this->db->fieldExists('current_unit_id', 'tenants');
  }

  private function hasIsBlacklisted(): bool
  {
      return $this->db->tableExists('tenants')
          && $this->db->fieldExists('is_blacklisted', 'tenants');
  }

  /** @param array<string, mixed> $row */
  public function tenantIsBlacklisted(array $row): bool
  {
      if ($this->hasIsBlacklisted()) {
          return ! empty($row['is_blacklisted']);
      }

      return ($row['status'] ?? '') === 'blacklisted';
  }

  /** @return list<int> */
  private function tenantIdsForFacilities(array $facilityIds): array
  {
    if (! $this->db->tableExists('lease_contracts')) {
      return [];
    }

    return array_column(
      $this->db->table('lease_contracts')
        ->select('tenant_id')
        ->distinct()
        ->whereIn('facility_id', $facilityIds)
        ->where('deleted_at', null)
        ->get()->getResultArray(),
      'tenant_id'
    );
  }
}
