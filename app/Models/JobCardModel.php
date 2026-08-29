<?php

namespace App\Models;

use CodeIgniter\Model;

class JobCardModel extends Model
{
    protected $table          = 'job_cards';
    protected $primaryKey     = 'id';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';

    protected $allowedFields = [
        'jc_number', 'wo_id', 'supervisor_id', 'assigned_to',
        'description', 'status',
        'scheduled_date', 'scheduled_hours',
        'labor_hours', 'completion_notes', 'technician_notes', 'qa_notes',
        'before_image', 'after_image', 'customer_signature',
        'approved_by', 'approved_at', 'completed_at', 'created_by',
    ];

    protected $validationRules = [
        'wo_id'       => 'required|is_natural_no_zero',
        'assigned_to' => 'required|is_natural_no_zero',
        'description' => 'required|min_length[5]',
    ];

    // -------------------------------------------------------
    // Role-based scoping
    // -------------------------------------------------------

    public function scopeForUser(array $user): self
    {
        $role = $user['role_name'] ?? '';

        switch ($role) {
            case 'super_admin':
            case 'facility_manager':
                // No restriction — facility_manager check happens through WO
                break;

            case 'supervisor':
                $this->where('job_cards.supervisor_id', $user['id']);
                break;

            case 'technician':
                $this->where('job_cards.assigned_to', $user['id']);
                break;

            default:
                $this->where('1', '0');
        }

        return $this;
    }

    // -------------------------------------------------------
    // Number generation
    // -------------------------------------------------------

    public function generateJcNumber(): string
    {
        $year   = date('Y');
        $prefix = 'JC-' . $year . '-';
        $last   = $this->db->table('job_cards')
                           ->like('jc_number', $prefix, 'after')
                           ->orderBy('id', 'DESC')
                           ->limit(1)
                           ->get()->getRow();

        $seq = 1;
        if ($last) {
            $parts = explode('-', $last->jc_number);
            $seq   = ((int) end($parts)) + 1;
        }
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    // -------------------------------------------------------
    // Detail with joins
    // -------------------------------------------------------

    public function getDetail(int $id): ?array
    {
        return $this->db->table('job_cards jc')
            ->select('jc.*,
                      wo.wo_number, wo.title AS wo_title, wo.priority,
                      wo.facility_id, f.name AS facility_name,
                      u1.name AS technician_name,
                      u2.name AS supervisor_name,
                      u3.name AS created_by_name,
                      u4.name AS approved_by_name')
            ->join('work_orders wo', 'wo.id = jc.wo_id', 'left')
            ->join('facilities f',   'f.id = wo.facility_id', 'left')
            ->join('users u1',       'u1.id = jc.assigned_to', 'left')
            ->join('users u2',       'u2.id = jc.supervisor_id', 'left')
            ->join('users u3',       'u3.id = jc.created_by', 'left')
            ->join('users u4',       'u4.id = jc.approved_by', 'left')
            ->where('jc.id', $id)
            ->where('jc.deleted_at', null)
            ->get()->getRowArray();
    }

    public function getListForWo(int $woId): array
    {
        return $this->db->table('job_cards jc')
            ->select('jc.*, u1.name AS technician_name, u2.name AS supervisor_name')
            ->join('users u1', 'u1.id = jc.assigned_to', 'left')
            ->join('users u2', 'u2.id = jc.supervisor_id', 'left')
            ->where('jc.wo_id', $woId)
            ->where('jc.deleted_at', null)
            ->orderBy('jc.created_at', 'ASC')
            ->get()->getResultArray();
    }

    public function getMaterialsForCard(int $jcId): array
    {
        return $this->db->table('jc_materials')
            ->where('jc_id', $jcId)
            ->get()->getResultArray();
    }

    public function getAttachmentsForCard(int $jcId): array
    {
        return $this->db->table('jc_attachments')
            ->where('jc_id', $jcId)
            ->get()->getResultArray();
    }
}
