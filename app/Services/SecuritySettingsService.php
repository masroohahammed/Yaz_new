<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Security-related system_settings with sane defaults.
 */
class SecuritySettingsService
{
    public const KEYS = [
        'sec_public_registration'     => '0',
        'sec_login_max_attempts'        => '5',
        'sec_login_lockout_minutes'     => '15',
        'sec_password_min_length'       => '8',
        'sec_password_expire_days'      => '90',
        'sec_require_mfa_admins'        => '0',
        'sec_session_idle_minutes'      => '120',
        'sec_audit_sensitive_actions'   => '1',
        'sec_force_https'               => '0',
    ];

    public const LABELS = [
        'sec_public_registration'   => 'Allow public self-registration',
        'sec_login_max_attempts'    => 'Max failed login attempts before lockout',
        'sec_login_lockout_minutes' => 'Login lockout window (minutes)',
        'sec_password_min_length'   => 'Minimum password length',
        'sec_password_expire_days'  => 'Force password change after (days, 0=off)',
        'sec_require_mfa_admins'      => 'Require MFA for super_admin accounts',
        'sec_session_idle_minutes'  => 'Session idle timeout (minutes, 0=browser session)',
        'sec_audit_sensitive_actions' => 'Log sensitive create/update/delete actions',
        'sec_force_https'           => 'Redirect HTTP to HTTPS (when server supports TLS)',
    ];

    private ?BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db;
    }

    /** @return array<string, string> */
    public function all(): array
    {
        $out = self::KEYS;
        if ($this->db === null || ! $this->db->tableExists('system_settings')) {
            return $out;
        }

        $rows = $this->db->table('system_settings')
            ->whereIn('setting_key', array_keys(self::KEYS))
            ->get()->getResultArray();

        foreach ($rows as $row) {
            $out[$row['setting_key']] = (string) $row['setting_value'];
        }

        return $out;
    }

    public function get(string $key, ?string $default = null): string
    {
        $all = $this->all();

        return $all[$key] ?? $default ?? self::KEYS[$key] ?? '';
    }

    public function enabled(string $key): bool
    {
        return $this->get($key) === '1';
    }

    public function int(string $key): int
    {
        return (int) $this->get($key);
    }

    public function save(array $posted): void
    {
        if ($this->db === null) {
            throw new \RuntimeException('Database required');
        }

        $toggles = [
            'sec_public_registration',
            'sec_require_mfa_admins',
            'sec_audit_sensitive_actions',
            'sec_force_https',
        ];

        foreach (array_keys(self::KEYS) as $key) {
            if (in_array($key, $toggles, true)) {
                $val = ! empty($posted[$key]) ? '1' : '0';
            } else {
                $val = trim((string) ($posted[$key] ?? self::KEYS[$key]));
            }
            $this->upsert($key, $val);
        }

        cache()->delete('fm_system_settings');
        helper('fm');
        if (function_exists('fm_clear_settings_cache')) {
            fm_clear_settings_cache();
        }
    }

    private function upsert(string $key, string $value): void
    {
        $exists = $this->db->table('system_settings')->where('setting_key', $key)->countAllResults();
        if ($exists) {
            $this->db->table('system_settings')->where('setting_key', $key)->update(['setting_value' => $value]);
        } else {
            $this->db->table('system_settings')->insert([
                'setting_key'   => $key,
                'setting_value' => $value,
                'category'      => 'security',
                'created_at'    => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
