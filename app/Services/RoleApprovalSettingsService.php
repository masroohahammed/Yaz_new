<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Per-role approval workflow toggles for FM and PM workspaces.
 * Stored in role_approval_settings (role_id + workspace + setting_key).
 */
class RoleApprovalSettingsService
{
    /** @var array<string, array<string, string>> workspace => key => label */
    public const APPROVAL_KEYS = [
        'fm' => [
            'fm_helpdesk_approve'      => 'Approve helpdesk / maintenance requests',
            'fm_wo_budget_approve'     => 'Approve work order budgets',
            'fm_estimation_approve'    => 'Approve estimations / vendor bids',
            'fm_procurement_approve'   => 'Approve purchase requests',
            'fm_qa_signoff'            => 'QA sign-off on completed work',
        ],
        'pm' => [
            'pm_helpdesk_approve'      => 'Approve tenant / property requests',
            'pm_lease_approve'         => 'Approve lease contracts',
            'pm_payout_approve'        => 'Approve landlord payouts',
            'pm_property_cost_approve' => 'Approve property operating costs',
            'pm_sales_deal_approve'    => 'Approve sales / commission deals',
        ],
    ];

    /** Default enabled flags when seeding a new role (by role name). */
    /** @var array<string, list<string>> */
    private const DEFAULTS_BY_ROLE = [
        'facility_manager'    => ['fm_helpdesk_approve', 'fm_wo_budget_approve', 'fm_estimation_approve', 'fm_procurement_approve'],
        'property_manager'    => ['pm_helpdesk_approve', 'pm_lease_approve', 'pm_payout_approve', 'pm_property_cost_approve'],
        'real_estate_manager' => ['pm_helpdesk_approve', 'pm_lease_approve', 'pm_sales_deal_approve'],
        'finance_manager'     => ['fm_estimation_approve', 'fm_procurement_approve', 'pm_payout_approve', 'pm_property_cost_approve'],
        'supervisor'          => ['fm_helpdesk_approve'],
        'qa_inspector'        => ['fm_qa_signoff'],
        'procurement_officer' => ['fm_procurement_approve'],
        'salesman'            => ['pm_sales_deal_approve'],
    ];

    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db ??= \Config\Database::connect();
    }

    public function tableReady(): bool
    {
        return $this->db->tableExists('role_approval_settings');
    }

    /** @return array<string, array<string, bool>> workspace => key => enabled */
    public function forRole(int $roleId): array
    {
        $out = ['fm' => [], 'pm' => []];
        foreach (self::APPROVAL_KEYS as $ws => $keys) {
            foreach (array_keys($keys) as $key) {
                $out[$ws][$key] = false;
            }
        }

        if (! $this->tableReady() || $roleId <= 0) {
            return $out;
        }

        $rows = $this->db->table('role_approval_settings')
            ->where('role_id', $roleId)
            ->get()->getResultArray();

        foreach ($rows as $row) {
            $ws = $row['workspace'] ?? '';
            $key = $row['setting_key'] ?? '';
            if (isset($out[$ws][$key])) {
                $out[$ws][$key] = (bool) ($row['enabled'] ?? 0);
            }
        }

        return $out;
    }

    public function canApprove(string $roleName, string $workspace, string $settingKey): bool
    {
        if ($roleName === 'super_admin') {
            return true;
        }

        if (! $this->tableReady()) {
            return $this->legacyCanApprove($roleName, $workspace, $settingKey);
        }

        $role = $this->db->table('roles')->where('name', $roleName)->get()->getRowArray();
        if (! $role) {
            return false;
        }

        $row = $this->db->table('role_approval_settings')
            ->where('role_id', (int) $role['id'])
            ->where('workspace', $workspace)
            ->where('setting_key', $settingKey)
            ->get()->getRowArray();

        if ($row) {
            return (bool) ($row['enabled'] ?? 0);
        }

        return in_array($settingKey, self::DEFAULTS_BY_ROLE[$roleName] ?? [], true);
    }

    /** @param array<string, array<string, mixed>> $posted fm/pm => key => '1'|null */
    public function save(int $roleId, array $posted): void
    {
        if (! $this->tableReady() || $roleId <= 0) {
            return;
        }

        $this->db->table('role_approval_settings')->where('role_id', $roleId)->delete();

        foreach (self::APPROVAL_KEYS as $ws => $keys) {
            $wsPost = $posted[$ws] ?? [];
            foreach (array_keys($keys) as $key) {
                $enabled = ! empty($wsPost[$key]) ? 1 : 0;
                $this->db->table('role_approval_settings')->insert([
                    'role_id'     => $roleId,
                    'workspace'   => $ws,
                    'setting_key' => $key,
                    'enabled'     => $enabled,
                    'created_at'  => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    /** Seed defaults for a newly created role. */
    public function seedDefaults(int $roleId, string $roleName): void
    {
        if (! $this->tableReady() || $roleId <= 0) {
            return;
        }

        $defaults = self::DEFAULTS_BY_ROLE[$roleName] ?? [];
        foreach (self::APPROVAL_KEYS as $ws => $keys) {
            foreach (array_keys($keys) as $key) {
                $this->db->table('role_approval_settings')->insert([
                    'role_id'     => $roleId,
                    'workspace'   => $ws,
                    'setting_key' => $key,
                    'enabled'     => in_array($key, $defaults, true) ? 1 : 0,
                    'created_at'  => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    private function legacyCanApprove(string $roleName, string $workspace, string $settingKey): bool
    {
        if ($settingKey === 'fm_helpdesk_approve' || $settingKey === 'pm_helpdesk_approve') {
            return in_array($roleName, ['super_admin', 'facility_manager', 'property_manager', 'real_estate_manager'], true);
        }
        if ($settingKey === 'fm_estimation_approve') {
            return in_array($roleName, ['super_admin', 'facility_manager', 'finance_manager'], true);
        }
        if ($settingKey === 'pm_lease_approve') {
            return in_array($roleName, ['super_admin', 'property_manager', 'real_estate_manager'], true);
        }
        if ($settingKey === 'pm_payout_approve') {
            return in_array($roleName, ['super_admin', 'finance_manager', 'property_manager'], true);
        }

        return $roleName === 'super_admin';
    }
}
