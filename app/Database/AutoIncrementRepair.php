<?php

namespace App\Database;

use CodeIgniter\Database\BaseConnection;

/**
 * Ensures a table's primary key id column is AUTO_INCREMENT before inserts.
 */
class AutoIncrementRepair
{
    public static function ensure(BaseConnection $db, string $table): void
    {
        if (! $db->tableExists($table)) {
            return;
        }

        $col = $db->query("SHOW COLUMNS FROM `{$table}` WHERE Field = 'id'")->getRowArray();
        if (! $col || stripos((string) ($col['Extra'] ?? ''), 'auto_increment') !== false) {
            return;
        }

        $maxRow = $db->table($table)->selectMax('id', 'max_id')->get()->getRowArray();
        $maxId  = (int) ($maxRow['max_id'] ?? 0);

        if ($db->table($table)->where('id', 0)->countAllResults() > 0) {
            $newId = $maxId + 1;
            $db->table($table)->where('id', 0)->update(['id' => $newId]);
            $maxId = $newId;
        }

        $type = $col['Type'] ?? 'int(10) unsigned';
        $next = $maxId + 1;
        $db->query(
            "ALTER TABLE `{$table}` MODIFY `id` {$type} NOT NULL AUTO_INCREMENT, AUTO_INCREMENT={$next}"
        );
    }
}
