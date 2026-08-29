<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Helpdesk → WO → Job execution → QA → Client approval → Invoice closure.
 */
class WorkOrderWorkflowService
{
    public function __construct(
        private BaseConnection $db,
        private array $settings = []
    ) {
    }

    public function supportsExtendedWorkflow(): bool
    {
        return $this->db->fieldExists('qa_status', 'work_orders');
    }

    public function onStatusChanged(int $woId, string $newStatus): void
    {
        if (!$this->supportsExtendedWorkflow() || $newStatus !== 'completed') {
            return;
        }

        $wf = new WorkflowSettingsService($this->db);
        if (!$wf->enabled('wf_require_qa_on_complete')) {
            return;
        }

        $wo = $this->db->table('work_orders')->where('id', $woId)->get()->getRowArray();
        if (!$wo || $wo['qa_status'] !== 'none') {
            return;
        }

        $this->db->table('work_orders')->where('id', $woId)->update([
            'qa_status'               => 'pending',
            'client_approval_status'  => 'none',
        ]);

        $this->recordApproval($woId, 'qa', 'pending', 'Work marked completed — awaiting QA', 0);
    }

    public function qaApprove(int $woId, int $userId, ?string $notes = null): void
    {
        $wo = $this->requireWo($woId);
        if (!$this->supportsExtendedWorkflow()) {
            throw new \RuntimeException('Run database migration or SQL patch to enable workflow.');
        }
        if (($wo['qa_status'] ?? 'none') !== 'pending') {
            throw new \RuntimeException('Work order is not pending QA.');
        }

        $completedAt = $wo['completed_at'] ?? date('Y-m-d H:i:s');
        $wf = new WorkflowSettingsService($this->db);
        $requireClient = $wf->enabled('wf_require_client_approval');
        $this->db->table('work_orders')->where('id', $woId)->update([
            'qa_status'              => 'approved',
            'qa_approved_by'         => $userId,
            'qa_approved_at'         => date('Y-m-d H:i:s'),
            'client_approval_status' => $requireClient ? 'pending' : 'none',
            'workflow_stage'         => $wf->enabled('wf_auto_invoice_on_client_approve') ? 'job_completed' : 'invoice_prep',
            'status'                 => 'completed',
            'completed_at'           => $completedAt,
        ]);

        $this->clearSummaryCache($woId);
        $this->recordApproval($woId, 'qa', 'approved', $notes, $userId);
    }

    public function qaReject(int $woId, int $userId, ?string $notes = null): void
    {
        $wo = $this->requireWo($woId);
        if ($wo['qa_status'] !== 'pending') {
            throw new \RuntimeException('Work order is not pending QA.');
        }

        $this->db->table('work_orders')->where('id', $woId)->update([
            'qa_status'      => 'rejected',
            'qa_approved_by' => $userId,
            'qa_approved_at' => date('Y-m-d H:i:s'),
            'status'         => 'in_progress',
            'workflow_stage' => 'work_execution',
        ]);

        $this->clearSummaryCache($woId);
        $this->recordApproval($woId, 'qa', 'rejected', $notes, $userId);
    }

    /**
     * @return array{invoice_id: int, invoice_number: string}
     */
    public function clientApprove(int $woId, int $userId, ?string $notes = null, ?string $signaturePath = null): array
    {
        $wo = $this->requireWo($woId);
        if (!$this->supportsExtendedWorkflow()) {
            throw new \RuntimeException('Run database migration or SQL patch to enable workflow.');
        }
        if ($wo['qa_status'] !== 'approved' || $wo['client_approval_status'] !== 'pending') {
            throw new \RuntimeException('Client approval requires QA-approved work order.');
        }

        $wf = new WorkflowSettingsService($this->db);
        $update = [
            'client_approval_status' => 'approved',
            'client_approved_by'     => $userId,
            'client_approved_at'     => date('Y-m-d H:i:s'),
        ];

        $invoice = ['invoice_id' => 0, 'invoice_number' => '', 'subtotal' => 0, 'total' => 0];

        if ($wf->enabled('wf_auto_invoice_on_client_approve')) {
            $invoice = (new InvoiceFromWorkOrderService($this->db, $this->settings))
                ->createDraftFromWorkOrder($woId, $userId);
            $update['invoice_id']     = $invoice['invoice_id'];
            $update['workflow_stage'] = 'wo_closed';
            $update['status']         = 'closed';
        } else {
            $update['workflow_stage'] = 'invoice_prep';
        }

        $this->db->table('work_orders')->where('id', $woId)->update($update);

        $this->clearSummaryCache($woId);
        $this->recordApproval($woId, 'client', 'approved', $notes, $userId, $signaturePath);

        return $invoice;
    }

    public function clientReject(int $woId, int $userId, ?string $notes = null): void
    {
        $wo = $this->requireWo($woId);
        if ($wo['client_approval_status'] !== 'pending') {
            throw new \RuntimeException('Work order is not pending client approval.');
        }

        $this->db->table('work_orders')->where('id', $woId)->update([
            'client_approval_status' => 'rejected',
            'client_approved_by'     => $userId,
            'client_approved_at'     => date('Y-m-d H:i:s'),
        ]);

        $this->clearSummaryCache($woId);
        $this->recordApproval($woId, 'client', 'rejected', $notes, $userId);
    }

    /** @return array<string, mixed> */
    /** @var array<int, array<string, mixed>> */
    private static array $summaryCache = [];

    public function workflowSummary(int $woId): array
    {
        if (isset(self::$summaryCache[$woId])) {
            return self::$summaryCache[$woId];
        }
        $wo = $this->requireWo($woId);
        if (!$this->supportsExtendedWorkflow()) {
            $r = ['enabled' => false];
            self::$summaryCache[$woId] = $r;
            return $r;
        }

        $invoice = null;
        if (!empty($wo['invoice_id'])) {
            $invoice = $this->db->table('invoices')->where('id', $wo['invoice_id'])->get()->getRowArray();
        }

        $result = [
            'enabled'                 => true,
            'qa_status'               => $wo['qa_status'] ?? 'none',
            'client_approval_status'  => $wo['client_approval_status'] ?? 'none',
            'invoice'                 => $invoice,
        ];
        self::$summaryCache[$woId] = $result;
        return $result;
    }

    private function clearSummaryCache(int $woId): void
    {
        unset(self::$summaryCache[$woId]);
    }

    private function requireWo(int $woId): array
    {
        $wo = $this->db->table('work_orders')->where('id', $woId)->get()->getRowArray();
        if (!$wo) {
            throw new \RuntimeException('Work order not found.');
        }

        return $wo;
    }

    private function recordApproval(int $woId, string $type, string $action, ?string $notes, int $userId, ?string $signaturePath = null): void
    {
        if (!$this->db->tableExists('wo_approvals')) {
            return;
        }

        $map = ['qa' => 'completion', 'client' => 'completion', 'pending' => 'supervisor'];
        $approvalType = $map[$type] ?? 'completion';
        $dbAction     = in_array($action, ['approved', 'rejected'], true) ? $action : 'approved';

        if ($userId > 0) {
            $row = [
                'wo_id'          => $woId,
                'approval_type'  => $approvalType,
                'action'         => $dbAction,
                'notes'          => trim(($type . ': ' . ($notes ?? $action))),
                'actioned_by'    => $userId,
            ];
            if ($signaturePath && $this->db->fieldExists('signature_path', 'wo_approvals')) {
                $row['signature_path'] = $signaturePath;
            }
            $this->db->table('wo_approvals')->insert($row);
        }
    }
}
