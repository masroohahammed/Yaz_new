<?php

namespace App\Models;

use CodeIgniter\Model;

class WorkOrderModel extends Model
{
    protected $table          = 'work_orders';
    protected $primaryKey     = 'id';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';

    protected $allowedFields = [
        'company_id', 'wo_number', 'facility_id', 'asset_id', 'unit_id',
        'title', 'description', 'type', 'category', 'priority',
        'status', 'workflow_stage',
        'assigned_to', 'supervisor_id', 'vendor_id', 'created_by',
        'sla_due', 'planned_start', 'planned_end', 'sla_breached',
        'started_at', 'completed_at',
        'estimated_cost', 'actual_cost', 'completion_notes',
        'requester_name', 'requester_phone', 'requester_email',
        'approval_status', 'approved_by', 'approved_at',
        'verified_by', 'verified_at',
        'qa_status', 'qa_approved_by', 'qa_approved_at',
        'client_approval_status', 'client_approved_by', 'client_approved_at',
        'invoice_id',
    ];

    protected $validationRules = [
        'facility_id' => 'required|is_natural_no_zero',
        'title'       => 'required|min_length[3]|max_length[255]',
        'type'        => 'required|in_list[corrective,preventive,predictive,breakdown,inspection,emergency,project]',
        'priority'    => 'required|in_list[critical,high,medium,low]',
    ];

    // -------------------------------------------------------
    // Scoping helpers — used only with findAll() / Model queries
    // For joined list queries use getListWithRelations($filters, $perPage, $user)
    // -------------------------------------------------------

    public function scopeForUser(array $user): self
    {
        $role = $user['role_name'] ?? '';

        switch ($role) {
            case 'super_admin':
                break;

            case 'facility_manager':
                $ids = $this->getManagedFacilityIds($user['id']);
                $ids ? $this->whereIn('facility_id', $ids)
                     : $this->where('1', '0');
                break;

            case 'supervisor':
                $this->where('supervisor_id', $user['id']);
                break;

            case 'technician':
                $this->where('assigned_to', $user['id']);
                break;

            case 'client':
                $mrIds = $this->getClientRequestIds($user['id']);
                if ($mrIds) {
                    $sub   = $this->db->table('maintenance_requests')
                                      ->select('converted_to_wo')
                                      ->whereIn('id', $mrIds)
                                      ->whereNotNull('converted_to_wo')
                                      ->get()->getResultArray();
                    $woIds = array_column($sub, 'converted_to_wo');
                    $woIds ? $this->whereIn('id', $woIds) : $this->where('1', '0');
                } else {
                    $this->where('1', '0');
                }
                break;

            case 'finance_manager':
            case 'finance_user':
            case 'procurement_officer':
                if (! empty($user['company_id'])) {
                    $this->where('company_id', $user['company_id']);
                }
                break;
        }

        return $this;
    }

    // -------------------------------------------------------
    // Workflow stage machine
    // -------------------------------------------------------

    /**
     * Advance the workflow stage.
     *
     * @param  int    $id    Work order ID
     * @param  string $stage Target stage slug
     * @param  array  $extra Additional column updates
     */
    public function advanceStage(int $id, string $stage, array $extra = []): bool
    {
        $stageToStatus = [
            'complaint_received'     => 'new',
            'complaint_verification' => 'new',
            'approval_process'       => 'new',
            'converted_to_wo'        => 'new',
            'assigned_to_supervisor' => 'assigned',
            'job_card_created'       => 'assigned',
            'technician_assigned'    => 'assigned',
            'planning_scheduling'    => 'assigned',
            'work_execution'         => 'in_progress',
            'inspection_qc'          => 'in_progress',
            'job_completed'          => 'completed',
            'wo_closed'              => 'closed',
        ];

        return $this->update($id, array_merge([
            'workflow_stage' => $stage,
            'status'         => $stageToStatus[$stage] ?? 'new',
        ], $extra));
    }

    // -------------------------------------------------------
    // Number generation
    // -------------------------------------------------------

