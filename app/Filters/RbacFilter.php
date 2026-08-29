<?php

namespace App\Filters;

use App\Services\RbacService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Enforces RBAC on protected routes (runs after auth).
 */
class RbacFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('fm');
        $role = session()->get('user_role') ?? 'client';
        $uri  = fm_normalize_route_path($request->getUri()->getPath());

        $rbac = new RbacService(\Config\Database::connect());
        if (!$rbac->canAccessRoute($role, $uri)) {
            return redirect()->to(base_url('dashboard'))
                ->with('error', 'You do not have permission to access that module.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
