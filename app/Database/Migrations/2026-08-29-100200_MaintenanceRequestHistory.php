<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Append-only audit trail for maintenance request workflow actions.
 * Existing review/verify/approve columns on maintenance_requests are kept.
 */
class MaintenanceRequestHistory extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('maintenance_request_history')) {
            return;
        }

        $this->forge->addField([
            'id'                     => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'maintenance_request_id' => ['type' => 'INT', 'unsigned' => true],
            'action'                 => ['type' => 'VARCHAR', 'constraint' => 50],
            'previous_status'        => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'new_status'             => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'performed_by'           => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'note'                   => ['type' => 'TEXT', 'null' => true],
            'metadata'               => ['type' => 'JSON', 'null' => true],
            'created_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('maintenance_request_id');
        $this->forge->addKey('created_at');
        $this->forge->createTable('maintenance_request_history', true);
    }

    public function down(): void
    {
        if ($this->db->tableExists('maintenance_request_history')) {
            $this->forge->dropTable('maintenance_request_history', true);
        }
    }
}
