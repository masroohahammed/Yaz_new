<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Add high-value lookup indexes that are missing from the production dump.
 * Reversible. Skips an index if an equivalent already exists.
 */
class RemediationOperationalIndexes extends Migration
{
    /** @var array<string, list<array{name: string, cols: list<string>}>> */
    private array $indexes = [
        'lease_contracts' => [
            ['name' => 'idx_lc_company', 'cols' => ['company_id']],
            ['name' => 'idx_lc_template', 'cols' => ['template_id']],
            ['name' => 'idx_lc_parent', 'cols' => ['parent_contract_id']],
            ['name' => 'idx_lc_status', 'cols' => ['status']],
            ['name' => 'idx_lc_created', 'cols' => ['created_at']],
        ],
        'tenants' => [
            ['name' => 'idx_tenants_company', 'cols' => ['company_id']],
            ['name' => 'idx_tenants_current_unit', 'cols' => ['current_unit_id']],
            ['name' => 'idx_tenants_status', 'cols' => ['status']],
        ],
        'facilities' => [
            ['name' => 'idx_fac_landlord', 'cols' => ['landlord_id']],
            ['name' => 'idx_fac_caretaker', 'cols' => ['caretaker_id']],
        ],
        'landlords' => [
            ['name' => 'idx_landlords_company', 'cols' => ['company_id']],
            ['name' => 'idx_landlords_status', 'cols' => ['status']],
        ],
        'users' => [
            ['name' => 'idx_users_company', 'cols' => ['company_id']],
            ['name' => 'idx_users_tenant', 'cols' => ['tenant_id']],
        ],
        'lease_payments' => [
            ['name' => 'idx_lp_company', 'cols' => ['company_id']],
            ['name' => 'idx_lp_status', 'cols' => ['status']],
            ['name' => 'idx_lp_due', 'cols' => ['due_date']],
        ],
        'job_cards' => [
            ['name' => 'idx_jc_wo', 'cols' => ['wo_id']],
            ['name' => 'idx_jc_status', 'cols' => ['status']],
        ],
        'jc_materials' => [
            ['name' => 'idx_jcm_jc', 'cols' => ['jc_id']],
        ],
        'work_orders' => [
            ['name' => 'idx_wo_company', 'cols' => ['company_id']],
            ['name' => 'idx_wo_status', 'cols' => ['status']],
        ],
        'maintenance_requests' => [
            ['name' => 'idx_mr_company', 'cols' => ['company_id']],
            ['name' => 'idx_mr_status', 'cols' => ['status']],
            ['name' => 'idx_mr_created', 'cols' => ['created_at']],
        ],
        'documents' => [
            ['name' => 'idx_docs_module_ref', 'cols' => ['module', 'ref_id']],
        ],
        'landlord_payouts' => [
            ['name' => 'idx_lpayout_company', 'cols' => ['company_id']],
            ['name' => 'idx_lpayout_status', 'cols' => ['status']],
        ],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $table => $defs) {
            if (! $this->db->tableExists($table)) {
                continue;
            }
            foreach ($defs as $def) {
                $this->addIndexIfMissing($table, $def['name'], $def['cols']);
            }
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $table => $defs) {
            if (! $this->db->tableExists($table)) {
                continue;
            }
            foreach ($defs as $def) {
                if ($this->indexExists($table, $def['name'])) {
                    $safeTable = str_replace('`', '', $table);
                    $safeName  = str_replace('`', '', $def['name']);
                    $this->db->query("ALTER TABLE `{$safeTable}` DROP INDEX `{$safeName}`");
                }
            }
        }
    }

    /** @param list<string> $cols */
    private function addIndexIfMissing(string $table, string $name, array $cols): void
    {
        foreach ($cols as $col) {
            if (! $this->db->fieldExists($col, $table)) {
                return;
            }
        }
        if ($this->indexExists($table, $name) || $this->columnsAlreadyIndexed($table, $cols)) {
            return;
        }
        $safeTable = str_replace('`', '', $table);
        $safeName  = str_replace('`', '', $name);
        $colSql    = implode(', ', array_map(static fn ($c) => '`' . str_replace('`', '', $c) . '`', $cols));
        $this->db->query("ALTER TABLE `{$safeTable}` ADD INDEX `{$safeName}` ({$colSql})");
    }

    /** @param list<string> $cols */
    private function columnsAlreadyIndexed(string $table, array $cols): bool
    {
        $wanted = implode(',', $cols);
        foreach ($this->indexMap($table) as $indexCols) {
            if (implode(',', $indexCols) === $wanted) {
                return true;
            }
        }

        return false;
    }

    private function indexExists(string $table, string $name): bool
    {
        return isset($this->indexMap($table)[$name]);
    }

    /** @return array<string, list<string>> */
    private function indexMap(string $table): array
    {
        static $cache = [];
        if (isset($cache[$table])) {
            return $cache[$table];
        }

        $map = [];
        try {
            $driver = $this->db->DBDriver;
            if ($driver === 'MySQLi' || $driver === 'MySQL') {
                $rows = $this->db->query('SHOW INDEX FROM `' . str_replace('`', '', $table) . '`')->getResultArray();
                foreach ($rows as $row) {
                    $key = (string) ($row['Key_name'] ?? '');
                    $col = (string) ($row['Column_name'] ?? '');
                    if ($key === '' || $col === '') {
                        continue;
                    }
                    $map[$key][] = $col;
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Index audit failed for ' . $table . ': ' . $e->getMessage());
        }

        $cache[$table] = $map;

        return $map;
    }
}
