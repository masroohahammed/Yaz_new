<?php

namespace App\Models;

use CodeIgniter\Model;

class Landlord_model extends Model
{
    protected $table          = 'landlords';
    protected $primaryKey     = 'id';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';

    protected $allowedFields = [
        'company_id', 'full_name', 'full_name_ar', 'phone', 'phone2', 'email',
        'nationality', 'id_type', 'id_number', 'id_expiry', 'address',
        'bank_name', 'bank_account', 'bank_iban', 'commission_pct', 'status', 'notes',
    ];

    public function listPaginated(array $filters, int $perPage = 20, int $page = 1, ?array $facilityIds = null): array
    {
        $builder = $this->db->table('landlords l')
            ->select('l.*, COUNT(DISTINCT f.id) AS property_count')
            ->join('facilities f', 'f.landlord_id = l.id AND f.deleted_at IS NULL', 'left')
            ->where('l.deleted_at', null)
            ->groupBy('l.id');

        if ($facilityIds !== null) {
            if (empty($facilityIds)) {
                $builder->where('1 = 0', null, false);
            } else {
                $builder->whereIn('f.id', $facilityIds);
            }
        }

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $builder->groupStart()
                ->like('l.full_name', $search)
                ->orLike('l.full_name_ar', $search)
                ->orLike('l.phone', $search)
                ->orLike('l.email', $search)
                ->orLike('l.id_number', $search)
                ->groupEnd();
        }

        $status = trim((string) ($filters['status'] ?? ''));
        if ($status !== '') {
            $builder->where('l.status', $status);
        }

        $total = (clone $builder)->countAllResults(false);
        $rows  = $builder->orderBy('l.full_name', 'ASC')
            ->limit($perPage, ($page - 1) * $perPage)
            ->get()->getResultArray();

