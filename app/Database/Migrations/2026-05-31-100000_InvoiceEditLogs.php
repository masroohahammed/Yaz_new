<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InvoiceEditLogs extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('invoice_edit_logs')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'invoice_id' => ['type' => 'INT', 'unsigned' => true],
            'user_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'action' => ['type' => 'VARCHAR', 'constraint' => 50, 'default' => 'update'],
            'summary' => ['type' => 'TEXT', 'null' => true],
            'changes_json' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('invoice_id');
        $this->forge->createTable('invoice_edit_logs', true);
    }

    public function down()
    {
        if ($this->db->tableExists('invoice_edit_logs')) {
            $this->forge->dropTable('invoice_edit_logs', true);
        }
    }
}
