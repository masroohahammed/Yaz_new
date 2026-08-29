<?php

namespace App\Models;

use CodeIgniter\Model;

class EstimationItemModel extends Model
{
  protected $table            = 'estimation_items';
  protected $primaryKey       = 'id';
  protected $useAutoIncrement = true;
  protected $returnType       = 'array';
  protected $allowedFields    = [
    'est_id', 'type', 'item_name', 'description', 'quantity', 'unit',
    'unit_price', 'estimated_unit_cost', 'actual_unit_cost',
    'unit_cost', 'total_cost', 'line_total', 'estimated_total', 'actual_total',
    'profit', 'margin_percent', 'variance',
    'actual_used_qty', 'wastage_qty', 'wastage_cost', 'sort_order',
  ];
  protected $useTimestamps = true;
  protected $createdField  = 'created_at';
  protected $updatedField  = 'updated_at';
}
