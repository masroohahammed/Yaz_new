<?php

namespace App\Services\Hr;

use CodeIgniter\Database\BaseConnection;

class OnboardingService
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
        return $this->db->tableExists('hr_onboarding_instances');
    }

    /** @return list<array<string, mixed>> */
    public function checklists(?int $companyId = null): array
    {
        if (! $this->db->tableExists('hr_onboarding_checklists')) {
            return [];
        }

        $q = $this->db->table('hr_onboarding_checklists')->where('is_active', 1)->orderBy('sort_order');
        if ($companyId) {
            $q->groupStart()->where('company_id', $companyId)->orWhere('company_id', null)->groupEnd();
        }

        return $q->get()->getResultArray();
    }

    public function instanceForEmployee(int $employeeId): ?array
    {
        if (! $this->tablesReady()) {
            return null;
        }

        $row = $this->db->table('hr_onboarding_instances')->where('employee_id', $employeeId)->orderBy('id', 'DESC')->limit(1)->get()->getRowArray();

        return $row ?: null;
    }

    /** @return list<array<string, mixed>> */
    public function tasksForInstance(int $instanceId): array
    {
        if (! $this->db->tableExists('hr_onboarding_task_status')) {
            return [];
        }

        return $this->db->table('hr_onboarding_task_status ts')
            ->select('ts.*, t.name, t.code, t.assignee_role, t.is_mandatory')
            ->join('hr_onboarding_tasks t', 't.id = ts.task_id', 'left')
            ->where('ts.instance_id', $instanceId)
            ->orderBy('t.sort_order')
            ->get()->getResultArray();
    }

    public function startOnboarding(int $employeeId, int $userId, ?int $checklistId = null): int
    {
        if (! $this->tablesReady()) {
            throw new \RuntimeException('Onboarding tables missing.');
        }

        $emp = (new EmployeeService($this->db))->find($employeeId);
        if (! $emp) {
            throw new \RuntimeException('Employee not found.');
        }

        if (! $checklistId) {
            $checklist = $this->db->table('hr_onboarding_checklists')
                ->where('code', 'DEFAULT')
                ->where('company_id', null)
                ->get()->getRowArray();
            $checklistId = $checklist ? (int) $checklist['id'] : null;
        }

        if (! $checklistId) {
            throw new \RuntimeException('No onboarding checklist configured.');
        }

        helper('fm');
        $payload = [
            'employee_id'          => $employeeId,
            'checklist_id'         => $checklistId,
            'employment_period_id' => $emp['current_employment_period_id'] ?? null,
            'status'               => 'in_progress',
            'started_at'           => date('Y-m-d H:i:s'),
            'created_by'           => $userId,
        ];

        if (function_exists('fm_insert_row_id')) {
            $instanceId = fm_insert_row_id($this->db, 'hr_onboarding_instances', $payload);
        } else {
            $this->db->table('hr_onboarding_instances')->insert($payload);
            $instanceId = (int) $this->db->insertID();
        }

        $tasks = $this->db->table('hr_onboarding_tasks')->where('checklist_id', $checklistId)->where('is_active', 1)->orderBy('sort_order')->get()->getResultArray();
        foreach ($tasks as $task) {
            $this->db->table('hr_onboarding_task_status')->insert([
                'instance_id' => $instanceId,
                'task_id'     => (int) $task['id'],
                'status'      => 'pending',
            ]);
        }

        (new EmployeeLifecycleService($this->db))->transitionStatus($employeeId, 'pre_joining', ['reason' => 'Onboarding started'], $userId);

        $this->timeline->record($employeeId, 'onboarding', 'Onboarding started', 'started', null, 'onboarding', $instanceId, null, $userId);

        return $instanceId;
    }

    public function completeTask(int $taskStatusId, int $userId, ?string $notes = null): bool
    {
        $row = $this->db->table('hr_onboarding_task_status')->where('id', $taskStatusId)->get()->getRowArray();
        if (! $row) {
            return false;
        }

        $this->db->table('hr_onboarding_task_status')->where('id', $taskStatusId)->update([
            'status'       => 'done',
            'completed_by' => $userId,
            'completed_at' => date('Y-m-d H:i:s'),
            'notes'        => $notes,
        ]);

        $this->maybeCompleteInstance((int) $row['instance_id'], $userId);

        return true;
    }

    private function maybeCompleteInstance(int $instanceId, int $userId): void
    {
        $pending = $this->db->table('hr_onboarding_task_status ts')
            ->join('hr_onboarding_tasks t', 't.id = ts.task_id')
            ->where('ts.instance_id', $instanceId)
            ->where('ts.status', 'pending')
            ->where('t.is_mandatory', 1)
            ->countAllResults();

        if ($pending > 0) {
            return;
        }

        $instance = $this->db->table('hr_onboarding_instances')->where('id', $instanceId)->get()->getRowArray();
        if (! $instance) {
            return;
        }

        $this->db->table('hr_onboarding_instances')->where('id', $instanceId)->update([
            'status'       => 'completed',
            'completed_at' => date('Y-m-d H:i:s'),
        ]);

        (new EmployeeLifecycleService($this->db))->transitionStatus((int) $instance['employee_id'], 'active', ['reason' => 'Onboarding complete'], $userId);
        $this->timeline->record((int) $instance['employee_id'], 'onboarding', 'Onboarding completed', 'completed', null, 'onboarding', $instanceId, null, $userId);
    }
}
