<?php

namespace App\Models;

use CodeIgniter\Model;

class SlaRuleModel extends Model
{
    protected $table      = 'sla_rules';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'priority', 'response_hours', 'resolution_hours', 'escalation_hours',
    ];

    public function getForPriority(string $priority): ?array
    {
        return $this->where('priority', $priority)->first();
    }
}
