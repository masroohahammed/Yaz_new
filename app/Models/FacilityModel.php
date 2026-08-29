<?php

namespace App\Models;

use CodeIgniter\Model;

class FacilityModel extends Model
{
    protected $table          = 'facilities';
    protected $primaryKey     = 'id';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';

    protected $allowedFields = [
        'name', 'code', 'address', 'city', 'country',
        'manager_id', 'area_sqm', 'floors', 'status',
        'latitude', 'longitude', 'company_id', 'total_units',
    ];

    /**
     * Role-based scope: limits which facilities a user can see.
     */
    public function scopeForUser(array $user): self
    {
        $role = $user['role_name'] ?? '';

        switch ($role) {
            case 'super_admin':
                break;

            case 'facility_manager':
                $this->where('facilities.manager_id', $user['id']);
                break;

            case 'supervisor':
            case 'technician':
                // Linked through work orders
                $facilityIds = array_unique(array_column(
                    $this->db->table('work_orders')
                             ->select('facility_id')
                             ->where('supervisor_id', $user['id'])
                             ->orWhere('assigned_to', $user['id'])
                             ->where('deleted_at', null)
                             ->get()->getResultArray(),
                    'facility_id'
                ));
                $facilityIds ? $this->whereIn('facilities.id', $facilityIds)
                             : $this->where('1', '0');
                break;

            default:
                if (! empty($user['company_id'])) {
                    $this->where('facilities.company_id', $user['company_id']);
                }
        }

        return $this;
    }

    /**
     * Get all facilities with company and manager name.
     */
    public function getAllWithDetails(): array
    {
        return $this->db->table('facilities f')
            ->select('f.*, c.name AS company_name, u.name AS manager_name')
            ->join('companies c', 'c.id = f.company_id', 'left')
            ->join('users u',     'u.id = f.manager_id', 'left')
            ->where('f.deleted_at', null)
            ->orderBy('f.name', 'ASC')
            ->get()->getResultArray();
    }
}
