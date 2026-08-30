<?php

namespace App\Controllers\Traits;

/**
 * QR scan → inspection create routing (property / unit / asset).
 */
trait QrInspectionRedirectTrait
{
    protected function qrScanShowsHub(): bool
    {
        $action = strtolower(trim((string) ($this->request->getGet('action') ?? 'inspect')));

        return in_array($action, ['hub', 'menu', 'landing'], true);
    }

    protected function redirectToInspectionUrl(string $url): \CodeIgniter\HTTP\RedirectResponse
    {
        if (! session()->get('user_id')) {
            session()->set('redirect_after_login', $url);

            return redirect()->to(base_url('login'))
                ->with('info', 'Please log in to start the inspection.');
        }

        return redirect()->to($url);
    }

    /**
     * @param array<string, string> $extraQuery
     */
    protected function appendQuery(string $url, array $extraQuery): string
    {
        if ($extraQuery === []) {
            return $url;
        }

        $sep = str_contains($url, '?') ? '&' : '?';

        return $url . $sep . http_build_query($extraQuery);
    }

    protected function inspectionTypeFromScan(): ?string
    {
        $type = strtolower(trim((string) ($this->request->getGet('inspection_type') ?? '')));
        if (in_array($type, ['move_in', 'move_out', 'routine'], true)) {
            return $type;
        }

        return null;
    }

    protected function resolveQrScanRedirect(?string $inspectionUrl, ?string $maintenanceUrl = null): ?\CodeIgniter\HTTP\RedirectResponse
    {
        $action = strtolower(trim((string) ($this->request->getGet('action') ?? 'inspect')));

        if ($action === 'maintenance' && $maintenanceUrl !== null) {
            return redirect()->to($maintenanceUrl);
        }

        if ($this->qrScanShowsHub() || $inspectionUrl === null) {
            return null;
        }

        $extra = [];
        $inspectionType = $this->inspectionTypeFromScan();
        if ($inspectionType !== null) {
            $extra['inspection_type'] = $inspectionType;
        }

        return $this->redirectToInspectionUrl($this->appendQuery($inspectionUrl, $extra));
    }
}
