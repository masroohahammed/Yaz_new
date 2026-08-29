<?php

namespace App\Services\Hr;

use CodeIgniter\Database\BaseConnection;

class HrAuditService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? \Config\Database::connect();
    }

    public function tablesReady(): bool
    {
        return $this->db->tableExists('hr_audit_logs');
    }

    /** @param array<string, mixed>|null $old */
    /** @param array<string, mixed>|null $new */
    public function log(
        string $module,
        string $action,
        ?string $entityType,
        ?int $entityId,
        int $userId,
        ?array $old = null,
        ?array $new = null,
        ?string $reason = null,
        ?int $employeeId = null,
        ?int $companyId = null
    ): void {
        if (! $this->tablesReady()) {
            return;
        }

        $this->db->table('hr_audit_logs')->insert([
            'company_id'   => $companyId,
            'employee_id'  => $employeeId,
            'user_id'      => $userId,
            'module'       => $module,
            'action'       => $action,
            'entity_type'  => $entityType,
            'entity_id'    => $entityId,
            'old_values'   => $old ? json_encode($old) : null,
            'new_values'   => $new ? json_encode($new) : null,
            'reason'       => $reason,
            'ip_address'   => service('request')->getIPAddress(),
        ]);
    }
}
