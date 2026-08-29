<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ServiceCustomersAndCleanup extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('service_customers')) {
            $this->forge->addField([
                'id'         => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
                'name'       => ['type' => 'VARCHAR', 'constraint' => 150],
                'phone'      => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
                'email'      => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
                'location'   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'notes'      => ['type' => 'TEXT', 'null' => true],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('phone');
            $this->forge->addKey('name');
            $this->forge->createTable('service_customers', true);
        }

        if ($this->db->tableExists('maintenance_requests') && ! $this->db->fieldExists('service_customer_id', 'maintenance_requests')) {
            $this->forge->addColumn('maintenance_requests', [
                'service_customer_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'facility_id'],
                'requester_location'  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'requester_phone'],
            ]);
        }
    }

    public function down()
    {
    }
}
