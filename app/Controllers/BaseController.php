<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Services\CompanyScopeService;
use App\Services\WorkspaceService;

/**
 * BaseController — shared helpers for all controllers.
 *
 * FIXES applied (2026-05-21):
 *  1. protected $settings  — populated from system_settings in initController()
 *  2. logoUrl()            — builds base_url() path to the company logo
 *  3. roleIdByName()       — looks up a role's PK by its slug name
 *  4. scopeCompany()       — applies company_id filter to a query builder
 *  5. scopeFacilities()    — applies facility-level multi-tenancy filter
 *  6. companyScope()       — returns a lazy-initialised CompanyScopeService
 *  7. viewData()           — merges standard layout variables (pageTitle,
 *                            currentUser, settings) into any view data array
 *
 * NOTE: Do NOT re-declare $request, $response, or $logger here.
 * CI4's parent Controller already declares them. Re-declaring with
 * a type in PHP 8.x causes a fatal "must not be defined" error.
 */
abstract class BaseController extends Controller
{
    /**
     * Workspace gate for this controller: 'pm', 'fm', or null (shared/global).
     * Enforced in initController() after auth filters run.
     */
    protected ?string $workspaceRequired = null;

    /** @var \CodeIgniter\Database\BaseConnection */
    protected $db;

    /**
     * System settings loaded from `system_settings` table.
     * Available in every child controller as $this->settings['key'].
     *
     * @var array<string,string>
     */
    protected array $settings = [];

    /** Lazy-initialised CompanyScopeService — use companyScope() accessor. */
    private ?CompanyScopeService $_companyScope = null;

    // ─────────────────────────────────────────────────────────────────────────
    // Bootstrap
    // ─────────────────────────────────────────────────────────────────────────

    public function initController(
        RequestInterface  $request,
        ResponseInterface $response,
        LoggerInterface   $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->db = \Config\Database::connect();

        // Populate $this->settings once per request (static cache shared across
        // all controller instances in the same request lifecycle).
        if (self::$_settingsCache === null) {
            $cached = cache()->get('fm_system_settings');
            if (is_array($cached)) {
                self::$_settingsCache = $cached;
            } else {
                try {
                    $rows = $this->db->table('system_settings')
                                     ->select('setting_key, setting_value')
                                     ->get()->getResultArray();
                    self::$_settingsCache = array_column(
                        $rows, 'setting_value', 'setting_key'
                    );
                    cache()->save('fm_system_settings', self::$_settingsCache, 300);
                } catch (\Throwable $e) {
                    log_message('error', 'BaseController: could not load system_settings — ' . $e->getMessage());
                    self::$_settingsCache = [];
                }
            }
        }
        $this->settings = self::$_settingsCache;

        $this->enforceWorkspaceAccess();
    }

    protected function enforceWorkspaceAccess(): void
    {
        if ($this->workspaceRequired === null) {
            return;
        }

        $role      = (string) session()->get('user_role');
        $workspace = (new WorkspaceService($this->db))->sessionWorkspace($role);

        if ($workspace === 'both' || $workspace === $this->workspaceRequired) {
            return;
        }

        if ($this->request->isAJAX()) {
            $this->response
                ->setStatusCode(403)
                ->setJSON(['status' => 'error', 'message' => 'Module not available in your workspace.'])
                ->send();
            exit;
        }

        session()->setFlashdata('error', 'That module is not available in your workspace.');
        redirect()->to(base_url('dashboard'))->send();
        exit;
    }

    protected function currentWorkspace(): string
    {
        $role = (string) session()->get('user_role');

        return (string) (session()->get('workspace')
            ?: (new WorkspaceService($this->db))->sessionWorkspace($role));
    }

    protected function isPmWorkspace(): bool
    {
        $ws = $this->currentWorkspace();

        return $ws === 'pm' || $ws === 'both';
    }

