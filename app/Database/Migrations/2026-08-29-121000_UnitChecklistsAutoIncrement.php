<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Production databases may have unit_checklists.id without AUTO_INCREMENT
 * and a legacy row with id=0, causing "Duplicate entry '0' for key 'PRIMARY'".
 */
class UnitChecklistsAutoIncrement extends Migration
{
    public function up(): void
    {
        $table = 'unit_checklists';
        if (! $this->db->tableExists($table)) {
            return;
        }

        $col = $this->db->query("SHOW COLUMNS FROM `{$table}` WHERE Field = 'id'")->getRowArray();
        if (! $col || stripos((string) ($col['Extra'] ?? ''), 'auto_increment') !== false) {
            return;
        }

        $maxRow = $this->db->table($table)->selectMax('id', 'max_id')->get()->getRowArray();
        $maxId  = (int) ($maxRow['max_id'] ?? 0);

        if ($this->db->table($table)->where('id', 0)->countAllResults() > 0) {
            $newId = $maxId + 1;
            $this->db->table($table)->where('id', 0)->update(['id' => $newId]);
            $maxId = $newId;
        }

        $type = $col['Type'] ?? 'int(10) unsigned';
        $next = $maxId + 1;
        $this->db->query(
            "ALTER TABLE `{$table}` MODIFY `id` {$type} NOT NULL AUTO_INCREMENT, AUTO_INCREMENT={$next}"
        );
    }

    public function down(): void
    {
        // Non-destructive upgrade migration.
    }
}
