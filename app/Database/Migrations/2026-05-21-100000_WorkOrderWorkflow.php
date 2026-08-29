<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class WorkOrderWorkflow extends Migration
{
    public function up()
    {
        $fields = [
            'qa_status' => [
                'type'       => 'ENUM',
                'constraint' => ['none', 'pending', 'approved', 'rejected'],
                'default'    => 'none',
                'after'      => 'approval_status',
            ],
            'qa_approved_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'qa_approved_at' => ['type' => 'DATETIME', 'null' => true],
            'client_approval_status' => [
                'type'       => 'ENUM',
                'constraint' => ['none', 'pending', 'approved', 'rejected'],
                'default'    => 'none',
            ],
            'client_approved_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'client_approved_at' => ['type' => 'DATETIME', 'null' => true],
            'invoice_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
        ];

        foreach ($fields as $name => $def) {
            if (!$this->db->fieldExists($name, 'work_orders')) {
                $this->forge->addColumn('work_orders', [$name => $def]);
            }
        }

        if (!$this->db->fieldExists('company_id', 'maintenance_requests')) {
            $this->forge->addColumn('maintenance_requests', [
                'company_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'facility_id'],
            ]);
        }

        foreach (['inventory_items', 'vendors'] as $table) {
            if (!$this->db->fieldExists('company_id', $table)) {
                $this->forge->addColumn($table, [
                    'company_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'id'],
                ]);
            }
        }
    }

    public function down()
    {
        foreach ([
            'invoice_id', 'client_approved_at', 'client_approved_by', 'client_approval_status',
            'qa_approved_at', 'qa_approved_by', 'qa_status',
        ] as $col) {
            if ($this->db->fieldExists($col, 'work_orders')) {
                $this->forge->dropColumn('work_orders', $col);
            }
        }
    }
}
