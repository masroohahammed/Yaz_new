<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Resolves PM/FM workspace from role and enforces route access.
 */
class WorkspaceService
{
    /** @var array<string, string> role slug => pm|fm|both */
    private const ROLE_WORKSPACE = [
        'super_admin'          => 'both',
        'facility_manager'     => 'fm',
        'maintenance_supervisor' => 'fm',
        'technician'           => 'fm',
        'qa_inspector'         => 'fm',
        'procurement_officer'  => 'fm',
        'property_manager'     => 'pm',
        'real_estate_manager'  => 'pm',
        'salesman'             => 'pm',
        'finance_manager'      => 'pm',
        'finance_user'         => 'pm',
        'supervisor'           => 'pm',
        'landlord'             => 'pm',
        'crm_agent'            => 'pm',
        'leasing_agent'        => 'pm',
        'accountant'           => 'pm',
        'caretaker'            => 'fm',
        'maintenance'          => 'fm',
        'maintenance_staff'    => 'fm',
        'client'               => 'portal',
        'tenant'               => 'portal',
        'cash_collector'       => 'collector',
    ];

    public function __construct(private ?BaseConnection $db = null)
    {
    }

    public function workspaceForRole(string $role): string
    {
        $role = strtolower(trim($role));
        if ($role === '') {
            return 'fm';
        }

        $fromDb = $this->workspaceFromDatabase($role);
        if ($fromDb !== null) {
            return $fromDb;
        }

        return self::ROLE_WORKSPACE[$role] ?? 'fm';
    }

    public function sessionWorkspace(string $role): string
    {
        $ws = $this->workspaceForRole($role);
        if ($ws === 'both') {
            return 'both';
        }
        if (in_array($ws, ['portal', 'collector'], true)) {
            return $ws;
        }

        return in_array($ws, ['pm', 'fm'], true) ? $ws : 'fm';
    }

    public function canAccessRoute(string $role, string $uri): bool
    {
        $workspace = $this->sessionWorkspace($role);
        if (in_array($workspace, ['both', 'portal', 'collector'], true)) {
            return true;
        }

        helper('fm');
        $uri      = fm_normalize_route_path($uri);
        $required = $this->requiredWorkspaceForUri($uri);
        if ($required === null) {
            return true;
        }

        if ($required === 'shared') {
            return true;
        }

        return $workspace === $required;
    }

    /**
     * PM users may browse maintenance/helpdesk read-only; block mutating POSTs.
     */
    public function isReadOnlyMaintenanceRequest(string $role, string $method): bool
    {
        if (strtoupper($method) !== 'POST') {
            return false;
        }

        return $this->sessionWorkspace($role) === 'pm';
    }

    public function requiredWorkspaceForUri(string $uri): ?string
    {
        helper('fm');
        $uri = fm_normalize_route_path($uri);
        $config = config('Workspace');

        foreach ($config->pmOnlyPrefixes as $prefix) {
            if ($uri === $prefix || str_starts_with($uri, $prefix . '/')) {
                return 'pm';
            }
        }

        foreach ($config->fmOnlyPrefixes as $prefix) {
            if ($uri === $prefix || str_starts_with($uri, $prefix . '/')) {
                return 'fm';
            }
        }

        foreach ($config->sharedPrefixes as $prefix) {
            if ($uri === $prefix || str_starts_with($uri, $prefix . '/')) {
                return 'shared';
            }
        }

        return null;
    }

    /** @return list<array<string, mixed>> */
    public function buildMenu(string $role): array
    {
        $workspace   = $this->sessionWorkspace($role);
        $pmMenu      = config('PmMenu')->items;
        $fmMenu      = config('FmMenu')->items;
        $hrMenu      = config('HrMenu')->items;
        $financeMenu = config('FinanceMenu')->items;

        if ($workspace === 'portal') {
            return config('PortalMenu')->items;
        }

        if ($workspace === 'collector') {
            return config('CollectorMenu')->items;
        }

        if ($workspace === 'both') {
            $core = $this->mergeMenus($pmMenu, $fmMenu);

            return $this->mergeMenus($this->mergeMenus($core, $hrMenu), $financeMenu);
        }
        if ($workspace === 'pm') {
            return $this->mergeMenus($pmMenu, $this->filterFinanceMenuForPm($financeMenu));
        }

        return $this->mergeMenus($this->mergeMenus($fmMenu, $hrMenu), $financeMenu);
    }

    /**
     * PM workspace: drop Finance — Treasury section (petty cash, bank recon, etc.).
     *
     * @param list<array<string, mixed>> $items
     * @return list<array<string, mixed>>
     */
    private function filterFinanceMenuForPm(array $items): array
    {
        $treasuryKeys = ['petty_cash', 'bank_reconciliation', 'reimbursements', 'cash_flow'];
        $out                = [];
        $inTreasurySection  = false;

        foreach ($items as $item) {
            if (($item['type'] ?? '') === 'heading') {
                $label = (string) ($item['label'] ?? '');
                $inTreasurySection = $label === 'Finance — Treasury';
                if ($inTreasurySection) {
                    continue;
                }
                $out[] = $item;
                continue;
            }

            if ($inTreasurySection) {
                continue;
            }

            $key = (string) ($item['key'] ?? '');
            if (in_array($key, $treasuryKeys, true)) {
                continue;
            }

            $out[] = $item;
        }

        return $out;
    }

    /** @param list<array<string, mixed>> $a @param list<array<string, mixed>> $b */
    private function mergeMenus(array $a, array $b): array
    {
        $seen = [];
        $out  = [];

        foreach (array_merge($a, $b) as $item) {
            if (($item['type'] ?? '') === 'heading') {
                $label = (string) ($item['label'] ?? '');
                $last  = $out[count($out) - 1] ?? null;
                if ($last !== null
                    && ($last['type'] ?? '') === 'heading'
                    && (string) ($last['label'] ?? '') === $label) {
                    continue;
                }
                $out[] = $item;
                continue;
            }

            $key = (string) ($item['key'] ?? $item['url'] ?? '');
            if ($key === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $item;
        }

        return $out;
    }

    private function workspaceFromDatabase(string $role): ?string
    {
        if ($this->db === null || ! $this->db->tableExists('roles')) {
            return null;
        }
        if (! $this->db->fieldExists('workspace', 'roles')) {
            return null;
        }

        try {
            $row = $this->db->table('roles')
                ->select('workspace')
                ->where('name', $role)
                ->limit(1)
                ->get()
                ->getRowArray();
            if (! $row || empty($row['workspace'])) {
                return null;
            }
            $ws = (string) $row['workspace'];

            return in_array($ws, ['pm', 'fm', 'both', 'portal', 'collector'], true) ? $ws : null;
        } catch (\Throwable $e) {
            log_message('error', 'WorkspaceService DB lookup failed: ' . $e->getMessage());

            return null;
        }
    }
}
