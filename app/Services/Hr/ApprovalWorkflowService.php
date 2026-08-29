<?php

namespace App\Services\Hr;

use CodeIgniter\Database\BaseConnection;

class ApprovalWorkflowService
{
    private BaseConnection $db;
    private HrAuditService $audit;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db    = $db ?? \Config\Database::connect();
        $this->audit = new HrAuditService($this->db);
    }

    public function tablesReady(): bool
    {
        return $this->db->tableExists('hr_approval_requests')
            && $this->db->tableExists('hr_employee_requests');
    }

    /** @param array<string, mixed> $data */
    public function submitRequest(array $data, int $userId): int
    {
        if (! $this->tablesReady()) {
            throw new \RuntimeException('Approval tables missing.');
        }

        $workflow = $this->resolveWorkflow($data);
        $reqNo    = 'REQ-' . date('Y') . '-' . random_int(10000, 99999);

        helper('fm');
        $reqPayload = [
            'request_number' => $reqNo,
            'company_id'     => $data['company_id'] ?? null,
            'employee_id'    => (int) $data['employee_id'],
            'module'         => $data['module'],
            'request_type'   => $data['request_type'] ?? null,
            'source_table'   => $data['source_table'] ?? null,
            'source_id'      => $data['source_id'] ?? null,
            'title'          => $data['title'] ?? 'HR Request',
            'description'    => $data['description'] ?? null,
            'amount'         => $data['amount'] ?? null,
            'status'         => 'pending',
            'submitted_by'   => $userId,
            'submitted_at'   => date('Y-m-d H:i:s'),
            'current_step_no'=> 1,
        ];

        if (function_exists('fm_insert_row_id')) {
            $employeeRequestId = fm_insert_row_id($this->db, 'hr_employee_requests', $reqPayload);
        } else {
            $this->db->table('hr_employee_requests')->insert($reqPayload);
            $employeeRequestId = (int) $this->db->insertID();
        }

        $this->db->table('hr_approval_requests')->insert([
            'workflow_id'         => $workflow['id'] ?? null,
            'employee_request_id' => $employeeRequestId,
            'module'              => $data['module'],
            'source_table'        => $data['source_table'] ?? null,
            'source_id'           => $data['source_id'] ?? null,
            'status'              => 'pending',
            'current_step_no'     => 1,
            'initiated_by'        => $userId,
        ]);
        $approvalRequestId = (int) $this->db->insertID();

        $this->db->table('hr_employee_requests')->where('id', $employeeRequestId)->update([
            'approval_request_id' => $approvalRequestId,
        ]);

        $this->recordAction($approvalRequestId, 0, 'submitted', $userId, $data['description'] ?? null);

        return $approvalRequestId;
    }

    /** @return list<array<string, mixed>> */
    public function pendingRequests(?int $companyId = null): array
    {
        if (! $this->tablesReady()) {
            return [];
        }

        $q = $this->db->table('hr_employee_requests r')
            ->select('r.*, u.name AS employee_name, e.emp_code')
            ->join('employees e', 'e.id = r.employee_id', 'left')
            ->join('users u', 'u.id = e.user_id', 'left')
            ->whereIn('r.status', ['pending', 'submitted']);

        if ($companyId) {
            $q->where('r.company_id', $companyId);
        }

        return $q->orderBy('r.submitted_at', 'ASC')->get()->getResultArray();
    }

    public function approveStep(int $approvalRequestId, int $userId, ?string $notes = null): bool
    {
        $req = $this->findApprovalRequest($approvalRequestId);
        if (! $req || $req['status'] !== 'pending') {
            return false;
        }

        $steps = $this->stepsForWorkflow((int) ($req['workflow_id'] ?? 0));
        $current = (int) $req['current_step_no'];

        $this->recordAction($approvalRequestId, $current, 'approved', $userId, $notes);

        if ($current >= count($steps)) {
            $this->db->table('hr_approval_requests')->where('id', $approvalRequestId)->update([
                'status'       => 'approved',
                'completed_at' => date('Y-m-d H:i:s'),
            ]);
            if (! empty($req['employee_request_id'])) {
                $this->db->table('hr_employee_requests')->where('id', $req['employee_request_id'])->update(['status' => 'approved']);
            }

            return true;
        }

        $this->db->table('hr_approval_requests')->where('id', $approvalRequestId)->update([
            'current_step_no' => $current + 1,
        ]);

        if (! empty($req['employee_request_id'])) {
            $this->db->table('hr_employee_requests')->where('id', $req['employee_request_id'])->update([
                'current_step_no' => $current + 1,
            ]);
        }

        return true;
    }

    public function rejectRequest(int $approvalRequestId, int $userId, ?string $notes = null): bool
    {
        $req = $this->findApprovalRequest($approvalRequestId);
        if (! $req || $req['status'] !== 'pending') {
            return false;
        }

        $this->recordAction($approvalRequestId, (int) $req['current_step_no'], 'rejected', $userId, $notes);

        $this->db->table('hr_approval_requests')->where('id', $approvalRequestId)->update([
            'status'       => 'rejected',
            'completed_at' => date('Y-m-d H:i:s'),
        ]);

        if (! empty($req['employee_request_id'])) {
            $this->db->table('hr_employee_requests')->where('id', $req['employee_request_id'])->update(['status' => 'rejected']);
        }

        return true;
    }

    /** @param array<string, mixed> $data */
    /** @return array<string, mixed>|null */
    private function resolveWorkflow(array $data): ?array
    {
        if (! $this->db->tableExists('hr_approval_workflows')) {
            return null;
        }

        $q = $this->db->table('hr_approval_workflows')
            ->where('module', $data['module'])
            ->where('is_active', 1)
            ->orderBy('priority', 'DESC');

        if (! empty($data['request_type'])) {
            $q->groupStart()->where('request_type', $data['request_type'])->orWhere('request_type', null)->groupEnd();
        }
        if (! empty($data['company_id'])) {
            $q->groupStart()->where('company_id', $data['company_id'])->orWhere('company_id', null)->groupEnd();
        }

        $row = $q->limit(1)->get()->getRowArray();

        return $row ?: null;
    }

    /** @return list<array<string, mixed>> */
    private function stepsForWorkflow(int $workflowId): array
    {
        if ($workflowId < 1 || ! $this->db->tableExists('hr_approval_steps')) {
            return [];
        }

        return $this->db->table('hr_approval_steps')->where('workflow_id', $workflowId)->orderBy('step_no')->get()->getResultArray();
    }

    public function findApprovalRequest(int $id): ?array
    {
        $row = $this->db->table('hr_approval_requests')->where('id', $id)->get()->getRowArray();

        return $row ?: null;
    }

    private function recordAction(int $approvalRequestId, int $stepNo, string $action, int $userId, ?string $notes): void
    {
        if (! $this->db->tableExists('hr_approval_actions')) {
            return;
        }

        $this->db->table('hr_approval_actions')->insert([
            'approval_request_id' => $approvalRequestId,
            'step_no'             => $stepNo,
            'action'              => $action,
            'notes'               => $notes,
            'actioned_by'         => $userId,
        ]);

        $this->audit->log('approvals', $action, 'approval_request', $approvalRequestId, $userId, null, null, $notes);
    }
}
