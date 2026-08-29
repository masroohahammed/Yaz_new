<?php

namespace App\Controllers\Hr;

use App\Controllers\BaseController;

abstract class HrBaseController extends BaseController
{
    protected ?string $workspaceRequired = null;

    protected function requireHrAccess(): void
    {
        $this->requireRole('super_admin', 'facility_manager', 'hr', 'finance_manager');
    }

    protected function profileIdForUser(int $userId): ?int
    {
        if (! $this->db->tableExists('employee_profiles') || $userId <= 0) {
            return null;
        }
        $row = $this->db->table('employee_profiles')->select('id')->where('user_id', $userId)->where('deleted_at', null)->get()->getRowArray();

        return $row ? (int) $row['id'] : null;
    }
}
