<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ContractReManager extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('user_facilities')) {
            $this->forge->addField([
                'id'          => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'user_id'     => ['type' => 'INT', 'unsigned' => true],
                'facility_id' => ['type' => 'INT', 'unsigned' => true],
                'created_at'  => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['user_id', 'facility_id']);
            $this->forge->addKey('facility_id');
            $this->forge->createTable('user_facilities');
        }

        if ($this->db->tableExists('work_orders') && ! $this->db->fieldExists('contract_id', 'work_orders')) {
            $this->forge->addColumn('work_orders', [
                'contract_id' => [
                    'type'       => 'INT',
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'estimation_id',
                ],
            ]);
        }

        if ($this->db->tableExists('work_orders') && ! $this->db->fieldExists('maintenance_request_id', 'work_orders')) {
            $this->forge->addColumn('work_orders', [
                'maintenance_request_id' => [
                    'type'     => 'INT',
                    'unsigned' => true,
                    'null'     => true,
                    'after'    => 'contract_id',
                ],
            ]);
        }

        $exists = $this->db->table('roles')->where('name', 'real_estate_manager')->countAllResults();
        if (! $exists) {
            $this->db->table('roles')->insert([
                'name'         => 'real_estate_manager',
                'display_name' => 'Real Estate Manager',
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function down()
    {
        if ($this->db->tableExists('user_facilities')) {
            $this->forge->dropTable('user_facilities', true);
        }
        if ($this->db->fieldExists('contract_id', 'work_orders')) {
            $this->forge->dropColumn('work_orders', 'contract_id');
        }
        if ($this->db->fieldExists('maintenance_request_id', 'work_orders')) {
            $this->forge->dropColumn('work_orders', 'maintenance_request_id');
        }
        $this->db->table('roles')->where('name', 'real_estate_manager')->delete();
    }
}
