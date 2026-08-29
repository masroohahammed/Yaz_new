<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class InvoiceBillToNonFacility extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('invoices') && $this->db->fieldExists('facility_id', 'invoices')) {
            $this->db->query('ALTER TABLE `invoices` MODIFY `facility_id` INT(10) UNSIGNED NULL');
        }

        if ($this->db->tableExists('invoices')) {
            $cols = [
                'bill_to_name'    => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true, 'after' => 'facility_id'],
                'bill_to_email'   => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => true],
                'bill_to_phone'   => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
                'bill_to_address' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'service_customer_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            ];
            foreach ($cols as $name => $def) {
                if (! $this->db->fieldExists($name, 'invoices')) {
                    $this->forge->addColumn('invoices', [$name => $def]);
                }
            }
        }

        if ($this->db->tableExists('work_orders')) {
            if (! $this->db->fieldExists('service_customer_id', 'work_orders')) {
                $this->forge->addColumn('work_orders', [
                    'service_customer_id' => ['type' => 'INT', 'unsigned' => true, 'null' => true, 'after' => 'facility_id'],
                ]);
            }
            if (! $this->db->fieldExists('requester_location', 'work_orders')) {
                $this->forge->addColumn('work_orders', [
                    'requester_location' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true, 'after' => 'requester_email'],
                ]);
            }
        }
    }

    public function down()
    {
        // Non-destructive: leave columns in place on rollback
    }
}
