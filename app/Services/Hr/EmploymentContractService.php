<?php

namespace App\Services\Hr;

use CodeIgniter\Database\BaseConnection;

class EmploymentContractService
{
    public const STATUSES = [
        'draft'            => 'Draft',
        'pending_approval' => 'Pending Approval',
        'upcoming'         => 'Upcoming',
        'active'           => 'Active',
        'expiring_soon'    => 'Expiring Soon',
        'renewal_pending'  => 'Renewal Pending',
        'renewed'          => 'Renewed',
        'completed'        => 'Completed',
        'released'         => 'Released',
        'terminated'       => 'Terminated',
        'cancelled'        => 'Cancelled',
    ];

    public const EXPIRY_BUCKETS = [
        'expired' => ['label' => 'Expired', 'max' => 0],
        '7'       => ['label' => 'Expiring in 7 days', 'min' => 0, 'max' => 7],
        '15'      => ['label' => 'Expiring in 15 days', 'min' => 8, 'max' => 15],
        '30'      => ['label' => 'Expiring in 30 days', 'min' => 16, 'max' => 30],
        '60'      => ['label' => 'Expiring in 60 days', 'min' => 31, 'max' => 60],
        '90'      => ['label' => 'Expiring in 90 days', 'min' => 61, 'max' => 90],
    ];

    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function tablesReady(): bool
    {
        return $this->db->tableExists('hr_employment_contracts');
    }

    /** @return list<array<string, mixed>> */
    public function forEmployee(int $employeeId, bool $includeHistory = true): array
    {
        if (! $this->tablesReady()) {
            return [];
        }
        $q = $this->db->table('hr_employment_contracts c')
            ->select('c.*, v.name AS supplier_name, comp.name AS company_name, fb.name AS operating_company_name')
            ->join('vendors v', 'v.id = c.supplier_id', 'left')
            ->join('companies comp', 'comp.id = c.company_id', 'left')
            ->join('finance_branches fb', 'fb.id = c.operating_company_id', 'left')
            ->where('c.employee_id', $employeeId)
            ->orderBy('c.contract_start_date', 'DESC');

        if (! $includeHistory) {
            $q->where('c.is_current', 1);
        }

        return $q->get()->getResultArray();
    }

    public function find(int $id): ?array
    {
        if (! $this->tablesReady()) {
            return null;
        }
        $row = $this->db->table('hr_employment_contracts c')
            ->select('c.*, v.name AS supplier_name, e.emp_code, u.name AS employee_name')
            ->join('vendors v', 'v.id = c.supplier_id', 'left')
            ->join('employees e', 'e.id = c.employee_id', 'left')
            ->join('users u', 'u.id = e.user_id', 'left')
            ->where('c.id', $id)
            ->get()->getRowArray();

        return $row ?: null;
    }

