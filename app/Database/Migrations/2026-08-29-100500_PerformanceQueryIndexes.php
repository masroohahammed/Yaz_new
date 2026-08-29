<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Query-path indexes for dashboards, notifications, HR, and invoices.
 */
class PerformanceQueryIndexes extends Migration
{
    /** @var array<string, list<array{name: string, cols: list<string>}>> */
    private array $indexes = [
        'invoices' => [
            ['name' => 'idx_inv_status_due', 'cols' => ['status', 'due_date']],
            ['name' => 'idx_inv_facility_status', 'cols' => ['facility_id', 'status']],
            ['name' => 'idx_inv_issue', 'cols' => ['issue_date']],
            ['name' => 'idx_inv_company', 'cols' => ['company_id']],
        ],
        'expenses' => [
            ['name' => 'idx_exp_status_date', 'cols' => ['status', 'expense_date']],
            ['name' => 'idx_exp_facility', 'cols' => ['facility_id']],
        ],
        'notifications' => [
            ['name' => 'idx_notif_user_read', 'cols' => ['user_id', 'is_read']],
        ],
        'employee_profiles' => [
            ['name' => 'idx_ep_company', 'cols' => ['company_id']],
            ['name' => 'idx_ep_user', 'cols' => ['user_id']],
        ],
        'work_orders' => [
            ['name' => 'idx_wo_assigned', 'cols' => ['assigned_to']],
            ['name' => 'idx_wo_supervisor', 'cols' => ['supervisor_id']],
        ],
        'lease_payments' => [
            ['name' => 'idx_lpay_unit_status', 'cols' => ['unit_id', 'status']],
            ['name' => 'idx_lpay_facility_status', 'cols' => ['facility_id', 'status']],
        ],
        'commission_rules' => [
            ['name' => 'idx_cr_company', 'cols' => ['company_id']],
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
        if ($this->indexExists($table, $name)) {
            return;
        }
        $safeTable = str_replace('`', '', $table);
        $safeName  = str_replace('`', '', $name);
        $colSql    = implode(', ', array_map(static fn ($c) => '`' . str_replace('`', '', $c) . '`', $cols));
        $this->db->query("ALTER TABLE `{$safeTable}` ADD INDEX `{$safeName}` ({$colSql})");
    }

    private function indexExists(string $table, string $name): bool
    {
        try {
            if ($this->db->DBDriver !== 'MySQLi' && $this->db->DBDriver !== 'MySQL') {
                return false;
            }
            $rows = $this->db->query('SHOW INDEX FROM `' . str_replace('`', '', $table) . '`')->getResultArray();
            foreach ($rows as $row) {
                if (($row['Key_name'] ?? '') === $name) {
                    return true;
                }
            }
        } catch (\Throwable $e) {
            log_message('error', 'Index check failed: ' . $e->getMessage());
        }

        return false;
    }
}
