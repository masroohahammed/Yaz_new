<?php

namespace App\Models;

use CodeIgniter\Model;

class HelpdeskModel extends Model
{
    protected $table         = 'maintenance_requests';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'ticket_number', 'facility_id', 'unit_id', 'asset_id', 'scan_source', 'company_id',
        'requester_name', 'requester_email', 'requester_phone',
        'category', 'description', 'priority', 'status',
        'image_path',
        'reviewed_by', 'reviewed_at',
        'verified_by', 'verified_at', 'verification_notes',
        'approval_status', 'approved_by', 'approved_at', 'rejection_reason',
        'converted_to_wo',
    ];

    protected $validationRules = [
        'requester_name' => 'required|min_length[2]|max_length[150]',
        'description'    => 'required|min_length[10]',
        'priority'       => 'required|in_list[critical,high,medium,low]',
        'facility_id'    => 'permit_empty|is_natural_no_zero',
        'unit_id'        => 'permit_empty|is_natural_no_zero',
    ];

    // -------------------------------------------------------
    // Role scoping
    // -------------------------------------------------------

    public function scopeForUser(array $user): self
    {
        $role = $user['role_name'] ?? '';

        switch ($role) {
            case 'super_admin':
                break;

            case 'facility_manager':
                $ids = $this->getManagedFacilityIds($user['id']);
                $ids ? $this->whereIn('maintenance_requests.facility_id', $ids)
                     : $this->where('1', '0');
                break;

            case 'supervisor':
                // Supervisors see pending / verified complaints for their facility
                $ids = $this->getSupervisorFacilityIds($user['id']);
                $ids ? $this->whereIn('maintenance_requests.facility_id', $ids)
                     : $this->where('1', '0');
                break;

            case 'client':
                // Clients see only their own submissions
                $this->where('maintenance_requests.requester_email', $user['email']);
                break;

            default:
                $this->where('1', '0');
        }

        return $this;
    }

    // -------------------------------------------------------
    // Number generation
    // -------------------------------------------------------

    public function generateTicketNumber(): string
    {
        $year   = date('Y');
        $prefix = 'MR-' . $year . '-';
        $last   = $this->db->table('maintenance_requests')
                           ->like('ticket_number', $prefix, 'after')
                           ->orderBy('id', 'DESC')
                           ->limit(1)
                           ->get()->getRow();

        $seq = 1;
        if ($last) {
            $parts = explode('-', $last->ticket_number);
            $seq   = ((int) end($parts)) + 1;
        }
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    // -------------------------------------------------------
    // Detail fetch
    // -------------------------------------------------------

    public function getDetail(int $id): ?array
    {
        return $this->db->table('maintenance_requests mr')
            ->select('mr.*,
                      f.name  AS facility_name,
                      u1.name AS reviewed_by_name,
                      u2.name AS verified_by_name,
                      u3.name AS approved_by_name,
                      wo.wo_number AS converted_wo_number')
            ->join('facilities f',    'f.id = mr.facility_id', 'left')
            ->join('users u1',        'u1.id = mr.reviewed_by', 'left')
            ->join('users u2',        'u2.id = mr.verified_by', 'left')
            ->join('users u3',        'u3.id = mr.approved_by', 'left')
            ->join('work_orders wo',  'wo.id = mr.converted_to_wo', 'left')
            ->where('mr.id', $id)
            ->get()->getRowArray();
    }

    // -------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------

    private function getManagedFacilityIds(int $userId): array
    {
        return array_column(
            $this->db->table('facilities')->select('id')
                     ->where('manager_id', $userId)->where('deleted_at', null)
                     ->get()->getResultArray(),
            'id'
        );
    }

    private function getSupervisorFacilityIds(int $userId): array
    {
        // Supervisors are linked to facilities through their work orders
        return array_unique(array_column(
            $this->db->table('work_orders')->select('facility_id')
                     ->where('supervisor_id', $userId)->where('deleted_at', null)
                     ->get()->getResultArray(),
            'facility_id'
        ));
    }
}
