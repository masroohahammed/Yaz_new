<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Mobile app remote logs (CTA success/error, splash, API failures).
 */
class AppMobileLogs extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('app_mobile_logs')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'action' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'info',
            ],
            'message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'context_json' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'app_version' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
                'null'       => true,
            ],
            'platform' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
                'null'       => true,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('action');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');
        $this->forge->createTable('app_mobile_logs', true);
    }

    public function down(): void
    {
        if ($this->db->tableExists('app_mobile_logs')) {
            $this->forge->dropTable('app_mobile_logs', true);
        }
    }
}
