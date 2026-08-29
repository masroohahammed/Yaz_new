<?php

namespace App\Services\Hr;

use CodeIgniter\Database\BaseConnection;

class OffboardingService
{
    private BaseConnection $db;
    private EmployeeTimelineService $timeline;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db       = $db ?? \Config\Database::connect();
        $this->timeline = new EmployeeTimelineService($this->db);
    }

    public function tablesReady(): bool
    {
        return $this->db->tableExists('hr_clearance_instances');
    }

    public function instanceForEmployee(int $employeeId): ?array
    {
        if (! $this->tablesReady()) {
            return null;
        }

        $row = $this->db->table('hr_clearance_instances')->where('employee_id', $employeeId)->orderBy('id', 'DESC')->limit(1)->get()->getRowArray();

        return $row ?: null;
    }

    /** @return list<array<string, mixed>> */
    public function itemsForInstance(int $instanceId): array
    {
        if (! $this->db->tableExists('hr_clearance_item_status')) {
            return [];
        }

        return $this->db->table('hr_clearance_item_status cs')
            ->select('cs.*, i.name, i.code, i.department, i.is_mandatory')
            ->join('hr_clearance_items i', 'i.id = cs.item_id', 'left')
            ->where('cs.instance_id', $instanceId)
            ->orderBy('i.sort_order')
            ->get()->getResultArray();
    }

    public function startClearance(int $employeeId, int $userId, string $separationType = 'resignation'): int
    {
        if (! $this->tablesReady()) {
            throw new \RuntimeException('Clearance tables missing.');
        }

        $emp = (new EmployeeService($this->db))->find($employeeId);
        if (! $emp) {
            throw new \RuntimeException('Employee not found.');
        }

        $checklist = $this->db->table('hr_clearance_checklists')
            ->groupStart()->where('separation_type', $separationType)->orWhere('separation_type', 'all')->groupEnd()
            ->where('is_active', 1)
            ->orderBy('separation_type', 'ASC')
            ->limit(1)
            ->get()->getRowArray();

        if (! $checklist) {
            throw new \RuntimeException('No clearance checklist configured.');
        }

        helper('fm');
        $payload = [
            'employee_id'          => $employeeId,
            'checklist_id'         => (int) $checklist['id'],
            'employment_period_id' => $emp['current_employment_period_id'] ?? null,
            'status'               => 'in_progress',
            'created_by'           => $userId,
        ];

        if (function_exists('fm_insert_row_id')) {
            $instanceId = fm_insert_row_id($this->db, 'hr_clearance_instances', $payload);
        } else {
            $this->db->table('hr_clearance_instances')->insert($payload);
            $instanceId = (int) $this->db->insertID();
        }

        $items = $this->db->table('hr_clearance_items')->where('checklist_id', (int) $checklist['id'])->orderBy('sort_order')->get()->getResultArray();
        foreach ($items as $item) {
            $this->db->table('hr_clearance_item_status')->insert([
                'instance_id' => $instanceId,
                'item_id'     => (int) $item['id'],
                'status'      => 'pending',
            ]);
        }

        (new EmployeeLifecycleService($this->db))->startOffboarding($employeeId, $separationType, [], $userId);
        $this->timeline->record($employeeId, 'clearance', 'Exit clearance started', 'started', null, 'offboarding', $instanceId, null, $userId);

        return $instanceId;
    }

    public function clearItem(int $itemStatusId, int $userId, ?string $notes = null): bool
    {
        $row = $this->db->table('hr_clearance_item_status')->where('id', $itemStatusId)->get()->getRowArray();
        if (! $row) {
            return false;
        }

        $this->db->table('hr_clearance_item_status')->where('id', $itemStatusId)->update([
            'status'     => 'cleared',
            'cleared_by' => $userId,
            'cleared_at' => date('Y-m-d H:i:s'),
            'notes'      => $notes,
        ]);

        $this->maybeCompleteClearance((int) $row['instance_id'], $userId);

        return true;
    }

    public function isClearanceComplete(int $instanceId): bool
    {
        $instance = $this->db->table('hr_clearance_instances')->where('id', $instanceId)->get()->getRowArray();

        return $instance && $instance['status'] === 'cleared';
    }

    private function maybeCompleteClearance(int $instanceId, int $userId): void
    {
        $pending = $this->db->table('hr_clearance_item_status cs')
            ->join('hr_clearance_items i', 'i.id = cs.item_id')
            ->where('cs.instance_id', $instanceId)
            ->where('cs.status', 'pending')
            ->where('i.is_mandatory', 1)
            ->countAllResults();

        if ($pending > 0) {
            return;
        }

        $instance = $this->db->table('hr_clearance_instances')->where('id', $instanceId)->get()->getRowArray();
        if (! $instance) {
            return;
        }

        $this->db->table('hr_clearance_instances')->where('id', $instanceId)->update([
            'status'     => 'cleared',
            'cleared_at' => date('Y-m-d H:i:s'),
        ]);

        $this->timeline->record((int) $instance['employee_id'], 'clearance', 'Exit clearance completed', 'cleared', null, 'offboarding', $instanceId, null, $userId);
    }
}
