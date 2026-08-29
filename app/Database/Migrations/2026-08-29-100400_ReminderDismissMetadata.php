<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ReminderDismissMetadata extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('reminders')) {
            return;
        }
        $cols = [];
        if (! $this->db->fieldExists('dismissed_by', 'reminders')) {
            $cols['dismissed_by'] = ['type' => 'INT', 'unsigned' => true, 'null' => true];
        }
        if (! $this->db->fieldExists('dismissed_at', 'reminders')) {
            $cols['dismissed_at'] = ['type' => 'DATETIME', 'null' => true];
        }
        if ($cols !== []) {
            $this->forge->addColumn('reminders', $cols);
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('reminders')) {
            return;
        }
        foreach (['dismissed_by', 'dismissed_at'] as $col) {
            if ($this->db->fieldExists($col, 'reminders')) {
                $this->forge->dropColumn('reminders', $col);
            }
        }
    }
}
