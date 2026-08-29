<?php

namespace App\Services\Finance;

use CodeIgniter\Database\BaseConnection;

class FinanceSettingsService
{
    public const KEYS = [
        'fin_prefix_income'              => 'INC',
        'fin_prefix_expense'             => 'EXP',
        'fin_prefix_deposit'             => 'DEP',
        'fin_prefix_withdrawal'          => 'WDL',
        'fin_prefix_transfer'            => 'TRF',
        'fin_prefix_receipt'             => 'REC',
        'fin_prefix_payment'             => 'PAY',
        'fin_prefix_adjustment'          => 'ADJ',
        'fin_approval_enabled'           => '1',
        'fin_self_approval_override'     => '0',
        'fin_approval_tier1_max'         => '5000',
        'fin_approval_tier2_max'         => '25000',
        'fin_approval_tier1_roles'       => 'finance_manager',
        'fin_approval_tier2_roles'       => 'finance_manager,facility_manager',
        'fin_approval_tier3_roles'       => 'finance_manager,facility_manager,super_admin',
        'fin_low_balance_default'        => '10000',
        'fin_opening_balance_adjust_role'=> 'super_admin,finance_manager',
    ];

    /** @var array<string,string> */
    private array $cache = [];

    public function __construct(private BaseConnection $db)
    {
    }

    public function get(string $key, string $default = ''): string
    {
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        if (! $this->db->tableExists('system_settings')) {
            return self::KEYS[$key] ?? $default;
        }

        $row = $this->db->table('system_settings')->where('setting_key', $key)->get()->getRowArray();
        $val = (string) ($row['setting_value'] ?? (self::KEYS[$key] ?? $default));
        $this->cache[$key] = $val;

        return $val;
    }

    public function prefix(string $type): string
    {
        return match ($type) {
            'income'     => $this->get('fin_prefix_income', 'INC'),
            'expense'    => $this->get('fin_prefix_expense', 'EXP'),
            'deposit'    => $this->get('fin_prefix_deposit', 'DEP'),
            'withdrawal' => $this->get('fin_prefix_withdrawal', 'WDL'),
            'transfer'   => $this->get('fin_prefix_transfer', 'TRF'),
            'receipt'    => $this->get('fin_prefix_receipt', 'REC'),
            'payment'    => $this->get('fin_prefix_payment', 'PAY'),
            'adjustment' => $this->get('fin_prefix_adjustment', 'ADJ'),
            default      => strtoupper(substr($type, 0, 3)),
        };
    }

    /** @return list<string> */
    public function approvalRolesForAmount(float $amount): array
    {
        $t1 = (float) $this->get('fin_approval_tier1_max', '5000');
        $t2 = (float) $this->get('fin_approval_tier2_max', '25000');

        $key = $amount <= $t1 ? 'fin_approval_tier1_roles'
            : ($amount <= $t2 ? 'fin_approval_tier2_roles' : 'fin_approval_tier3_roles');

        $raw = $this->get($key, 'finance_manager');

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    public function approvalEnabled(): bool
    {
        return $this->get('fin_approval_enabled', '1') === '1';
    }

    public function selfApprovalOverrideAllowed(): bool
    {
        return $this->get('fin_self_approval_override', '0') === '1';
    }

    /** @param array<string,string> $values */
    public function save(array $values): void
    {
        if (! $this->db->tableExists('system_settings')) {
            return;
        }

        foreach ($values as $key => $value) {
            if (! array_key_exists($key, self::KEYS)) {
                continue;
            }
            $exists = $this->db->table('system_settings')->where('setting_key', $key)->countAllResults();
            if ($exists) {
                $this->db->table('system_settings')->where('setting_key', $key)->update(['setting_value' => $value]);
            } else {
                $this->db->table('system_settings')->insert([
                    'setting_key'   => $key,
                    'setting_value' => $value,
                    'category'      => 'finance',
                    'created_at'    => date('Y-m-d H:i:s'),
                ]);
            }
            $this->cache[$key] = (string) $value;
        }
        cache()->delete('fm_system_settings');
    }
}