    public function generateWoNumber(): string
    {
        $prefix = 'WO-' . date('Y') . '-';
        $last   = $this->db->table('work_orders')
                           ->like('wo_number', $prefix, 'after')
                           ->orderBy('id', 'DESC')
                           ->limit(1)->get()->getRow();

        $seq = 1;
        if ($last && ! empty($last->wo_number)) {
            helper('fm');
            $seq = fm_sequence_from_code($last->wo_number) + 1;
        }
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    // -------------------------------------------------------
    // Detail fetch with joins
    // -------------------------------------------------------

    public function getDetail(int $id): ?array
    {
        return $this->db->table('work_orders wo')
            ->select('wo.*,
                      f.name  AS facility_name,
                      f.code  AS facility_code,
                      a.name  AS asset_name,
                      a.asset_code,
                      a.brand   AS asset_brand,
                      a.model   AS asset_model,
                      a.warranty_expiry AS asset_warranty_expiry,
                      a.next_maintenance AS asset_next_maintenance,
                      a.last_maintenance AS asset_last_maintenance,
                      a.location_in_facility AS asset_location,
                      u1.name AS assigned_name,
                      u2.name AS supervisor_name,
                      u3.name AS created_by_name,
                      u4.name AS approved_by_name,
                      u5.name AS qa_approved_by_name,
                      c.name  AS company_name')
            ->join('facilities f', 'f.id = wo.facility_id',    'left')
            ->join('assets a',     'a.id = wo.asset_id',       'left')
            ->join('users u1',     'u1.id = wo.assigned_to',   'left')
            ->join('users u2',     'u2.id = wo.supervisor_id', 'left')
            ->join('users u3',     'u3.id = wo.created_by',    'left')
            ->join('users u4',     'u4.id = wo.approved_by',   'left')
            ->join('users u5',     'u5.id = wo.qa_approved_by','left')
            ->join('companies c',  'c.id = wo.company_id',     'left')
            ->where('wo.id', $id)
            ->where('wo.deleted_at', null)
            ->get()->getRowArray();
    }

    // -------------------------------------------------------
    // List with joins — accepts $user for role-based scoping
    // -------------------------------------------------------

    /**
     * @param  array      $filters  URL query params (status, priority, search, etc.)
     * @param  int        $perPage  Rows per page
     * @param  array|null $user     Result of BaseController::currentUser(); null = no scope
     */
    public function getListWithRelations(array $filters = [], int $perPage = 20, ?array $user = null): array
    {
        $builder = $this->db->table('work_orders wo')
            ->select('wo.id, wo.wo_number, wo.title, wo.type, wo.category, wo.priority,
                      wo.status, wo.workflow_stage, wo.approval_status, wo.created_at, wo.sla_due,
                      wo.sla_breached, wo.planned_start, wo.planned_end,
                      f.name  AS facility_name,
                      a.name  AS asset_name,
                      u1.name AS assigned_name,
                      u2.name AS supervisor_name,
                      u3.name AS created_by_name')
            ->join('facilities f', 'f.id = wo.facility_id',    'left')
            ->join('assets a',     'a.id = wo.asset_id',       'left')
            ->join('users u1',     'u1.id = wo.assigned_to',   'left')
            ->join('users u2',     'u2.id = wo.supervisor_id', 'left')
            ->join('users u3',     'u3.id = wo.created_by',    'left')
            ->where('wo.deleted_at', null)
            ->orderBy('wo.created_at', 'DESC');

        // ---- Role-based scoping (applied directly to this builder) ----
        if ($user) {
            $role = $user['role_name'] ?? '';
            switch ($role) {
                case 'super_admin':
                    break;

                case 'facility_manager':
                    $ids = $this->getManagedFacilityIds($user['id']);
                    $ids ? $builder->whereIn('wo.facility_id', $ids)
                         : $builder->where('1', '0');
                    break;

                case 'supervisor':
                    $builder->where('wo.supervisor_id', $user['id']);
                    break;

                case 'technician':
                    $builder->where('wo.assigned_to', $user['id']);
                    break;

                case 'client':
                    $mrIds = $this->getClientRequestIds($user['id']);
                    if ($mrIds) {
                        $sub   = $this->db->table('maintenance_requests')
                                          ->select('converted_to_wo')
                                          ->whereIn('id', $mrIds)
                                          ->whereNotNull('converted_to_wo')
                                          ->get()->getResultArray();
                        $woIds = array_column($sub, 'converted_to_wo');
                        $woIds ? $builder->whereIn('wo.id', $woIds)
                               : $builder->where('1', '0');
                    } else {
                        $builder->where('1', '0');
                    }
                    break;

                case 'finance_manager':
                case 'finance_user':
                case 'procurement_officer':
                    if (! empty($user['company_id'])) {
                        $builder->where('wo.company_id', $user['company_id']);
                    }
                    break;
            }
        }

        // ---- Search / filter conditions ----
        if (! empty($filters['status']))
            $builder->where('wo.status', $filters['status']);
        if (! empty($filters['workflow_stage']))
            $builder->where('wo.workflow_stage', $filters['workflow_stage']);
        if (! empty($filters['priority']))
            $builder->where('wo.priority', $filters['priority']);
        if (! empty($filters['facility_id']))
            $builder->where('wo.facility_id', (int) $filters['facility_id']);
        if (! empty($filters['unit_id']))
            $builder->where('wo.unit_id', (int) $filters['unit_id']);
        if (! empty($filters['asset_id']))
            $builder->where('wo.asset_id', (int) $filters['asset_id']);
        if (! empty($filters['search']))
            $builder->groupStart()
                    ->like('wo.title', $filters['search'])
                    ->orLike('wo.wo_number', $filters['search'])
                    ->groupEnd();
        if (! empty($filters['wo_ids']))
            $builder->whereIn('wo.id', $filters['wo_ids']);

        // ---- Pagination ----
        $page       = max(1, (int) ($filters['page'] ?? 1));
        $totalCount = (clone $builder)->countAllResults(false);
        $results    = $builder->limit($perPage, ($page - 1) * $perPage)
                              ->get()->getResultArray();

        return ['data' => $results, 'total' => $totalCount, 'page' => $page, 'perPage' => $perPage];
    }

    // -------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------

    private function getManagedFacilityIds(int $userId): array
    {
        return array_column(
            $this->db->table('facilities')
                     ->select('id')
                     ->where('manager_id', $userId)
                     ->where('deleted_at', null)
                     ->get()->getResultArray(),
            'id'
        );
    }

    private function getClientRequestIds(int $userId): array
    {
        return array_column(
            $this->db->table('maintenance_requests')
                     ->select('id')
                     ->where('created_by', $userId)
                     ->get()->getResultArray(),
            'id'
        );
    }
}
