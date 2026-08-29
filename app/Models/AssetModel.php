<?php

namespace App\Models;

use CodeIgniter\Model;

class AssetModel extends Model
{
    protected $table          = 'assets';
    protected $primaryKey     = 'id';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';

    protected $allowedFields = [
        'facility_id', 'company_id',
        'name', 'asset_code', 'tag_number', 'category', 'asset_type',
        'brand', 'manufacturer', 'model', 'serial_number',
        'purchase_date', 'warranty_start', 'warranty_expiry', 'amc_expiry',
        'purchase_cost', 'location_in_facility', 'floor_room',
        'department', 'cost_center', 'assigned_to', 'criticality',
        'status', 'health_score', 'last_maintenance', 'next_maintenance',
        'qr_token', 'barcode_value', 'qr_generated_at', 'notes',
    ];

    protected $validationRules = [
        'facility_id' => 'required|is_natural_no_zero',
        'name'        => 'required|min_length[2]|max_length[200]',
        'asset_code'  => 'required|max_length[50]',
    ];

    public function getAllWithFacility(): array
    {
        return $this->db->table('assets a')
            ->select('a.*, f.name AS facility_name, f.code AS facility_code')
            ->join('facilities f', 'f.id = a.facility_id', 'left')
            ->where('a.deleted_at', null)
            ->orderBy('a.name', 'ASC')
            ->get()->getResultArray();
    }

    public function getByFacility(int $facilityId): array
    {
        return $this->where('facility_id', $facilityId)
                    ->where('deleted_at', null)
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }

    public function generateCode(): string
    {
        $last = $this->db->table('assets')
                         ->orderBy('id', 'DESC')
                         ->limit(1)
                         ->get()->getRow();
        $seq  = $last ? ((int) preg_replace('/\D/', '', $last->asset_code)) + 1 : 1;
        return 'AST-' . str_pad($seq, 5, '0', STR_PAD_LEFT);
    }
}
