<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Enforces valid work order status transitions and closure workflow gates.
 */
class WorkflowGuardService
{
    /** @var array<string, list<string>> */
    private const ALLOWED_STATUS = [
        'new'         => ['assigned', 'in_progress', 'cancelled'],
        'assigned'    => ['in_progress', 'on_hold', 'cancelled'],
        'in_progress' => ['on_hold', 'completed', 'cancelled'],
        'on_hold'     => ['in_progress', 'cancelled'],
        'completed'   => ['closed', 'in_progress'],
        'closed'      => [],
        'cancelled'   => [],
    ];

    private WorkflowSettingsService $wf;

    public function __construct(private BaseConnection $db)
    {
        $this->wf = new WorkflowSettingsService($db);
    }

    public function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::ALLOWED_STATUS[$from] ?? [], true);
    }

    public function assertTransition(string $from, string $to): void
    {
        if (!$this->canTransition($from, $to)) {
            throw new \RuntimeException("Invalid status transition: {$from} → {$to}");
        }
    }

    public function assertCanComplete(array $wo): void
    {
        if (!$this->db->fieldExists('qa_status', 'work_orders')) {
            return;
        }

        if ($this->wf->enabled('wf_require_supervisor_approval')
            && ($wo['approval_status'] ?? 'approved') === 'pending') {
            throw new \RuntimeException('Work order requires budget/supervisor approval before completion.');
        }

        if (!$this->wf->enabled('wf_require_labor_or_material')) {
            return;
        }

        $hasLabor = $this->db->table('wo_labor')->where('wo_id', $wo['id'])->countAllResults() > 0;
        $hasMat   = $this->db->table('wo_materials')->where('wo_id', $wo['id'])->countAllResults() > 0;
        if (!$hasLabor && !$hasMat && empty($wo['actual_cost'])) {
            throw new \RuntimeException('Add labor, materials, or actual cost before marking completed.');
        }
    }

    public function assertCanClose(array $wo): void
    {
        if (!$this->db->fieldExists('client_approval_status', 'work_orders')) {
            return;
        }

        if ($this->wf->enabled('wf_require_qa_on_complete') && ($wo['qa_status'] ?? 'none') !== 'approved') {
            throw new \RuntimeException('QA approval required before closing.');
        }
        if ($this->wf->enabled('wf_require_client_approval') && ($wo['client_approval_status'] ?? 'none') !== 'approved') {
            throw new \RuntimeException('Client approval required before closing.');
        }
        if ($this->wf->enabled('wf_require_invoice_before_close') && empty($wo['invoice_id'])) {
            throw new \RuntimeException('Invoice must be generated before closing.');
        }
    }
}
