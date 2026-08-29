<?php

namespace App\Filters;

use App\Services\WorkspaceService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Hard-redirect when a user's workspace cannot access the requested module.
 * Portal → /portal, Collector → /collector (except profile/notifications/auth).
 */
class WorkspaceFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('fm');
        $role = (string) (session()->get('user_role') ?? 'client');
        $uri  = fm_normalize_route_path($request->getUri()->getPath());

        $svc       = new WorkspaceService(\Config\Database::connect());
        $workspace = $svc->sessionWorkspace($role);

        // Portal users may only use portal (+ profile/notifications)
        if ($workspace === 'portal') {
            $allowed = $uri === '' || $uri === 'portal' || str_starts_with($uri, 'portal/')
                || str_starts_with($uri, 'profile')
                || str_starts_with($uri, 'notifications')
                || str_starts_with($uri, 'auth/')
                || $uri === 'logout'
                || str_starts_with($uri, 'file/');
            if (! $allowed) {
                return redirect()->to(base_url('portal'))
                    ->with('error', 'Please use the Tenant Portal.');
            }

            return;
        }

        // Cash collectors may only use collector app
        if ($workspace === 'collector') {
            $allowed = $uri === '' || $uri === 'collector' || str_starts_with($uri, 'collector/')
                || str_starts_with($uri, 'profile')
                || str_starts_with($uri, 'notifications')
                || str_starts_with($uri, 'auth/')
                || $uri === 'logout'
                || str_starts_with($uri, 'file/');
            if (! $allowed) {
                return redirect()->to(base_url('collector'))
                    ->with('error', 'Please use the Cash Collector app.');
            }

            return;
        }

        // PM/FM staff may use collector assign/handoff (shared); block portal routes for them
        if (in_array($workspace, ['pm', 'fm'], true) && ($uri === 'portal' || str_starts_with($uri, 'portal/'))) {
            return redirect()->to(base_url('dashboard'))
                ->with('error', 'Tenant Portal is for tenant users only.');
        }

        if ($svc->isReadOnlyMaintenanceRequest($role, $request->getMethod())) {
            $maintenancePaths = ['helpdesk', 'maintenance'];
            foreach ($maintenancePaths as $prefix) {
                if ($uri === $prefix || str_starts_with($uri, $prefix . '/')) {
                    $mutating = ! in_array($uri, [$prefix, $prefix . '/create'], true)
                        && ! preg_match('#^' . preg_quote($prefix, '#') . '/view/#', $uri);
                    if ($mutating && strtoupper($request->getMethod()) === 'POST') {
                        return redirect()->to(base_url('dashboard'))
                            ->with('error', 'Maintenance workflow is read-only in Property Management workspace.');
                    }
                }
            }
        }

        if (! $svc->canAccessRoute($role, $uri)) {
            return redirect()->to(base_url('dashboard'))
                ->with('error', 'That module is not available in your workspace.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
