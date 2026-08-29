<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ComplianceInspectionAutoincrement extends Migration
{
    public function up(): void
    {
        foreach (['inspection_checklists', 'inspection_items'] as $table) {
            if (! $this->db->tableExists($table)) {
                continue;
            }

            $col = $this->db->query("SHOW COLUMNS FROM `{$table}` WHERE Field = 'id'")->getRowArray();
            if (! $col) {
                continue;
            }

            if (stripos((string) ($col['Extra'] ?? ''), 'auto_increment') !== false) {
                continue;
            }

            $maxRow = $this->db->table($table)->selectMax('id', 'max_id')->get()->getRowArray();
            $maxId  = (int) ($maxRow['max_id'] ?? 0);

            if ($this->db->table($table)->where('id', 0)->countAllResults() > 0) {
                $newId = $maxId + 1;
                $this->db->table($table)->where('id', 0)->update(['id' => $newId]);
                $maxId = $newId;
            }

            $type = $col['Type'] ?? 'int(11)';
            $next = $maxId + 1;
            $this->db->query(
                "ALTER TABLE `{$table}` MODIFY `id` {$type} NOT NULL AUTO_INCREMENT, AUTO_INCREMENT={$next}"
            );
        }
    }

    public function down(): void
    {
        // Non-destructive upgrade migration.
    }
}
