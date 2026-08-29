<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class WorkOrderContractAndDocs extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('work_orders') && ! $this->db->fieldExists('contract_id', 'work_orders')) {
            $this->forge->addColumn('work_orders', [
                'contract_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'unit_id'],
            ]);
        }

        if ($this->db->tableExists('contracts') && ! $this->db->fieldExists('attachment_path', 'contracts')) {
            $this->forge->addColumn('contracts', [
                'attachment_path' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'notes'],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->fieldExists('contract_id', 'work_orders')) {
            $this->forge->dropColumn('work_orders', 'contract_id');
        }
        if ($this->db->fieldExists('attachment_path', 'contracts')) {
            $this->forge->dropColumn('contracts', 'attachment_path');
        }
    }
}
