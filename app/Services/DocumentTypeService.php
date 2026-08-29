<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Document type labels for uploads (defaults + admin-defined custom types in system_settings).
 */
class DocumentTypeService
{
    public const SETTING_KEY = 'document_management_types';

    private ?BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db;
    }

    /** @return array<string, string> */
    public function defaults(): array
    {
        $cfg = config('DocumentManagementTypes');

        return $cfg->types ?? [];
    }

    /** @return array<string, string> */
    public function custom(): array
    {
        helper('fm');
        $json = fm_setting(self::SETTING_KEY, '');
        if ($json === '') {
            return [];
        }
        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : [];
    }

    /** @return array<string, string> */
    public function all(): array
    {
        return array_merge($this->defaults(), $this->custom());
    }

    public function label(string $key): string
    {
        $all = $this->all();

        return $all[$key] ?? $key;
    }

    public function normalizeKey(string $key): string
    {
        $key = strtolower(trim($key));
        if ($key === '') {
            return '';
        }

        $key = preg_replace('/[^a-z0-9_]+/', '_', $key) ?? '';
        $key = trim($key, '_');

        return $key;
    }

    public function add(string $key, string $label): bool
    {
        $key   = $this->normalizeKey($key);
        $label = trim($label);
        if ($key === '' || $label === '') {
            return false;
        }

        $custom = $this->custom();
        $custom[$key] = $label;
        $this->saveCustom($custom);

        return true;
    }

    /** @param array<string, string> $types */
    private function saveCustom(array $types): void
    {
        $db = $this->db ?? \Config\Database::connect();
        if (! $db->tableExists('system_settings')) {
            throw new \RuntimeException('system_settings table is not available.');
        }

        $json = json_encode($types, JSON_UNESCAPED_UNICODE);
        $exists = $db->table('system_settings')->where('setting_key', self::SETTING_KEY)->countAllResults();
        if ($exists) {
            $db->table('system_settings')->where('setting_key', self::SETTING_KEY)->update(['setting_value' => $json]);
        } else {
            $db->table('system_settings')->insert([
                'setting_key'   => self::SETTING_KEY,
                'setting_value' => $json,
            ]);
        }

        cache()->delete('system_settings');
        helper('fm');
        fm_clear_settings_cache();
    }
}
