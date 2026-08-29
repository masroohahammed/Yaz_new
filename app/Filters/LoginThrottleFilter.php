<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Rate-limit login attempts (5 failures / 15 min per email+IP, with IP fallback).
 * Attached only to login POST URIs — does not throttle authenticated navigation.
 */
class LoginThrottleFilter implements FilterInterface
{
    private const MAX_ATTEMPTS = 5;
    private const WINDOW_MIN   = 15;

    public function before(RequestInterface $request, $arguments = null)
    {
        if (strtolower($request->getMethod()) !== 'post') {
            return;
        }

        $uri = $request->getUri()->getPath();
        if (! str_contains($uri, 'login')) {
            return;
        }

        try {
            $db = \Config\Database::connect();
        } catch (\Throwable $e) {
            log_message('error', 'LoginThrottleFilter: DB unavailable — ' . $e->getMessage());

            return;
        }

        if (! $db->tableExists('login_attempts')) {
            return;
        }

        $email = strtolower(trim((string) ($request->getPost('email') ?? '')));
        $ip    = $request->getIPAddress();
        $since = date('Y-m-d H:i:s', strtotime('-' . self::WINDOW_MIN . ' minutes'));

        $q = $db->table('login_attempts')->where('success', 0)->where('created_at >=', $since);
        if ($email !== '') {
            $q->groupStart()
                ->where('email', $email)
                ->orWhere('ip_address', $ip)
                ->groupEnd();
        } else {
            $q->where('ip_address', $ip);
        }

        $fails = $q->countAllResults();

        if ($fails >= self::MAX_ATTEMPTS) {
            log_message('warning', sprintf(
                'Login throttle: %d failed attempts in %d min (email=%s ip=%s)',
                $fails,
                self::WINDOW_MIN,
                $email !== '' ? hash('sha256', $email) : '(empty)',
                $ip
            ));

            return redirect()->to(base_url('login'))
                ->with('error', 'Too many failed login attempts. Try again in ' . self::WINDOW_MIN . ' minutes.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
