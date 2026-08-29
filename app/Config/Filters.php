<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\SecureHeaders;

/**
 * FM ERP — Filters Configuration
 *
 * Layers (do not collapse):
 *  - csrf        global (except API v1)
 *  - auth        authenticated route group
 *  - rbac        route-permission map (primary authorisation)
 *  - workspace   PM / FM / portal / collector isolation
 *  - permission  extra controller::method check on sensitive mutations
 *  - loginThrottle  POST /login and POST /auth/login only
 */
class Filters extends BaseConfig
{
    public array $aliases = [
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,

        'auth'           => \App\Filters\AuthFilter::class,
        'rbac'           => \App\Filters\RbacFilter::class,
        'workspace'      => \App\Filters\WorkspaceFilter::class,
        'permission'     => \App\Filters\PermissionFilter::class,
        'jwt'            => \App\Filters\JwtFilter::class,
        'loginThrottle'  => \App\Filters\LoginThrottleFilter::class,
    ];

    public array $globals = [
        'before' => [
            'csrf' => ['except' => ['api/v1/*', 'api/legacy/*']],
        ],
        'after' => ENVIRONMENT === 'production' ? [] : [
            'toolbar',
        ],
    ];

    public array $methods = [];

    /**
     * URI-pattern filters. loginThrottle is POST-gated inside the filter.
     * PermissionFilter is applied to sensitive mutation URIs (see Routes.php
     * for the matching POST registrations); it does not replace rbac/workspace.
     */
    public array $filters = [
        'loginThrottle' => [
            'before' => [
                'login',
                'auth/login',
            ],
        ],
    ];
}
