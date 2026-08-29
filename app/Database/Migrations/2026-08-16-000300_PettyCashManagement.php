<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Petty Cash Management — SQL: database/patch_petty_cash_management.sql */
class PettyCashManagement20260816 extends Migration
{
    public function up(): void
    {
        $path = ROOTPATH . 'database/patch_petty_cash_management.sql';
        if (! is_file($path)) {
            return;
        }

        $sql = file_get_contents($path);
        foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
            if ($stmt === '' || str_starts_with($stmt, '--') || str_starts_with(strtoupper($stmt), 'SET ')) {
                continue;
            }
            try {
                $this->db->query($stmt);
            } catch (\Throwable $e) {
                log_message('warning', 'PettyCash migration stmt skipped: ' . $e->getMessage());
            }
        }
    }

    public function down(): void
    {
        foreach ([
            'finance_petty_audit_logs', 'finance_petty_reconciliations', 'finance_petty_count_lines',
            'finance_petty_counts', 'finance_petty_transfers', 'finance_petty_replenishments',
            'finance_petty_advance_settlements', 'finance_petty_advances', 'finance_petty_expenses',
            'finance_petty_custodian_history', 'finance_petty_cash_accounts',
        ] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table, true);
            }
        }
    }
}