    protected function isFmWorkspace(): bool
    {
        $ws = $this->currentWorkspace();

        return $ws === 'fm' || $ws === 'both';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // View helper
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Merge standard layout variables into any controller's view data array.
     *
     * Every view that extends the main layout needs at minimum:
     *   • pageTitle    — used in <title> and the topbar heading
     *   • currentUser  — array with id, name, email, role_name, company_id
     *   • settings     — system_settings key=>value map
     *
     * Usage in controllers:
     *   return view('some/view', $this->viewData(['title' => 'My Page', ...]));
     *
     * @param array<string,mixed> $extra
     * @return array<string,mixed>
     */
    protected function viewData(array $extra = []): array
    {
        $user = $this->currentUser();

        $base = [
            'pageTitle'   => $extra['title'] ?? $extra['pageTitle'] ?? 'FM ERP',
            'currentUser' => $user,
            'settings'    => $this->settings,
            'currency'    => $this->settings['currency'] ?? 'QAR',
        ];

        // Allow callers to set pageTitle via either 'title' or 'pageTitle'
        if (isset($extra['title'])) {
            $base['pageTitle'] = $extra['title'];
        }

        helper('fm');
        $useBranding = ! empty($extra['usePdf']) || ! empty($extra['useCompanyBranding']);
        if ($useBranding) {
            $branding = fm_company_branding(
                $this->settings,
                isset($extra['companyId']) ? (int) $extra['companyId'] : null
            );
            $base['settings']         = $branding['settings'];
            $base['companyLogoUrl']   = $branding['logoUrl'];
            $base['companyLogoB64']   = $branding['logoB64'];
            $base['companyBranding']  = $branding;
        } else {
            $base['companyLogoUrl'] = $this->logoUrl();
            if (! empty($extra['usePdf'])) {
                $base['companyLogoB64'] = fm_logo_data_uri($this->settings['company_logo'] ?? '');
            }
        }

        return array_merge($base, $extra);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Multi-tenancy scoping helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Return a lazy-initialised CompanyScopeService for the current session.
     * Used by Dashboard and Reports for facility-list lookups.
     *
     * Usage:
     *   $this->companyScope()->facilityIds()
     *   $this->companyScope()->activeCompanyId()
     */
    protected function companyScope(): CompanyScopeService
    {
        if ($this->_companyScope === null) {
            $this->_companyScope = new CompanyScopeService($this->db, session());
        }
        return $this->_companyScope;
    }

    /**
     * Apply a company_id WHERE clause to a query builder when the current user
     * is scoped to a single company.  Super-admins with no company_id get all
     * rows (no clause added).
     *
     * Supports two calling conventions:
     *
     *   // Two-arg: builder is modified in place AND returned
     *   $this->scopeCompany($builder, 'v.company_id');
     *
     *   // One-arg: builder returned (column defaults to 'company_id')
     *   $count = $this->scopeCompany($this->db->table('facilities'))->countAllResults();
     *
     * @param  object      $builder  CI4 query builder
     * @param  string      $column   Qualified column name, e.g. 'v.company_id'
     * @return object                The same builder (fluent)
     */
    protected function scopeCompany(object $builder, string $column = 'company_id'): object
    {
        return $this->companyScope()->applyCompanyColumn($builder, $column);
    }

    /**
     * Apply a facility_id WHERE / WHERE IN clause to a query builder, scoped
     * to the facilities the current user's company owns.
     *
     * Super-admins with no company see all facilities (no clause added).
     * Users whose company has no active facilities get a WHERE 1=0 (empty set).
     *
     * @param  object $builder        CI4 query builder
     * @param  string $facilityColumn Qualified column name, e.g. 'w.facility_id'
     * @return object                 The same builder (fluent)
     */
    protected function scopeFacilities(object $builder, string $facilityColumn = 'facility_id'): object
    {
        return $this->companyScope()->applyFacilityScope($builder, $facilityColumn);
    }

    /**
     * SQL fragment + params for raw queries: AND alias.facility_id IN (...)
     *
     * @return array{0: string, 1: list<int|string>}
     */
    protected function sqlFacilityFilter(string $tableAlias = 'i'): array
    {
        $ids = $this->companyScope()->facilityIds();
        if ($ids === null) {
            return ['', []];
        }
        if ($ids === []) {
            return [' AND 1=0 ', []];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $col          = $tableAlias !== '' ? "{$tableAlias}.facility_id" : 'facility_id';

        return [" AND {$col} IN ({$placeholders}) ", $ids];
    }

    /** Abort if current user cannot access this facility (multi-tenant guard). */
    protected function assertFacilityAccess(int $facilityId): void
    {
        if ($facilityId < 1) {
            return;
        }
        $ids = $this->companyScope()->facilityIds();
        if ($ids === null) {
            return;
        }
        if (! in_array($facilityId, $ids, true)) {
            if ($this->request->isAJAX()) {
                $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Access denied for this facility.'])->send();
                exit;
            }
            session()->setFlashdata('error', 'You do not have access to this facility.');
            redirect()->to(base_url('dashboard'))->send();
            exit;
        }
    }

    protected function canManageFinancePayments(): bool
    {
        return in_array($this->currentUser()['role_name'], ['super_admin', 'finance_manager', 'facility_manager'], true);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Logo & role helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Return a full base_url() path to the company logo stored in
     * writable/uploads/, or an empty string when none is set.
     */
    protected function logoUrl(): string
    {
        helper('fm');

        return fm_logo_url($this->settings['company_logo'] ?? '');
    }

    /**
     * Look up a role's primary-key integer by its slug name.
     * Returns null when the role does not exist so callers can handle
     * the missing-role case gracefully instead of inserting a null FK.
     */
    protected function roleIdByName(string $name): ?int
    {
        try {
            $row = $this->db->table('roles')
                            ->select('id')
                            ->where('name', $name)
                            ->limit(1)
                            ->get()->getRowArray();
            return $row ? (int) $row['id'] : null;
        } catch (\Throwable $e) {
            log_message('error', 'roleIdByName failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function usersByRole(string $roleName, ?int $companyId = null): array
    {
        return (new \App\Models\UserModel($this->db))->getUsersByRole($roleName, $companyId);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Current user
    // ─────────────────────────────────────────────────────────────────────────

    protected function currentUser(): array
    {
        helper('fm');
        $s = session();
        $roleName = (string) $s->get('user_role');

        return [
            'id'           => (int)    $s->get('user_id'),
            'name'         => (string) $s->get('user_name'),
            'email'        => (string) $s->get('user_email'),
            'role_id'      => (int)    $s->get('role_id'),
            'role_name'    => $roleName,
            'role_display' => fm_role_display($roleName),
            'company_id'   => $s->get('company_id'),
            'phone'        => '',
        ];
    }

    protected function currentUserProfile(): array
    {
        $user = $this->currentUser();
        if ($user['id'] < 1) {
            return $user;
        }

        try {
            $row = $this->db->table('users u')
                ->select('u.name, u.email, u.phone, u.company_id, r.name AS role_name, r.display_name AS role_display')
                ->join('roles r', 'r.id = u.role_id', 'left')
                ->where('u.id', $user['id'])
                ->get()->getRowArray();
            if ($row) {
                $user['name']         = (string) ($row['name'] ?? $user['name']);
                $user['email']        = (string) ($row['email'] ?? $user['email']);
                $user['phone']        = (string) ($row['phone'] ?? '');
                $user['role_name']    = (string) ($row['role_name'] ?? $user['role_name']);
                $user['role_display'] = (string) ($row['role_display'] ?? fm_role_display($user['role_name']));
                $user['company_id']   = $row['company_id'] ?? $user['company_id'];
            }
        } catch (\Throwable $e) {
            log_message('error', 'currentUserProfile: ' . $e->getMessage());
        }

        return $user;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Role enforcement
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @param string|array<string> ...$roles Role slug(s) allowed to proceed.
     */
    protected function requireRole(string|array ...$roles): void
    {
        $flat = [];
        foreach ($roles as $r) {
            $flat = array_merge($flat, is_array($r) ? $r : [$r]);
        }
        $user = $this->currentUser();
        if (in_array($user['role_name'], $flat, true)) {
            return;
        }

        if ($this->request->isAJAX()) {
            $this->response
                 ->setStatusCode(403)
                 ->setJSON(['status' => 'error', 'message' => 'Access denied.'])
                 ->send();
            exit;
        }

        session()->setFlashdata('error', 'You do not have permission to perform this action.');
        redirect()->back()->send();
        exit;
    }

    protected function requirePermission(string $permission): void
    {
        $role = (string) (session()->get('user_role') ?? 'client');
        $rbac = new \App\Services\RbacService($this->db);

        if ($rbac->can($role, $permission)) {
            return;
        }

        if ($this->request->isAJAX()) {
            $this->response
                 ->setStatusCode(403)
                 ->setJSON(['status' => 'error', 'message' => 'Access denied.'])
                 ->send();
            exit;
        }

        session()->setFlashdata('error', 'You do not have permission to perform this action.');
        redirect()->back()->send();
        exit;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Activity log (silent — never breaks page on failure)
    // ─────────────────────────────────────────────────────────────────────────

    protected function logActivity(string $action, string $module, int $recordId, ?string $description = null): void
    {
        $user = $this->currentUser();
        try {
            $role = $user['role_name'] ?? (string) session()->get('user_role');
            $name = $user['name'] ?? (string) session()->get('user_name');
            $uri  = (string) ($this->request->getUri()->getPath() ?? '');
            $meth = strtoupper((string) ($this->request->getMethod() ?? 'GET'));
            $desc = trim((string) ($description ?? ''));
            $parts = array_filter([
                $name !== '' ? $name : null,
                $role !== '' ? '(' . $role . ')' : null,
                $desc !== '' ? $desc : null,
                $uri !== '' ? $meth . ' ' . $uri : null,
            ]);
            $fullDesc = implode(' — ', $parts);

            $this->db->table('activity_logs')->insert([
                'user_id'     => max(0, (int) ($user['id'] ?? 0)),
                'action'      => $action,
                'module'      => $module,
                'record_id'   => $recordId > 0 ? $recordId : null,
                'description' => $fullDesc,
                'ip_address'  => $this->request->getIPAddress(),
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'logActivity failed: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Notifications (silent — never breaks page on failure)
    // ─────────────────────────────────────────────────────────────────────────

    protected function sendNotification(
        int    $userId,
        string $title,
        string $message,
        string $type        = 'general',
        ?int   $referenceId = null
    ): void {
        try {
            $this->db->table('notifications')->insert([
                'user_id'      => $userId,
                'title'        => $title,
                'message'      => $message,
                'type'         => $type,
                'reference_id' => $referenceId,
                'is_read'      => 0,
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'sendNotification failed: ' . $e->getMessage());
        }
    }

    protected function notifyManagers(string $title, string $message): void
    {
        try {
            $managers = $this->db->table('users u')
                                 ->select('u.id')
                                 ->join('roles r', 'r.id = u.role_id')
                                 ->whereIn('r.name', ['super_admin', 'facility_manager'])
                                 ->where('u.status', 'active')
                                 ->get()->getResultArray();
            foreach ($managers as $mgr) {
                $this->sendNotification((int) $mgr['id'], $title, $message, 'work_order');
            }
        } catch (\Throwable $e) {
            log_message('error', 'notifyManagers failed: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Settings helpers (kept for backward compatibility)
    // $this->settings is now the canonical way to access system settings.
    // ─────────────────────────────────────────────────────────────────────────

    /** @var array<string,string>|null */
    private static ?array $_settingsCache = null;

    protected function getSetting(string $key, string $default = ''): string
    {
        return (string) ($this->settings[$key] ?? $default);
    }

    protected function getSystemSettings(): array
    {
        return $this->settings;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Pagination & document numbers
    // ─────────────────────────────────────────────────────────────────────────

    /** @return array{page: int, perPage: int, offset: int} */
    protected function paginate(int $perPage = 25): array
    {
        $page   = max(1, (int) ($this->request->getGet('page') ?? 1));
        $offset = ($page - 1) * $perPage;

        return [
            'page'    => $page,
            'perPage' => $perPage,
            'offset'  => $offset,
        ];
    }

    protected function generateNumber(string $prefix, string $table, string $column): string
    {
        $like = $prefix . '-' . date('Y') . '-';
        $last = $this->db->table($table)
                         ->like($column, $like, 'after')
                         ->orderBy('id', 'DESC')
                         ->limit(1)
                         ->get()
                         ->getRowArray();

        $seq = 1;
        if ($last && ! empty($last[$column])) {
            helper('fm');
            $seq = fm_sequence_from_code((string) $last[$column]) + 1;
        }

        return $like . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * CI4-compatible NULL filter (some hosts lack Builder::whereNull).
     */
    protected function whereNull($builder, string $column)
    {
        return $builder->where($column . ' IS NULL', null, false);
    }

    /**
     * Inventory rows for dropdowns (schema-safe: no status column in default schema).
     */
    protected function inventoryItemsForSelect(): array
    {
        $builder = $this->db->table('inventory_items')->orderBy('name', 'ASC');
        if ($this->db->fieldExists('deleted_at', 'inventory_items')) {
            $builder->where('deleted_at', null);
        } elseif ($this->db->fieldExists('status', 'inventory_items')) {
            $builder->where('status', 'active');
        }
        return $builder->get()->getResultArray();
    }

    /**
     * Materials used on a work order with optional inventory join (no sku column).
     */
    protected function woMaterialsForOrder(int $woId): array
    {
        $builder = $this->db->table('wo_materials wm')->where('wm.wo_id', $woId);
        if ($this->db->tableExists('inventory_items')) {
            $cols = ['wm.*'];
            if ($this->db->fieldExists('item_code', 'inventory_items')) {
                $cols[] = 'i.item_code AS inv_item_code';
            }
            if ($this->db->fieldExists('name', 'inventory_items')) {
                $cols[] = 'i.name AS inv_item_name';
            }
            $builder->select(implode(', ', $cols))
                ->join('inventory_items i', 'i.id = wm.item_id', 'left');
        } else {
            $builder->select('wm.*');
        }
        $rows = $builder->get()->getResultArray();
        foreach ($rows as &$row) {
            if (empty($row['item_code']) && ! empty($row['inv_item_code'])) {
                $row['item_code'] = $row['inv_item_code'];
            }
            if (empty($row['item_name']) && ! empty($row['inv_item_name'])) {
                $row['item_name'] = $row['inv_item_name'];
            }
        }
        unset($row);
        return $rows;
    }


}

