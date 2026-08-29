<?php

namespace App\Models;

use CodeIgniter\Model;

class EstimationModel extends Model
{
  protected $table            = 'estimations';
  protected $primaryKey       = 'id';
  protected $useAutoIncrement = true;
  protected $returnType       = 'array';
  protected $allowedFields    = [
    'est_number', 'facility_id', 'wo_id', 'title', 'description',
    'labor_cost', 'material_cost', 'other_cost',
    'actual_labor_cost', 'actual_material_cost', 'actual_transport_cost',
    'actual_equipment_cost', 'actual_misc_cost', 'actual_other_cost',
    'actual_total', 'actual_total_cost',
    'selling_subtotal', 'estimated_subtotal', 'actual_subtotal',
    'total_profit', 'total_margin', 'cost_variance',
    'subtotal', 'vat_rate', 'vat_amount', 'total',
    'status', 'approved_by', 'approved_at', 'revision', 'notes', 'created_by',
  ];
  protected $useTimestamps = true;
  protected $createdField  = 'created_at';
  protected $updatedField  = 'updated_at';
}
