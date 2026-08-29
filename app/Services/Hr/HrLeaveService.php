<?php

namespace App\Services\Hr;

use CodeIgniter\Database\BaseConnection;

class HrLeaveService
{
    public const STATUSES = [
        'draft'     => 'Draft',
        'pending'   => 'Pending',
        'approved'  => 'Approved',
        'rejected'  => 'Rejected',
        'cancelled' => 'Cancelled',
    ];

    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function tablesReady(): bool
    {
        $schema = new HrSchemaService($this->db);

        return $schema->leaveOperationsReady() && $schema->leaveTypesReady();
    }

    /** @return list<array<string, mixed>> */
    public function leaveTypes(?int $companyId = null): array
    {
        if (! $this->db->tableExists('hr_leave_types')) {
            return [];
        }

        $q = $this->db->table('hr_leave_types');
        if ($this->db->fieldExists('is_active', 'hr_leave_types')) {
            $q->where('is_active', 1);
        }
        if ($this->db->fieldExists('sort_order', 'hr_leave_types')) {
            $q->orderBy('sort_order', 'ASC');
        }
        if ($companyId && $this->db->fieldExists('company_id', 'hr_leave_types')) {
            $q->groupStart()->where('company_id', $companyId)->orWhere('company_id', null)->groupEnd();
        }

        return $q->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function balancesForEmployee(int $employeeId, ?int $year = null): array
    {
        if (! $this->db->tableExists('hr_leave_balances')) {
            return [];
        }

        $year = $year ?? (int) date('Y');

        $q = $this->db->table('hr_leave_balances b')
            ->select('b.*, t.code AS type_code, t.name AS type_name, t.is_paid')
            ->join('hr_leave_types t', 't.id = b.leave_type_id', 'left')
            ->where('b.employee_id', $employeeId)
            ->where('b.balance_year', $year);
        if ($this->db->fieldExists('sort_order', 'hr_leave_types')) {
            $q->orderBy('t.sort_order', 'ASC');
        } else {
            $q->orderBy('t.name', 'ASC');
        }

        return $q->get()->getResultArray();
    }

    public function availableBalance(int $employeeId, int $leaveTypeId, ?int $year = null): float
    {
        $year    = $year ?? (int) date('Y');
        $balance = $this->ensureBalance($employeeId, $leaveTypeId, $year);

        return max(0, (float) $balance['opening_balance'] + (float) $balance['accrued'] + (float) $balance['adjusted']
            - (float) $balance['used'] - (float) $balance['pending']);
    }

    /** @param array<string, mixed> $data */
    public function submitRequest(array $data, int $userId): int
    {
        if (! $this->tablesReady()) {
            throw new \RuntimeException('Leave tables missing.');
        }

        $employeeId  = (int) $data['employee_id'];
        $leaveTypeId = (int) $data['leave_type_id'];
        $year        = (int) date('Y', strtotime((string) $data['start_date']));

        $emp = $this->db->table('employees')->where('id', $employeeId)->get()->getRowArray();
        if (! $emp || empty($emp['leave_applicable'])) {
            throw new \RuntimeException('Leave is not applicable for this employee.');
        }

        $days = (float) ($data['days_requested'] ?? $this->calculateDays(
            (string) $data['start_date'],
            (string) $data['end_date'],
            (string) ($data['half_day'] ?? 'none')
        ));

        if ($days <= 0) {
            throw new \RuntimeException('Invalid leave dates.');
        }

        $available = $this->availableBalance($employeeId, $leaveTypeId, $year);
        $type      = $this->db->table('hr_leave_types')->where('id', $leaveTypeId)->get()->getRowArray();
        if ($type && $type['code'] !== 'unpaid' && $days > $available) {
            throw new \RuntimeException('Insufficient leave balance.');
        }

        $payload = [
            'employee_id'     => $employeeId,
            'leave_type_id'   => $leaveTypeId,
            'start_date'      => $data['start_date'],
            'end_date'        => $data['end_date'],
            'days_requested'  => $days,
            'half_day'        => $data['half_day'] ?? 'none',
            'reason'          => $data['reason'] ?? null,
            'status'          => 'pending',
            'requested_by'    => $userId,
        ];

        helper('fm');
        if (function_exists('fm_insert_row_id')) {
            $id = fm_insert_row_id($this->db, 'hr_leave_requests', $payload);
        } else {
            $this->db->table('hr_leave_requests')->insert($payload);
            $id = (int) $this->db->insertID();
        }

        $this->incrementPending($employeeId, $leaveTypeId, $year, $days);
        $this->writeHistory($id, $employeeId, 'submitted', $userId, $payload);

        return $id;
    }

    public function approveRequest(int $requestId, int $reviewerId, ?string $notes = null): bool
    {
        $req = $this->findRequest($requestId);
        if (! $req || $req['status'] !== 'pending') {
            return false;
        }

        $year = (int) date('Y', strtotime((string) $req['start_date']));
        $this->finalizePending((int) $req['employee_id'], (int) $req['leave_type_id'], $year, (float) $req['days_requested'], approve: true);
        $this->markAttendanceLeave($req);

        $this->db->table('hr_leave_requests')->where('id', $requestId)->update([
            'status'       => 'approved',
            'reviewed_by'  => $reviewerId,
            'reviewed_at'  => date('Y-m-d H:i:s'),
            'review_notes' => $notes,
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
        $this->writeHistory($requestId, (int) $req['employee_id'], 'approved', $reviewerId, $req);

        return true;
    }

    public function rejectRequest(int $requestId, int $reviewerId, ?string $notes = null): bool
    {
        $req = $this->findRequest($requestId);
        if (! $req || $req['status'] !== 'pending') {
            return false;
        }

        $year = (int) date('Y', strtotime((string) $req['start_date']));
        $this->finalizePending((int) $req['employee_id'], (int) $req['leave_type_id'], $year, (float) $req['days_requested'], approve: false);

        $this->db->table('hr_leave_requests')->where('id', $requestId)->update([
            'status'       => 'rejected',
            'reviewed_by'  => $reviewerId,
            'reviewed_at'  => date('Y-m-d H:i:s'),
            'review_notes' => $notes,
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
        $this->writeHistory($requestId, (int) $req['employee_id'], 'rejected', $reviewerId, $req);

        return true;
    }

    /** @param array<string, mixed> $filters */
    public function listRequests(array $filters = [], int $limit = 200): array
    {
        if (! $this->tablesReady() || ! $this->db->fieldExists('employee_id', 'hr_leave_requests')) {
            return [];
        }

        $q = $this->db->table('hr_leave_requests r')
            ->select('r.*, t.name AS leave_type_name, t.code AS leave_type_code, e.emp_code, u.name AS employee_name')
            ->join('hr_leave_types t', 't.id = r.leave_type_id', 'left')
            ->join('employees e', 'e.id = r.employee_id', 'left')
            ->join('users u', 'u.id = e.user_id', 'left');

        if (! empty($filters['employee_id'])) {
            $q->where('r.employee_id', (int) $filters['employee_id']);
        }
        if (! empty($filters['status'])) {
            $q->where('r.status', (string) $filters['status']);
        }
        if (! empty($filters['company_id']) && $this->db->fieldExists('company_id', 'employees')) {
            $q->where('e.company_id', (int) $filters['company_id']);
        }

        return $q->orderBy('r.created_at', 'DESC')->limit($limit)->get()->getResultArray();
    }

    /** @return list<array<string, mixed>> */
    public function pendingApprovals(?int $companyId = null): array
    {
        return $this->listRequests(array_filter([
            'status'     => 'pending',
            'company_id' => $companyId,
        ]));
    }

    /** @return list<array<string, mixed>> */
    public function forEmployee(int $employeeId, int $limit = 50): array
    {
        return $this->listRequests(['employee_id' => $employeeId], $limit);
    }

    public function findRequest(int $id): ?array
    {
        if (! $this->tablesReady()) {
            return null;
        }

        $row = $this->db->table('hr_leave_requests r')
            ->select('r.*, t.name AS leave_type_name, t.code AS leave_type_code')
            ->join('hr_leave_types t', 't.id = r.leave_type_id', 'left')
            ->where('r.id', $id)
            ->get()->getRowArray();

        return $row ?: null;
    }

    public function initializeBalances(int $employeeId, ?int $companyId = null): int
    {
        if (! $this->db->tableExists('hr_leave_balances')) {
            return 0;
        }

        $year  = (int) date('Y');
        $types = $this->leaveTypes($companyId);
        $count = 0;

        foreach ($types as $type) {
            if ($this->db->table('hr_leave_balances')
                ->where('employee_id', $employeeId)
                ->where('leave_type_id', (int) $type['id'])
                ->where('balance_year', $year)
                ->countAllResults() > 0) {
                continue;
            }

            $entitlement = $this->resolveEntitlement((int) $type['id'], $employeeId);

            $this->db->table('hr_leave_balances')->insert([
                'employee_id'      => $employeeId,
                'leave_type_id'    => (int) $type['id'],
                'balance_year'     => $year,
                'opening_balance'  => $entitlement,
                'accrued'          => 0,
                'used'             => 0,
                'pending'          => 0,
                'adjusted'         => 0,
            ]);
            $count++;
        }

        return $count;
    }

    public function calculateDays(string $start, string $end, string $halfDay = 'none'): float
    {
        $startTs = strtotime($start);
        $endTs   = strtotime($end);
        if ($startTs === false || $endTs === false || $endTs < $startTs) {
            return 0;
        }

        $days = (int) floor(($endTs - $startTs) / 86400) + 1;
        if ($halfDay !== 'none' && $days === 1) {
            return 0.5;
        }

        return (float) $days;
    }

    /** @return array<string, mixed> */
    private function ensureBalance(int $employeeId, int $leaveTypeId, int $year): array
    {
        $row = $this->db->table('hr_leave_balances')
            ->where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('balance_year', $year)
            ->get()->getRowArray();

        if ($row) {
            return $row;
        }

        $emp = $this->db->table('employees')->where('id', $employeeId)->get()->getRowArray();
        $this->initializeBalances($employeeId, $emp['company_id'] ?? null);

        $row = $this->db->table('hr_leave_balances')
            ->where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('balance_year', $year)
            ->get()->getRowArray();

        return $row ?: [
            'opening_balance' => 0, 'accrued' => 0, 'used' => 0, 'pending' => 0, 'adjusted' => 0,
        ];
    }

    private function resolveEntitlement(int $leaveTypeId, int $employeeId): float
    {
        if (! $this->db->tableExists('hr_leave_policies')) {
            return 0;
        }

        $emp = $this->db->table('employees')->where('id', $employeeId)->get()->getRowArray();
        $q   = $this->db->table('hr_leave_policies')
            ->where('leave_type_id', $leaveTypeId)
            ->where('is_active', 1);

        if (! empty($emp['company_id'])) {
            $q->groupStart()->where('company_id', (int) $emp['company_id'])->orWhere('company_id', null)->groupEnd();
        }

        $policy = $q->orderBy('company_id', 'DESC')->limit(1)->get()->getRowArray();

        return $policy ? (float) $policy['annual_entitlement'] : 0;
    }

    private function incrementPending(int $employeeId, int $leaveTypeId, int $year, float $days): void
    {
        $this->ensureBalance($employeeId, $leaveTypeId, $year);
        $this->db->table('hr_leave_balances')
            ->where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('balance_year', $year)
            ->set('pending', 'pending + ' . (float) $days, false)
            ->update(['updated_at' => date('Y-m-d H:i:s')]);
    }

    private function finalizePending(int $employeeId, int $leaveTypeId, int $year, float $days, bool $approve): void
    {
        $builder = $this->db->table('hr_leave_balances')
            ->where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('balance_year', $year)
            ->set('pending', 'GREATEST(pending - ' . (float) $days . ', 0)', false);

        if ($approve) {
            $builder->set('used', 'used + ' . (float) $days, false);
        }

        $builder->update(['updated_at' => date('Y-m-d H:i:s')]);
    }

    /** @param array<string, mixed> $req */
    private function markAttendanceLeave(array $req): void
    {
        if (! $this->db->tableExists('attendance')) {
            return;
        }

        $start = strtotime((string) $req['start_date']);
        $end   = strtotime((string) $req['end_date']);
        if ($start === false || $end === false) {
            return;
        }

        for ($ts = $start; $ts <= $end; $ts += 86400) {
            $date = date('Y-m-d', $ts);
            $existing = $this->db->table('attendance')
                ->where('employee_id', (int) $req['employee_id'])
                ->where('date', $date)
                ->get()->getRowArray();

            $status = ($req['half_day'] ?? 'none') !== 'none' && $date === $req['start_date'] ? 'half_day' : 'leave';

            if ($existing) {
                $update = ['status' => $status, 'notes' => 'Leave #' . $req['id']];
                $this->db->table('attendance')->where('id', $existing['id'])->update($update);
            } else {
                $this->db->table('attendance')->insert([
                    'employee_id' => (int) $req['employee_id'],
                    'date'        => $date,
                    'status'      => $status,
                    'notes'       => 'Leave #' . $req['id'],
                    'created_at'  => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    /** @param array<string, mixed> $snapshot */
    private function writeHistory(int $requestId, int $employeeId, string $action, int $userId, array $snapshot): void
    {
        if (! $this->db->tableExists('hr_leave_request_history')) {
            return;
        }

        $this->db->table('hr_leave_request_history')->insert([
            'request_id'  => $requestId,
            'employee_id' => $employeeId,
            'action'      => $action,
            'snapshot'    => json_encode($snapshot),
            'changed_by'  => $userId,
            'changed_at'  => date('Y-m-d H:i:s'),
        ]);
    }
}
