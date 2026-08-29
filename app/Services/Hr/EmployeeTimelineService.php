<?php

namespace App\Services\Hr;

use CodeIgniter\Database\BaseConnection;

class EmployeeTimelineService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function tablesReady(): bool
    {
        return $this->db->tableExists('hr_employee_timeline');
    }

    /** @param array<string, mixed>|null $metadata */
    public function record(
        int $employeeId,
        string $eventType,
        string $title,
        ?string $eventCode = null,
        ?string $description = null,
        ?string $refModule = null,
        ?int $refId = null,
        ?array $metadata = null,
        ?int $userId = null,
        ?int $employmentPeriodId = null,
        ?string $eventAt = null
    ): void {
        if (! $this->tablesReady()) {
            return;
        }

        $this->db->table('hr_employee_timeline')->insert([
            'employee_id'          => $employeeId,
            'employment_period_id' => $employmentPeriodId,
            'event_type'           => $eventType,
            'event_code'           => $eventCode,
            'title'                => $title,
            'description'          => $description,
            'ref_module'           => $refModule,
            'ref_id'               => $refId,
            'metadata'             => $metadata ? json_encode($metadata) : null,
            'event_at'             => $eventAt ?? date('Y-m-d H:i:s'),
            'created_by'           => $userId,
        ]);
    }

    /** @return list<array<string, mixed>> */
    public function forEmployee(int $employeeId, int $limit = 100): array
    {
        if (! $this->tablesReady()) {
            return [];
        }

        return $this->db->table('hr_employee_timeline')
            ->where('employee_id', $employeeId)
            ->orderBy('event_at', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }
}
