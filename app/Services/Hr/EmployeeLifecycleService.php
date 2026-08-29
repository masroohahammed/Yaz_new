<?php

namespace App\Services\Hr;

use CodeIgniter\Database\BaseConnection;

class EmployeeLifecycleService
{
    private BaseConnection $db;
    private EmployeeTimelineService $timeline;
    private HrAuditService $audit;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db       = $db ?? \Config\Database::connect();
        $this->timeline = new EmployeeTimelineService($this->db);
        $this->audit    = new HrAuditService($this->db);
    }

    public function tablesReady(): bool
    {
        return $this->db->tableExists('hr_employee_statuses');
    }

    /** @param array<string, mixed> $context */
    public function transitionStatus(int $employeeId, string $toStatusCode, array $context, int $userId): bool
    {
        $emp = (new EmployeeService($this->db))->find($employeeId);
        if (! $emp) {
            throw new \RuntimeException('Employee not found.');
        }

        $toStatus = $this->statusByCode($toStatusCode, $emp['company_id'] ?? null);
        if (! $toStatus) {
            throw new \RuntimeException('Invalid status code.');
        }

        $fromCode = $emp['status_code'] ?? $emp['status'] ?? 'active';
        $patch    = [
            'status_id'  => (int) $toStatus['id'],
            'updated_by' => $userId,
        ];
        if (! empty($toStatus['legacy_status'])) {
            $patch['status'] = $toStatus['legacy_status'];
        } elseif (in_array($toStatusCode, ['inactive', 'resigned', 'terminated'], true)) {
            $patch['status'] = 'inactive';
        }

        if ($toStatusCode === 'notice_period' && ! empty($context['last_working_date'])) {
            $patch['last_working_date'] = $context['last_working_date'];
        }

        $this->db->table('employees')->where('id', $employeeId)->update($patch);

        $this->timeline->record(
            $employeeId,
            'status_change',
            'Status: ' . ($toStatus['name'] ?? $toStatusCode),
            $toStatusCode,
            $context['reason'] ?? null,
            'lifecycle',
            null,
            ['from' => $fromCode, 'to' => $toStatusCode],
            $userId,
            $emp['current_employment_period_id'] ?? null
        );

        $this->audit->log(
            'lifecycle',
            'status_change',
            'employee',
            $employeeId,
            $userId,
            ['status' => $fromCode],
            ['status' => $toStatusCode],
            $context['reason'] ?? null,
            $employeeId,
            $emp['company_id'] ?? null
        );

        return true;
    }

    public function confirmProbation(int $employeeId, int $userId, ?string $confirmationDate = null): bool
    {
        $emp = (new EmployeeService($this->db))->find($employeeId);
        if (! $emp) {
            return false;
        }

        $this->db->table('employees')->where('id', $employeeId)->update([
            'confirmation_date' => $confirmationDate ?? date('Y-m-d'),
            'updated_by'        => $userId,
        ]);

        return $this->transitionStatus($employeeId, 'confirmed', ['reason' => 'Probation confirmed'], $userId);
    }

    /** @param array<string, mixed> $context */
    public function startOffboarding(int $employeeId, string $separationType, array $context, int $userId): bool
    {
        $statusMap = [
            'resignation'   => 'notice_period',
            'termination'   => 'terminated',
            'contract_end'  => 'contract_completed',
        ];
        $code = $statusMap[$separationType] ?? 'notice_period';

        return $this->transitionStatus($employeeId, $code, $context, $userId);
    }

    public function completeInactive(int $employeeId, int $userId, ?string $reason = null): bool
    {
        return $this->transitionStatus($employeeId, 'inactive', ['reason' => $reason ?? 'Separation complete'], $userId);
    }

    /** @param array<string, mixed> $data */
    public function rejoin(int $employeeId, array $data, int $userId): int
    {
        if (! $this->db->tableExists('hr_employment_periods')) {
            throw new \RuntimeException('Employment periods table missing.');
        }

        $emp = (new EmployeeService($this->db))->find($employeeId);
        if (! $emp) {
            throw new \RuntimeException('Employee not found.');
        }

        $this->db->table('hr_employment_periods')
            ->where('employee_id', $employeeId)
            ->where('is_current', 1)
            ->update(['is_current' => 0, 'end_date' => date('Y-m-d')]);

        $periodNo = (int) $this->db->table('hr_employment_periods')
            ->where('employee_id', $employeeId)
            ->countAllResults() + 1;

        $activeStatus = $this->statusByCode('active', $emp['company_id'] ?? null);

        $this->db->table('hr_employment_periods')->insert([
            'employee_id'          => $employeeId,
            'company_id'           => $emp['company_id'] ?? null,
            'operating_company_id' => $data['operating_company_id'] ?? $emp['operating_company_id'] ?? null,
            'period_number'        => $periodNo,
            'joining_date'         => $data['joining_date'] ?? date('Y-m-d'),
            'status_id'            => $activeStatus['id'] ?? null,
            'is_current'           => 1,
        ]);
        $periodId = (int) $this->db->insertID();

        $this->db->table('employees')->where('id', $employeeId)->update([
            'current_employment_period_id' => $periodId,
            'joining_date'                 => $data['joining_date'] ?? date('Y-m-d'),
            'last_working_date'            => null,
            'status_id'                    => $activeStatus['id'] ?? null,
            'status'                       => 'active',
            'updated_by'                   => $userId,
        ]);

        $this->timeline->record(
            $employeeId,
            'rejoin',
            'Employee rejoined (period #' . $periodNo . ')',
            'rejoined',
            $data['reason'] ?? null,
            'lifecycle',
            $periodId,
            null,
            $userId,
            $periodId
        );

        return $periodId;
    }

    /** @return list<array<string, mixed>> */
    public function statuses(?int $companyId = null): array
    {
        if (! $this->tablesReady()) {
            return [];
        }

        $q = $this->db->table('hr_employee_statuses')->where('is_active', 1)->orderBy('sort_order');
        if ($companyId) {
            $q->groupStart()->where('company_id', $companyId)->orWhere('company_id', null)->groupEnd();
        }

        return $q->get()->getResultArray();
    }

    /** @return array<string, mixed>|null */
    private function statusByCode(string $code, ?int $companyId): ?array
    {
        $q = $this->db->table('hr_employee_statuses')->where('code', $code);
        if ($companyId) {
            $q->groupStart()->where('company_id', $companyId)->orWhere('company_id', null)->groupEnd()->orderBy('company_id', 'DESC');
        }
        $row = $q->limit(1)->get()->getRowArray();

        return $row ?: null;
    }
}
