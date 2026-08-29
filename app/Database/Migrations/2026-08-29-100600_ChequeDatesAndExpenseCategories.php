<?php

namespace App\Database\Migrations;

use App\Support\PmExpenseCategories;
use CodeIgniter\Database\Migration;

/**
 * Persist cheque deposit/clearance dates and expand expenses.category
 * without dropping existing values.
 */
class ChequeDatesAndExpenseCategories extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('cheques')) {
            $cols = [];
            if (! $this->db->fieldExists('deposit_date', 'cheques')) {
                $cols['deposit_date'] = ['type' => 'DATE', 'null' => true];
            }
            if (! $this->db->fieldExists('clearance_date', 'cheques')) {
                $cols['clearance_date'] = ['type' => 'DATE', 'null' => true];
            }
            if ($cols !== []) {
                $this->forge->addColumn('cheques', $cols);
            }
            $this->addIndexIfMissing('cheques', 'idx_chq_facility_date', ['facility_id', 'cheque_date']);
            $this->addIndexIfMissing('cheques', 'idx_chq_status', ['status']);
        }

        if ($this->db->tableExists('expenses')) {
            $enum = PmExpenseCategories::sqlEnum();
            $this->db->query("ALTER TABLE `expenses` MODIFY COLUMN `category` ENUM({$enum}) NOT NULL DEFAULT 'other'");
            $this->addIndexIfMissing('expenses', 'idx_exp_category', ['category']);
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('cheques')) {
            foreach (['deposit_date', 'clearance_date'] as $col) {
                if ($this->db->fieldExists($col, 'cheques')) {
                    $this->forge->dropColumn('cheques', $col);
                }
            }
        }
        // ENUM shrink is skipped — existing rows may already use new values.
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
