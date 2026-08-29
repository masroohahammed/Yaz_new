<?php

/**
 * FM ERP Global Helpers
 *
 * Load in app/Config/Autoload.php  $helpers array:
 *   public $helpers = ['fm', 'url', 'form', ...];
 *
 * Or load per-controller:
 *   helper('fm');
 */

if (! function_exists('fm_setting')) {
    /**
     * Read a value from system_settings. Cached in memory + app cache (5 min).
     */
    function fm_setting(string $key, string $default = ''): string
    {
        static $cache = null;

        if ($cache === null) {
            $cache = cache()->get('fm_system_settings');
            if (! is_array($cache)) {
                try {
                    $db   = \Config\Database::connect();
                    $rows = $db->table('system_settings')
                        ->select('setting_key, setting_value')
                        ->get()->getResultArray();
                    $cache = array_column($rows, 'setting_value', 'setting_key');
                    cache()->save('fm_system_settings', $cache, 300);
                } catch (\Throwable $e) {
                    $cache = [];
                    log_message('error', 'fm_setting cache failed: ' . $e->getMessage());
                }
            }
        }

        return (string) ($cache[$key] ?? $default);
    }
}

if (! function_exists('fm_clear_settings_cache')) {
    function fm_clear_settings_cache(): void
    {
        cache()->delete('fm_system_settings');
    }
}

if (! function_exists('fm_unread_count')) {
    /**
     * Return unread notification count for the current session user.
     */
    function fm_unread_count(): int
    {
        $userId = (int) session()->get('user_id');
        if (! $userId) {
            return 0;
        }

        $cacheKey = 'fm_unread_' . $userId;
        $cached   = cache()->get($cacheKey);
        if ($cached !== null && is_numeric($cached)) {
            return (int) $cached;
        }

        try {
            $count = (int) \Config\Database::connect()
                ->table('notifications')
                ->where('user_id', $userId)
                ->where('is_read', 0)
                ->countAllResults();
            cache()->save($cacheKey, $count, 30);

            return $count;
        } catch (\Throwable $e) {
            return 0;
        }
    }
}

if (! function_exists('fm_status_badge')) {
    /**
     * Render a status badge span.
     */
    function fm_status_badge(string $status, ?string $label = null): string
    {
        $label = $label ?? ucwords(str_replace('_', ' ', $status));
        return '<span class="badge-status badge-' . esc($status) . '">' . esc($label) . '</span>';
    }
}

if (! function_exists('fm_priority_badge')) {
    function fm_priority_badge(string $priority): string
    {
        return '<span class="badge-status badge-' . esc($priority) . '">' . esc(ucfirst($priority)) . '</span>';
    }
}

if (! function_exists('fm_logo_url')) {
    function fm_logo_url(string $stored = ''): string
    {
        $stored = trim($stored);
        if ($stored === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $stored)) {
            return $stored;
        }

        if (str_starts_with($stored, '/')) {
            return base_url(ltrim($stored, '/'));
        }

        if (str_contains($stored, '/')) {
            [$dir, $file] = array_pad(explode('/', $stored, 2), 2, '');
            $dir  = basename($dir);
            $file = basename($file);
            if ($dir !== '' && $file !== '' && is_file(WRITEPATH . 'uploads/' . $dir . '/' . $file)) {
                return base_url('file/' . $dir . '/' . rawurlencode($file));
            }

            return '';
        }

        $file = basename($stored);
        if (is_file(WRITEPATH . 'uploads/logos/' . $file)) {
            return base_url('file/logos/' . rawurlencode($file));
        }
        if (is_file(WRITEPATH . 'uploads/' . $file)) {
            return base_url('file/logo/' . rawurlencode($file));
        }

        return '';
    }
}

if (! function_exists('fm_role_display')) {
    function fm_role_display(?string $roleName): string
    {
        if ($roleName === null || $roleName === '') {
            return '';
        }

        static $map = null;
        if ($map === null) {
            $map = [];
            try {
                $rows = \Config\Database::connect()
                    ->table('roles')
                    ->select('name, display_name')
                    ->get()->getResultArray();
                foreach ($rows as $r) {
                    $map[$r['name']] = $r['display_name'];
                }
            } catch (\Throwable $e) {
                $map = [];
            }
        }

        return $map[$roleName] ?? ucwords(str_replace('_', ' ', $roleName));
    }
}

