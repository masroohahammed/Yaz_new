<?php
namespace Config;

use CodeIgniter\Config\BaseConfig;

class Security extends BaseConfig
{
    /**
     * CSRF Protection Method.
     * Options: 'cookie' | 'session'
     * Session is safer for SPAs / AJAX-heavy apps.
     */
    public string $csrfProtection = 'session';

    /**
     * Randomize CSRF token on every request.
     * FIX SEC-06: Was set to false — now true for production security.
     */
    public bool $tokenRandomize = true;

    /**
     * Regenerate CSRF token after each successful verification.
     * FIX: REQUIRED by CI4 4.7.2 — Security.php line 263 reads
     * $this->config->regenerate. Omitting this causes:
     *   "Undefined property: Config\Security::$regenerate"
     */
    public bool $regenerate = true;

    /**
     * CSRF token name.
     */
    public string $tokenName = 'csrf_token';

    /**
     * CSRF Header name (for AJAX requests).
     */
    public string $headerName = 'X-CSRF-TOKEN';

    /**
     * CSRF Cookie name.
     */
    public string $cookieName = 'csrf_cookie';

    /**
     * CSRF token expiration time (in seconds). 0 = browser session only.
     */
    public int $expires = 7200;

    /**
     * If true, CSRF errors redirect instead of throwing a 403.
     */
    public bool $redirect = true;

    /**
     * CSRF SameSite cookie attribute: None | Lax | Strict | ''
     */
    public string $samesite = 'Lax';
}
