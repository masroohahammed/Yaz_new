<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class SalesNonFacilityWorkflow extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('customers')) {
            $this->forge->addField([
                'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'name'       => ['type' => 'VARCHAR', 'constraint' => 200],
                'mobile'     => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
                'email'      => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
                'company_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'notes'      => ['type' => 'TEXT', 'null' => true],
                'created_by' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('mobile');
            $this->forge->createTable('customers');
        }

        if ($this->db->tableExists('maintenance_requests')) {
            if (! $this->db->fieldExists('work_type', 'maintenance_requests')) {
                $this->forge->addColumn('maintenance_requests', [
                    'work_type' => [
                        'type'       => 'ENUM',
                        'constraint' => ['facility', 'non_facility'],
                        'default'    => 'facility',
                        'after'      => 'company_id',
                    ],
                ]);
            }
            foreach ([
                'customer_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'work_type'],
                'salesman_id'  => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'customer_id'],
                'sales_rep_name' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'salesman_id'],
            ] as $col => $def) {
                if (! $this->db->fieldExists($col, 'maintenance_requests')) {
                    $this->forge->addColumn('maintenance_requests', [$col => $def]);
                }
            }
        }

        if ($this->db->tableExists('estimations')) {
            foreach ([
                'salesman_id'            => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'created_by'],
                'customer_id'            => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'salesman_id'],
                'maintenance_request_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'customer_id'],
            ] as $col => $def) {
                if (! $this->db->fieldExists($col, 'estimations')) {
                    $this->forge->addColumn('estimations', [$col => $def]);
                }
            }
        }

        if ($this->db->tableExists('invoices') && ! $this->db->fieldExists('estimation_id', 'invoices')) {
            $this->forge->addColumn('invoices', [
                'estimation_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'work_order_id'],
            ]);
        }

        if (! $this->db->table('roles')->where('name', 'salesman')->countAllResults()) {
            $this->db->table('roles')->insert([
                'name'         => 'salesman',
                'display_name' => 'Salesman',
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down()
    {
        $this->db->table('roles')->where('name', 'salesman')->delete();
        if ($this->db->tableExists('customers')) {
            $this->forge->dropTable('customers', true);
        }
    }
}
