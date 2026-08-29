<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AuthFilter — blocks unauthenticated requests and enforces role-based access.
 *
 * FIX (2026-05-21):
 *   • The original used UserModel::find() which only queries the `users` table —
 *     it never joins `roles`, so $user['role_name'] was always undefined, causing
 *     the role refresh to silently fail and the session to be stale.
 *     Replaced with a direct DB query that includes the roles join.
 *   • Redirect target changed from /auth/login to /login so it matches the
 *     short alias added in the Routes fix.
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (! $session->get('user_id')) {
            return redirect()->to('/login')->with('error', 'Please log in to continue.');
        }

        // Use a raw DB query with the roles join so role_name is always available.
        // UserModel::find() only hits the users table and never returns role_name.
        $db   = \Config\Database::connect();
        $user = $db->table('users u')
                   ->select('u.id, u.status, u.company_id, r.name AS role_name')
                   ->join('roles r', 'r.id = u.role_id', 'left')
                   ->where('u.id', (int) $session->get('user_id'))
                   ->get()->getRowArray();

        if (! $user || $user['status'] !== 'active') {
            $session->destroy();
            return redirect()->to('/login')->with('error', 'Your account has been deactivated.');
        }

        // Refresh session data in case role or company changed since last login
        $session->set('user_role',  $user['role_name'] ?? $session->get('user_role'));
        $session->set('company_id', $user['company_id']);

        $workspace = (new \App\Services\WorkspaceService($db))->sessionWorkspace((string) ($user['role_name'] ?? ''));
        $session->set('workspace', $workspace);

        // Enforce forced password change mid-session
        $path = trim($request->getUri()->getPath(), '/');
        if ($session->get('force_password_change')
            && ! str_starts_with($path, 'profile/force-password')
            && ! str_starts_with($path, 'auth/logout')
            && ! str_starts_with($path, 'logout')) {
            return redirect()->to(base_url('profile/force-password-change'))
                ->with('error', 'Your password has expired. Please set a new password.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
