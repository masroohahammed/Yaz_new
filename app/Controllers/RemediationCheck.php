<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Public deploy verification — confirms Sep 4 remediation files are on disk.
 * GET /remediation-check  or  /public/remediation-check (depends on baseURL)
 */
class RemediationCheck extends Controller
{
    /** @var list<array{path: string, label: string}> */
    private const REQUIRED_FILES = [
        ['path' => 'app/Controllers/PublicContractSign.php', 'label' => 'Public contract sign controller'],
        ['path' => 'app/Services/ContractSignatureService.php', 'label' => 'Contract signature service'],
        ['path' => 'app/Services/SignatureStorageService.php', 'label' => 'Signature storage service'],
        ['path' => 'app/Views/partials/_lease_signature_panel.php', 'label' => 'Signing link UI panel'],
        ['path' => 'app/Views/public/contract_sign.php', 'label' => 'Public sign page view'],
        ['path' => 'public/assets/js/signature-pad.js', 'label' => 'Signature pad JS'],
        ['path' => 'public/assets/css/contract-signature.css', 'label' => 'Signature pad CSS'],
        ['path' => 'app/Services/UserFacilityService.php', 'label' => 'User facility scoping service'],
        ['path' => 'app/Services/PropertyAssignmentService.php', 'label' => 'Property staff assignment service'],
        ['path' => 'app/Services/ParkingContractPhotoService.php', 'label' => 'Parking contract photos service'],
        ['path' => 'app/Services/UnitExpiryService.php', 'label' => 'Unit expiry display service'],
        ['path' => 'app/Views/partials/_unit_contract_expiry.php', 'label' => 'Unit contract expiry partial'],
        ['path' => 'public/assets/css/contract-signature.css', 'label' => 'Contract signature + bilingual CSS'],
        ['path' => 'app/Services/UnitTenancyService.php', 'label' => 'Unit tenancy guard service'],
        ['path' => 'app/Helpers/fm_helper.php', 'label' => 'FM helper (fm_can_view_kpis)'],
        ['path' => 'database/patches/2026-09-02-lease-contract-signature.sql', 'label' => 'Signature SQL patch'],
        ['path' => 'database/patches/2026-09-04-user-facilities-autoincrement.sql', 'label' => 'User facilities SQL patch'],
        ['path' => 'database/patches/2026-09-05-units-contracts-autoincrement.sql', 'label' => 'Units/contracts SQL patch'],
        ['path' => 'database/patches/2026-09-04-parking-contract-photos.sql', 'label' => 'Parking photos SQL patch'],
        ['path' => 'database/patches/fm-erp-complete.sql', 'label' => 'Complete SQL bundle'],
    ];

    public function index(): ResponseInterface
    {
        $root  = rtrim(FCPATH, '/\\') . '/..';
        $build = $this->loadBuildMeta();

        $files = [];
        $missing = [];
        foreach (self::REQUIRED_FILES as $item) {
            $abs = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $item['path']);
            $ok  = is_file($abs) && filesize($abs) > 0;
            $entry = [
                'path'    => $item['path'],
                'label'   => $item['label'],
                'present' => $ok,
                'bytes'   => $ok ? filesize($abs) : 0,
            ];
            $files[] = $entry;
            if (! $ok) {
                $missing[] = $item['path'];
            }
        }

        $routesFile = $root . '/app/Config/Routes.php';
        $routesSrc  = is_file($routesFile) ? (string) file_get_contents($routesFile) : '';
        $routesOk   = str_contains($routesSrc, 'PublicContractSign::show')
            && str_contains($routesSrc, 'generate-sign-link')
            && str_contains($routesSrc, 'contract/sign');

        $helperFile = $root . '/app/Helpers/fm_helper.php';
        $helperSrc  = is_file($helperFile) ? (string) file_get_contents($helperFile) : '';
        $kpiOk      = str_contains($helperSrc, 'fm_can_view_kpis');

        $complete = $missing === [] && $routesOk && $kpiOk;

        return $this->response
            ->setStatusCode($complete ? 200 : 503)
            ->setJSON([
                'ok'       => $complete,
                'release'  => $build['release'] ?? 'unknown',
                'commit'   => $build['commit'] ?? 'unknown',
                'branch'   => $build['branch'] ?? 'unknown',
                'checked'  => date('c'),
                'routes'   => ['contract_sign' => $routesOk, 'generate_sign_link' => $routesOk],
                'kpi_helper' => $kpiOk,
                'missing'  => $missing,
                'files'    => $files,
                'deploy'   => [
                    'clone'     => 'git clone https://github.com/masroohahammed/Yaz_new.git && cd Yaz_new && git checkout cursor/fm-erp-remediation-a002',
                    'sql'       => 'Run database/patches/fm-erp-complete.sql in phpMyAdmin',
                    'verify'    => base_url('remediation-check'),
                ],
            ]);
    }

    /** @return array<string, mixed> */
    private function loadBuildMeta(): array
    {
        $path = FCPATH . 'BUILD.json';
        if (! is_file($path)) {
            return [];
        }
        $decoded = json_decode((string) file_get_contents($path), true);

        return is_array($decoded) ? $decoded : [];
    }
}