    /** @param array<string, mixed> $data */
    public function create(array $data, int $userId): int
    {
        if (! $this->tablesReady()) {
            throw new \RuntimeException('Contract tables missing.');
        }
        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;
        $data['is_current'] = $data['is_current'] ?? 1;
        if (empty($data['contract_status'])) {
            $data['contract_status'] = 'draft';
        }
        $this->syncContractStatus($data);

        if (! empty($data['is_current'])) {
            $this->db->table('hr_employment_contracts')
                ->where('employee_id', (int) $data['employee_id'])
                ->update(['is_current' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
        }

        helper('fm');
        if (function_exists('fm_insert_row_id')) {
            $id = fm_insert_row_id($this->db, 'hr_employment_contracts', $data);
        } else {
            $this->db->table('hr_employment_contracts')->insert($data);
            $id = (int) $this->db->insertID();
        }
        $this->writeHistory($id, (int) $data['employee_id'], 'created', $userId, $data);

        return $id;
    }

    /** @param array<string, mixed> $data */
    public function update(int $id, array $data, int $userId): bool
    {
        $existing = $this->find($id);
        if (! $existing) {
            return false;
        }
        $this->writeHistory($id, (int) $existing['employee_id'], 'updated', $userId, $existing);
        $data['updated_by'] = $userId;
        $this->syncContractStatus($data);

        return $this->db->table('hr_employment_contracts')->where('id', $id)->update($data);
    }

    /** @param array<string, mixed> $newData */
    public function renew(int $oldContractId, array $newData, int $userId): int
    {
        $old = $this->find($oldContractId);
        if (! $old) {
            throw new \RuntimeException('Contract not found.');
        }
        $this->db->table('hr_employment_contracts')->where('id', $oldContractId)->update([
            'contract_status' => 'renewed',
            'is_current'      => 0,
            'renewal_status'  => 'renewed',
            'updated_by'      => $userId,
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);
        $this->writeHistory($oldContractId, (int) $old['employee_id'], 'renewed', $userId, $old);

        $newData['employee_id']      = $old['employee_id'];
        $newData['company_id']       = $newData['company_id'] ?? $old['company_id'];
        $newData['renewal_status']   = 'active';
        $newData['contract_status']  = $newData['contract_status'] ?? 'active';
        $newData['is_current']       = 1;

        return $this->create($newData, $userId);
    }

    /** @param array<string, mixed> $filters */
    public function bucketRows(string $bucket, array $filters = []): array
    {
        if (! isset(self::EXPIRY_BUCKETS[$bucket])) {
            return [];
        }
        $today = date('Y-m-d');
        $q     = $this->baseQuery($filters)->where('c.contract_end_date IS NOT NULL');

        if ($bucket === 'expired') {
            $q->where('c.contract_end_date <', $today);
        } else {
            $max = (int) self::EXPIRY_BUCKETS[$bucket]['max'];
            $min = (int) (self::EXPIRY_BUCKETS[$bucket]['min'] ?? 0);
            $q->where('c.contract_end_date >=', $today)
                ->where('c.contract_end_date <=', date('Y-m-d', strtotime("+{$max} days")));
            if ($min > 0) {
                $q->where('c.contract_end_date >=', date('Y-m-d', strtotime("+{$min} days")));
            }
        }

        return $q->whereIn('c.contract_status', ['active', 'expiring_soon', 'renewal_pending', 'upcoming'])
            ->where('c.is_current', 1)
            ->orderBy('c.contract_end_date', 'ASC')
            ->limit(200)
            ->get()->getResultArray();
    }

    /** @param array<string, mixed> $filters */
    public function expiryDashboard(array $filters = []): array
    {
        $out = [];
        foreach (array_keys(self::EXPIRY_BUCKETS) as $key) {
            $out[$key] = $this->bucketRows($key, $filters);
        }

        return $out;
    }

    /**
     * Sync contract statuses from end dates and dispatch expiry alerts (idempotent per day).
     *
     * @return array{synced: int, alerts_sent: int}
     */
    public function processExpiryAlerts(\App\Services\AlertDispatchService $alerts, array $settings = []): array
    {
        if (! $this->tablesReady()) {
            return ['synced' => 0, 'alerts_sent' => 0];
        }

        $synced = $this->syncAllContractStatuses();
        $sent   = 0;

        foreach (['expired', '7', '15', '30'] as $bucket) {
            $rows = $this->bucketRows($bucket, ['active_only' => true]);
            foreach ($rows as $row) {
                if ($this->alertAlreadySentToday((int) $row['id'], $bucket)) {
                    continue;
                }

                $employeeId = (int) ($row['employee_id'] ?? 0);
                $emp        = $employeeId > 0
                    ? $this->db->table('employees e')
                        ->select('e.*, u.name, u.id AS user_id')
                        ->join('users u', 'u.id = e.user_id', 'left')
                        ->where('e.id', $employeeId)
                        ->get()->getRowArray()
                    : null;

                $title   = 'Contract expiry: ' . ($row['contract_number'] ?: ('#' . $row['id']));
                $message = sprintf(
                    '%s — contract %s ends on %s (%s).',
                    $emp['name'] ?? ('Employee #' . $employeeId),
                    $row['contract_number'] ?: ('#' . $row['id']),
                    $row['contract_end_date'] ?? '—',
                    self::EXPIRY_BUCKETS[$bucket]['label'] ?? $bucket
                );

                if (! empty($emp['user_id'])) {
                    $alerts->notifyUser((int) $emp['user_id'], $title, $message, 'contract_expiry', (int) $row['id']);
                    $this->logAlert((int) $row['id'], $employeeId, $bucket, (int) $emp['user_id'], $row['contract_end_date'] ?? null);
                    $sent++;
                }

                $managers = $this->db->table('users u')
                    ->select('u.id')
                    ->join('roles r', 'r.id = u.role_id')
                    ->whereIn('r.name', ['super_admin', 'facility_manager', 'finance_manager'])
                    ->where('u.status', 'active')
                    ->get()->getResultArray();
                foreach ($managers as $mgr) {
                    $alerts->notifyUser((int) $mgr['id'], $title, $message, 'contract_expiry', (int) $row['id']);
                }

                if (! empty($emp['reporting_manager_id'])) {
                    $rm = $this->db->table('employees')->select('user_id')->where('id', (int) $emp['reporting_manager_id'])->get()->getRowArray();
                    if (! empty($rm['user_id'])) {
                        $alerts->notifyUser((int) $rm['user_id'], $title, $message, 'contract_expiry', (int) $row['id']);
                    }
                }
            }
        }

        return ['synced' => $synced, 'alerts_sent' => $sent];
    }

    public function syncAllContractStatuses(): int
    {
        if (! $this->tablesReady()) {
            return 0;
        }

        $rows = $this->db->table('hr_employment_contracts')
            ->where('is_current', 1)
            ->whereIn('contract_status', ['active', 'expiring_soon'])
            ->where('contract_end_date IS NOT NULL')
            ->get()->getResultArray();

        $count = 0;
        foreach ($rows as $row) {
            $data = $row;
            $before = $data['contract_status'] ?? '';
            $this->syncContractStatus($data);
            if (($data['contract_status'] ?? '') !== $before) {
                $this->db->table('hr_employment_contracts')->where('id', (int) $row['id'])->update([
                    'contract_status' => $data['contract_status'],
                    'updated_at'      => date('Y-m-d H:i:s'),
                ]);
                $count++;
            }
        }

        return $count;
    }

    /** @param array<string, mixed> $row */
    private function syncContractStatus(array &$row): void
    {
        if (empty($row['contract_end_date'])) {
            return;
        }
        $days = (int) floor((strtotime((string) $row['contract_end_date']) - strtotime(date('Y-m-d'))) / 86400);
        if ($days < 0 && in_array($row['contract_status'] ?? '', ['active', 'expiring_soon'], true)) {
            $row['contract_status'] = 'completed';
        } elseif ($days >= 0 && $days <= 30 && ($row['contract_status'] ?? '') === 'active') {
            $row['contract_status'] = 'expiring_soon';
        }
    }

    /** @param array<string, mixed> $snapshot */
    private function writeHistory(int $contractId, int $employeeId, string $action, int $userId, array $snapshot): void
    {
        if (! $this->db->tableExists('hr_employment_contract_history')) {
            return;
        }
        $this->db->table('hr_employment_contract_history')->insert([
            'contract_id' => $contractId,
            'employee_id' => $employeeId,
            'action'      => $action,
            'snapshot'    => json_encode($snapshot),
            'changed_by'  => $userId,
            'changed_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    /** @param array<string, mixed> $filters */
    private function baseQuery(array $filters): \CodeIgniter\Database\BaseBuilder
    {
        $q = $this->db->table('hr_employment_contracts c')
            ->select('c.*, e.emp_code, u.name AS employee_name, v.name AS supplier_name, f.name AS facility_name')
            ->join('employees e', 'e.id = c.employee_id', 'left')
            ->join('users u', 'u.id = e.user_id', 'left')
            ->join('vendors v', 'v.id = c.supplier_id', 'left')
            ->join('facilities f', 'f.id = e.facility_id', 'left');

        if (! empty($filters['company_id'])) {
            $q->where('c.company_id', (int) $filters['company_id']);
        }
        if (! empty($filters['facility_id'])) {
            $q->where('e.facility_id', (int) $filters['facility_id']);
        }
        if (! empty($filters['supplier_id'])) {
            $q->where('c.supplier_id', (int) $filters['supplier_id']);
        }
        if (! empty($filters['search'])) {
            $term = trim((string) $filters['search']);
            $q->groupStart()
                ->like('c.contract_number', $term)
                ->orLike('e.emp_code', $term)
                ->orLike('u.name', $term)
                ->groupEnd();
        }
        if (! empty($filters['active_only'])) {
            $q->where('e.status !=', 'inactive');
        }

        return $q;
    }

    private function alertAlreadySentToday(int $contractId, string $alertType): bool
    {
        if (! $this->db->tableExists('hr_contract_expiry_alerts')) {
            return false;
        }

        return $this->db->table('hr_contract_expiry_alerts')
            ->where('contract_id', $contractId)
            ->where('alert_type', $alertType)
            ->where('DATE(notified_at)', date('Y-m-d'))
            ->countAllResults() > 0;
    }

    private function logAlert(int $contractId, int $employeeId, string $alertType, int $userId, ?string $contractEndDate): void
    {
        if (! $this->db->tableExists('hr_contract_expiry_alerts')) {
            return;
        }
        $this->db->table('hr_contract_expiry_alerts')->insert([
            'contract_id'       => $contractId,
            'employee_id'       => $employeeId,
            'alert_type'        => $alertType,
            'contract_end_date' => $contractEndDate,
            'notified_user_id'  => $userId,
            'notified_at'       => date('Y-m-d H:i:s'),
        ]);
    }
}