        return [
            'rows'    => $rows,
            'total'   => $total,
            'perPage' => $perPage,
            'page'    => $page,
        ];
    }

    public function findDetail(int $id): ?array
    {
        $row = $this->db->table('landlords l')
            ->select('l.*, COUNT(DISTINCT f.id) AS property_count')
            ->join('facilities f', 'f.landlord_id = l.id AND f.deleted_at IS NULL', 'left')
            ->where('l.id', $id)
            ->where('l.deleted_at', null)
            ->groupBy('l.id')
            ->get()->getRowArray();

        return $row ?: null;
    }

    /** Landlord 360° profile aggregates */
    public function get360Data(int $landlordId): array
    {
        $out = [
            'properties'  => [],
            'payouts'     => [],
            'documents'   => [],
            'reminders'   => [],
            'activeLeases' => [],
        ];

        if ($this->db->tableExists('facilities')) {
            $out['properties'] = $this->db->table('facilities')
                ->select('id, name, code, status, city, landlord_id')
                ->where('landlord_id', $landlordId)
                ->where('deleted_at', null)
                ->orderBy('name')
                ->get()->getResultArray();
        }

        if ($this->db->tableExists('landlord_payouts')) {
            $out['payouts'] = $this->db->table('landlord_payouts lp')
                ->select('lp.*, f.name AS property_name')
                ->join('facilities f', 'f.id = lp.facility_id', 'left')
                ->where('lp.landlord_id', $landlordId)
                ->orderBy('lp.period_to', 'DESC')
                ->limit(50)
                ->get()->getResultArray();
        }

        if ($this->db->tableExists('documents')) {
            $out['documents'] = $this->db->table('documents')
                ->where('module', 'landlords')
                ->where('ref_id', $landlordId)
                ->orderBy('created_at', 'DESC')
                ->get()->getResultArray();
        }

        $propertyIds = array_column($out['properties'], 'id');
        if ($propertyIds && $this->db->tableExists('lease_contracts')) {
            $out['activeLeases'] = $this->db->table('lease_contracts lc')
                ->select('lc.id, lc.contract_number, lc.end_date, lc.rent_amount, lc.status, f.name AS property_name, u.unit_number, t.full_name AS tenant_name')
                ->join('facilities f', 'f.id = lc.facility_id', 'left')
                ->join('units u', 'u.id = lc.unit_id', 'left')
                ->join('tenants t', 't.id = lc.tenant_id', 'left')
                ->whereIn('lc.facility_id', $propertyIds)
                ->where('lc.status', 'active')
                ->where('lc.deleted_at', null)
                ->orderBy('lc.end_date', 'ASC')
                ->get()->getResultArray();
        }

        $out['reminders'] = $this->buildReminders($landlordId, $out);

        return $out;
    }

    /** @return list<array<string, mixed>> */
    public function buildReminders(int $landlordId, ?array $data360 = null): array
    {
        $reminders = [];
        $landlord  = $this->find($landlordId);
        if (! $landlord) {
            return [];
        }

        $dismissedKeys = $this->dismissedReminderKeys($landlordId);
        $today         = date('Y-m-d');
        $warnDays      = 60;

        if (! empty($landlord['id_expiry'])) {
            $exp = $landlord['id_expiry'];
            if ($exp < $today) {
                $key = 'id_expiry_expired';
                if (! isset($dismissedKeys[$key])) {
                    $reminders[] = [
                        'key'     => $key,
                        'type'    => 'id_expiry',
                        'message' => 'ID/document expired on ' . $exp,
                        'severity' => 'danger',
                        'due'     => $exp,
                    ];
                }
            } elseif ($exp <= date('Y-m-d', strtotime("+{$warnDays} days"))) {
                $key = 'id_expiry_soon';
                if (! isset($dismissedKeys[$key])) {
                    $reminders[] = [
                        'key'     => $key,
                        'type'    => 'id_expiry',
                        'message' => 'ID/document expires on ' . $exp,
                        'severity' => 'warning',
                        'due'     => $exp,
                    ];
                }
            }
        }

        $data360 ??= $this->get360Data($landlordId);
        foreach ($data360['activeLeases'] ?? [] as $lc) {
            if (empty($lc['end_date'])) {
                continue;
            }
            $end = $lc['end_date'];
            if ($end < $today) {
                continue;
            }
            if ($end <= date('Y-m-d', strtotime("+{$warnDays} days"))) {
                $key = 'lease_' . $lc['id'];
                if (! isset($dismissedKeys[$key])) {
                    $reminders[] = [
                        'key'     => $key,
                        'type'    => 'contract_renewal',
                        'message' => 'Contract ' . ($lc['contract_number'] ?? $lc['id']) . ' at ' . ($lc['property_name'] ?? '') . ' ends ' . $end,
                        'severity' => 'info',
                        'due'     => $end,
                        'ref_id'  => (int) $lc['id'],
                    ];
                }
            }
        }

        if ($this->db->tableExists('reminders')) {
            $dbReminders = $this->db->table('reminders')
                ->where('module', 'landlords')
                ->where('ref_id', $landlordId)
                ->where('status', 'pending')
                ->orderBy('reminder_datetime', 'ASC')
                ->get()->getResultArray();
            foreach ($dbReminders as $r) {
                $key = 'db_' . $r['id'];
                if (! isset($dismissedKeys[$key])) {
                    $reminders[] = [
                        'key'     => $key,
                        'type'    => 'system',
                        'message' => $r['message'],
                        'severity' => 'info',
                        'due'     => $r['reminder_datetime'],
                        'reminder_id' => (int) $r['id'],
                    ];
                }
            }
        }

        return $reminders;
    }

    /** @return array<string, true> */
    private function dismissedReminderKeys(int $landlordId): array
    {
        if (! $this->db->tableExists('reminders')) {
            return [];
        }

        $rows = $this->db->table('reminders')
            ->select('message')
            ->where('module', 'landlords')
            ->where('ref_id', $landlordId)
            ->where('status', 'dismissed')
            ->get()->getResultArray();

        $keys = [];
        foreach ($rows as $r) {
            $msg = (string) ($r['message'] ?? '');
            if (str_starts_with($msg, 'dismiss:')) {
                $keys[substr($msg, 8)] = true;
            }
        }

        return $keys;
    }

    public function dismissReminder(int $landlordId, string $reminderKey, int $userId, ?int $reminderId = null): void
    {
        if (! $this->db->tableExists('reminders')) {
            return;
        }

        if ($reminderId > 0) {
            $this->db->table('reminders')
                ->where('id', $reminderId)
                ->where('module', 'landlords')
                ->where('ref_id', $landlordId)
                ->update(['status' => 'dismissed']);

            return;
        }

        $this->db->table('reminders')->insert([
            'module'            => 'landlords',
            'ref_id'            => $landlordId,
            'reminder_datetime' => date('Y-m-d H:i:s'),
            'message'           => 'dismiss:' . $reminderKey,
            'status'            => 'dismissed',
            'created_by'        => $userId,
            'created_at'        => date('Y-m-d H:i:s'),
        ]);
    }

    /** @return list<array<string, mixed>> */
    public function propertiesForLandlord(int $landlordId, ?array $facilityIds = null): array
    {
        if (! $this->db->tableExists('facilities')) {
            return [];
        }

        $q = $this->db->table('facilities')
            ->select('id, name, code')
            ->where('landlord_id', $landlordId)
            ->where('deleted_at', null)
            ->orderBy('name');

        if ($facilityIds !== null && ! empty($facilityIds)) {
            $q->whereIn('id', $facilityIds);
        }

        return $q->get()->getResultArray();
    }

    public function listPayouts(int $landlordId): array
    {
        if (! $this->db->tableExists('landlord_payouts')) {
            return [];
        }

        return $this->db->table('landlord_payouts lp')
            ->select('lp.*, f.name AS property_name')
            ->join('facilities f', 'f.id = lp.facility_id', 'left')
            ->where('lp.landlord_id', $landlordId)
            ->orderBy('lp.period_to', 'DESC')
            ->get()->getResultArray();
    }

  /** @return array{errors: array<string, string>, warnings: array<string, string>} */
    public function validateLandlordData(array $post, bool $isEdit): array
    {
        $errors   = [];
        $warnings = [];

        $fullName = trim((string) ($post['full_name'] ?? ''));
        $phone    = trim((string) ($post['phone'] ?? ''));
        $email    = trim((string) ($post['email'] ?? ''));
        $idExpiry = trim((string) ($post['id_expiry'] ?? ''));

        if ($fullName === '') {
            $errors['full_name'] = 'Full name is required.';
        }
        if ($phone === '') {
            $errors['phone'] = 'Phone is required.';
        }
        if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email format is invalid.';
        }
        if ($idExpiry !== '') {
            if ($idExpiry < date('Y-m-d')) {
                $warnings['id_expiry'] = 'ID expiry is in the past.';
            } elseif ($idExpiry <= date('Y-m-d', strtotime('+60 days'))) {
                $warnings['id_expiry'] = 'ID expires within 60 days.';
            }
        }

        return ['errors' => $errors, 'warnings' => $warnings];
    }

    /** @return array{errors: array<string, string>} */
    public function validatePayoutData(array $post, ?array $landlord = null): array
    {
        $errors = [];

        $facilityId = (int) ($post['property_id'] ?? $post['facility_id'] ?? 0);
        $from       = trim((string) ($post['period_from'] ?? ''));
        $to         = trim((string) ($post['period_to'] ?? ''));
        $gross      = (float) ($post['gross_rent'] ?? 0);

        if ($facilityId <= 0) {
            $errors['property_id'] = 'Property is required.';
        }
        if ($from === '') {
            $errors['period_from'] = 'Period from is required.';
        }
        if ($to === '') {
            $errors['period_to'] = 'Period to is required.';
        }
        if ($from !== '' && $to !== '' && $to < $from) {
            $errors['period_to'] = 'Period to must be on or after period from.';
        }
        if ($gross <= 0) {
            $errors['gross_rent'] = 'Gross rent must be greater than zero.';
        }

        if ($landlord && $facilityId > 0 && $this->db->tableExists('facilities')) {
            $prop = $this->db->table('facilities')->where('id', $facilityId)->get()->getRowArray();
            if ($prop && (int) ($prop['landlord_id'] ?? 0) !== (int) $landlord['id']) {
                $errors['property_id'] = 'Selected property does not belong to this landlord.';
            }
        }

        return ['errors' => $errors];
    }

    /** @return array{errors: array<string, string>} */
    public function validateMarkPaidData(array $post): array
    {
        $errors = [];
        $paidDate = trim((string) ($post['paid_date'] ?? ''));
        if ($paidDate === '') {
            $errors['paid_date'] = 'Paid date is required.';
        }

        return ['errors' => $errors];
    }

    public function computePayoutAmounts(float $gross, float $commissionPct, ?float $commission = null, ?float $deductions = null): array
    {
        $commission  = $commission ?? round($gross * $commissionPct / 100, 2);
        $deductions  = $deductions ?? 0.0;
        $net         = round($gross - $commission - $deductions, 2);

        return [
            'commission' => $commission,
            'deductions' => $deductions,
            'net_amount' => $net,
        ];
    }
}
