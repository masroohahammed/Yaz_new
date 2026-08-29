<?php

namespace App\Controllers;

use App\Services\MaintenanceScopeQuery;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Public maintenance form — extends Controller directly (not BaseController)
 * so the GET path never runs query-builder scoping that triggers CI4 whereKey errors.
 */
class PublicMaintenance extends Controller
{
    /** Bump when deploying — visible in page source as fm-maintenance-build */
    public const MAINTENANCE_BUILD = '2026-08-29-6';

    /** @var \CodeIgniter\Database\BaseConnection */
    protected $db;

    /** @var array<string, string> */
    protected array $settings = [];

    /** @var array<string, string>|null */
    private static ?array $settingsCache = null;

    public function initController(
        RequestInterface $request,
        ResponseInterface $response,
        LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->db = \Config\Database::connect();
        $this->settings = self::loadSettings($this->db);
    }

    /**
     * Deploy verification — any of:
     *   GET public/maintenance?ping=1          (works without extra route)
     *   GET public/maintenance/ping            (needs Routes.php)
     *   GET maintenance-ping                   (flat alias)
     */
    public function ping()
    {
        return $this->pingResponse();
    }

    public function index()
    {
        if ($this->request->getGet('ping') !== null) {
            return $this->pingResponse();
        }

        $facilityId = (int) ($this->request->getGet('facility_id') ?? $this->request->getPost('facility_id') ?? 0);
        $unitId     = (int) ($this->request->getGet('unit_id') ?? $this->request->getPost('unit_id') ?? 0);
        $assetId    = (int) ($this->request->getGet('asset_id') ?? $this->request->getPost('asset_id') ?? 0);

        try {
            if ($facilityId > 0 && $unitId <= 0 && $assetId <= 0) {
                return $this->renderPropertyMaintenance($facilityId);
            }

            return $this->renderMaintenancePage();
        } catch (\Throwable $e) {
            log_message('error', 'PublicMaintenance::index [' . self::MAINTENANCE_BUILD . ']: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());

            if ($facilityId <= 0 && $unitId <= 0 && $assetId <= 0) {
                throw $e;
            }

            return $this->maintenanceView([
                'scope'   => $this->fallbackScope($facilityId, $unitId, $assetId),
                'records' => [],
                'units'   => $facilityId > 0 ? MaintenanceScopeQuery::unitsForFacility($this->db, $facilityId) : [],
            ]);
        }
    }

    public function submit()
    {
        $scope = $this->resolveScope();
        if ($scope === null) {
            return redirect()->back()->with('error', 'Invalid scope.');
        }

        $rules = [
            'requester_name' => 'required|min_length[2]',
            'description'    => 'required|min_length[10]',
            'priority'       => 'required|in_list[critical,high,medium,low]',
            'category'       => 'permit_empty|max_length[100]',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $unitId = (int) ($this->request->getPost('unit_id') ?? 0);
        if ($unitId <= 0 && ! empty($scope['unit_id'])) {
            $unitId = (int) $scope['unit_id'];
        }
        $facilityId = (int) ($scope['facility_id'] ?? 0);
        if ($unitId > 0) {
            $fromUnit = MaintenanceScopeQuery::unitFacilityId($this->db, $unitId);
            if ($fromUnit) {
                $facilityId = $fromUnit;
            }
        }

        $ticket = 'TKT-' . date('Y') . '-' . strtoupper(substr(uniqid(), -6));

        try {
            MaintenanceScopeQuery::insertRequest($this->db, [
                'ticket_number'   => $ticket,
                'facility_id'     => $facilityId > 0 ? $facilityId : null,
                'unit_id'         => $unitId > 0 ? $unitId : null,
                'asset_id'        => ! empty($scope['asset_id']) ? (int) $scope['asset_id'] : null,
                'requester_name'  => esc($this->request->getPost('requester_name')),
                'requester_email' => esc($this->request->getPost('requester_email') ?? ''),
                'requester_phone' => esc($this->request->getPost('requester_phone') ?? ''),
                'category'        => esc($this->request->getPost('category') ?: 'general'),
                'description'     => esc($this->request->getPost('description')),
                'priority'        => $this->request->getPost('priority'),
                'status'          => 'pending',
                'approval_status' => 'pending',
                'scan_source'     => session()->get('user_id') ? 'staff_form' : 'public_form',
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'PublicMaintenance::submit insert: ' . $e->getMessage());

            return redirect()->back()->withInput()->with('error', 'Could not save maintenance request. Please try again.');
        }

        $qs = $this->scopeQueryString($scope);

        return redirect()->to(base_url('public/maintenance?' . $qs))
            ->with('success', 'Maintenance request submitted. Ticket: ' . $ticket);
    }

    /** Fast path: ?facility_id= only — raw SQL only */
    private function renderPropertyMaintenance(int $facilityId): string
    {
        $facility = MaintenanceScopeQuery::resolveFacility($this->db, $facilityId);
        if (! $facility) {
            return redirect()->to(base_url('request'))->with('error', 'Property not found.');
        }

        $scope = [
            'type'        => 'property',
            'facility_id' => $facilityId,
            'label'       => $facility['name'] ?? ('Property #' . $facilityId),
            'subtitle'    => trim(($facility['code'] ?? '') . ' · ' . ($facility['city'] ?? ''), ' ·'),
            'scan_url'    => ! empty($facility['qr_token'])
                ? base_url('scan/property/' . $facility['qr_token'])
                : base_url('scan/property/id/' . $facilityId),
        ];

        return $this->maintenanceView([
            'scope'   => $scope,
            'records' => MaintenanceScopeQuery::listRecords($this->db, $scope),
            'units'   => MaintenanceScopeQuery::unitsForFacility($this->db, $facilityId),
        ]);
    }

    /** @return \CodeIgniter\HTTP\RedirectResponse|string */
    private function renderMaintenancePage()
    {
        $scope = $this->resolveScope();
        if ($scope === null) {
            return redirect()->to(base_url('request'))->with('error', 'Property, unit, or asset not specified.');
        }

        $units = (($scope['type'] ?? '') === 'property' && ! empty($scope['facility_id']))
            ? MaintenanceScopeQuery::unitsForFacility($this->db, (int) $scope['facility_id'])
            : [];

        return $this->maintenanceView([
            'scope'   => $scope,
            'records' => MaintenanceScopeQuery::listRecords($this->db, $scope),
            'units'   => $units,
        ]);
    }

    /**
     * @param array{scope: array<string, mixed>, records: list<array<string, mixed>>, units: list<array<string, mixed>>} $data
     */
    private function maintenanceView(array $data): string
    {
        $scope = $data['scope'];
        $isLoggedIn = (bool) session()->get('user_id');
        $user = null;

        if ($isLoggedIn) {
            try {
                $user = $this->db->query(
                    'SELECT name, email FROM users WHERE id = ? LIMIT 1',
                    [(int) session()->get('user_id')]
                )->getRowArray();
            } catch (\Throwable $e) {
                log_message('error', 'PublicMaintenance user lookup: ' . $e->getMessage());
            }
        }

        $this->response->setHeader('X-FM-Maintenance-Build', self::MAINTENANCE_BUILD);

        return view('public/maintenance', [
            'title'       => 'Maintenance — ' . ($scope['label'] ?? 'Entity'),
            'scope'       => $scope,
            'entityLabel' => (string) ($scope['label'] ?? 'Entity'),
            'records'     => $data['records'],
            'units'       => $data['units'],
            'isLoggedIn'  => $isLoggedIn,
            'user'        => $user,
            'settings'    => $this->settings,
            'backUrl'     => $scope['scan_url'] ?? base_url('request'),
            'buildId'     => self::MAINTENANCE_BUILD,
        ]);
    }

    /** @return \CodeIgniter\HTTP\ResponseInterface */
    private function pingResponse()
    {
        return $this->response
            ->setHeader('X-FM-Maintenance-Build', self::MAINTENANCE_BUILD)
            ->setJSON([
                'ok'         => true,
                'build'      => self::MAINTENANCE_BUILD,
                'service'    => MaintenanceScopeQuery::BUILD,
                'controller' => self::class,
            ]);
    }

    /** @return array<string, mixed> */
    private function fallbackScope(int $facilityId, int $unitId, int $assetId): array
    {
        return [
            'type'        => $facilityId > 0 ? 'property' : ($unitId > 0 ? 'unit' : 'asset'),
            'facility_id' => $facilityId ?: null,
            'unit_id'     => $unitId ?: null,
            'asset_id'    => $assetId ?: null,
            'label'       => $facilityId > 0 ? ('Property #' . $facilityId) : ($unitId > 0 ? ('Unit #' . $unitId) : ('Asset #' . $assetId)),
            'subtitle'    => '',
            'scan_url'    => base_url('request'),
        ];
    }

    /** @return array<string, mixed>|null */
    private function resolveScope(): ?array
    {
        $facilityId = (int) ($this->request->getGet('facility_id') ?? $this->request->getPost('facility_id') ?? 0);
        $unitId     = (int) ($this->request->getGet('unit_id') ?? $this->request->getPost('unit_id') ?? 0);
        $assetId    = (int) ($this->request->getGet('asset_id') ?? $this->request->getPost('asset_id') ?? 0);

        if ($unitId > 0) {
            $unit = MaintenanceScopeQuery::resolveUnit($this->db, $unitId);
            if (! $unit) {
                return null;
            }

            return [
                'type'        => 'unit',
                'unit_id'     => $unitId,
                'facility_id' => (int) ($unit['facility_id'] ?? 0),
                'label'       => 'Unit ' . ($unit['unit_number'] ?? $unitId),
                'subtitle'    => $unit['facility_name'] ?? '',
                'scan_url'    => ! empty($unit['qr_token']) ? base_url('scan/unit/' . $unit['qr_token']) : base_url('scan/unit/id/' . $unitId),
            ];
        }

        if ($assetId > 0) {
            $asset = MaintenanceScopeQuery::resolveAsset($this->db, $assetId);
            if (! $asset) {
                return null;
            }

            return [
                'type'        => 'asset',
                'asset_id'    => $assetId,
                'facility_id' => (int) ($asset['facility_id'] ?? 0),
                'label'       => $asset['name'] ?? ('Asset #' . $assetId),
                'subtitle'    => ($asset['asset_code'] ?? '') . ' · ' . ($asset['facility_name'] ?? ''),
                'scan_url'    => ! empty($asset['qr_token']) ? base_url('scan/asset/' . $asset['qr_token']) : base_url('scan/asset/id/' . $assetId),
            ];
        }

        if ($facilityId > 0) {
            $facility = MaintenanceScopeQuery::resolveFacility($this->db, $facilityId);
            if (! $facility) {
                return null;
            }

            return [
                'type'        => 'property',
                'facility_id' => $facilityId,
                'label'       => $facility['name'] ?? ('Property #' . $facilityId),
                'subtitle'    => trim(($facility['code'] ?? '') . ' · ' . ($facility['city'] ?? ''), ' ·'),
                'scan_url'    => ! empty($facility['qr_token'])
                    ? base_url('scan/property/' . $facility['qr_token'])
                    : base_url('scan/property/id/' . $facilityId),
            ];
        }

        return null;
    }

    /** @param array<string, mixed> $scope */
    private function scopeQueryString(array $scope): string
    {
        $parts = [];
        if (! empty($scope['facility_id']) && ($scope['type'] ?? '') === 'property') {
            $parts['facility_id'] = (int) $scope['facility_id'];
        }
        if (! empty($scope['unit_id'])) {
            $parts['unit_id'] = (int) $scope['unit_id'];
        }
        if (! empty($scope['asset_id'])) {
            $parts['asset_id'] = (int) $scope['asset_id'];
        }

        return http_build_query($parts);
    }

    /** @return array<string, string> */
    private static function loadSettings(\CodeIgniter\Database\BaseConnection $db): array
    {
        if (self::$settingsCache !== null) {
            return self::$settingsCache;
        }

        try {
            $rows = $db->query('SELECT setting_key, setting_value FROM system_settings')->getResultArray();
            self::$settingsCache = array_column($rows, 'setting_value', 'setting_key');
        } catch (\Throwable $e) {
            log_message('error', 'PublicMaintenance settings: ' . $e->getMessage());
            self::$settingsCache = [];
        }

        return self::$settingsCache;
    }
}
