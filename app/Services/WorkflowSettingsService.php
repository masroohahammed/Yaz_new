<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

/**
 * Admin-configurable workflow gates (stored in system_settings).
 */
class WorkflowSettingsService
{
    public const KEYS = [
        'wf_require_supervisor_approval' => '1',
        'wf_require_qa_on_complete'        => '1',
        'wf_require_client_approval'       => '1',
        'wf_require_invoice_before_close'  => '1',
        'wf_require_labor_or_material'     => '1',
        'wf_auto_invoice_on_client_approve'=> '0',
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
        if ($this->db === null || !$this->db->tableExists('system_settings')) {
            return $out;
        }
        $rows = $this->db->table('system_settings')
            ->whereIn('setting_key', array_keys(self::KEYS))
            ->get()
            ->getResultArray();
        foreach ($rows as $r) {
            $out[$r['setting_key']] = (string) $r['setting_value'];
        }

        return $out;
    }

    public function enabled(string $key): bool
    {
        $all = $this->all();

        return ($all[$key] ?? self::KEYS[$key] ?? '0') === '1';
    }

    public function save(array $posted): void
    {
        if ($this->db === null) {
            throw new \RuntimeException('Database connection required for save operation');
        }
        foreach (array_keys(self::KEYS) as $key) {
            $val = isset($posted[$key]) && $posted[$key] ? '1' : '0';
            $exists = $this->db->table('system_settings')->where('setting_key', $key)->countAllResults();
            if ($exists) {
                $this->db->table('system_settings')->where('setting_key', $key)->update(['setting_value' => $val]);
            } else {
                $this->db->table('system_settings')->insert(['setting_key' => $key, 'setting_value' => $val]);
            }
        }
        cache()->delete('system_settings');
    }
}