if (! function_exists('fm_logo_data_uri')) {
    /**
     * Convert a stored logo path to an inline base64 data-URI for PDF/print views.
     * Falls back to an empty string so views can safely do: $img ? "src='$img'" : ''.
     */
    function fm_logo_data_uri(string $stored = ''): string
    {
        $stored = trim($stored);
        if ($stored === '') {
            return '';
        }

        // Resolve the physical path the same way fm_logo_url() does
        $candidates = [];
        if (str_contains($stored, '/')) {
            [$dir, $file] = array_pad(explode('/', $stored, 2), 2, '');
            $dir  = basename($dir);
            $file = basename($file);
            if ($dir !== '' && $file !== '') {
                $candidates[] = WRITEPATH . 'uploads/' . $dir . '/' . $file;
            }
        }
        $file        = basename($stored);
        $candidates[] = WRITEPATH . 'uploads/logos/' . $file;
        $candidates[] = WRITEPATH . 'uploads/' . $file;
        $candidates[] = FCPATH . 'uploads/logos/' . $file;
        $candidates[] = FCPATH . 'uploads/' . $file;

        $path = '';
        foreach ($candidates as $c) {
            if (is_file($c)) {
                $path = $c;
                break;
            }
        }

        if ($path === '' || ! is_readable($path)) {
            return '';
        }

        $raw  = @file_get_contents($path);
        if ($raw === false) {
            return '';
        }

        $mime = mime_content_type($path) ?: 'image/png';
        return 'data:' . $mime . ';base64,' . base64_encode($raw);
    }
}

if (! function_exists('fm_company_branding')) {
    /**
     * Letterhead data from companies table merged over system_settings.
     *
     * @param array<string,string> $systemSettings
     * @return array{company_id: int, settings: array<string,string>, logoUrl: string, logoB64: string, row: array<string,mixed>|null}
     */
    function fm_company_branding(array $systemSettings = [], ?int $companyId = null): array
    {
        $db = \Config\Database::connect();

        return (new \App\Services\CompanyBrandingService($db))->branding($systemSettings, $companyId);
    }
}

if (! function_exists('fm_sequence_from_code')) {
    /** Last numeric segment from codes like WO-2026-0001 (PHP 8+ safe for end()). */
    function fm_sequence_from_code(string $code, string $delimiter = '-'): int
    {
        $parts = explode($delimiter, $code);
        if ($parts === []) {
            return 0;
        }
        $last = $parts[count($parts) - 1];

        return (int) $last;
    }
}

if (! function_exists('fm_session_user_id')) {
    /** Session user_id; treats 0 as valid (legacy DB rows). */
    function fm_session_user_id(): int
    {
        if (! session()->has('user_id')) {
            return 0;
        }

        return (int) session()->get('user_id');
    }
}

if (! function_exists('fm_is_logged_in')) {
    function fm_is_logged_in(): bool
    {
        return session()->has('user_id');
    }
}

if (! function_exists('fm_insert_row_id')) {
    /**
     * Insert a row and return the new primary key.
     * Handles legacy tables missing AUTO_INCREMENT by assigning next id explicitly.
     *
     * @param  array<string, mixed>  $row
     */
    function fm_insert_row_id(\CodeIgniter\Database\BaseConnection $db, string $table, array $row): int
    {
        $db->table($table)->insert($row);
        $id = (int) $db->insertID();
        if ($id > 0) {
            return $id;
        }

        $max = $db->table($table)->selectMax('id', 'max_id')->get()->getRowArray();
        $next = ((int) ($max['max_id'] ?? 0)) + 1;
        $row['id'] = $next;
        $db->table($table)->insert($row);

        return $next;
    }
}

if (! function_exists('fm_workspace_prefix')) {
    /** PM workspace uses /properties; FM uses /facilities. */
    function fm_workspace_prefix(): string
    {
        $ws = session()->get('workspace') ?? 'fm';

        return in_array($ws, ['pm', 'both'], true) ? 'properties' : 'facilities';
    }
}

if (! function_exists('fm_normalize_route_path')) {
    /**
     * Strip index.php and /public/ prefix from request paths for RBAC/workspace routing.
     */
    function fm_normalize_route_path(string $path): string
    {
        $path = preg_replace('#^.*/index\.php/#', '', $path) ?? $path;
        $path = trim($path, '/');
        while ($path !== '' && str_starts_with($path, 'public/')) {
            $path = substr($path, 7);
            $path = trim($path, '/');
        }

        return $path;
    }
}

if (! function_exists('fm_property_url')) {
    function fm_property_url(int $facilityId, string $suffix = ''): string
    {
        $path = fm_workspace_prefix() . '/' . $facilityId;
        if ($suffix !== '') {
            $path .= '/' . ltrim($suffix, '/');
        }

        return base_url($path);
    }
}

if (! function_exists('fm_property_units_url')) {
    function fm_property_units_url(int $facilityId): string
    {
        return fm_property_url($facilityId, 'units');
    }
}

if (! function_exists('fm_unit_view_url')) {
    function fm_unit_view_url(int $unitId): string
    {
        $ws = session()->get('workspace') ?? 'fm';
        if (in_array($ws, ['pm', 'both'], true)) {
            return base_url('properties/units/view/' . $unitId);
        }

        return base_url('units/view/' . $unitId);
    }
}

if (! function_exists('fm_document_types')) {
    /**
     * Canonical document type labels used by upload forms.
     *
     * @return array<string, string>
     */
    function fm_document_types(): array
    {
        return [
            'general'    => 'General',
            'id'         => 'ID / QID',
            'passport'   => 'Passport',
            'title_deed' => 'Title deed',
            'contract'   => 'Contract',
            'bank'       => 'Bank',
            'insurance'  => 'Insurance',
            'other'      => 'Other',
        ];
    }
}
